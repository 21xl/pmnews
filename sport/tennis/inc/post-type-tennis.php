<?php
function add_custom_tennis_menu()
{
    add_menu_page(
        'Tennis',
        'Tennis',
        'manage_options',
        'tennis_menu',
        '',
        get_template_directory_uri() . '/sport/src/img/cmt-tennis.svg',
        6
    );
}
add_action('admin_menu', 'add_custom_tennis_menu');

// 1. Регистрация кастомного посттайпа 'tennis'
function register_tennis_post_type()
{
    $labels = [
        'name' => 'Tennis',
        'singular_name' => 'Tennis',
        'add_new' => 'Add tournament',
        'add_new_item' => 'Add new tournament',
        'edit_item' => 'Edit tournament',
        'new_item' => 'New tournament',
        'view_item' => 'Watch tournament',
        'all_items' => 'All tournaments',
        'search_items' => 'Find tournaments',
        'not_found' => 'Tournaments not found',
        'not_found_in_trash' => 'Not found tournaments in trash',
    ];

    $args = [
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => '/statistics/tennis'],
        'hierarchical' => true,
        'supports' => [
            'title',
            'page-attributes',
            'editor',
            'custom-fields',
        ],
        'show_in_rest' => true,
        'show_in_menu' => 'tennis_menu',
        'template' => [
            ['core/paragraph', ['placeholder' => 'Add description...']],
        ],
        'template_lock' => false,
    ];

    register_post_type('tennis', $args);
}
add_action('init', 'register_tennis_post_type');

// 2. Регистрация кастомного посттайпа 'tennis_rating'
function register_tennis_rating_post_type()
{
    $labels = [
        'name' => 'Rating',
        'singular_name' => 'Rating',
        'add_new' => 'Add rating',
        'add_new_item' => 'Add new rating',
        'edit_item' => 'Edit rating',
        'new_item' => 'New rating',
        'view_item' => 'Watch rating',
        'all_items' => 'All ratings',
        'search_items' => 'Find ratings',
        'not_found' => 'Ratings not found',
        'not_found_in_trash' => 'Not found ratings in trash',
    ];

    $args = [
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'statistics/tennis/rating', 'with_front' => false],
        'hierarchical' => true,
        'supports' => [
            'title',
            'editor',
            'comments',
            'revisions',
            'trackbacks',
            'author',
            'excerpt',
            'page-attributes',
            'thumbnail',
            'custom-fields',
            'post-formats'
        ],
        'show_in_rest' => true,
        'show_in_menu' => 'tennis_menu'
    ];

    register_post_type('tennis_rating', $args);
}
add_action('init', 'register_tennis_rating_post_type');

// 3. Регистрация кастомного посттайпа 'tennis_calendar'
function register_tennis_calendar_post_type()
{
    $labels = [
        'name' => 'Calender',
        'singular_name' => 'Calender',
        'add_new' => 'Add calender',
        'add_new_item' => 'Add new calender',
        'edit_item' => 'Edir calender',
        'new_item' => 'New calender',
        'view_item' => 'Watch calender',
        'all_items' => 'All calenders',
        'search_items' => 'Find calender',
        'not_found' => 'Calenders not found',
        'not_found_in_trash' => 'Not found calenders in trash',
    ];

    $args = [
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'statistics/tennis/calendar', 'with_front' => false],
        'hierarchical' => true,
        'supports' => [
            'title',
            'editor',
            'page-attributes',
            'custom-fields',
            'thumbnail',
        ],
        'show_in_rest' => true,
        'show_in_menu' => 'tennis_menu'
    ];

    register_post_type('tennis_calendar', $args);
}
add_action('init', 'register_tennis_calendar_post_type');

add_action('init', function () {
    add_rewrite_rule('statistics/tennis/rating/([^/]+)/?$', 'index.php?tennis_rating=$matches[1]', 'top');
    add_rewrite_rule('statistics/tennis/calendar/([^/]+)/?$', 'index.php?tennis_calendar=$matches[1]', 'top');
});

// 4. Регистрация мета-полей '_category_id', '_tournament_id' и '_seo_title' для 'tennis'
function register_tennis_meta_fields()
{
    $meta_fields = ['_category_id', '_tournament_id', '_seo_title'];
    foreach ($meta_fields as $field) {
        register_post_meta('tennis', $field, [
            'type' => 'string',
            'description' => ucfirst(str_replace('_', ' ', $field)),
            'single' => true,
            'show_in_rest' => true,
        ]);
    }
}
add_action('init', 'register_tennis_meta_fields');

