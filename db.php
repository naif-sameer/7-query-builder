<?php

$dns = "mysql:host=localhost";
$username = 'root';
$password = '';
$database_name = 'e-shop';


/**
 * where
 */
trait where
{
  public function where(string $key, string $value)
  {
    $this->db_query .= " WHERE `$key` = '$value'";

    return $this;;
  }

  public function orWhere(string $key, string $value)
  {
    $this->db_query .= " OR `$key` = '$value'";

    return $this;;
  }
}

/**
 * runQuery functions
 */
trait runQuery
{
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

  public function run()
  {
    return $this->conn->prepare($this->db_query)->execute();
  }
}

/**
 * join
 */
trait join
{

  public function innerJoin(string $table_name, array $table_list)
  {
    if (count($table_list) !== 2) {
      throw new Exception('table list must be two items');
    }

    $select_tables = "'{$table_list[0]}' = '{$table_list[1]}'";

    $this->db_query .= " INNER JOIN `{$table_name}` ON " . $select_tables;
    return $this;
  }

  public function leftJoin(string $table_name, array $table_list)
  {
    if (count($table_list) !== 2) {
      throw new Exception('table list must be two items');
    }

    $select_tables = "'{$table_list[0]}' = '{$table_list[1]}'";

    $this->db_query .= " LEFT JOIN `{$table_name}` ON " . $select_tables;
    return $this;
  }

  public function outerJoin(string $table_name, array $table_list)
  {
    if (count($table_list) !== 2) {
      throw new Exception('table list must be two items');
    }

    $select_tables = "'{$table_list[0]}' = '{$table_list[1]}'";

    $this->db_query .= " FULL OUTER JOIN `{$table_name}` ON " . $select_tables;
    return $this;
  }
}

/**
 * queryBy
 */
trait queryBy
{
  public function orderBy(string $column_name, string $order_type)
  {
    $this->db_query .= " ORDER BY $column_name $order_type ";

    return $this;
  }

  public function groupBy(string $column_name)
  {
    $this->db_query .= " GROUP BY $column_name  ";

    return $this;
  }
}

class Database
{
  use where, runQuery, join, queryBy;

  private $conn;
  private $table_name;
  private $db_query;

  public function __construct(string $table_name, array $table_data)
  {
    global $dns, $username, $password, $database_name;

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
    string $table_name,
    array $table_columns = ["*"]
  ) {
    $columns_name = "";
    foreach ($table_columns as $column) {
      $columns_name .= "'$column' ,";
    }
    // remove last comma
    $columns_name = substr($columns_name, 0, -1);

    $query = "SELECT $columns_name FROM `{$table_name}` ";

    $this->db_query = $query;

    return $this;
  }

  public function insert(string $table_name, array $table_data)
  {
    $columns = '';
    $values = '';

    foreach ($table_data as $key => $value) {
      $columns .= "`$key` ,";
      $values .= "'$value' ,";
    }

    $columns = substr($columns, 0, -1);
    $values = substr($values, 0, -1);

    $query = "INSERT INTO `{$table_name}` ($columns) VALUES ({$values})";

    return $this->conn->prepare($query)->execute();
  }

  public function update(string $table_name, array $table_data)
  {
    $params = [];

    foreach ($table_data as $key => $value) {
      $params[] = "`$key` = '$value'";
    }

    $this->db_query  = "UPDATE `{$table_name}` SET " . implode(',', $params);

    return $this;
  }

  public function delete(string $table_name)
  {
    $this->db_query = "DELETE FROM `{$table_name}` ";

    return $this;
  }

  public function count(string $table_name, string $table_column)
  {
    $this->db_query = " SELECT COUNT('$table_column') FROM `$table_name` ";

    return $this;
  }

  public function limit(int $number)
  {
    $this->db_query .= " LIMIT $number ";

    return $this;
  }
}

$user = new Database('user', ["name" => "int"]);

// print_r(
//   $user
//     ->select('users', ["name" => "naif"])
//     ->where('id', '1')
//     ->orWhere('name', 'ali')
// );

// print_r(
//   $user
//     ->select('users', ['users.id', 'posts.id'])
//     ->innerJoin("customers", ["users.id", "customers.id"])
// );

// print_r(
//   $user
//     ->select('users', ['users.id', 'posts.id'])
//     ->orderBy("customers", "DESC")
// );

// print_r(
//   $user
//     ->count('users', 'id')
//     ->orderBy("id", "DESC")
// );

print_r(
  $user
    ->select('users', ['id'])
    ->limit(1)
);


// you need to use run() if you want to run the query
// use get or getAll if you want to get data;