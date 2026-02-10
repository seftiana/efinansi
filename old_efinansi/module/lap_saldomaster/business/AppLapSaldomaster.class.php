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
		$result = $this->open($this->mSqlQueries['get_saldo'],array($tglAwal,$tgl));	  	  
		return $result;
	}
	
}
?>