// 5. Регистрация мета-полей '_seo_title' и '_type' для 'tennis_rating'
function register_tennis_rating_meta_fields()
{
    $meta_fields = [
        '_seo_title' => ['type' => 'string', 'description' => 'SEO Title'],
        '_type' => ['type' => 'string', 'description' => 'Type']
    ];
    foreach ($meta_fields as $field => $options) {
        register_post_meta('tennis_rating', $field, [
            'type' => $options['type'],
            'description' => $options['description'],
            'single' => true,
            'show_in_rest' => true,
        ]);
    }
}
add_action('init', 'register_tennis_rating_meta_fields');

// 6. Регистрация мета-полей '_category_id' и '_seo_title' для 'tennis_calendar'
function register_tennis_calendar_meta_fields()
{
    $meta_fields = [
        '_category_id' => ['type' => 'string', 'description' => 'Category ID'],
        '_seo_title' => ['type' => 'string', 'description' => 'SEO Title'],
    ];
    foreach ($meta_fields as $field => $options) {
        register_post_meta('tennis_calendar', $field, [
            'type' => $options['type'],
            'description' => $options['description'],
            'single' => true,
            'show_in_rest' => true,
        ]);
    }
}
add_action('init', 'register_tennis_calendar_meta_fields');

// 7. Добавление метабоксов для 'tennis'
function add_tennis_meta_boxes()
{
    add_meta_box('tennis_category_id_metabox', 'Category ID', 'render_tennis_category_id_metabox', 'tennis', 'side', 'default');
    add_meta_box('tournament_id_metabox', 'Tournament ID', 'render_tournament_id_metabox', 'tennis', 'side', 'default');
    add_meta_box('tennis_seo_title_metabox', 'Tournament SEO Title', 'render_tennis_seo_title_metabox', 'tennis', 'side', 'default');
}
add_action('add_meta_boxes', 'add_tennis_meta_boxes');

// 8. Добавление метабоксов для 'tennis_rating'
function add_tennis_rating_meta_boxes()
{
    add_meta_box('tennis_rating_seo_title_metabox', 'SEO Title', 'render_tennis_rating_seo_title_metabox', 'tennis_rating', 'side', 'default');
    add_meta_box('tennis_rating_type_metabox', 'Type', 'render_tennis_rating_type_metabox', 'tennis_rating', 'side', 'default');
}
add_action('add_meta_boxes', 'add_tennis_rating_meta_boxes');

// 9. Добавление метабоксов для 'tennis_calendar'
function add_tennis_calendar_meta_boxes()
{
    add_meta_box('tennis_calendar_category_id_metabox', 'Category ID', 'render_tennis_calendar_category_id_metabox', 'tennis_calendar', 'side', 'default');
    add_meta_box('tennis_calendar_seo_title_metabox', 'SEO Title', 'render_tennis_calendar_seo_title_metabox', 'tennis_calendar', 'side', 'default');
}
add_action('add_meta_boxes', 'add_tennis_calendar_meta_boxes');

// 10. Отображение HTML для метабоксов 'tennis'
function render_tennis_category_id_metabox($post)
{
    $category_id = get_post_meta($post->ID, '_category_id', true);
    wp_nonce_field('save_tennis_category_id', 'tennis_category_id_nonce');
    echo '<label for="tennis_category_id_field">Введите Category ID:</label>';
    echo '<input type="text" id="tennis_category_id_field" name="_category_id" value="' . esc_attr($category_id) . '" />';
}

function render_tournament_id_metabox($post)
{
    $tournament_id = get_post_meta($post->ID, '_tournament_id', true);
    wp_nonce_field('save_tournament_id', 'tournament_id_nonce');
    echo '<label for="tournament_id_field">Введите Tournament ID:</label>';
    echo '<input type="text" id="tournament_id_field" name="_tournament_id" value="' . esc_attr($tournament_id) . '" />';
}

function render_tennis_seo_title_metabox($post)
{
    $seo_title = get_post_meta($post->ID, '_seo_title', true);
    wp_nonce_field('save_tennis_seo_title', 'tennis_seo_title_nonce');
    echo '<label for="tennis_seo_title_field">Введите Tournament SEO Title:</label>';
    echo '<input type="text" id="tennis_seo_title_field" name="_seo_title" value="' . esc_attr($seo_title) . '" />';
}

