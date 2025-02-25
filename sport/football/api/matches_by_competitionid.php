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
                    return is_string($param); // Проверка, что это строка
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

    // Получаем строковый competition_id
    $competition_id = sanitize_text_field($request->get_param('competition_id'));

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

    // Получаем данные категории и страны
    $country_query = $wpdb->prepare("
        SELECT id, name, name_ru, logo, slug FROM wp_sport_country_data
        WHERE id = %s
    ", $competition->country_id);
    $country = $wpdb->get_row($country_query);

    $category_query = $wpdb->prepare("
        SELECT id, name, name_ru, slug FROM wp_sport_category_data 
        WHERE id = %s
    ", $competition->category_id);
    $category = $wpdb->get_row($category_query);

    // Получаем список матчей
    $matches_query = $wpdb->prepare("
        SELECT * 
        FROM wp_sport_matches_shedule 
        WHERE competition_id = %s
        ORDER BY match_time ASC
    ", $competition_id);
    $matches = $wpdb->get_results($matches_query);

    // Проверка: если матчей нет, возвращаем пустой массив
    if (empty($matches)) {
        $matches_response = [];
    } else {
        // Получаем данные всех команд
        $teams_query = "SELECT id, name, name_ru, logo FROM wp_soccer_teams";
        $teams = $wpdb->get_results($teams_query);

        $teams_map = array();
        foreach ($teams as $team) {
            $teams_map[$team->id] = array(
                'name' => $team->name_ru ? $team->name_ru : $team->name,
                'logo' => $team->logo ?? '/wp-content/themes/pm-news/sport/src/img/football-team-placeholder.svg',
            );
        }

        // Формируем массив матчей
        // $matches_response = array_map(function ($match) use ($teams_map) {
        //     $home_team = isset($teams_map[$match->home_team_id]) ? $teams_map[$match->home_team_id] : null;
        //     $away_team = isset($teams_map[$match->away_team_id]) ? $teams_map[$match->away_team_id] : null;

        //     return array(
        //         'id' => $match->id,
        //         'season_id' => $match->season_id,
        //         'home_team' => array(
        //             'id' => $match->home_team_id,
        //             'name' => $home_team ? $home_team['name'] : null,
        //             'logo' => $home_team ? $home_team['logo'] : null,
        //         ),
        //         'away_team' => array(
        //             'id' => $match->away_team_id,
        //             'name' => $away_team ? $away_team['name'] : null,
        //             'logo' => $away_team ? $away_team['logo'] : null,
        //         ),
        //         'status_id' => $match->status_id,
        //         'match_time' => $match->match_time,
        //         'venue_id' => $match->venue_id,
        //         'referee_id' => $match->referee_id,
        //         'neutral' => $match->neutral,
        //         'note' => $match->note,
        //         'home_scores' => json_decode($match->home_scores, true),
        //         'away_scores' => json_decode($match->away_scores, true),
        //         'home_position' => $match->home_position,
        //         'away_position' => $match->away_position,
        //         'coverage' => json_decode($match->coverage, true),
        //         'round' => json_decode($match->round, true),
        //         'related_id' => $match->related_id,
        //         'agg_score' => json_decode($match->agg_score, true),
        //         'environment' => json_decode($match->environment, true),
        //         'updated_at' => $match->updated_at,
        //         'kickoff_timestamp' => $match->kickoff_timestamp,
        //     );
        // }, $matches);

        $matches_response = array_map(function ($match) use ($teams_map) {
            $home_team = isset($teams_map[$match->home_team_id]) ? $teams_map[$match->home_team_id] : null;
            $away_team = isset($teams_map[$match->away_team_id]) ? $teams_map[$match->away_team_id] : null;

            // Функция для безопасного декодирования JSON
            $safe_json_decode = function ($data) {
                // Если JSON некорректен, пробуем извлечь числа вручную
                if (is_string($data)) {
                    preg_match_all('/\d+/', $data, $matches);
                    return array_map('intval', $matches[0]);
                }
                $decoded = json_decode($data, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }

                return null; // Если данные не строка, вернуть null
            };

            return array(
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
                'home_scores' => $safe_json_decode($match->home_scores),
                'away_scores' => $safe_json_decode($match->away_scores),
                'home_position' => $match->home_position,
                'away_position' => $match->away_position,
                'coverage' => json_decode($match->coverage, true),
                'round' => json_decode($match->round, true),
                'related_id' => $match->related_id,
                'agg_score' => json_decode($match->agg_score, true),
                'environment' => json_decode($match->environment, true),
                'updated_at' => $match->updated_at,
                'kickoff_timestamp' => $match->kickoff_timestamp,
            );
        }, $matches);

    }

    // Формируем финальный ответ
    $response = array(
        'competition' => array(
            'id' => $competition->id,
            'name' => $competition->name_ru ?: $competition->name,
            'logo' => $competition->logo,
            'country_id' => $competition->country_id,
            'category_id' => $competition->category_id,
            'slug' => $competition->slug,
            'cur_round' => (int) $competition->cur_round,
        ),
        'matches' => $matches_response,
    );

    // Добавляем страну, если она существует
    if (isset($country)) {
        $response['country'] = array(
            'id' => $country->id,
            'name' => $country->name_ru ?: $country->name,
            'logo' => $country->logo ?: '/wp-content/themes/pm-news/sport/src/img/world.svg',
            'slug' => $country->slug,
        );
    }

    // Добавляем категорию, если она существует
    if (isset($category)) {
        $response['category'] = array(
            'id' => $category->id,
            'name' => $category->name_ru ?: $category->name,
            'slug' => $category->slug,
            'logo' => $category->logo ?? '/wp-content/themes/pm-news/sport/src/img/world.svg',
        );
    }

    return rest_ensure_response($response);
}
