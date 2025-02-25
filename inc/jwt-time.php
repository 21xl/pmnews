<?php
add_filter('jwt_auth_expire', 'oaf_set_token_expire_seconds', 10, 2);
function oaf_set_token_expire_seconds()
{
    $expire = time() + 9999999;
    return $expire;
}