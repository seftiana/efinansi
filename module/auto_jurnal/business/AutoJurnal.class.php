<?php

/**
 * 
 * @package auto_jurnal
 * @subpackage business
 * @classname AutoJurnal
 * @description untuk menjalankan query query auto jurnal
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright gamatechno indonesia 2014
 * 
 */


require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/generate_number/business/GenerateNumber.class.php';

require_once GTFWConfiguration::GetValue( 'application', 'docroot') .			
			'module/application/business/Application.class.php';	

	
class AutoJurnal extends Database
{
	private $_mSqlFile;
	private $_mError = 0;
	private $_mErrorMessage ;
	private $_mErrorFields = array();		
	private $_mFields =array();
	private $_mKodeTransaksi;
	private $_mUserNameService;
	
	private $_mGNumberObj;

	function __construct ($connectionNumber=0)
	{
		$this->mSqlFile = 'module/'.Dispatcher::Instance()->mModule.'/business/auto_jurnal.sql.php';
		parent::__construct($connectionNumber);
		$this->_mGNumberObj = new GenerateNumber();
		$this->_mFields['kode_unit'] = NULL;
		$this->_mFields['tanggal'] = NULL;
		$this->_mFields['nomor'] = NULL;
		$this->_mFields['uraian'] = NULL;
		$this->_mFields['total'] = NULL;
		$this->_mFields['penanggung_jawab'] = NULL;
		
		/**
		 * mendefiniskan user name untuk proses transaksi auto jurnal
		 */
		$this->_mUserNameService = Application::Instance()->GetSettingValue('username_service_trans_auto_jurnal');	
		
		//$this->setDebugOn();
	}

	public function SetKodeTransaksi($kodeTransaksi = 'POK')
	{
		$this->_mKodeTransaksi = $kodeTransaksi;
	}
	
	public function AssignValue($arrData)
	{
		$this->_mFields['kode_unit'] = isset($arrData['kode_unit']) ? $arrData['kode_unit'] : NULL;
		$this->_mFields['tanggal'] = isset($arrData['tanggal']) ? $arrData['tanggal'] : NULL;
		$this->_mFields['nomor'] = isset($arrData['nomor']) ? $arrData['nomor'] : NULL;
		$this->_mFields['uraian'] = isset($arrData['uraian']) ? $arrData['uraian'] : NULL;
		$this->_mFields['total'] = isset($arrData['total']) ? $arrData['total'] : NULL;
		$this->_mFields['penanggung_jawab'] = isset($arrData['penanggung_jawab']) ? $arrData['penanggung_jawab'] : NULL;
	}
	
	public function SendTransaksi() 
	{		
		$response = array();
				
		if(( $this->_isValidate() === TRUE)){
			
			if($this->_AutoJurnal() === TRUE){
				$response['status'] = 201;			
				$response['message'] =  'Berhasil mengirim data transaksi.';
				$response['data'] = $this->_mFields;
			} else {
				$response['status'] = 406;			
				$response['message'] =  'Gagal mengirim data transaksi.';//. $this->_mErrorMessage;
				$response['data'] = $this->_mFields;
			}
		} else {
		
			$response['status'] = 406;			
			$response['message'] = $this->_mErrorMessage;
			$response['data'] = $this->_mFields;
		}			
			
		return $response;
	}
	
	private function _isValidate()
	{
		if((is_array($this->_mFields)) && (!empty($this->_mFields))) {
			if($this->_IsEmpty() > 0 ){
				$this->_mErrorMessage = 'Data '.$this->_GetErrorFields().' Masih Kosong.';
				$this->_mError = 1;
			} elseif($this->_UnitKerjaIsExist() === FALSE){
				$this->_mErrorMessage = 'Unit Kerja tidak ditemukan.';
				$this->_mError = 1;
			/*} elseif($this->_IsAutoJurnalReady() === FALSE){	
				$this->_mErrorMessage = 'Proses Auto Jurnal Belum Siap. Hubungi Administrator.';
				$this->_mError = 1;*/
			} else {
				$this->_mErrorMessage = '';
				$this->_mError = 0;
			}	
				
		} else {
			$this->_mErrorMessage ='Tidak Ada Data Yang Dikirim.';
			$this->_mError = 1;
		}
		
		if($this->_mError > 0){
			return FALSE;
		} else {
			return TRUE;
		}
	}
	
