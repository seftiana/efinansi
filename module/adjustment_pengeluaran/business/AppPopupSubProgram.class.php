<?php

class AppPopupSubProgram extends Database {

   protected $mSqlFile= 'module/adjustment_pengeluaran/business/apppopupsubprogram.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
      
   function GetDataSubProgram ($offset, $limit, $program='', $subprogram='', $kode='', $jenis='') {
      if($jenis == "all") $jenis = "";
      $result = $this->Open($this->mSqlQueries['get_data_subprogram'], array($program,'%'.$subprogram.'%', '%'.$kode.'%', '%'.$jenis.'%', $offset, $limit));
      return $result;
   }

   function GetCountDataSubProgram ($program='', $subprogram='', $kode='', $jenis='') {
      if($jenis == "all") $jenis = "";
      $result = $this->Open($this->mSqlQueries['get_count_data_subprogram'], array($program, '%'.$subprogram.'%', '%'.$kode.'%', '%'.$jenis.'%'));
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
