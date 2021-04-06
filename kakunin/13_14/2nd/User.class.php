<?php
require_once __DIR__ . "/../common/config.php";
require_once __DIR__ . "/../common/functions.php";

class User
{
    private const IMAGE_ROOT_PATH = '/images/users/';
    private const NO_IMAGE = 'no_image.png';

    private $id;
    private $email;
    private $password;
    private $confirm_password;
    private $name;
    private $profile;
    private $avatar;
    private $created_at;
    private $updated_at;

    public function __construct($params)
    {
        $this->id = $params['id'];
        $this->email = $params['email'];
        $this->password = $params['password'];
        $this->confirm_password = $params['confirm_password'];
        $this->name = $params['name'];
        $this->profile = $params['profile'];
        $this->avatar = $params['avatar'];
        $this->created_at = $params['created_at'];
        $this->updated_at = $params['updated_at'];
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAvatarPath()
    {
        if (empty($this->avatar)) {
            // 画像が登録されていない場合はno_image.pngを返す
            return self::IMAGE_ROOT_PATH . self::NO_IMAGE;
        } else {
            return self::IMAGE_ROOT_PATH . $this->avatar;
        }
    }

    public static function find($id)
    {
        return self::findById($id);
    }

    public static function findByIdsAsArray($ids)
    {
        return self::findUsersByIdsAsArray($ids);
    }

    private static function findById($id)
    {
        $instance = [];
        try {
            // データベース接続
            $dbh = connectDb();

            $sql = 'SELECT * FROM users WHERE id = :id';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $instance = new static($user);
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
        return $instance;
    }

    private static function findUsersByIdsAsArray($ids)
    {
        $instances = [];
        try {
            if (is_array($ids)) {
                // データベース接続
                $dbh = connectDb();

                $sql = 'SELECT * FROM users ';
                $sql .= 'WHERE id IN (' . substr(str_repeat(',?', count($ids)), 1) . ')';
                $stmt = $dbh->prepare($sql);
                $stmt->execute($ids);
                $instances = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
        return $instances;
    }
}