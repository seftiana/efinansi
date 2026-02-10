<?php

class AppLapAliranKas extends Database
{
	
	protected $mSqlFile = 'module/lap_alirankas/business/lapalirankas.sql.php';
	function __construct($connectionNumber = 0) 
	{
		parent::__construct($connectionNumber);
		
	}
	function GetMinMaxThnTrans() 
	{
		$ret = $this->open($this->mSqlQueries['get_minmax_tahun_transaksi'], array(
			$start,
			$count
		));
		
		if ($ret) 
		return $ret[0];
		else 
		{
			$now_thn = date('Y');
			$thn['minTahun'] = $now_thn - 5;
			$thn['maxTahun'] = $now_thn + 5;
			
			return $thn;
		}
	}
	function GetLaporanAll($tglAwal, $tgl) 
	{
		
		if (GTFWConfiguration::GetValue('application', 'hide_zero_value')) $addSql = " WHERE nilai != 0 ";
		else $addSql = "";
		$newSql = sprintf($this->mSqlQueries['get_laporan_all'], '%s', '%s', '%s', '%s',$addSql);
		
		$result = $this->open($newSql, array(
			$tglAwal, 
         $tgl,
         $tglAwal, 
			$tgl
		));
		//echo $this->GetLastError();
		return $result;
	}
	function GetLaporanKasSetaraKas($tgl){
      if(GTFWConfiguration::GetValue( 'application', 'hide_zero_value'))
         $addSql = " WHERE nilai != 0 ";
      else
         $addSql = "";
         
      $newSql = sprintf($this->mSqlQueries['get_laporan_kas_setara_kas'],'%s',$addSql);
      
      
      return $this->open($newSql,array($tgl));
   }
	function GetSaldoCoaAliranKas() 
	{
		$result = $this->open($this->mSqlQueries['get_saldo_coa_aliran_kas'], array());
		
		return $result[0]['saldo_akhir'];
	}
}
?>
