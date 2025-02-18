<div class="breadcrumbs">
    <div class="breadcrumbs__wrapper wrapper">
        <nav class="breadcrumbs">
            <?php global $post; ?>

            <!-- Главная страница -->
            <a href="<?php echo home_url(); ?>" class="breadcrumbs__main"><span>
                    <?php pll_e('Home'); ?></span></a>

            <?php
            // Для одиночных записей
            if (is_single()) {
                $post_type = get_post_type();

                if ($post_type != 'post') {
                    $post_type_object = get_post_type_object($post_type);
                    $post_type_archive = get_post_type_archive_link($post_type);
                    ?>
                    <a href="<?php echo $post_type_archive; ?>">
                        <span>
                            <?php echo $post_type_object->labels->singular_name; ?>
                        </span>
                    </a>
                    <?php
                }

                $terms = get_the_terms($post->ID, 'category');
                if (!$terms || is_wp_error($terms)) {
                    $terms = get_the_terms($post->ID, 'rubrics');
                }

                if ($terms && !is_wp_error($terms)) {
                    $term = array_shift($terms);
                    $parent_terms = get_term_parents_list($term->term_id, $term->taxonomy, ['separator' => ' ', 'link' => true]);
                    $parent_terms = preg_replace('/<a([^>]+)>([^<]+)<\/a>/', '<a$1><span>$2</span></a>', $parent_terms);
                    echo $parent_terms;
                }
                ?>
                <span class="breadcrumbs__current"><?php echo get_the_title(); ?></span>
            <?php } ?>

            <?php if (is_page() && !is_front_page()) {
                $parents = get_post_ancestors($post->ID);
                if ($parents) {
                    foreach (array_reverse($parents) as $parent_id) { ?>
                        <a href="<?php echo get_permalink($parent_id); ?>"><span><?php echo get_the_title($parent_id); ?></span></a>
                    <?php }
                } ?>
                <span class="breadcrumbs__current"><?php echo get_the_title(); ?></span>
            <?php } ?>

            <?php if (is_tax() || is_category() || is_tag()) {
                $term = get_queried_object();
                if ($term->parent != 0) {
                    $parent_terms = get_term_parents_list($term->parent, $term->taxonomy, ['separator' => ' ', 'link' => true]);
                    $parent_terms = preg_replace('/<a([^>]+)>([^<]+)<\/a>/', '<a$1><span>$2</span></a>', $parent_terms);
                    echo $parent_terms;
                } ?>
                <span class="breadcrumbs__current"><?php echo single_term_title('', false); ?></span>
            <?php } ?>

            <?php if (is_post_type_archive()) { ?>
                <span class="breadcrumbs__current"><?php echo post_type_archive_title('', false); ?></span>
            <?php } ?>

            <?php if (is_search()) { ?>
                <span class="breadcrumbs__current">Результаты поиска для
                    "<span><?php echo get_search_query(); ?></span>"</span>
            <?php } ?>

            <?php if (is_404()) { ?>
                <span class="breadcrumbs__current">Страница не найдена</span>
            <?php } ?>

            <?php if (is_author()) {
                $author = get_queried_object(); ?>
                <span class="breadcrumbs__current"><?php echo esc_html($author->display_name); ?></span>
            <?php } ?>
        </nav>
    </div>
</div>