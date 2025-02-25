<?php
// // Регистрация нового API-эндпоинта
// function register_competition_endpoint()
// {
//     register_rest_route('sports/v1', '/matches_by_competition', array(
//         'methods' => 'POST',
//         'callback' => 'get_competition',
//         'args' => array(
//             'competition_ids' => array(
//                 'required' => true,
//                 'validate_callback' => function ($param) {
//                     return is_array($param) && !empty($param);
//                 },
//             ),
//         ),
//         'permission_callback' => '__return_true',
//     ));
// }
// add_action('rest_api_init', 'register_competition_endpoint');

// // Обработчик для получения матчей по идентификаторам соревнований
// function get_competition($request)
// {
//     global $wpdb;

//     $competition_ids = $request->get_param('competition_ids');

//     // Очистка входных данных
//     $competition_ids = array_map('sanitize_text_field', $competition_ids);

//     // Преобразуем список идентификаторов в строку для SQL-запроса
//     $placeholders = implode(',', array_fill(0, count($competition_ids), '%s'));

//     // Запрос для получения всех матчей по переданным идентификаторам соревнований
//     $matches_query = $wpdb->prepare("
//         SELECT * 
//         FROM wp_sport_matches_shedule 
//         WHERE competition_id IN ($placeholders)
//         ORDER BY match_time ASC
//     ", $competition_ids);
//     $matches = $wpdb->get_results($matches_query);

//     // Получение всех остальных данных: соревнования, страны, команды и категории
//     $competitions_query = "SELECT id, name, name_ru, logo, country_id, category_id, slug FROM wp_sport_competitions";
//     $competitions = $wpdb->get_results($competitions_query);

//     $countries_query = "SELECT id, name, name_ru, logo, slug FROM wp_sport_country_data";
//     $countries = $wpdb->get_results($countries_query);

//     $teams_query = "SELECT id, name, name_ru, logo FROM wp_soccer_teams";
//     $teams = $wpdb->get_results($teams_query);

//     $categories_query = "SELECT id, name, name_ru, slug FROM wp_sport_category_data";
//     $categories = $wpdb->get_results($categories_query);

//     // Создаём вспомогательные массивы для быстрого поиска данных
//     $competitions_map = array();
//     foreach ($competitions as $competition) {
//         $competitions_map[$competition->id] = array(
//             'id' => $competition->id,
//             'name' => $competition->name,
//             'name_ru' => $competition->name_ru,
//             'logo' => $competition->logo,
//             'country_id' => $competition->country_id,
//             'category_id' => $competition->category_id,
//             'slug' => $competition->slug
//         );
//     }

//     $countries_map = array();
//     foreach ($countries as $country) {
//         $countries_map[$country->id] = array(
//             'id' => $country->id,
//             'name' => $country->name,
//             'name_ru' => $country->name_ru,
//             'logo' => $country->logo,
//             'slug' => $country->slug
//         );
//     }

//     $teams_map = array();
//     foreach ($teams as $team) {
//         $teams_map[$team->id] = array(
//             'name' => $team->name_ru ? $team->name_ru : $team->name,
//             'logo' => $team->logo,
//         );
//     }

//     $categories_map = array();
//     foreach ($categories as $category) {
//         $categories_map[$category->id] = array(
//             'id' => $category->id,
//             'name' => $category->name,
//             'name_ru' => $category->name_ru,
//             'slug' => $category->slug
//         );
//     }

//     // Группируем матчи по соревнованиям
//     $response = array();
//     foreach ($matches as $match) {
//         $competition_id = $match->competition_id;

//         // Проверяем, что у соревнования есть данные
//         if (!isset($competitions_map[$competition_id])) {
//             continue;
//         }

//         $competition = $competitions_map[$competition_id];
//         $country_id = $competition['country_id'];
//         $category_id = $competition['category_id'];
//         $country = isset($countries_map[$country_id]) ? $countries_map[$country_id] : null;
//         $category = isset($categories_map[$category_id]) ? $categories_map[$category_id] : null;

//         // Добавляем соревнование в ответ, если оно ещё не было добавлено
//         if (!isset($response[$competition_id])) {
//             $response[$competition_id] = array(
//                 'competition' => $competition,
//                 'country' => $country,
//                 'category' => $category,
//                 'matches' => array(),
//             );
//         }

//         // Получаем данные команд для home_team_id и away_team_id
//         $home_team = isset($teams_map[$match->home_team_id]) ? $teams_map[$match->home_team_id] : null;
//         $away_team = isset($teams_map[$match->away_team_id]) ? $teams_map[$match->away_team_id] : null;

