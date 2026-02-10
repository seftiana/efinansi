<?php

/**
 * untuk mengakses rest client
 */	
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .			
			'module/application/business/Application.class.php';


class AppUnitkerja extends Database 
{

	protected $mSqlFile= 'module/unitkerja_tree/business/appunitkerja.sql.php';

	
	/**
	 * untuk kebutuhan client service
	 */
	protected $mClientServiceOn;
	
	protected $mServiceAddressPembayaranId = '520';
	protected $mServiceAddressKeuanganId = '530';
	
	protected $mModServiceInsert ="?mod=unitkerja&sub=InsertReferensiUnitKerja&act=rest&typ=rest&";
	protected $mModServiceUpdate ="?mod=unitkerja&sub=UpdateReferensiUnitKerja&act=rest&typ=rest&";
	protected $mModServiceDelete ="?mod=unitkerja&sub=DeleteReferensiUnitKerja&act=rest&typ=rest&";
	
	
	/**
	 * end
	 */
	
	public function __construct($connectionNumber=0) 
    {
		
		parent::__construct($connectionNumber);		
		                 
		$this->idUser = Security::Instance()->mAuthentication->getcurrentuser()->GetUserId();		
		$this->mClientServiceOn = Application::Instance()->GetSettingValue('client_service_unit_kerja_on');		
		//$this->setDebugOn();
	}
   
    public function GetDataExcel($unitkerja='', $kode='', $tipeunit='') 
    {
		if($tipeunit != "") 
			$str_tipeunit = " AND unitkerjaTipeUnitId = " . $tipeunit;
		else 
			$str_tipeunit = "";

		$sql = sprintf( $this->mSqlQueries['get_data_excel'],
                        '%'.$kode.'%', 
                        '%'.$kode.'%', 
                        '%'.$unitkerja.'%', 
                        '%'.$unitkerja.'%', 
                        $str_tipeunit);
		
		return $this->Open($sql, array());
	}
   
	public function GetUnitKerja($offset, $limit,$kode,$nama,$type)
    {
		$strplus ='';
		if($type!='')
			$strplus = " AND unitkerjaTipeunitId = '%s'";
		
		$newSql = sprintf($this->mSqlQueries['get_unit_kerja'],'%s','%s',$strplus,'%s','%s',$strplus,'%s','%s');
		
		if($type!='')
			$ret = $this->Open($newSql,
                                array(
                                        '%'.$kode.'%',
                                        '%'.$nama.'%',
                                        $type,
                                        '%'.$kode.'%',
                                        '%'.$nama.'%',
                                        $type,
                                        $offset, 
                                        $limit));
		else
			$ret = $this->Open($newSql,
                                array(
                                        '%'.$kode.'%',
                                        '%'.$nama.'%',
                                        '%'.$kode.'%',
                                        '%'.$nama.'%',
                                        $offset, 
                                        $limit));
      //print_r($ret);
      return $ret;
	}
	//old function

		
	public function GetDataUnitkerja ($offset, $limit, $unitkerja='', $kode='', $tipeunit='',$parentId=0) 
    {
		if($tipeunit != "") 
			$str_tipeunit = " AND unitkerjaTipeUnitId = " . $tipeunit;
		else 
			$str_tipeunit = "";

		$sql = sprintf(
					$this->mSqlQueries['get_data_unitkerja'],
							'%'.$kode.'%', 
							'%'.$kode.'%', 
							'%'.$unitkerja.'%', 
							'%'.$unitkerja.'%', 
							$str_tipeunit, 
							$offset, 
							$limit);
		return $this->Open($sql, array());
	}

	public function GetCountDataUnitkerja ($unitkerja='', $kode='', $tipeunit='',$parentId=0) 
    {
		if($tipeunit != "") 
			$str_tipeunit = " AND unitkerjaTipeUnitId = " . $tipeunit;
		else 
			$str_tipeunit = "";
		
		$sql = sprintf(
						$this->mSqlQueries['get_count_data_unitkerja'], 
								'%'.$kode.'%', 
								'%'.$kode.'%', 
								'%'.$unitkerja.'%', 
								'%'.$unitkerja.'%',
								 $str_tipeunit);
		$result = $this->Open($sql, array());
	 

		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}
	 
	public function GetDataUnitkerjaById($unitkerjaId) 
    {
		$result = $this->Open($this->mSqlQueries['get_data_unitkerja_by_id'], array($unitkerjaId));
	
		return $result;
	}

	public function GetDataUnitkerjaByArrayId($arrUnitkerjaId) 
    {
		$unitkerjaId = implode("', '", $arrUnitkerjaId);
		$result = $this->Open($this->mSqlQueries['get_data_unitkerja_by_array_id'], array($unitkerjaId));
		return $result;
	}

