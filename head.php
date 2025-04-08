<!doctype html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <script async src="https://unpkg.com/imask"></script>

    <?php
    global $custom_meta_title;
    if (!empty($custom_meta_title)) {
        add_filter('rank_math/frontend/title', '__return_false');
        add_filter('pre_get_document_title', function ($title) use ($custom_meta_title) {
            return $custom_meta_title;
        });
    }
    ?>

    <?php
    global $custom_meta_description;
    if (!empty($custom_meta_description)) {
        if (!empty($custom_meta_description)) {
            add_filter('rank_math/frontend/description', function ($title) use ($custom_meta_description) {
                return $custom_meta_description;
            });
        }
    }
    ?>

    <?php
    global $robots;
    if (!empty($robots)) {
        add_filter('rank_math/frontend/robots', function ($title) use ($robots) {
            return $robots;
        });
    }
    ?>

    <?php wp_head(); ?>

    <!-- Hotjar Tracking Code -->
    <script> (function (h, o, t, j, a, r) { h.hj = h.hj || function () { (h.hj.q = h.hj.q || []).push(arguments) }; h._hjSettings = { hjid: 5343481, hjsv: 6 }; a = o.getElementsByTagName('head')[0]; r = o.createElement('script'); r.async = 1; r.src = t + h._hjSettings.hjid + j + h._hjSettings.hjsv; a.appendChild(r); })(window, document, 'https://static.hotjar.com/c/hotjar-', '.js?sv='); </script>
    <!-- End Hotjar -->

    <!-- Google Tag Manager -->
    <script>(function (w, d, s, l, i) {
            w[l] = w[l] || []; w[l].push({
                'gtm.start':
                    new Date().getTime(), event: 'gtm.js'
            }); var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : ''; j.async = true; j.src =
                    'https://www.googletagmanager.com/gtm.js?id=' + i + dl; f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-K7GX4L48');</script>
    <!-- End Google Tag Manager -->

    <!-- Google Analytics -->
    <meta name="google-site-verification" content="uOY84V5tVqNbF5oFO_HAvy_yDkpkeOkPbjWhmqXI-3c" />
    <!-- End Google Analytics -->
</head>