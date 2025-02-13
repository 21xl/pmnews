<?php
add_action('wp_head', function () {
    // Если это архив категории, тега или таксономии
    if (is_category() || is_tag() || is_tax()) {
        $canonical_url = get_category_link(get_queried_object_id()); // Ссылка на текущую категорию или тег

    } elseif (is_singular()) {
        // Для постов и страниц
        $canonical_url = get_permalink(); // Ссылка на пост или страницу

        $custom_page = get_query_var('custom_page');

        // Если есть параметр custom_page
        if ($custom_page && in_array($custom_page, ['results', 'standings', 'live'])) {
            $base_url = get_permalink();
            $canonical_url = user_trailingslashit(
                trailingslashit($base_url) . $custom_page
            );
        }

    } elseif (is_home()) {
        // Для главной страницы блога
        $canonical_url = get_home_url(); // Ссылка на главную страницу

    } elseif (is_paged()) {
        // Для архивов с пагинацией
        $canonical_url = get_pagenum_link(1); // Ссылка на первую страницу пагинации

    } else {
        // Для всех остальных страниц
        $canonical_url = get_permalink();
    }

    // Выводим каноническую ссылку
    echo '<link rel="canonical" href="' . esc_url($canonical_url) . '" />' . "\n";
}, 5);

// Добавляем параметр custom_page в разрешённые query vars
add_filter('query_vars', function ($vars) {
    $vars[] = 'custom_page';
    return $vars;
});
