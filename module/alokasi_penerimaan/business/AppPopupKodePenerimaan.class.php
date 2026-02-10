<?php

/**
 * @package alokasi_penerimaan
 * @since 27 Maret 2012
 * @copyright 2012 Gamatechno
 */ 


class AppPopupKodePenerimaan extends Database 
{

    protected $mSqlFile= 'module/alokasi_penerimaan/business/app_popup_kode_penerimaan.sql.php';
   
    public function __construct($connectionNumber=0) 
    {    
        parent::__construct($connectionNumber);       
    }

    public function GetData($offset, $limit, $keyword='', $unitkerja) 
	{
		$result = $this->Open($this->mSqlQueries['get_data'], 	
						array(
								'%'.$keyword.'%', 
								'%'.$keyword.'%', 
								$offset, 
								$limit));
		return $result;
	}

	public function GetCountData($keyword='', $unitkerja='') 
	{
		$result = $this->Open($this->mSqlQueries['get_count_data'],
						array(
								'%'.$keyword.'%', 
								'%'.$keyword.'%',
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