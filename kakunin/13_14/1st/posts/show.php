<?php
require_once __DIR__ . "/../../models/Post.class.php";

$id = filter_input(INPUT_GET, 'id');
$post = Post::find($id);

$comments = $post->findCommentsWithUser();
?>
<!DOCTYPE html>
<html lang="ja">

<?php include __DIR__ . "/../common/_head.php"; ?>

<body>
    <?php include __DIR__ . "/../common/_header.php"; ?>

    <div class="wrapper">
        <article class="post-detail">
            <h2 class="post-title"><?= h($post->getTitle()); ?></h2>
            <div class="post-user-area">
                <div class="post-user">
                    <img src="<?= h($post->getUser()->getAvatarPath()); ?>" alt="">
                    <p class="post-user-name"><?= h($post->getUser()->getName()); ?></p>
                </div>
                <div class="post-date"><?= h($post->getCreatedAt()); ?></div>
            </div>
            <div class="image-trim">
                <img src="<?= h($post->getImagePath()); ?>" alt="">
            </div>
            <p class="post-body"><?= nl2br(h($post->getBody())); ?></p>
            <div class="post-btn-edit-area">
                <a href="edit.php" class="btn btn-edit">編集</a>
                <form action="delete.php" method="post">
                    <input type="submit" value="削除" class="btn btn-delete" onClick="return confirm('ブログを削除しますか？')">
                </form>
            </div>
        </article>
        <div class="comment">
            <div class="comment-header">
                <h3 class="comment-count">
                    コメント(<?= h($post->getCommentsCount()); ?>)
                </h3>
                <a href="/comments/new.php" class="btn-comment-new">コメントする</a>
            </div>
            <?php if ($comments) : ?>
                <hr class="comment-hr">
                <ul class="comment-list">
                    <?php foreach ($comments as $i => $c) : ?>
                        <li class="comment-list-item">
                            <div class="comment-no"><?= ++$i; ?></div>
                            <div class="comment-detail">
                                <p class="comment-body"><?= nl2br(h($c->getComment())); ?></p>
                                <div class="comment-user-area">
                                    <div class="comment-user">
                                        <img src="<?= h($c->getUser()->getAvatarPath()); ?>" alt="">
                                        <h4 class="comment-user-name"><?= h($c->getUser()->getName()); ?></h4>
                                    </div>
                                    <p class="comment-date"><?= h($c->getCreatedAt()); ?></p>
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


    <?php include __DIR__ . "/../common/_footer.php"; ?>
</body>

</html>