	//untuk combo box
	public function GetDataSatker($unitkerjaId = NULL) 
    {
		$sql_params = (empty($unitkerjaId) ? "" : " WHERE unitkerjaId='".$unitkerjaId."'");
		$sql = sprintf($this->mSqlQueries['get_data_satker'],$sql_params);
		$result = $this->Open($sql, array());
		return $result;
	}

	public function GetDataTipeUnit($unitkerjaId = NULL) 
    {
		$result = $this->Open($this->mSqlQueries['get_data_tipe_unit'], array());
		return $result;
	}
	
	public function GetStatusUnitKerja()
    {
		return $this->Open($this->mSqlQueries['get_status_unit_kerja'],array());
	}

//===DO==
	public function GetGenerateKodeSistem($unitKerjaParent=0)
	{
		$result = $this->Open($this->mSqlQueries['generate_kode_sistem'], 
							array(
							$unitKerjaParent,$unitKerjaParent,$unitKerjaParent,
							$unitKerjaParent,$unitKerjaParent,$unitKerjaParent,
							$unitKerjaParent
							));
		return $result[0]['kode'];
	}
    
	public function DoAddUnitkerja($pimpinan,$unitkerjaKode,$unitkerjaNama,$tipeunit,$statusunit,$parentId=0) 
    {	
		$this->StartTrans();	
		if($parentId == '' or empty($parentId)) $parentId = '0';
			$kodeSistem  = $this->GetGenerateKodeSistem($parentId);
			$result = $this->Execute($this->mSqlQueries['do_add_unitkerja'], 
						array(
								$unitkerjaKode, 
								$kodeSistem,
								$unitkerjaNama, 
								$tipeunit, 
								$statusunit, 
								$parentId, 
								$pimpinan));
		/**
		 * send data 
		 */
			$resultUnitKode = $this->Open($this->mSqlQueries['get_kode_unit_kerja'], array($parentId));
			//$data['unitkerjaId'] = $this->LastInsertId();
			$data['unitkerjaKodeSistem'] = $kodeSistem;
			$data['unitkerjaKode'] = $unitkerjaKode;
			$data['unitkerjaNama'] = $unitkerjaNama;
			$data['unitkerjaTipeunitId'] = $tipeunit;
			$data['unitkerjaNamaPimpinan'] = $pimpinan;
			$data['unitKerjaUnitStatusId'] = $statusunit; 
			$data['unitKerjaJenisId'] = 1;
			$data['unitkerjaParentKode'] = $resultUnitKode[0]['unitkerjaKode'];
			
		/**
		 * end send data
		 */									
				
		if($this->mClientServiceOn === 'true'){			
			
			Application::Instance()->SetRestServiceAddress($this->mServiceAddressPembayaranId,$this->mModServiceInsert);
			$resultServicePembayaran = Application::Instance()->SendRestDataDB($data,$result);
			//echo $this->mServiceAddressId;
			//print_r($resultServicePembayaran);
			
			Application::Instance()->SetRestServiceAddress($this->mServiceAddressKeuanganId,$this->mModServiceInsert);
			$resultServiceKeuangan = Application::Instance()->SendRestDataDB($data,$result);
			//print_r($resultServiceKeuangan);
			
			if(
			(!empty($resultServicePembayaran['status'])  && (($resultServicePembayaran['status'] === '201') || ($resultServicePembayaran['status'] === 201)))  &&
			(!empty($resultServiceKeuangan['status'])  && (($resultServiceKeuangan['status'] === '201') || ($resultServiceKeuangan['status'] === 201))) 
			) {
					
				$resultService['status'] = 'dataSend';
				$dbResult = $result;	
				
			} else {
				//proses roleback
				$dataKode['unitkerjaKode'] = $unitkerjaKode ;
				if(!empty($resultServicePembayaran['status'])  && (($resultServicePembayaran['status'] === '201') || ($resultServicePembayaran['status'] === 201))) {
					Application::Instance()->SetRestServiceAddress($this->mServiceAddressPembayaranId,$this->mModServiceDelete);
					Application::Instance()->SendRestDataDB($dataKode,$result);
				}
				
				if(!empty($resultServiceKeuangan['status'])  && (($resultServiceKeuangan['status'] === '201') || ($resultServiceKeuangan['status'] === 201))) { 
					Application::Instance()->SetRestServiceAddress($this->mServiceAddressKeuanganId,$this->mModServiceDelete);
					Application::Instance()->SendRestDataDB($dataKode,$result);
				}
							
				$resultService['status'] = 'dataNotSend';
				$dbResult = false;
			}
		} else {
			$dbResult = $result;
		}
		
		$this->EndTrans($dbResult);		
		return array('dbResult' => $dbResult ,'serviceResult'=>$resultService['status']);
	}
	