// 11. Отображение HTML для метабоксов 'tennis_rating'
function render_tennis_rating_seo_title_metabox($post)
{
    $seo_title = get_post_meta($post->ID, '_seo_title', true);
    wp_nonce_field('save_tennis_rating_seo_title', 'tennis_rating_seo_title_nonce');
    echo '<label for="tennis_rating_seo_title_field">Введите SEO Title:</label>';
    echo '<input type="text" id="tennis_rating_seo_title_field" name="_seo_title" value="' . esc_attr($seo_title) . '" />';
}

function render_tennis_rating_type_metabox($post)
{
    $type = get_post_meta($post->ID, '_type', true);
    wp_nonce_field('save_tennis_rating_type', 'tennis_rating_type_nonce');
    echo '<label for="tennis_rating_type_field">Введите Type:</label>';
    echo '<input type="text" id="tennis_rating_type_field" name="_type" value="' . esc_attr($type) . '" />';
}

// 12. Отображение HTML для метабоксов 'tennis_calendar'
function render_tennis_calendar_category_id_metabox($post)
{
    $category_id = get_post_meta($post->ID, '_category_id', true);
    wp_nonce_field('save_tennis_calendar_category_id', 'tennis_calendar_category_id_nonce');
    echo '<label for="tennis_calendar_category_id_field">Введите Category ID:</label>';
    echo '<input type="text" id="tennis_calendar_category_id_field" name="_category_id" value="' . esc_attr($category_id) . '" />';
}

function render_tennis_calendar_seo_title_metabox($post)
{
    $seo_title = get_post_meta($post->ID, '_seo_title', true);
    wp_nonce_field('save_tennis_calendar_seo_title', 'tennis_calendar_seo_title_nonce');
    echo '<label for="tennis_calendar_seo_title_field">Введите SEO Title:</label>';
    echo '<input type="text" id="tennis_calendar_seo_title_field" name="_seo_title" value="' . esc_attr($seo_title) . '" />';
}

// 13. Сохранение значений мета-полей для 'tennis'
function save_tennis_meta_fields($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
    if (!isset($_POST['post_type']) || $_POST['post_type'] !== 'tennis')
        return;
    if (!current_user_can('edit_post', $post_id))
        return;

    $meta_fields = [
        '_category_id' => ['field' => '_category_id', 'nonce' => 'tennis_category_id_nonce'],
        '_tournament_id' => ['field' => '_tournament_id', 'nonce' => 'tournament_id_nonce'],
        '_seo_title' => ['field' => '_seo_title', 'nonce' => 'tennis_seo_title_nonce']
    ];

    foreach ($meta_fields as $meta_key => $field_info) {
        if (isset($_POST[$field_info['field']])) {
            update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$field_info['field']]));
        } else {
            delete_post_meta($post_id, $meta_key); // Если поле не передано, удаляем мета-данные
        }
    }
}
add_action('save_post', 'save_tennis_meta_fields');

// 14. Сохранение значений мета-полей для 'tennis_rating'
function save_tennis_rating_meta_fields($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
    if (!isset($_POST['post_type']) || $_POST['post_type'] !== 'tennis_rating')
        return;
    if (!current_user_can('edit_post', $post_id))
        return;

    $meta_fields = [
        '_seo_title' => ['field' => '_seo_title', 'nonce' => 'tennis_rating_seo_title_nonce'],
        '_type' => ['field' => '_type', 'nonce' => 'tennis_rating_type_nonce']
    ];

    foreach ($meta_fields as $meta_key => $field_info) {
        if (isset($_POST[$field_info['field']])) {
            update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$field_info['field']]));
        } else {
            delete_post_meta($post_id, $meta_key); // Если поле не передано, удаляем мета-данные
        }
    }
}
add_action('save_post', 'save_tennis_rating_meta_fields');

