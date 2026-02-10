<?php

class AppPopupProgram extends Database {

   protected $mSqlFile= 'module/realisasi_pencairan_2/business/apppopupprogram.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
      
   function GetDataProgram ($offset, $limit, $tahun_anggaran, $program='', $kode='',$unit_id) {      
      $result = $this->Open($this->mSqlQueries['get_data_program'], array($tahun_anggaran, '%'.$program.'%', '%'.$kode.'%', $unit_id,$offset, $limit));	  	  
      return $result;
   }

   function GetCountDataProgram ($tahun_anggaran, $program='', $kode='',$unit_id) {
      $result = $this->Open($this->mSqlQueries['get_count_data_program'], array($tahun_anggaran, '%'.$program.'%', '%'.$kode.'%',$unit_id));      
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }
}
?>