<?php
/*
Template Name: Главная страница
*/

get_template_part('head');
?>

<div class="page">
  <?php get_template_part('template-parts/aside') ?>

  <div class="content">
    <?php get_header(); ?>

    <main>
      <?php get_template_part('template-parts/hero') ?>

      <?php if (have_rows('blocks')):
        while (have_rows('blocks')):
          the_row();

          if (get_row_layout() == 'daily_news') {
            get_template_part('template-parts/daily-news');
          } elseif (get_row_layout() == 'home_news') {
            get_template_part('template-parts/home-news');
          } elseif (get_row_layout() == 'youtube_slider') {
            get_template_part('template-parts/youtube-slider');
          } elseif (get_row_layout() == 'category_slider') {
            get_template_part('template-parts/category-slider');
          }

        endwhile;
      endif; ?>
    </main>

    <?php get_template_part('template-parts/blue-popup') ?>

  </div>
</div>

<?php get_footer(); ?>