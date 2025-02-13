<?php

function register_table_endpoint()
{
    register_rest_route('sports/v1', '/competition_standings_data', array(
        'methods' => 'GET',
        'callback' => 'get_competition_standings_data',
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
add_action('rest_api_init', 'register_table_endpoint');

function get_competition_standings_data($request)
{
    global $wpdb;

    $competition_id = sanitize_text_field($request->get_param('competition_id'));

    // Получение данных о соревновании
    $competition = $wpdb->get_row($wpdb->prepare("
        SELECT * 
        FROM wp_sport_competitions 
        WHERE id = %s
    ", $competition_id));

    if (!$competition) {
        return rest_ensure_response(array('error' => 'Competition not found'));
    }

    // Получение матчей для текущего сезона соревнования
    $matches = $wpdb->get_results($wpdb->prepare("
        SELECT * 
        FROM wp_sport_matches_shedule 
        WHERE competition_id = %s AND season_id = %s
        ORDER BY match_time ASC
    ", $competition_id, $competition->cur_season_id));

    $brackets = null;
    // Получение данных стендинга
    $brackets = get_brackets_with_relations($competition->cur_season_id);

    $standings = getCompetitionStandingsData($competition_id, $competition->cur_season_id, $matches);

    // Формирование ответа
    $response = array(
        'competition' => $competition,
        // 'teams' => $teams,
        'brackets' => $brackets,
        'standings' => $standings,
    );

    return rest_ensure_response($response);
}

function getCompetitionStandingsData($competition_id, $season_id, $matches)
{
    global $wpdb;

    if (empty($competition_id) || empty($season_id)) {
        return null;
    }

    // Таблицы
    $tableTable = $wpdb->prefix . 'sport_football_tables';
    $tableRow = $wpdb->prefix . 'football_standings_rows';
    $tablePromotion = $wpdb->prefix . 'sport_football_promotions';

    // Получаем таблицы
    $tables = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $tableTable WHERE season_id = %s ORDER BY `group` ASC",
            $season_id
        )
    );

    if (empty($tables)) {
        return null;
    }

    // Формируем данные
    $result = [];
    foreach ($tables as $table) {
        // Получаем строки для таблицы
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $tableRow WHERE table_id = %s ORDER BY position ASC",
                $table->id
            )
        );

        $rowsData = [];
        $uniquePromotions = [];

        foreach ($rows as $row) {
            // Получаем данные о повышении/понижении
            $promotion = null;
            if (!empty($row->promotion_id)) {
                $promotion = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM $tablePromotion WHERE id = %s",
                        $row->promotion_id
                    )
                );

                // Сохраняем уникальные промоушены
                if ($promotion && !isset($uniquePromotions[$promotion->id])) {
                    $uniquePromotions[$promotion->id] = [
                        'id' => $promotion->id,
                        'name' => $promotion->name,
                        'color' => $promotion->color,
                    ];
                }
            }

            // Собираем матчи
            $matchesData = [];
            foreach ($matches as $match) {
                // Фильтруем только те матчи, у которых статус равен 8
                $round = safeJsonDecode($match->round);

                if (
                    $match->status_id == 8 &&
                    ($match->home_team_id == $row->team_id || $match->away_team_id == $row->team_id) && ($round->stage_id == $table->stage_id)
                ) {
                    $isHome = $match->home_team_id == $row->team_id;
                    $homeScore = json_decode($match->home_scores, true)[0] ?? 0;
                    $awayScore = json_decode($match->away_scores, true)[0] ?? 0;

                    // Определяем результат для этой команды
                    $resultType = 'draw';
                    if (($isHome && $homeScore > $awayScore) || (!$isHome && $awayScore > $homeScore)) {
                        $resultType = 'win';
                    } elseif (($isHome && $homeScore < $awayScore) || (!$isHome && $awayScore < $homeScore)) {
                        $resultType = 'loss';
                    }

                    // Добавляем матч в данные
                    $matchesData[] = [
                        'match_id' => $match->id,
                        'home_team_id' => $match->home_team_id,
                        'away_team_id' => $match->away_team_id,
                        'home_score' => $homeScore,
                        'away_score' => $awayScore,
                        'match_time' => $match->match_time,
                        'venue_id' => $match->venue_id,
                        'result' => $resultType,
                        'is_home' => $isHome,
                        'stage_id' => $round->stage_id
                    ];
                }
            }

            $team_data_result = getTeamData($row->team_id);
            if (!$team_data_result['error'] && !empty($team_data_result['data'])) {
                $team_data = $team_data_result['data'];
                $team_name = !empty($team_data['name_ru']) ? $team_data['name_ru'] : $team_data['name'];
                $team_slug = !empty($team_data['slug']) ? $team_data['slug'] : NULL;
                $team_logo = !empty($team_data['logo']) ? $team_data['logo'] : '/wp-content/themes/pm-news/sport/src/img/football-team-placeholder.svg';

            } else {
                $team_name = 'Неизвестная команда'; // Значение по умолчанию, если данных нет
            }

            // Добавляем строку
            $rowsData[] = [
                'id' => $row->id,
                'team_id' => $row->team_id,
                'team_name' => $team_name,
                'team_logo' => $team_logo,
                'team_slug' => $team_slug,
                'points' => $row->points,
                'position' => $row->position,
                'promotion' => $promotion ? [
                    'id' => $promotion->id,
                    'name' => $promotion->name,
                    'color' => $promotion->color
                ] : null,
                'stats' => [
                    'total' => $row->total,
                    'won' => $row->won,
                    'draw' => $row->draw,
                    'loss' => $row->loss,
                    'goals' => $row->goals,
                    'goals_against' => $row->goals_against,
                    'goal_diff' => $row->goal_diff
                ],
                'home_stats' => [
                    'points' => $row->home_points,
                    'position' => $row->home_position,
                    'total' => $row->home_total,
                    'won' => $row->home_won,
                    'draw' => $row->home_draw,
                    'loss' => $row->home_loss,
                    'goals' => $row->home_goals,
                    'goals_against' => $row->home_goals_against,
                    'goal_diff' => $row->home_goal_diff
                ],
                'away_stats' => [
                    'points' => $row->away_points,
                    'position' => $row->away_position,
                    'total' => $row->away_total,
                    'won' => $row->away_won,
                    'draw' => $row->away_draw,
                    'loss' => $row->away_loss,
                    'goals' => $row->away_goals,
                    'goals_against' => $row->away_goals_against,
                    'goal_diff' => $row->away_goal_diff
                ],
                'matches' => array_slice($matchesData, -5)
            ];
        }

        $result[] = [
            'table_id' => $table->id,
            'conference' => $table->conference,
            'group' => $table->group,
            'stage_id' => $table->stage_id,
            'rows' => $rowsData,
            'promotions' => array_values($uniquePromotions) // Добавляем уникальные промоушены
        ];
    }


    return groupAndSortStandings($result, $season_id);

}

function getTeamData($team_id)
{
    global $wpdb;

    // Проверяем, что передан идентификатор команды
    if (empty($team_id)) {
        return [
            'error' => true,
            'message' => 'Не указан идентификатор команды.'
        ];
    }

    // Название таблицы
    $tableTeams = $wpdb->prefix . 'soccer_teams';

    // Выполняем запрос к таблице
    $team = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $tableTeams WHERE id = %s",
            $team_id
        ),
        ARRAY_A // Возвращаем массив в виде ассоциативного массива
    );

    // Проверяем, найдена ли команда
    if (!$team) {
        return [
            'error' => true,
            'message' => 'Данные команды не найдены.'
        ];
    }

    return [
        'error' => false,
        'data' => $team
    ];
}

