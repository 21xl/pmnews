<?php
/*
Template Name: Football Match
*/

$match_query = $wpdb->prepare("
    SELECT 
        IF(ht.name_ru IS NULL OR ht.name_ru = '', ht.name, ht.name_ru) AS home_team_name,
        IF(at.name_ru IS NULL OR at.name_ru = '', at.name, at.name_ru) AS away_team_name
    FROM 
        wp_sport_matches_shedule AS m
    LEFT JOIN 
        wp_soccer_teams AS ht ON ht.id = m.home_team_id
    LEFT JOIN 
        wp_soccer_teams AS at ON at.id = m.away_team_id
    WHERE 
        m.id = %s
", $match_id);

$match = $wpdb->get_row($match_query);

global $custom_meta_title;
if (!isset($match)) {
    die;
}

$custom_meta_title = $match->home_team_name . ' - ' . $match->away_team_name . " | " . esc_html(get_bloginfo('name'));


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
                            <?php pll_e('Статистика'); ?>
                        </h2>

                        <div class="statistics__mobile-sidebar">
                            Все лиги
                        </div>

                        <?php get_template_part('template-parts/sport/statistics-type-tabs') ?>

                        <div class="statistics__main">
                            <?php get_template_part('template-parts/sport/statistics-sidebar', null, ['page_data' => 'match']) ?>

                            <?php get_template_part('template-parts/sport/football/statistics-match') ?>

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

<script src="<?php echo get_template_directory_uri() . '/sport/football/js/single/index.min.js' ?>"></script>

<?php get_footer(); ?>