<?php
function set_headers_for_304()
{
    if (is_front_page() || is_home()) {
        // Получаем время последнего изменения (для примера - текущее время)
        $last_modified = gmdate('D, d M Y H:i:s', strtotime('-1 day')) . ' GMT'; // Измените при необходимости
        $etag = md5($last_modified); // Генерация ETag на основе времени

        // Устанавливаем заголовки
        header("Last-Modified: $last_modified");
        header("ETag: \"$etag\"");

        // Проверяем условные запросы от клиента
        if (
            (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= strtotime($last_modified)) ||
            (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag)
        ) {
            header("HTTP/1.1 304 Not Modified");
            exit; // Завершаем выполнение
        }
    }
}
add_action('template_redirect', 'set_headers_for_304');