function groupAndSortStandings($result, $season_id)
{
    global $wpdb;

    // Извлечение данных из таблицы wp_football_stages
    $stages = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}football_stages WHERE season_id = %s",
            $season_id
        ),
        OBJECT_K // Вернуть результат как ассоциативный массив с ключами stage_id
    );

    if (empty($stages)) {
        return [
            'error' => true,
            'message' => 'Данные для указанных стадий не найдены.'
        ];
    }

    // Сгруппировать результат по stage_id
    $grouped = [];
    foreach ($result as $item) {
        $stage_id = $item['stage_id'];
        if (!isset($grouped[$stage_id])) {
            $grouped[$stage_id] = [
                'stage' => isset($stages[$stage_id]) ? $stages[$stage_id] : null,
                'tables' => []
            ];
        }
        $grouped[$stage_id]['tables'][] = $item;
    }

    // Сортировка групп по `order` из таблицы wp_football_stages
    uasort($grouped, function ($a, $b) {
        $orderA = $a['stage']->order ?? PHP_INT_MAX; // Если order отсутствует, сортировать в конец
        $orderB = $b['stage']->order ?? PHP_INT_MAX;
        return $orderA <=> $orderB;
    });

    return array_values($grouped); // Сбросить ключи для упрощения структуры
}

