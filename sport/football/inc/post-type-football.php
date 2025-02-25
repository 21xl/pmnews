<?php
function add_custom_football_menu()
{
    add_menu_page(
        'Футбол и Букмекерские конторы', // Заголовок страницы
        'Футбол',           // Имя пункта меню
        'manage_options',   // Необходимые права доступа
        'football_menu',    // Идентификатор меню
        '',                 // Callback-функция (оставляем пустым)
        get_template_directory_uri() . '/sport/src/img/cmt-football.svg', // Путь к кастомной иконке
        5                   // Позиция меню
    );
}
add_action('admin_menu', 'add_custom_football_menu');
// 1. Регистрация кастомного посттайпа 'football'
function register_football_post_type()
{
    $labels = [
        'name' => 'Футбол',
        'singular_name' => 'Футбол',
        'add_new' => 'Добавить соревнование',
        'add_new_item' => 'Добавить новое соревнование',
        'edit_item' => 'Редактировать соревнование',
        'new_item' => 'Новое соревнование',
        'view_item' => 'Просмотреть соревнование',
        'all_items' => 'Все соревнования',
        'search_items' => 'Найти соревнование',
        'not_found' => 'Соревнования не найдены',
        'not_found_in_trash' => 'Нет соревнований в корзине',
    ];

    $args = [
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => '/statistics/football'],
        'hierarchical' => true,
        'supports' => [
            'title',
            'page-attributes',
            'editor',
            'custom-fields',
        ],
        'show_in_rest' => true,
        'show_in_menu' => 'football_menu',
        'template' => [
            ['core/paragraph', ['placeholder' => 'Add description...']],
        ],
        'template_lock' => true, // Дает возможность редактировать шаблон
        'template_select' => true, // Включает выбор шаблонов
    ];

    register_post_type('football', $args);
}
add_action('init', 'register_football_post_type');

// 2. Регистрация мета-полей '_category_id', '_competition_id' и '_country_id'
function register_football_meta_fields()
{
    $meta_fields = ['_category_id', '_competition_id', '_country_id', '_seo_title'];
    foreach ($meta_fields as $field) {
        register_post_meta('football', $field, [
            'type' => 'string',
            'description' => ucfirst(str_replace('_', ' ', $field)),
            'single' => true,
            'show_in_rest' => true,
        ]);
    }
}
add_action('init', 'register_football_meta_fields');

// 3. Добавление метабоксов для каждого мета-поля
function add_football_meta_boxes()
{
    add_meta_box('category_id_metabox', 'Category ID', 'render_category_id_metabox', 'football', 'side', 'default');
    add_meta_box('competition_id_metabox', 'Competition ID', 'render_competition_id_metabox', 'football', 'side', 'default');
    add_meta_box('country_id_metabox', 'Country ID', 'render_country_id_metabox', 'football', 'side', 'default');
    add_meta_box('seo_title_metabox', 'Competition SEO Title', 'render_seo_title_metabox', 'football', 'side', 'default');
}
add_action('add_meta_boxes', 'add_football_meta_boxes');

// 4. Отображение HTML для метабоксов
function render_category_id_metabox($post)
{
    $category_id = get_post_meta($post->ID, '_category_id', true);
    wp_nonce_field('save_category_id', 'category_id_nonce');
    echo '<label for="category_id_field">Введите Category ID:</label>';
    echo '<input type="text" id="category_id_field" name="_category_id" value="' . esc_attr($category_id) . '" />';
}

function render_competition_id_metabox($post)
{
    $competition_id = get_post_meta($post->ID, '_competition_id', true);
    wp_nonce_field('save_competition_id', 'competition_id_nonce');
    echo '<label for="competition_id_field">Введите Competition ID:</label>';
    echo '<input type="text" id="competition_id_field" name="_competition_id" value="' . esc_attr($competition_id) . '" />';
}

function render_country_id_metabox($post)
{
    $country_id = get_post_meta($post->ID, '_country_id', true);
    wp_nonce_field('save_country_id', 'country_id_nonce');
    echo '<label for="country_id_field">Введите Country ID:</label>';
    echo '<input type="text" id="country_id_field" name="_country_id" value="' . esc_attr($country_id) . '" />';
}

