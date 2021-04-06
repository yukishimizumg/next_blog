<?php
require_once __DIR__ . "/../../common/functions.php";
require_once __DIR__ . "/../../models/User.class.php";

session_start();

$token = generateToken();
$alert = getAlert();
$errors = getErrors();
$user = new User(getPostData());
?>
<!DOCTYPE html>
<html lang="ja">

<?php include __DIR__ . "/../common/_head.php"; ?>

<body>
    <?php include __DIR__ . "/../common/_header.php"; ?>

    <div class="wrapper user-wrapper">
        <div class="form-main">
            <h2 class="title">アカウント登録</h2>
            <?php include __DIR__ . "/../common/_alert.php"; ?>

            <form action="create.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="email">メールアドレス<span class="required">必須</span></label>
                    <input type="email" id="email" name="user[email]" placeholder="メールアドレスを入力してください" required value="<?= h($user->getEmail()); ?>" <?php if ($errors['email']) echo 'class="error-field"'; ?>>
                    <?php if ($errors['email']) echo (createErrMsg($errors['email'])); ?>
                </div>
                <div class="form-group">
                    <label for="password">パスワード<span class="required">必須</span></label>
                    <input type="password" id="password" name="user[password]" placeholder="半角英数を組み合わせた8文字以上" required <?php if ($errors['password']) echo 'class="error-field"'; ?>>
                    <?php if ($errors['password']) echo (createErrMsg($errors['password'])); ?>
                </div>
                <div class="form-group">
                    <label for="confirm_password">確認用パスワード<span class="required">必須</span></label>
                    <input type="password" id="confirm_password" name="user[confirm_password]" placeholder="半角英数を組み合わせた8文字以上" required <?php if ($errors['confirm_password']) echo 'class="error-field"'; ?>>
                    <?php if ($errors['confirm_password']) echo (createErrMsg($errors['confirm_password'])); ?>
                </div>
                <div class="form-group">
                    <label for="name">ユーザー名<span class="required">必須</span></label>
                    <input type="text" id="name" name="user[name]" placeholder="ユーザー名を入力してください" required value="<?= h($user->getName()); ?>" <?php if ($errors['name']) echo 'class="error-field"'; ?>>
                    <?php if ($errors['name']) echo (createErrMsg($errors['name'])); ?>
                </div>
                <div class="form-group">
                    <label for="profile">自己紹介<span class="required">必須</span></label>
                    <textarea id="profile" name="user[profile]" rows="5" placeholder="自己紹介を入力してください" required <?php if ($errors['profile']) echo 'class="error-field"'; ?>><?= h($user->getProfile()); ?></textarea>
                    <?php if ($errors['profile']) echo (createErrMsg($errors['profile'])); ?>
                </div>
                <div class="form-group">
                    <label for="avatar">プロフィール画像</label>
                    <input type="file" name="avatar" id="avatar"
                        <?php if ($errors['avatar']) echo 'class="error-field"'; ?>>
                    <?php if ($errors['avatar']) echo (createErrMsg($errors['avatar'])); ?>
                </div>
                <input type="hidden" name="token" value="<?= h($token); ?>">
                <div class="form-group">
                    <input type="submit" class="btn" value="登録">
                </div>
            </form>
        </div>
    </div>

    <?php include __DIR__ . "/../common/_footer.php"; ?>
</body>

</html>