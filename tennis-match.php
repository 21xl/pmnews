<?php
/*
Template Name: Tennis Match
*/

$match = get_tennis_match_by_id($match_id);

if (!$match) {
    wp_die('Матч не найден или произошла ошибка при загрузке данных.');
}

global $custom_meta_title;

$custom_meta_title = get_translated_name($match['homeTeam']['names']) . ' - ' .
    get_translated_name($match['awayTeam']['names']) . " | " .
    esc_html(get_bloginfo('name'));

get_template_part('head');
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
                            <?php pll_e('Statistics'); ?>
                        </h2>

                        <div class="statistics__mobile-sidebar">
                            <span><?php pll_e('All leagues') ?></span>
                        </div>

                        <?php get_template_part('template-parts/sport/statistics-type-tabs') ?>

                        <div class="statistics__main">
                            <?php get_template_part('template-parts/sport/tennis/statistics-sidebar', null, ['page_data' => 'match']); ?>

                            <?php get_template_part('template-parts/sport/tennis/statistics-match', null, ['match' => $match]); ?>

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

<script src="<?php echo get_template_directory_uri() . '/sport/tennis/js/single/index.min.js' ?>"></script>

<?php get_footer(); ?>