function render_seo_title_metabox($post)
{
    $seo_title = get_post_meta($post->ID, '_seo_title', true); // Получаем сохраненное значение
    wp_nonce_field('save_seo_title', 'seo_title_nonce'); // Защита от CSRF
    echo '<label for="seo_title_field">Введите Competition SEO Title:</label>';
    echo '<input type="text" id="seo_title_field" name="_seo_title" value="' . esc_attr($seo_title) . '" />';
}


// 5. Сохранение значений мета-полей при сохранении поста
function save_football_meta_fields($post_id)
{

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return; // Предотвращаем автосохранение
    if (!isset($_POST['post_type']) || $_POST['post_type'] !== 'football')
        return; // Проверяем тип поста
    if (!current_user_can('edit_post', $post_id))
        return; // Проверяем права доступа

    $meta_fields = [
        '_category_id' => ['field' => '_category_id', 'nonce' => 'category_id_nonce'],
        '_competition_id' => ['field' => '_competition_id', 'nonce' => 'competition_id_nonce'],
        '_country_id' => ['field' => '_country_id', 'nonce' => 'country_id_nonce'],
        '_seo_title' => ['field' => '_seo_title', 'nonce' => 'seo_title_nonce']
    ];

    foreach ($meta_fields as $meta_key => $field_info) {
        if (isset($_POST[$field_info['field']])) {
            update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$field_info['field']]));
        } else {
            delete_post_meta($post_id, $meta_key); // Если поле не передано, удаляем мета-данные
        }
    }
}
add_action('save_post', 'save_football_meta_fields');

// 6. Вывод мета-полей в колонках административной панели
function add_football_meta_columns($columns)
{
    $columns['_category_id'] = 'Category ID';
    $columns['_competition_id'] = 'Competition ID';
    $columns['_country_id'] = 'Country ID';
    $columns['_seo_title'] = 'Competition SEO Title';
    return $columns;
}
add_filter('manage_football_posts_columns', 'add_football_meta_columns');

function render_football_meta_columns($column, $post_id)
{
    $meta_value = get_post_meta($post_id, $column, true);
    if ($meta_value) {
        echo esc_html($meta_value);
    }
}
add_action('manage_football_posts_custom_column', 'render_football_meta_columns', 10, 2);

function remove_post_id_from_custom_tables($post_id)
{
    global $wpdb;

    // Проверяем тип поста
    $post_type = get_post_type($post_id);


    if ($post_type === 'football') { // Убедитесь, что это правильный тип поста
        // Проверяем метаданные, чтобы определить, что удалять
        $category_id = get_post_meta($post_id, '_category_id', true);
        $country_id = get_post_meta($post_id, '_country_id', true);
        $competition_id = get_post_meta($post_id, '_competition_id', true);

        // Если это категория, удаляем связь с таблицей wp_sport_category_data
        if ($category_id) {
            error_log("Кат ID: {$category_id}");
            $wpdb->update(
                'wp_sport_category_data',
                ['post_id' => null], // Удаляем связь с постом
                ['id' => $category_id],
                ['%d'],
                ['%s']
            );
        }

        // Если это страна, удаляем связь с таблицей wp_sport_country_data
        if ($country_id) {
            error_log("Стр ID: {$country_id}");
            $wpdb->update(
                'wp_sport_country_data',
                ['post_id' => null], // Удаляем связь с постом
                ['id' => $country_id],
                ['%d'],
                ['%s']
            );
        }

        // Если это соревнование, удаляем связь с таблицей wp_sport_competitions
        if ($competition_id) {
            error_log("Комп ID: {$competition_id}");
            $wpdb->update(
                'wp_sport_competitions',
                ['post_id' => null], // Удаляем связь с постом
                ['id' => $competition_id],
                ['%d'],
                ['%s']
            );
        }
    }
}

// Подключаем функцию к хуку удаления поста
add_action('before_delete_post', 'remove_post_id_from_custom_tables');

