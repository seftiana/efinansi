<?php


class HTPopupMak extends Database
{
	protected $mSqlFile= 'module/history_transaksi_pengeluaran/business/ht_popup_mak.sql.php';
	
	public function __construct($connectionNumber=0) 
	{
		parent::__construct($connectionNumber);
		//$this->SetDebugOn();
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
	
	public function GetDaftarMak($offset, $limit, $nama='')
	{
		$result = $this->Open($this->mSqlQueries['get_daftar_mak'],
													array(
															'%'.$nama.'%',
															$offset, 
															$limit));      
		return $result;
	}
}

?>