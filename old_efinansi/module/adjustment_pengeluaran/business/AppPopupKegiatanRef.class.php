<?php

class AppPopupKegiatanRef extends Database {

   protected $mSqlFile= 'module/adjustment_pengeluaran/business/apppopupkegiatanref.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
      
   function GetDataKegiatanRef ($offset, $limit, $subprogram='', $kegiatanref='') {
      $sql = sprintf($this->mSqlQueries['get_data_kegiatanref'], '%s', '%s', '%d','%d');
      $result = $this->Open($sql, array($subprogram,'%'.$kegiatanref.'%', $offset, $limit));
	  //$debug = sprintf($sql, $subprogram,'%'.$kegiatanref.'%', $offset, $limit);
	  //echo $debug;
      return $result;
   }

   function GetCountDataKegiatanRef ($subprogram='', $kegiatanref='') {
      $result = $this->Open($this->mSqlQueries['get_count_data_kegiatanref'], array($subprogram, '%'.$kegiatanref.'%'));      
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }
}
?>
