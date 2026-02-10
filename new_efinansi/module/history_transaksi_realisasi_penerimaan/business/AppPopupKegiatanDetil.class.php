<?php

class AppPopupKegiatanDetil extends Database {

   protected $mSqlFile= 'module/history_transaksi_realisasi_penerimaan/business/apppopupkegiatandetil.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
		//
   }
      
   function GetData($offset, $limit, $unitkerja, $nama='') {
      //print_r($limit);
		//$sql = sprintf(, '%s', '%d','%d');
      //echo sprintf($this->mSqlQueries['get_data'], $unitkerja, '%'.$nama.'%', $offset, $limit);
     # 
      $result = $this->Open($this->mSqlQueries['get_data'], array($unitkerja, '%'.$nama.'%', $offset, $limit));
      //echo $this->GetLastError();
		//print_r($result);
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