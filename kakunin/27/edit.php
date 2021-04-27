<?php
require_once __DIR__ . '/../../common/config.php';
require_once __DIR__ . '/../../common/functions.php';
require_once __DIR__ . '/../../models/Comment.class.php';
require_once __DIR__ . '/../../models/Post.class.php';

session_start();

$token = generateToken();
$alert = getAlert();
$errors = getErrors();

// ログイン判定
$current_user = getCurrentUser();
if (empty($current_user)) {
    redirectAlert(
        '/users/log_in.php',
        MSG_PLEASE_SIGN_IN
    );
}

// URLパラメーターからidを取得
$id = filter_input(INPUT_GET, 'id');
$comment = Comment::find($id);

if (empty($comment)) {
    redirectAlert(
        '/',
        MSG_COMMENT_DOES_NOT_EXIST
    );
}

// 自分のコメントかチェック
if ($current_user['id'] !== $comment->getUserId()) {
    redirectAlert(
        "/posts/show.php?id={$comment->getPostId()}",
        MSG_COMMENT_CANNOT_BE_MODIFIED
    );
}

// 入力内容を取得してインスタンスのプロパティを更新
$post_data = getPostData();
if ($post_data) {
    $comment->updateProperty($post_data);
}

$post = $comment->getPost();
?>
<!DOCTYPE html>
<html lang="ja">

<?php include_once __DIR__ . '/../common/_head.php' ?>

<body>
    <?php include_once __DIR__ . '/../common/_header.php' ?>

    <div class="wrapper">
        <?php include_once __DIR__ . '/_post.php' ?>
        <?php include_once __DIR__ . '/../common/_alert.php' ?>

        <form action="update.php" method="post">
            <?php include_once __DIR__ . '/_form.php' ?>
            <input type="hidden" name="comment[id]" value="<?= h($comment->getId()) ?>">
            <div class="form-group">
                <input type="submit" class="btn" value="更新">
            </div>
        </form>
    </div>

    <?php include_once __DIR__ . '/../common/_footer.php' ?>
</body>

</html>