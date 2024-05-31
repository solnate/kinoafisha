<?php
namespace Lib;
use Exception;
use mysqli;
define('USER_TABLE', 'users');
define('USER_NAME', 'name');
define('ROLE_TABLE', 'roles');
define('ROLE_NAME', 'role');
define('CROSS_TABLE', 'users_roles');
define('CROSS_TABLE_USER_NAME', 'id_users');
define('CROSS_TABLE_ROLE_NAME', 'id_roles');
class Database
{
    private string $host, $user, $password, $database;
    private $connection;
    /**
     * @throws Exception
     */
    public function __construct(string $host, string $user, string $password, string $database)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        try{
            $this->connect();
        } catch (Exception $e) {
            return throw new Exception("Connection Error");
        }
    }

    /**
     * Подключение к базе
     * @return void
     * @throws Exception
     */
    protected function connect(): void
    {
        $this->connection = new mysqli($this->host, $this->user, $this->password, $this->database);
        if ($this->connection->connect_errno) {
            throw new Exception($this->connection->connect_error);
        }
    }
    public function disconnect(): void
    {
        $this->connection->close();
    }

    /**
     * Выполняет запрос к базе
     * @param $query
     * @param $table
     * @return array
     */
    protected function execute($query, $table): array
    {
        //Проверка подключения
        if (!$this->connection) {
            return array('status' => false, 'error' => 'connection aborted');
        }
        //Существует ли таблица
        $tablesInDb = $this->connection->query('SHOW TABLES FROM ' . $this->database . ' LIKE "' . $table . '"');
        if (!$tablesInDb || mysqli_num_rows($tablesInDb) != 1) {
            return array('status' => false, 'error' => $table . ' not found');
        }
        //Выполняет запрос
        $execute = $this->connection->query($query);
        if (!is_bool($execute)) {
            //Сбор ответа в массив
            $result = array();
            while ($row = $execute->fetch_array()) {
                $result['data'][$row[0]] = $row;
            }
            $execute->close();
            $result['status'] = true;
            return $result;
        }
        else if($execute) {
            return array('status' => true, 'id' => $this->connection->insert_id);
        }
        return array('status' => false, 'error' => 'empty execute');
    }

    /**
     * Экранирует данные в массиве
     * @param $array
     * @return array
     */
    protected function sqlWithArray($array): array
    {
        $return = array();
        foreach($array as $field=>$val){
            $return[$field] = "'".mysqli_real_escape_string($this->connection,htmlspecialchars(trim($val)))."'";
        }
        return $return;
    }

    /**
     * Экранирует данные
     * @param $connection
     * @param $val
     * @return float|int|string
     */
    public function sql($connection, $val): float|int|string
    {
        if(is_numeric($val)){
            return $val;
        }
        return "'".mysqli_real_escape_string($connection,htmlspecialchars(trim($val)))."'";
    }

    /**
     * Создает запись
     * @param string $table
     * @param array $rows
     * @return false|array
     */
    public function create(string $table, array $rows): false|array
    {
        $rows       = $this->sqlWithArray($rows);
        $keys       = "(".implode(" ,", array_keys($rows)).")";
        $values     = " VALUES (".implode(", ", array_values($rows)).")";
        $query      = "INSERT INTO ".$table.$keys.$values;
        return $this->execute($query, $table);
    }

    /**
     * Читает таблицу
     * @param string $table
     * @param string $rows
     * @param $where_tag
     * @param $where
     * @param $order
     * @return array
     */
    public function read(string $table, string $rows = '*', $where_tag = null, $where = null, $order = null): array
    {
        $query = "SELECT $rows FROM $table";
        if($where != null) {
            $query .= " WHERE $where_tag=$where";
        }
        if($order != null) {
            $query .= " ORDER BY $order";
        }
        return $this->execute($query, $table);
    }

    /**
     * Обновляет таблицу
     * @param string $table
     * @param array $rows
     * @param $where_rows
     * @return array
     */
    public function update(string $table, array $rows, $where_rows){
        //Собираем данные для обновления из массива
        $set = [];
        foreach($rows as $field => $val){
            $set[] = "$field = " . $this->sql($this->connection, $val);
        }
        $query = "UPDATE $table SET ";
        $query .= implode(", ", $set);
        $query .= " WHERE ";
        //Собираем условие
        $where = [];
        foreach($where_rows as $field => $val){
            $where[] = "$field = " . $this->sql($this->connection, $val);
        }
        $query .= implode(" AND ", $where);
        return $this->execute($query, $table);
    }

    /**
     * Обновляет таблицу взаимосвязей
     * @param string $table
     * @param array $data
     * @param $where
     * @return mixed
     */
    public function update_cross(string $table, array $data, $where){
        $result = $this->read(CROSS_TABLE, "*", CROSS_TABLE_USER_NAME, $where)['data'];
        //Собираем в массив текущие роли пользователя в массив
        $current_roles = [];
        foreach ($result as $role){
            $current_roles[] = $role[CROSS_TABLE_ROLE_NAME];
        }
        //Находим элементы для удаления и добавления в таблицу взаимосвязей
        $insert_list  = array_diff($data[ROLE_NAME], $current_roles);
        $delete_list = array_diff($current_roles, $data[ROLE_NAME]);
        //Удаляем устаревшие
        foreach($result as $role) {
            if(in_array($role[CROSS_TABLE_ROLE_NAME], $delete_list))
                $this->delete($table, 'id', $role['id']);
        }
        //Добавляем новые
        foreach($insert_list as $role) {
            $this->create(
                $table,
                [
                    CROSS_TABLE_USER_NAME => $data['id'],
                    CROSS_TABLE_ROLE_NAME => $role
                ]);
        }
    }

    /**
     * Удаляет запись
     * @param string $table
     * @param null $where_tag
     * @param null $where
     * @return array
     */
    public function delete(string $table, $where_tag = null, $where = null): array
    {
        if ($where == null || $where_tag == null) {
            $query = "DELETE  $table";
        } else {
            $query = "DELETE FROM $table WHERE $where_tag=$where";
        }
        return $this->execute($query, $table);
    }
}