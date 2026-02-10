<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
				'module/history_transaksi_pencairan/business/HTSpj.class.php';

/**
 * Class HTSpjPerTransaksi
 * extends class HTSpj
 */

class HTSpjPerTransaksi extends HTSpj
{
	protected $mSqlFile= 'module/history_transaksi_pencairan/business/ht_spj_per_transaksi.sql.php';

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