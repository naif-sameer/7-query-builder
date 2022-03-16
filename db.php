<?php

namespace DB;

use PDO;

$dns = "mysql:host=localhost";
$username = 'root';
$password = '';
$database_name = 'e-shop';


/**
 * where
 */
trait Where
{
  public function where(string $key, string $value)
  {
    $this->db_query .= " WHERE `$key` = '$value'";

    return $this;;
  }
}


class Database
{
  use Where;

  private $conn;
  private $table_name;
  private $db_query;

  public function __construct(string $table_name, array $table_data)
  {
    global $dns, $username, $password, $database_name;
    $this->table_name = $table_name;

    $this->conn = new PDO($dns, $username, $password, [
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // create database
    $this->conn->exec("CREATE DATABASE IF NOT EXISTS `$database_name`; USE `$database_name`");


    // create table
    $table_columns = "";
    foreach ($table_data as $key => $value) {
      $table_columns .= "`$key` $value ,";
    }

    $query = "CREATE TABLE IF NOT EXISTS `$table_name` ( 
          `id` INT NOT NULL AUTO_INCREMENT ,
          $table_columns
          `is_active` INT DEFAULT 1 ,
          PRIMARY KEY (`id`));
    )";

    $this->conn->prepare($query)->execute();
  }

  public function select(
    array $table_columns = ["*"]
  ) {

    $columns_name = implode(',', $table_columns);
    $query = "SELECT $columns_name FROM {$this->table_name} ";

    $this->db_query = $query;

    return $this;
  }



  public function get()
  {
    $statement = $this->conn->prepare($this->db_query);
    $statement->execute();

    return $statement->fetch();
  }

  public function getAll()
  {
    $statement = $this->conn->prepare($this->db_query);
    $statement->execute();

    return $statement->fetchAll();
  }

  public function insert(array $table_data)
  {
    $columns = '';
    $values = '';

    foreach ($table_data as $key => $value) {
      $columns .= "`$key` ,";
      $values .= "'$value' ,";
    }

    $columns = substr($columns, 0, -1);
    $values = substr($values, 0, -1);

    $query = "INSERT INTO `{$this->table_name}` ($columns) VALUES ({$values})";

    return $this->conn->prepare($query)->execute();
  }

  public function update(int $id, array $table_data)
  {
    $params = [];

    foreach ($table_data as $key => $value) {
      $params[] = "`$key` = '$value'";
    }

    $query  = "UPDATE {$this->table_name} SET " . implode(',', $params) .  " WHERE `id` = $id";

    $this->conn->prepare($query)->execute();
  }

  public function delete(int $id)
  {
    $query = "DELETE FROM `{$this->table_name}` WHERE `id` = $id";

    $this->conn->prepare($query)->execute();
  }
}

$user = new Database('user', ["name" => "int"]);