function add_update_posts_button_to_filters($post_type)
{
    print_r($post_type);
    if ($post_type === 'football') { // Убедитесь, что тип записи совпадает
        ?>
        <div style="display: inline-block; margin-left: 10px;">
            <button id="update-football-posts" class="button button-primary">
                Обновить посты
            </button>
            <span id="update-football-status" style="margin-left: 10px; font-weight: bold;"></span>
        </div>

        <script type="text/javascript">
            document.getElementById('update-football-posts').addEventListener('click', async function () {
                const statusEl = document.getElementById('update-football-status');
                statusEl.textContent = 'Обновление...';

                try {
                    // Отправляем запрос
                    const response = await fetch(ajaxurl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'update_football_posts',
                        }),
                    });

                    // Обрабатываем ответ
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
add_action('restrict_manage_posts', 'add_update_posts_button_to_filters');

function add_results_standings_rewrite_rules()
{
    add_rewrite_rule(
        '^statistics/football/([^/]+)/([^/]+)/results/?$',
        'index.php?post_type=football&name=$matches[2]&custom_page=results',
        'top'
    );

    add_rewrite_rule(
        '^statistics/football/([^/]+)/([^/]+)/standings/?$',
        'index.php?post_type=football&name=$matches[2]&custom_page=standings',
        'top'
    );

    add_rewrite_rule(
        '^statistics/football/([^/]+)/([^/]+)/live/?$',
        'index.php?post_type=football&name=$matches[2]&custom_page=live',
        'top'
    );
}
add_action('init', 'add_results_standings_rewrite_rules');

function add_custom_query_vars($vars)
{
    $vars[] = 'custom_page';
    return $vars;
}
add_filter('query_vars', 'add_custom_query_vars');

function custom_football_template_loader($template)
{
    $custom_page = get_query_var('custom_page');
    $post_type = get_post_type();

    if ($post_type === 'football') {
        if ($custom_page === 'results') {
            $custom_template = locate_template('football-results.php');
            if ($custom_template) {
                return $custom_template;
            }
        } elseif ($custom_page === 'standings') {
            $custom_template = locate_template('football-standings.php');
            if ($custom_template) {
                return $custom_template;
            }
        } elseif ($custom_page === 'live') {
            $custom_template = locate_template('football-live.php');
            if ($custom_template) {
                return $custom_template;
            }
        }
    }

    return $template;
}
add_filter('template_include', 'custom_football_template_loader');

// Добавляем параметр custom_page в разрешённые query vars
add_filter('query_vars', function ($vars) {
    $vars[] = 'custom_page';
    return $vars;
});

add_filter('rank_math/frontend/canonical', '__return_false');
function add_match_page_rewrite_rule()
{
    add_rewrite_rule(
        '^statistics/football/match/([\w-]+)/?$',
        'index.php?post_type=football&custom_page=match&match_id=$matches[1]',
        'top'
    );
}
add_action('init', 'add_match_page_rewrite_rule');

function add_match_query_vars($vars)
{
    $vars[] = 'custom_page'; // Для определения пользовательской страницы
    $vars[] = 'match_id';    // Для передачи ID матча
    return $vars;
}
add_filter('query_vars', 'add_match_query_vars');

function match_template_loader($template)
{
    $custom_page = get_query_var('custom_page');
    $match_id = get_query_var('match_id');

    // Проверяем, что страница — match и передан match_id
    if ($custom_page === 'match' && !empty($match_id)) {
        $custom_template = locate_template('football-match.php'); // Путь к вашему шаблону
        if ($custom_template) {
            return $custom_template;
        }
    }

    return $template; // Возвращаем стандартный шаблон
}

add_filter('template_include', 'match_template_loader');


// Регистрация кастомного посттайпа "Букмекерские конторы"
function register_advertisement_post_type()
{
    $labels = [
        'name' => 'Букмекерская контора',
        'singular_name' => 'Букмекерская контора',
        'add_new' => 'Добавить БК',
        'add_new_item' => 'Добавить новую БК',
        'edit_item' => 'Редактировать БК',
        'new_item' => 'Новая БК',
        'view_item' => 'Просмотреть БК',
        'all_items' => 'Букмекерские конторы',
        'search_items' => 'Найти БК',
        'not_found' => 'Букмекерские конторы не найдена',
        'not_found_in_trash' => 'Нет БК в корзине',
    ];

    $args = [
        'labels' => $labels,
        'public' => false, // Делаем недоступным для публичного просмотра
        'show_ui' => true, // Отображаем в админке
        'menu_icon' => 'dashicons-megaphone', // Иконка для меню
        'supports' => ['title', 'thumbnail', 'custom-fields'],
        'show_in_rest' => true,
        'show_in_menu' => 'football_menu', // Общий идентификатор меню
    ];

    register_post_type('advertisement', $args);
}
add_action('init', 'register_advertisement_post_type');

// Добавление метабоксов для посттайпа "Букмекерские конторы"
function add_advertisement_meta_boxes()
{
    add_meta_box('advertisement_logo', 'Логотип', 'render_advertisement_logo_metabox', 'advertisement', 'side', 'default');
    add_meta_box('advertisement_link', 'Ссылка', 'render_advertisement_link_metabox', 'advertisement', 'side', 'default');
}
add_action('add_meta_boxes', 'add_advertisement_meta_boxes');

// Отображение HTML для метабокса "Логотип"
function render_advertisement_logo_metabox($post)
{
    $logo = get_post_meta($post->ID, '_advertisement_logo', true);
    echo '<label for="advertisement_logo_field">URL Логотипа:</label>';
    echo '<input type="text" id="advertisement_logo_field" name="advertisement_logo_field" value="' . esc_attr($logo) . '" style="width: 100%;" />';
}

// Отображение HTML для метабокса "Ссылка"
function render_advertisement_link_metabox($post)
{
    $link = get_post_meta($post->ID, '_advertisement_link', true);
    echo '<label for="advertisement_link_field">Внешняя ссылка:</label>';
    echo '<input type="url" id="advertisement_link_field" name="advertisement_link_field" value="' . esc_attr($link) . '" style="width: 100%;" />';
}

// Сохранение мета-полей
function save_advertisement_meta_fields($post_id)
{
    if (!empty($_POST['advertisement_logo_field'])) {
        update_post_meta($post_id, '_advertisement_logo', sanitize_text_field($_POST['advertisement_logo_field']));
    } else {
        delete_post_meta($post_id, '_advertisement_logo');
    }

    if (!empty($_POST['advertisement_link_field'])) {
        update_post_meta($post_id, '_advertisement_link', esc_url_raw($_POST['advertisement_link_field']));
    } else {
        delete_post_meta($post_id, '_advertisement_link');
    }
}
add_action('save_post', 'save_advertisement_meta_fields');


function register_teams_post_type()
{
    $labels = [
        'name' => 'Футбольные команды',
        'singular_name' => 'Команда',
        'add_new' => 'Добавить команду',
        'add_new_item' => 'Добавить новую команду',
        'edit_item' => 'Редактировать команду',
        'new_item' => 'Новая команда',
        'view_item' => 'Просмотреть команду',
        'all_items' => 'Все команды',
        'search_items' => 'Найти команду',
        'not_found' => 'Команды не найдены',
        'not_found_in_trash' => 'Нет команд в корзине',
    ];

    $args = [
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => '/statistics/teams'],
        'hierarchical' => true,
        'supports' => [
            'title',
            'editor',
            'custom-fields',
            'page-attributes',
        ],
        'show_in_rest' => true,
        'show_in_menu' => 'football_menu',
        'template_lock' => true, // Дает возможность редактировать шаблон
        'template_select' => true, // Включает выбор шаблонов
    ];

    register_post_type('football_team', $args);
}
add_action('init', 'register_teams_post_type');


function register_teams_meta_fields()
{
    $meta_fields = ['_team_id', '_stadium_id', '_country_id'];
    foreach ($meta_fields as $field) {
        register_post_meta('football_team', $field, [
            'type' => 'string',
            'description' => ucfirst(str_replace('_', ' ', $field)),
            'single' => true,
            'show_in_rest' => true,
        ]);
    }
}
add_action('init', 'register_teams_meta_fields');

function add_teams_meta_boxes()
{
    add_meta_box('team_id_metabox', 'Team ID', 'render_team_id_metabox', 'football_team', 'side', 'default');
    add_meta_box('stadium_id_metabox', 'Stadium ID', 'render_stadium_id_metabox', 'football_team', 'side', 'default');
    add_meta_box('country_id_metabox', 'Country ID', 'render_country_id_metabox', 'football_team', 'side', 'default');
}
add_action('add_meta_boxes', 'add_teams_meta_boxes');

function render_team_id_metabox($post)
{
    $team_id = get_post_meta($post->ID, '_team_id', true);
    wp_nonce_field('save_team_id', 'team_id_nonce');
    echo '<label for="team_id_field">Введите Team ID:</label>';
    echo '<input type="text" id="team_id_field" name="team_id_field" value="' . esc_attr($team_id) . '" />';
}

function render_stadium_id_metabox($post)
{
    $stadium_id = get_post_meta($post->ID, '_stadium_id', true);
    wp_nonce_field('save_stadium_id', 'stadium_id_nonce');
    echo '<label for="stadium_id_field">Введите Stadium ID:</label>';
    echo '<input type="text" id="stadium_id_field" name="stadium_id_field" value="' . esc_attr($stadium_id) . '" />';
}

function save_teams_meta_fields($post_id)
{
    $meta_fields = [
        '_team_id' => ['field' => 'team_id_field', 'nonce' => 'team_id_nonce'],
        '_stadium_id' => ['field' => 'stadium_id_field', 'nonce' => 'stadium_id_nonce'],
        '_country_id' => ['field' => 'country_id_field', 'nonce' => 'country_id_nonce']
    ];

    foreach ($meta_fields as $meta_key => $field_info) {
        if (!isset($_POST[$field_info['nonce']]) || !wp_verify_nonce($_POST[$field_info['nonce']], 'save_' . $meta_key)) {
            continue;
        }
        if (!current_user_can('edit_post', $post_id)) {
            continue;
        }
        if (isset($_POST[$field_info['field']])) {
            update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$field_info['field']]));
        } else {
            delete_post_meta($post_id, $meta_key);
        }
    }
}
add_action('save_post', 'save_teams_meta_fields');

