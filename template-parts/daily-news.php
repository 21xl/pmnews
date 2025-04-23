<?php
$today = current_time('Y-m-d');

$args = array(
    'post_type' => 'post',
    'posts_per_page' => -1,
    'date_query' => array(
        array(
            'after' => $today . ' 00:00:00',
            'before' => $today . ' 23:59:59',
            'inclusive' => true,
        ),
    ),
);

$query = new WP_Query($args);

if ($query->have_posts()): ?>
    <section class="daily-news">
        <div class="daily-news__wrapper wrapper">
            <?php $title = get_sub_field('title');

            if ($title): ?>
                <h2 class="daily-news__title">
                    <?php echo esc_html($title); ?>
                </h2>
            <?php endif; ?>

            <div class="daily-swiper daily-news__list">
                <div class="swiper-wrapper">
                    <?php
                    $current_time = current_time('timestamp');
                    while ($query->have_posts()):
                        $query->the_post();
                        $title = get_the_title();
                        $categories = get_the_terms(get_the_ID(), 'category');
                        $post_tag = '';
                        if (!empty($categories) && !is_wp_error($categories)) {
                            $post_tag = esc_html($categories[0]->name);
                            $category_link = get_term_link($categories[0]->term_id, 'category');
                        }
                        $image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full')[0];
                        $link = get_the_permalink();
                        $post_time = get_the_time('U');
                        $time = translate_human_time_diff($post_time, $current_time);

                        if (date('Y-m-d', $post_time) === $today): ?>
                            <div class="swiper-slide daily-news__card">
                                <?php if ($post_tag && !is_wp_error($category_link)): ?>
                                    <a href="<?php echo esc_url($category_link); ?>" class="daily-news__slide-tag">
                                        <?php echo esc_html($post_tag); ?>
                                    </a>
                                <?php endif; ?>
                                <a href="<?php echo esc_url($link); ?>" class="daily-news__slide">
                                    <div class="daily-news__content">
                                        <h2 class="daily-news__slide-title">
                                            <?php echo esc_html($title); ?>
                                        </h2>

                                        <span class="daily-news__slide-time">
                                            <?php echo esc_html($time); ?>
                                            <?php pll_e('ago'); ?>
                                        </span>

                                        <span class="daily-news__slide-btn"> <?php pll_e('More details'); ?></span>
                                    </div>

                                    <?php if ($image): ?>
                                        <div class="daily-news__slide-img">
                                            <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>"
                                                loading="lazy">

                                        </div>
                                    <?php else: ?>
                                        <div class="daily-news__slide-img daily-news__slide-img--error">
                                            <img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/placeholder-daily.webp'); ?>"
                                                alt="<?php echo esc_attr($title); ?>" loading="lazy">
                                        </div>
                                    <?php endif; ?>

                                </a>
                            </div>

                        <?php endif;
                    endwhile; ?>
                </div>
                <?php if ($query->post_count > 1): ?>
                    <div class="swiper-button-prev daily-news__prev"></div>
                    <div class="swiper-button-next daily-news__next"></div>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php endif;
wp_reset_postdata();
?>