<?php
/*
Template Name: Football category
Template Post Type: Football
*/

global $custom_meta_title;
global $custom_meta_description;

$custom_meta_title = get_the_title() . ': Расписание игр и Календарь матчей';
$custom_meta_description = 'Узнайте расписание игр и календарь матчей ' . get_the_title() . '. Не пропустите самые важные игры сезона! Следите за матчами легко и удобно.';

global $robots;
$robots = [
    'index' => 'noindex',
    'follow' => 'nofollow',
];

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
                        <h2 class="statistics__title">
                            <?php pll_e('Статистика'); ?>
                        </h2>

                        <div class="statistics__mobile-sidebar">
                            <span> Все лиги</span>
                        </div>

                        <?php get_template_part('template-parts/sport/statistics-type-tabs') ?>

                        <?php get_template_part('template-parts/sport-quiz') ?>

                        <div class="statistics__main">
                            <?php get_template_part('template-parts/sport/statistics-sidebar', null, ['page_data' => 'category']) ?>

                            <?php get_template_part('template-parts/sport/statistics-category') ?>

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

<script src="<?php echo get_template_directory_uri() . '/sport/football/js/country/index.min.js' ?>"></script>


<?php get_footer(); ?>