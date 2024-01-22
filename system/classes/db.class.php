<?php

class my_db extends PDO {
   var $debug = true;
	  
   public function __construct($dns, $user, $pass, $options) {
      parent::__construct($dns, $user, $pass, $options);
   }

   /**
      *  Произвольный запрос
      * @param $sql string - текст запроса
      * @return bool|mysqli_result
   */
   public function query($sql) {
      return parent::query($sql)or die ($this->errLog($sql));
   }
 
   /**
      *  Получает данные ввиде массива ROW
      * @param $sql string - текст запроса
      * @return mixed
   */
   public function row($sql) {
      $in = parent::query($sql);
      return $in->fetchAll()or die ($this->errLog($sql));
   }

   /**
      *  Получает данные ввиде ассоциативного массива
      * @param $sql string - текст запроса
      * @return array
   */
   public function assoc($sql, $args = false) {
      if ($args) {
         $in = parent::prepare($sql);
         $in->execute($args)or die ($this->errLog($sql));
      } else {
         $in = parent::query($sql)or die ($this->errLog($sql));
      }

      $fet = $in->fetch();
      return $fet;
   }

   public function select($table, $query = "*", $where="", $order_by = "DESC") {
      $where_var = is_array($where) ? implode(" = ?,", array_keys($where))." = ?" : $where;
      $args = is_array($where) ? array_values($where) : [];

      $sql = "SELECT $query FROM $table".($where_var ? " WHERE $where_var" : "")." ORDER BY id ".$order_by;

      $out = array();

      // exit($sql);

      $q = parent::prepare($sql);
      $q->execute($args)or die ($this->errLog($sql));

      while ($assoc = $q->fetch()) {
         $out[] = $assoc;
      }
      return $out;
   }

   /**
      *  Получает данные ввиде многомерного массива
      * @param $sql string - текст запроса
      * @return array
   */
   public function in_array($sql, $args = false) {
      $out = array();

      if ($args) {
         $q = parent::prepare($sql);
         $q->execute($args)or die ($this->errLog($sql));
      } else {
         $q = parent::query($sql)or die ($this->errLog($sql));
      }
      while ($assoc = $q->fetch()) {
         $out[] = $assoc;
      }
      return $out;
   }

   /**
      *  Производит подсчет кол-ва записей
      * @param $sql string - текст запроса
      * @return int
   */
   public function result($sql) {
      $in = parent::query($sql)or die ($this->errLog($sql));
      $ass = $in->fetchAll();
      return is_array($ass) ? count($ass) : 0;
   }

   /**
   *  Добавляет запись в БД
   * @param $table string - название таблицы
   * @param $query string|array - данные для добавления
   * @return int
   */
   public function insert($table, $query) {
      if (is_array($query)) {
         $t = parent::prepare("INSERT INTO $table SET ".implode(" = ?,", array_keys($query))." = ? ");
         $t->execute(array_values($query))or die ($this->errLog('Insert-'.$table));
         return parent::lastInsertId();
      }
   }

   /**
      * Обновить запись в БД. Как ассоциативный массив
      * @param $table string - имя таблицы
      * @param $query string|array - данные для обновления
      * @param $where string - условие
      * @return bool|mysqli_result - результат запроса
   */
   public function update($table, $query, $where = "", $no_logging = false) {
      global $user_id;

      if (is_array($query) || is_array($where)) {
         $sql = "UPDATE $table SET ".(is_array($query) ? implode(" = ?, ", array_keys($query)) : $query)." = ? ".(is_array($where) ? "WHERE ".implode(" = ? AND ", array_keys($where))." = ?" : ($where ? "WHERE $where" : ""));

         if ($no_logging == false) {
            $old_data = $this->in_array("SELECT * FROM $table " . (is_array($where) ? "WHERE ".implode(" = ? AND ", array_keys($where))." = ?" : ($where ? "WHERE $where" : "")), is_array($where) ? array_values($where) : []);
   
            $this->insert("db_updated", [
               "creator_user_id" => $user_id,
               "table_name" => $table,
               "query_data" => (is_array($query) ? json_encode($query, JSON_UNESCAPED_UNICODE) : $query),
               "where_data" => (is_array($where) ? json_encode($where, JSON_UNESCAPED_UNICODE) : $where),
               "old_data" => (is_array($old_data) ? json_encode($old_data, JSON_UNESCAPED_UNICODE) : $old_data)
            ]);
         }

         $t = parent::prepare($sql);
         $t->execute(
            array_merge(
               is_array($query) ? array_values($query) : [],
               is_array($where) ? array_values($where) : []
            )
         )or die ($this->errLog('Insert-'.$table));
      } else {
         $t = parent::query("UPDATE $table SET $query ".($where ? "WHERE $where" : ""));
      }
   }

