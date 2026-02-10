<?php

class AppPopupOutput extends Database {

   protected $mSqlFile= 'module/usulan_kegiatan/business/apppopupoutput.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
      
   function GetDataOutput ($offset, $limit, $nama='', $kode='') {
      $sql = sprintf($this->mSqlQueries['get_data_output'], '%s', '%s', '%s', '%d','%d');
      $result = $this->Open($sql, array('%'.$nama.'%', '%'.$kode.'%', $offset, $limit));
      return $result;
   }

   function GetCountDataOutput ($nama='', $kode='') {
      $result = $this->Open($this->mSqlQueries['get_count_data_output'], array('%'.$nama.'%', '%'.$kode.'%'));      
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }
}
?>
