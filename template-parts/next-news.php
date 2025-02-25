<?php
$current_categories = get_the_category();
$category_ids = array();
$next_post_id = 0;

if (!empty($current_categories) && is_array($current_categories)) {
    foreach ($current_categories as $category) {
        if (isset($category->term_id)) {
            $category_ids[] = $category->term_id;
        }
    }
}

if (!empty($category_ids)) {
    $prev_post_query = new WP_Query(array(
        'category__in' => $category_ids,
        'posts_per_page' => 1,
        'post__not_in' => array(get_the_ID()), // Исключаем текущий пост
        'orderby' => 'date',
        'order' => 'DESC',
        'date_query' => array(
            array(
                'before' => get_the_date('Y-m-d H:i:s'), // Показать только посты до текущего
                'inclusive' => false
            )
        )
    ));

    if ($prev_post_query->have_posts()) {
        while ($prev_post_query->have_posts()) {
            $prev_post_query->the_post();
            ?>
            <div class="single-aside__next">
                <span class="single-aside__next-title">
                    <?php pll_e('Next article'); ?>
                </span>

                <?php get_template_part('template-parts/card'); ?>
            </div>
            <?php
        }
        wp_reset_postdata();
    }
}


// Recommendation Section

$current_post_id = get_the_ID();
$exclude_posts = array($current_post_id);
if ($next_post_id) {
    $exclude_posts[] = $next_post_id;
}

$popularpost = new WP_Query(array(
    'posts_per_page' => 9,
    'meta_key' => 'post_views_count',
    'orderby' => 'meta_value_num',
    'order' => 'DESC',
    'post__not_in' => $exclude_posts,
));

if ($popularpost->have_posts()): ?>
    <div class="single__recommendation swiper">
        <h2 class="single__recommendation-title">
            <?php pll_e('Recommended'); ?>
        </h2>

        <div class="swiper-wrapper">
            <?php while ($popularpost->have_posts()):
                $popularpost->the_post();
                $post_tags = get_the_tags();
                ?>

                <div class="single__recommendation-item swiper-slide 
                    <?php echo empty($post_tags) ? 'single__recommendation-item--without-tags' : ''; ?>">

                    <?php if (!empty($post_tags)):
                        shuffle($post_tags);
                        $random_tags = array_slice($post_tags, 0, 3); ?>

                        <div class="single__recommendation-tags">
                            <?php foreach ($random_tags as $tag): ?>
                                <span class="tag-item">
                                    <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>">
                                        <?php echo esc_html($tag->name); ?>
                                    </a>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <a href="<?php echo esc_url(get_the_permalink()); ?>" class="single__recommendation-link">
                        <div class="single__recommendation-numbers">
                            <span class="single__recommendation-date"><?php echo esc_html(get_the_date('d.m.Y')); ?></span>
                            <span class="card__separator">•</span>
                            <span class="single__recommendation-time">
                                <?php echo esc_html(get_reading_time()); ?>
                            </span>
                        </div>
                        <h3 class="single__recommendation-item--title">
                            <?php echo esc_html(get_the_title()); ?>
                        </h3>
                    </a>
                </div>
            <?php endwhile;
            wp_reset_postdata(); ?>
        </div>

        <div class="single__recommendation-navigation">
            <div class="single__recommendation__prev"></div>
            <div class="single__recommendation__pagination"></div>
            <div class="single__recommendation__next"></div>
        </div>
    </div>
<?php endif; ?>