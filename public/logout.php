<?php
require_once __DIR__ . '/inc/bootstrap.php';
session_destroy();
header('Location: login_roles.php');
exit;
