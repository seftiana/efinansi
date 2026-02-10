<?php

class AppPopupSkenario extends Database {

   protected $mSqlFile= 'module/transaksi_realisasi_kode_jurnal/business/apppopupskenario.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }

	function GetData($offset, $limit, $nama='') {
		$ret = $this->Open($this->mSqlQueries['get_data'], array("%".$nama."%", $offset, $limit));
      return $ret;
	}

	function GetCountData($nama='') {
		$result = $this->Open($this->mSqlQueries['get_count_data'], array("%".$nama."%"));
		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}
}
?>
