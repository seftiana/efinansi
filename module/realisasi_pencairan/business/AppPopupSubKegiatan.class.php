<?php

class AppPopupKegiatanRef extends Database {

   protected $mSqlFile= 'module/realisasi_pencairan/business/apppopupsubkegiatan.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
      
   function GetDataKegiatanRef ($offset, $limit, $kegiatan_id='',$kegiatanunit_id='', $kegiatanref='') {
      //$sql = sprintf($this->mSqlQueries['get_data_kegiatanref'], '%s', '%s', '%d','%d');
      $result = $this->Open($this->mSqlQueries['get_data_kegiatanref'], array($kegiatanunit_id,$kegiatan_id,'%'.$kegiatanref.'%', $offset, $limit));	  	  
	  //$this->mdebug();
      return $result;
   }

   function GetCountDataKegiatanRef ($kegiatan_id='',$kegiatanunit_id='', $kegiatanref='') {
      $result = $this->Open($this->mSqlQueries['get_count_data_kegiatanref'], array($kegiatanunit_id,$kegiatan_id, '%'.$kegiatanref.'%'));      	  
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }
}
?>
