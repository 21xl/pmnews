<?php if (have_rows('hero_news')): ?>
    <section class="hero">
        <div class="hero__swipper swiper wrapper">
            <div class="swiper-wrapper">
                <?php
                $slide_count = 0;
                while (have_rows('hero_news')):
                    the_row();
                    $post_object = get_sub_field('news');
                    if ($post_object):
                        $slide_count++;
                        $post = $post_object;
                        setup_postdata($post);

                        $title = get_the_title() ? esc_html(get_the_title()) : '';
                        $img_id = get_post_thumbnail_id();
                        $img_url = get_the_post_thumbnail_url(null, 'medium_large');
                        $img_srcset = wp_get_attachment_image_srcset($img_id, 'full');
                        $img_alt = get_post_meta($img_id, '_wp_attachment_image_alt', true);
                        $link = get_the_permalink();
                        $placeholder_url = esc_url(get_template_directory_uri() . '/src/img/placeholder-hero.webp');
                        ?>

                        <div class="swiper-slide hero__slide">
                            <div class="hero__slide-content">
                                <h2 class="hero__title">
                                    <?php echo esc_html($title); ?>
                                </h2>

                                <?php if (get_the_content()): ?>
                                    <p class="hero__text">
                                        <?php echo esc_html(wp_strip_all_tags(get_the_content())); ?>
                                    </p>
                                <?php endif; ?>

                                <a href="<?php echo esc_url($link); ?>" class="hero__link">
                                    <?php pll_e('Детали'); ?>
                                </a>
                            </div>

                            <div class="hero__img">
                                <img src="<?php echo esc_url($img_url ? $img_url : $placeholder_url); ?>"
                                    srcset="<?php echo esc_attr($img_srcset ?: ''); ?>"
                                    sizes="(max-width: 600px) 100vw, (max-width: 1200px) 50vw, 33vw"
                                    alt="<?php echo esc_attr($img_alt ?: $title); ?>" loading="lazy">
                            </div>
                        </div>

                        <?php wp_reset_postdata(); ?>
                    <?php endif; ?>
                <?php endwhile; ?>
            </div>

            <?php if ($slide_count > 1): ?>
                <div class="hero__navigation">
                    <div class="hero__navigation-next swiper-button-next "></div>
                    <div class="hero__navigation-prev swiper-button-prev"></div>
                    <div class="hero__navigation-pagination swiper-pagination">
                        <?php for ($i = 1; $i <= $slide_count; $i++): ?>
                            <span class="hero__navigation-bullet swiper-pagination-bullet">
                                <span class="hero__navigation-counter swiper-counter">
                                    <?php echo esc_html($i); ?>
                                </span>
                            </span>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>