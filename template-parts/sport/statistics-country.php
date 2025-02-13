<?php
/*
Template Name: Soccer Country
*/

global $wpdb;
$post_id = get_the_ID();
$country_id = get_post_meta($post_id, '_country_id', true);
$country_data = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM wp_sport_country_data WHERE id = %s",
    $country_id
));

if ($country_data): ?>
    <section class="statistics-competition statistics-country">
        <?php get_template_part('template-parts/sport/statistics-breadcrumbs'); ?>

        <div class="statistics-competition__matches">
            <div class="matches">
                <div class="matches__head">
                    <?php get_template_part('template-parts/sport/statistics-locations-tabs'); ?>

                    <div class="matches__top-block">
                        <?php get_template_part('template-parts/sport/date-picker') ?>
                    </div>
                </div>

                <div class="matches__main" data-id="<?php echo $country_id; ?>" data-location="country">
                    <div class="matches__ligue">
                        <div class="matches__ligue-head">
                            <div class="matches__ligue-block">
                                <div class="matches__ligue-fav skeleton"></div>
                                <div class="matches__ligue-title skeleton"></div>
                            </div>

                            <div class="matches__ligue-control skeleton"></div>
                        </div>

                        <div class="matches__ligue-content">
                            <div class="matches__item">
                                <div class="matches__item-block">
                                    <div class="matches__item-fav skeleton"></div>

                                    <div class="matches__item-time skeleton"></div>

                                    <div class="matches__item-rivals">
                                        <div class="matches__item-team skeleton"></div>

                                        <div class="matches__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches__item">
                                <div class="matches__item-block">
                                    <div class="matches__item-fav skeleton"></div>

                                    <div class="matches__item-time skeleton"></div>

                                    <div class="matches__item-rivals">
                                        <div class="matches__item-team skeleton"></div>

                                        <div class="matches__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches__item">
                                <div class="matches__item-block">
                                    <div class="matches__item-fav skeleton"></div>

                                    <div class="matches__item-time skeleton"></div>

                                    <div class="matches__item-rivals">
                                        <div class="matches__item-team skeleton"></div>

                                        <div class="matches__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches__item">
                                <div class="matches__item-block">
                                    <div class="matches__item-fav skeleton"></div>

                                    <div class="matches__item-time skeleton"></div>

                                    <div class="matches__item-rivals">
                                        <div class="matches__item-team skeleton"></div>

                                        <div class="matches__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches__item">
                                <div class="matches__item-block">
                                    <div class="matches__item-fav skeleton"></div>

                                    <div class="matches__item-time skeleton"></div>

                                    <div class="matches__item-rivals">
                                        <div class="matches__item-team skeleton"></div>

                                        <div class="matches__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="matches__ligue">
                        <div class="matches__ligue-head">
                            <div class="matches__ligue-block">
                                <div class="matches__ligue-fav skeleton"></div>
                                <div class="matches__ligue-title skeleton"></div>
                            </div>

                            <div class="matches__ligue-control skeleton"></div>
                        </div>

                        <div class="matches__ligue-content">
                            <div class="matches__item">
                                <div class="matches__item-block">
                                    <div class="matches__item-fav skeleton"></div>

                                    <div class="matches__item-time skeleton"></div>

                                    <div class="matches__item-rivals">
                                        <div class="matches__item-team skeleton"></div>

                                        <div class="matches__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches__item">
                                <div class="matches__item-block">
                                    <div class="matches__item-fav skeleton"></div>

                                    <div class="matches__item-time skeleton"></div>

                                    <div class="matches__item-rivals">
                                        <div class="matches__item-team skeleton"></div>

                                        <div class="matches__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches__item">
                                <div class="matches__item-block">
                                    <div class="matches__item-fav skeleton"></div>

                                    <div class="matches__item-time skeleton"></div>

                                    <div class="matches__item-rivals">
                                        <div class="matches__item-team skeleton"></div>

                                        <div class="matches__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches__item">
                                <div class="matches__item-block">
                                    <div class="matches__item-fav skeleton"></div>

                                    <div class="matches__item-time skeleton"></div>

                                    <div class="matches__item-rivals">
                                        <div class="matches__item-team skeleton"></div>

                                        <div class="matches__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches__item">
                                <div class="matches__item-block">
                                    <div class="matches__item-fav skeleton"></div>

                                    <div class="matches__item-time skeleton"></div>

                                    <div class="matches__item-rivals">
                                        <div class="matches__item-team skeleton"></div>

                                        <div class="matches__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="matches__ligue">
                        <div class="matches__ligue-head">
                            <div class="matches__ligue-block">
                                <div class="matches__ligue-fav skeleton"></div>
                                <div class="matches__ligue-title skeleton"></div>
                            </div>

                            <div class="matches__ligue-control skeleton"></div>
                        </div>

                        <div class="matches__ligue-content">
                            <div class="matches__item">
                                <div class="matches__item-block">
                                    <div class="matches__item-fav skeleton"></div>

                                    <div class="matches__item-time skeleton"></div>

                                    <div class="matches__item-rivals">
                                        <div class="matches__item-team skeleton"></div>

                                        <div class="matches__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches__item">
                                <div class="matches__item-block">
                                    <div class="matches__item-fav skeleton"></div>

                                    <div class="matches__item-time skeleton"></div>

                                    <div class="matches__item-rivals">
                                        <div class="matches__item-team skeleton"></div>

                                        <div class="matches__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches__item">
                                <div class="matches__item-block">
                                    <div class="matches__item-fav skeleton"></div>

                                    <div class="matches__item-time skeleton"></div>

                                    <div class="matches__item-rivals">
                                        <div class="matches__item-team skeleton"></div>

                                        <div class="matches__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches__item">
                                <div class="matches__item-block">
                                    <div class="matches__item-fav skeleton"></div>

                                    <div class="matches__item-time skeleton"></div>

                                    <div class="matches__item-rivals">
                                        <div class="matches__item-team skeleton"></div>

                                        <div class="matches__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches__item">
                                <div class="matches__item-block">
                                    <div class="matches__item-fav skeleton"></div>

                                    <div class="matches__item-time skeleton"></div>

                                    <div class="matches__item-rivals">
                                        <div class="matches__item-team skeleton"></div>

                                        <div class="matches__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="matches__ligue">
                        <div class="matches__ligue-head">
                            <div class="matches__ligue-block">
                                <div class="matches__ligue-fav skeleton"></div>
                                <div class="matches__ligue-title skeleton"></div>
                            </div>

                            <div class="matches__ligue-control skeleton"></div>
                        </div>

                        <div class="matches__ligue-content">
                            <div class="matches__item">
                                <div class="matches__item-block">
                                    <div class="matches__item-fav skeleton"></div>

                                    <div class="matches__item-time skeleton"></div>

                                    <div class="matches__item-rivals">
                                        <div class="matches__item-team skeleton"></div>

                                        <div class="matches__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches__item">
                                <div class="matches__item-block">
                                    <div class="matches__item-fav skeleton"></div>

                                    <div class="matches__item-time skeleton"></div>

                                    <div class="matches__item-rivals">
                                        <div class="matches__item-team skeleton"></div>

                                        <div class="matches__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches__item">
                                <div class="matches__item-block">
                                    <div class="matches__item-fav skeleton"></div>

                                    <div class="matches__item-time skeleton"></div>

                                    <div class="matches__item-rivals">
                                        <div class="matches__item-team skeleton"></div>

                                        <div class="matches__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches__item">
                                <div class="matches__item-block">
                                    <div class="matches__item-fav skeleton"></div>

                                    <div class="matches__item-time skeleton"></div>

                                    <div class="matches__item-rivals">
                                        <div class="matches__item-team skeleton"></div>

                                        <div class="matches__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches__item">
                                <div class="matches__item-block">
                                    <div class="matches__item-fav skeleton"></div>

                                    <div class="matches__item-time skeleton"></div>

                                    <div class="matches__item-rivals">
                                        <div class="matches__item-team skeleton"></div>

                                        <div class="matches__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="matches__ligue">
                        <div class="matches__ligue-head">
                            <div class="matches__ligue-block">
                                <div class="matches__ligue-fav skeleton"></div>
                                <div class="matches__ligue-title skeleton"></div>
                            </div>

                            <div class="matches__ligue-control skeleton"></div>
                        </div>

                        <div class="matches__ligue-content">
                            <div class="matches__item">
                                <div class="matches__item-block">
                                    <div class="matches__item-fav skeleton"></div>

                                    <div class="matches__item-time skeleton"></div>

                                    <div class="matches__item-rivals">
                                        <div class="matches__item-team skeleton"></div>

                                        <div class="matches__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches__item">
                                <div class="matches__item-block">
                                    <div class="matches__item-fav skeleton"></div>

                                    <div class="matches__item-time skeleton"></div>

                                    <div class="matches__item-rivals">
                                        <div class="matches__item-team skeleton"></div>

                                        <div class="matches__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches__item">
                                <div class="matches__item-block">
                                    <div class="matches__item-fav skeleton"></div>

                                    <div class="matches__item-time skeleton"></div>

                                    <div class="matches__item-rivals">
                                        <div class="matches__item-team skeleton"></div>

                                        <div class="matches__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches__item">
                                <div class="matches__item-block">
                                    <div class="matches__item-fav skeleton"></div>

                                    <div class="matches__item-time skeleton"></div>

                                    <div class="matches__item-rivals">
                                        <div class="matches__item-team skeleton"></div>

                                        <div class="matches__item-team skeleton"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="matches__item">
                                <div class="matches__item-block">
                                    <div class="matches__item-fav skeleton"></div>

                                    <div class="matches__item-time skeleton"></div>

                                    <div class="matches__item-rivals">
                                        <div class="matches__item-team skeleton"></div>

                                        <div class="matches__item-team skeleton"></div>
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

    <div class="matches__error">
        <span><?php pll_e('Matches not found') ?></span>
    </div>

<?php endif; ?>