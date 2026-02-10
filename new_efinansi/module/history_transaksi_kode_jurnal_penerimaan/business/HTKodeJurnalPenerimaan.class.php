<?php

/**
 * class HTKodeJurnalPenerimaan
 * proses sama dengan class HTkodeJurnal
 */
 
class HTKodeJurnalPenerimaan extends Database 
{
	protected $mSqlFile= 'module/history_transaksi_kode_jurnal_penerimaan/business/ht_kode_jurnal_penerimaan.sql.php';
	
	public function __construct($connectionNumber=0) 
	{
		parent::__construct($connectionNumber);
		//$this->SetDebugOn();
	}
		
	public function GetData($offset, $limit, $awal, $akhir, $nomor='',$tipe='') 
	{
		if($tipe != ''){
			$tipe_sql = " AND transTtId ='".$tipe."' ";
		} else {
			$tipe_sql ='';
		}
		$query = sprintf($this->mSqlQueries['get_data'], 
													$awal, 
													$akhir,
													'%'.$nomor.'%', 
													$tipe_sql,  
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
	
	public function GetComboTipeTransaksi() 
	{
		$userName = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserName());
		$result = $this->Open($this->mSqlQueries['get_combo_tipe_transaksi'], array($userName));
		return $result;
   	}
}

?>