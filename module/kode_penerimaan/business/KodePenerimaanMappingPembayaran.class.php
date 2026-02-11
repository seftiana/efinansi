<?php

/**
 * untuk mengakses rest client
 */	
require_once GTFWConfiguration::GetValue( 'application', 'docroot').'module/application/business/Application.class.php';

class KodePenerimaanMappingPembayaran extends Database 
{

   protected $mSqlFile= 'module/kode_penerimaan/business/kode_penerimaan_mapping_pembayaran.sql.php';

	/**
	 * untuk kebutuhan client service
	 */
	protected $mClientServiceOn;
	protected $mServiceAddressId = '520';	
	protected $mModServiceInsert = "?mod=pemetaan_kode_penerimaan&sub=InsertKodePenerimaanRef&act=rest&typ=rest&";
	protected $mModServiceDelete = "?mod=pemetaan_kode_penerimaan&sub=DeleteKodePenerimaanRef&act=rest&typ=rest&";

	 
	public function __construct($connectionNumber=1) 
	{
		parent::__construct($connectionNumber);
		$this->mClientServiceOn = Application::Instance()->GetSettingValue('client_service_renstra_on');
	}
   
	//==GET==
	public function GetData ($offset, $limit, $data) 
	{
		$result = $this->Open($this->mSqlQueries['get_data_jenis_pembayaran'], array('%'.$data['kode'].'%','%'.$data['nama'].'%',$offset,$limit));
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
	
	public function GetDataJenisPembayaranId($id) 
	{
		$result = $this->Open($this->mSqlQueries['get_data_jenis_pembayaran_id'], array($id));
		return $result;
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

	public function DoAdd($data) 
	{	   
		$this->StartTrans();
		$result = $this->Execute($this->mSqlQueries['do_add'], 
	  			array(
	  				$data['penerimaan_id'],
				  	$data['prodi_id'],
				  	$data['coaid']
				 )
			);
				  
		$dbResult = $result;
		$this->EndTrans($dbResult);		
		return array('dbResult' => $dbResult);
		
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
		

		$dbResult = $result;
		
		$this->EndTrans($dbResult);
		//return $dbResult;
		return array('dbResult' => $dbResult);
	}
   
	public function DoDeleteCoaMap($id) 
	{
		$result=$this->Execute($this->mSqlQueries['do_delete_coa_map'], array($id));
		return $result;
	}
	
	public function ChangeKeyName($input = array(), $case = 'lower')
   {
      if(!is_array($input)){
         return $input;
      }

      foreach ($input as $key => $value) {
         if(is_array($value)){
            foreach ($value as $k => $v) {
               $array[$key][self::humanize($k, $case)] = $v;
            }
         }
         else{
            $array[self::humanize($key, $case)]  = $value;
         }
      }

      return (array)$array;
   }
   
	public function GetDataExcel()
	{
      $return     = $this->Open($this->mSqlQueries['get_data_jenis_pembayaran_all'], array());
	  // echo '<pre>';print_r($return);die;
      return $return;
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
 	
	
	public function DoUpdateCoaMapBiayaPembayaran($data) 
	{
		$this->StartTrans();
		$result = $this->Execute($this->mSqlQueries['do_update'], 
  				array(
		  			  $data['penerimaan_id'],
		  			  $data['prodi_id'],
					  $data['coaid'],
					  $data['id']
				)
			);
		$dbResult = $result;
		$this->EndTrans($dbResult);
		return array('dbResult' => $dbResult);
	}
}
?>