// function get_brackets_with_relations($season_id)
// {
//     global $wpdb;

//     // Получить все брекеты для указанного сезона
//     $brackets = $wpdb->get_results(
//         $wpdb->prepare(
//             "SELECT * FROM wp_football_brackets WHERE season_id = %s",
//             $season_id
//         )
//     );

//     // Итерация по брекетам для получения связанных данных
//     foreach ($brackets as &$bracket) {
//         // Получение групп для брекета
//         $bracket->groups = $wpdb->get_results(
//             $wpdb->prepare(
//                 "SELECT * FROM wp_football_bracket_groups WHERE bracket_id = %s",
//                 $bracket->id
//             )
//         );

//         // Итерация по группам для получения раундов
//         foreach ($bracket->groups as &$group) {
//             $group->rounds = $wpdb->get_results(
//                 $wpdb->prepare(
//                     "SELECT * FROM wp_football_bracket_rounds WHERE group_id = %s ORDER BY number ASC",
//                     $group->id
//                 )
//             );

//             // Итерация по раундам для получения матчей
//             foreach ($group->rounds as &$round) {
//                 $round->matchups = $wpdb->get_results(
//                     $wpdb->prepare(
//                         "SELECT * FROM wp_football_match_ups WHERE round_id = %s",
//                         $round->id
//                     )
//                 );

//                 foreach ($round->matchups as &$matchup) {
//                     // Получаем информацию о домашней команде
//                     $home_team = $wpdb->get_row(
//                         $wpdb->prepare(
//                             "SELECT name, name_ru, logo FROM wp_soccer_teams WHERE id = %s",
//                             $matchup->home_team_id
//                         )
//                     );

//                     // Получаем информацию о выездной команде
//                     $away_team = $wpdb->get_row(
//                         $wpdb->prepare(
//                             "SELECT name, name_ru, logo FROM wp_soccer_teams WHERE id = %s",
//                             $matchup->away_team_id
//                         )
//                     );

//                     // Формируем объект для домашней команды
//                     $matchup->home_team = [
//                         'name' => $home_team->name_ru ?: $home_team->name,  // Если есть name_ru, используем его
//                         'logo' => $home_team->logo ?: '/wp-content/themes/pm-news/sport/src/img/football-team-placeholder.svg'  // Если нет лого, ставим дефолтное
//                     ];

//                     // Формируем объект для выездной команды
//                     $matchup->away_team = [
//                         'name' => $away_team->name_ru ?: $away_team->name,  // Если есть name_ru, используем его
//                         'logo' => $away_team->logo ?: '/wp-content/themes/pm-news/sport/src/img/football-team-placeholder.svg'  // Если нет лого, ставим дефолтное
//                     ];
//                 }
//             }
//         }
//     }

//     // Также получение раундов без группы для брекета
//     $bracket->rounds = $wpdb->get_results(
//         $wpdb->prepare(
//             "SELECT * FROM wp_football_bracket_rounds WHERE bracket_id = %s AND group_id IS NULL",
//             $bracket->id
//         )
//     );

//     // Итерация по таким раундам для получения матчей
//     foreach ($bracket->rounds as &$round) {
//         $round->matchups = $wpdb->get_results(
//             $wpdb->prepare(
//                 "SELECT * FROM wp_football_match_ups WHERE round_id = %s",
//                 $round->id
//             )
//         );


//     }

//     return $brackets;
// }

