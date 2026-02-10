<?php
#doc
#    classname:    Visi
#    scope:        PUBLIC
#
#/doc

class Visi extends Database
{
    #    internal variables
    protected $mSqlFile = 'module/visi/business/visi.sql.php';
    #    Constructor
    function __construct ($connectionNumber = 0)
    {
        # code...
        parent::__construct($connectionNumber);
    }
    
    public function DoInsertVisi($renstra_id,$kode,$nama,$user_id)
    {
        $result     = $this->Execute(
            $this->mSqlQueries['do_insert_visi'],
            array(
                $renstra_id,
                $kode,
                $nama,
                $user_id
            )
        );
        if($result){
            return true;
        }else{
            return $this->GetLastError();
        }
    }
    
    public function GetData($kode,$offset,$limit)
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
    
    public function CountData($kode)
    {
        $return     = $this->Open(
            $this->mSqlQueries['count_data'],
            array(
                '%'.$kode.'%',
                '%'.$kode.'%'
            )
        );
        
        return $return[0]['count'];
    }
    
    public function GetDataId($id)
    {
        $return     = $this->Open(
            $this->mSqlQueries['get_data_id'],
            array(
                $id
            )
        );
        
        return $return[0];
    }
    
    public function DoUpdateData($renstra_id,$kode,$nama,$user_id,$id)
    {
        $result     = $this->Execute(
            $this->mSqlQueries['update_data'],
            array(
                $renstra_id,
                $kode,
                $nama,
                $user_id,
                $id
            )
        );
        
        if($result){
            return true;
        }else{
            return $this->GetLastError();
        }
    }
    
    public function DeleteData($id)
    {
        $result     = $this->Execute(
            $this->mSqlQueries['delete_data'],
            array(
                $id
            )
        );
        
        if($result){
            return true;
        }else{
            return $this->GetLastError();
        }
    }
    
    public function CheckVisibility($kode,$renstra)
    {
        $result     = $this->Open(
            $this->mSqlQueries['check_visibility'], 
            array(
                $kode,
                $renstra
            )
        );
        
        return $result[0]['count'];
    }
}
?>
