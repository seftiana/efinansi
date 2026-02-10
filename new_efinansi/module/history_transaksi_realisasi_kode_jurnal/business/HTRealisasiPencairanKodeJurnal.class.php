<?php

/**
 * ClassHTRealisasiPencairanKodeJurnal
 */
 
class HTRealisasiPencairanKodeJurnal extends Database
{
	protected $mSqlFile= 'module/history_transaksi_realisasi_kode_jurnal/business/ht_realisasi_pencairan_kode_jurnal.sql.php';
	
	public function __construct($connectionNumber=0) 
	{
		parent::__construct($connectionNumber);
		//$this->SetDebugOn();
	}

	public function GetCountData()
	{				
		$result = $this->Open($this->mSqlQueries['get_count_data'],array());
		if(!empty($result)){
			return $result[0]['total'];
		} else {
			return 0;
		}	
	}	
			
	public function GetData($offset, $limit, $awal, $akhir,$nomor='',$posting='') 
	{
		if($posting=='all') $posting='';
		$query = sprintf($this->mSqlQueries['get_data'], 
												$awal, 
												$akhir,
												'%'.$nomor.'%',
												'%'.$posting.'%',
												$offset, 
												$limit);
												
		$result = $this->Open($query,array());
      //print_r($result);
      //echo sprintf($this->mSqlQueries['get_data'], $periode, $offset, $limit);
		return $result;
	}
}

?>