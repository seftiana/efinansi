<?php

class AppDetilTransaksi extends Database {

	protected $mSqlFile= 'module/transaksi_realisasi_penerimaan/business/appdetiltransaksi.sql.php';
	
	function __construct($connectionNumber=0) {
		parent::__construct($connectionNumber);
		//$this->setdebugOn();
	}
		
	function GetData($offset, $limit, $awal, $akhir) {
		$result = $this->Open($this->mSqlQueries['get_data'], array($awal, $akhir, $offset, $limit));      
      //echo sprintf($this->Open($this->mSqlQueries['get_data'], array($awal, $akhir, $offset, $limit)));
		//echo $this->GetLastError();
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
