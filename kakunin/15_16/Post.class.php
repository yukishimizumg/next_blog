<?php
require_once __DIR__ . "/../common/config.php";
require_once __DIR__ . "/../common/functions.php";
require_once "User.class.php";
require_once "Comment.class.php";

class Post
{
    public const PER_PAGE = 8;
    private const IMAGE_ROOT_PATH = '/images/posts/';
    private const NO_IMAGE = 'no_image.png';

    private $id;
    private $category_id;
    private $user_id;
    private $title;
    private $body;
    private $image;
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

    public function getImagePath()
    {
        if (empty($this->image)) {
            // 画像が登録されていない場合はno_image.pngを返す
            return self::IMAGE_ROOT_PATH . self::NO_IMAGE;
        } else {
            return self::IMAGE_ROOT_PATH . $this->image;
        }
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

    public function findCommentsWithUser()
    {
        // ブログに関連するコメントの取得
        $this->findComments();

        // ブログのコメントに関するユーザーの取得
        $this->findCommentUsers();

        return $this->comments;
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

    public static function findWithUser($id)
    {
        // 自クラスのクラスメソッドを呼び出すためselfを付ける
        $post = self::findById($id);

        // ブログを投稿したユーザーの取得
        $post->findUser();

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

    private static function findById($id)
    {
        // 空の配列で初期化
        $instans = [];
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
                $instans = new static($post);
            }
        } catch (PDOException $e) {
            // エラーの場合はログに出力
            error_log($e->getMessage());
        }
        return $instans;
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
}