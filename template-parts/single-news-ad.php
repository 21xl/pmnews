<?php
$banner = get_field('ad_single', 'option');

if ($banner):
    $banner_img = esc_url($banner['image']);
    $banner_link = esc_url($banner['link']);
    ?>
    <a href="<?php echo $banner_link; ?>" class="single-banner" style="background:url('<?php echo $banner_img; ?>')"
        rel="nofollow noopener" target="_blank">
    </a>
<?php endif; ?>