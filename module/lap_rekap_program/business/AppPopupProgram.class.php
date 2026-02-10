<?php

class AppPopupProgram extends Database {

   protected $mSqlFile= 'module/lap_rekap_program/business/apppopupprogram.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
      
   function GetDataProgram ($offset, $limit, $tahun_anggaran, $program='', $kode='') {
      $sql = sprintf($this->mSqlQueries['get_data_program'], '%s', '%s', '%s', '%d','%d');
      $result = $this->Open($sql, array($tahun_anggaran, '%'.$program.'%', '%'.$kode.'%', $offset, $limit));
	  //$debug = sprintf($sql, $tahun_anggaran, '%'.$program.'%', '%'.$pimpinan.'%', $offset, $limit);
	  //echo $debug;
      return $result;
   }

   function GetCountDataProgram ($tahun_anggaran, $program='', $kode='') {
      $result = $this->Open($this->mSqlQueries['get_count_data_program'], array($tahun_anggaran, '%'.$program.'%', '%'.$kode.'%'));      
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }
}
?>
