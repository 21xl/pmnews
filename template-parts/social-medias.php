<?php if (have_rows('social_media', 'options')): ?>
    <div class="social-medias">
        <?php while (have_rows('social_media', 'options')):
            the_row();
            $icon_media = get_sub_field('icon');
            $link_media = get_sub_field('link');

            if ($icon_media && $link_media): ?>
                <a href="<?php echo esc_url($link_media); ?>" target="_blank" class="social-medias__icon">
                    <img src="<?php echo esc_url($icon_media); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                </a>
            <?php endif; ?>
        <?php endwhile; ?>
    </div>
<?php endif; ?>