// 15. Сохранение значений мета-полей для 'tennis_calendar'
function save_tennis_calendar_meta_fields($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
    if (!isset($_POST['post_type']) || $_POST['post_type'] !== 'tennis_calendar')
        return;
    if (!current_user_can('edit_post', $post_id))
        return;

    $meta_fields = [
        '_category_id' => ['field' => '_category_id', 'nonce' => 'tennis_calendar_category_id_nonce'],
        '_seo_title' => ['field' => '_seo_title', 'nonce' => 'tennis_calendar_seo_title_nonce'],
    ];

    foreach ($meta_fields as $meta_key => $field_info) {
        if (isset($_POST[$field_info['field']])) {
            update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$field_info['field']]));
        } else {
            delete_post_meta($post_id, $meta_key); // Если поле не передано, удаляем мета-данные
        }
    }
}
add_action('save_post', 'save_tennis_calendar_meta_fields');

// 16. Вывод мета-полей в колонках административной панели для 'tennis'
function add_tennis_meta_columns($columns)
{
    $columns['_category_id'] = 'Category ID';
    $columns['_tournament_id'] = 'Tournament ID';
    $columns['_seo_title'] = 'Tournament SEO Title';
    return $columns;
}
add_filter('manage_tennis_posts_columns', 'add_tennis_meta_columns');

function render_tennis_meta_columns($column, $post_id)
{
    $meta_value = get_post_meta($post_id, $column, true);
    if ($meta_value) {
        echo esc_html($meta_value);
    }
}
add_action('manage_tennis_posts_custom_column', 'render_tennis_meta_columns', 10, 2);

// 17. Вывод мета-полей в колонках административной панели для 'tennis_rating'
function add_tennis_rating_meta_columns($columns)
{
    $columns['_seo_title'] = 'SEO Title';
    $columns['_type'] = 'Type';
    return $columns;
}
add_filter('manage_tennis_rating_posts_columns', 'add_tennis_rating_meta_columns');

function render_tennis_rating_meta_columns($column, $post_id)
{
    $meta_value = get_post_meta($post_id, $column, true);
    if ($meta_value) {
        echo esc_html($meta_value);
    }
}
add_action('manage_tennis_rating_posts_custom_column', 'render_tennis_rating_meta_columns', 10, 2);

// 18. Вывод мета-полей в колонках административной панели для 'tennis_calendar'
function add_tennis_calendar_meta_columns($columns)
{
    $columns['_category_id'] = 'Category ID';
    $columns['_seo_title'] = 'SEO Title';
    return $columns;
}
add_filter('manage_tennis_calendar_posts_columns', 'add_tennis_calendar_meta_columns');

function render_tennis_calendar_meta_columns($column, $post_id)
{
    $meta_value = get_post_meta($post_id, $column, true);
    if ($meta_value) {
        echo esc_html($meta_value);
    }
}
add_action('manage_tennis_calendar_posts_custom_column', 'render_tennis_calendar_meta_columns', 10, 2);

// Добавляем кнопку "Обновить посты" в админку
function add_update_tennis_posts_button($post_type)
{
    if ($post_type === 'tennis') {
        ?>
        <div style="display: inline-block; margin-left: 10px;">
            <button id="update-tennis-posts" class="button button-primary">
                Обновить посты
            </button>
            <span id="update-tennis-status" style="margin-left: 10px; font-weight: bold;"></span>
        </div>

        <script type="text/javascript">
            document.getElementById('update-tennis-posts').addEventListener('click', async function () {
                const statusEl = document.getElementById('update-tennis-status');
                statusEl.textContent = 'Обновление...';

                try {
                    const response = await fetch(ajaxurl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'update_tennis_posts',
                        }),
                    });

                    const data = await response.json();

                    if (data.success) {
                        statusEl.textContent = 'Посты успешно обновлены!';
                    } else {
                        statusEl.textContent = 'Ошибка: ' + (data.data || 'неизвестная ошибка');
                    }
                } catch (error) {
                    statusEl.textContent = 'Ошибка: ' + error.message;
                }
            });
        </script>
        <?php
    }
}


//TENNIS POST UPDDATER
function schedule_tennis_posts_update()
{
    if (!wp_next_scheduled('auto_update_tennis_posts_event')) {
        // Запускаем задачу каждую минуту (для теста)
        wp_schedule_event(time(), 'every_minute', 'auto_update_tennis_posts_event');
    }
}
add_action('wp', 'schedule_tennis_posts_update');

// Добавляем свой интервал в 1 минуту
function add_custom_cron_intervals($schedules)
{
    $schedules['every_minute'] = array(
        'interval' => 60, // 1 минута в секундах
        'display' => __('Every Minute')
    );
    return $schedules;
}
add_filter('cron_schedules', 'add_custom_cron_intervals');

