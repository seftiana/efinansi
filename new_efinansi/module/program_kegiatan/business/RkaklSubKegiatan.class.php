<?php

class RkaklSubKegiatan extends Database {

   protected $mSqlFile= 'module/program_kegiatan/business/rkaklsubkegiatan.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
      
   function GetDataRkaklSubKegiatan ($offset, $limit, $kode='', $nama='') {
      $result = $this->Open($this->mSqlQueries['get_data_rkakl_subkegiatan'], array($kode.'%', '%'.$nama.'%', $offset, $limit));
      return $result;
   }

   function GetCountDataRkaklSubKegiatan ($kode='', $nama='') {
      $result = $this->Open($this->mSqlQueries['get_count_data_rkakl_subkegiatan'], array($kode.'%', '%'.$nama.'%'));
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }
   
}
?>
