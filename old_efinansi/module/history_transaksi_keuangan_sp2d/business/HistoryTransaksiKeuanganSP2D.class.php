<?php

/**
 * 
 * @class HistoryTransaksiKeuanganSP2D
 * @package history_transaksi_keuangan_sp2e
 * @description untuk menjalankan query daftar transaksi keuagan spj
 * @analyst dyah fajar <dyah@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @since Januari 2014 
 * @copyright 2014 Gamatechno Indonedia
 * 
 */
 
class HistoryTransaksiKeuanganSP2D extends Database 
{

	protected $mSqlFile= 'module/history_transaksi_keuangan_sp2d/business/history_transaksi_keuangan_sp2d.sql.php';
	
	public function __construct($connectionNumber=0) 
	{
		parent::__construct($connectionNumber);
	}
		
	public function GetData($offset, $limit, $awal, $akhir, $nomor='', $posting='') 
	{
		if($posting=='all') $posting='';

		$query = sprintf($this->mSqlQueries['get_data'], 
											$awal, 
											$akhir, 
											'%'.$nomor.'%', 
											"%".$posting."%",										
											$offset, 
											$limit);
		$result = $this->Open($query,array());
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


?>