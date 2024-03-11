<?php

class Db
{
    private PDO $pdo;
    private string $error;
    private $stmt;

    public function __construct()
    {
        try {
            $this->pdo = new \PDO(
                'mysql:host=' . DB_HOST . ';
            dbname=' . DB_NAME,
                DB_USER,
                DB_PASS,
                DB_OPTIONS
            );
            $this->pdo->exec('SET NAMES UTF8');
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            echo $this->error;
        }
    }

    public function query($sql)
    {
        $this->stmt = $this->pdo->prepare($sql);
    }

    public function bind($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute()
    {
        return $this->stmt->execute();
    }

    public function resultSet()
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function single()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getById($id, $tableName)
    {
        $this->query("SELECT * FROM `{$tableName}` WHERE id = :id");
        $this->bind(':id', $id);
        $item = $this->single();
        if ($item) {
            return $item;
        } else {
            return false;
        }
    }
}
