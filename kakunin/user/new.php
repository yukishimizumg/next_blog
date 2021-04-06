<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300&family=Sawarabi+Mincho&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
    <title>ブログアプリ</title>
</head>

<body>
    <?php include __DIR__ . "/../common/_header.php" ?>

    <div class="wrapper user-wrapper">
        <div class="form-main">
            <h2 class="title">アカウント登録</h2>
            <form action="create.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="email">メールアドレス<span class="required">必須</span></label>
                    <input type="email" id="email" name="user[email]" placeholder="メールアドレスを入力してください" required>
                </div>
                <div class="form-group">
                    <label for="password">パスワード<span class="required">必須</span></label>
                    <input type="password" id="password" name="user[password]" placeholder="半角英数を組み合わせた8文字以上" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">確認用パスワード<span class="required">必須</span></label>
                    <input type="password" id="confirm_password" name="user[confirm_password]" placeholder="半角英数を組み合わせた8文字以上" required>
                </div>
                <div class="form-group">
                    <label for="name">ユーザー名<span class="required">必須</span></label>
                    <input type="text" id="name" name="user[name]" placeholder="ユーザー名を入力してください" required>
                </div>
                <div class="form-group">
                    <label for="profile">自己紹介<span class="required">必須</span></label>
                    <textarea id="profile" name="user[profile]" rows="5" placeholder="自己紹介を入力してください" required></textarea>
                </div>
                <div class="form-group">
                    <label for="avatar">プロフィール画像</label>
                    <input type="file" name="avatar" id="avatar">
                </div>
                <div class="form-group">
                    <input type="submit" class="btn" value="登録">
                </div>
            </form>
        </div>
    </div>

    <?php include __DIR__ . "/../common/_footer.php" ?>
</body>

</html>