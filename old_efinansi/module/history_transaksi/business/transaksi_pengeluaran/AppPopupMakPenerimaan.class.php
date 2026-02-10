<?php

class AppPopupMakPenerimaan extends Database {

   protected $mSqlFile= 'module/transaksi_pengeluaran/business/apppopupmakpenerimaan.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }

	function GetData($offset, $limit, $nama='', $unit_kerja) {
		$ret = $this->Open($this->mSqlQueries['get_data'], array("%".$nama."%", "%".$nama."%", $unit_kerja, $offset, $limit));
      return $ret;
	}

	function GetCountData($nama='', $unit_kerja) {
		$result = $this->Open($this->mSqlQueries['get_count'], array("%".$nama."%", "%".$nama."%", $unit_kerja));
		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}
}
?>
