<?php

class PopupUnitkerja extends Database 
{

   protected $mSqlFile= 'module/pagu_anggaran_unit/business/popupunitkerja.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
   	
	function GetDataUnitkerja ($offset, $limit, $kode='', $unitkerja='', $tipeunit='', $parentId) 
	{
		
		$result = $this->Open($this->mSqlQueries['get_list_unit_kerja'],
					array(
							$parentId,
							'%',
							$parentId,
							'%'.$kode.'%',
							'%'.$unitkerja.'%',
							'%'.$tipeunit.'%',
							$offset, $limit
						 )
					);
     	return $result;
     	
	}

	function GetCountDataUnitkerja ($kode='', $unitkerja='', $tipeunit='', $parentId) 
	{
		
		$result = $this->Open($this->mSqlQueries['get_count_list_unit_kerja'],
					array(
							$parentId,
							'%',
							$parentId,
							'%'.$kode.'%',
							'%'.$unitkerja.'%',
							'%'.$tipeunit.'%'
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