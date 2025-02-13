<?php
$blue_popup_logo = get_field('blue_popup_logo', 'options');
$blue_popup_show = get_field('blue_popup_show', 'options');
$blue_popup_delay = get_field('blue_popup_delay', 'options');
$blue_popup_text = get_field('blue_popup_text', 'options');
$blue_popup_link = get_field('blue_popup_link', 'options');

if (empty($blue_popup_delay)) {
    $blue_popup_delay = 10;
}
?>

<?php if ($blue_popup_show): ?>
    <?php if ($blue_popup_logo || $blue_popup_text || $blue_popup_link): ?>
        <div class="blue-popup" data-delay="<?php echo esc_attr($blue_popup_delay); ?>">
            <div class="blue-popup__wrapper">
                <div class="blue-popup__left">
                    <div class="blue-popup__img">
                        <img src="<?php echo esc_url($blue_popup_logo); ?>" alt="<?php echo esc_html($blue_popup_text); ?>">
                    </div>

                    <span class="blue-popup__text">
                        <?php echo esc_html($blue_popup_text); ?>
                    </span>
                </div>

                <div class="blue-popup__right">
                    <a href="<?php echo esc_url($blue_popup_link); ?>" class="blue-popup__link">
                        <?php pll_e('Сделать ставку'); ?>
                    </a>

                    <div class="blue-popup__cancel">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/blue-x.svg'); ?>" alt="Close">
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>