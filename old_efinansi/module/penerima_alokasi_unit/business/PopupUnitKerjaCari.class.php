<?php

class PopupUnitKerjaCari extends Database 
{

   protected $mSqlFile= 'module/penerima_alokasi_unit/business/popup_unit_kerja_cari.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
     //$this->setDebugOn();
   }
   	
	function GetDataUnitkerja ($offset, $limit, $kode='', 
								$unitkerja='', $tipeunit='', $parentId) 
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

	function GetCountDataUnitkerja ($kode='', $unitkerja='', $tipeunit='', $parentId) 
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
	
	
}

?>