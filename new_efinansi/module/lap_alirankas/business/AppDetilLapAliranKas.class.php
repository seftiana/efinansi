<?php

class AppKelpLaporan extends Database
{
	
	protected $mSqlFile = 'module/lap_alirankas/business/appDetilLapAliranKas.sql.php';
	function GetDataDetilKlpLaporan($tanggal_awal,$tanggal, $id, $offset, $limit) 
	{
		$result = $this->Open($this->mSqlQueries['get_data_detil_klp_laporan'], array(
			$tanggal_awal,
         $tanggal,
         $tanggal_awal,
			$tanggal,
			$id,
			$offset,
			$limit
		));
		return $result;
	}
	function GetDataDetilKlpLaporanKasSetaraKas($tanggal, $id, $offset, $limit) {
   
      $result = $this->Open($this->mSqlQueries['get_data_detil_klp_laporan_kas_setara_kas'], array($tanggal, $id, $offset, $limit));
	  
      return $result;
   }
}
?>
