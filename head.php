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

    <?php


    ?>


    <?php wp_head(); ?>

    <!-- Meta Pixel Code -->
    <script>
        !function (f, b, e, v, n, t, s) {
            if (f.fbq) return; n = f.fbq = function () {
                n.callMethod ?
                    n.callMethod.apply(n, arguments) : n.queue.push(arguments)
            };
            if (!f._fbq) f._fbq = n; n.push = n; n.loaded = !0; n.version = '2.0';
            n.queue = []; t = b.createElement(e); t.async = !0;
            t.src = v; s = b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t, s)
        }(window, document, 'script',
            'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '1652984091830983');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id=1652984091830983&ev=PageView&noscript=1" /></noscript>
    <!-- End Meta Pixel Code -->

    <!-- Google Tag Manager -->
    <script>(function (w, d, s, l, i) {
            w[l] = w[l] || []; w[l].push({
                'gtm.start':
                    new Date().getTime(), event: 'gtm.js'
            }); var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : ''; j.async = true; j.src =
                    'https://www.googletagmanager.com/gtm.js?id=' + i + dl; f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-M9QSKL6C');</script>
    <!-- End Google Tag Manager -->

    <script charset="UTF-8" src="//web.webpushs.com/js/push/249e2079f60de4bd7db46f4ffa871e03_1.js" async></script>

    <meta name="google-site-verification" content="ikG_KeZSDK8bTckYlUA6ZvblK8hcdAhFvnk-0_X6ooU" />

</head>