<?php

class AppLapSaldoMaster extends Database {

	protected $mSqlFile= 'module/lap_saldomaster/business/applapsaldomaster.sql.php';
	
	function __construct($connectionNumber=0) {
		parent::__construct($connectionNumber);
		//$this->mrDbEngine->debug = 1;
	}
	
	function GetMinMaxThnTrans() {
		$ret = $this->open($this->mSqlQueries['get_minmax_tahun_transaksi'],array($start , $count));	  	  
	 	if($ret) 
	   	 return $ret[0];
	 	else {
	   	$now_thn = date('Y');
	   	$thn['minTahun'] = $now_thn - 5 ;
	   	$thn['maxTahun'] = $now_thn + 5 ;
	   	return $thn;
     	}    
	}
	
	function GetSaldo($tglAwal,$tgl) {
     	$this->SetDebugOn();
		$result = $this->open($this->mSqlQueries['get_saldo'],array($tglAwal,$tgl,$tglAwal,$tgl));	  	  
		return $result;
	}

	function GetSaldoBerjalan($tgl) {
		$tglFilter = date('Y-m-d', strtotime($tgl));
		$tglAkhir = date('Y', strtotime($tgl)).'-12-31'; 
		if($tglAkhir === $tglFilter) {
			$result = $this->open($this->mSqlQueries['get_saldo_tahun_berjalan'],array($tgl,$tgl));
			return $result[0]['saldo_akhir'];
		} else {
			return 0;
		}
		
	}
	
}
?>