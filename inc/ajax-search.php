<?php
function search_ajax_handler()
{
    if (empty($_POST['query'])) {
        wp_send_json_error('No search query provided');
        wp_die();
    }

    $query = sanitize_text_field($_POST['query']);
    if (strlen($query) > 100) {
        wp_send_json_error('Search query is too long');
        wp_die();
    }

    $unique_token = uniqid('search_', true);
    $results = [];
    $total_results = 0;
    $output = '';
    $current_lang = pll_current_language();

    // Поиск по постам
    $post_args = array(
        'post_type' => array('post'),
        'posts_per_page' => -1,
        'post_status' => 'publish',
        's' => $query,
        'lang' => $current_lang,
        // 'fields' => 'ids',
    );

    $post_query = new WP_Query($post_args);
    if ($post_query->have_posts()) {
        foreach ($post_query->posts as $post_id) {
            $results[] = array(
                'title' => esc_html(get_the_title($post_id)),
                'url' => esc_url(get_permalink($post_id)),
                'type' => esc_html(get_post_type($post_id)),
                'categories' => wp_kses_post(get_the_tag_list('', ' ', '', $post_id)),
            );
            $total_results++;
        }
    }
    wp_reset_postdata();

    // Поиск по категориям
    $category_results = get_terms(array(
        'taxonomy' => 'category',
        'search' => $query,
        'hide_empty' => true,
        'lang' => $current_lang,
        // 'suppress_filters' => false
    ));

    if (!empty($category_results)) {
        $total_results += count($category_results);
        $output .= '<p class="resluting-search__subtitle">' . esc_html(pll__('Categories')) . ' (' . count($category_results) . ')</p>';
        $output .= '<div class="modal-search__categories">';

        foreach ($category_results as $category) {
            $output .= '<a href="' . esc_url(get_term_link($category)) . '" class="resluting-search__category">' . esc_html($category->name) . '</a>';
        }
        $output .= '</div>';
    }

    // Поиск по тегам
    $tag_results = get_terms(array(
        'taxonomy' => 'post_tag',
        'search' => $query,
        'hide_empty' => true,
        'lang' => $current_lang,

    ));

    $total_tags = count($tag_results);
    if ($total_tags > 0) {
        $total_results += $total_tags;
        $output .= '<p class="resluting-search__subtitle">' . esc_html(pll__('Tags')) . ' (' . $total_tags . ')</p>';
        $output .= '<ul class="modal-search__tags">';

        foreach (array_slice($tag_results, 0, 5) as $tag) {
            $output .= '<li><a href="' . esc_url(get_term_link($tag)) . '" class="tags__item">' . esc_html($tag->name) . '</a></li>';
        }
        $output .= '</ul>';
    }

    $user_args = array(
        'search' => '*' . esc_attr($query) . '*',
        'search_columns' => array('display_name'),
    );
    $user_results = get_users($user_args);
    $filtered_users = array_filter($user_results, function ($user) use ($current_lang) {
        return get_user_meta($user->ID, 'lang', true) === $current_lang;
    });

    $total_users = count($filtered_users);
    if ($total_users > 0) {
        $total_results += $total_users;
        $output .= '<p class="resluting-search__subtitle">' . esc_html(pll__('Authors')) . ' (' . $total_users . ')</p>';
        $output .= '<div class="resluting-search__people">';

        foreach (array_slice($filtered_users, 0, 5) as $user) {
            $author_avatar = get_field('author_avatar', 'user_' . $user->ID);
            $author_position = get_field('author_position', 'user_' . $user->ID);

            $avatar_html = $author_avatar
                ? '<img src="' . esc_url($author_avatar) . '" alt="' . esc_attr($user->display_name) . '" />'
                : '<img src="' . esc_url(get_template_directory_uri() . '/src/img/placeholder-daily.webp') . '" alt="' . esc_attr($user->display_name) . '" loading="lazy" />';

            $output .= '<div class="resluting-search__people--human">';
            $output .= '<a href="' . esc_url(get_author_posts_url($user->ID)) . '" class="resluting-search__people--wrapper">';
            $output .= $avatar_html;
            $output .= '<div class="author-info">';
            $output .= '<p>' . esc_html($user->display_name) . '</p>';
            $output .= '<p class="user-role">' . esc_html($author_position ? $author_position : 'Author') . '</p>';
            $output .= '</div>';
            $output .= '</a>';
            $output .= '</div>';
        }
        $output .= '</div>';
    }

    // Поиск по страницам
    $page_args = array(
        'post_type' => 'page',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        's' => $query,
        'fields' => 'ids',
        'lang' => $current_lang,
    );

    $page_query = new WP_Query($page_args);
    if ($page_query->have_posts()) {
        $total_results += $page_query->found_posts;
        $output .= '<p class="resluting-search__subtitle">' . esc_html(pll__('Pages')) . ' (' . $page_query->found_posts . ')</p>';
        $output .= '<div class="modal-search__pages">';

        foreach ($page_query->posts as $post_id) {
            $output .= '<a href="' . esc_url(get_permalink($post_id)) . '" class="resluting-search__page">';
            $output .= esc_html(get_the_title($post_id));
            $output .= '</a>';
        }
        $output .= '</div>';
    }
    wp_reset_postdata();

    $search_query = '?s=' . urlencode($query);
    $view_all_url = home_url(($current_lang !== pll_default_language() ? "/$current_lang/" : '') . $search_query);
    $view_all_button = '<a href="' . esc_url($view_all_url) . '" class="view-all-results">' . esc_html(pll__('View all results')) . ' (' . $total_results . ')</a>';

    wp_send_json(array(
        'html' => $output,
        'total' => $total_results,
        'view_all_button' => $view_all_button,
        'token' => $unique_token,
    ));
    wp_die();
}

add_action('wp_ajax_search_ajax', 'search_ajax_handler');
add_action('wp_ajax_nopriv_search_ajax', 'search_ajax_handler');
