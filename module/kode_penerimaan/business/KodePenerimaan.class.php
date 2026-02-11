<?php

/**
 * untuk mengakses rest client
 */	
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .			
			'module/application/business/Application.class.php';

class KodePenerimaan extends Database 
{

   protected $mSqlFile= 'module/kode_penerimaan/business/kode_penerimaan.sql.php';

	/**
	 * untuk kebutuhan client service
	 */
	protected $mClientServiceOn;
	protected $mServiceAddressId = '520';	
	protected $mModServiceInsert = "?mod=pemetaan_kode_penerimaan&sub=InsertKodePenerimaanRef&act=rest&typ=rest&";
	protected $mModServiceDelete = "?mod=pemetaan_kode_penerimaan&sub=DeleteKodePenerimaanRef&act=rest&typ=rest&";
	/**
	 * end
	 */
	 
	public function __construct($connectionNumber=0) 
	{
		parent::__construct($connectionNumber);
		$this->mClientServiceOn = Application::Instance()->GetSettingValue('client_service_renstra_on');
	}
   
	//==GET==
	public function GetData ($offset, $limit, $data) 
	{
		$result = $this->Open($this->mSqlQueries['get_data'], array('%'.$data['kode'].'%','%'.$data['nama'].'%',$offset,$limit));
		return $result;
	}

	public function GetCount ($data) 
	{
		$result = $this->Open($this->mSqlQueries['get_count'], array('%'.$data['kode'].'%','%'.$data['nama'].'%'));
		if (!$result)
			return 0;
		else
			return $result[0]['total'];
	}

	public function GetDataById($id) 
	{
		$result = $this->Open($this->mSqlQueries['get_data_by_id'], array($id));
		return $result;
	}
	
	public function GetLastKodePenerimaanId()
	{
		$result = $this->Open($this->mSqlQueries['get_last_kode_penerimaan_id'], array());
      return $result[0]['last_id'];
	}
	
	public function GetCoaMap($id)
	{
		$result = $this->Open($this->mSqlQueries['get_coa_map'], array($id));
      	return $result;
	}
	
	public function GetCountCoaMap($id)
	{
		$result = $this->Open($this->mSqlQueries['get_count_coa_map'], array($id));
      	return $result['0']['total'];
	}


	//===DO==

	public function DoAdd($data) 
	{	   
		$this->StartTrans();
		$result = $this->Execute($this->mSqlQueries['do_add'], 
	  			array(
	  				$data['kode'],
				  	$data['nama'],
				  	$data['is_header'],
				  	empty($data['kode_rkakl'])? NULL:$data['kode_rkakl'], 
				  	empty($data['mak_id']) ? NULL : trim($data['mak_id']), 
				  	$data['aktif'],
				  	empty($data['sd_id']) ? NULL : trim($data['sd_id']),
				  	empty($data['parent_id']) ? 0 : trim($data['parent_id'])
				  	)
				  );
				  
		if($result && (!empty($data['coaid']))){
			$result = $this->Execute($this->mSqlQueries['do_add_coa_map'], 
					array(
						$data['coaid'],
						$this->LastInsertId())
						);
		}
      	/**
		 * send data 
		 */
			$sendData['kodeterimaId'] = $this->LastInsertId();
			$sendData['kodeterimaKode'] = $data['kode'];
			$sendData['kodeterimaNama'] = $data['nama'];
			$sendData['kodeterimaTipe'] = $data['is_header'];
			$sendData['kodeterimaRKAKLKodePenerimaanId'] = (empty($data['kode_rkakl'])? NULL:$data['kode_rkakl']);
			$sendData['kodeterimaPaguBasId'] = (empty($data['mak_id']) ? NULL : trim($data['mak_id']));
			$sendData['kodeterimaIsAktif'] = $data['aktif'];
			$sendData['kodeterimaSatKompId'] = NULL;
			$sendData['kodeterimaSumberdanaId'] = (empty($data['sd_id']) ? NULL : trim($data['sd_id']));
			$sendData['kodeterimaParentId'] =(empty($data['parent_id']) ? 0 : trim($data['parent_id']));			
		/**
		 * end send data
		 */
		 
		if($this->mClientServiceOn === 'true'){			
			Application::Instance()->SetRestServiceAddress($this->mServiceAddressId,$this->mModServiceInsert);			
			$resultService = Application::Instance()->SendRestDataDB($sendData,$result);
			
			if(!empty($resultService['status'])  && $resultService['status'] === '201'){
				$resultService['status'] = 'dataSend';
				$dbResult = $result;	
			} else {
				$resultService['status'] = 'dataNotSend';
				$dbResult = false;
			}
		} else {
			$dbResult = $result;
		}
		
		$this->EndTrans($dbResult);		
		return array('dbResult' => $dbResult ,'serviceResult'=>$resultService['status']);
		
	}
   
	public function DoAddCoa($coaid,$id) 
	{
		$result = $this->Execute($this->mSqlQueries['do_add_coa_map'], 
	  			array(
	  				$coaid,
				  	$id)
				  );

		return $result;
	}