   /**
      * Удаляет запись в БД
      * @param string $table - имя таблицы
      * @param int $id - id удаляемой записи
      * @param string - поле id
   */
   public function delete($table, $id, $field = 'id') {
      global $user_id;

      $table_data = $this->in_array("SELECT * FROM $table WHERE $field = ?", [ $id ]);
      
      $this->insert("db_deleted", [
         "user_id" => $user_id,
         "table_name" => $table,
         "table_id" => $id,
         "table_field" => $field,
         "table_data" => json_encode($table_data, JSON_UNESCAPED_UNICODE)
      ]);
      
      return parent::prepare("DELETE FROM $table WHERE $field = ?")->execute([$id])or die ($this->errLog('Delete-'.$table));
   }

   /**
      * Очищает таблицу
      * @param string $table - имя таблицы для очистки
   */
   public function clear($table) {
      return parent::query("TRUNCATE TABLE `$table`")or die ($this->errLog('Clear-'.$table));
   }

   /**
      *  Производит чтение ячейки БД
      * @param $sql string - текст запроса
      * @return string
   */
   public function read($sql) {
      $lst = parent::query($sql)->fetchColumn()or die ($this->errLog($sql));
      return $lst;
   }

   /**
      *  Лог ошибок
      * @param $mess string - текст ошибки
      * @return int - номер ошибки mysqli
   */
   public function errLog($mess) {
      $mess = (is_array(parent::errorInfo()) ? implode(",", parent::errorInfo()) : parent::errorInfo()).' '. $mess;
      if (!$this->debug) {
         echo $mess;
      } else {
         $log_dir = "files/logs/";
         if (!file_exists($log_dir)) {
            mkdir($log_dir, 0777, true);
         }

         $f = @fopen($log_dir.'mysql_'.date ("d.m.Y").'.log','a');
         if ($f) {
            fwrite ($f, "[".date("Y-m-d H:i:s")."] ".$_SERVER['REQUEST_URI']." - ".$mess."\r\n");
            fclose ($f);
         }
      }
   }
}


if ($_SERVER['HTTP_HOST'] == "crm.yangiasr.uz" || $_SERVER['HTTP_HOST'] == "www.crm.yangiasr.uz"){
   $db2_host = 'localhost';
   $db2_user = 'crm_yangiasr';
   $db2_base = 'crm_yangiasr';
   $db2_pass = 'fRaZCRzKrPgeeMt5';
} else if ($_SERVER['HTTP_HOST'] == "old.yangiasr.uz" || $_SERVER['HTTP_HOST'] == "www.old.yangiasr.uz"){
   $db2_host = 'localhost';
   $db2_user = 'crm_yangiasr';
   $db2_base = 'crm_yangiasr_old';
   $db2_pass = 'fRaZCRzKrPgeeMt5';
} else {
   $db2_host = 'localhost';
   $db2_user = 'root';
   $db2_base = 'crm_yangi_asr';
   $db2_pass = '';
}

$db = new my_db("mysql:host=$db2_host;dbname=$db2_base",
   $db2_user,
   $db2_pass,
[
   PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
   PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
   PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'",
   PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,
]);

// yangi asr.uz ga ulanish
$db5_host = 'localhost';
$db5_user = 'yangiasr_uz';
$db5_base = 'yangiasr_uz';
$db5_pass = 'LQuW0BdCOo9YQrPI';

$db5 = new my_db("mysql:host=$db5_host;dbname=$db5_base",
   $db5_user,
   $db5_pass,
[
   PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
   PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
   PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'",
   PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,
]);
?>