	public function DoUpdateUnitkerja($pimpinan, $unitkerjaKode, $unitkerjaNama, $tipeunit, 
                $statusunit, $parentId=0, $unitkerjaId) 
    {
		//$this->setDebugOn();
		if(is_object($unitkerjaId)){
				$unitkerjaId = $unitkerjaId->mrVariable;
		}
		
		if($parentId == '' or empty($parentId)) $parentId = '0';
      
		$get_unit_kode_sistem = $this->GetKodeSistem($unitkerjaId);
      
		$kodeSistemLama =  $get_unit_kode_sistem['kode_sistem'];
      
		if($parentId != $get_unit_kode_sistem['parent_id']){
            $kodeSistem = $this->GetGenerateKodeSistem($parentId);
		} else {
            $kodeSistem = $get_unit_kode_sistem['kode_sistem'];
		}
      
		$kodeSistemBaru = $kodeSistem;
      
		$this->StartTrans();
		$result = $this->Execute($this->mSqlQueries['do_update_unitkerja'], 
                                    array(
                                            $unitkerjaKode, 
                                            /*$kodeSistem,*/
                                            $unitkerjaNama, 
                                            $tipeunit, 
                                            $statusunit,
                                             $parentId, 
                                             $pimpinan, 
                                             $unitkerjaId));
        if($result && ($kodeSistemLama != $kodeSistemBaru)){
			$result = $this->Execute($this->mSqlQueries['do_update_kode_sistem'], 
                                    array(
                                            $kodeSistemBaru,
                                            $kodeSistemLama,
                                            $kodeSistemLama.'.%',
                                            $kodeSistemLama
                                            ));
		}
		
			$unitKerjaKodeLama = $this->Open($this->mSqlQueries['get_kode_unit_kerja'], array($unitkerjaId));
			$resultUnitKode = $this->Open($this->mSqlQueries['get_kode_unit_kerja'], array($parentId));
		//print_r($unitKerjaKodeLama[0]['unitkerjaKode']);
		/**
		 * send data 
		 */	 
			//$data['unitkerjaId'] = $unitkerjaId;
			$data['unitkerjaKodeSistem'] = $kodeSistemBaru;
			$data['unitkerjaKode'] = $unitkerjaKode;
			$data['unitkerjaNama'] = $unitkerjaNama;
			$data['unitkerjaTipeunitId'] = $tipeunit;
			$data['unitkerjaNamaPimpinan'] = $pimpinan;
			$data['unitKerjaUnitStatusId'] = $statusunit; 
			$data['unitKerjaJenisId'] = 1;
			$data['unitkerjaParentKode'] = $resultUnitKode[0]['unitkerjaKode'];//$parentId;	
			   $data['unitkerjaKode'] = $unitKerjaKodeLama[0]['unitkerjaKode'];

		
		/**
		 * end send data
		 */	
			if($this->mClientServiceOn === 'true'){
				
			Application::Instance()->SetRestServiceAddress($this->mServiceAddressPembayaranId,$this->mModServiceUpdate);
			$resultServicePembayaran = Application::Instance()->SendRestDataDB($data,$result);
			//echo $this->mServiceAddressId;
			//print_r($resultServicePembayaran);
			Application::Instance()->SetRestServiceAddress($this->mServiceAddressKeuanganId,$this->mModServiceUpdate);
			$resultServiceKeuangan = Application::Instance()->SendRestDataDB($data,$result);
			//print_r($resultServiceKeuangan);						
			
			if(
			(!empty($resultServicePembayaran['status'])  && (($resultServicePembayaran['status'] === '201') || ($resultServicePembayaran['status'] === 201)))  &&
			(!empty($resultServiceKeuangan['status'])  && (($resultServiceKeuangan['status'] === '201') || ($resultServiceKeuangan['status'] === 201))) 
			) {
					
					$resultService['status'] = 'dataSend';
					$dbResult = $result;	
				} else {					
					//proses roleback
					$dataKode['unitkerjaKode'] = $unitkerjaKode ;
					if(!empty($resultServicePembayaran['status'])  && (($resultServicePembayaran['status'] === '201') || ($resultServicePembayaran['status'] === 201))) {
						Application::Instance()->SetRestServiceAddress($this->mServiceAddressPembayaranId,$this->mModServiceDelete);
						Application::Instance()->SendRestDataDB($dataKode,$result);
					}
				
					if(!empty($resultServiceKeuangan['status'])  && (($resultServiceKeuangan['status'] === '201') || ($resultServiceKeuangan['status'] === 201))) { 
						Application::Instance()->SetRestServiceAddress($this->mServiceAddressKeuanganId,$this->mModServiceDelete);
						Application::Instance()->SendRestDataDB($dataKode,$result);
					}
									
					$resultService['status'] = 'dataNotSend';
					$dbResult = false;
				}
			} else {
				$dbResult = $result;
			}
			
		$this->EndTrans($dbResult);
		return array('dbResult' => $dbResult ,'serviceResult'=>$resultService['status']);
	}
	
