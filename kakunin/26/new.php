<?php
require_once __DIR__ . '/../../common/config.php';
require_once __DIR__ . '/../../common/functions.php';
require_once __DIR__ . '/../../models/Post.class.php';
require_once __DIR__ . '/../../models/Comment.class.php';

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

$post_id = filter_input(INPUT_GET, 'post_id');
$post = Post::find($post_id);

if (empty($post)) {
    redirectAlert(
        '/',
        MSG_POST_DOES_NOT_EXIST
    );
}

$comment = new Comment(getPostData());
?>
<!DOCTYPE html>
<html lang="ja">

<?php include_once __DIR__ . '/../common/_head.php' ?>

<body>
    <?php include_once __DIR__ . '/../common/_header.php' ?>

    <div class="wrapper">
        <?php include_once __DIR__ . '/_post.php' ?>
        <?php include_once __DIR__ . '/../common/_alert.php' ?>
        
        <form action="create.php" method="post">
            <?php include_once __DIR__ . '/_form.php' ?>
            <div class="form-group">
                <input type="submit" class="btn" value="登録">
            </div>
        </form>
    </div>

    <?php include_once __DIR__ . '/../common/_footer.php' ?>
</body>

</html>