// Привязываем существующую функцию к событию
add_action('auto_update_tennis_posts_event', 'update_tennis_posts_handler');

// Ваш существующий обработчик
function update_tennis_posts_handler()
{
    // Проверяем права пользователя только для AJAX-запросов
    if (defined('DOING_AJAX') && DOING_AJAX) {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('У вас нет прав для выполнения этого действия.');
            return;
        }
    }

    $base_url = defined('API_URL') ? API_URL : 'http://sport_back:3277';
    $api_url = "{$base_url}/api/tennis/current-tournaments";
    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        error_log('Ошибка при запросе API: ' . $response->get_error_message());
        if (defined('DOING_AJAX') && DOING_AJAX) {
            wp_send_json_error('Ошибка при запросе API: ' . $response->get_error_message());
        }
        return;
    }

    $categories = json_decode(wp_remote_retrieve_body($response), true);

    if (empty($categories) || !is_array($categories)) {
        error_log('Категории или турниры не найдены в ответе API.');
        if (defined('DOING_AJAX') && DOING_AJAX) {
            wp_send_json_error('Категории или турниры не найдены в ответе API.');
        }
        return;
    }

    foreach ($categories as $categoryData) {
        $category = $categoryData['category'];
        $tournaments = $categoryData['tournaments'];

        $category_id = sanitize_text_field($category['id']);
        $category_name = sanitize_text_field($category['name'] ?? 'Без названия');
        $category_seo_title = sanitize_text_field($category['names']['name_en'] ?? $category_name);
        $category_slug = sanitize_text_field($category['slug'] ?? sanitize_title($category_name));
        $category_template = 'tennis-category-template.php';

        // Проверяем, существует ли пост для категории
        $category_posts = get_posts([
            'post_type' => 'tennis',
            'meta_key' => '_category_id',
            'meta_value' => $category_id,
            'posts_per_page' => 1,
            'post_status' => 'any',
            'post_parent' => 0,
        ]);

        // Если пост для категории не существует, создаем новый
        if (empty($category_posts)) {
            $category_post_id = wp_insert_post([
                'post_title' => $category_name,
                'post_name' => $category_slug,
                'post_status' => 'publish',
                'post_type' => 'tennis',
                'post_parent' => 0,
                'meta_input' => [
                    '_category_id' => $category_id,
                    '_tournament_id' => '',
                    '_seo_title' => $category_seo_title,
                    '_wp_page_template' => $category_template,
                ],
            ]);

            if (is_wp_error($category_post_id)) {
                error_log("Ошибка при создании поста для категории {$category_id}: " . $category_post_id->get_error_message());
                continue;
            }

        } else {
            $category_post_id = $category_posts[0]->ID;
            // error_log("Пост для категории {$category_id} уже существует: ID {$category_post_id}");
        }

        $tournament_template = 'tennis-competition-template.php';
        foreach ($tournaments as $tournament) {
            $tournament_id = sanitize_text_field($tournament['id']);
            $tournament_name = sanitize_text_field($tournament['name'] ?? 'Без названия');
            $tournament_seo_title = sanitize_text_field($tournament['names']['name_en'] ?? $tournament_name);
            $tournament_slug = sanitize_text_field($tournament['slug'] ?? sanitize_title($tournament_name));

            // Проверяем, существует ли пост для турнира
            $tournament_posts = get_posts([
                'post_type' => 'tennis',
                'meta_key' => '_tournament_id',
                'meta_value' => $tournament_id,
                'posts_per_page' => 1,
                'post_status' => 'any',
            ]);

            // Если пост для турнира не существует, создаем новый
            if (empty($tournament_posts)) {
                $tournament_post_id = wp_insert_post([
                    'post_title' => $tournament_name,
                    'post_name' => $tournament_slug,
                    'post_status' => 'publish',
                    'post_type' => 'tennis',
                    'post_parent' => $category_post_id,
                    'meta_input' => [
                        '_category_id' => $category_id,
                        '_tournament_id' => $tournament_id,
                        '_seo_title' => $tournament_seo_title,
                        '_wp_page_template' => $tournament_template,
                    ],
                ]);

                if (is_wp_error($tournament_post_id)) {
                    error_log("Ошибка при создании поста для турнира {$tournament_id}: " . $tournament_post_id->get_error_message());
                    continue;
                }

            } else {
                // error_log("Пост для турнира {$tournament_id} уже существует: ID {$tournament_posts[0]->ID}");
            }
        }
    }

    if (defined('DOING_AJAX') && DOING_AJAX) {
        wp_send_json_success('Новые посты успешно созданы.');
    }
}
add_action('wp_ajax_update_tennis_posts', 'update_tennis_posts_handler');

