<?php
add_action('wp_head', function () {

    // Категории, теги, таксономии
    if (is_category() || is_tag() || is_tax()) {
        $canonical_url = get_category_link(get_queried_object_id());

        // Страницы с шаблоном footballteam-template.php
    } elseif (is_page_template('footballteam-template.php')) {
        $page_type = trim(get_query_var('page_type'));

        if ($page_type === 'live') {
            $canonical_url = trailingslashit(get_permalink()) . 'live/';
        } elseif ($page_type === 'results') {
            $canonical_url = trailingslashit(get_permalink()) . 'results/';
        } elseif ($page_type === 'squad') {
            $canonical_url = trailingslashit(get_permalink()) . 'squad/';
        } else {
            $canonical_url = get_permalink();
        }

        // Обычные записи и страницы
    } elseif (is_singular()) {
        $canonical_url = get_permalink();

        $custom_page = get_query_var('custom_page');
        if ($custom_page && in_array($custom_page, ['results', 'standings', 'live'])) {
            $canonical_url = trailingslashit(get_permalink()) . trailingslashit($custom_page);
        }

        // Главная страница блога
    } elseif (is_home()) {
        $canonical_url = get_home_url();

        // Пагинация
    } elseif (is_paged()) {
        $canonical_url = get_pagenum_link(1);

        // Остальное
    } else {
        $canonical_url = get_permalink();
    }

    echo '<link rel="canonical" href="' . esc_url($canonical_url) . '" />' . "\n";
}, 5);

// Регистрируем переменные custom_page и page_type
add_filter('query_vars', function ($vars) {
    $vars[] = 'custom_page';
    $vars[] = 'page_type';
    return $vars;
});
