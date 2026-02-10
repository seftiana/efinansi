<?php

/**
 * 
 * class PopupTujuan
 * @package sasaran
 * @subpackage business
 * @filename PopupTujuan.class.php
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * @since 2 Agustus 2012
 * 
 */
 
class PopupTujuan extends Database
{
    
    protected $mSqlFile = 'module/sasaran/business/popup_tujuan.sql.php';
    
    
    public function __construct ($connectionNumber = 0)
    {
        parent::__construct($connectionNumber);
        //$this->setDebugOn();
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
