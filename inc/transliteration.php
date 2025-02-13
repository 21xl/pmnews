<?php
function transliterate_slug($slug)
{
    $map = array_combine(
        [
            'а',
            'ә',
            'б',
            'в',
            'г',
            'ғ',
            'д',
            'е',
            'ё',
            'ж',
            'з',
            'и',
            'й',
            'к',
            'қ',
            'л',
            'м',
            'н',
            'ң',
            'о',
            'ө',
            'п',
            'р',
            'с',
            'т',
            'у',
            'ұ',
            'ү',
            'ф',
            'х',
            'һ',
            'ц',
            'ч',
            'ш',
            'щ',
            'ы',
            'і',
            'э',
            'ю',
            'я',
            'А',
            'Ә',
            'Б',
            'В',
            'Г',
            'Ғ',
            'Д',
            'Е',
            'Ё',
            'Ж',
            'З',
            'И',
            'Й',
            'К',
            'Қ',
            'Л',
            'М',
            'Н',
            'Ң',
            'О',
            'Ө',
            'П',
            'Р',
            'С',
            'Т',
            'У',
            'Ұ',
            'Ү',
            'Ф',
            'Х',
            'Һ',
            'Ц',
            'Ч',
            'Ш',
            'Щ',
            'Ы',
            'І',
            'Э',
            'Ю',
            'Я'
        ],
        [
            'a',
            'a',
            'b',
            'v',
            'g',
            'g',
            'd',
            'e',
            'yo',
            'zh',
            'z',
            'i',
            'y',
            'k',
            'k',
            'l',
            'm',
            'n',
            'ng',
            'o',
            'o',
            'p',
            'r',
            's',
            't',
            'u',
            'u',
            'u',
            'f',
            'h',
            'h',
            'ts',
            'ch',
            'sh',
            'sch',
            'y',
            'i',
            'e',
            'yu',
            'ya',
            'A',
            'A',
            'B',
            'V',
            'G',
            'G',
            'D',
            'E',
            'Yo',
            'Zh',
            'Z',
            'I',
            'Y',
            'K',
            'K',
            'L',
            'M',
            'N',
            'Ng',
            'O',
            'O',
            'P',
            'R',
            'S',
            'T',
            'U',
            'U',
            'U',
            'F',
            'H',
            'H',
            'Ts',
            'Ch',
            'Sh',
            'Sch',
            'Y',
            'I',
            'E',
            'Yu',
            'Ya'
        ]
    );

    $slug = strtr($slug, $map);
    // Убираем неизвестные символы
    $slug = preg_replace('/[^a-zA-Z0-9\-]+/', '', $slug);
    $slug = trim($slug, '-');

    return $slug;
}

// Фильтр для уникальных слагов записей (постов и страниц)
add_filter('wp_unique_post_slug', function ($slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug) {
    return transliterate_slug(urldecode($original_slug));
}, 10, 6);

// Фильтр для уникальных слагов таксономий (рубрики, метки и другие)
add_filter('wp_unique_term_slug', function ($slug, $term, $taxonomy) {
    return transliterate_slug(urldecode($slug));
}, 10, 3);

// Фильтр для слагов авторов
add_filter('get_the_author_user_nicename', function ($slug) {
    return transliterate_slug(urldecode($slug));
});

// Фильтр для слагов кастомных URL (архивы, кастомные таксономии и т.д.)
add_filter('request', function ($query_vars) {
    if (isset($query_vars['name'])) {
        $query_vars['name'] = transliterate_slug(urldecode($query_vars['name']));
    }
    if (isset($query_vars['author_name'])) {
        $query_vars['author_name'] = transliterate_slug(urldecode($query_vars['author_name']));
    }
    return $query_vars;
});