<?php
// Регистрация нового API-эндпоинта
function register_matche_data_incidents()
{
    register_rest_route('sports/v1', '/matche_data', array(
        'methods' => 'GET',
        'callback' => 'get_matche_data_incidents',
        'args' => array(
            'match_id' => array(
                'required' => true,
                'validate_callback' => function ($param) {
                    return is_string($param); // Проверка, что это строка
                },
            ),
            'tab' => array(
                'required' => false, // Этот параметр необязательный
                'validate_callback' => function ($param) {
                    return is_string($param); // Проверка, что это строка
                },
            ),
        ),
        'permission_callback' => '__return_true', // Доступ открыт для всех
    ));
}

add_action('rest_api_init', 'register_matche_data_incidents');

function get_matche_data_incidents($request)
{
    global $wpdb;

    // Получаем параметры запроса
    $match_id = sanitize_text_field($request->get_param('match_id'));
    $tab = sanitize_text_field($request->get_param('tab')); // Получаем параметр 'tab'

    if ($tab === 'review') {
        // Получаем данные соревнования
        $match_query = $wpdb->prepare("
                SELECT * 
                FROM wp_sport_matches_shedule 
                WHERE id = %s
            ", $match_id);
        $match = $wpdb->get_row($match_query);

        if (!$match) {
            return new WP_Error(
                'match_not_found',
                __('Match not found for the specified ID.', 'sports'),
                array('status' => 404)
            );
        }

        if (!empty($match->incidents)) {
            $match->incidents = json_decode($match->incidents, true); // Конвертируем JSON в массив
            if (json_last_error() !== JSON_ERROR_NONE) {
                $match->incidents = null; // Если произошла ошибка при парсинге, устанавливаем null
            }
        }

        // Получаем данные команд
        $home_team_query = $wpdb->prepare("
                SELECT 
                    id, 
                    logo, 
                    IF(name_ru IS NULL OR name_ru = '', name, name_ru) AS name 
                FROM 
                    wp_soccer_teams 
                WHERE 
                    id = %s
            ", $match->home_team_id);

        $away_team_query = $wpdb->prepare("
                SELECT 
                    id, 
                    logo, 
                    IF(name_ru IS NULL OR name_ru = '', name, name_ru) AS name 
                FROM 
                    wp_soccer_teams 
                WHERE 
                    id = %s
            ", $match->away_team_id);

        $home_team = $wpdb->get_row($home_team_query);
        $away_team = $wpdb->get_row($away_team_query);


        if ($home_team) {
            $home_team->logo = !empty($home_team->logo) ? $home_team->logo : '/wp-content/themes/pm-news/sport/src/img/football-team-placeholder.svg';
        } else {
            $home_team = null;
        }

        // Проверяем и добавляем данные для away_team
        if ($away_team) {
            $away_team->logo = !empty($away_team->logo) ? $away_team->logo : '/wp-content/themes/pm-news/sport/src/img/football-team-placeholder.svg';
        } else {
            $away_team = null;
        }

        // Добавляем данные команд в ответ
        $match->home_team = $home_team;
        $match->away_team = $away_team;

        // Добавляем параметр tab в ответ (например, для отладки)
        $match->tab = $tab;

        return rest_ensure_response($match);

    } else if ($tab === 'squad') {
        // Обработка для 'squad'
        $lineup_query = $wpdb->prepare("
                SELECT coach_id, home_lineup, away_lineup 
                FROM wp_football_lineup 
                WHERE match_id = %s
            ", $match_id);
        $lineup = $wpdb->get_row($lineup_query);

        $match_query = $wpdb->prepare(
            "SELECT home_team_id, away_team_id 
            FROM wp_sport_matches_shedule 
            WHERE id = %s",
            $match_id
        );
        $match = $wpdb->get_row($match_query);

        if (!$match) {
            return new WP_Error(
                'match_not_found',
                __('Match not found for the specified ID.', 'sports'),
                array('status' => 404)
            );
        }

        if (!$lineup) {
            $home_players_query = $wpdb->prepare(
                "SELECT p.id, p.name, p.name_ru, p.age, p.country_id, p.team_id, 
                       c.logo AS country_logo, c.name AS country_name, 
                       t.logo AS team_logo, t.name AS team_name, t.name_ru AS team_name_ru
                FROM wp_football_players p
                LEFT JOIN wp_sport_country_data c ON p.country_id = c.id
                LEFT JOIN wp_soccer_teams t ON p.team_id = t.id
                WHERE p.team_id = %s",
                $match->home_team_id
            );

            $away_players_query = $wpdb->prepare(
                "SELECT p.id, p.name, p.name_ru, p.age, p.country_id, p.team_id, 
                       c.logo AS country_logo, c.name AS country_name, 
                       t.logo AS team_logo, t.name AS team_name, t.name_ru AS team_name_ru
                FROM wp_football_players p
                LEFT JOIN wp_sport_country_data c ON p.country_id = c.id
                LEFT JOIN wp_soccer_teams t ON p.team_id = t.id
                WHERE p.team_id = %s",
                $match->away_team_id
            );

            $home_players = $wpdb->get_results($home_players_query);
            $away_players = $wpdb->get_results($away_players_query);

            $players_data = [];

            foreach (array_merge($home_players, $away_players) as $player) {
                $team_name = !empty($player->team_name_ru) ? $player->team_name_ru : $player->team_name;

                $players_data[] = [
                    'id' => $player->id,
                    'name' => !empty($player->name_ru) ? $player->name_ru : $player->name,
                    'age' => $player->age,
                    'country_logo' => $player->country_logo ?? null,
                    'country_name' => $player->country_name,
                    'team' => [
                        'logo' => $player->team_logo,
                        'name' => $team_name,
                    ],
                    'logo' => (!empty($player->logo) && $player->logo !== "")
                        ? $player->logo
                        : '/wp-content/themes/pm-news/sport/src/img/player.svg',
                ];
            }

            return rest_ensure_response(['squad' => $players_data, 'tab' => $tab]);
        }

        $home_lineup = json_decode($lineup->home_lineup, true);
        $away_lineup = json_decode($lineup->away_lineup, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error(
                'invalid_json',
                __('Invalid JSON in lineup data.', 'sports'),
                array('status' => 500)
            );
        }

        $player_ids = array_merge(
            array_column($home_lineup, 'id'),
            array_column($away_lineup, 'id')
        );

        $player_ids_placeholder = implode(',', array_fill(0, count($player_ids), '%s'));

        // Получаем игроков
        $players_query = $wpdb->prepare("
                SELECT p.id, p.name, p.name_ru, p.age, p.country_id, p.team_id, 
                    c.logo AS country_logo, c.name AS country_name, 
                    t.logo AS team_logo, t.name AS team_name, t.name_ru AS team_name_ru
                FROM wp_football_players p
                LEFT JOIN wp_sport_country_data c ON p.country_id = c.id
                LEFT JOIN wp_soccer_teams t ON p.team_id = t.id
                WHERE p.id IN ($player_ids_placeholder)
            ", $player_ids);
        $players = $wpdb->get_results($players_query);

        $players_data = [];
        foreach ($players as $player) {
            $team_name = !empty($player->team_name_ru) ? $player->team_name_ru : $player->team_name;

            $players_data[$player->id] = [
                'id' => $player->id,
                'name' => !empty($player->name_ru) ? $player->name_ru : $player->name,
                'age' => $player->age,
                'country_logo' => $player->country_logo ?? null,
                'country_name' => $player->country_name,
                'team' => [
                    'logo' => $player->team_logo,
                    'name' => $team_name,
                ],
                'logo' => (!empty($player->logo) && $player->logo !== "")
                    ? $player->logo
                    : '/wp-content/themes/pm-news/sport/src/img/player.svg',
            ];
        }

        // Формируем массив squad
        $squad = [];
        foreach ($home_lineup as $player) {
            $player_id = $player['id'];
            $player_data = $players_data[$player_id] ?? [];

            if (empty($player_data['name']) && !empty($player['name'])) {
                $player_data['name'] = $player['name'];
            }
            if (empty($player_data['age'])) {
                $player_data['age'] = '-';
            }
            if (empty($player_data['country_logo'])) {
                $player_data['country_logo'] = null;
            }

            $squad[] = array_merge($player_data, [
                'id' => $player_id,
                'pos' => 1,
                'logo' => (!empty($player['logo']) && $player['logo'] !== "")
                    ? $player['logo']
                    : '/wp-content/themes/pm-news/sport/src/img/player.svg',
                'captain' => $player['captain'],
                'shirt_number' => $player['shirt_number'],
                'position' => get_full_position($player['position']),
                'first' => $player['first']
            ]);
        }


        foreach ($away_lineup as $player) {
            $player_id = $player['id'];

            $player_data = $players_data[$player_id] ?? [];

            if (empty($player_data['name']) && !empty($player['name'])) {
                $player_data['name'] = $player['name'];
            }
            if (empty($player_data['age'])) {
                $player_data['age'] = '-';
            }
            if (empty($player_data['country_logo'])) {
                $player_data['country_logo'] = '/wp-content/themes/pm-news/sport/src/img/world.svg';
            }
            $squad[] = array_merge($players_data[$player_id] ?? [], [
                'id' => $player_id,
                'pos' => 2,
                'logo' => (!empty($player['logo']) && $player['logo'] !== "")
                    ? $player['logo']
                    : '/wp-content/themes/pm-news/sport/src/img/player.svg',
                'captain' => $player['captain'],
                'shirt_number' => $player['shirt_number'],
                'position' => get_full_position($player['position']),
                'first' => $player['first']
            ]);
        }

        // Разбор JSON строки coach_id
        $coach_ids = json_decode($lineup->coach_id, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error(
                'invalid_json',
                __('Invalid JSON in coach data.', 'sports'),
                array('status' => 500)
            );
        }

        // Получение тренеров
        $unique_coach_ids = array_values($coach_ids); // Собираем все уникальные ID
        $coach_ids_placeholder = implode(',', array_fill(0, count($unique_coach_ids), '%s'));

        $coaches_query = $wpdb->prepare("
                SELECT c.id, c.name, c.name_ru, c.age, c.logo, c.country_id, 
                    d.logo AS country_logo, d.name AS country_name 
                FROM wp_football_coaches c
                LEFT JOIN wp_sport_country_data d ON c.country_id = d.id
                WHERE c.id IN ($coach_ids_placeholder)
            ", $unique_coach_ids);
        $coaches = $wpdb->get_results($coaches_query);

        // Преобразование данных тренеров в массив
        $coaches_data = [];
        foreach ($coaches as $coach) {
            $coaches_data[$coach->id] = [
                'id' => $coach->id,
                'name' => !empty($coach->name_ru) ? $coach->name_ru : $coach->name,
                'age' => $coach->age,
                'country_logo' => $coach->country_logo ?? '/wp-content/themes/pm-news/sport/src/img/world.svg',
                'country_name' => $coach->country_name,
                'logo' => (!empty($coach->logo) && $coach->logo !== "")
                    ? $coach->logo
                    : '/wp-content/themes/pm-news/sport/src/img/player.svg',

            ];
        }

        // Формируем объект с данными тренеров
        $formatted_coaches = [
            'home' => isset($coaches_data[$coach_ids['home']]) ? $coaches_data[$coach_ids['home']] : null,
            'away' => isset($coaches_data[$coach_ids['away']]) ? $coaches_data[$coach_ids['away']] : null,
        ];

        return rest_ensure_response([
            'squad' => $squad,
            'coaches' => $formatted_coaches,
            'tab' => $tab
        ]);
    } else if ($tab === 'h2h') {
        // Запрос на получение всех матчей
        $match_query = $wpdb->prepare("
            SELECT home_team_id, away_team_id 
            FROM wp_sport_matches_shedule 
            WHERE id = %s
        ", $match_id);
        $match = $wpdb->get_row($match_query);

        if (!$match) {
            return new WP_Error(
                'match_not_found',
                __('Match not found for the specified ID.', 'sports'),
                array('status' => 404)
            );
        }

        $home_team_id = $match->home_team_id;
        $away_team_id = $match->away_team_id;

        // Запрос на получение всех матчей для обеих команд
        $matches_query = $wpdb->prepare("
            SELECT 
                id, 
                season_id, 
                competition_id, 
                home_team_id, 
                away_team_id, 
                status_id, 
                match_time, 
                home_scores, 
                away_scores, 
                kickoff_timestamp, 
                slug  
            FROM wp_sport_matches_shedule 
            WHERE status_id = '8' 
            AND (home_team_id = %s OR away_team_id = %s 
                OR home_team_id = %s OR away_team_id = %s)
            ORDER BY match_time DESC
        ", $home_team_id, $home_team_id, $away_team_id, $away_team_id);

        $matches = $wpdb->get_results($matches_query);

        if (empty($matches)) {
            $response = [
                'code' => 404,
                'tab' => $tab,
            ];

            return rest_ensure_response($response);
        }

        // Собираем уникальные ID команд из всех матчей
        $team_ids = [];
        foreach ($matches as $match) {
            $team_ids[] = $match->home_team_id;
            $team_ids[] = $match->away_team_id;
        }

        // Получаем уникальные ID команд
        $team_ids = array_unique($team_ids);

        // Получаем информацию о командах
        $teams_query = $wpdb->prepare("
            SELECT id, name_ru, name, logo 
            FROM wp_soccer_teams 
            WHERE id IN (" . implode(',', array_fill(0, count($team_ids), '%s')) . ")
        ", ...$team_ids);

        $teams = $wpdb->get_results($teams_query);
        $team_info = [];
        foreach ($teams as $team) {
            $team_info[$team->id] = [
                'name_ru' => $team->name_ru,
                'name' => $team->name,
                'logo' => $team->logo ?? 'wp-content/themes/pm-news/sport/src/img/football-team-placeholder.svg',
            ];
        }

        // Собираем данные о матчах, добавляя информацию о командах
        $match_data = [];
        foreach ($matches as $match) {
            // Распарсить JSON-строки в массивы
            $home_scores = json_decode($match->home_scores, true);
            $away_scores = json_decode($match->away_scores, true);

            $match_data[] = [
                "id" => $match->id,
                "season_id" => $match->season_id,
                "competition_id" => $match->competition_id,
                "home_team_id" => $match->home_team_id,
                "home_team" => isset($team_info[$match->home_team_id]) ? $team_info[$match->home_team_id] : null, // Информация о домашней команде
                "away_team_id" => $match->away_team_id,
                "away_team" => isset($team_info[$match->away_team_id]) ? $team_info[$match->away_team_id] : null, // Информация о выездной команде
                "status_id" => $match->status_id,
                "match_time" => $match->match_time,
                "home_scores" => $home_scores,
                "away_scores" => $away_scores,
                "kickoff_timestamp" => $match->kickoff_timestamp,
                "slug" => $match->slug,
            ];
        }

        $response = [
            'code' => 200,
            'matches' => isset($match_data) ? $match_data : null,
            'tab' => $tab,
        ];

        return rest_ensure_response($response);
    } else if ($tab === 'statistics') {
        global $wpdb;

        // Запрос на получение статистики для матча по match_id
        $statistics_query = $wpdb->prepare("
            SELECT match_id, full_time, first_half, sec_half, first_overtime, sec_overtime 
            FROM wp_football_team_stats_half 
            WHERE match_id = %s
        ", $match_id);

        $statistics = $wpdb->get_row($statistics_query);

        if (!$statistics) {
            $response = [
                'match_id' => $statistics->match_id,
                'statistics' => Null,
                'tab' => $tab,
            ];

            return rest_ensure_response($response);
        }

        // Массив с описаниями для каждого типа статистики
        $statistics_types = [
            25 => ['name' => 'Ball possession', 'data_type' => 'percentage'],
            1 => ['name' => 'Goal', 'data_type' => 'count'],
            2 => ['name' => 'Corner', 'data_type' => 'count'],
            3 => ['name' => 'Yellow card', 'data_type' => 'count'],
            4 => ['name' => 'Red card', 'data_type' => 'count'],
            5 => ['name' => 'Offside', 'data_type' => 'count'],
            6 => ['name' => 'Free kick', 'data_type' => 'count'],
            7 => ['name' => 'Goal kick', 'data_type' => 'count'],
            8 => ['name' => 'Penalty', 'data_type' => 'count'],
            9 => ['name' => 'Substitution', 'data_type' => 'count'],
            15 => ['name' => 'Card upgrade confirmed', 'data_type' => 'count'],
            16 => ['name' => 'Penalty missed', 'data_type' => 'count'],
            17 => ['name' => 'Own goal', 'data_type' => 'count'],
            21 => ['name' => 'Shots on target', 'data_type' => 'count'],
            22 => ['name' => 'Shots off target', 'data_type' => 'count'],
            23 => ['name' => 'Attacks', 'data_type' => 'count'],
            24 => ['name' => 'Dangerous Attack', 'data_type' => 'count'],
            32 => ['name' => 'Throw in', 'data_type' => 'count'],
            33 => ['name' => 'Dribble', 'data_type' => 'count'],
            34 => ['name' => 'Dribble success', 'data_type' => 'count'],
            36 => ['name' => 'Clearances', 'data_type' => 'count'],
            37 => ['name' => 'Blocked shots', 'data_type' => 'count'],
            38 => ['name' => 'Intercept', 'data_type' => 'count'],
            39 => ['name' => 'Tackles', 'data_type' => 'count'],
            40 => ['name' => 'Pass', 'data_type' => 'count'],
            41 => ['name' => 'Pass success', 'data_type' => 'rate'],
            42 => ['name' => 'Key passes', 'data_type' => 'count'],
            43 => ['name' => 'Cross', 'data_type' => 'count'],
            44 => ['name' => 'Cross success', 'data_type' => 'rate'],
            45 => ['name' => 'Long pass', 'data_type' => 'count'],
            46 => ['name' => 'Long pass success', 'data_type' => 'rate'],
            48 => ['name' => '1 to 1 fight success', 'data_type' => 'rate'],
            49 => ['name' => 'The pass is broken', 'data_type' => 'count'],
            52 => ['name' => 'Save', 'data_type' => 'count'],
            53 => ['name' => 'Punches', 'data_type' => 'count'],
            54 => ['name' => 'Goalkeeper strikes', 'data_type' => 'count'],
            55 => ['name' => 'Goalkeeper strikes success', 'data_type' => 'rate'],
            56 => ['name' => 'High altitude attack', 'data_type' => 'count'],
            61 => ['name' => '1 on 1 fight failed', 'data_type' => 'count'],
            63 => ['name' => 'Free kick', 'data_type' => 'count'],
            65 => ['name' => 'Free kick goal', 'data_type' => 'count'],
            69 => ['name' => 'Hit woodwork', 'data_type' => 'count'],
            70 => ['name' => 'Fast break', 'data_type' => 'count'],
            71 => ['name' => 'Fast break shot', 'data_type' => 'count'],
            72 => ['name' => 'Fast break goal', 'data_type' => 'count'],
            78 => ['name' => 'Lost the ball', 'data_type' => 'count'],
            83 => ['name' => 'Shots', 'data_type' => 'count']
        ];

        // Преобразуем JSON статистику в ассоциативные массивы
        $statistics_data = [
            'full_time' => $statistics->full_time ? decode_stats(json_decode($statistics->full_time, true), $statistics_types) : Null,
            'first_half' => $statistics->first_half ? decode_stats(json_decode($statistics->first_half, true), $statistics_types) : Null,
            'sec_half' => $statistics->sec_half ? decode_stats(json_decode($statistics->sec_half, true), $statistics_types) : Null,
            'first_overtime' => $statistics->first_overtime ? decode_stats(json_decode($statistics->first_overtime, true), $statistics_types) : Null,
            'sec_overtime' => $statistics->sec_overtime ? decode_stats(json_decode($statistics->sec_overtime, true), $statistics_types) : Null,
        ];
        // Функция для декодирования статистики по ключам


        // Формирование ответа
        $response = [
            'match_id' => $statistics->match_id,
            'statistics' => $statistics_data,
            'tab' => $tab,
        ];

        return rest_ensure_response($response);
    } else if ($tab === 'odds') {
        $odds_query = $wpdb->prepare("
            SELECT * 
            FROM wp_football_odds 
            WHERE match_id = %s
        ", $match_id);
        $odds = $wpdb->get_results($odds_query);

        if (!$odds) {
            return new WP_Error(
                'odds_not_found',
                __('Odds not found for the specified ID.', 'sports'),
                array('status' => 404)
            );
        }

        // Функция для обработки коэффициентов
        function processOdds($odds)
        {
            $processedOdds = [];

            foreach ($odds as $odd) {
                $processedOdd = new stdClass(); // Создаем объект для хранения результата
                $processedOdd->id = $odd->id; // Копируем ID
                $processedOdd->match_id = $odd->match_id;
                $processedOdd->company_id = $odd->company_id;

                if (!empty($odd->eu)) {
                    $euData = json_decode($odd->eu, true); // Преобразуем JSON в массив
                    $processedEu = [];

                    if (count($euData) === 1) {
                        // Если в массиве только одно значение, считаем, что изменений нет
                        $values = $euData[0];
                        if (isset($values[2], $values[3], $values[4])) {
                            $processedEu = [
                                [$values[2], 'up'], // Win
                                [$values[3], 'up'], // Draw
                                [$values[4], 'up'], // Loss
                            ];
                        }
                    } elseif (count($euData) > 1) {
                        // Если есть два значения, сравниваем
                        $newValues = $euData[0]; // Новое значение
                        $oldValues = $euData[1]; // Старое значение

                        if (isset($newValues[2], $newValues[3], $newValues[4], $oldValues[2], $oldValues[3], $oldValues[4])) {
                            $processedEu = [
                                [$newValues[2], $newValues[2] > $oldValues[2] ? 'up' : ($newValues[2] < $oldValues[2] ? 'down' : 'up')], // Win
                                [$newValues[3], $newValues[3] > $oldValues[3] ? 'up' : ($newValues[3] < $oldValues[3] ? 'down' : 'up')], // Draw
                                [$newValues[4], $newValues[4] > $oldValues[4] ? 'up' : ($newValues[4] < $oldValues[4] ? 'down' : 'up')], // Loss
                            ];
                        }
                    }

                    $processedOdd->eu = $processedEu;
                }

                $processedOdds[] = $processedOdd; // Добавляем обработанный объект в итоговый массив
            }

            return $processedOdds;
        }

        // Обработка коэффициентов
        $processedOdds = processOdds($odds);

        // Логирование обработанных коэффициентов


        return rest_ensure_response(['odds' => $processedOdds]);
    }


}


function get_full_position($position)
{
    switch ($position) {
        case 'F':
            return 'Forward';
        case 'M':
            return 'Midfielder';
        case 'D':
            return 'Defender';
        case 'G':
            return 'Goalkeeper';
        default:
            return 'Unknown';
    }
}


function processLatestValue($values)
{
    if (empty($values)) {
        return null; // Если нет значений, возвращаем null
    }

    if (count($values) === 1) {
        return [$values[0], 'up']; // Если одно значение — "up"
    }

    $latest_value = end($values); // Последнее значение
    $prev_value = prev($values); // Предыдущее значение

    if ($latest_value > $prev_value) {
        return [$latest_value, 'up'];
    } else {
        return [$latest_value, 'down'];
    }
}

function decode_stats($stats, $types)
{
    $decoded = [];
    foreach ($types as $key => $typeInfo) {
        if (isset($stats[$key])) {
            $decoded[] = [
                'type' => $typeInfo['name'],
                'data_type' => $typeInfo['data_type'],
                'home_val' => $stats[$key][0], // Value for home team
                'away_val' => $stats[$key][1], // Value for away team
            ];
        }
    }
    return $decoded;
}