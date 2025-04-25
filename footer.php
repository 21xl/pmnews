<?php
$site_name = get_bloginfo('name');
$full_logo = get_field('full_logo', 'options');
?>

<?php get_template_part('template-parts/popup') ?>

<?php get_template_part('template-parts/search') ?>
<?php get_template_part('template-parts/cookie-popup'); ?>

<footer class="footer">
    <div class="footer__wrapper wrapper">
        <div class="footer__column">
            <a href="<?php echo esc_url(get_home_url()) ?>" class="footer__logo footer__logo--desktop">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/logo.svg') ?>"
                    alt="<?php echo esc_attr($site_name) ?>">
            </a>

            <div class="footer__text">
                <?php pll_e('Stay updated on all sports news from Indonesia and the world. Receive the latest information on sports, match results, and interesting articles from our team of journalists.') ?>
            </div>

            <?php get_template_part('template-parts/copyright') ?>
        </div>

        <?php if (has_nav_menu('footer_menu_1')): ?>
            <div class="footer__column">
                <span class="footer__title">
                    <?php pll_e('Company'); ?>
                </span>

                <?php wp_nav_menu(
                    array(
                        'theme_location' => 'footer_menu_1',
                        'menu_class' => 'footer__list',
                        'depth' => 0,
                    )
                ); ?>
            </div>
        <?php endif; ?>

        <?php if (has_nav_menu('footer_menu_2')): ?>
            <div class="footer__column">
                <span class="footer__title">
                    <?php pll_e('Categories'); ?>
                </span>

                <?php wp_nav_menu(
                    array(
                        'theme_location' => 'footer_menu_2',
                        'menu_class' => 'footer__list footer__list--2',
                        'depth' => 0,
                    )
                ); ?>
            </div>
        <?php endif; ?>

        <div class="footer__column">
            <span class="footer__title">
                <?php pll_e('Join us'); ?>
            </span>

            <?php get_template_part('template-parts/social-medias') ?>
        </div>

        <a href="<?php echo esc_url(get_home_url()) ?>" class="footer__logo footer__logo--mobile">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/logo.svg') ?>"
                alt="<?php echo esc_attr($site_name) ?>">
        </a>
    </div>
</footer>

<?php wp_footer(); ?>

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-K7GX4L48" height="0" width="0"
        style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

</body>

</html>