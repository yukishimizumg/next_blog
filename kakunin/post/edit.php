<!DOCTYPE html>
<html lang="ja">

<?php include __DIR__ . "/../common/_head.php"; ?>

<body>
    <?php include __DIR__ . "/../common/_header.php"; ?>

    <div class="wrapper">
        <div class="form-main">
            <h2 class="title">ブログ更新</h2>
            <form action="update.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">タイトル<span class="required">必須</span></label>
                    <input type="text" id="title" name="post[title]" placeholder="タイトルを入力してください" required>
                </div>
                <div class="form-group">
                    <label for="catogory">カテゴリー<span class="required">必須</span></label>
                    <select name="category_id" id="category" required>
                        <option disabled selected value="">選択してください</option>
                        <option value="1">インターネット・コンピュータ</option>
                        <option value="2">エンターテインメント</option>
                        <option value="3">生活・文化</option>
                        <option value="4">社会・経済</option>
                        <option value="5">健康と医療</option>
                        <option value="6">ペットグルメ</option>
                        <option value="7">住まい</option>
                        <option value="8">花・ガーデニング</option>
                        <option value="9">育児</option>
                        <option value="10">旅行・観光</option>
                        <option value="11">写真</option>
                        <option value="12">手芸・ハンドクラフト</option>
                        <option value="13">スポーツ</option>
                        <option value="14">アウトドア</option>
                        <option value="15">美容・ビューティー</option>
                        <option value="16">ファッション</option>
                        <option value="17">恋愛・結婚</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="body">本文<span class="required">必須</span></label>
                    <textarea name="post[body]" id="body" rows="10" placeholder="本文を入力してください" required></textarea>
                </div>
                <div class="form-group">
                    <label for="image">イメージ画像</label>
                    <img src="/images/posts/202102021501171_post4.jpg" alt="" class="image-preview">
                    <input type="file" name="image" id="image">
                </div>
                <div class="form-group">
                    <input type="submit" class="btn" value="更新">
                </div>
            </form>
        </div>
    </div>

    <?php include __DIR__ . "/../common/_footer.php"; ?>
</body>

</html>
