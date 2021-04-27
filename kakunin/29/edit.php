<?php
require_once __DIR__ . '/../../common/config.php';
require_once __DIR__ . '/../../common/functions.php';
require_once __DIR__ . '/../../models/User.class.php';

session_start();

$token = generateToken();
$alert = getAlert();
$errors = getErrors();
$post_data = getPostData();
$current_user = getCurrentUser();

// ログイン判定
if (empty($current_user)) {
    redirectAlert(
        '/users/log_in.php',
        MSG_PLEASE_SIGN_IN
    );
}

$user = User::find($current_user['id']);
if ($post_data) {
    $user->updateProperty($post_data);
}
?>
<!DOCTYPE html>
<html lang="ja">

<?php include_once __DIR__ . '/../common/_head.php' ?>

<body>
    <?php include_once __DIR__ . '/../common/_header.php' ?>

    <div class="wrapper user-wrapper">
        <div class="form-main">
            <h2 class="title">アカウント更新</h2>
            <?php include_once __DIR__ . '/../common/_alert.php' ?>

            <form action="update.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="email">メールアドレス<span class="required">必須</span></label>
                    <input type="email" id="email" name="user[email]" placeholder="メールアドレスを入力してください" required value="<?= h($user->getEmail()) ?>" <?php if ($errors['email']) echo 'class="error-field"' ?>>
                    <?php if ($errors['email']) echo (createErrMsg($errors['email'])) ?>
                </div>
                <div class="form-group">
                    <label for="now_password">現在のパスワード（パスワード変更時に入力）</label>
                    <input type="password" id="now_password" name="user[now_password]" placeholder="半角英数を組み合わせた8文字以上" <?php if ($errors['now_password']) echo 'class="error-field"' ?>>
                    <?php if ($errors['now_password']) echo (createErrMsg($errors['now_password'])) ?>
                </div>
                <div class="form-group">
                    <label for="password">新しいパスワード</label>
                    <input type="password" id="password" name="user[password]" placeholder="半角英数を組み合わせた8文字以上" <?php if ($errors['password']) echo 'class="error-field"' ?>>
                    <?php if ($errors['password']) echo (createErrMsg($errors['password'])) ?>
                </div>
                <div class="form-group">
                    <label for="confirm_password">確認用パスワード</label>
                    <input type="password" id="confirm_password" name="user[confirm_password]" placeholder="半角英数を組み合わせた8文字以上" <?php if ($errors['confirm_password']) echo 'class="error-field"' ?>>
                    <?php if ($errors['confirm_password']) echo (createErrMsg($errors['confirm_password'])) ?>
                </div>
                <div class="form-group">
                    <label for="name">ユーザー名<span class="required">必須</span></label>
                    <input type="text" id="name" name="user[name]" placeholder="ユーザー名を入力してください" required value="<?= h($user->getName()) ?>" <?php if ($errors['name']) echo 'class="error-field"' ?>>
                    <?php if ($errors['name']) echo (createErrMsg($errors['name'])) ?>
                </div>
                <div class="form-group">
                    <label for="profile">自己紹介<span class="required">必須</span></label>
                    <textarea id="profile" name="user[profile]" rows="5" placeholder="自己紹介を入力してください" required <?php if ($errors['profile']) echo 'class="error-field"' ?>><?= h($user->getProfile()) ?></textarea>
                    <?php if ($errors['profile']) echo (createErrMsg($errors['profile'])) ?>
                </div>
                <div class="form-group">
                    <label for="avatar">プロフィール画像</label>
                    <?php if ($user->getAvatar()): ?>
                        <img src="<?= $user->getAvatarPath() ?>" alt="" class="image-preview">
                    <?php endif; ?>
                    <input type="file" name="avatar" id="avatar">
                    <?php if ($errors['avatar']) echo (createErrMsg($errors['avatar'])) ?>
                </div>
                <input type="hidden" name="token" value="<?= h($token) ?>">
                <div class="form-group">
                    <input type="submit" class="btn" value="更新">
                </div>
            </form>
            <form action="delete.php" method="post">
                <input type="hidden" name="token" value="<?= h($token) ?>">
                <input type="hidden" name="id" value="<?= h($user->getId()) ?>">
                <input type="submit" value="削除" class="btn btn-delete" onClick="return confirm('アカウントを削除しますか？')">
            </form>
        </div>
    </div>

    <?php include_once __DIR__ . '/../common/_footer.php' ?>
</body>

</html>