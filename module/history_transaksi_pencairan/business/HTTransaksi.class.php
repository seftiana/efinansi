<?php

class HTTransaksi extends Database
{

	protected $mSqlFile= 'module/history_transaksi_pencairan/business/ht_transaksi.sql.php';

	public function __construct($connectionNumber=0)
	{
		parent::__construct($connectionNumber);
		//$this->SetDebugOn();
	}

	public function GetData($offset, $limit, $awal, $akhir, $nomor='', $posting='',$tipe='')
	{
		if($posting=='all') $posting='';
		if($tipe != ''){
			$tipe_sql = " AND transTtId ='".$tipe."' ";
		} else {
			$tipe_sql ='';
		}
		$query = sprintf($this->mSqlQueries['get_data'],
											$awal,
											$akhir,
											'%'.$nomor.'%',
											"%".$posting."%",
											$tipe_sql,
											$offset,
											$limit);
		$result = $this->Open($query,array());
        //print_r($result);
        //echo sprintf($this->mSqlQueries['get_data'],
	    //$awal, $akhir, '%'.$nomor.'%', '%'.$uraian.'%', "%".$posting."%", $offset, $limit);
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