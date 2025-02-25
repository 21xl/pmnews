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
</head>