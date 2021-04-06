<?php
require_once __DIR__ . "/../../common/config.php";
require_once __DIR__ . "/../../common/functions.php";

session_start();

$_SESSION = [];

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 86400);
}
session_regenerate_id(true);
redirectNotice(
    '/',
    MSG_SIGN_OUT
);