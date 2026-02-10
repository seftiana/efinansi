<?php

/**
 * 
 * class PopupSasaran
 * @package program_kegiatan
 * @subpackage business
 * @filename PopupSasaran.class.php
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * @since 3 Agustus 2012
 * 
 */

class PopupSasaran extends Database
{
	
    protected $mSqlFile = 'module/program_kegiatan/business/popup_sasaran.sql.php';
    
    public function __construct ($connectionNumber = 0)
    {
        parent::__construct($connectionNumber);
    }
    
    public function GetData($kode,$offset,$limit)
    {
        $return = $this->Open(
							$this->mSqlQueries['get_data'], 
							array(
								'%'.$kode.'%',
								'%'.$kode.'%',
								$offset,
								$limit
								)
							);
        
        return $return;
    }
    
    public function CountData($kode)
    {
		$return = $this->Open(
							$this->mSqlQueries['count_data'],
							array(
								'%'.$kode.'%',
								'%'.$kode.'%'
								)
						);
        
        return $return[0]['total'];
    }
}