	public function DoDeleteUnitkerjaById($unitkerjaId) 
    {
		$result=$this->Execute($this->mSqlQueries['do_delete_unitkerja_by_id'], 
                                    array(
                                            $unitkerjaId,
                                            $unitkerjaId));
		
		return $result;
	}
	
	public function DoDeleteUnitkerjaByArrayId($arrUnitkerjaId) 
    {
		
		$this->StartTrans();		
		$unitkerjaId = implode("', '", $arrUnitkerjaId);
		
		$resultUnitKode = $this->Open($this->mSqlQueries['get_kode_unit_kerja'], array($unitkerjaId));
		
		$result = $this->Execute($this->mSqlQueries['do_delete_unitkerja_by_array_id'], array($unitkerjaId));
		
		if(!empty($resultUnitKode)){
			foreach($resultUnitKode as $key => $val){
				$arrUnitKerjaKode[] = $val['unitkerjaKode'];
			}
		}		
		
		$data['unitkerjaKode'] = $arrUnitKerjaKode;		
		
		if($this->mClientServiceOn === 'true'){
			
			Application::Instance()->SetRestServiceAddress($this->mServiceAddressPembayaranId,$this->mModServiceDelete);
			$resultServicePembayaran = Application::Instance()->SendRestDataDB($data,$result);
			//echo $this->mServiceAddressId;
			//print_r($resultServicePembayaran);
			Application::Instance()->SetRestServiceAddress($this->mServiceAddressKeuanganId,$this->mModServiceDelete);
			$resultServiceKeuangan = Application::Instance()->SendRestDataDB($data,$result);
		//	print_r($resultServiceKeuangan);
						
			if(
			(!empty($resultServicePembayaran['status'])  && (($resultServicePembayaran['status'] === '201') || ($resultServicePembayaran['status'] === 201)))  ||
			(!empty($resultServiceKeuangan['status'])  && (($resultServiceKeuangan['status'] === '201') || ($resultServiceKeuangan['status'] === 201))) 
			) {
				$resultService['status'] = 'dataSend';
				$dbResult = $result;	
			} else {					
				$resultService['status'] = 'dataNotSend';
				$dbResult = false;
			}
			
		} else {
			$dbResult = $result;
		}
			
		$this->EndTrans(dbResult);
		return array('dbResult' => $dbResult ,'serviceResult'=>$resultService['status']);
	}

   public function GetComboUnitKerja()
   {
      return $this->Open($this->mSqlQueries['get_combo_unit_kerja'],array());
   }
   
   public function cekUnitParent($parentId)
   {
      $total = $this->Open($this->mSqlQueries['cek_unit_parent'],array($parentId));
      return $total[0]['total'];
   }
   
   public function GetKodeSistem($unitId)
   {
        $kode_sistem = $this->Open($this->mSqlQueries['get_kode_sistem'],array($unitId));
        return $kode_sistem[0];
   }
   
   //function GetUnitKerjaByParentId($parentId = 0,$namaUnit='',$tipe='',$kode='')
   public function GetUnitKerjaByParentId($parentId = 0)
   {
    /**
          if($tipe != ''){
            $sql = " AND (c.`unitkerjaTipeunitId` = ".$tipe." OR m.`unitkerjaTipeunitId` = ".$tipe.")";
          } else {
            $sql ="";
          }
          */
          if(empty($parentId)){
            $parentId = 0;
         }
         
         $query = sprintf($this->mSqlQueries['get_unit_kerja_by_parent_id'],$parentId);
         /**
         $query = sprintf($this->mSqlQueries['get_unit_kerja_by_parent_id_v2'],
                                    '%'.$namaUnit.'%',
                                    '%'.$namaUnit.'%',
                                    '%'.$kode.'%',
                                    '%'.$kode.'%',
                                    $sql,
                                    '%'.$namaUnit.'%',
                                    '%'.$namaUnit.'%',
                                    '%'.$kode.'%',
                                    '%'.$kode.'%',
                                    $sql,
                                    $parentId);
                                    */
        return $this->Open($query,array());
    }
   
    public function GetCountChild($parentId = 0)
    {
        if(empty($parentId)){
            $parentId = 0;
         }
        $result = $this->Open($this->mSqlQueries['get_count_child'],array($parentId));
        return $result[0]['total'];
    }
	
}

?>