<?php

class AppPopupKodeRkakl extends Database {

   protected $mSqlFile= 'module/kode_penerimaan/business/apppopupkoderkakl.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
      
   function GetDataKodeRkakl ($offset, $limit, $nama='', $kode='') {
      $sql = sprintf($this->mSqlQueries['get_data_kode_rkakl'], '%s', '%s', '%s', '%d','%d');
      $result = $this->Open($sql, array('%'.$nama.'%', '%'.$kode.'%', $offset, $limit));
      return $result;
   }

   function GetCountDataKodeRkakl ($nama='', $kode='') {
      $result = $this->Open($this->mSqlQueries['get_count_data_kode_rkakl'], array('%'.$nama.'%', '%'.$kode.'%'));      
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }
}
?>