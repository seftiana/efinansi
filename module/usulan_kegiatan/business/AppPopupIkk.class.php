<?php

class AppPopupIkk extends Database {

   protected $mSqlFile= 'module/usulan_kegiatan/business/apppopupikk.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
      
   function GetDataIkk ($offset, $limit, $nama='', $kode='') {
      $sql = sprintf($this->mSqlQueries['get_data_ikk'], '%s', '%s', '%s', '%d','%d');
      $result = $this->Open($sql, array('%'.$nama.'%', '%'.$kode.'%', $offset, $limit));
      return $result;
   }

   function GetCountDataIkk ($nama='', $kode='') {
      $result = $this->Open($this->mSqlQueries['get_count_data_ikk'], array('%'.$nama.'%', '%'.$kode.'%'));      
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }
}
?>
