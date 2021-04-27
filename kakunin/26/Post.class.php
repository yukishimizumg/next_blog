<?php
require_once __DIR__ . '/../common/config.php';
require_once __DIR__ . '/../common/functions.php';
require_once __DIR__ . '/User.class.php';
require_once __DIR__ . '/Comment.class.php';

class Post
{
    public const PER_PAGE = 8;
    private const IMAGE_DIR_PATH = '/var/www/public/images/posts/';
    private const IMAGE_ROOT_PATH = '/images/posts/';
    private const NO_IMAGE = 'no_image.png';
    private const EXTENTION = ['jpg', 'jpeg', 'png', 'gif'];

    private $id;
    private $category_id;
    private $user_id;
    private $title;
    private $body;
    private $image;
    private $image_tmp;
    private $image_old;
    private $comments_count;
    private $created_at;
    private $updated_at;
    private $user;
    private $comments;

    public function __construct($params)
    {
        $this->id = $params['id'];
        $this->category_id = $params['category_id'];
        $this->user_id = $params['user_id'];
        $this->title = $params['title'];
        $this->body = $params['body'];
        $this->image = $params['image'];
        $this->image_tmp = $params['image_tmp'];
        $this->image_old = $params['image_old'];
        $this->comments_count = $params['comments_count'];
        $this->created_at = $params['created_at'];
        $this->updated_at = $params['updated_at'];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function getImagePath()
    {
        if (empty($this->image)) {
            // 画像が登録されていない場合はno_image.pngを返す
            return self::IMAGE_ROOT_PATH . self::NO_IMAGE;
        } else {
            return self::IMAGE_ROOT_PATH . $this->image;
        }
    }

    public function getCategoryId()
    {
        return $this->category_id;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function getCommentsCount()
    {
        return $this->comments_count;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function findCommentsWithUser()
    {
        // ブログに関連するコメントの取得
        $this->findComments();

        // ブログのコメントに関するユーザーの取得
        $this->findCommentUsers();

        return $this->comments;
    }

    public function validate()
    {
        $this->categoryValidate();
        $this->titleValidate();
        $this->bodyValidate();
        $this->imageValidate();

        return $this->errors ? false : true;
    }

    public function insert()
    {
        try {
            // データベース接続
            $dbh = connectDb();
            $dbh->beginTransaction();

            $this->insertMe($dbh);

            if (!$this->fileUpload()) {
                throw new Exception(MSG_UPLOAD_FAILED);
            }

            $dbh->commit();
            return true;
        } catch (Exception $e) {
            error_log($e->getMessage());
            $dbh->rollBack();
            return false;
        }
    }

    public function updateProperty($params)
    {
        $this->updateMyProperty($params);
    }

    public function update()
    {
        try {
            // データベース接続
            $dbh = connectDb();
            $dbh->beginTransaction();

            $this->updateMe($dbh);

            if (!$this->fileUpload()) {
                throw new Exception(MSG_UPLOAD_FAILED);
            }

            if ($this->image_old) {
                if (!$this->fileDelete($this->image_old)) {
                    throw new Exception(MSG_FILE_DELETE_FAILED);
                }
            }

            $dbh->commit();
            return true;
        } catch (Exception $e) {
            error_log($e->getMessage());
            if ($this->image_old) {
                $this->fileDelete($this->image);
                $this->image = $this->image_old;
            }

            $dbh->rollBack();
            return false;
        }
    }

    public function delete()
    {
        try {
            // データベース接続
            $dbh = connectDb();
            $dbh->beginTransaction();

            $this->deleteMe($dbh);

            $this->fileDelete($this->image);
            $dbh->commit();

            return true;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $dbh->rollBack();
            return false;
        }
    }

    private function findUser()
    {
        $this->user = User::find($this->user_id);
    }

    private function findComments()
    {
        // ブログのidでコメントを取得
        $this->comments = Comment::findByPostId($this->id);
    }

    private function findCommentUsers()
    {
        // コメントからuser_idだけを抽出
        $ids = array_map(
            function ($comment) {
                return $comment->getUserId();
            },
            $this->comments
        );

        // user_idの重複を削除
        $find_user_ids = array_values(array_unique($ids));

        // user_idの配列を使用してユーザー情報を連想配列として取得
        $users = User::findByIdsAsArray($find_user_ids);

        //idだけの配列を作成
        $user_ids = array_column($users, 'id');

        // Commentクラスのインスタンスのuserプロパティに、Userクラスのインスタンスをセット
        foreach ($this->comments as $c) {
            $comment_user = $users[array_search(
                $c->getUserId(),
                $user_ids
            )];
            $c->setUser(new User($comment_user));
        }
    }

    private function categoryValidate()
    {
        if ($this->category_id == '') {
            $this->errors['category_id'][] = MSG_CATEGORY_REQUIRED;
        }
    }

    private function titleValidate()
    {
        if ($this->title == '') {
            $this->errors['title'][] = MSG_TITLE_REQUIRED;
        }

        if (mb_strlen($this->title) > 255) {
            $this->errors['title'][] = MSG_TITLE_MAX;
        }
    }

    private function bodyValidate()
    {
        if ($this->body == '') {
            $this->errors['body'][] = MSG_BODY_REQUIRED;
        }
    }

    private function imageValidate()
    {
        if ($this->image_tmp["name"]) {
            $ext = mb_strtolower(pathinfo($this->image_tmp["name"], PATHINFO_EXTENSION));
            if (!in_array($ext, self::EXTENTION)) {
                $this->errors['image'][] = MSG_IMAGE_FORMAT;
            }
        }
    }

    private function insertMe($dbh)
    {
        $sql = <<<EOM
        INSERT INTO
            posts (category_id, user_id, title, body, image)
        VALUES
            (:category_id, :user_id, :title, :body, :image)
        EOM;

        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':category_id', $this->category_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
        $stmt->bindParam(':title', $this->title, PDO::PARAM_STR);
        $stmt->bindParam(':body', $this->body, PDO::PARAM_STR);
        $stmt->bindParam(':image', $this->image, PDO::PARAM_STR);
        $stmt->execute();

        $this->id = $dbh->lastInsertId();
    }

    private function updateMe($dbh)
    {
        $sql = <<<EOM
        UPDATE
            posts
        SET
            category_id = :category_id,
            title = :title,
            body = :body,
            image = :image
        WHERE
            id = :id
        EOM;

        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':category_id', $this->category_id, PDO::PARAM_INT);
        $stmt->bindParam(':title', $this->title, PDO::PARAM_STR);
        $stmt->bindParam(':body', $this->body, PDO::PARAM_STR);
        $stmt->bindParam(':image', $this->image, PDO::PARAM_STR);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();
    }

    private function deleteMe($dbh)
    {
        $sql = 'DELETE FROM posts WHERE id = :id';

        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();
    }

    private function fileUpload()
    {
        try {
            if ($this->image_tmp['name']) {
                move_uploaded_file(
                    $this->image_tmp['tmp_name'],
                    self::IMAGE_DIR_PATH . $this->image
                );
            }
            return true;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    private function updateMyProperty($params)
    {
        $this->category_id = $params['category_id'];
        $this->title = $params['title'];
        $this->body = $params['body'];

        if ($params['image_tmp']['name']) {
            $this->image_tmp = $params['image_tmp'];
            $this->image_old = $this->image;
            $this->image = date('YmdHis') . '_' . $params['image_tmp']['name'];
        }
    }

    private function fileDelete($file)
    {
        try {
            $file_path = self::IMAGE_DIR_PATH . $file;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            return true;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public static function findWithUser($id)
    {
        // 自クラスのクラスメソッドを呼び出すためselfを付ける
        $post = self::findById($id);

        // ブログを投稿したユーザーの取得
        if ($post) {
            $post->findUser();
        }

        return $post;
    }

    public static function findIndexView($category_id, $page)
    {
        // 一覧画面に表示するブログ情報取得
        $posts = self::findPostIndexView($category_id, $page);

        // ブログ情報からuser_idを配列で取得
        $find_user_ids = self::getPostUserIds($posts);

        // user_idの配列を使用してユーザー情報を連想配列として取得
        $users = User::findByIdsAsArray($find_user_ids);

        // インスタンスのuserプロパティに、Userクラスのインスタンスをセット
        self::setPostUsers($posts, $users);

        return $posts;
    }

    public static function findIndexViewCount($category_id)
    {
        return self::findPostIndexViewCount($category_id);
    }

    public static function setParams($input_params)
    {
        return self::setInputParams($input_params);
    }

    public static function find($id)
    {
        return self::findById($id);
    }

    public static function updatePostCommentsCountByIds($dbh, $ids)
    {
        return self::updateCommentCountByIds($dbh, $ids);
    }

    private static function findById($id)
    {
        // 空の配列で初期化
        $instance = [];
        try {
            // データベース接続
            $dbh = connectDb();

            // $idを使用してデータを抽出
            $sql = 'SELECT * FROM posts WHERE id = :id';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $post = $stmt->fetch(PDO::FETCH_ASSOC);

            // データを取得できた場合
            if ($post) {
                // new Post($post)と同じ意味のコードを
                // 自クラスで実行する場合、以下の構文となる
                $instance = new static($post);
            }
        } catch (PDOException $e) {
            // エラーの場合はログに出力
            error_log($e->getMessage());
        }
        return $instance;
    }

    private static function findPostIndexView($category_id, $page)
    {
        $instances = [];
        try {
            // データベース接続
            $dbh = connectDb();

            // $sql = 'SELECT * FROM posts ORDER BY created_at desc';
            $sql = <<<EOM
            SELECT
                p.*
            FROM
                posts p
            INNER JOIN
                categories c
            ON
                p.category_id = c.id
            EOM;

            // カテゴリー検索用
            if ($category_id) {
                $sql .= ' WHERE p.category_id = :category_id';
            }

            $sql .= ' ORDER BY p.created_at desc';
            $sql .= ' LIMIT :par_page OFFSET :offset_count';
            $stmt = $dbh->prepare($sql);

            // カテゴリー検索用
            if ($category_id) {
                $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
            }

            // ページネーション用
            $par_page = self::PER_PAGE;
            $stmt->bindParam(':par_page', $par_page, PDO::PARAM_INT);
            $offset = ($page - 1) * self::PER_PAGE;
            $stmt->bindParam(':offset_count', $offset, PDO::PARAM_INT);

            $stmt->execute();
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($posts as $p) {
                $instances[] = new static($p);
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }
        return $instances;
    }

    private static function getPostUserIds($posts)
    {
        $user_ids = array_map(
            function ($post) {
                return $post->user_id;
            },
            $posts
        );

        return array_values(array_unique($user_ids));
    }

    private static function setPostUsers($posts, $users)
    {
        $user_ids = array_column($users, 'id');
        foreach ($posts as $p) {
            $post_user = $users[array_search(
                $p->user_id,
                $user_ids
            )];
            $p->user = new User($post_user);
        }
    }

    private static function findPostIndexViewCount($category_id)
    {
        $count = 0;
        try {
            // データベース接続
            $dbh = connectDb();

            $sql = <<<EOM
            SELECT
                COUNT(*) AS count
            FROM
                posts p
            INNER JOIN
                categories c
            ON
                p.category_id = c.id
            EOM;
            if ($category_id) {
                $sql .= ' WHERE p.category_id = :category_id';
            }

            $stmt = $dbh->prepare($sql);
            if ($category_id) {
                $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
            }
            $stmt->execute();
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = $post['count'];
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }
        return $count;
    }

    private static function setInputParams($input_params)
    {
        $params = [];
        $params['category_id'] = $input_params['category_id'];
        $params['user_id'] = $input_params['current_user']['id'];
        $params['title'] = $input_params['title'];
        $params['body'] = $input_params['body'];

        if ($input_params['image_tmp']['name']) {
            $params['image_tmp'] = $input_params['image_tmp'];
            $params['image'] = date('YmdHis') . '_' . $input_params['image_tmp']['name'];
        }
        return $params;
    }

    private static function updateCommentCountByIds($dbh, $ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $sql = '';
        $sql .= 'UPDATE ';
        $sql .= '    posts AS p ';
        $sql .= 'INNER JOIN ';
        $sql .= '   ( ';
        $sql .= '    SELECT ';
        $sql .= '        COUNT(c.id) AS cnt, ';
        $sql .= '        c.post_id ';
        $sql .= '    FROM ';
        $sql .= '        comments c ';
        $sql .= '    WHERE ';
        $sql .= '        c.post_id IN (' . substr(str_repeat(',?', count($ids)), 1) . ') ';
        $sql .= '    GROUP BY c.post_id ';
        $sql .= '   ) cm ';
        $sql .= 'ON ';
        $sql .= '    p.id = cm.post_id ';
        $sql .= 'SET ';
        $sql .= '    p.comments_count = cm.cnt';

        $stmt = $dbh->prepare($sql);
        $stmt->execute($ids);
    }
}