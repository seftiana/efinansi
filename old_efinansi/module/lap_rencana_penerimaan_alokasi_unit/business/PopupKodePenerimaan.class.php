<?php

/**
 * 
 * class PopupKodePenerimaan
 * @since 11 November 2012
 * @analyst nanang_ruswianto<nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */

class PopupKodePenerimaan extends Database 
{

	protected $mSqlFile= 'module/lap_rencana_penerimaan_alokasi_unit_v2/business/popup_kode_penerimaan.sql.php';
   
	public function __construct($connectionNumber=0) 
	{
		parent::__construct($connectionNumber);       
	}

	public function GetData($offset, $limit, $kode='',$nama='') 
	{
		
		$result = $this->Open($this->mSqlQueries['get_data'], 	
						array(
								'%'.$kode.'%', 
								'%'.$nama.'%', 
								$offset, 
								$limit));
		return $result;
	}

	public function GetCountData($kode='',$nama='') 
	{
		
		$result = $this->Open($this->mSqlQueries['get_count_data'],
						array(
								'%'.$kode.'%', 
								'%'.$nama.'%'
							)
						);
		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}
}

?>