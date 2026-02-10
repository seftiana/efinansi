<?php

/**
 * 
 * class Tujuan
 * @package tujuan
 * @subpackage business
 * @filename Tujuan.class.php
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * @since 2 Agustus 2012
 * 
 */
 
class Tujuan extends Database
{
    
    protected $mSqlFile = 'module/tujuan/business/tujuan.sql.php';
    
    
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
    
    public function GetDataById($id)
    {
        $return  = $this->Open(
							$this->mSqlQueries['get_data_by_id'], 
							array($id)
					);

        return $return[0];
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
    
    public function Add($kode,$nama,$user_id)
    {
		$this->StartTrans();
        $result = $this->Execute(
								$this->mSqlQueries['add'],
								array(
									$kode,
									$nama,
									$user_id
								)
				);
        $this->EndTrans($result);
		return $result;
    }
    
    public function Update($kode,$nama,$user_id,$data_id)
    {
		$this->StartTrans();
        $result = $this->Execute(
								$this->mSqlQueries['update'],
								array(
									$kode,
									$nama,
									$user_id,
									$data_id
								)
					);
		 $this->EndTrans($result);
		return $result;
    }
    
    /**
    public function Delete($data_id)
    {
        $result = $this->Execute(
								$this->mSqlQueries['delete'],
							array($data_id)
					);
        
        if ($result){
            return true;
        } else {
            return $this->GetLastError();
        }
    }
	*/
	
	public function Delete($data_id)
	{
		$this->StartTrans();
		$result = $this->Execute($this->mSqlQueries['delete'], array($data_id));
		$this->EndTrans($result);
		return $result;
	}
	
	public function DeleteByArrayId($arrId)
	{
		$id = implode("', '", $arrId);
		$this->StartTrans();
		$result = $this->Execute($this->mSqlQueries['delete'], array($id));
		$this->EndTrans($result);
		return $result;
	}    
}
