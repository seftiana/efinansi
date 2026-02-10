<?php

class HTRealisasiPenerimaan extends Database
{

	protected $mSqlFile= 'module/history_transaksi_pencairan/business/ht_realisasi_penerimaan.sql.php';

	public function __construct($connectionNumber=0)
	{
		parent::__construct($connectionNumber);
		//$this->setdebugOn();
	}

	public function GetData($offset, $limit, $awal, $akhir,$nomor='',$map_nama='',$posting='')
	{
		if($posting=='all') $posting='';
		if($map_nama != ''){
			$map_sql=" AND mp.map_nama LIKE '%".$map_nama."%' ";
		} else {
			$map_sql ='';
		}
		$query = sprintf($this->mSqlQueries['get_data'],
														$awal,
														$akhir,
														'%'.$nomor.'%',
														'%'.$posting.'%',
														$map_sql,
														$offset,
														$limit);
		$result = $this->Open($query,array());
		return $result;
	}

	public function GetCountData() {
		$result = $this->Open($this->mSqlQueries['get_count_data'], array());
		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}

	public function GetDaftarMap($offset, $limit, $nama='')
	{
		$result = $this->Open($this->mSqlQueries['get_daftar_map'],
													array(
															'%'.$nama.'%',
															$offset,
															$limit));
		return $result;
	}
}
