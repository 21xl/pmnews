<?php
// Регистрация нового API-эндпоинта
function register_matches_by_competition_endpoint()
{
    register_rest_route('sports/v1', '/matches_by_competition_id', array(
        'methods' => 'GET',
        'callback' => 'get_matches_by_competition',
        'args' => array(
            'competition_id' => array(
                'required' => true,
                'validate_callback' => function ($param) {
                    return is_string($param) && !empty($param); // Проверка, что это непустая строка
                },
            ),
        ),
        'permission_callback' => '__return_true', // Доступ открыт для всех
    ));
}

add_action('rest_api_init', 'register_matches_by_competition_endpoint');

function get_matches_by_competition($request)
{
    global $wpdb;

    // Получаем и очищаем competition_id
    $competition_id = sanitize_text_field($request->get_param('competition_id'));

    // Проверяем, что competition_id не пустой после очистки
    if (empty($competition_id)) {
        return new WP_Error(
            'invalid_competition_id',
            __('Invalid competition ID provided.', 'sports'),
            array('status' => 400)
        );
    }

    // Получаем данные соревнования
    $competition_query = $wpdb->prepare("
        SELECT * 
        FROM wp_sport_competitions 
        WHERE id = %s
    ", $competition_id);
    $competition = $wpdb->get_row($competition_query);

    if (!$competition) {
        return new WP_Error(
            'competition_not_found',
            __('Competition not found for the specified ID.', 'sports'),
            array('status' => 404)
        );
    }

    // Получаем данные страны, если country_id существует
    $country = null;
    if (!empty($competition->country_id)) {
        $country_query = $wpdb->prepare("
            SELECT id, name, name_ru, logo, slug 
            FROM wp_sport_country_data
            WHERE id = %s
        ", $competition->country_id);
        $country = $wpdb->get_row($country_query);
    }

    // Получаем данные категории, если category_id существует
    $category = null;
    if (!empty($competition->category_id)) {
        $category_query = $wpdb->prepare("
            SELECT id, name, name_ru, slug, logo 
            FROM wp_sport_category_data 
            WHERE id = %s
        ", $competition->category_id);
        $category = $wpdb->get_row($category_query);
    }

    // Получаем список матчей
    $matches_query = $wpdb->prepare("
        SELECT * 
        FROM wp_sport_matches_shedule 
        WHERE competition_id = %s
        ORDER BY match_time ASC
    ", $competition_id);
    $matches = $wpdb->get_results($matches_query);

    // Формируем ответ для матчей
    if (empty($matches)) {
        $matches_response = [];
    } else {
        // Получаем данные всех команд
        $teams_query = "SELECT id, name, name_ru, logo FROM wp_soccer_teams";
        $teams = $wpdb->get_results($teams_query);

        $teams_map = array();
        foreach ($teams as $team) {
            $teams_map[$team->id] = array(
                'name' => $team->name ?? 'Unknown Team',
                'logo' => !empty($team->logo) ? $team->logo : '/wp-content/themes/pm-news/sport/src/img/football-team-placeholder.svg',
            );
        }

        $matches_response = array_map(function ($match) use ($teams_map) {
            $home_team = isset($teams_map[$match->home_team_id]) ? $teams_map[$match->home_team_id] : null;
            $away_team = isset($teams_map[$match->away_team_id]) ? $teams_map[$match->away_team_id] : null;

            // Функция для безопасного декодирования JSON
            $safe_json_decode = function ($data) {
                if (empty($data)) {
                    return null;
                }
                if (is_string($data)) {
                    $decoded = json_decode($data, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return $decoded;
                    }
                    // Если JSON некорректен, пробуем извлечь числа
                    preg_match_all('/\d+/', $data, $matches);
                    return !empty($matches[0]) ? array_map('intval', $matches[0]) : null;
                }
                return null;
            };

            return array(
                'id' => $match->id ?? null,
                'season_id' => $match->season_id ?? null,
                'home_team' => array(
                    'id' => $match->home_team_id ?? null,
                    'name' => $home_team ? $home_team['name'] : 'Unknown Team',
                    'logo' => $home_team ? $home_team['logo'] : '/wp-content/themes/pm-news/sport/src/img/football-team-placeholder.svg',
                ),
                'away_team' => array(
                    'id' => $match->away_team_id ?? null,
                    'name' => $away_team ? $away_team['name'] : 'Unknown Team',
                    'logo' => $away_team ? $away_team['logo'] : '/wp-content/themes/pm-news/sport/src/img/football-team-placeholder.svg',
                ),
                'status_id' => $match->status_id ?? null,
                'match_time' => $match->match_time ?? null,
                'venue_id' => $match->venue_id ?? null,
                'referee_id' => $match->referee_id ?? null,
                'neutral' => $match->neutral ?? null,
                'note' => $match->note ?? null,
                'home_scores' => $safe_json_decode($match->home_scores),
                'away_scores' => $safe_json_decode($match->away_scores),
                'home_position' => $match->home_position ?? null,
                'away_position' => $match->away_position ?? null,
                'coverage' => $safe_json_decode($match->coverage),
                'round' => $safe_json_decode($match->round),
                'related_id' => $match->related_id ?? null,
                'agg_score' => $safe_json_decode($match->agg_score),
                'environment' => $safe_json_decode($match->environment),
                'updated_at' => $match->updated_at ?? null,
                'kickoff_timestamp' => $match->kickoff_timestamp ?? null,
            );
        }, $matches);
    }

    // Формируем финальный ответ
    $response = array(
        'competition' => array(
            'id' => $competition->id ?? null,
            'name' => $competition->name ?? 'Unknown Competition',
            'logo' => $competition->logo ?? null,
            'country_id' => $competition->country_id ?? null,
            'category_id' => $competition->category_id ?? null,
            'slug' => $competition->slug ?? null,
            'cur_round' => isset($competition->cur_round) ? (int) $competition->cur_round : null,
        ),
        'matches' => $matches_response,
    );

    // Добавляем страну, если она существует
    if ($country) {
        $response['country'] = array(
            'id' => $country->id ?? null,
            'name' => $country->name ?? 'Unknown Country',
            'logo' => !empty($country->logo) ? $country->logo : '/wp-content/themes/pm-news/sport/src/img/world.svg',
            'slug' => $country->slug ?? null,
        );
    }

    // Добавляем категорию, если она существует
    if ($category) {
        $response['category'] = array(
            'id' => $category->id ?? null,
            'name' => $category->name ?? 'Unknown Category',
            'slug' => $category->slug ?? null,
            'logo' => !empty($category->logo) ? $category->logo : '/wp-content/themes/pm-news/sport/src/img/world.svg',
        );
    }

    return rest_ensure_response($response);
}