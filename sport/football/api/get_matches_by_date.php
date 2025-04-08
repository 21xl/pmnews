<?php
// Регистрация нового API-эндпоинта
function register_matches_by_date_endpoint()
{
    register_rest_route('sports/v1', '/matches_by_date', array(
        'methods' => 'GET',
        'callback' => 'get_matches_by_date',
        'args' => array(
            'date' => array(
                'required' => true,
                'validate_callback' => function ($param) {
                    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $param); // Проверка формата даты YYYY-MM-DD
                },
            ),
            'timezone_offset' => array(
                'required' => false,
                'validate_callback' => function ($param) {
                    return is_numeric($param); // Проверка, что это число
                },
            ),
        ),
        'permission_callback' => '__return_true',
    ));
}

add_action('rest_api_init', 'register_matches_by_date_endpoint');

// Обработчик для получения матчей за выбранную дату, сгруппированных по соревнованиям
function get_matches_by_date($request)
{
    global $wpdb;

    $date = sanitize_text_field($request->get_param('date'));
    $timezone_offset = intval($request->get_param('timezone_offset', 0)); // Смещение временной зоны (в минутах)

    // Конвертируем смещение из минут в секунды
    $offset_in_seconds = $timezone_offset * 60;

    // Определяем временные границы для выбранной даты с учетом смещения
    $start_timestamp = strtotime($date . ' 00:00:00') + $offset_in_seconds;
    $end_timestamp = strtotime($date . ' 23:59:59') + $offset_in_seconds;

    // Проверяем тип даты: сегодня, будущее или прошлое
    $today = date('Y-m-d');
    $is_today = $today === $date;
    $is_future = $date > $today;

    // Запрос для получения матчей за выбранную дату
    if ($is_future) {
        // Исключаем матчи со статусом 9 для будущих дат
        $matches_query = $wpdb->prepare("
            SELECT * 
            FROM wp_sport_matches_shedule 
            WHERE match_time BETWEEN %d AND %d
              AND status_id != 9
            ORDER BY match_time ASC
        ", $start_timestamp, $end_timestamp);
    } else {
        // Стандартный запрос для текущих и прошедших дат
        $matches_query = $wpdb->prepare("
            SELECT * 
            FROM wp_sport_matches_shedule 
            WHERE match_time BETWEEN %d AND %d
            ORDER BY match_time ASC
        ", $start_timestamp, $end_timestamp);
    }
    $matches = $wpdb->get_results($matches_query);

    // Запрос для получения лайв-матчей (только если дата — сегодняшняя)
    $prev_matches = [];
    if ($is_today) {
        $prev_date = date('Y-m-d', strtotime($date . ' -1 day'));
        $prev_start_timestamp = strtotime($prev_date . ' 00:00:00') - $offset_in_seconds;
        $prev_end_timestamp = strtotime($prev_date . ' 23:59:59') - $offset_in_seconds;

        $prev_matches_query = $wpdb->prepare("
            SELECT * 
            FROM wp_sport_matches_shedule 
            WHERE match_time BETWEEN %d AND %d 
              AND status_id IN (2, 3, 4, 5, 6, 7)
            ORDER BY match_time ASC
        ", $prev_start_timestamp, $prev_end_timestamp);
        $prev_matches = $wpdb->get_results($prev_matches_query);
    }

    // Объединяем текущие и предыдущие матчи
    $all_matches = array_merge($matches, $prev_matches);

    // Получение всех остальных данных: соревнования, страны, команды и категории
    $competitions_query = "
    SELECT id, name, name_ru, logo, country_id, category_id, slug 
    FROM wp_sport_competitions
    WHERE (country_id IS NOT NULL AND country_id != '') 
       OR (category_id IS NOT NULL AND category_id != '')
";
    $competitions = $wpdb->get_results($competitions_query);


    $countries_query = "SELECT id, name, name_ru, logo, slug FROM wp_sport_country_data";
    $countries = $wpdb->get_results($countries_query);

    $teams_query = "SELECT id, name, name_ru, logo FROM wp_soccer_teams";
    $teams = $wpdb->get_results($teams_query);

    $categories_query = "SELECT id, name, name_ru, slug FROM wp_sport_category_data";
    $categories = $wpdb->get_results($categories_query);

    // Создаём вспомогательные массивы для быстрого поиска данных
    $competitions_map = array();
    foreach ($competitions as $competition) {
        $competitions_map[$competition->id] = array(
            'id' => $competition->id,
            'name' => $competition->name_ru ? $competition->name_ru : $competition->name,
            'logo' => $competition->logo,
            'country_id' => $competition->country_id,
            'category_id' => $competition->category_id,
            'slug' => $competition->slug
        );
    }

    $countries_map = array();
    foreach ($countries as $country) {
        $countries_map[$country->id] = array(
            'id' => $country->id,
            'name' => $country->name_ru ? $country->name_ru : $country->name,
            'logo' => $country->logo ?? '/wp-content/themes/pm-news/sport/src/img/world.svg',
            'slug' => $country->slug,
        );
    }

    $teams_map = array();
    foreach ($teams as $team) {
        $teams_map[$team->id] = array(
            'name' => $team->name_ru ? $team->name_ru : $team->name,
            'logo' => $team->logo ?? '/wp-content/themes/pm-news/sport/src/img/football-team-placeholder.svg',
        );
    }

    $categories_map = array();
    foreach ($categories as $category) {
        $categories_map[$category->id] = array(
            'id' => $category->id,
            'name' => $category->name_ru ? $category->name_ru : $category->name,
            'slug' => $category->slug,
            'logo' => '/wp-content/themes/pm-news/sport/src/img/world.svg',
        );
    }

    // Группируем матчи по соревнованиям
    $response = array();
    foreach ($all_matches as $match) {
        $competition_id = $match->competition_id;

        // Проверяем, что у соревнования есть данные
        if (!isset($competitions_map[$competition_id])) {
            continue;
        }

        $competition = $competitions_map[$competition_id];
        $country_id = $competition['country_id'];
        $category_id = $competition['category_id'];
        $country = isset($countries_map[$country_id]) ? $countries_map[$country_id] : null;
        $category = isset($categories_map[$category_id]) ? $categories_map[$category_id] : null;

        // Добавляем соревнование в ответ, если оно ещё не было добавлено
        if (!isset($response[$competition_id])) {
            $response[$competition_id] = array(
                'competition' => $competition,
                'country' => $country,
                'category' => $category,
                'matches' => array(),
            );
        }

        // Получаем данные команд для home_team_id и away_team_id
        $home_team = isset($teams_map[$match->home_team_id]) ? $teams_map[$match->home_team_id] : null;
        $away_team = isset($teams_map[$match->away_team_id]) ? $teams_map[$match->away_team_id] : null;

        // Добавляем матч в соревнование
        // Добавляем матч в соревнование
        $response[$competition_id]['matches'][] = array(
            'id' => $match->id,
            'season_id' => $match->season_id,
            'home_team' => array(
                'id' => $match->home_team_id,
                'name' => $home_team ? $home_team['name'] : null,
                'logo' => $home_team ? $home_team['logo'] : null,
            ),
            'away_team' => array(
                'id' => $match->away_team_id,
                'name' => $away_team ? $away_team['name'] : null,
                'logo' => $away_team ? $away_team['logo'] : null,
            ),
            'status_id' => $match->status_id,
            'match_time' => $match->match_time,
            'venue_id' => $match->venue_id,
            'referee_id' => $match->referee_id,
            'neutral' => $match->neutral,
            'note' => $match->note,
            'home_scores' => $match->home_scores !== null ? json_decode($match->home_scores, true) : null,
            'away_scores' => $match->away_scores !== null ? json_decode($match->away_scores, true) : null,
            'home_position' => $match->home_position,
            'away_position' => $match->away_position,
            'coverage' => $match->coverage !== null ? json_decode($match->coverage, true) : null,
            'round' => $match->round !== null ? json_decode($match->round, true) : null,
            'related_id' => $match->related_id,
            'agg_score' => $match->agg_score !== null ? json_decode($match->agg_score, true) : null,
            'environment' => $match->environment !== null ? json_decode($match->environment, true) : null,
            'updated_at' => $match->updated_at,
            'kickoff_timestamp' => $match->kickoff_timestamp,
        );
    }

    // Преобразуем массив соревнований в упорядоченный список для ответа
    $ordered_response = array_values($response);

    // Сортировка массива соревнований по наличию страны и названию страны
    usort($ordered_response, function ($a, $b) use ($countries_map, $categories_map) {
        $country_a_exists = isset($countries_map[$a['competition']['country_id']]);
        $country_b_exists = isset($countries_map[$b['competition']['country_id']]);

        if ($country_a_exists && !$country_b_exists) {
            return -1;
        }
        if (!$country_a_exists && $country_b_exists) {
            return 1;
        }

        if ($country_a_exists && $country_b_exists) {
            // Оба имеют страны, сортируем по названию стран
            $country_a = $countries_map[$a['competition']['country_id']]['name'];
            $country_b = $countries_map[$b['competition']['country_id']]['name'];

            $cyrillic_a = preg_match('/[А-Яа-я]/u', $country_a);
            $cyrillic_b = preg_match('/[А-Яа-я]/u', $country_b);

            if ($cyrillic_a && !$cyrillic_b) {
                return -1;
            }
            if (!$cyrillic_a && $cyrillic_b) {
                return 1;
            }

            $comparison = strcasecmp($country_a, $country_b);
            if ($comparison !== 0) {
                return $comparison;
            }
        }

        if (!$country_a_exists && !$country_b_exists) {
            // У обоих нет стран, сортируем по категориям
            $category_a = isset($categories_map[$a['competition']['category_id']])
                ? $categories_map[$a['competition']['category_id']]['name']
                : '';
            $category_b = isset($categories_map[$b['competition']['category_id']])
                ? $categories_map[$b['competition']['category_id']]['name']
                : '';

            $cyrillic_a = preg_match('/[А-Яа-я]/u', $category_a);
            $cyrillic_b = preg_match('/[А-Яа-я]/u', $category_b);

            if ($cyrillic_a && !$cyrillic_b) {
                return -1;
            }
            if (!$cyrillic_a && $cyrillic_b) {
                return 1;
            }

            return strcasecmp($category_a, $category_b);
        }

        return 0;
    });


    // Возвращаем ответ
    return rest_ensure_response($ordered_response);
}
