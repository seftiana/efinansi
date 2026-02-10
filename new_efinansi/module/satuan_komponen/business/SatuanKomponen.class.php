<?php

class SatuanKomponen extends Database {

   protected $mSqlFile= 'module/satuan_komponen/business/satuankomponen.sql.php';
   
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
      
   function GetLimitSatuanKomponen($params) { 
     $query = $this->GetQueryKeren($this->mSqlQueries['get_limit_satuan_komponen'], $params);
	  return $this->Open($query,array());    
   }

   function JumlahListSatuanKomponen($params) {
     $query = $this->GetQueryKeren($this->mSqlQueries['jumlah_list_satuan_komponen'], $params);
	  $rs = $this->Open($query, array());
     return $rs[0]['jumlah'];
   }

   function GetSatuanKomponenFromId($params) {     	  
	  return $this->Open($this->mSqlQueries['get_satuan_komponen_from_id'], array($params));    
   }

   function InsertSatuanKomponen($params) {     	  
	  return $this->Execute($this->mSqlQueries['insert_satuan_komponen'], $params);    
   }

   function UpdateSatuanKomponen($params) {     	  
	  return $this->Execute($this->mSqlQueries['update_satuan_komponen'], $params);    
   }

   function DeleteSatuanKomponen($params) { 
     $query = $this->GetQueryKeren($this->mSqlQueries['delete_satuan_komponen'], array($params));
	  return $this->Execute($query, array());    
   }
   
   function CekSatuanKomponen($params) { 
     $query = $this->GetQueryKeren($this->mSqlQueries['cek_satuan_komponen'], array($params));
	 $result = $this->Open($query,array());
	 return $result[0];
   }
}
?>
