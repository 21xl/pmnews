<?php
$content = get_the_content();
if (!empty($content)): ?>
    <section class="editor-content">
        <div class="editor-content__wrapper wrapper">
            <?php the_content() ?>
            <?php
            if (has_term(array('predictions', 'transliacii'), 'category')) {
                echo '<em>' . pll__('Узнавайте итоги всех футбольных матчей первыми на нашей странице с') . ' <a href="/statistics/">' . pll__('Live-результатами') . '</a></em>';
            }
            ?>



        </div>
    </section>
<?php endif; ?>