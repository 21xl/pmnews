<?php
$banner = get_field('ad_mobile', 'options');

if (
    is_array($banner) &&
    !empty($banner['image']) &&
    !empty($banner['link'])
):
    $banner_img = esc_url($banner['image']);
    $banner_link = esc_url($banner['link']);
    ?>
    <a href="<?php echo $banner_link; ?>" class="mobile-banner" style="background: url('<?php echo $banner_img; ?>')"
        rel="nofollow noopener" target="_blank">
    </a>
<?php endif; ?>