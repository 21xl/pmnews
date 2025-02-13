<?php
$banner = get_field('ad_mobile', 'option');

if ($banner):
    $banner_img = esc_url($banner['image']);
    $banner_link = esc_url($banner['link']);
    ?>
    <a href="<?php echo esc_url($banner_link); ?>" class="mobile-banner"
        style="background:url('<?php echo esc_url($banner_img); ?>')" rel="nofollow noopener" target="_blank">

    </a>
    <?php
endif;
?>