<?php
require_once __DIR__ . "/../models/Post.class.php";
require_once __DIR__ . "/../models/Category.class.php";

$categories = Category::findAll();

$category_id = filter_input(INPUT_GET, 'category_id') ?? '';
$page = filter_input(INPUT_GET, 'page') ?? 1;

$posts = Post::findIndexView($category_id, $page);
$total_count = Post::findIndexViewCount($category_id);
?>
<!DOCTYPE html>
<html lang="ja">

<?php include __DIR__ . "/common/_head.php"; ?>

<body>
    <?php include __DIR__ . "/common/_header.php"; ?>
    <div class="wrapper">
        <div class="mobile-area">
            <a href="/posts/new.php" class="btn btn-block btn-new">ブログを書く</a>
        </div>
        <div class="post-index-main">
            <article class="post-index-group">
                <?php foreach ($posts as $p) : ?>
                    <section class="post-index-list">
                        <div class="image-trim">
                            <a href="/posts/show.php?id=<?= h($p->getId()); ?>">
                                <img src="<?= h($p->getImagePath()); ?>" alt="">
                            </a>
                        </div>
                        <h3 class="post-index-title"><?= h($p->getTitle()); ?></h3>
                        <p>
                            <?= mb_substr(h($p->getBody()), 1, 50); ?><span><a href="/posts/show.php?id=<?= h($p->getId()); ?>">続きを読む</a></span>
                        </p>
                        <div class="post-index-detail">
                            <div class="post-index-user">
                                <img src="<?= $p->getUser()->getAvatarPath(); ?>" alt="">
                                <h4 class="post-index-user-name"><?= $p->getUser()->getName(); ?></h4>
                            </div>
                            <div class="post-index-meta">
                                <p class="post-index-date"><?= $p->getCreatedAt(); ?></p>
                                <div class="post-index-comment">
                                    <i class="fa fa-comments-o fa-icon"></i>
                                    <?= $p->getCommentsCount(); ?>
                                </div>
                            </div>
                        </div>
                    </section>
                <?php endforeach; ?>
            </article>
            <aside class="log-in-user-area">
                <div class="log-in-user-group">
                    <div class="log-in-user">
                        <img src="/images/users/20210202150117_user2.png" alt="">
                        <h4 class="log-in-user-name">
                            ログインユーザー
                        </h4>
                    </div>
                    <a href="/posts/new.php" class="btn btn-block btn-new">ブログを書く</a>
                </div>
                <div class="category">
                    <h3 class="category-title">CATEGORIES</h3>
                    <ul class="category-list">
                        <li><a href="/">全て</a></li>
                        <?php foreach ($categories as $c) : ?>
                            <li><a href="/?category_id=<?= $c->getId(); ?>"><?= $c->getName(); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </aside>
        </div>
        <?= createPager($page, $total_count, $category_id); ?>
    </div>

    <?php include __DIR__ . "/common/_footer.php"; ?>
</body>

</html>