//END TENNIS POST UPDDATER
function add_tennis_results_standings_rewrite_rules()
{
    add_rewrite_rule(
        '^statistics/tennis/([^/]+)/([^/]+)/results/?$',
        'index.php?post_type=tennis&name=$matches[2]&custom_page=results',
        'top'
    );

    add_rewrite_rule(
        '^statistics/tennis/([^/]+)/([^/]+)/standings/?$',
        'index.php?post_type=tennis&name=$matches[2]&custom_page=standings',
        'top'
    );

    add_rewrite_rule(
        '^statistics/tennis/([^/]+)/([^/]+)/live/?$',
        'index.php?post_type=tennis&name=$matches[2]&custom_page=live',
        'top'
    );

    add_rewrite_rule(
        '^statistics/tennis/player/([^/]+)/([^/]+)/?$',
        'index.php?post_type=tennis&player=$matches[1]&team_id=$matches[2]&custom_page=team',
        'top'
    );

    add_rewrite_rule(
        '^statistics/tennis/player/([^/]+)/([^/]+)/results/?$',
        'index.php?post_type=tennis&player=$matches[1]&team_id=$matches[2]&custom_page=results',
        'top'
    );
}
add_action('init', 'add_tennis_results_standings_rewrite_rules');

function add_tennis_custom_query_vars($vars)
{
    $vars[] = 'custom_page';
    $vars[] = 'player';
    $vars[] = 'team_id';
    return $vars;
}
add_filter('query_vars', 'add_tennis_custom_query_vars');

function custom_tennis_template_loader($template)
{
    $custom_page = get_query_var('custom_page');
    $post_type = get_post_type();
    $player = get_query_var('player');
    $team_id = get_query_var('team_id');

    if ($post_type === 'tennis') {
        if ($custom_page === 'team' && $player && $team_id) {
            $custom_template = locate_template('tennisteam-template.php');
            if ($custom_template) {
                return $custom_template;
            }
        }
        // Обработка результатов команды
        elseif ($custom_page === 'results' && $player && $team_id) {
            $custom_template = locate_template('tennisteam-template.php');
            if ($custom_template) {
                return $custom_template;
            }
        }
        if ($custom_page === 'results') {
            $custom_template = locate_template('tennis-results.php');
            if ($custom_template) {
                return $custom_template;
            }
        } elseif ($custom_page === 'standings') {
            $custom_template = locate_template('tennis-standings.php');
            if ($custom_template) {
                return $custom_template;
            }
        } elseif ($custom_page === 'live') {
            $custom_template = locate_template('tennis-live.php');
            if ($custom_template) {
                return $custom_template;
            }
        }
    }

    return $template;
}
add_filter('template_include', 'custom_tennis_template_loader');

// Добавляем параметр custom_page в разрешённые query vars
add_filter('query_vars', function ($vars) {
    $vars[] = 'custom_page';
    return $vars;
});

function add_tennis_match_page_rewrite_rule()
{
    add_rewrite_rule(
        '^statistics/tennis/match/([\w-]+)/?$',
        'index.php?post_type=tennis&custom_page=match&match_id=$matches[1]',
        'top'
    );
}
add_action('init', 'add_tennis_match_page_rewrite_rule');

function add_tennis_match_query_vars($vars)
{
    $vars[] = 'custom_page'; // Для определения пользовательской страницы
    $vars[] = 'match_id';    // Для передачи ID матча
    return $vars;
}
add_filter('query_vars', 'add_tennis_match_query_vars');

function tennis_match_template_loader($template)
{
    $custom_page = get_query_var('custom_page');
    $match_id = get_query_var('match_id');
    $post_type = get_query_var('post_type');

    if ($custom_page === 'match' && !empty($match_id) && $post_type === 'tennis') {
        $custom_template = locate_template('tennis-match.php');
        if ($custom_template) {
            return $custom_template;
        }
    }

    return $template; // Возвращаем стандартный шаблон, если условия не выполнены
}
add_filter('template_include', 'tennis_match_template_loader');