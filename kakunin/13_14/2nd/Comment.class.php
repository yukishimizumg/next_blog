<?php
require_once __DIR__ . "/../common/config.php";
require_once __DIR__ . "/../common/functions.php";

class Comment
{
    private $id;
    private $post_id;
    private $user_id;
    private $comment;
    private $created_at;
    private $updated_at;
    private $user;

    public function __construct($params)
    {
        $this->id = $params['id'];
        $this->post_id = $params['post_id'];
        $this->user_id = $params['user_id'];
        $this->comment = $params['comment'];
        $this->created_at = $params['created_at'];
        $this->updated_at = $params['updated_at'];
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public static function findByPostId($post_id)
    {
        return self::findCommentsByPostId($post_id);
    }

    private static function findCommentsByPostId($post_id)
    {
        $instances = [];
        try {
            // データベース接続
            $dbh = connectDb();

            $sql = 'SELECT * FROM comments WHERE post_id = :post_id';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
            $stmt->execute();
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 配列の中に各データが連想配列で設定されている
            foreach ($comments as $c) {
                // 1件ずつCommentクラスのインスタンスとして生成
                $instances[] = new static($c);
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }
        return $instances;
    }
}