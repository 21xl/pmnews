<?php
/**
 * Remove xmlrpc.
 */
add_filter('xmlrpc_enabled', '__return_false');

add_action('init', function () {
    if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
        wp_die('XML-RPC service is disabled on this site.', 'XML-RPC Disabled', array('response' => 403));
    }
});

remove_action('wp_head', 'rsd_link');

/**
 * Hide version WP.
 */
remove_action('wp_head', 'wp_generator');

/**
 * Disable default editor theme.
 */
define('DISALLOW_FILE_EDIT', true);


/**
 *Security the_content(); 
 */
$allowed_tags = array(
    'a' => array(
        'href' => array(),
        'title' => array(),
        'rel' => array(),
        'target' => array()
    ),
    'abbr' => array(
        'title' => array()
    ),
    'b' => array(),
    'blockquote' => array(
        'cite' => array()
    ),
    'cite' => array(),
    'code' => array(),
    'del' => array(
        'datetime' => array()
    ),
    'dd' => array(),
    'div' => array(
        'class' => array(),
        'id' => array(),
        'style' => array()
    ),
    'dl' => array(),
    'dt' => array(),
    'em' => array(),
    'h1' => array(),
    'h2' => array(),
    'h3' => array(),
    'h4' => array(),
    'h5' => array(),
    'h6' => array(),
    'i' => array(),
    'img' => array(
        'alt' => array(),
        'src' => array(),
        'height' => array(),
        'width' => array(),
        'class' => array(),
        'id' => array(),
        'style' => array()
    ),
    'li' => array(
        'class' => array()
    ),
    'ol' => array(
        'class' => array(),
        'start' => array(),
        'type' => array()
    ),
    'p' => array(
        'class' => array(),
        'style' => array()
    ),
    'q' => array(
        'cite' => array()
    ),
    'span' => array(
        'class' => array(),
        'style' => array()
    ),
    'strike' => array(),
    'strong' => array(),
    'sub' => array(),
    'sup' => array(),
    'table' => array(
        'class' => array(),
        'style' => array()
    ),
    'tbody' => array(),
    'td' => array(
        'colspan' => array(),
        'rowspan' => array(),
        'class' => array(),
        'style' => array()
    ),
    'tfoot' => array(),
    'th' => array(
        'colspan' => array(),
        'rowspan' => array(),
        'scope' => array(),
        'class' => array(),
        'style' => array()
    ),
    'thead' => array(),
    'tr' => array(
        'class' => array(),
        'style' => array()
    ),
    'ul' => array(
        'class' => array()
    ),
    'br' => array(),
    'hr' => array(),
    'pre' => array(),
    'blockquote' => array(
        'cite' => array()
    ),
    'ins' => array(
        'datetime' => array(),
        'cite' => array()
    ),
    'del' => array(
        'datetime' => array(),
        'cite' => array()
    ),
    'small' => array(),
    'big' => array(),
    'tt' => array(),
    'kbd' => array(),
    'samp' => array(),
    'var' => array(),
    'bdo' => array(
        'dir' => array()
    ),
    'cite' => array(),
    'abbr' => array(
        'title' => array()
    ),
    'acronym' => array(
        'title' => array()
    ),
    'address' => array(),
    'center' => array(),
    'time' => array(),
    'mark' => array(),
    'bdi' => array(),
    'wbr' => array(),
);

echo wp_kses(get_the_content(), $allowed_tags);
