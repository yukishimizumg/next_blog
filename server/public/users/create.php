<?php
require_once __DIR__ . "/../../common/config.php";
require_once __DIR__ . "/../../models/User.class.php";

session_start();

if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
    $token = filter_input(INPUT_POST, 'token');
    if (empty($_SESSION['token']) || $_SESSION['token'] !== $token) {
        redirectAlert(
            '/',
            MSG_BAD_REQUEST
        );
    }

    $input_params = 
        filter_input(
            INPUT_POST,
            'user',
            FILTER_DEFAULT,
            FILTER_REQUIRE_ARRAY
        );
    $user = new User(User::setParams($input_params));

    // バリデーション & 登録
    if ($user->validate() && $user->insert()) {
        redirectNotice(
            '/',
            MSG_USER_REGISTER
        );
    } else {
        redirectAlert(
            'new.php',
            MSG_USER_CANT_REGISTER,
            $input_params,
            $user->getErrors()
        );
    }
}

