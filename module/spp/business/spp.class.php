<?php
class Spp extends Database
{
	protected $mSqlFile	= 'module/spp/business/spp.sql.php';
	
	function __construct($connectionNumber = 0){
		parent::__construct($connectionNumber);
	}
	
	//COMBO TA
	function GetTa(){
		$return	= $this->Open($this->mSqlQueries['get_data_ta'], array());
		
		return $return;
	}
	
	function JenisPembayaran(){
		$return	= $this->Open($this->mSqlQueries['jenis_pembayaran']);
		
		return $return;
	}
	
	function GetTaAktif(){
		$return	= $this->Open($this->mSqlQueries['get_ta_active'], array());
		
		return $return[0]['id'];
	}
	
	function ComboJenisPembayaran(){
		$return	= $this->Open($this->mSqlQueries['jenis_pembayaran'], array());
		
		return $return;
	}
	
	function ComboSifatPembayaran(){
		$return = $this->Open($this->mSqlQueries['sifat_pembayaran'], array());
		
		return $return;
	}
	
	function GetData($ta,$unit,$offset,$limit){
		$return		= $this->Open($this->mSqlQueries['get_data'], array($ta,$unit,$offset,$limit));
		
		return $return;
	}
	
	function GetDataById($id,$ta,$unit){
		$return		= $this->Open($this->mSqlQueries['get_data_by_id'], array($id,$ta,$unit));
		
		return $return[0];
	}
	function CountData($ta,$unit){
		$return		= $this->Open($this->mSqlQueries['count_data'], array($ta,$unit));
		
		return $return[0]['total_data'];
	}
	
	// get unit kerja
	function GetUnitKerjaByUser()
	{
		$userId	= trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$return 	= $this->Open($this->mSqlQueries['get_unit_kerja_by_user'], array($userId));
		
		return $return;
	}
	
	// do input data spp
	function InputSpp($nomor,$sifatPembayaran,$jenisPembayaran,$keperluan,$jenisBelanja,$nama,$alamat,$rekening,$nilaiSpk,$total,$user){
		$return		= $this->Execute($this->mSqlQueries['input_spp'], 
					  array(
					  $nomor,
					  $sifatPembayaran,
					  $jenisPembayaran,
					  $keperluan,
					  $jenisBelanja,
					  $nama,
					  $alamat,
					  $rekening,
					  $nilaiSpk,
					  $total,
					  $user
					  ));
		// print_r($return);
		return $return;
	}
	
	function GetLastNumb(){
		$return		= $this->Open($this->mSqlQueries['get_last_number'], array());
		$last		= $return[0]['last_nomor'];
		if($last == 0 OR $last == '') $last = '1';
		$length		= '000';
		
		return substr($length,0,strlen($lenth)-strlen($last)).$last;
	}
	
	function GetLastId(){
		$return		= $this->Open($this->mSqlQueries['get_last_id'], array());
		
		return $return[0]['last_id'];
	}
	function InsertSppDetail($sppId,$rncpengeluaranId,$sppNominal,$userId){
		$return		= $this->Execute($this->mSqlQueries['insert_spp_det'], array($sppId,$rncpengeluaranId,$sppNominal,$userId));
		
		return $return;
	}
	
	function DeleteSpp($id){
		$return	= $this->Execute($this->mSqlQueries['delete_spp'],array($id));
		
		return $return;
	}
}
?>