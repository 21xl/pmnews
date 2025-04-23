<?php
/*
Template Name: Soccer Country
*/

global $wpdb;
$post_id = get_the_ID();
$category_id = get_post_meta($post_id, '_category_id', true);
$country_data = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM wp_sport_category_data WHERE id = %s",
    $category_id
));

if ($country_data): ?>
    <section class="statistics-competition statistics-country">
        <?php get_template_part('template-parts/sport/tennis/statistics-breadcrumbs'); ?>

        <div class="statistics-competition__matches">
            <div class="matches tennis">

                <div class="matches-tennis__head">
                    <?php get_template_part('template-parts/sport/football/statistics-locations-tabs'); ?>
                    <div class="matches-tennis__top-block">
                        <?php get_template_part('template-parts/sport/date-picker') ?>
                    </div>
                </div>
                <div class="matches-tennis__main" data-id="<?php echo $category_id; ?>" data-location="category">
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
    <?php
else: ?>
    <div class="matches-tennis__error">
        <span><?php pll_e('Matches not found') ?></span>
    </div>
<?php endif; ?>