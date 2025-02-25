<?php
// Регистрация API-эндпоинта
function register_competitions_endpoint()
{
    register_rest_route('sports/v1', '/competitions_by_c', array(
        'methods' => 'GET',
        'callback' => 'get_competitions',
        'args' => array(
            'country_id' => array(
                'validate_callback' => function ($param) {
                    return is_string($param);
                },
            ),
            'category_id' => array(
                'validate_callback' => function ($param) {
                    return is_string($param);
                },
            ),
        ),
        'permission_callback' => '__return_true', // Позволяет всем доступ к эндпоинту
    ));
}
add_action('rest_api_init', 'register_competitions_endpoint');

// Обработчик запроса
function get_competitions($request)
{
    global $wpdb;

    $country_id = sanitize_text_field($request->get_param('country_id'));
    $category_id = sanitize_text_field($request->get_param('category_id'));

    if (!$country_id && !$category_id) {
        return new WP_Error('missing_params', 'Необходимо указать либо country_id, либо category_id', array('status' => 400));
    }

    $response = array();
    $logo = '';
    $parent_slug = '';

    if ($country_id && $country_id != 'undefined') {
        // Поиск по country_id
        $country_query = $wpdb->prepare("
            SELECT logo, slug FROM wp_sport_country_data 
            WHERE id = %s
        ", $country_id);
        $country_data = $wpdb->get_row($country_query);

        if (!$country_data) {
            return new WP_Error('no_country', 'Страна не найдена', array('status' => 404));
        }

        $competitions_query = $wpdb->prepare("
            SELECT c.* 
            FROM wp_sport_competitions c
            JOIN wp_sport_matches_shedule m ON c.id = m.competition_id
            WHERE c.country_id = %s 
              AND c.cur_season_id IS NOT NULL 
              AND c.cur_season_id != ''
            GROUP BY c.id
        ", $country_id);
        $competitions = $wpdb->get_results($competitions_query);

        if (empty($competitions)) {
            return new WP_Error('no_competitions', 'Соревнования для данной страны не найдены', array('status' => 404));
        }

        $response['country_logo'] = $country_data->logo;
        $response['country_slug'] = $country_data->slug;
        $logo = $country_data->logo;
        $parent_slug = $country_data->slug;
    } else {
        // Поиск по category_id
        $category_query = $wpdb->prepare("
            SELECT name_ru AS category_name, logo AS category_logo, slug AS category_slug 
            FROM wp_sport_category_data 
            WHERE id = %s
        ", $category_id);
        $category_data = $wpdb->get_row($category_query);

        if (!$category_data) {
            return new WP_Error('no_category', 'Категория не найдена', array('status' => 404));
        }

        $competitions_query = $wpdb->prepare("
            SELECT c.* 
            FROM wp_sport_competitions c
            JOIN wp_sport_matches_shedule m ON c.id = m.competition_id
            WHERE c.category_id = %s 
              AND (c.country_id IS NULL OR c.country_id = '') 
              AND c.cur_season_id IS NOT NULL 
              AND c.cur_season_id != ''
            GROUP BY c.id
        ", $category_id);
        $competitions = $wpdb->get_results($competitions_query);

        if (empty($competitions)) {
            return new WP_Error('no_competitions', 'Соревнования для данной категории не найдены', array('status' => 404));
        }

        $response['category_name'] = $category_data->category_name;
        $response['category_logo'] = $category_data->category_logo;
        $response['category_slug'] = $category_data->category_slug;

        $logo = '/wp-content/themes/pm-news/sport/src/img/world.svg';
        $parent_slug = $category_data->category_slug;
    }

    // Формируем общий список соревнований
    $response['total'] = count($competitions);
    $response['competitions'] = array_map(function ($competition) use ($logo, $parent_slug) {
        return array(
            'id' => $competition->id,
            'category_id' => $competition->category_id,
            'country_id' => $competition->country_id,
            'name' => isset($competition->name_ru) && $competition->name_ru !== ''
                ? $competition->name_ru
                : $competition->name,
            'country_logo' => $logo, // Добавляем логотип страны или категории
            'cur_season_id' => $competition->cur_season_id,
            'updated_at' => $competition->updated_at,
            'slug' => $competition->slug,
            'parent_slug' => $parent_slug
        );
    }, $competitions);

    return rest_ensure_response($response);
}
