<?php
#doc
#    classname:    Sasaran
#    scope:        PUBLIC
#
#/doc

class Sasaran extends Database
{
    #    internal variables
    protected $mSqlFile = 'module/sasaran/business/sasaran.sql.php';
    #    Constructor
    function __construct ($connectionNumber = 0)
    {
        # code...
        parent::__construct($connectionNumber);
    }
    
    function GetData($kode,$offset,$limit)
    {
        $return     = $this->Open(
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
    
    function GetDataById($id)
    {
        $return     = $this->Open(
            $this->mSqlQueries['get_data_by_id'], 
            array(
                $id
            )
        );
        //print_r($return);
        return $return[0];
    }
    
    function CountData($kode)
    {
        $return     = $this->Open(
            $this->mSqlQueries['count_data'],
            array(
                '%'.$kode.'%',
                '%'.$kode.'%'
            )
        );
        
        return $return[0]['total'];
    }
    
    function InsertIntoSasaran($kode,$nama,$tujuan_id,$user_id)
    {
        $result     = $this->Execute(
            $this->mSqlQueries['insert_into_sasaran'],
            array(
				$tujuan_id,
                $kode,
                $nama,
                $user_id
            )
        );
        
        if ($result)
        {
            return true;
        }
        else
        {
            return $this->GetLastError();
        }
        
    }
    
    function UpdateSasaran($kode,$nama,$tujuan_id,$user_id,$data_id)
    {
        $result     = $this->Execute(
            $this->mSqlQueries['update_sasaran'],
            array(
				$tujuan_id,
                $kode,
                $nama,
                $user_id,
                $data_id
            )
        );
        
        if ($result)
        {
            return true;
        }
        else
        {
            return $this->GetLastError();
        }
        
    }
    
    /**
     * old method
    function DeleteSasaran($data_id)
    {
        $result     = $this->Execute(
            $this->mSqlQueries['delete_sasaran'],
            array(
                $data_id
            )
        );
        
        if ($result)
        {
            return true;
        }
        else
        {
            return $this->GetLastError();
        }
        
    }
    */

	/**
	 * new method
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
	
	/**
	 * end
	 */   
}
