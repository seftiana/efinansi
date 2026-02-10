<?php

class Coa extends Database {

    protected $mSqlFile = 'module/coa/business/coa.sql.php';
    
    private $_mCoaDepMasuk = NULL;

    public function __construct($connectionNumber = 0) {
        parent::__construct($connectionNumber);
        
        //untuk mengecek dao deposit masuk
        $this->GetGenerateCoaDepMasuk();
    }

    function ResetCoaKodeSistem() {
        # initiate coaKodeSistem with NULL
        $this->Execute($this->mSqlQueries['update_coa_kodesistem_null']);

        # update one by one coaKodeSistem
        $coaArray = $this->Open($this->mSqlQueries['get_list_coa']);
        foreach ($coaArray as $coa) {
            $kodeSistem = $this->GetGenerateKodeSistem($coa['coaParentAkun']);
            $result = $this->Execute($this->mSqlQueries['update_coa_kodesistem'], array($kodeSistem, $coa['coaId']));
            if ($result == false) {
                break;
            }
        }
        return $result;
    }

    function GetGenerateKodeSistem($coaParentAkun = 0) {
        $result = $this->Open($this->mSqlQueries['generate_kode_sistem'], array(
            $coaParentAkun, $coaParentAkun, $coaParentAkun,
            $coaParentAkun, $coaParentAkun, $coaParentAkun,
            $coaParentAkun
        ));
        return $result[0]['kodeSistem'];
    }

    public function GetComboCoa() {
        return $this->Open($this->mSqlQueries['get_combo_coa'], array());
    }

    public function GetListCoa($param) {
        extract($param);
        //$query = $this->GetQueryKeren($this->mSqlQueries['get_list_coa'], array());
        return $this->Open($this->mSqlQueries['get_list_coa'], array(
            $unit_kerja,
            (int)($unit_kerja == '' || $unit_kerja == 'all') ? 1 : 0,
        ));
    }

    public function GetCoaFromId($params) {
        return $this->Open($this->mSqlQueries['get_coa_from_id'], array($params));
    }

    public function GetCoaTipeRef() {
        return $this->Open($this->mSqlQueries['get_coa_tipe_ref'], array());
    }

    public function GetCoaTipeRefById($param) {
        return $this->Open($this->mSqlQueries['get_coa_tipe_ref_by_id'], array($param));
    }

    public function GetCoaTipeRefByArrayCrashId($arrCrashId) {
        $strCrashId = @implode("', '", $arrCrashId);
        return $this->Open($this->mSqlQueries['get_coa_tipe_ref_by_array_crash_id'], array(
                    $strCrashId
        ));
    }

    public function GetCoaTipeCoaByCoaId($params) {
        $ret = $this->Open($this->mSqlQueries['get_coa_tipe_coa_by_coa_id'], array(
            $params
        ));

        return $ret;
    }

    public function GetCoaFromNamaKodeCount() { 
        $result = $this->Open($this->mSqlQueries['get_coa_from_nama_kode_count'], array());
        return $result[0]['total'];
    }

    public function GetCoaFromNamaKode($params, $start = 0, $limit = 40) {
        extract($params);
        $arg = array(
            "%".$input."%",
            "%".$input."%",
            (($coa_is_kas =='1') ? 0 : 1),
            (($coa_is_laba_rugi =='1') ? 0 : 1),
            (($coa_is_laba_rugi_at =='1') ? 0 : 1),
            $unit_kerja,
            (int)($unit_kerja == '' || $unit_kerja == 'all') ? 1 : 0,
            $start,
            $limit
        );
        $result = $this->Open($this->mSqlQueries['get_coa_from_nama_kode'], $arg);

        return $result;
    }

    public function GetComboUnitKerja() {
        //	$query = $this->GetQueryKeren($this->mSqlQueries['get_combo_unit_kerja'], array());

        return $this->Open($this->mSqlQueries['get_combo_unit_kerja'], array());
    }

    public function GetCoaUnitKerjaByCoaId($id){
        return $this->Open($this->mSqlQueries['get_coa_unit_kerja_by_coa_id'],array($id));
    }

    public function InsertCoa($params, $arr_tipe_coa_id = "", $unit = array()) {
        $this->StartTrans();
        $result = $this->Execute($this->mSqlQueries['insert_coa'], $params);

        $last_id = $this->LastInsertId();

        if ($result === true AND ( !empty($arr_tipe_coa_id))) {
            $result = $this->InsertCoaTipeCoa($arr_tipe_coa_id, $last_id);
        }

        $resultUnit = true;

        if($result === true && !empty($unit)){
            foreach($unit as $value){
                $resultUnit &= $this->Execute($this->mSqlQueries['insert_coa_unit_kerja'],array(
                    $last_id,
                    $value['id']
                ));
            }
        }
        
        $ret = $result && $resultUnit;

        $this->EndTrans($ret);

        return (bool)$ret;
    }