function get_brackets_with_relations($season_id)
{
    global $wpdb;

    // Получить все брекеты для указанного сезона
    $brackets = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM wp_football_brackets WHERE season_id = %s",
            $season_id
        )
    );

    if (empty($brackets)) {
        return [];
    }

    $bracket_ids = array_map(fn($b) => $b->id, $brackets);

    // Получить все группы для этих брекетов
    $groups = [];
    if (!empty($bracket_ids)) {
        $groups = $wpdb->get_results(
            sprintf(
                "SELECT * FROM wp_football_bracket_groups WHERE bracket_id IN (%s)",
                implode(',', array_map(fn($id) => "'" . esc_sql($id) . "'", $bracket_ids))
            )
        );
    }

    $group_ids = array_map(fn($g) => $g->id, $groups);

    // Получить все раунды (с сортировкой по number)
    $rounds = [];
    if (!empty($bracket_ids)) {
        $rounds = $wpdb->get_results(
            sprintf(
                "SELECT * FROM wp_football_bracket_rounds WHERE bracket_id IN (%s) ORDER BY number ASC",
                implode(',', array_map(fn($id) => "'" . esc_sql($id) . "'", $bracket_ids))
            )
        );
    }

    $round_ids = array_map(fn($r) => $r->id, $rounds);

    // Получить все матчи
    $matchups = [];
    if (!empty($round_ids)) {
        error_log('Начало процесса: передан массив round_ids: ' . print_r($round_ids, true));

        // Получение матчей из wp_football_match_ups
        $query = sprintf(
            "SELECT * FROM wp_football_match_ups WHERE round_id IN (%s) ORDER BY number ASC",
            implode(',', array_map(fn($id) => "'" . esc_sql($id) . "'", $round_ids))
        );
        error_log('SQL-запрос для wp_football_match_ups: ' . $query);

        $matchups = $wpdb->get_results($query);
        error_log('Результат запроса wp_football_match_ups: ' . print_r($matchups, true));

        // Проверяем, что у нас есть данные
        if (!empty($matchups)) {
            foreach ($matchups as &$matchup) {

                // Проверяем наличие match_ids и преобразуем в массив
                $match_ids = !empty($matchup->match_ids) ? json_decode($matchup->match_ids, true) : [];

                $matchup->matches = []; // Массив для данных о матчах

                if (!empty($match_ids)) {
                    // Составляем SQL-запрос для получения информации о матчах
                    $placeholders = implode(',', array_fill(0, count($match_ids), '%s'));
                    $query = $wpdb->prepare(
                        "SELECT * FROM wp_sport_matches_shedule WHERE id IN ($placeholders)",
                        ...$match_ids
                    );

                    $schedule_data = $wpdb->get_results($query);


                    foreach ($schedule_data as $schedule) {
                        // Парсим note, если оно есть
                        if (!empty($schedule->note)) {
                            $parsed_note = parseNote($schedule->note);
                            $schedule->parsed_note = $parsed_note; // Добавляем распарсенные данные в объект
                        }
                        $matchup->matches[] = $schedule; // Добавляем данные о матче
                    }
                } else {
                }
            }
        } else {
        }
    }


    // Получить все команды, которые участвуют в матчах
    $team_ids = array_unique(array_merge(
        array_column($matchups, 'home_team_id'),
        array_column($matchups, 'away_team_id')
    ));

    $teams = [];
    if (!empty($team_ids)) {
        $team_data = $wpdb->get_results(
            sprintf(
                "SELECT id, name, name_ru, logo FROM wp_soccer_teams WHERE id IN (%s)",
                implode(',', array_map(fn($id) => "'" . esc_sql($id) . "'", $team_ids))
            )
        );

        foreach ($team_data as $team) {
            $teams[$team->id] = [
                'name' => $team->name_ru ?: $team->name,
                'logo' => $team->logo ?: '/wp-content/themes/pm-news/sport/src/img/football-team-placeholder.svg',
            ];
        }
    }

    // Построить иерархию данных
    // foreach ($brackets as &$bracket) {
    //     $bracket->groups = array_filter($groups, fn($g) => $g->bracket_id === $bracket->id);

    //     foreach ($bracket->groups as &$group) {
    //         $group->rounds = array_filter($rounds, fn($r) => $r->group_id === $group->id);

    //         foreach ($group->rounds as &$round) {

    //             // Изменение здесь: теперь matchups - это массив
    //             $round->matchups = array_values(array_filter($matchups, fn($m) => $m->round_id === $round->id));

    //             foreach ($round->matchups as &$matchup) {
    //                 $matchup->match_ids = json_decode($matchup->match_ids);
    //                 $matchup->parent_ids = json_decode($matchup->parent_ids);
    //                 $matchup->children_ids = json_decode($matchup->children_ids);
    //                 $matchup->home_team = $teams[$matchup->home_team_id] ?? null;
    //                 $matchup->away_team = $teams[$matchup->away_team_id] ?? null;
    //             }
    //         }
    //     }

    //     $bracket->rounds = array_filter($rounds, fn($r) => $r->bracket_id === $bracket->id && is_null($r->group_id));

    //     foreach ($bracket->rounds as &$round) {

    //         // Изменение здесь: теперь matchups - это массив
    //         $round->matchups = array_values(array_filter($matchups, fn($m) => $m->round_id === $round->id));

    //         foreach ($round->matchups as &$matchup) {
    //             $matchup->home_team = $teams[$matchup->home_team_id] ?? null;
    //             $matchup->away_team = $teams[$matchup->away_team_id] ?? null;
    //         }
    //     }
    // }

    foreach ($brackets as &$bracket) {
        $bracket->groups = array_values(array_filter($groups, fn($g) => $g->bracket_id === $bracket->id));

        foreach ($bracket->groups as &$group) {
            $group->rounds = array_values(array_filter($rounds, fn($r) => $r->group_id === $group->id));

            foreach ($group->rounds as &$round) {
                $round->matchups = array_values(array_filter($matchups, fn($m) => $m->round_id === $round->id));

                foreach ($round->matchups as &$matchup) {
                    $matchup->match_ids = json_decode($matchup->match_ids, true);
                    $matchup->parent_ids = json_decode($matchup->parent_ids, true);
                    $matchup->children_ids = json_decode($matchup->children_ids, true);
                    $matchup->home_team = $teams[$matchup->home_team_id] ?? null;
                    $matchup->away_team = $teams[$matchup->away_team_id] ?? null;
                }
            }
        }

        $bracket->rounds = array_values(array_filter($rounds, fn($r) => $r->bracket_id === $bracket->id && is_null($r->group_id)));

        foreach ($bracket->rounds as &$round) {
            $round->matchups = array_values(array_filter($matchups, fn($m) => $m->round_id === $round->id));

            foreach ($round->matchups as &$matchup) {
                $matchup->home_team = $teams[$matchup->home_team_id] ?? null;
                $matchup->away_team = $teams[$matchup->away_team_id] ?? null;
            }
        }
    }


    return $brackets[0]->groups[0]->rounds;
}




