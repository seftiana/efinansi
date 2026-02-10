<?php

class AppPopupTupoksi extends Database {

   protected $mSqlFile= 'module/usulan_kegiatan/business/apppopuptupoksi.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
      
   function GetDataTupoksi ($offset, $limit, $nama='', $kode='') {
      $sql = sprintf($this->mSqlQueries['get_data_tupoksi'], '%s', '%s', '%s', '%d','%d');
      $result = $this->Open($sql, array('%'.$nama.'%', '%'.$kode.'%', $offset, $limit));
      return $result;
   }

   function GetCountDataTupoksi ($nama='', $kode='') {
      $result = $this->Open($this->mSqlQueries['get_count_data_tupoksi'], array('%'.$nama.'%', '%'.$kode.'%')); 
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }
}
?>
