<h2 class="title">コメントする記事</h2>
<div class="comment-blog">
    <h3 class="comment-post-title"><?= h($post->getTitle()) ?></h3>
    <p><?= mb_substr(nl2br(h($post->getBody())), 0, 200) ?></p>
    <div class="comment-post-show"><a href="/posts/show.php?id=<?= h($post->getId()) ?>">記事を確認する</a></div>
</div>