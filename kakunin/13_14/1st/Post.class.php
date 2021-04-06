<?php
require_once __DIR__ . "/../common/config.php";
require_once __DIR__ . "/../common/functions.php";
require_once "User.class.php";
require_once "Comment.class.php";

class Post
{
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
        // ユーザー情報を保持していない時は取得する
        if (empty($this->user)) {
            $this->user = User::find($this->user_id);
        }
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
        // user_idの重複を削除する
        $user_ids = array_unique($ids);

        // user_idの配列でユーザーを取得
        $users = User::findByIds($user_ids);

        // Commentクラスのインスタンスのuserプロパティに、Userクラスのインスタンスをセット
        foreach ($this->comments as $c) {
            $c->setUser($users[array_search($c->getUserId(), $user_ids)]);
        }
    }

    public static function find($id)
    {
        // 自クラスのクラスメソッドを呼び出すためselfを付ける
        return self::findById($id);
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
}