	public function DoUpdate($data) 
	{
		$this->StartTrans();
		$result = $this->Execute($this->mSqlQueries['do_update'], 
  				array(
		  			  $data['kode'],
				  	  $data['nama'],
					  $data['is_header'],
					  empty($data['kode_rkakl'])? NULL:$data['kode_rkakl'],
					  empty($data['mak_id']) ? NULL : $data['mak_id'],
					  $data['aktif'],
					  empty($data['sd_id']) ? NULL : $data['sd_id'],
					  empty($data['parent_id']) ? 0 : trim($data['parent_id']),
					  $data['id'])
				  );
		if($result) {
			$getCountCoa = $this->GetCountCoaMap($data['id']);
			if ($getCountCoa > 0){
				 $result = $this->Execute($this->mSqlQueries['do_update_coa_map'], 
							array(
								$data['id'],
								$data['coaid'],
								$data['id'])
								);
			} else {
				if((!empty($data['coaid'])) && (!empty($data['id']))){
					$result = $this->Execute($this->mSqlQueries['do_add_coa_map'], 
							array(
									$data['coaid'],
									$data['id']
								));
				}				
			}
		}
      	/**
		 * send data 
		 */
			$sendData['kodeterimaId'] =  $data['id'];
			$sendData['kodeterimaKode'] = $data['kode'];
			$sendData['kodeterimaNama'] = $data['nama'];
			$sendData['kodeterimaTipe'] = $data['is_header'];
			$sendData['kodeterimaRKAKLKodePenerimaanId'] = (empty($data['kode_rkakl'])? NULL:$data['kode_rkakl']);
			$sendData['kodeterimaPaguBasId'] = (empty($data['mak_id']) ? NULL : trim($data['mak_id']));
			$sendData['kodeterimaIsAktif'] = $data['aktif'];
			$sendData['kodeterimaSatKompId'] = NULL;
			$sendData['kodeterimaSumberdanaId'] = (empty($data['sd_id']) ? NULL : trim($data['sd_id']));
			$sendData['kodeterimaParentId'] =(empty($data['parent_id']) ? 0 : trim($data['parent_id']));			
		/**
		 * end send data
		 */
		 
		if($this->mClientServiceOn === 'true'){			
			Application::Instance()->SetRestServiceAddress($this->mServiceAddressId,$this->mModServiceInsert);
			$resultService = Application::Instance()->SendRestDataDB($sendData,$result);
			
			if(!empty($resultService['status'])  && $resultService['status'] === '201'){
				$resultService['status'] = 'dataSend';
				$dbResult = $result;	
			} else {
				$resultService['status'] = 'dataNotSend';
				$dbResult = false;
			}
		} else {
			$dbResult = $result;
		}
		
		$this->EndTrans($dbResult);		
		return array('dbResult' => $dbResult ,'serviceResult'=>$resultService['status']);
		      
	}
	
	public function DoUpdateCoaMap($data) 
	{
		$result = $this->Execute($this->mSqlQueries['do_update_coa_map'], 
  				array(
		  			  $data['id'],
					  $data['coaid'],
					  $data['id'])
				  );
		//$this->mdebug(1);
		return $result;
	}

	public function DoDelete($id) 
	{
		$this->StartTrans();
		$result=$this->Execute($this->mSqlQueries['do_delete'], array($id));
		
		if($result){
			$getCountCoa = $this->GetCountCoaMap($id);
			if($getCountCoa > 0 ){
				$result = $this->Execute($this->mSqlQueries['do_delete_coa_map'], array($id));
			}
		}
		
		$sendData['kodeterimaId'] = $id;
		
		if($this->mClientServiceOn === 'true'){
			
			Application::Instance()->SetRestServiceAddress($this->mServiceAddressId,$this->mModServiceDelete);
			$resultService = Application::Instance()->SendRestDataDB($sendData,$result);
							
			if(!empty($resultService['status'])  && $resultService['status'] === '201'){
				$resultService['status'] = 'dataSend';
				$dbResult = $result;	
			} else {					
				$resultService['status'] = 'dataNotSend';
				$dbResult = false;
			}
			
		} else {
			$dbResult = $result;
		}
			
		$this->EndTrans($dbResult);
		//return $dbResult;
		return array('dbResult' => $dbResult ,'serviceResult'=>$resultService['status']);
	}
   
	public function DoDeleteCoaMap($id) 
	{
		$result=$this->Execute($this->mSqlQueries['do_delete_coa_map'], array($id));
		return $result;
	}
   
	/**
 	 * added
 	 * @since 29 Fabruari 2012
 	 * mendapatkan satuan dari satuan komponen
 	 */
 
	public function GetListSatuan()
 	{
		return $this->Open($this->mSqlQueries['get_list_satuan'], array());
 	}
 	
 	public function GetSatuanById($id='')
 	{
		return $this->Open($this->mSqlQueries['get_satuan_by_id'], array($id));
 	}
 	
	/* add cecep 10 februari 2026 */
	public function GetDataByIdJnsPembayaran($id) 
	{
		$result = $this->Open($this->mSqlQueries['get_coa_map_id_pembayaran'], array($id));
		return $result;
	}
	
 	public function GetCoaMapByIdPembayaran($id)
	{
		$result = $this->Open($this->mSqlQueries['get_coa_by_id'], array($id));
      	return $result;
	}
	
	public function DoUpdateMapping($data) 
	{
		$this->StartTrans();
		$this->Execute($this->mSqlQueries['do_delete_map_pembayaran_penerimaan'], array($data['jenis_pembayaran_id']));
		$result = $this->Execute($this->mSqlQueries['do_update_mapping_pembayaran_id'], 
  				array(
					  $data['jenis_pembayaran_id'],
					  $data['penerimaan_id']
					  )
				  );
			
		$resultService['status'] = 'dataNotSend';
		$dbResult = $result;
		
		$this->EndTrans($dbResult);		
		return array('dbResult' => $dbResult ,'serviceResult'=>$resultService['status']);
		      
	}
	
	public function GetArrCoa()
	{
		$result = $this->Open($this->mSqlQueries['get_coa_all'], array());
      	return $result;
	}
	/* end  */
}
?>