//         // Добавляем матч в соревнование
//         $response[$competition_id]['matches'][] = array(
//             'id' => $match->id,
//             'season_id' => $match->season_id,
//             'home_team' => array(
//                 'id' => $match->home_team_id,
//                 'name' => $home_team ? $home_team['name'] : null,
//                 'logo' => $home_team ? $home_team['logo'] : null,
//             ),
//             'away_team' => array(
//                 'id' => $match->away_team_id,
//                 'name' => $away_team ? $away_team['name'] : null,
//                 'logo' => $away_team ? $away_team['logo'] : null,
//             ),
//             'status_id' => $match->status_id,
//             'match_time' => $match->match_time,
//             'venue_id' => $match->venue_id,
//             'referee_id' => $match->referee_id,
//             'neutral' => $match->neutral,
//             'note' => $match->note,
//             'home_scores' => json_decode($match->home_scores, true),
//             'away_scores' => json_decode($match->away_scores, true),
//             'home_position' => $match->home_position,
//             'away_position' => $match->away_position,
//             'coverage' => json_decode($match->coverage, true),
//             'round' => json_decode($match->round, true),
//             'related_id' => $match->related_id,
//             'agg_score' => json_decode($match->agg_score, true),
//             'environment' => json_decode($match->environment, true),
//             'updated_at' => $match->updated_at,
//             'kickoff_timestamp' => $match->kickoff_timestamp,
//         );
//     }

//     // Преобразуем массив соревнований в упорядоченный список для ответа
//     $ordered_response = array_values($response);

//     // Возвращаем ответ
//     return rest_ensure_response($ordered_response);
// }

// Регистрация нового API-эндпоинта
function register_competition_endpoint()
{
    register_rest_route('sports/v1', '/matches_by_competition', array(
        'methods' => 'POST',
        'callback' => 'get_competition',
        'args' => array(
            'competition_ids' => array(
                'required' => true,
                'validate_callback' => function ($param) {
                    return is_array($param) && !empty($param);
                },
            ),
        ),
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'register_competition_endpoint');

// Обработчик для получения матчей по идентификаторам соревнований
function get_competition($request)
{
    global $wpdb;

    $competition_ids = $request->get_param('competition_ids');

    // Очистка входных данных
    $competition_ids = array_map('sanitize_text_field', $competition_ids);

    // Преобразуем список идентификаторов в строку для SQL-запроса
    $placeholders = implode(',', array_fill(0, count($competition_ids), '%s'));

    // Запрос для получения данных соревнований
    $competitions_query = $wpdb->prepare("
        SELECT * 
        FROM wp_sport_competitions 
        WHERE id IN ($placeholders)
    ", $competition_ids);
    $competitions = $wpdb->get_results($competitions_query);

    // Запрос для получения стран
    $countries_query = "SELECT id, name, name_ru, logo, slug FROM wp_sport_country_data";
    $countries = $wpdb->get_results($countries_query);

    // Запрос для получения категорий
    $categories_query = "SELECT id, name, name_ru, logo, slug FROM wp_sport_category_data";
    $categories = $wpdb->get_results($categories_query);

    // Создаём вспомогательные массивы для быстрого поиска
    $countries_map = array();
    foreach ($countries as $country) {
        $countries_map[$country->id] = array(
            'name' => $country->name,
            'name_ru' => $country->name_ru,
            'logo' => $country->logo,
            'slug' => $country->slug
        );
    }

    $categories_map = array();
    foreach ($categories as $category) {
        $categories_map[$category->id] = array(
            'name' => $category->name,
            'name_ru' => $category->name_ru,
            'logo' => $category->logo,
            'slug' => $category->slug
        );
    }

    // Сопоставляем соревнования с их ID для сортировки
    $competitions_map = array();
    foreach ($competitions as $competition) {
        $competitions_map[$competition->id] = $competition;
    }

    // Формируем ответ в порядке, заданном competition_ids
    $response = array();
    foreach ($competition_ids as $competition_id) {
        if (!isset($competitions_map[$competition_id])) {
            continue; // Пропускаем, если соревнование с таким ID не найдено
        }

        $competition = $competitions_map[$competition_id];
        $country_id = $competition->country_id;
        $category_id = $competition->category_id;

        // Данные страны или категории
        $country = isset($countries_map[$country_id]) ? $countries_map[$country_id] : null;
        $category = isset($categories_map[$category_id]) ? $categories_map[$category_id] : null;

        // Логика для логотипа
        $logo = $country && $country['logo'] ? $country['logo'] : ($category && $category['logo'] ? $category['logo'] : '/wp-content/themes/pm-news/sport/src/img/world.svg');

        // Логика для имени
        if ($country) {
            $name = $country['name_ru'] ? $country['name_ru'] : $country['name'];
        } elseif ($category) {
            $name = $category['name_ru'] ? $category['name_ru'] : $category['name'];
        } else {
            $name = '';
        }

        // Логика для слага
        $slug = $country && $country['slug'] ? $country['slug'] : ($category && $category['slug'] ? $category['slug'] : '');

        // Добавление данных соревнования
        $response[] = array(
            'id' => $competition->id,
            'name' => $competition->name_ru ? $competition->name_ru : $competition->name,
            'slug' => $competition->slug,
            'logo' => $logo,
            'name_geo' => $name,
            'slug_geo' => $slug
        );
    }

    // Возвращаем ответ
    return rest_ensure_response($response);
}
