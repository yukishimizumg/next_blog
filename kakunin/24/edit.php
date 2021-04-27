<?php
require_once __DIR__ . '/../../common/config.php';
require_once __DIR__ . '/../../common/functions.php';
require_once __DIR__ . '/../../models/Category.class.php';
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
// idを基に編集するブログを取得
$post = Post::find($id);

// ブログの存在チェック
if (empty($post)) {
    redirectAlert(
        '/',
        MSG_POST_DOES_NOT_EXIST
    );
}

// 自分のブログかチェック
if ($current_user['id'] !== $post->getUserId()) {
    redirectAlert(
        "show.php?id={$id}",
        MSG_POST_CANNOT_BE_MODIFIED
    );
}

// 入力内容を取得してインスタンスのプロパティを更新
$post_data = getPostData();
if ($post_data) {
    $post->updateProperty($post_data);
}

$categories = Category::findAll();
?>
<!DOCTYPE html>
<html lang="ja">
<?php include_once __DIR__ . '/../common/_head.php' ?>

<body>
    <?php include_once __DIR__ . '/../common/_header.php' ?>

    <div class="wrapper">
        <div class="form-main">
            <h2 class="title">ブログ更新</h2>
            <?php include_once __DIR__ . '/../common/_alert.php' ?>

            <form action="update.php" method="post" enctype="multipart/form-data">
                <?php include_once __DIR__ . '/_form.php' ?>
                <input type="hidden" name="post[id]" value=<?= h($post->getId()) ?>>
                <div class="form-group">
                    <input type="submit" class="btn" value="更新">
                </div>
            </form>
        </div>
    </div>

    <?php include_once __DIR__ . '/../common/_footer.php' ?>
</body>

</html>