<?php

class RkaklKegiatan extends Database {

   protected $mSqlFile= 'module/program_kegiatan/business/rkaklkegiatan.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
      
   function GetDataRkaklKegiatan ($offset, $limit, $kode='', $nama='') {
      $result = $this->Open($this->mSqlQueries['get_data_rkakl_kegiatan'], array($kode.'%', '%'.$nama.'%', $offset, $limit));
      return $result;
   }

   function GetCountDataRkaklKegiatan ($kode='', $nama='') {
      $result = $this->Open($this->mSqlQueries['get_count_data_rkakl_kegiatan'], array($kode.'%', '%'.$nama.'%'));
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }
   
}
?>
