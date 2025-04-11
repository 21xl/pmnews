<?php


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
            $name = $country['name'];
        } elseif ($category) {
            $name = $category['name'];
        } else {
            $name = '';
        }

        // Логика для слага
        $slug = $country && $country['slug'] ? $country['slug'] : ($category && $category['slug'] ? $category['slug'] : '');

        // Добавление данных соревнования
        $response[] = array(
            'id' => $competition->id,
            'name' => $competition->name,
            'slug' => $competition->slug,
            'logo' => $logo,
            'name_geo' => $name,
            'slug_geo' => $slug
        );
    }

    // Возвращаем ответ
    return rest_ensure_response($response);
}
