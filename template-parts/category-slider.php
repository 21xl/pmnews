<section class="category-slider">
    <div class="category-slider__wrapper wrapper">
        <div class="category-slider__slider">
            <?php if (have_rows('category_slider_list')): ?>
                <?php while (have_rows('category_slider_list')):
                    the_row();
                    $category = get_sub_field('news');
                    if (isset($category) && !empty($category)) {
                        $category_name = esc_html($category->name ?? '');

                        $today = date('Y-m-d');
                        $args_today = [
                            'post_type' => 'post',
                            'posts_per_page' => -1,
                            'tax_query' => [
                                [
                                    'taxonomy' => 'category',
                                    'field' => 'term_id',
                                    'terms' => $category->term_id ?? 0,
                                ],
                            ],
                            'date_query' => [
                                [
                                    'after' => $today . ' 00:00:00',
                                    'before' => $today . ' 23:59:59',
                                    'inclusive' => true,
                                ],
                            ],
                            'orderby' => 'date',
                            'order' => 'DESC',
                        ];

                        $posts_today = new WP_Query($args_today);

                        $args_future = [
                            'post_type' => 'post',
                            'posts_per_page' => -1,
                            'tax_query' => [
                                [
                                    'taxonomy' => 'category',
                                    'field' => 'term_id',
                                    'terms' => $category->term_id ?? 0,
                                ],
                            ],
                            'date_query' => [
                                [
                                    'after' => $today . ' 23:59:59',
                                ],
                            ],
                            'orderby' => 'date',
                            'order' => 'ASC',
                        ];

                        $posts_future = new WP_Query($args_future);
                        ?>
                        <div class="category-slider__inner swiper" id="category-slider-<?php echo uniqid(); ?>">

                            <h2 class="category-slider__title">
                                <?php echo $category_name; ?>
                            </h2>

                            <div class="swiper-wrapper">
                                <?php while ($posts_today->have_posts()):
                                    $posts_today->the_post();
                                    $post_categories = get_the_category();
                                    $belongs_to_parent = false;
                                    $post_child_categories = [];

                                    foreach ($post_categories as $post_category) {
                                        if ($post_category->term_id === $category->term_id || $post_category->parent === $category->term_id) {
                                            $belongs_to_parent = true;
                                            if ($post_category->parent === $category->term_id) {
                                                $post_child_categories[] = $post_category;
                                            }
                                        }
                                    }

                                    if ($belongs_to_parent): ?>
                                        <div
                                            class="category-slider-item swiper-slide <?php echo empty($post_child_categories) ? 'category-slider-item--without-tags' : ''; ?>">
                                            <?php if (!empty($post_child_categories)): ?>
                                                <div class="category-slider-tags">
                                                    <?php foreach ($post_child_categories as $post_child_category): ?>
                                                        <span class="tag-item">
                                                            <a href="<?php echo esc_url(get_term_link($post_child_category->term_id)); ?>">
                                                                <?php echo esc_html($post_child_category->name ?? ''); ?>
                                                            </a>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>

                                            <a href="<?php echo esc_url(get_the_permalink()); ?>" class="category-slider-link">
                                                <div class="category-slider-numbers">
                                                    <span class="category-slider-date"><?php echo esc_html(get_the_date('d.m.Y')); ?></span>
                                                    <span class="card__separator">•</span>
                                                    <span class="category-slider-time">
                                                        <?php echo esc_html(get_reading_time()); ?>
                                                    </span>
                                                </div>
                                                <h3 class="category-slider-item--title">
                                                    <?php
                                                    $title = get_the_title();
                                                    $title = str_replace(": смотреть онлайн прямой эфир", "", $title);
                                                    echo esc_html($title);
                                                    ?>
                                                </h3>
                                            </a>
                                        </div>
                                    <?php endif;
                                endwhile;
                                wp_reset_postdata();
                                wp_reset_query();
                                ?>

                                <?php while ($posts_future->have_posts()):
                                    $posts_future->the_post();
                                    $post_categories = get_the_category();
                                    $belongs_to_parent = false;
                                    $post_child_categories = [];

                                    foreach ($post_categories as $post_category) {
                                        if ($post_category->term_id === $category->term_id || $post_category->parent === $category->term_id) {
                                            $belongs_to_parent = true;
                                            if ($post_category->parent === $category->term_id) {
                                                $post_child_categories[] = $post_category;
                                            }
                                        }
                                    }

                                    if ($belongs_to_parent): ?>
                                        <div
                                            class="category-slider-item swiper-slide <?php echo empty($post_child_categories) ? 'category-slider-item--without-tags' : ''; ?>">
                                            <?php if (!empty($post_child_categories)): ?>
                                                <div class="category-slider-tags">
                                                    <?php foreach ($post_child_categories as $post_child_category): ?>
                                                        <span class="tag-item">
                                                            <a href="<?php echo esc_url(get_term_link($post_child_category->term_id)); ?>">
                                                                <?php echo esc_html($post_child_category->name ?? ''); ?>
                                                            </a>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>

                                            <a href="<?php echo esc_url(get_the_permalink()); ?>" class="category-slider-link">
                                                <div class="category-slider-numbers">
                                                    <span class="category-slider-date"><?php echo esc_html(get_the_date('d.m.Y')); ?></span>
                                                    <span class="card__separator">•</span>
                                                    <span class="category-slider-time">
                                                        <?php echo esc_html(get_reading_time()); ?>
                                                    </span>
                                                </div>
                                                <h3 class="category-slider-item--title">
                                                    <?php
                                                    $title = get_the_title();
                                                    $title = str_replace(": смотреть онлайн прямой эфир", "", $title);
                                                    echo esc_html($title);
                                                    ?>
                                                </h3>
                                            </a>
                                        </div>
                                    <?php endif;
                                endwhile;
                                wp_reset_postdata();
                                wp_reset_query();
                                ?>
                            </div>

                            <div class="category-slider-navigation">
                                <div class="category-slider__prev"></div>
                                <div class="category-slider__pagination"></div>
                                <div class="category-slider__next"></div>
                            </div>
                        </div>
                    <?php } ?>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</section>