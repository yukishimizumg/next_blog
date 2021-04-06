<?php
require_once __DIR__ . "/../common/config.php";
require_once __DIR__ . "/../common/functions.php";

class Category
{
    private $id;
    private $name;

    public function __construct($params)
    {
        $this->id = $params['id'];
        $this->name = $params['name'];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public static function findAll()
    {
        return self::findCategoryAll();

    }

    private static function findCategoryAll()
    {
        $instances = [];
        try {
            // データベース接続
            $dbh = connectDb();

            $sql = 'SELECT * FROM categories ORDER BY id';
            $stmt = $dbh->prepare($sql);
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($categories as $c) {
                $instances[] = new static($c);
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
        return $instances;
    }
}