	private function _IsEmpty()
	{
		if((is_array($this->_mFields)) && (!empty($this->_mFields))){
			foreach($this->_mFields as $key => $val){
				if(empty($val) || (trim($val) =='')){
					$this->_mErrorFields[] = $key;
				}				
			}
		}
		
		if(is_array($this->_mErrorFields)  && (!empty($this->_mErrorFields))) {
			return count($this->_mErrorFields);
		} else {
			return 0;
		}
	}
		
	private function _GetErrorFields()
	{
		if(is_array($this->_mErrorFields)  && (!empty($this->_mErrorFields))) {
			return implode(',',str_replace('_',' ', $this->_mErrorFields));
		} else {
			return NULL;
		}
	}
	
	private function _UnitKerjaIsExist()
	{
		$result =  $this->Open($this->mSqlQueries['get_count_unit_kerja'],array($this->_mFields['kode_unit']));
		if($result[0]['total'] > 0 ){
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	private function _IsAutoJurnalReady()
	{
		$result = $this->Open($this->mSqlQueries['get_count_ref_form_coa'], array($this->_mKodeTransaksi));
		if($result[0]['total'] > 0){
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	private function _AutoJurnal()
	{
		
		$getUnitId = $this->Open($this->mSqlQueries['get_unit_kerja_id'], array($this->_mFields['kode_unit']));
		
		$getUserId = $this->Open($this->mSqlQueries['get_user_id'], array($this->_mUserNameService));
		//print_r($getUserId);
		$unitKerjId = $getUnitId[0]['unit_id'];
		$userId = $getUserId[0]['user_id'];
		
		$noBuktiTransaksi = $this->_mGNumberObj->GetNoBukti($this->_mKodeTransaksi,$unitKerjId);
		
		/**
		 * simpan transaksi
		 */
		 
		 $this->StartTrans();
		 $kelompok = strtolower($this->_mKodeTransaksi);
		 $result = $this->Execute($this->mSqlQueries['insert_transaksi'],
												array(
														$this->_mKodeTransaksi,
														$unitKerjId,
														$noBuktiTransaksi,
														$userId,
														$this->_mFields['tanggal'],
														$this->_mFields['tanggal'],
														'no.'.$this->_mFields['nomor'].' : '.$this->_mFields['uraian'],
														$this->_mFields['total'],
														$this->_mFields['penanggung_jawab'],
														$kelompok,
														'Y'
													));

		// $transId = $this->LastInsertId();
		 
		 /**
		  * proses penjurnalan
		  */
		  
		  /* 
		 if($result){
			$transInfo = $this->Open($this->mSqlQueries['get_transaksi_info'],
												array(
														$transId
													));			
			*/
			 
			/**
			 * catat jurnal
			 * 1. catat di pembukuan ref
			 * 2. catat di pembukuan detail
			 */
			
			/**
			 * simpan ke tabel pembukuan referensi
			 */
			
			 /*
				$result = $this->Execute($this->mSqlQueries['insert_pembukuan_ref'],
														array(
																$transId,
																$userId,
																$this->_mFields['tanggal'],//$transInfo[0]['tanggal'] ,
																$noBuktiTransaksi//$transInfo[0]['referensi']
																));
				
				$prId = $this->LastInsertId();
			*/
			
			/**
			 * simpan ke tabel pembukuan detail
			 */	
			/*
				if($result){	
					$result = $this->Execute($this->mSqlQueries['insert_pembukuan_detail'],
														array(
																$prId,
																$this->_mFields['total'],//$transInfo[0]['nominal'],
																$noBuktiTransaksi,//$transInfo[0]['referensi'],
																$transInfo[0]['catatan'],
																$this->_mKodeTransaksi
																));					
				}
			}
			*/
		 $this->EndTrans($result);
		/**
		 * end proses jurnal
		 */  
		// $this->_mErrorMessage  = $sql;
		 return $result;
		 
	}
	
}

?>