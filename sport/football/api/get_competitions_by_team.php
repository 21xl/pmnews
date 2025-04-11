<?php

function register_matches_by_team_endpoint()
{
    register_rest_route('sports/v1', '/matches_by_team', array(
        'methods' => 'GET',
        'callback' => 'get_matches_by_team',
        'args' => array(
            'team_id' => array(
                'required' => true,
                'validate_callback' => function ($param) {
                    return is_string($param);
                },
            ),
            'tab' => array(
                'required' => true,
                'validate_callback' => function ($param) {
                    return is_string($param);
                },
            ),
        ),
        'permission_callback' => '__return_true',
    ));
}

add_action('rest_api_init', 'register_matches_by_team_endpoint');

function get_matches_by_team($request)
{
    global $wpdb;

    $team_id = $request->get_param('team_id');
    $tab = $request->get_param('tab');
    $statuses = array();
    $response = array();

    switch ($tab) {
        case 'sheduled':
            $statuses = [1];
            break;
        case 'live':
            $statuses = [2, 3, 4, 5, 7];
            break;
        default:
            $statuses = [8];
            break;
    }

    if ($tab === 'squad') {
        $squad_query = $wpdb->prepare("SELECT * FROM wp_football_squad_data WHERE team_id = %s", $team_id);
        $squad = $wpdb->get_results($squad_query);

        if (empty($squad)) {
            return rest_ensure_response([]);
        }

        $squad_data = json_decode($squad[0]->squad, true);

        if (empty($squad_data)) {
            return rest_ensure_response([]);
        }

        $player_ids = array_unique(array_column(array_column($squad_data, 'player'), 'id'));

        // Получаем данные о футболистах
        $placeholders = implode(',', array_fill(0, count($player_ids), '%s'));
        $players_query = $wpdb->prepare("SELECT * FROM wp_football_players WHERE id IN ($placeholders)", $player_ids);
        $players = $wpdb->get_results($players_query);

        $players_indexed = [];
        foreach ($players as $player) {
            $players_indexed[$player->id] = $player;
        }

        // Получаем country_id для игроков
        $country_ids = array_unique(array_column($players_indexed, 'country_id'));

        // Запрос тренера
        $coach_query = $wpdb->prepare("SELECT * FROM wp_football_coaches WHERE team_id = %s", $team_id);
        $coach = $wpdb->get_row($coach_query);

        if ($coach && !empty($coach->country_id)) {
            $country_ids[] = $coach->country_id;
        }

        // Получаем информацию о странах
        $countries_indexed = [];
        if (!empty($country_ids)) {
            $placeholders_country = implode(',', array_fill(0, count($country_ids), '%s'));
            $countries_query = $wpdb->prepare("SELECT id, logo FROM wp_sport_country_data WHERE id IN ($placeholders_country)", $country_ids);
            $countries = $wpdb->get_results($countries_query);

            foreach ($countries as $country) {
                $countries_indexed[$country->id] = $country;
            }
        }

        // Обогащаем данные игроков
        $enhanced_squad = array_map(function ($member) use ($players_indexed, $countries_indexed) {
            $player_id = $member['player']['id'];
            $additional_info = $players_indexed[$player_id] ?? null;

            if ($additional_info) {
                $country_id = $additional_info->country_id ?? null;
                $country_logo = $countries_indexed[$country_id]->logo ?? null;
                $member = array_merge($member, (array) $additional_info);
                $member['country_logo'] = $country_logo;
                $member['logo'] = !empty($member['logo']) ? $member['logo'] : '/wp-content/themes/pm-news/sport/src/img/player.svg';
            }
            return $member;
        }, $squad_data);

        // Группировка по позиции
        $grouped_by_position = [
            'Goalkeeper' => [],
            'Defender' => [],
            'Midfielder' => [],
            'Forward' => [],
            'Coach' => []
        ];

        if ($coach) {
            $coach_country_logo = $countries_indexed[$coach->country_id]->logo ?? null;
            $coach_data = [
                'id' => $coach->id,
                'name' => $coach->name,
                'age' => $coach->age,
                'logo' => !empty($coach->logo) ? $coach->logo : '/wp-content/themes/pm-news/sport/src/img/coach.svg',
                'country_logo' => $coach_country_logo
            ];
            $grouped_by_position['Coach'][] = $coach_data;
        }

        // Группируем игроков по их позиции
        foreach ($enhanced_squad as $player) {
            $position = $player['position'] ?? 'Unknown';

            switch ($position) {
                case 'G':
                    $grouped_by_position['Goalkeeper'][] = $player;
                    break;
                case 'D':
                    $grouped_by_position['Defender'][] = $player;
                    break;
                case 'M':
                    $grouped_by_position['Midfielder'][] = $player;
                    break;
                case 'F':
                    $grouped_by_position['Forward'][] = $player;
                    break;
                default:
                    $grouped_by_position['Unknown'][] = $player;
                    break;
            }
        }

        $result = [];
        foreach ($grouped_by_position as $position => $players) {
            $result[] = (object) [
                'position' => $position,
                'players' => $players
            ];
        }

        return rest_ensure_response($result);
    } else {

        $matches_query = $wpdb->prepare("
            SELECT id, season_id, competition_id, home_team_id, away_team_id, status_id, match_time, home_scores, away_scores, round, kickoff_timestamp
            FROM wp_sport_matches_shedule
            WHERE (home_team_id = %s OR away_team_id = %s) AND status_id IN (" . implode(',', array_fill(0, count($statuses), '%d')) . ")
            ORDER BY match_time ASC
        ", $team_id, $team_id, ...$statuses);

        $matches = $wpdb->get_results($matches_query);

        if (empty($matches)) {
            return rest_ensure_response([]);
        }

        $competition_ids = array_unique(array_column($matches, 'competition_id'));
        $competition_ids_list = implode(',', array_map('intval', $competition_ids));

        $competitions_query = sprintf("
            SELECT id, name, name_ru, logo, country_id, category_id, slug
            FROM wp_sport_competitions
            WHERE id IN (%s)
        ", $competition_ids_list);

        $competitions = $wpdb->get_results($competitions_query);

        $team_ids = array_unique(array_merge(
            array_column($matches, 'home_team_id'),
            array_column($matches, 'away_team_id')
        ));

        $teams_query = sprintf("
            SELECT id, name, name_ru, logo
            FROM wp_soccer_teams
            WHERE id IN ('%s')
        ", implode("','", array_map('esc_sql', $team_ids)));

        $teams = $wpdb->get_results($teams_query);

        $teams_map = array();
        foreach ($teams as $team) {
            $teams_map[$team->id] = array(
                'id' => $team->id,
                'name' => $team->name,
                'logo' => $team->logo ?: '/wp-content/themes/pm-news/sport/src/img/football-team-placeholder.svg',
            );
        }

        $competitions_map = array();
        foreach ($competitions as $competition) {
            $competitions_map[$competition->id] = array(
                'id' => $competition->id,
                'name' => $competition->name,
                'logo' => $competition->logo ?: '/wp-content/themes/pm-news/sport/src/img/football-team-placeholder.svg',
                'country_id' => $competition->country_id,
                'category_id' => $competition->category_id,
                'slug' => $competition->slug,
            );
        }

        $country_ids = array_unique(array_column($competitions, 'country_id'));
        $category_ids = array_unique(array_column($competitions, 'category_id'));

        $countries_query = sprintf("
        SELECT id, name, name_ru, logo, slug
        FROM wp_sport_country_data
        WHERE id IN ('%s')
    ", implode("','", array_map('esc_sql', $country_ids)));

        $countries = $wpdb->get_results($countries_query);

        $countries_map = array();
        foreach ($countries as $country) {
            $countries_map[$country->id] = array(
                'id' => $country->id,
                'name' => $country->name,
                'logo' => $country->logo ?: '/wp-content/themes/pm-news/sport/src/img/world.svg',
                'slug' => $country->slug,
            );
        }

        $categories_query = sprintf("
        SELECT id, name, name_ru, slug
        FROM wp_sport_category_data
        WHERE id IN ('%s')
    ", implode("','", array_map('esc_sql', $category_ids)));

        $categories = $wpdb->get_results($categories_query);

        $categories_map = array();
        foreach ($categories as $category) {
            $categories_map[$category->id] = array(
                'id' => $category->id,
                'name' => $category->name,
                'logo' => '/wp-content/themes/pm-news/sport/src/img/world.svg',
                'slug' => $category->slug,
            );
        }
        foreach ($matches as $match) {
            $competition_id = $match->competition_id;

            if (!isset($competitions_map[$competition_id])) {
                continue;
            }

            $competition = $competitions_map[$competition_id];
            $country = isset($countries_map[$competition['country_id']]) ? $countries_map[$competition['country_id']] : null;
            $category = isset($categories_map[$competition['category_id']]) ? $categories_map[$competition['category_id']] : null;

            if (!isset($response[$competition_id])) {
                $response[$competition_id] = array(
                    'competition' => $competition,
                    'country' => $country,
                    'category' => $category,
                    'matches' => array(),
                );
            }

            $home_team = $teams_map[$match->home_team_id] ?? null;
            $away_team = $teams_map[$match->away_team_id] ?? null;

            $response[$competition_id]['matches'][] = array(
                'id' => $match->id,
                'season_id' => $match->season_id,
                'home_team' => $home_team,
                'away_team' => $away_team,
                'status_id' => $match->status_id,
                'match_time' => $match->match_time,
                'home_scores' => json_decode($match->home_scores, true),
                'away_scores' => json_decode($match->away_scores, true),
                'round' => json_decode($match->round, true),
                'kickoff_timestamp' => $match->kickoff_timestamp,
            );
        }
        return rest_ensure_response(array_values($response));
    }
}