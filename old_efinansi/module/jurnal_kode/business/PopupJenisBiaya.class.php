<?php

require_once GTFWConfiguration::GetValue('application','docroot').
    'module/rest/business/RestDb.class.php';

class PopupJenisBiaya {
    
    private $appPembayaran  = 520;
    
    public function __construct() {}

    public function DummyJenisBiaya() {
        return array(
            array( 'jenisBiayaId' => 1,'jenisBiayaNama'  => 'Jenis Biaya Dummy 1'),
            array( 'jenisBiayaId' => 2,'jenisBiayaNama'  => 'Jenis Biaya Dummy 2'),
            array( 'jenisBiayaId' => 3,'jenisBiayaNama'  => 'Jenis Biaya Dummy 3'),
            array( 'jenisBiayaId' => 4,'jenisBiayaNama'  => 'Jenis Biaya Dummy 4'),
            array( 'jenisBiayaId' => 5,'jenisBiayaNama'  => 'Jenis Biaya Dummy 5')
        );
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