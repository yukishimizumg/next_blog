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

$post = new Post(getPostData());
$categories = Category::findAll();
?>
<!DOCTYPE html>
<html lang="ja">

<?php include_once __DIR__ . '/../common/_head.php' ?>

<body>
    <?php include_once __DIR__ . '/../common/_header.php' ?>

    <div class="wrapper">
        <div class="form-main">
            <h2 class="title">ブログ登録</h2>
            <?php include_once __DIR__ . '/../common/_alert.php' ?>

            <form action="create.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">タイトル<span class="required">必須</span></label>
                    <input type="text" id="title" name="post[title]" placeholder="タイトルを入力してください" required value="<?= h($post->getTitle()) ?>" <?php if ($errors['title']) echo 'class="error-field"' ?>>
                    <?php if ($errors['title']) echo (createErrMsg($errors['title'])) ?>
                </div>
                <div class="form-group">
                    <label for="category">カテゴリー<span class="required">必須</span></label>
                    <select name="post[category_id]" id="category" placeholder="" required <?php if ($errors['category_id']) echo 'class="error-field"' ?>>
                        <option disabled value="" <?php if (empty($post->getCategoryId())) echo 'selected' ?>>選択してください</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c->getId() ?>" <?php if ($c->getId() === $post->getCategoryId()) echo 'selected' ?>><?= h($c->getName()) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($errors['category_id']) echo (createErrMsg($errors['category_id'])) ?>
                </div>
                <div class="form-group">
                    <label for="body">本文<span class="required">必須</span></label>
                    <textarea name="post[body]" id="body" rows="10" placeholder="本文を入力してください" required <?php if ($errors['body']) echo 'class="error-field"' ?>><?= h($post->getBody()) ?></textarea>
                    <?php if ($errors['body']) echo (createErrMsg($errors['body'])) ?>
                </div>
                <div class="form-group">
                    <label for="image">イメージ画像</label>
                    <input type="file" name="image" id="image" <?php if ($errors['image']) echo 'class="error-field"' ?>>
                    <?php if ($errors['image']) echo (createErrMsg($errors['image'])) ?>
                </div>
                <input type="hidden" name="token" value="<?= h($token) ?>">
                <div class="form-group">
                    <input type="submit" class="btn" value="登録">
                </div>
            </form>
        </div>
    </div>

    <?php include_once __DIR__ . '/../common/_footer.php' ?>
</body>

</html>