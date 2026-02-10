<?php


class HTSpjPerTransaksi extends Database
{
	protected $mSqlFile= 'module/history_transaksi_spj_pertransaksi/business/ht_spj_per_transaksi.sql.php';

	public function __construct($connectionNumber=0) 
	{
		parent::__construct($connectionNumber);
		//$this->mrDbEngine->debug = 1;
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
		
	public function GetData($offset, $limit, $awal, $akhir,$nomor='',$ref_transaksi='') 
	{
		$query = sprintf($this->mSqlQueries['get_data'], 
											$awal, 
											$akhir,
											'%'.$nomor.'%',
											'%'.$ref_transaksi.'%', 
											$offset, 
											$limit);
											
		$result = $this->Open($query,array());
    	return $result;
	}
	
	public function GetDaftarRefTransaksi($offset,$limit,$ref_transaksi='')
	{
 		$result=$this->open($this->mSqlQueries['get_daftar_ref_transaksi'],
		 										array(
												 		'%'.$ref_transaksi.'%',
														 $offset,
														 $limit));
		return $result; 
	}
}


?>