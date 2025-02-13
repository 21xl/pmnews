<?php
$site_name = get_bloginfo('name');
$ad_banner = get_field('ad_menu', 'options');
?>

<aside class="aside">
    <div class="aside__wrapper">

        <?php get_template_part('template-parts/logo-header'); ?>

        <?php if (has_nav_menu('header_menu')): ?>
            <div class="aside__nav nav">
                <?php wp_nav_menu(
                    array(
                        'theme_location' => 'header_menu',
                        'menu_class' => 'aside__list',
                        'depth' => 0,
                        'walker' => new description_walker()
                    )
                ); ?>
            </div>
        <?php endif; ?>

        <div class="aside__language">
            <?php echo do_shortcode('[language_switcher]'); ?>
        </div>

        <?php get_template_part('template-parts/social-medias'); ?>
    </div>

    <button class="aside__collapse">
        <img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/collapse.svg'); ?>" alt="Collapse">
    </button>

</aside>