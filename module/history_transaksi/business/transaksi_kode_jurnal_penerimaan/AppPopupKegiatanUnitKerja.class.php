<?php

class AppPopupKegiatanUnitKerja extends Database {

   protected $mSqlFile= 'module/transaksi_kode_jurnal_penerimaan/business/apppopupkegiatanunitkerja.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
      
   function GetData($offset, $limit, $kegiatan_detil='') {
      $result = $this->Open($this->mSqlQueries['get_kegiatan_unit_kerja'], array('%'.$kegiatan_detil.'%', $offset, $limit));
      return $result;
   }

   function GetCountData() {
      $result = $this->Open($this->mSqlQueries['get_count_kegiatan_unit_kerja'], array());
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }
}
?>
