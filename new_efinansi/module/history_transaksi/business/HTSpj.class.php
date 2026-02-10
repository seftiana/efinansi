<?php

class HTSpj extends Database 
{

	protected $mSqlFile= 'module/history_transaksi/business/ht_spj.sql.php';
	
	public function __construct($connectionNumber=0) 
	{
		parent::__construct($connectionNumber);
		//$this->mrDbEngine->debug = 1;
	}
		
	public function GetData($offset, $limit, $awal, $akhir,$nomor='',$mak_nama='') 
	{
		$result = $this->Open($this->mSqlQueries['get_data'], 
									array(
											$awal, 
											$akhir,
											'%'.$nomor.'%', 
											'%'.$mak_nama.'%',
											$offset, 
											$limit));
    	return $result;
	}

	public function GetCountData() 
	{
		$result = $this->Open($this->mSqlQueries['get_count_data'], array());
		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}
}