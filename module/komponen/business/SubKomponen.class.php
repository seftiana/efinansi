<?php

class SubKomponen extends Database {

   protected $mSqlFile= 'module/komponen/business/subkomponen.sql.php';
   
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
      
   function GetLimitSubKomponenFromKomponen($params) { 
     $query = $this->GetQueryKeren($this->mSqlQueries['get_limit_sub_komponen_from_komponen'], $params);
	  return $this->Open($query,array());    
   }

   function JumlahListSubKomponenFrom($params) {
     $query = $this->GetQueryKeren($this->mSqlQueries['jumlah_list_sub_komponen_from_komponen'], $params);
	  $rs = $this->Open($query, array());
     return $rs[0]['jumlah'];
   }

   function GetSubKomponenFromId($params) {
	  return $this->Open($this->mSqlQueries['get_sub_komponen_from_id'], array($params));    
   }

   function InsertSubKomponen($params) {     	  
	  return $this->Execute($this->mSqlQueries['insert_sub_komponen'], $params);    
   }

   function UpdateSubKomponen($params) {     	  
	  return $this->Execute($this->mSqlQueries['update_sub_komponen'], $params);    
   }

   function DeleteSubKomponen($params) { 
     $query = $this->GetQueryKeren($this->mSqlQueries['delete_sub_komponen'], array($params));
	  return $this->Execute($query, array());    
   }
}
?>
