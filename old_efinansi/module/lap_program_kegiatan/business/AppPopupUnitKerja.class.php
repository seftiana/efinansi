<?php

class AppPopupUnitkerja extends Database 
{

    protected $mSqlFile= 'module/lap_program_kegiatan/business/apppopupunitkerja.sql.php';
   
    public function __construct($connectionNumber=0) 
    {
        parent::__construct($connectionNumber);
	  //$this->SetDebugOn();       
    }
	
    public function GetDataUnitkerja ($offset, $limit, $kode='', $unitkerja='', $tipeunit='', $parentId) 
	{
		$result = $this->Open($this->mSqlQueries['get_list_unit_kerja'],
					array(
							$parentId,
							'.%',
							$parentId,
							'%'.$kode.'%',
							'%'.$unitkerja.'%',
							'%'.$tipeunit.'%',
							$offset, $limit
						 )
					);
	
		return $result;
	}

    public function GetCountDataUnitkerja ($kode='', $unitkerja='', $tipeunit='',$parentId) 
	{
        $result = $this->Open($this->mSqlQueries['get_count_list_unit_kerja'],
					array(
							$parentId,
							'.%',
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
    
    public function GetDataTipeUnit($unitkerjaId = NULL) 
    {
		$result = $this->Open($this->mSqlQueries['get_data_tipe_unit'], array());
		return $result;
	}
	
	/**
	 * added fitur baru
	 * @since 30 Desember 2011
	 */
 	public function GetTotalSubUnitKerja($parentId)
	 {
	 	$result = $this->Open($this->mSqlQueries['get_total_sub_unit_kerja'], array($parentId));
	 	return $result[0]['total'];
	 }
	 
	
	/**
	 * end
	 */	

}