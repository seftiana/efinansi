<?php

class HTRealisasiPencairan extends Database 
{

	protected $mSqlFile= 'module/history_transaksi/business/ht_realisasi_pencairan.sql.php';
	
	public function __construct($connectionNumber=0) 
	{
		parent::__construct($connectionNumber);
		//$this->SetDebugOn();
	}
		
	public function GetData($offset, $limit, $awal, $akhir,$nomor='',$posting='',$mak_nama='') 
	{
		if($posting=='all') $posting='';
		if($mak_nama != ''){
			$mak_sql=" AND mk.nama LIKE '%".$mak_nama."%' ";
		} else {
			$mak_sql ='';
		}
		$query = sprintf($this->mSqlQueries['get_data'], 
												$awal, 
												$akhir,
												'%'.$nomor.'%',
												'%'.$posting.'%',
												$mak_sql,
												$offset, 
												$limit);
												
		$result = $this->Open($query,array());
      //print_r($result);
      //echo sprintf($this->mSqlQueries['get_data'], $periode, $offset, $limit);
		return $result;
	}
}