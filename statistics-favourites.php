<?php
/*
Template Name: Избранное - Статистика
*/

get_template_part('head');
$banner = get_field('ad_statistics', 'option');
$ad = get_field('ad_category', 'options');
?>

<div class="page">
    <?php get_template_part('template-parts/aside') ?>

    <div class="content">
        <?php get_header('white'); ?>

        <main>

            <div class="container">
                <section class="statistics">
                    <div class="statistics__wrapper wrapper">

                        <div class="statistics__head">
                            <h1 class="statistics__title">
                                <?php echo esc_html(get_the_title()); ?>
                            </h1>

                            <?php get_template_part('template-parts/sport/statistics-type-tabs') ?>
                        </div>

                        <div class="statistics__mobile-sidebar">
                            <span>All leagues</span>
                        </div>

                        <?php get_template_part('template-parts/sport-quiz') ?>

                        <div class="statistics__main">
                            <?php get_template_part('template-parts/sport/statistics-sidebar', null, ['page_data' => 'main']) ?>

                            <?php get_template_part('template-parts/sport/statistics-favourites') ?>

                            <?php if ($ad):
                                $ad_link = isset($ad['link']) ? esc_url($ad['link']) : '';
                                $ad_img = isset($ad['image']) ? esc_url($ad['image']) : '';

                                if ($ad_link && $ad_img): ?>
                                    <div class="statistics__ad">
                                        <a href="<?php echo esc_url($ad_link); ?>" class="statistics__ad-wrapper"
                                            style="background-image:url('<?php echo esc_url($ad_img); ?>')"
                                            rel="nofollow noopener" target="_blank">
                                        </a>
                                    </div>
                                <?php endif;
                            endif; ?>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
</div>

<script src="<?php echo get_template_directory_uri() . '/sport/football/js/fav/index.min.js' ?>"></script>

<?php get_footer(); ?>