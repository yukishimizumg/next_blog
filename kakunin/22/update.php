<?php
require_once __DIR__ . '/../../common/config.php';
require_once __DIR__ . '/../../common/functions.php';
require_once __DIR__ . '/../../models/User.class.php';

session_start();

if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
    $token = filter_input(INPUT_POST, 'token');

    if (empty($_SESSION['token']) || $_SESSION['token'] !== $token) {
        redirectAlert(
            '/',
            MSG_BAD_REQUEST
        );
    }
    if (empty($_SESSION['current_user'])) {
        redirectAlert(
            '/users/log_in.php',
            MSG_PLEASE_SIGN_IN
        );
    }

    // ログインユーザー情報の取得
    $id = $_SESSION['current_user']['id'];
    $user = User::find($id);

    // 入力内容の受け取り
    $input_params =
        filter_input(
            INPUT_POST,
            'user',
            FILTER_DEFAULT,
            FILTER_REQUIRE_ARRAY
        );

    // プロパティの上書き
    $user->updatePropaty($input_params);

    // バリデーション & 更新
    if ($user->updateValidate() && $user->update()) {
        redirectNotice(
            '/',
            MSG_USER_UPDATE
        );
    } else {
        redirectAlert(
            "edit.php?id={$id}",
            MSG_USER_CANT_UPDATE,
            $input_params,
            $user->getErrors()
        );
    }
}