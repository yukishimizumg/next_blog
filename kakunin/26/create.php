<?php
require_once __DIR__ . '/../../common/config.php';
require_once __DIR__ . '/../../common/functions.php';
require_once __DIR__ . '/../../models/Comment.class.php';
require_once __DIR__ . '/../../models/Post.class.php';

session_start();

if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
    // トークンの検証
    $token = filter_input(INPUT_POST, 'token');
    if (empty($_SESSION['token']) || $_SESSION['token'] !== $token) {
        redirectAlert(
            '/',
            MSG_BAD_REQUEST
        );
    }

    // ログイン判定
    $current_user = getCurrentUser();
    if (empty($current_user)) {
        redirectAlert(
            '/users/log_in.php',
            MSG_PLEASE_SIGN_IN
        );
    }

    // 入力内容の受け取り
    $input_params =
        filter_input(
            INPUT_POST,
            'comment',
            FILTER_DEFAULT,
            FILTER_REQUIRE_ARRAY
        );
    // ログインユーザーの情報もセット
    $input_params['current_user'] = $current_user;

    // コメントするブログの情報取得
    $post = Post::find($input_params['post_id']);
    if (empty($post)) {
        redirectAlert(
            '/',
            MSG_POST_DOES_NOT_EXIST
        );
    }

    $comment = new Comment(Comment::setParams($input_params));

    // バリデーション & 登録
    if ($comment->validate() && $comment->insert()) {
        redirectNotice(
            "/posts/show.php?id={$comment->getPostId()}",
            MSG_COMMENT_REGISTER
        );
    } else {
        redirectAlert(
            "new.php?post_id={$comment->getPostId()}",
            MSG_COMMENT_CANT_REGISTER,
            $input_params,
            $comment->getErrors()
        );
    }
}