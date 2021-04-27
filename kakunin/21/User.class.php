<?php
require_once __DIR__ . "/../common/config.php";
require_once __DIR__ . "/../common/functions.php";

class User
{
    private const IMAGE_DIR_PATH = '/var/www/public/images/users/';
    private const IMAGE_ROOT_PATH = '/images/users/';
    private const NO_IMAGE = 'no_image.png';
    private const EXTENTION = ['jpg', 'jpeg', 'png', 'gif'];

    private $id;
    private $email;
    private $password;
    private $confirm_password;
    private $name;
    private $profile;
    private $avatar;
    private $avatar_tmp;
    private $created_at;
    private $updated_at;
    private $errors = [];

    public function __construct($params)
    {
        $this->id = $params['id'];
        $this->email = $params['email'];
        $this->password = $params['password'];
        $this->confirm_password = $params['confirm_password'];
        $this->name = $params['name'];
        $this->profile = $params['profile'];
        $this->avatar = $params['avatar'];
        $this->avatar_tmp = $params['avatar_tmp'];
        $this->created_at = $params['created_at'];
        $this->updated_at = $params['updated_at'];
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getProfile()
    {
        return $this->profile;
    }

    public function getAvatarPath()
    {
        if (empty($this->avatar)) {
            return self::IMAGE_ROOT_PATH . self::NO_IMAGE;
        } else {
            return self::IMAGE_ROOT_PATH . $this->avatar;
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function validate()
    {
        $this->emailValidate();
        $this->passwordValidate();
        $this->nameValidate();
        $this->profileValidate();
        $this->avatarValidate();

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

            session_regenerate_id(true);
            $this->setCurrentUser();

            return true;
        } catch (Exception $e) {
            error_log($e->getMessage());
            $dbh->rollBack();
            return false;
        }
    }

    public function loginValidate()
    {
        return $this->loginRequiredValidate();
    }

    public function logIn()
    {
        return $this->logInMe();
    }

    private function emailValidate()
    {
        if ($this->email == '') {
            $this->errors['email'][] = MSG_EMAIL_REQUIRED;
        } else {
            if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                $this->errors['email'][] = MSG_EMAIL_FORMAT;
            }

            if (mb_strlen($this->email) > 255) {
                $this->errors['email'][] = MSG_EMAIL_MAX;
            }

            if (self::findByEmail($this->email)) {
                $this->errors['email'][] = MSG_EMAIL_USED;
            }
        }
    }

    private function passwordValidate()
    {
        if ($this->password == '') {
            $this->errors['password'][] = MSG_PASSWORD_REQUIRED;
        } else {
            if (mb_strlen($this->password) > 255) {
                $this->errors['password'][] = MSG_PASSWORD_MAX;
            }
        }

        $reg_str = '/\A(?=.*?[a-z])(?=.*?\d)[a-z\d]{8,}+\z/i';
        if (!preg_match($reg_str, $this->password)) {
            $this->errors['password'][] = MSG_PASSWORD_FORMAT;
        }

        if ($this->confirm_password == '') {
            $this->errors['confirm_password'][] = MSG_CONFIRM_PASSWORD_REQUIRED;
        } else {
            if ($this->password != $this->confirm_password) {
                $this->errors['password'][] = MSG_PASSWORD_NOT_MATCH;
                $this->errors['confirm_password'][] = MSG_PASSWORD_NOT_MATCH;
            }
        }
    }

    private function nameValidate()
    {
        if ($this->name == '') {
            $this->errors['name'] = MSG_USER_NAME_REQUIRED;
        } else {
            if (mb_strlen($this->name) > 50) {
                $this->errors['name'][] = MSG_USER_NAME_MAX;
            }
        }
    }

    private function profileValidate()
    {
        if ($this->profile == '') {
            $this->errors['profile'][] = MSG_PROFILE_REQUIRED;
        }
    }

    private function avatarValidate()
    {
        if ($this->avatar_tmp["name"]) {
            $ext = mb_strtolower(pathinfo($this->avatar_tmp["name"], PATHINFO_EXTENSION));
            if (!in_array($ext, self::EXTENTION)) {
                $this->errors['avatar'][] = MSG_AVATAR_FORMAT;
            }
        }
    }

    private function insertMe($dbh)
    {
        $sql = <<<EOM
        INSERT INTO
            users (email, password, name, profile, avatar)
        VALUES
            (:email, :password, :name, :profile, :avatar)
        EOM;

        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
        // パスワードのハッシュ化
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        $stmt->bindParam(':password', $this->password, PDO::PARAM_STR);
        $stmt->bindParam(':name', $this->name, PDO::PARAM_STR);
        $stmt->bindParam(':profile', $this->profile, PDO::PARAM_STR);
        $stmt->bindParam(':avatar', $this->avatar, PDO::PARAM_STR);
        $stmt->execute();

        $this->id = $dbh->lastInsertId('id');
    }

    private function fileUpload()
    {
        try {
            if ($this->avatar_tmp["name"]) {
                $image = date('YmdHis') . '_' . $this->avatar_tmp["name"];
                move_uploaded_file($this->avatar_tmp['tmp_name'], self::IMAGE_DIR_PATH . $image);
                $this->avatar = $image;
            }
            return true;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    private function setCurrentUser()
    {
        $_SESSION['current_user']['id'] = $this->id;
        $_SESSION['current_user']['name'] = $this->name;
        if (empty($this->avatar)) {
            $_SESSION['current_user']['avatar'] = self::IMAGE_DIR_PATH . self::NO_IMAGE;
        } else {
            $_SESSION['current_user']['avatar'] = self::IMAGE_DIR_PATH . $this->avatar;
        }
    }

    private function loginRequiredValidate()
    {
        if ($this->email == '') {
            $this->errors['email'][] = MSG_EMAIL_REQUIRED;
        }

        if ($this->password == '') {
            $this->errors['password'][] = MSG_PASSWORD_REQUIRED;
        }

        return $this->errors ? false : true;
    }

    private function logInMe()
    {
        $user = self::findByEmail($this->email);
        if ($user && password_verify($this->password, $user->password)) {
            session_regenerate_id(true);
            $user->setCurrentUser();
            return true;
        } else {
            $this->errors['email'][] = MSG_EMAIL_PASSWORD_NOT_MATCH;
            $this->errors['password'][] = MSG_EMAIL_PASSWORD_NOT_MATCH;
            return false;
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

    public static function setParams($input_params)
    {
        return self::setInputParams($input_params);
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

    private static function setInputParams($input_params)
    {
        $params = [];
        $params['email'] = $input_params['email'];
        $params['password'] = $input_params['password'];
        $params['confirm_password'] = $input_params['confirm_password'];
        $params['name'] = $input_params['name'];
        $params['profile'] = $input_params['profile'];
        $params['email'] = $input_params['email'];

        if ($_FILES['avatar']['name']) {
            $params['avatar_tmp'] = $_FILES['avatar'];
            $params['avatar'] = date('YmdHis') . '_' . $params['avatar_tmp']['name'];
        }
        return $params;
    }

    private static function findByEmail($email)
    {
        $instance = [];
        try {
            // データベース接続
            $dbh = connectDb();

            $sql = 'SELECT * FROM users WHERE email = :email';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $instance = new static($user);
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }
        return $instance;
    }
}