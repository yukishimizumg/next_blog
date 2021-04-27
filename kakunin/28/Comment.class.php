<?php
require_once __DIR__ . '/../common/config.php';
require_once __DIR__ . '/../common/functions.php';
require_once __DIR__ . '/Post.class.php';

class Comment
{
    private $id;
    private $post_id;
    private $user_id;
    private $comment;
    private $created_at;
    private $updated_at;
    private $user;
    private $errors = [];

    public function __construct($params)
    {
        $this->id = $params['id'];
        $this->post_id = $params['post_id'];
        $this->user_id = $params['user_id'];
        $this->comment = $params['comment'];
        $this->created_at = $params['created_at'];
        $this->updated_at = $params['updated_at'];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPostId()
    {
        return $this->post_id;
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

    public function getPost()
    {
        if (empty($this->post)) {
            $this->post = Post::find($this->post_id);
        }
        return $this->post;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function validate()
    {
        $this->commentValidate();

        return $this->errors ? false : true;
    }

    public function insert()
    {
        try {
            // データベース接続
            $dbh = connectDb();
            $dbh->beginTransaction();

            $this->insertMe($dbh);
            Post::updatePostCommentsCountByIds($dbh, $this->post_id);

            $dbh->commit();
            return true;
        } catch (PDOException $e) {
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

            $dbh->commit();
            return true;
        } catch (PDOException $e) {
            error_log($e->getMessage());
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
            Post::updatePostCommentsCountByIds($dbh, $this->post_id);

            $dbh->commit();
            return true;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $dbh->rollBack();
            return false;
        }
    }

    private function commentValidate()
    {
        if ($this->comment == '') {
            $this->errors['comment'][] = MSG_COMMENT_REQUIRED;
        }

        if (mb_strlen($this->comment) > 255) {
            $this->errors['comment'][] = MSG_COMMENT_MAX;
        }
    }

    private function insertMe($dbh)
    {
        $sql = <<<EOM
        INSERT INTO
            comments (post_id, user_id, comment)
        VALUES
            (:post_id, :user_id, :comment)
        EOM;

        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':post_id', $this->post_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
        $stmt->bindParam(':comment', $this->comment, PDO::PARAM_STR);
        $stmt->execute();

        $this->id = $dbh->lastInsertId();
    }

    private function updateMyProperty($params)
    {
        $this->comment = $params['comment'];
    }

    private function updateMe($dbh)
    {
        $sql = <<<EOM
        UPDATE
            comments
        SET
            comment = :comment
        WHERE
            id = :id
        EOM;

        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':comment', $this->comment, PDO::PARAM_STR);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();
    }

    private function deleteMe($dbh)
    {
        $sql = 'DELETE FROM comments WHERE id = :id';

        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();
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

    public static function setParams($input_params)
    {
        return self::setInputParams($input_params);
    }

    public static function find($id)
    {
        return self::findById($id);
    }

    private static function setInputParams($input_params)
    {
        $params = [];
        $params['post_id'] = $input_params['post_id'];
        $params['user_id'] = $input_params['current_user']['id'];
        $params['comment'] = $input_params['comment'];
        return $params;
    }

    private static function findById($id)
    {
        $instance = [];
        try {
            // データベース接続
            $dbh = connectDb();

            $sql = 'SELECT * FROM comments WHERE id = :id';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $comment = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($comment) {
                $instance = new static($comment);
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }
        return $instance;
    }
}