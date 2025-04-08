<?php
function create_teams_posts()
{
    global $wpdb;

    // Получаем все ID команд, для которых уже существуют посты
    $existing_team_ids = $wpdb->get_col("
        SELECT DISTINCT meta_value
        FROM $wpdb->postmeta
        WHERE meta_key = '_team_id'
    ");

    // Формируем условие для исключения уже существующих команд
    if (!empty($existing_team_ids)) {
        $escaped_ids = array_map(function ($id) use ($wpdb) {
            return "'" . esc_sql($id) . "'";
        }, $existing_team_ids);

        $exclude_condition = "AND id NOT IN (" . implode(',', $escaped_ids) . ")";
    } else {
        $exclude_condition = "";
    }

    // Получаем команды, для которых ещё не созданы посты
    $teams = $wpdb->get_results("
        SELECT *
        FROM wp_soccer_teams
        WHERE country_id IS NOT NULL
          AND country_id != ''
          $exclude_condition
    ");

    if (empty($teams)) {

        wp_send_json_error('Нет новых команд для создания постов.');
    }

    $teams_count = count($teams);

    // Запись в лог


    $posts_created = false;
    $created_count = 0;

    foreach ($teams as $team) {
        // Создаём новый пост
        $post_id = wp_insert_post([
            'post_title' => $team->name_ru ?: $team->name,
            'post_name' => sanitize_title($team->slug),
            'post_status' => 'publish',
            'post_type' => 'football_team',
            'meta_input' => [
                '_team_id' => $team->id,
                '_wp_page_template' => 'footballteam-template.php',
            ],
        ]);

        if (!is_wp_error($post_id)) {
            $posts_created = true;
            $created_count++;
        }
    }

    // Обновляем правила пермалинков, если были созданы новые посты
    if ($posts_created) {
        flush_rewrite_rules();
    }

    // Возвращаем результат
    wp_send_json_success("Создано {$created_count} новых постов команд!");
}

// Добавляем обработчик AJAX
add_action('wp_ajax_create_teams_posts', 'create_teams_posts');

add_filter('action_scheduler_queue_runner_concurrent_batches', function () {
    return 10; // Увеличьте число задач, выполняемых одновременно
});