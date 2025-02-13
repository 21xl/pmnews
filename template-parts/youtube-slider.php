<?php
$youtube_link = get_field('youtube_link', 'options');
?>

<section class="youtube-slider">
    <div class="youtube-slider__wrapper wrapper">
        <div class="youtube-slider__swiper">
            <div class="youtube-slider__top swiper-wrapper ">
                <?php while (have_rows('video_list')):
                    the_row();
                    $video = get_sub_field('video');
                    ?>

                    <div class="youtube-slider__slide swiper-slide" data-embed="<?php echo esc_url($video); ?>">
                        <div class="youtube-thumbnail"
                            style="background-image: url('<?php echo esc_url(get_template_directory_uri() . '/src/img/placeholder-card.webp') ?>');">
                            <div class="play-button"></div>
                        </div>
                    </div>

                <?php endwhile; ?>
            </div>

            <div class="youtube-slider__pagination youtube-slider__pagination--desktop">
                <div class="youtube-slider__prev swiper-button-prev"></div>
                <div class="youtube-slider__next swiper-button-next"></div>
            </div>
        </div>

        <a href="<?php echo esc_url($youtube_link) ?>" class="youtube-slider__bottom" rel="nofollow noopener"
            target="_blank">
            <div class="youtube-slider__text">
                <h2 class="youtube-slider__title">
                    <?php pll_e('Самые свежие новости'); ?>
                </h2>


                <p class="youtube-slider__desc">
                    <?php pll_e('YouTube канал SportPulse - ваш надёжный источник свежих новостей из мира спорта!'); ?>
                </p>
            </div>

            <div class="youtube-slider__img">
            </div>
        </a>

        <div class="youtube-slider__pagination youtube-slider__pagination--mobile">
            <div class="youtube-slider__prev swiper-button-prev"></div>
            <div class="youtube-slider__next swiper-button-next"></div>
        </div>
    </div>
</section>