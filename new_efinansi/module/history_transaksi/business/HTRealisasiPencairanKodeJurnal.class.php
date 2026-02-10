<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
				'module/history_transaksi/business/HTRealisasiPencairan.class.php';

/**
 * ClassHTRealisasiPencairanKodeJurnal
 * Proses penampilaan data sama dengan class HTRealisasiPencairan
 */
 
class HTRealisasiPencairanKodeJurnal extends HTRealisasiPencairan 
{
	protected $mSqlFile= 'module/history_transaksi/business/ht_realisasi_pencairan_kode_jurnal.sql.php';
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