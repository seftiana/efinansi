<?php
class Spp extends Database{
	protected $mSqlFile = 'module/spp_cetak/business/spp.sql.php';
	
	function __construct($connectionNumber = 0){
		parent::__construct($connectionNumber);
	}
	function Count($ta,$unit){
		$return	= $this->Open($this->mSqlQueries['get_count'], array($ta,$unit));
		
		return $return[0]['total_data'];
	}
	
	function GetData($ta,$unit,$offset,$limit){
		$return = $this->Open($this->mSqlQueries['get_data'], array($ta,$unit,$offset,$limit));
		
		return $return;
	}
	
	function GetDataById($id_spp,$ta,$unit){
		$return	= $this->Open($this->mSqlQueries['get_data_by_id'], array($id_spp,$ta,$unit));
		
		return $return[0];
	}
	//COMBO TA
	function GetTa(){
		$return	= $this->Open($this->mSqlQueries['get_data_ta'], array());
		
		return $return;
	}
	function GetTaAktif(){
		$return	= $this->Open($this->mSqlQueries['get_ta_active'], array());
		
		return $return[0]['id'];
	}
	// get unit kerja
	function GetUnitKerjaByUser()
	{
		$userId	= trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$return 	= $this->Open($this->mSqlQueries['get_unit_kerja_by_user'], array($userId));
		
		return $return;
	}
}
?>