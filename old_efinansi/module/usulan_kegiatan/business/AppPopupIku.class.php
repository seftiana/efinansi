<?php

class AppPopupIku extends Database {

   protected $mSqlFile= 'module/usulan_kegiatan/business/apppopupiku.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
      
   function GetDataIku ($offset, $limit, $nama='', $kode='') {
      $sql = sprintf($this->mSqlQueries['get_data_iku'], '%s', '%s', '%s', '%d','%d');
      $result = $this->Open($sql, array('%'.$nama.'%', '%'.$kode.'%', $offset, $limit));
      return $result;
   }

   function GetCountDataIku ($nama='', $kode='') {
      $result = $this->Open($this->mSqlQueries['get_count_data_iku'], array('%'.$nama.'%', '%'.$kode.'%'));      
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }
}
?>
