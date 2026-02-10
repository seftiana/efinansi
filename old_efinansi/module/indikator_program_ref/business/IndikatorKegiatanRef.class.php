<?php

/**
 * Class IndikatorKegiatanRef
 * @package indikator_program_ref
 * @todo Untuk menjalankan perintah-perintah query
 * @subpackage business
 * @since 22 Juni 2012
 * @SystemAnalyst Dyan Galih Nugroho Wicaksi <galih@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
class IndikatorKegiatanRef extends Database
{
    
    protected $mSqlFile= 'module/indikator_program_ref/business/indikatorkegiatanref.sql.php';
    protected $mUserId;
    
    public function __construct($connectionNumber = 0)
    {
        $this->mUserId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
        parent::__construct($connectionNumber);
        //$this->SetDebugOn();       
    }
    
    /**
     *  untuk proses akses data
     */
    
    public function GetDataById($id)
    {
        $result = $this->Open($this->mSqlQueries['get_data_by_id'],array($id));
        return $result;        
    } 
    
    /**
     * fungsi GetCountKode
     * @param number $kode
     * @todo untuk mengecek kode yang sudah ada
     * @return number
     */
    public function GetCountKode($kode)
    {
        //$this->SetDebugOn();
        $result = $this->Open($this->mSqlQueries['get_count_kode'],array($kode));
        return $result[0]['total'];
    }
    /**
     * untuk proses manipulasi data
     */    
    public function Add($arrData)
    {    
        //$this->SetDebugOn();
        if(is_array($arrData) && (!empty($arrData))){
            $this->StartTrans();
            $result = $this->Execute($this->mSqlQueries['add'],
                                                    array(
                                                       $arrData['ipId'],
                                                       $arrData['kode'],
                                                       $arrData['nama'],
                                                       $arrData['value'],
                                                       $this->mUserId 
                                                    ));
            $this->EndTrans($result);
        }            
        return $result;
    }
    
    public function Update($arrData)
    {
        if(is_array($arrData) && (!empty($arrData))){
            $this->StartTrans();
            $result = $this->Execute($this->mSqlQueries['update'],
                                                    array(
                                                        $arrData['ipId'],
                                                        $arrData['kode'],
                                                        $arrData['nama'],
                                                        $arrData['value'],
                                                        $this->mUserId,
                                                        $arrData['dataId']
                                                    ));
            $this->EndTrans($result);
        }            
        return $result;
        
    }
    
    public function Delete($id)
    {
        if(!empty($id)){
            $this->StartTrans();
            $query = sprintf($this->mSqlQueries['delete'],$id);
            $result = $this->Execute($query,array());
            $this->EndTrans($result);
        }               
        return $result; 
    }
}