<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/rest/business/RestDb.class.php';

class CoaJenisBiaya extends Database {

    protected $mSqlFile = 'module/finansi_coa_jenis_biaya/business/coa_jenis_biaya.sql.php';
    private $appPembayaran = 520;

    function __construct($connectionNumber = 0) {
        parent::__construct($connectionNumber);
        //$this->mrDbEngine->debug = 1;
    }

    public function GetDataJenisBiaya() {
        $module = 'services';
        $subModule = 'ReferensiJenisBiaya';
        $action = $type = 'rest';

        RestDb::Instance()->setApplication($this->appPembayaran);
        RestDb::Instance()->setModule($module);
        RestDb::Instance()->setSubModule($subModule);
        RestDb::Instance()->setAction($action);
        RestDb::Instance()->setType($type);
        $return = RestDb::Instance()->SendNull('post');
        $result['status'] = $return['status'];
        $result['data_list'] = $return['data'];

        if (empty($result['data_list'])) {
            $result['data_list'] = array(); //$this->DummyJenisBiaya();
        }
        return $result;
    }

    public function getCoaJenisBiaya() {
        return $this->Open($this->mSqlQueries['get_coa_jenis_biaya'], array());
    }

    function GetData($offset, $limit, $nama = '') {
        return $this->Open($this->mSqlQueries['get_data'], array(
                    '%' . $nama . '%',
                    '%' . $nama . '%',
                    $offset,
                    $limit
        ));
    }

    function GetCountData($nama) {
        $result = $this->Open($this->mSqlQueries['get_count_data'], array(
            '%' . $nama . '%',
            '%' . $nama . '%'
        ));

        if (!$result) {
            return 0;
        } else {
            return $result[0]['total'];
        }
    }

    function GetDataById($id) {
        $result = $this->Open($this->mSqlQueries['get_data_by_id'], array($id));
        return $result;
    }

//===DO==

    function DoAddData($params = array()) {
        $result = $this->Execute($this->mSqlQueries['add_coa_jenis_biaya'], array(
            $params['jenis_biaya_id'], 
            $params['jenis_biaya_nama'], 
            $params['jenis_biaya_pembayaran_coa_id'],
            $params['jenis_biaya_pembayaran_coa_dk'],
            $params['jenis_biaya_potongan_coa_id'],
            $params['jenis_biaya_potongan_coa_dk'],
            $params['jenis_biaya_deposit_coa_id'],
            $params['jenis_biaya_deposit_coa_dk'],
            $params['jenis_biaya_piutang_coa_id'],
            $params['jenis_biaya_piutang_coa_dk']
        ));

        return $result;
    }

    function DoUpdateData($params = array()) {
        $result = $this->Execute($this->mSqlQueries['update_coa_jenis_biaya'], array(
            $params['jenis_biaya_id'], 
            $params['jenis_biaya_nama'], 
            $params['jenis_biaya_pembayaran_coa_id'],
            $params['jenis_biaya_pembayaran_coa_dk'],
            $params['jenis_biaya_potongan_coa_id'],
            $params['jenis_biaya_potongan_coa_dk'],
            $params['jenis_biaya_deposit_coa_id'],
            $params['jenis_biaya_deposit_coa_dk'],
            $params['jenis_biaya_piutang_coa_id'],
            $params['jenis_biaya_piutang_coa_dk'],
            $params['dataId']
        ));


        return $result;
    }

    function DoDeleteData($id) {
        $result = $this->Execute($this->mSqlQueries['do_delete_data_by_id'], array($id));
        return $result;
    }

}

?>