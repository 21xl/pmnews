<?php get_template_part('head'); ?>
<?php $ad_banner = get_field('ad_category', 'options'); ?>

<div class="page">
    <?php get_template_part('template-parts/aside') ?>

    <div class="content">
        <?php get_header('white'); ?>

        <main class="attachment-page__flex">
            <div class="attachment-page__content">
                <?php get_template_part('template-parts/breadcrumbs') ?>

                <section class="attachment-page">
                    <div class="wrapper">
                        <h1 class="attachment-page__title"> <?php pll_e('Имя файла:'); ?>
                            <?php echo esc_html(get_the_title()); ?></h1>
                        <div class="attachment-page__img">
                            <?php

                            if (is_attachment()) {

                                $attachment_id = get_the_ID();

                                echo wp_get_attachment_image($attachment_id, 'full', false, [
                                    'alt' => esc_attr(get_the_title($attachment_id)),
                                    'class' => 'attachment-image'
                                ]);
                            } else {

                                ?>
                                <img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/placeholder-hero.webp'); ?>"
                                    alt="<?php echo esc_attr__('Default image', 'your-textdomain'); ?>">
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </section>
            </div>


            <?php get_template_part('template-parts/single-aside') ?>


        </main>
    </div>
</div>

<?php get_footer(); ?>