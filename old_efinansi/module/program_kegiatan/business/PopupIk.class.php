<?php
class PopupIk extends Database{
    protected $mSqlFile = 'module/program_kegiatan/business/popupik.sql.php';
    
    function __construct($connectionNumber = 0){
        parent::__construct($connectionNumber);
    }
    
    function Count($kode){
        $return = $this->Open($this->mSqlQueries['count_data'], array('%'.$kode.'%','%'.$kode.'%'));
        
        return $return[0]['total'];
    }
    
    function GetData($kode =''){
        $return = $this->Open($this->mSqlQueries['get_data'], array('%'.$kode.'%','%'.$kode.'%'));
        
        return $return;
    }
}
?>