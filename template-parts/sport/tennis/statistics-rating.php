<?php
$type = get_post_meta(get_the_ID(), '_type', true);
$seo_title = get_post_meta(get_the_ID(), '_seo_title', true);
$title = '';

if (!empty($seo_title)) {
    $title = $seo_title;
} else {
    $title = get_the_title();
}

$thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
?>

<div class="rating">
    <div class="rating__head">
        <?php get_template_part('template-parts/sport/tennis/statistics-rating-tabs') ?>
    </div>

    <div class="rating__search">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <g id="Group">
                <g id="Group_2">
                    <path id="Path"
                        d="M16.7472 6.18801C19.6632 9.10405 19.6632 13.8319 16.7472 16.7479C13.8311 19.6639 9.10328 19.6639 6.18727 16.7479C3.27123 13.8319 3.27123 9.10402 6.18727 6.18801C9.10331 3.27197 13.8312 3.27197 16.7472 6.18801"
                        stroke="#111319" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    <path id="Path_2" d="M20.0002 20.001L16.7502 16.751" stroke="#111319" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round" />
                </g>
            </g>
        </svg>

        <input id="team" type="text" placeholder="<?php pll_e('Search'); ?>" maxlength="30">
    </div>

    <div class="rating__main" data-type="<?= $type; ?>">
        <div class="table rating-table">
            <div class="table__wrapper">
                <div class="row head rating-table__head">
                    <div class="rating-table__logo">
                        <img src="<?= esc_url($thumbnail_url) ?>" alt="<?= $title ?>">
                    </div>

                    <span class="rating-table__title"><?= $title ?></span>
                </div>

                <div class="row head">
                    <div class="cell rating-number">
                        №
                    </div>

                    <div class="cell rating-player-row">
                        <?php pll_e('Имя') ?>
                    </div>

                    <div class="cell rating-country-row">
                        <?php pll_e('Страна') ?>
                    </div>

                    <div class="cell rating-points">
                        <?php pll_e('Очки') ?>
                    </div>

                    <div class="cell rating-tournaments">
                        <?php pll_e('Турниры') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>