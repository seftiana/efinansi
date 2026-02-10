<?php

class AppPopupUnitKerja extends Database {

   protected $mSqlFile= 'module/spp_cetak/business/app_popup_unit_kerja.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
      
   function GetDataSatker ($offset, $limit, $satker='', $pimpinan='') {
      $sql = sprintf($this->mSqlQueries['get_data_satker'], '%s', '%s', '%d','%d');
      $result = $this->Open($sql, array('%'.$satker.'%', '%'.$pimpinan.'%', $offset, $limit));
	  //$debug = sprintf($sql, '%'.$satker.'%', '%'.$pimpinan.'%', $offset, $limit);
	  //echo $debug;
      return $result;
   }

   function GetCountDataSatker ($satker='', $pimpinan='') {
      $result = $this->Open($this->mSqlQueries['get_count_data_satker'], array('%'.$satker.'%', '%'.$pimpinan.'%'));      
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }
}
?>
