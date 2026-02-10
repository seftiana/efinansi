<?php

class PopupUnitKerjaPusat extends Database 
{

   protected $mSqlFile= 'module/penerima_alokasi_unit/business/popup_unit_kerja_pusat.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
     //$this->setDebugOn();
   }
   	
	function GetDataUnitkerja ($offset, $limit, $kode='', 
								$unitkerja='', $tipeunit='') 
	{ 
		if($tipeunit ==''){
			$flag = 1;
		} else {
			$flag = 0;
		}
		
		
			$result = $this->Open($this->mSqlQueries['get_list_unit_kerja'],
					array(
							'%'.$kode.'%',
							'%'.$unitkerja.'%',
							$tipeunit,$flag,
							$offset, $limit
						 )
					);
		
     	return $result;
     	
	}

	function GetCountDataUnitkerja ($kode='', $unitkerja='', $tipeunit='') 
	{
		if($tipeunit ==''){
			$flag = 1;
		} else {
			$flag = 0;
		}	
		
	
			$result = $this->Open($this->mSqlQueries['get_count_list_unit_kerja'],
					array(
							'%'.$kode.'%',
							'%'.$unitkerja.'%',
							$tipeunit,$flag
						 )
						);
	
		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}
	function GetDataTipeUnit($unitkerjaId = NULL) {
		$result = $this->Open($this->mSqlQueries['get_data_tipe_unit'], array());
		return $result;
	}
	
	public function GetDataUnitAnak($unitKerjaId) 
	{
		$dataUnit = array();
		$result = $this->Open($this->mSqlQueries['get_data_unit_parent_id'], 
					array($unitKerjaId,$unitKerjaId));
		foreach($result as $key => $value){
			$dataUnit[$value['unit_parent_id']] = $this->Open(
											$this->mSqlQueries['get_data_unit_anak'], 
											array(
													$value['unit_parent_id'],
													$value['unit_parent_id']
												));
		}
		return $dataUnit;
	}
	
}

?>