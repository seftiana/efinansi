<?php

class AppDetilTransaksi extends Database {

	protected $mSqlFile= 'module/history_transaksi_keuangan/business/appdetiltransaksi.sql.php';
	
	function __construct($connectionNumber=0) {
		parent::__construct($connectionNumber);
		//$this->mrDbEngine->debug = 1;
	}
		
	function GetData($offset, $limit, $awal, $akhir, $nomor='', $uraian='', $posting) {
		if($posting=='all') $posting='';
		$result = $this->Open($this->mSqlQueries['get_data'], array($awal, $akhir, '%'.$nomor.'%', '%'.$uraian.'%', "%".$posting."%", $offset, $limit));
      //print_r($result);
      //echo sprintf($this->mSqlQueries['get_data'], $awal, $akhir, '%'.$nomor.'%', '%'.$uraian.'%', "%".$posting."%", $offset, $limit);
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