function add_teams_meta_columns($columns)
{
    $columns['_team_id'] = 'Team ID';
    $columns['_stadium_id'] = 'Stadium ID';
    $columns['_country_id'] = 'Country ID';
    return $columns;
}
add_filter('manage_teams_posts_columns', 'add_teams_meta_columns');

function render_teams_meta_columns($column, $post_id)
{
    $meta_value = get_post_meta($post_id, $column, true);
    if ($meta_value) {
        echo esc_html($meta_value);
    }
}
add_action('manage_teams_posts_custom_column', 'render_teams_meta_columns', 10, 2);

// function remove_team_id_from_soccer_table($post_id)
// {
//     global $wpdb;

//     $post_type = get_post_type($post_id);

//     if ($post_type === 'football_team') {
//         $team_id = get_post_meta($post_id, '_team_id', true);

//         if ($team_id) {
//             $wpdb->update(
//                 'wp_soccer_teams',
//                 ['post_id' => null], // Удаляем связь с постом
//                 ['id' => $team_id],
//                 ['%d'],
//                 ['%s']
//             );
//         }
//     }
// }
// add_action('before_delete_post', 'remove_team_id_from_soccer_table');

function add_update_posts_button_teams($post_type)
{
    if ($post_type === 'football_team') { // Убедитесь, что тип записи совпадает
        ?>
        <div style="display: inline-block; margin-left: 10px;">
            <button id="update-teams-posts" class="button button-primary">
                Обновить посты
            </button>
            <span id="update-teams-status" style="margin-left: 10px; font-weight: bold;"></span>
        </div>

        <script type="text/javascript">
            document.getElementById('update-teams-posts').addEventListener('click', async function () {
                const statusEl = document.getElementById('update-teams-status');
                statusEl.textContent = 'Обновление...';

                try {
                    // Отправляем запрос
                    const response = await fetch(ajaxurl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'create_teams_posts',
                        }),
                    });

                    // Обрабатываем ответ
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
add_action('restrict_manage_posts', 'add_update_posts_button_teams');

function update_teams_posts_handler()
{
    // Проверяем права доступа пользователя
    if (!current_user_can('manage_options')) {
        wp_send_json_error('У вас нет прав для выполнения этого действия.');
    }

    global $wpdb;

    // Пример логики обновления записей
    $teams = get_posts([
        'post_type' => 'football_team',
        'post_status' => 'publish',
        'numberposts' => -1,
    ]);

    if (!empty($teams)) {
        foreach ($teams as $team) {
            // Здесь можно выполнить любое обновление записей
            // Например, синхронизацию с таблицей wp_soccer_teams
            $team_id = get_post_meta($team->ID, '_team_id', true);

            if ($team_id) {
                // Пример обновления поля в таблице wp_soccer_teams
                // $wpdb->update(
                //     'wp_soccer_teams',
                //     ['updated_at' => current_time('mysql')], // Пример обновления столбца
                //     ['id' => $team_id],
                //     ['%s'],
                //     ['%s']
                // );
            }
        }

        wp_send_json_success('Команды успешно обновлены.');
    } else {
        wp_send_json_error('Нет доступных команд для обновления.');
    }
}
add_action('wp_ajax_update_teams_posts', 'update_teams_posts_handler');

function add_teams_rewrite_rules()
{
    add_rewrite_rule(
        '^statistics/teams/([^/]+)/(live|results|squad)/?$',
        'index.php?post_type=football_team&name=$matches[1]&page_type=$matches[2]',
        'top'
    );
}
add_action('init', 'add_teams_rewrite_rules');

function add_teams_query_vars($vars)
{
    $vars[] = 'page_type';
    return $vars;
}
add_filter('query_vars', 'add_teams_query_vars');

function teams_template_loader($template)
{
    if (get_post_type() === 'football_team' && get_query_var('page_type')) {
        $custom_template = locate_template('footballteam-template.php');
        if ($custom_template) {
            return $custom_template;
        }
    }
    return $template;
}
add_filter('template_include', 'teams_template_loader');


add_action('save_post', 'update_related_data_on_post_update', 10, 3);

function update_related_data_on_post_update($post_id, $post, $update)
{
    // Проверяем, что это не автосохранение и пост типа 'football'
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if ($post->post_type !== 'football') {
        return;
    }

    global $wpdb;

    // Получаем новое название из поста
    $new_name_ru = $post->post_title;

    // 1. Обновляем данные в таблице wp_sport_competitions
    $competition_id = get_post_meta($post_id, '_competition_id', true);
    if ($competition_id) {
        $updated = $wpdb->update(
            'wp_sport_competitions',
            ['name_ru' => $new_name_ru],
            ['id' => $competition_id],
            ['%s'],
            ['%s']
        );
        if ($updated !== false) {
            error_log("Название соревнования с ID {$competition_id} обновлено на '{$new_name_ru}'.");
        } else {
            error_log("Ошибка обновления названия соревнования с ID {$competition_id}.");
        }
    }

    // 2. Обновляем данные в таблице wp_sport_country_data
    $country_id = get_post_meta($post_id, '_country_id', true);
    if ($country_id) {
        $updated = $wpdb->update(
            'wp_sport_country_data',
            ['name_ru' => $new_name_ru],
            ['id' => $country_id],
            ['%s'],
            ['%s']
        );
        if ($updated !== false) {
            error_log("Название страны с ID {$country_id} обновлено на '{$new_name_ru}'.");
        } else {
            error_log("Ошибка обновления названия страны с ID {$country_id}.");
        }
    }

    // 3. Обновляем данные в таблице wp_sport_category_data
    $category_id = get_post_meta($post_id, '_category_id', true);
    if ($category_id) {
        $updated = $wpdb->update(
            'wp_sport_category_data',
            ['name_ru' => $new_name_ru],
            ['id' => $category_id],
            ['%s'],
            ['%s']
        );
        if ($updated !== false) {
            error_log("Название категории с ID {$category_id} обновлено на '{$new_name_ru}'.");
        } else {
            error_log("Ошибка обновления названия категории с ID {$category_id}.");
        }
    }
}

add_action('save_post', 'update_team_name_on_post_update', 10, 3);

function update_team_name_on_post_update($post_id, $post, $update)
{
    // Проверяем, что это не автосохранение и пост типа 'football_team'
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if ($post->post_type !== 'football_team') {
        return;
    }

    global $wpdb;

    // Получаем новое название из поста
    $new_name_ru = $post->post_title;

    // Проверяем наличие метаполя _team_id
    $team_id = get_post_meta($post_id, '_team_id', true);
    if ($team_id) {
        // Обновляем запись в таблице wp_soccer_teams
        $updated = $wpdb->update(
            'wp_soccer_teams',
            ['name_ru' => $new_name_ru], // Новое название
            ['id' => $team_id],         // Условие - ID команды
            ['%s'],                     // Формат данных
            ['%s']                      // Формат условия
        );

        if ($updated !== false) {
            error_log("Название команды с ID {$team_id} обновлено на '{$new_name_ru}'.");
        } else {
            error_log("Ошибка обновления названия команды с ID {$team_id}.");
        }
    }
}