    public function InsertCoaTipeCoa($arr_tipe_coa_id, $coaId) {
        foreach ($arr_tipe_coa_id as $kunci => $value) {
            $arr_param[] = "('%s', '%s')";
            $arrParam[] = $coaId;
            $arrParam[] = $value;
        }
        $str_param = implode(", ", $arr_param);

        $sql = sprintf($this->mSqlQueries['insert_coa_tipe_coa'], $str_param);

        $return = $this->Execute($sql, $arrParam);
        return $return;
    }

    public function UpdateCoa($params, $tipe, $unit = array()) {

        $this->StartTrans();
        $result = $this->Execute($this->mSqlQueries['update_coa'], $params);

        $coaId = end($params);

        if ($result) {
            if (!empty($tipe) && is_array($tipe)) {
                $result = $this->UpdateCoaTipeCoa($tipe, $params[10]);
            } else {
                $result = $this->DeleteCoaTipeCoaByCoaId($params[10]);
            }
        }

        $resultUnit = true;
        $resultUnit &= $this->Execute($this->mSqlQueries['delete_coa_unit_kerja'],array($coaId));

        if($result === true && !empty($unit)){
            foreach($unit as $value){
                $resultUnit &= $this->Execute($this->mSqlQueries['insert_coa_unit_kerja'],array(
                    $coaId,
                    $value['id']
                ));
            }
        }
        
        $result &= $resultUnit;

        $this->EndTrans($result);
        return (bool)$result;
    }

    public function UpdateCoaTipeCoa($arr_tipe_coa_id, $coaId) {
        foreach ($arr_tipe_coa_id as $kunci => $value) {
            $arr_param[] = "('%s', '%s')";
            $arrParam[] = $coaId;
            $arrParam[] = $value;
            $arrNotDelete[] = $value;
        }
        $str_param = implode(", ", $arr_param);

        $strNotDelete = implode("','", $arrNotDelete);

        /* Execute Query */
        $result = $this->Execute($this->mSqlQueries['delete_unused_coa_tipe_coa'], array($coaId, $strNotDelete));

        $sql = sprintf($this->mSqlQueries['insert_coa_tipe_coa'], $str_param);

        $return = $this->Execute($sql, $arrParam);


        return $return;
    }

    public function DeleteCoaTipeCoa($arr, $coaId) {
        $str_ctr = implode("', '", $arr);

        return $this->Execute($this->mSqlQueries['delete_coa_tipe_coa'], array(
                    $str_ctr,
                    $coaId
        ));
    }

    public function DeleteCoaTipeCoaByCoaId($coaId) {
        return $this->Execute($this->mSqlQueries['delete_coa_tipe_coa'], array($coaId));
    }

    public function GetListCoaExcel($filter) {
        extract($filter);
        //$query = $this->GetQueryKeren($this->mSqlQueries['get_list_coa'], array());

        return $this->Open($this->mSqlQueries['get_list_coa_2'], array(
            $unit_kerja,
            (int)($unit_kerja == '' || $unit_kerja == 'all') ? 1 : 0
        ));
    }

    protected function GetGenerateCoaDepMasuk() {
        ///$this->SetDebugOn();
        //hanya satu coa deposit masuk
        $num =  $this->Open($this->mSqlQueries['get_num_coa_dep_masuk'], array());
        if(!empty($num)) {
            $this->_mCoaDepMasuk['num'] = sizeof($num);
            $this->_mCoaDepMasuk['c_id'] = $num[0]['c_id'];
            $this->_mCoaDepMasuk['c_kode'] = $num[0]['c_kode'];
            $this->_mCoaDepMasuk['c_nama'] = $num[0]['c_nama'];
        } else {
            $this->_mCoaDepMasuk['num'] = 0;
        }
    }
    
    public function GetNumCoaDepositMasuk() {
        return (int) $this->_mCoaDepMasuk['num'];
    }
    
    public function GetCoaDepositMasuk() {
        return $this->_mCoaDepMasuk;
    }
    
    public function GetNumCoaLR($idLr) {
        ///$this->SetDebugOn();
        $num =  $this->Open($this->mSqlQueries['get_num_coa_lr'], array($idLr));
        return (int) $num[0]['c_lr'];
    }
    
    public function GetNumCoaLRAT($idLrAt) {
        $num =  $this->Open($this->mSqlQueries['get_num_coa_lr_at'], array($idLrAt));
        return  (int) $num[0]['c_lr_at'];
        
    }

    public function GetUnitKerjaRef() {
        return $this->Open($this->mSqlQueries['get_unit_kerja_ref'], array());
    }
    
    public function createTree(&$list, $parent){
        $tree = array();
        foreach ($parent as $k=>$l){
            if(isset($list[$l['id']])){
                $l['item'] = $this->createTree($list, $list[$l['id']]);
            }
            $tree[] = $l;
        }
        return $tree;
    }
}
