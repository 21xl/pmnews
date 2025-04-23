<div class="statistics-category__links links">
    <div class="links__list">
        <?php
        $rating_query = new WP_Query([
            'post_type' => 'tennis_rating',
            'posts_per_page' => -1, // Все посты
            'post_status' => 'publish',
            'orderby' => 'post_id',
            'order' => 'ASC',
        ]);

        // Получаем ID текущего поста
        $current_post_id = get_the_ID();

        if ($rating_query->have_posts()) {
            while ($rating_query->have_posts()) {
                $rating_query->the_post();
                $post_id = get_the_ID();
                $seo_title = get_post_meta($post_id, '_seo_title', true);
                $title = $seo_title ?: get_the_title();
                $permalink = get_permalink();

                // Проверяем, совпадает ли ID текущего поста с ID поста в цикле
                $is_active = ($current_post_id === $post_id) ? 'active' : '';
                ?>
                <a href="<?= $permalink ?>" class="links__item <?= $is_active ?>">
                    <span><?= $title ?></span>
                </a>
            <?php }
            wp_reset_postdata();
        }
        ?>
    </div>
</div>