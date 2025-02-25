<?php
/*
Template Name: Контакты
*/

get_template_part('head');

$contact_address = get_field('contact_address');
$contact_address_map = get_field('contact_address_map');
$edit_address = get_field('edit_address');
$ad_address = get_field('ad_address');
$contact_question_title = get_field('contact_question_title');
$contact_question_text = get_field('contact_question_text');
$contact_tg_link = get_field('contact_tg_link');
$contact_tg_text = get_field('contact_tg_text');
$contact_phone = get_field('contact_phone');
?>

<div class="page">
    <?php get_template_part('template-parts/aside') ?>

    <div class="content">
        <?php get_header('white'); ?>

        <main>
            <section class="contacts">
                <?php get_template_part('template-parts/breadcrumbs') ?>
                <div class="contacts__wrapper wrapper">
                    <h1 class="contacts__title">
                        <?php echo esc_html(get_the_title()) ?>
                    </h1>

                    <div class="contacts__list">
                        <?php if ($contact_address && $contact_address_map): ?>
                            <div class="contacts__item">
                                <div class="contacts__item-icon">
                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/contact-house.svg') ?>"
                                        alt=" <?php echo esc_attr(get_the_title()) ?>">
                                </div>

                                <div class="contacts__item-text">
                                    <p class="contacts__item-sub">
                                        <?php pll_e('Legal address of the editorial office'); ?>
                                    </p>

                                    <a href="<?php echo esc_url($contact_address_map) ?>" target="_blank"
                                        rel="nofollow noopener" class="contacts__item-info">
                                        <?php echo esc_html($contact_address) ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($edit_address): ?>
                            <div class="contacts__item">
                                <div class="contacts__item-icon">
                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/contact-case.svg') ?>"
                                        alt="<?php echo esc_attr(get_the_title()) ?>">
                                </div>

                                <div class="contacts__item-text">
                                    <p class="contacts__item-sub">
                                        <?php pll_e('Editorial office'); ?>
                                    </p>

                                    <a href="mailto:<?php echo esc_attr($edit_address); ?>" class="contacts__item-info">
                                        <?php echo esc_html($edit_address) ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($ad_address): ?>
                            <div class="contacts__item">
                                <div class="contacts__item-icon">
                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/contact-email.svg') ?>"
                                        alt="<?php echo esc_attr(get_the_title()); ?>">
                                </div>

                                <div class="contacts__item-text">
                                    <p class="contacts__item-sub">
                                        <?php pll_e('Advertising department'); ?>
                                    </p>

                                    <a href="mailto:<?php echo esc_attr($ad_address) ?>" class="contacts__item-info">
                                        <?php echo esc_html($ad_address) ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($contact_tg_link || $contact_tg_text || $contact_phone): ?>
                        <div class="contacts__additional">
                            <?php if ($contact_tg_link && $contact_tg_text): ?>
                                <a href="<?php echo esc_url($contact_tg_link) ?>" class="contacts__additional-item">
                                    <div class="contacts__additional-icon">
                                        <img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/tg-icons.svg') ?>"
                                            alt="<?php echo esc_attr(get_the_title()) ?>">
                                    </div>

                                    <div class="contacts__additional-inner">
                                        <span class="contacts__additional-name">
                                            <?php pll_e('Telegram'); ?>
                                        </span>

                                        <span class="contacts__additional-link">
                                            <?php echo esc_html($contact_tg_text) ?>
                                        </span>
                                    </div>
                                </a>
                            <?php endif; ?>

                            <?php if ($contact_phone): ?>
                                <a href="tel:<?php echo esc_attr($contact_phone) ?>" class="contacts__additional-item">
                                    <div class="contacts__additional-icon">
                                        <img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/phone-icons.svg') ?>"
                                            alt="<?php echo esc_attr(get_the_title()) ?>">
                                    </div>

                                    <div class="contacts__additional-inner">
                                        <span class="contacts__additional-name">
                                            <?php pll_e('Phone number'); ?>
                                        </span>

                                        <span class="contacts__additional-link">
                                            <?php echo esc_html($contact_phone) ?>
                                        </span>
                                    </div>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($contact_question_title && $contact_question_text): ?>
                        <div class="contacts__questions">
                            <div class="contacts__questions-text">
                                <h2 class="contacts__questions-title">
                                    <?php echo esc_html($contact_question_title) ?>
                                </h2>

                                <div class="contacts__questions-more">
                                    <?php echo esc_html($contact_question_text) ?>
                                </div>
                            </div>

                            <a href="mailto:<?php echo ($edit_address); ?>" class="contacts__questions-button button">
                                <?php pll_e('Interactive'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</div>

<?php get_footer(); ?>