<?php
// データベース接続用関数を定義してるファイルを読み込む
require_once __DIR__ . "/../common/functions.php";

define('RAND_VALUE', '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');

echo "データベースをSET UPしますか？ [yes] or [no]" . PHP_EOL;

// 上記で入力された内容を取得
$answer = trim(fgets(STDIN));

// yes以外の時は処理終了
if ($answer !== 'yes') exit;

try {
    // データベースに接続
    $dbh = connectDb();

    // 外部キー制約を無効化
    $dbh->query('SET foreign_key_checks = 0');

    // テーブルの削除 & categoriesテーブルのSET UP
    $sql_dir = __DIR__ . '/sql/';
    foreach (glob($sql_dir . "*.sql") as $file) {
        $sql = file_get_contents($file);
        $dbh->exec($sql);
    }
    echo '■■■■ テーブル削除完了 ■■■■' . PHP_EOL;
    echo '■■■■ categoriesテーブル SET UP完了 ■■■■' . PHP_EOL;

    // public/images/usersとpublic/images/posts内の画像ファイルを削除
    $images_dir = __DIR__ . '/../public/images/';
    foreach (glob($images_dir . "*") as $dir) {
        if (is_dir($dir)) {
            foreach (glob($dir . "/*") as $file) {
                if (basename($file) !== 'no_image.png') {
                    unlink($file);
                }
            }
        }
    }
    echo '■■■■ 画像ファイル削除完了 ■■■■' . PHP_EOL;

    // usersテーブルの登録
    $sql = <<<EOM
    INSERT INTO
        users (email, password, name, profile, avatar)
    VALUES
        (:email, :password, :name, :profile, :avatar)
    EOM;
    $stmt = $dbh->prepare($sql);

    // テストデータのアバターで使用する画像のパス
    $images_dir = __DIR__ . '/images/users/';
    // 登録時に画像をコピーするパス
    $copy_dir = __DIR__ . '/../public/images/users/';
    foreach (glob($images_dir . "*") as $i => $file) {
        // フルパスからフィアル名のみを取得
        $file_name = basename($file);
        // ファイル名の先頭に年月日時分秒を付加
        $image = date('YmdHis') . '_' . $file_name;
        // ファイルのコピー
        copy($file, $copy_dir . $image);

        // bindParamに指定する値は、変数にセットしないといけないのでここでセット
        $id = ++$i;
        $email = "test_" . (string) $id . "@example.com";
        $name = substr(str_shuffle(RAND_VALUE), 0, 10);
        $profile = substr(str_shuffle(RAND_VALUE), 0, 50);
        // パスワードはテーブルのデータを見ても分からないようにハッシュ化する
        $password = password_hash("password". (string) $id, PASSWORD_DEFAULT);

        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':profile', $profile, PDO::PARAM_STR);
        $stmt->bindParam(':avatar', $image, PDO::PARAM_STR);
        $stmt->execute();
    }
    echo '■■■■ usersテーブル SET UP完了 ■■■■' . PHP_EOL;

    // postsテーブルの登録
    // 登録時にcategory_idが必要なので、categoriesデータを取得
    $sql = 'SELECT id FROM categories';
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    // PDO::FETCH_COLUMNを使用することで、連想配列ではなく、配列でidを取得する
    $category_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 登録時にuser_idが必要なので、usersデータを取得
    $sql = 'SELECT id FROM users';
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    // PDO::FETCH_COLUMNを使用することで、連想配列ではなく、配列でidを取得する
    $user_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $sql = <<<EOM
    INSERT INTO
        posts (category_id, user_id, title, body, image)
    VALUES
        (:category_id, :user_id, :title, :body, :image)
    EOM;
    $stmt = $dbh->prepare($sql);

    // テストデータの投稿画像で使用する画像のパス
    $images_dir = __DIR__ . '/images/posts/';
    $files = [];
    // ファイル名の一覧を取得
    foreach (glob($images_dir . "*") as $file) {
        $files[] = $file;
    }

    // 登録時に画像をコピーするパス
    $copy_dir = __DIR__ . '/../public/images/posts/';
    for ($i = 1; $i <= 40; $i++) {
        // ランダムに画像ファイルを選出
        $file = $files[array_rand($files)];
        $file_name = basename($file);
        // 登録する画像ファイル名の先頭に年月日時分秒を付加
        // 秒だけだとファイル名が被る可能性があるので、$iも付加
        $image = date('YmdHis') . $i . '_' . $file_name;
        // 画像ファイルをコピー
        copy($file, $copy_dir . $image);

        $category_id = $category_ids[array_rand($category_ids)];
        $user_id = $user_ids[array_rand($user_ids)];
        $title = substr(str_shuffle(RAND_VALUE), 0, 10);
        $body = substr(str_shuffle(RAND_VALUE), 0, 50);

        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':body', $body, PDO::PARAM_STR);
        $stmt->bindParam(':image', $image, PDO::PARAM_STR);
        $stmt->execute();
    }
    echo '■■■■ postsテーブル SET UP完了 ■■■■' . PHP_EOL;

    // commentsテーブルの登録
    $sql = 'SELECT id FROM posts';
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $post_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $sql = <<<EOM
    INSERT INTO
        comments (post_id, user_id, comment)
    VALUES
        (:post_id, :user_id, :comment)
    EOM;

    $stmt = $dbh->prepare($sql);

    for ($i = 1; $i <= 100; $i++) {
        $post_id = $post_ids[array_rand($post_ids)];
        $user_id = $user_ids[array_rand($user_ids)];
        $comment = substr(str_shuffle(RAND_VALUE), 0, 100);

        $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->execute();
    }

    echo '■■■■ commentsテーブル SET UP完了 ■■■■' . PHP_EOL;

    // postsテーブルcomments_countの更新
    $sql = <<<EOM
    UPDATE
        posts AS p
        INNER JOIN
        (
            SELECT
                COUNT(c.id) AS cnt,
                c.post_id
            FROM
                comments c
            GROUP BY c.post_id
        ) cm
        ON
        p.id = cm.post_id
    SET
        p.comments_count = cm.cnt
    EOM;
    $stmt = $dbh->prepare($sql);
    $stmt->execute();

    echo '■■■■ comments_count SET UP完了 ■■■■' . PHP_EOL;

    // 外部キー制約を有効化
    $dbh->query('SET foreign_key_checks = 1');
    
    echo '■■■■ データベースSET UP完了 ■■■■' . PHP_EOL;
} catch (PDOException $e) {
    echo $e->getMessage();
    exit;
}