<?php
$popup_img = get_field('popup_img', 'options');
$popup_show = get_field('popup_show', 'options');
$popup_delay = get_field('popup_delay', 'options');
$popup_link = get_field('popup_link', 'options');

if (empty($popup_delay)) {
    $popup_delay = 12;
}
?>

<?php if ($popup_show):
    if (!empty($popup_img)): ?>
        <div class="popup" data-delay="<?php echo esc_attr($popup_delay); ?>">
            <div class="popup__wrapper" style="background:url('<?php echo esc_url($popup_img); ?>')">
                <a href="<?php echo esc_url($popup_link) ?>" class="popup__modal">
                </a>

                <div class="popup__cancel">
                    <img src="<?php echo esc_attr(get_template_directory_uri() . '/src/img/close.svg') ?>" alt="Close">
                </div>
            </div>
        </div>
    <?php endif;
endif; ?>