<?php
$post_id = get_the_ID();
$competition_id = get_post_meta($post_id, '_tournament_id', true);
$seo_title = get_post_meta($post_id, '_seo_title', true);
$competition_data = get_tennis_tournament_by_id($competition_id);


$team_name = null;
$team_logo = null;
$team_slug = null;

$content = get_the_content();
$title = '';

if ($competition_data) {
    if (!empty($seo_title)) {
        $title = $seo_title;
    } else {
        $title = get_translated_name($competition_data['unic']['names']);
    }
}

if ($competition_data): ?>
    <section class="statistics-competition" data-competition="<?php echo $competition_id; ?>">

        <?php get_template_part('template-parts/sport/tennis/statistics-breadcrumbs'); ?>

        <div class="statistics-competition__head">
            <div class="statistics-competition__img">
                <?php if ($competition_data['unic']['logo']): ?>
                    <img src="<?php echo esc_url($competition_data['unic']['logo']); ?>"
                        alt="<?php echo esc_attr(get_translated_name($competition_data['unic']['names'])); ?>">
                <?php else: ?>
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/sport/src/img/football-team-placeholder.svg'); ?>"
                        alt="<?php echo esc_attr(get_translated_name($competition_data['unic']['names'])); ?>">
                <?php endif; ?>
            </div>

            <div class="statistics-competition__content">
                <div class="statistics-competition__content-top">
                    <div class="statistics-competition__main">
                        <span class="statistics-competition__title">
                            <?php echo esc_html($title); ?>
                        </span>

                        <?php get_template_part('template-parts/sport/pin', null, array('competition_id' => $competition_id)); ?>
                    </div>

                    <?php if (!empty($content)): ?>
                        <div class="statistics-competition__desc">
                            <?php echo wp_kses_post($content); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- <?php if ($team_logo && $team_name): ?>
                    <div class="statistics-competition__winner">
                        <span class="statistics-competition__winner-text"><?php pll_e('Победитель турнира'); ?>:</span>

                        <div class="statistics-competition__winner-wrapper">
                            <div class="statistics-competition__winner-img">
                                <img src="<?= esc_url($team_logo); ?>" alt="<?php echo $team_name ?>">
                            </div>

                            <a href="/statistics/teams/<?= $team_slug ?>/">
                                <span class="statistics-competition__winner-title">
                                    <?php echo $team_name ?>
                                </span>
                            </a>
                        </div>
                    </div>
                <?php endif ?> -->
            </div>
        </div>

        <div class="statistics-competition__matches">
            <div class="matches tennis">
                <h1 class="statistics-competition__subtitle">
                    <?php pll_e('Live matches'); ?> -
                    <?php echo esc_html($title); ?>
                </h1>

                <div class="matches-tennis__head">
                    <?php get_template_part('template-parts/sport/tennis/statistics-competition-tabs', null, array('active_tab_index' => 1)); ?>
                </div>

                <div class="matches-tennis__main">
                    <div class="matches-tennis__ligue">
                        <div class="matches-tennis__ligue-head">
                            <div class="matches-tennis__ligue-block">
                                <div class="matches-tennis__ligue-fav skeleton"></div>
                                <div class="matches-tennis__ligue-title skeleton"></div>
                            </div>

                            <div class="matches-tennis__ligue-control skeleton"></div>
                        </div>

                        <div class="matches-tennis__ligue-content">
                            <div class="matches-tennis__item">
                                <div class="matches-tennis__item-block">
                                    <div class="matches-tennis__item-fav skeleton"></div>

                                    <div class="matches-tennis__item-time skeleton"></div>

                                    <div class="matches-tennis__item-rivals">
                                        <div class="matches-tennis__item-team skeleton"></div>

                                        <div class="matches-tennis__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches-tennis__item">
                                <div class="matches-tennis__item-block">
                                    <div class="matches-tennis__item-fav skeleton"></div>

                                    <div class="matches-tennis__item-time skeleton"></div>

                                    <div class="matches-tennis__item-rivals">
                                        <div class="matches-tennis__item-team skeleton"></div>

                                        <div class="matches-tennis__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches-tennis__item">
                                <div class="matches-tennis__item-block">
                                    <div class="matches-tennis__item-fav skeleton"></div>

                                    <div class="matches-tennis__item-time skeleton"></div>

                                    <div class="matches-tennis__item-rivals">
                                        <div class="matches-tennis__item-team skeleton"></div>

                                        <div class="matches-tennis__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches-tennis__item">
                                <div class="matches-tennis__item-block">
                                    <div class="matches-tennis__item-fav skeleton"></div>

                                    <div class="matches-tennis__item-time skeleton"></div>

                                    <div class="matches-tennis__item-rivals">
                                        <div class="matches-tennis__item-team skeleton"></div>

                                        <div class="matches-tennis__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches-tennis__item">
                                <div class="matches-tennis__item-block">
                                    <div class="matches-tennis__item-fav skeleton"></div>

                                    <div class="matches-tennis__item-time skeleton"></div>

                                    <div class="matches-tennis__item-rivals">
                                        <div class="matches-tennis__item-team skeleton"></div>

                                        <div class="matches-tennis__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="matches-tennis__ligue">
                        <div class="matches-tennis__ligue-head">
                            <div class="matches-tennis__ligue-block">
                                <div class="matches-tennis__ligue-fav skeleton"></div>
                                <div class="matches-tennis__ligue-title skeleton"></div>
                            </div>

                            <div class="matches-tennis__ligue-control skeleton"></div>
                        </div>

                        <div class="matches-tennis__ligue-content">
                            <div class="matches-tennis__item">
                                <div class="matches-tennis__item-block">
                                    <div class="matches-tennis__item-fav skeleton"></div>

                                    <div class="matches-tennis__item-time skeleton"></div>

                                    <div class="matches-tennis__item-rivals">
                                        <div class="matches-tennis__item-team skeleton"></div>

                                        <div class="matches-tennis__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches-tennis__item">
                                <div class="matches-tennis__item-block">
                                    <div class="matches-tennis__item-fav skeleton"></div>

                                    <div class="matches-tennis__item-time skeleton"></div>

                                    <div class="matches-tennis__item-rivals">
                                        <div class="matches-tennis__item-team skeleton"></div>

                                        <div class="matches-tennis__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches-tennis__item">
                                <div class="matches-tennis__item-block">
                                    <div class="matches-tennis__item-fav skeleton"></div>

                                    <div class="matches-tennis__item-time skeleton"></div>

                                    <div class="matches-tennis__item-rivals">
                                        <div class="matches-tennis__item-team skeleton"></div>

                                        <div class="matches-tennis__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches-tennis__item">
                                <div class="matches-tennis__item-block">
                                    <div class="matches-tennis__item-fav skeleton"></div>

                                    <div class="matches-tennis__item-time skeleton"></div>

                                    <div class="matches-tennis__item-rivals">
                                        <div class="matches-tennis__item-team skeleton"></div>

                                        <div class="matches-tennis__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches-tennis__item">
                                <div class="matches-tennis__item-block">
                                    <div class="matches-tennis__item-fav skeleton"></div>

                                    <div class="matches-tennis__item-time skeleton"></div>

                                    <div class="matches-tennis__item-rivals">
                                        <div class="matches-tennis__item-team skeleton"></div>

                                        <div class="matches-tennis__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="matches-tennis__ligue">
                        <div class="matches-tennis__ligue-head">
                            <div class="matches-tennis__ligue-block">
                                <div class="matches-tennis__ligue-fav skeleton"></div>
                                <div class="matches-tennis__ligue-title skeleton"></div>
                            </div>

                            <div class="matches-tennis__ligue-control skeleton"></div>
                        </div>

                        <div class="matches-tennis__ligue-content">
                            <div class="matches-tennis__item">
                                <div class="matches-tennis__item-block">
                                    <div class="matches-tennis__item-fav skeleton"></div>

                                    <div class="matches-tennis__item-time skeleton"></div>

                                    <div class="matches-tennis__item-rivals">
                                        <div class="matches-tennis__item-team skeleton"></div>

                                        <div class="matches-tennis__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches-tennis__item">
                                <div class="matches-tennis__item-block">
                                    <div class="matches-tennis__item-fav skeleton"></div>

                                    <div class="matches-tennis__item-time skeleton"></div>

                                    <div class="matches-tennis__item-rivals">
                                        <div class="matches-tennis__item-team skeleton"></div>

                                        <div class="matches-tennis__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches-tennis__item">
                                <div class="matches-tennis__item-block">
                                    <div class="matches-tennis__item-fav skeleton"></div>

                                    <div class="matches-tennis__item-time skeleton"></div>

                                    <div class="matches-tennis__item-rivals">
                                        <div class="matches-tennis__item-team skeleton"></div>

                                        <div class="matches-tennis__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches-tennis__item">
                                <div class="matches-tennis__item-block">
                                    <div class="matches-tennis__item-fav skeleton"></div>

                                    <div class="matches-tennis__item-time skeleton"></div>

                                    <div class="matches-tennis__item-rivals">
                                        <div class="matches-tennis__item-team skeleton"></div>

                                        <div class="matches-tennis__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches-tennis__item">
                                <div class="matches-tennis__item-block">
                                    <div class="matches-tennis__item-fav skeleton"></div>

                                    <div class="matches-tennis__item-time skeleton"></div>

                                    <div class="matches-tennis__item-rivals">
                                        <div class="matches-tennis__item-team skeleton"></div>

                                        <div class="matches-tennis__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="matches-tennis__ligue">
                        <div class="matches-tennis__ligue-head">
                            <div class="matches-tennis__ligue-block">
                                <div class="matches-tennis__ligue-fav skeleton"></div>
                                <div class="matches-tennis__ligue-title skeleton"></div>
                            </div>

                            <div class="matches-tennis__ligue-control skeleton"></div>
                        </div>

                        <div class="matches-tennis__ligue-content">
                            <div class="matches-tennis__item">
                                <div class="matches-tennis__item-block">
                                    <div class="matches-tennis__item-fav skeleton"></div>

                                    <div class="matches-tennis__item-time skeleton"></div>

                                    <div class="matches-tennis__item-rivals">
                                        <div class="matches-tennis__item-team skeleton"></div>

                                        <div class="matches-tennis__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches-tennis__item">
                                <div class="matches-tennis__item-block">
                                    <div class="matches-tennis__item-fav skeleton"></div>

                                    <div class="matches-tennis__item-time skeleton"></div>

                                    <div class="matches-tennis__item-rivals">
                                        <div class="matches-tennis__item-team skeleton"></div>

                                        <div class="matches-tennis__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches-tennis__item">
                                <div class="matches-tennis__item-block">
                                    <div class="matches-tennis__item-fav skeleton"></div>

                                    <div class="matches-tennis__item-time skeleton"></div>

                                    <div class="matches-tennis__item-rivals">
                                        <div class="matches-tennis__item-team skeleton"></div>

                                        <div class="matches-tennis__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches-tennis__item">
                                <div class="matches-tennis__item-block">
                                    <div class="matches-tennis__item-fav skeleton"></div>

                                    <div class="matches-tennis__item-time skeleton"></div>

                                    <div class="matches-tennis__item-rivals">
                                        <div class="matches-tennis__item-team skeleton"></div>

                                        <div class="matches-tennis__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches-tennis__item">
                                <div class="matches-tennis__item-block">
                                    <div class="matches-tennis__item-fav skeleton"></div>

                                    <div class="matches-tennis__item-time skeleton"></div>

                                    <div class="matches-tennis__item-rivals">
                                        <div class="matches-tennis__item-team skeleton"></div>

                                        <div class="matches-tennis__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="matches-tennis__ligue">
                        <div class="matches-tennis__ligue-head">
                            <div class="matches-tennis__ligue-block">
                                <div class="matches-tennis__ligue-fav skeleton"></div>
                                <div class="matches-tennis__ligue-title skeleton"></div>
                            </div>

                            <div class="matches-tennis__ligue-control skeleton"></div>
                        </div>

                        <div class="matches-tennis__ligue-content">
                            <div class="matches-tennis__item">
                                <div class="matches-tennis__item-block">
                                    <div class="matches-tennis__item-fav skeleton"></div>

                                    <div class="matches-tennis__item-time skeleton"></div>

                                    <div class="matches-tennis__item-rivals">
                                        <div class="matches-tennis__item-team skeleton"></div>

                                        <div class="matches-tennis__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches-tennis__item">
                                <div class="matches-tennis__item-block">
                                    <div class="matches-tennis__item-fav skeleton"></div>

                                    <div class="matches-tennis__item-time skeleton"></div>

                                    <div class="matches-tennis__item-rivals">
                                        <div class="matches-tennis__item-team skeleton"></div>

                                        <div class="matches-tennis__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches-tennis__item">
                                <div class="matches-tennis__item-block">
                                    <div class="matches-tennis__item-fav skeleton"></div>

                                    <div class="matches-tennis__item-time skeleton"></div>

                                    <div class="matches-tennis__item-rivals">
                                        <div class="matches-tennis__item-team skeleton"></div>

                                        <div class="matches-tennis__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches-tennis__item">
                                <div class="matches-tennis__item-block">
                                    <div class="matches-tennis__item-fav skeleton"></div>

                                    <div class="matches-tennis__item-time skeleton"></div>

                                    <div class="matches-tennis__item-rivals">
                                        <div class="matches-tennis__item-team skeleton"></div>

                                        <div class="matches-tennis__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches-tennis__item">
                                <div class="matches-tennis__item-block">
                                    <div class="matches-tennis__item-fav skeleton"></div>

                                    <div class="matches-tennis__item-time skeleton"></div>

                                    <div class="matches-tennis__item-rivals">
                                        <div class="matches-tennis__item-team skeleton"></div>

                                        <div class="matches-tennis__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php else: ?>

    <div class="matches-tennis__error">
        <span><?php pll_e('Matches not found') ?></span>
    </div>

<?php endif; ?>