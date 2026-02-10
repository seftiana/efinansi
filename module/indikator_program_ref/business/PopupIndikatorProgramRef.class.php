<?php

/**
 * Class PopupIndikatorProgramRef
 * @package indikator_program_ref
 * @todo Untuk menjalankan perintah-perintah query
 * @subpackage business
 * @since 22 Juni 2012
 * @SystemAnalyst Dyan Galih Nugroho Wicaksi <galih@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
class PopupIndikatorProgramRef extends Database
{
    
    protected $mSqlFile= 'module/indikator_program_ref/business/popupindikatorprogramref.php';
    
    public function __construct($connectionNumber = 0)
    {
        parent::__construct($connectionNumber);
        //$this->SetDebugOn();       
    }
    
    /**
     *  untuk proses akses data
     */
     
    public function GetData($kode,$nama,$startRec,$itemViewed)
    {
        $result = $this->Open($this->mSqlQueries['get_data'],
                                                array(
                                                        '%'.$kode.'%',
                                                        '%'.$nama.'%',
                                                        $startRec,
                                                        $itemViewed
                                                      ));
        return $result;                                                      
    } 
     
    public function GetDataCount($kode,$nama)
    {
        $result = $this->Open($this->mSqlQueries['get_data_count'],
                                                array(
                                                        '%'.$kode.'%',
                                                        '%'.$nama.'%'
                                                      ));
        return $result[0]['total'];                                                              
    }     
}