function safeJsonDecode($data)
{
    // Если входные данные - строка, пробуем декодировать JSON
    if (is_string($data)) {
        $decoded = json_decode($data, false); // Декодируем в объект

        // Если JSON корректно декодирован, возвращаем его
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        // Если JSON некорректен, пробуем извлечь числа вручную
        preg_match_all('/\d+/', $data, $matches);

        // Возвращаем объект с числовыми значениями
        return (object) array_map('intval', $matches[0]);
    }

    // Если входные данные массив, преобразуем в объект
    if (is_array($data)) {
        return (object) $data;
    }

    // Если входные данные уже объект, возвращаем как есть
    if (is_object($data)) {
        return $data;
    }

    // Если формат данных неизвестен, возвращаем пустой объект
    return (object) [];
}

function parseNote($note)
{
    $parsed = [];
    preg_match_all('/(FT|ET|PEN)\[([^\]]+)\]|([\w\s]+ win)/', $note, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        if (!empty($match[1]) && !empty($match[2])) {
            // Парсим значения вида "0-0" в массив чисел
            $parsed[$match[1]] = explode('-', $match[2]);
        } elseif (!empty($match[3])) {
            // Добавляем информацию о победителе
            $parsed['winner'] = $match[3];
        }
    }

    return $parsed;
}