private function updateMe($dbh)
{
    $sql = <<<EOM
    UPDATE
        users
    SET
        email = :email,
        name = :name,
        profile = :profile,
        avatar = :avatar
    EOM;

    if ($this->password) {
        $sql .= ',password = :password';
    }
    $sql .= ' WHERE id = :id';

    $stmt = $dbh->prepare($sql);

    $stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
    $stmt->bindParam(':name', $this->name, PDO::PARAM_STR);
    $stmt->bindParam(':profile', $this->profile, PDO::PARAM_STR);
    $stmt->bindParam(':avatar', $this->avatar, PDO::PARAM_STR);
    if ($this->password) {
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        $stmt->bindParam(':password', $this->password, PDO::PARAM_STR);
    }
    $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

    $stmt->execute();
}