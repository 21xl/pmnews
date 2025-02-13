<?php

function editLoginPage()
{
    ?>

    <style type="text/css">
        #login h1 a {
            background-image: url(<?= get_template_directory_uri() . '/src/img/logo.svg'; ?>);
            display: block;
            width: 180px;
            height: 70px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            margin: 0 auto 10px;
        }

        #login,
        #nav,
        #backtoblog,
        .language-switcher {
            z-index: 5;
            position: relative;
        }

        #wp-submit {
            background: #FF0000;
            border-color: #FF0000;
        }

        .dashicons-visibility:before,
        .dashicons-hidden:before {
            color: #FF0000;
        }
    </style>
    <?php
}

add_action('login_enqueue_scripts', 'editLoginPage');
