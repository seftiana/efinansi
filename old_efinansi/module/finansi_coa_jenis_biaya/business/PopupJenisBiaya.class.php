<?php

require_once GTFWConfiguration::GetValue('application','docroot').
    'module/rest/business/RestDb.class.php';

class PopupJenisBiaya extends Database {
    
    private $appPembayaran  = 520;
    protected $mSqlFile = 'module/finansi_coa_jenis_biaya/business/popup_jenis_biaya.sql.php';    
    
    public function __construct($connectionNumber = 0) {
        parent::__construct($connectionNumber);
        //$this->mrDbEngine->debug = 1;
    }


    public function DummyJenisBiaya() {
        return array(
            array( 'jenisBiayaId' => 1,'jenisBiayaNama'  => 'Jenis Biaya Dummy 1'),
            array( 'jenisBiayaId' => 2,'jenisBiayaNama'  => 'Jenis Biaya Dummy 2'),
            array( 'jenisBiayaId' => 3,'jenisBiayaNama'  => 'Jenis Biaya Dummy 3'),
            array( 'jenisBiayaId' => 4,'jenisBiayaNama'  => 'Jenis Biaya Dummy 4'),
            array( 'jenisBiayaId' => 5,'jenisBiayaNama'  => 'Jenis Biaya Dummy 5')
        );
    }

    public function GetJenisBiayaId() {
         $getDataJBId =  $this->Open($this->mSqlQueries['get_jenis_biaya_id'], array());
         $id = array();
         if(!empty($getDataJBId)) {
         
             foreach ($getDataJBId as $value) {
                 $id[$value['id']] = $value['id'];
             }
             return $id;
         } 
         
         return $id;
    }
    
    public function GetData() {
        $module     = 'services';
        $subModule  = 'ReferensiJenisBiaya';
        $action     = $type = 'rest';
        
        RestDb::Instance()->setApplication($this->appPembayaran);
        RestDb::Instance()->setModule($module);
        RestDb::Instance()->setSubModule($subModule);
        RestDb::Instance()->setAction($action);
        RestDb::Instance()->setType($type);
        $return     = RestDb::Instance()->SendNull('post');
        $result['status']    = $return['status'];
        $result['data_list'] = $return['data'];
        
        if(empty($result['data_list'])) {
            $result['data_list'] = array();//$this->DummyJenisBiaya();
        } 
        return $result;         
    }

    public function GetCount() {
        
    }

}

?>