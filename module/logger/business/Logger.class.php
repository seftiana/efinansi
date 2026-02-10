<?php

class Logger extends Database {

   protected $mSqlFile= 'module/logger/business/logger.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }

   function GetQueryKeren($sql,$params) {
      foreach ($params as $k => $v) {
         if (is_array($v)) {
            $params[$k] = '~~' . join("~~,~~", $v) . '~~';
            $params[$k] = str_replace('~~', '\'', addslashes($params[$k]));
         } else {
            $params[$k] = addslashes($params[$k]);
         }
      }
      $param_serialized = '~~' . join("~~,~~", $params) . '~~';
      $param_serialized = str_replace('~~', '\'', addslashes($param_serialized));
      eval('$sql_parsed = sprintf("' . $sql . '", ' . $param_serialized . ');');

		return $sql_parsed;
   }

   function InsertLogger($params){
      return $this->Execute($this->mSqlQueries['insert_logger'], $params);
   }

   function GetMaxId(){
      $rs = $this->Open($this->mSqlQueries['get_max_id'], array());
      return $rs[0]['maxid'];
   }
 }
?>