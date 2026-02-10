<?php

class AppPopupKegiatanDetil extends Database {

   protected $mSqlFile= 'module/transaksi_realisasi_kode_jurnal/business/apppopupkegiatandetil.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
      
   function GetData($offset, $limit, $unitkerja, $kegiatan_detil='') {
      //$sql = sprintf(, '%s', '%d','%d');
      //echo sprintf($this->mSqlQueries['get_data'], $unitkerja, '%'.$kegiatan_detil.'%', $offset, $limit);
      $result = $this->Open($this->mSqlQueries['get_data'], array($unitkerja, '%'.$kegiatan_detil.'%', $offset, $limit));
      return $result;
   }

   function GetCountData() {
      $result = $this->Open($this->mSqlQueries['get_count_data'], array());
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }
}
?>
