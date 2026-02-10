<?php

class AppPopupSubProgram extends Database {

   protected $mSqlFile= 'module/realisasi_pencairan_2/business/apppopupkegiatan.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
      
   function GetDataSubProgram ($offset, $limit, $program='', $subprogram='', $kode='', $jenis='',$kegiatanunit_id) {
      if($jenis=='all')
	     $jenis='';
      $result = $this->Open($this->mSqlQueries['get_data_subprogram'], array($program,'%'.$subprogram.'%', '%'.$kode.'%', '%'.$jenis.'%',$kegiatanunit_id, $offset, $limit));	  
	  return $result;
   }

   function GetCountDataSubProgram ($program='', $subprogram='', $kode='', $jenis='',$kegiatanunit_id) {
      $result = $this->Open($this->mSqlQueries['get_count_data_subprogram'], array($program, '%'.$subprogram.'%', '%'.$kode.'%', '%'.$jenis.'%',$kegiatanunit_id));      	  
	  //$this->mdebug();
	  
	  if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }
   function GetComboJenis () {
      $result = $this->Open($this->mSqlQueries['get_combo_jenis'], array());
      return $result;
   }
}
?>