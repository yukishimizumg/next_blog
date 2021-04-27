<?php
require_once __DIR__ . '/../../common/config.php';
require_once __DIR__ . '/../../common/functions.php';
require_once __DIR__ . '/../../models/Post.class.php';

session_start();

$token = generateToken();
$alert = getAlert();
$notice = getNotice();
$current_user = getCurrentUser();

$id = filter_input(INPUT_GET, 'id');
$post = Post::findWithUser($id);

if (empty($post)) {
    redirectAlert(
        '/',
        MSG_POST_DOES_NOT_EXIST
    );
}

$comments = $post->findCommentsWithUser();
?>
<!DOCTYPE html>
<html lang="ja">

<?php include_once __DIR__ . '/../common/_head.php' ?>

<body>
    <?php include_once __DIR__ . '/../common/_header.php' ?>

    <div class="wrapper">
        <?php include_once __DIR__ . '/../common/_notice.php' ?>
        <?php include_once __DIR__ . '/../common/_alert.php' ?>

        <article class="post-detail">
            <h2 class="post-title"><?= h($post->getTitle()) ?></h2>
            <div class="post-user-area">
                <div class="post-user">
                    <img src="<?= h($post->getUser()->getAvatarPath()) ?>" alt="">
                    <p class="post-user-name"><?= h($post->getUser()->getName()) ?></p>
                </div>
                <div class="post-date"><?= h($post->getCreatedAt()) ?></div>
            </div>
            <div class="image-trim">
                <img src="<?= h($post->getImagePath()) ?>" alt="">
            </div>
            <p class="post-body"><?= nl2br(h($post->getBody())) ?></p>
            <div class="post-btn-edit-area">
                <a href="edit.php?id=<?= $post->getId() ?>" class="btn btn-edit">編集</a>
                <form action="delete.php" method="post">
                    <input type="hidden" name="token" value="<?= h($token) ?>">
                    <input type="hidden" name="id" value="<?= h($post->getId()) ?>">
                    <input type="submit" value="削除" class="btn btn-delete" onClick="return confirm('ブログを削除しますか？')">
                </form>
            </div>
        </article>
        <div class="comment">
            <div class="comment-header">
                <h3 class="comment-count">
                    コメント(<?= h($post->getCommentsCount()) ?>)
                </h3>
                <?php if ($current_user['id']) : ?>
                    <a href="/comments/new.php?post_id=<?= h($post->getId()) ?>" class="btn-comment-new">コメントする</a>
                <?php endif; ?>
            </div>
            <?php if ($comments) : ?>
                <hr class="comment-hr">
                <ul class="comment-list">
                    <?php foreach ($comments as $i => $c) : ?>
                        <li class="comment-list-item">
                            <div class="comment-no"><?= ++$i ?></div>
                            <div class="comment-detail">
                                <p class="comment-body"><?= nl2br(h($c->getComment())) ?></p>
                                <div class="comment-user-area">
                                    <div class="comment-user">
                                        <img src="<?= h($c->getUser()->getAvatarPath()) ?>" alt="">
                                        <h4 class="comment-user-name"><?= h($c->getUser()->getName()) ?></h4>
                                    </div>
                                    <p class="comment-date"><?= h($c->getCreatedAt()) ?></p>
                                    <div class="comment-btn-area">
                                        <a href="/comments/edit.php" class="comment-edit">編集</a>
                                        <form action="/comments/delete.php" method="post">
                                            <input type="submit" value="削除" class="comment-delete" onClick="return confirm('ブログのコメントを削除しますか？')">
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <?php include_once __DIR__ . '/../common/_footer.php' ?>
</body>

</html>