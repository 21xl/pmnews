<?php
/*
-- Удаляем постмета для постов, созданных сегодня
DELETE pm
FROM wp_postmeta pm
JOIN wp_posts p ON pm.post_id = p.ID
WHERE DATE(p.post_date) = '2025-01-09';

-- Удаляем посты, созданные 21 ноября 2024 года
DELETE FROM wp_posts
WHERE DATE(post_date) = '2025-01-09';
*/

function allow_duplicate_slugs_for_football($slug, $post_ID, $post_status, $post_type, $post_parent)
{
    // Для кастомного пост-тайпа "football" разрешаем одинаковые слаги
    if ($post_type === 'football') {
        return $slug;
    }

    return $slug;
}
add_filter('wp_unique_post_slug', 'allow_duplicate_slugs_for_football', 10, 5);

function update_football_posts()
{
    global $wpdb;

    // Лог для отладки
    error_log("Начало выполнения функции update_football_posts");

    // 1. Обновляем категории
    $categories = $wpdb->get_results("SELECT * FROM wp_sport_category_data");

    error_log(print_r($categories));

    foreach ($categories as $category) {
        $existing_post_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM wp_postmeta WHERE meta_key = '_category_id' AND meta_value = %s",
            $category->id
        ));

        if (!$existing_post_id) {
            // Создаём новый пост
            $post_data = [
                'post_title' => $category->name_ru ?: $category->name,
                'post_name' => $category->slug,
                'post_status' => 'publish',
                'post_type' => 'football',
                'meta_input' => [
                    '_category_id' => $category->id,
                    '_wp_page_template' => 'category-template.php',
                ],
            ];

            $post_id = wp_insert_post($post_data);

            if (!is_wp_error($post_id)) {
                error_log("Создан пост для категории ID: {$category->id}, пост ID: {$post_id}");
            } else {
                error_log("Ошибка создания поста для категории ID: {$category->id}");
            }
        }
    }

    // 2. Обновляем страны
    $countries = $wpdb->get_results("SELECT * FROM wp_sport_country_data");

    foreach ($countries as $country) {
        error_log('test' . $country->id);
        $existing_post_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM wp_postmeta WHERE meta_key = '_country_id' AND meta_value = %s",
            $country->id
        ));

        if (!$existing_post_id) {
            $post_data = [
                'post_title' => $country->name_ru ?: $country->name,
                'post_name' => $country->slug,
                'post_status' => 'publish',
                'post_type' => 'football',
                'meta_input' => [
                    '_country_id' => $country->id,
                    '_wp_page_template' => 'country-template.php',
                ],
            ];

            $post_id = wp_insert_post($post_data);

            if (!is_wp_error($post_id)) {
                error_log("Создан пост для страны ID: {$country->id}, пост ID: {$post_id}");
            }
        }
    }

    // 3. Обновляем соревнования
    $competitions = $wpdb->get_results("SELECT * FROM wp_sport_competitions");

    foreach ($competitions as $competition) {
        $existing_post_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM wp_postmeta WHERE meta_key = '_competition_id' AND meta_value = %s",
            $competition->id
        ));

        if (!$existing_post_id) {
            $parent_post_id = null;

            // Устанавливаем родительский пост
            if ($competition->country_id) {
                $parent_post_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT post_id FROM wp_postmeta WHERE meta_key = '_country_id' AND meta_value = %s",
                    $competition->country_id
                ));
            }

            if (!$parent_post_id && $competition->category_id) {
                $parent_post_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT post_id FROM wp_postmeta WHERE meta_key = '_category_id' AND meta_value = %s",
                    $competition->category_id
                ));
            }

            // Создаём новый пост
            $post_data = [
                'post_title' => $competition->name_ru ?: $competition->name,
                'post_name' => $competition->slug,
                'post_status' => 'publish',
                'post_type' => 'football',
                'post_parent' => $parent_post_id ?: 0,
                'meta_input' => [
                    '_competition_id' => $competition->id,
                ],
            ];

            $post_id = wp_insert_post($post_data);

            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, '_wp_page_template', 'competition-template.php');
                error_log("Создан пост для соревнования ID: {$competition->id}, пост ID: {$post_id}");
            } else {
                error_log("Ошибка создания поста для соревнования ID: {$competition->id}");
            }
        }
    }

    wp_send_json_success('Посты для стран, категорий и соревнований обновлены!');
}

// Добавляем обработчик AJAX
add_action('wp_ajax_update_football_posts', 'update_football_posts');