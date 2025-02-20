<?php

function custom_breadcrumbs()
{
    global $post;

    // Главная страница
    echo '<nav class="breadcrumbs"><a href="' . home_url() . '" class="breadcrumbs__main"><span>Главная</span></a>';

    // Для одиночных записей
    if (is_single()) {
        $post_type = get_post_type();

        if ($post_type != 'post') {
            // Добавляем ссылку на архив типа записи
            $post_type_object = get_post_type_object($post_type);
            $post_type_archive = get_post_type_archive_link($post_type);
            echo ' <a href="' . $post_type_archive . '"><span>' . $post_type_object->labels->singular_name . '</span></a>';
        }

        // Получаем категории или рубрики
        $terms = get_the_terms($post->ID, 'category');
        if (!$terms || is_wp_error($terms)) {
            $terms = get_the_terms($post->ID, 'rubrics');
        }

        if ($terms && !is_wp_error($terms)) {
            $term = array_shift($terms);
            $parent_terms = get_term_parents_list($term->term_id, $term->taxonomy, ['separator' => ' ', 'link' => true]);

            // Используем регулярное выражение для оборачивания текста внутри <a> в <span>
            $parent_terms = preg_replace('/<a([^>]+)>([^<]+)<\/a>/', '<a$1><span>$2</span></a>', $parent_terms);

            echo ' ' . rtrim($parent_terms, ' ');
        }

        // Текущий пост
        echo ' <span class="breadcrumbs__current">' . get_the_title() . '</span>';
    }

    // Для страниц (не главная)
    if (is_page() && !is_front_page()) {
        $parents = get_post_ancestors($post->ID);
        if ($parents) {
            foreach (array_reverse($parents) as $parent_id) {
                echo ' <a href="' . get_permalink($parent_id) . '"><span>' . get_the_title($parent_id) . '</span></a>';
            }
        }
        // Текущая страница
        echo ' <span class="breadcrumbs__current">' . get_the_title() . '</span>';
    }

    // Для таксономий, категорий и тегов
    if (is_tax() || is_category() || is_tag()) {
        $term = get_queried_object();

        // Родительские таксономии
        if ($term->parent != 0) {
            $parent_terms = get_term_parents_list($term->parent, $term->taxonomy, ['separator' => ' ', 'link' => true]);

            // Используем регулярное выражение для оборачивания текста внутри <a> в <span>
            $parent_terms = preg_replace('/<a([^>]+)>([^<]+)<\/a>/', '<a$1><span>$2</span></a>', $parent_terms);

            echo ' ' . rtrim($parent_terms, ' ');
        }

        // Текущая таксономия
        echo ' <span class="breadcrumbs__current">' . single_term_title('', false) . '</span>';
    }

    // Архив типа записи
    if (is_post_type_archive()) {
        echo ' <span class="breadcrumbs__current">' . post_type_archive_title('', false) . '</span>';
    }

    // Результаты поиска
    if (is_search()) {
        echo '<span class="breadcrumbs__current">';
        pll_e('Search results');
        echo ' "<span>' . get_search_query() . '</span>"</span>';
    }


    // Страница 404
    if (is_404()) {
        echo ' <span class="breadcrumbs__current">Page Not Found</span>';
    }

    // Страница автора
    if (is_author()) {
        $author = get_queried_object();
        echo ' <span class="breadcrumbs__current">' . esc_html($author->display_name) . '</span>';
    }

    echo '</nav>';
}
