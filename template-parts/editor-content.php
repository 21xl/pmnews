<?php
$content = get_the_content();
if (!empty($content)): ?>
    <section class="editor-content">
        <div class="editor-content__wrapper wrapper">
            <?php the_content() ?>
            <?php
            if (has_term(array('predictions', 'transliacii'), 'category')) {
                echo '<em>' . pll__('Be the first to know the results of all football matches on our page with') . ' <a href="/statistics/">' . pll__('Live-результатами') . '</a></em>';
            }
            ?>



        </div>
    </section>
<?php endif; ?>