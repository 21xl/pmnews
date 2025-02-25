<?php
// Регистрация нового API-эндпоинта
function register_matches_by_location_endpoint()
{
    register_rest_route('sports/v1', '/matches_by_location', array(
        'methods' => 'GET',
        'callback' => 'get_matches_by_location',
        'args' => array(
            'data' => array(
                'required' => true,
                'validate_callback' => function ($param) {
                    return is_string($param);
                },
            ),
            'status' => array(
                'required' => false,
                'validate_callback' => function ($param) {
                    return is_string($param);
                },
            ),
            'location' => array(
                'required' => false,
                'validate_callback' => function ($param) {
                    return is_string($param);
                },
            ),
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

add_action('rest_api_init', 'register_matches_by_location_endpoint');

function get_matches_by_location($request)
{
    global $wpdb;

    $data_id = $request->get_param('data'); // ID страны или категории
    $timezone_offset = intval($request->get_param('timezone_offset', 0)); // Смещение временной зоны (в минутах)
    $status_string = $request->get_param('status'); // Статус матча: scheduled или ended
    $location = $request->get_param('location');
    $date = sanitize_text_field($request->get_param('date'));
    $timezone_offset = intval($request->get_param('timezone_offset', 0));

    // error_log("Location parameter: " . ($location !== null ? $location : "null"));

    // Преобразуем статус из строки в числовое значение
    $status = $status_string === 'scheduled' ? 1 : ($status_string === 'ended' ? 8 : null);

    // Конвертируем смещение из минут в секунды
    $offset_in_seconds = $timezone_offset * 60;


    // Определяем временные границы для выбранной даты с учетом смещения
    $start_timestamp = strtotime($date . ' 00:00:00') + $offset_in_seconds;
    $end_timestamp = strtotime($date . ' 23:59:59') + $offset_in_seconds;

    // Проверка статуса на допустимость
    if (is_null($status)) {
        return new WP_Error(
            'invalid_status',
            'Invalid status. Allowed values are scheduled or ended.',
            array('status' => 400)
        );
    }

    // Конвертируем смещение из минут в секунды
    $offset_in_seconds = $timezone_offset * 60;

    // Проверяем, является ли переданный ID страной
    $country_query = $wpdb->prepare("SELECT * FROM wp_sport_country_data WHERE id = %s", $data_id);
    $country = $wpdb->get_row($country_query);
    $country = $country ? array(
        'id' => $country->id,
        'name' => $country->name_ru ? $country->name_ru : $country->name,
        'logo' => $country->logo ?? '/wp-content/themes/pm-news/sport/src/img/world.svg',
        'slug' => $country->slug,
    ) : null;

    // Проверяем, является ли переданный ID категорией, если страна не найдена
    $category_query = $wpdb->prepare("SELECT * FROM wp_sport_category_data WHERE id = %s", $data_id);
    $category_row = $wpdb->get_row($category_query);

    $category = $category_row ? array(
        'id' => $category_row->id,
        'name' => $category_row->name_ru ? $category_row->name_ru : $category_row->name,
        'slug' => $category_row->slug,
        'logo' => '/wp-content/themes/pm-news/sport/src/img/world.svg',
    ) : null;

    // Если ID не соответствует ни стране, ни категории
    if (!$country && !$category) {
        return new WP_Error(
            'invalid_data',
            'Указанный ID не соответствует ни стране, ни категории.',
            array('status' => 400)
        );
    }

    // Получаем соревнования в зависимости от типа location
    $competitions = array();
    if ($location === 'country') {
        $competitions = get_competitions_by_country($data_id);
    } elseif ($location === 'category') {
        $competitions = get_competitions_by_category($data_id);
        $country = null;
    }

    // Если соревнований нет, возвращаем пустой ответ
    if (empty($competitions)) {
        return [];
    }

    // Собираем ID соревнований
    $competition_ids = array_map(function ($competition) {
        return $competition->id;
    }, $competitions);

    // Получаем матчи, связанные с найденными соревнованиями и соответствующим статусом
    $matches_query = sprintf("
        SELECT id, season_id, competition_id, home_team_id, away_team_id, status_id, match_time, home_scores, away_scores, round, kickoff_timestamp
        FROM wp_sport_matches_shedule 
        WHERE competition_id IN (%s) AND match_time BETWEEN %d AND %d AND status_id = %d
        ORDER BY match_time ASC
    ", implode(',', array_map('intval', $competition_ids)), $start_timestamp, $end_timestamp, $status);

    $matches = $wpdb->get_results($matches_query);

    // Собираем ID всех команд, участвующих в матчах
    $team_ids = array_unique(array_merge(
        array_column($matches, 'home_team_id'),
        array_column($matches, 'away_team_id')
    ));

    // Получаем данные только для задействованных команд
    $teams_query = sprintf("
        SELECT id, name, name_ru, logo 
        FROM wp_soccer_teams 
        WHERE id IN ('%s')
    ", implode("','", array_map('esc_sql', $team_ids)));

    $teams = $wpdb->get_results($teams_query);

    // Создаём маппинг команд
    $teams_map = array();
    foreach ($teams as $team) {
        $teams_map[$team->id] = array(
            'name' => $team->name_ru ? $team->name_ru : $team->name,
            'logo' => (!empty($team->logo) && $team->logo !== '') ? $team->logo : '/wp-content/themes/pm-news/sport/src/img/football-team-placeholder.svg',
        );
    }

    // Создаём маппинг соревнований
    $competitions_map = array();
    foreach ($competitions as $competition) {
        $competitions_map[$competition->id] = array(
            'id' => $competition->id,
            'name' => $competition->name_ru ? $competition->name_ru : $competition->name,
            'logo' => (!empty($competition->logo) && $competition->logo !== '') ? $competition->logo : '/wp-content/themes/pm-news/sport/src/img/football-team-placeholder.svg',
            'country_id' => $competition->country_id,
            'category_id' => $competition->category_id,
            'slug' => $competition->slug
        );
    }

    // Группируем матчи по соревнованиям
    $response = array();
    foreach ($matches as $match) {
        $competition_id = $match->competition_id;

        // Проверяем, что у соревнования есть данные
        if (!isset($competitions_map[$competition_id])) {
            continue;
        }

        $competition = $competitions_map[$competition_id];

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
            'home_scores' => json_decode($match->home_scores, true),
            'away_scores' => json_decode($match->away_scores, true),
            'round' => json_decode($match->round, true),
            'kickoff_timestamp' => $match->kickoff_timestamp,
        );
    }

    // Преобразуем массив соревнований в упорядоченный список для ответа
    return rest_ensure_response(array_values($response));
}


function get_competitions_by_country($country_id)
{
    global $wpdb;

    $query = $wpdb->prepare("
        SELECT id, name, name_ru, logo, country_id, category_id, slug
        FROM wp_sport_competitions 
        WHERE country_id = %s
    ", $country_id);

    return $wpdb->get_results($query);
}

function get_competitions_by_category($category_id)
{
    global $wpdb;

    $query = $wpdb->prepare("
        SELECT id, name, name_ru, logo, country_id, category_id, slug
        FROM wp_sport_competitions 
        WHERE category_id = %s AND (country_id = '' OR country_id IS NULL)  
    ", $category_id);

    return $wpdb->get_results($query);
}
