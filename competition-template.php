<?php
/*
Template Name: Football Competition
Template Post Type: Football
*/

global $custom_meta_title;
global $custom_meta_description;

$seo_title = get_post_meta(get_the_ID(), '_seo_title', true);
$title = '';

if (!empty($seo_title)) {
    $title = $seo_title;
} else {
    $title = get_the_title();
}

$custom_meta_title = $title . ': Game Schedule and Match Calendar';
$custom_meta_description = 'Find out the game schedule and match calendar ' . $title . '. Do not miss the most important games of the season! Follow the matches easily and conveniently.';

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
                        <span class="statistics__title">
                            <?php pll_e('Statistics'); ?>
                        </span>

                        <div class="statistics__mobile-sidebar">
                            <span>All leagues</span>
                        </div>

                        <?php get_template_part('template-parts/sport/statistics-type-tabs') ?>

                        <?php get_template_part('template-parts/sport-quiz') ?>

                        <div class="statistics__main">
                            <?php get_template_part('template-parts/sport/football/statistics-sidebar', null, ['page_data' => 'competition']) ?>

                            <?php get_template_part('template-parts/sport/football/statistics-competition') ?>

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

<script src="<?php echo get_template_directory_uri() . '/sport/football/js/competition/index.min.js' ?>"></script>

<?php get_footer(); ?>