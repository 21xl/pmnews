<?php
$banner_tg = get_field('banner_tg', 'option');

if ($banner_tg):
    $banner_tg_img = esc_url($banner_tg['image']);
    $banner_tg_link = esc_url($banner_tg['link']);
    ?>
    <section class="tg-banner">
        <div class="tg-banner__wrapper wrapper">
            <a href="<?php echo esc_url($banner_tg_link); ?>" class="tg-banner__content"
                style="background:url('<?php echo esc_url($banner_tg_img); ?>')" rel="nofollow noopener" target="_blank">
            </a>
        </div>
    </section>
<?php endif; ?>