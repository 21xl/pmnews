<?php
$ad_banner = get_field('ad_menu', 'options');
?>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <header class="header header--white">
        <div class="header__wrapper wrapper">
            <div class="header__left">
                <a href="<?php echo esc_url(get_home_url()); ?>" class="header__logo-mobile"
                    title="<?php pll_e('Main page!'); ?>">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/logo.svg'); ?>"
                        alt="<?php echo esc_attr(the_title()); ?>">
                </a>

                <div class="header__search">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/search-black.svg'); ?>"
                        alt="Поиск">
                </div>
            </div>

            <div class="header__buttons">
                <a href="#" class="button button--white"><?php pll_e('Регистрация') ?></a>
                <a href="#" class="button"><?php pll_e('Login') ?></a>
            </div>

            <?php echo do_shortcode('[language_switcher]'); ?>

            <div class="header__burger">
                <span></span>
                <span></span>
                <span></span>
            </div>

            <nav class="header__nav">
                <?php if (!empty($ad_banner) && is_array($ad_banner)):
                    $ad_banner_link = !empty($ad_banner['link']) ? esc_url($ad_banner['link']) : '';
                    $ad_banner_img = !empty($ad_banner['image']) ? esc_url($ad_banner['image']) : '';

                    if ($ad_banner_link && $ad_banner_img): ?>
                        <a href="<?php echo $ad_banner_link; ?>" class="aside__banner">
                            <img src="<?php echo $ad_banner_img; ?>" alt="Ad Banner">
                        </a>
                    <?php endif;
                endif; ?>

                <?php if (has_nav_menu('header_menu')): ?>
                    <?php wp_nav_menu(
                        array(
                            'theme_location' => 'header_menu',
                            'menu_class' => 'aside__list',
                            'depth' => 0,
                            'walker' => new description_walker(),
                        )
                    ); ?>
                <?php endif; ?>

                <?php get_template_part('template-parts/social-medias'); ?>
            </nav>
        </div>
    </header>