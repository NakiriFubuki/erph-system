<?php
/**
 * Entry page: redirect straight to the login page
 */
$query = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== ''
    ? '?' . $_SERVER['QUERY_STRING']
    : '';
header('Location: public/login_roles.php' . $query, true, 302);
exit;
