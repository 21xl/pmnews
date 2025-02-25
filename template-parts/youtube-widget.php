<?php
$term = get_queried_object();
$youtube_bg = get_field('youtube_widget_bg');
$youtube_link = get_field('youtube_link', 'options');
?>

<?php if (have_rows('youtube_widget_list', $term)): ?>
    <section class="youtube-widget">
        <div class="youtube-widget__wrapper wrapper">
            <div class="youtube-widget__swiper">
                <div class="youtube-widget__top swiper-wrapper ">
                    <?php while (have_rows('youtube_widget_list', $term)):
                        the_row();
                        $video = get_sub_field('video');
                        ?>

                        <div class="youtube-widget__slide swiper-slide" data-embed="<?php echo esc_url($video); ?>">

                            <div class="youtube-thumbnail"
                                style="background-image: url('<?php echo esc_url(get_template_directory_uri() . '/src/img/placeholder-card.webp') ?>');">
                                <div class="play-button"></div>
                            </div>
                        </div>

                    <?php endwhile; ?>
                </div>

                <div class="youtube-widget__pagination youtube-widget__pagination--desktop">
                    <div class="youtube-widget__prev swiper-button-prev"></div>
                    <div class="youtube-widget__next swiper-button-next"></div>
                </div>
            </div>


            <a href="<?php echo esc_url($youtube_link) ?>" class="youtube-widget__bottom" rel="nofollow noopener"
                target="_blank">
                <div class="youtube-widget__text">
                    <h2 class="youtube-widget__title">
                        <?php pll_e('Самые свежие новости'); ?>
                    </h2>

                    <p class="youtube-widget__desc">
                        <?php pll_e('YouTube канал SportPulse - ваш надёжный источник свежих новостей из мира спорта!'); ?>
                    </p>
                </div>

                <div class="youtube-widget__img">
                </div>
            </a>

            <div class="youtube-widget__pagination youtube-widget__pagination--mobile">
                <div class="youtube-widget__prev swiper-button-prev"></div>
                <div class="youtube-widget__next swiper-button-next"></div>
            </div>
        </div>
    </section>
<?php endif; ?>