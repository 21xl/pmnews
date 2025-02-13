<?php

function register_favorite_matches_endpoint()
{
    register_rest_route('sports/v1', '/favorite_matches', array(
        'methods' => 'POST',
        'callback' => 'handle_favorite_matches_request',
        'args' => array(
            'date' => array(
                'required' => true,
                'validate_callback' => function ($param) {
                    return preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $param); // Проверка формата даты YYYY-MM-DD
                },
            ),
            'timezone_offset' => array(
                'required' => true,
                'validate_callback' => function ($param) {
                    return is_numeric($param); // Проверка, что это число
                },
            ),
            'favorites' => array(
                'required' => true,
                'validate_callback' => function ($param) {
                    return is_array($param); // Проверка, что это массив
                },
            ),
        ),
        'permission_callback' => '__return_true',
    ));
}

add_action('rest_api_init', 'register_favorite_matches_endpoint');

function handle_favorite_matches_request(WP_REST_Request $request)
{
    // Получаем параметры из запроса
    $date = $request->get_param('date');
    $timezone_offset = $request->get_param('timezone_offset');
    $favorites = $request->get_param('favorites');

    global $wpdb;

    $grouped_matches = [];

    foreach ($favorites as $favorite_group) {
        if (isset($favorite_group['key']) && isset($favorite_group['data'])) {
            $key = $favorite_group['key'];
            $match_ids = $favorite_group['data'];

            if ($key === 'fb') {

                // Получаем матчи из таблицы wp_sport_matches_shedule
                $placeholders = implode(',', array_fill(0, count($match_ids), '%s'));
                $matches = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM wp_sport_matches_shedule WHERE id IN ($placeholders)",
                        $match_ids
                    )
                );

                foreach ($matches as $match) {
                    $competition_id = $match->competition_id;

                    // Получаем данные о соревновании
                    $competition = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT * FROM wp_sport_competitions WHERE id = %s",
                            $competition_id
                        )
                    );

                    // Определяем локацию (страна или категория)
                    if (!empty($competition->country_id)) {
                        $location = $wpdb->get_row(
                            $wpdb->prepare(
                                "SELECT * FROM wp_sport_country_data WHERE id = %s",
                                $competition->country_id
                            )
                        );
                    } else {
                        $location = $wpdb->get_row(
                            $wpdb->prepare(
                                "SELECT * FROM wp_sport_category_data WHERE id = %s",
                                $competition->category_id
                            )
                        );
                    }

                    if ($location) { // Убедимся, что $location не равно null
                        if (empty($location->logo)) { // Проверка на null или пустую строку
                            $location->logo = '/wp-content/themes/pm-news/sport/src/img/world.svg';
                        }
                    }

                    // Получаем данные о командах
                    $home_team = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT * FROM wp_soccer_teams WHERE id = %s",
                            $match->home_team_id
                        )
                    );

                    $away_team = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT * FROM wp_soccer_teams WHERE id = %s",
                            $match->away_team_id
                        )
                    );

                    // Формируем структуру данных для группировки
                    if (!isset($grouped_matches[$competition_id])) {
                        $grouped_matches[$competition_id] = [
                            'competition' => $competition,
                            'location' => $location,
                            'matches' => []
                        ];
                    }

                    $match->home_team = $home_team;
                    $match->away_team = $away_team;

                    $grouped_matches[$competition_id]['matches'][] = $match;
                }
            }
        }
    }

    return rest_ensure_response(array_values($grouped_matches));
}
