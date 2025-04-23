<?php

$team_data = get_tennis_team_info($team_id);

$team_name = null;
$team_logo = null;
$team_slug = null;

$tab = 0;

if ($custom_page === 'results') {
    $tab = 1;
} elseif ($custom_page === 'team') {
    $tab = 0;
} else {
    $tab = 2;
}

$title = '';

if ($team_data) {
    $title = get_translated_name($team_data['names']);
}

if ($team_data): ?>
    <section class="statistics-competition" data-teamid="<?php echo $team_data['id']; ?>">

        <?php get_template_part('template-parts/sport/tennis/statistics-breadcrumbs'); ?>

        <div class="statistics-competition__head">
            <div class="statistics-competition__img">
                <!-- Дополнительная проверка на наличие лого -->
                <?php if ($team_data['logo']): ?>
                    <img src="<?php echo esc_url($team_data['logo']); ?>" alt="<?php echo esc_attr($title); ?>">
                <?php else: ?>
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/sport/src/img/tennis-player-placeholder.svg'); ?>"
                        alt="<?php echo esc_attr($title); ?>">
                <?php endif; ?>
            </div>

            <div class="statistics-competition__content">
                <div class="statistics-competition__content-top">
                    <div class="statistics-competition__main">

                        <span class="statistics-competition__title">
                            <?php echo esc_html($title); ?>
                        </span>

                        <div class="matches-tennis__item-fav">
                            <div class="fav addfavteam" data-teamid="<?php echo $team_data['id']; ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M9.82028 2.77976C9.89346 2.6304 10.1064 2.63042 10.1795 2.77978L12.3994 7.3127C12.4284 7.37188 12.4847 7.41299 12.5499 7.4226L17.5184 8.15443C17.6819 8.17851 17.7473 8.37927 17.6293 8.49503L14.0329 12.0249C13.9861 12.0707 13.9648 12.1366 13.9758 12.2012L14.8243 17.1859C14.8521 17.3495 14.6801 17.4739 14.5334 17.3962L10.0936 15.0435C10.035 15.0125 9.96485 15.0125 9.90628 15.0435L5.4664 17.3962C5.31976 17.4739 5.14774 17.3495 5.17559 17.1859L6.02404 12.2012C6.03503 12.1366 6.01371 12.0707 5.96697 12.0249L2.37051 8.49503C2.25256 8.37926 2.31795 8.17851 2.48146 8.15442L7.44911 7.4226C7.5143 7.41299 7.57058 7.37189 7.59957 7.31272L9.82028 2.77976Z"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="statistics-competition__bio">
                    <div class="statistics-competition__bio-item">
                        <?php if (!empty($team_data['currentRanking']['position'])): ?>
                            <span
                                class="statistics-competition__bio-title"><?= $team_data['currentRanking']['type'] ?? '' ?>:</span>
                            <span><?= $team_data['currentRanking']['position'] ?? '' ?></span>
                        <?php endif ?>
                    </div>

                    <div class="statistics-competition__bio-item">
                        <?php if (!empty($team_data['extra']['age'])): ?>
                            <span class="statistics-competition__bio-title"><?php pll_e('Возраст:'); ?></span>

                            <span><?= $team_data['extra']['age'] . " (" . $team_data['extra']['birthdayFormatted'] . ")" ?? '' ?></span>
                        <?php endif ?>
                    </div>
                    <div class="statistics-competition__bio-item">
                        <?php if (!empty($team_data['country'])): ?>
                            <span class="statistics-competition__bio-title"><?php pll_e('Страна:'); ?></span>

                            <span><?= get_translated_name($team_data['country']['names']) ?? '' ?></span>
                        <?php endif ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="statistics-competition__matches">
            <div class="matches tennis">
                <div class="matches-tennis__head">
                    <?php get_template_part('template-parts/sport/tennis/statistics-team-tabs', null, array('active_tab_index' => $tab)); ?>
                </div>
                <div class="match__statistics-checkbox custom-checkbox">
                    <div class="custom-checkbox__item" data-checkbox-tab="single">
                        <input type="hidden" name="checkbox_single" value="true" class="custom-checkbox__input" />
                        <div class="custom-checkbox__box" data-checked="true"></div>
                        <span class="custom-checkbox__label"><?php pll_e('Singles'); ?></span>
                    </div>

                    <div class="custom-checkbox__item" data-checkbox-tab="doubles">
                        <input type="hidden" name="checkbox_doubles" value="false" class="custom-checkbox__input" />
                        <div class="custom-checkbox__box" data-checked="false"></div>
                        <span class="custom-checkbox__label"><?php pll_e('Doubles'); ?></span>
                    </div>
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