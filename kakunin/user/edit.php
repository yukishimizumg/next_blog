<!DOCTYPE html>
<html lang="ja">

<?php include __DIR__ . "/../common/_head.php" ?>

<body>
    <?php include __DIR__ . "/../common/_header.php" ?>

    <div class="wrapper user-wrapper">
        <div class="form-main">
            <h2 class="title">アカウント更新</h2>
            <form action="update.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="email">メールアドレス<span class="required">必須</span></label>
                    <input type="email" id="email" name="user[email]" placeholder="メールアドレスを入力してください" required>
                </div>
                <div class="form-group">
                    <label for="user[now_password]">現在のパスワード（パスワード変更時に入力）</label>
                    <input type="password" id="now_password" name="user[now_password]" placeholder="半角英数を組み合わせた8文字以上">
                </div>
                <div class="form-group">
                    <label for="password">新しいパスワード</label>
                    <input type="password" id="password" name="user[password]" placeholder="半角英数を組み合わせた8文字以上">
                </div>
                <div class="form-group">
                    <label for="confirm_password">確認用パスワード</label>
                    <input type="password" id="confirm_password" name="user[confirm_password]" placeholder="半角英数を組み合わせた8文字以上">
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
                    <input type="submit" class="btn" value="更新">
                </div>
            </form>
            <form action="delete.php" method="post">
                <input type="submit" value="削除" class="btn btn-delete" onClick="return confirm('アカウントを削除しますか？')">
            </form>
        </div>
    </div>

    <?php include __DIR__ . "/../common/_footer.php" ?>
</body>

</html>