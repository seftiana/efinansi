<?php
#doc
#    classname:    Misi
#    scope:        PUBLIC
#
#/doc

class Misi extends Database
{
    #    internal variables
    protected $mSqlFile = 'module/misi/business/misi.sql.php';
    #    Constructor
    function __construct ($connectionNumber = 0)
    {
        # code...
        parent::__construct($connectionNumber);
    }
    
    public function GetDataVisi($kode,$offset,$limit)
    {
        $return     = $this->Open(
            $this->mSqlQueries['get_data_visi'],
            array(
                '%'.$kode.'%',
                '%'.$kode.'%',
                $offset,
                $limit
            )
        );
        
        return $return;
    }
    
    public function CountDataVisi($kode)
    {
        $return     = $this->Open(
            $this->mSqlQueries['count_data_visi'],
            array(
                '%'.$kode.'%',
                '%'.$kode.'%'
            )
        );
        
        return $return[0]['count'];
    }
    
    public function DoInsertData($visi_id,$kode,$nama,$user_id)
    {
        $result     = $this->Execute(
            $this->mSqlQueries['do_insert_data'],
            array(
                $visi_id,
                $kode,
                $nama,
                $user_id
            )
        );
        
        if ($result)
        {
            # code...
            return true;
        }
        else
        {
            # code...
            return $this->GetLastError();
        }
    }
    
    public function GetData($renstra_id,$kode,$offset,$limit)
    {
        if($renstra_id != ''){
            $str    = ' AND ';
        }else{
            $str    = ' OR ';
        }
        
        $sql        = sprintf($this->mSqlQueries['get_data'], '%s','%s',$str,'%s','%d','%d');
        $return     = $this->Open(
            $sql, 
            array(
                '%'.$kode.'%',
                '%'.$kode.'%',
                $renstra_id,
                $offset,
                $limit
            )
        );
        
        return $return;
    }
    
    public function GetCountData($renstra_id,$kode)
    {
        if($renstra_id != ''){
            $str    = ' AND ';
        }else{
            $str    = ' OR ';
        }
        $sql        = sprintf($this->mSqlQueries['count_data'], '%s','%s',$str,'%s');
        $return     = $this->Open(
            $sql, 
            array(
                '%'.$kode.'%',
                '%'.$kode.'%',
                $renstra_id
            )
        );
        
        return $return[0]['total'];
    }
    
    public function GetDataById($id)
    {
        $return     = $this->Open($this->mSqlQueries['get_data_id'], array($id));
        
        return $return[0];
    }
    
    public function DoUpdateData($visi_id,$kode,$nama,$user_id,$data_id)
    {
        $result     = $this->Execute(
            $this->mSqlQueries['update_data'],
            array(
                $visi_id,
                $kode,
                $nama,
                $user_id,
                $data_id
            )
        );
        
        if ($result)
        {
            # code...
            return true;
        }
        else
        {
            # code...
            return $this->GetLastError();
        }
    }
    
    function DeleteData($idDelete)
    {
        $result     = $this->Execute(
            $this->mSqlQueries['delete_data'],
            array(
                $idDelete
            )
        );
        if($result){
            return true;
        }else{
            return $this->GetLastError();
        }
        
    }
}
?>
