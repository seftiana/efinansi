<?php


class GetCoaJenisBiaya extends Database {

    protected $mSqlFile = 'module/finansi_transaksi_penerimaan_bank_mhs/business/get_coa_jenis_biaya.sql.php';

    public function __construct($connectionNumber = 0) {
        parent::__construct($connectionNumber);
    }

    
    public function GetCoaDepositMasuk() {
        $coaDepMasuk = array();
        $dataCoaDepMasuk =  $this->Open($this->mSqlQueries['get_coa_deposit_masuk'], array());
        if(!empty($dataCoaDepMasuk)) {     
                $v = $dataCoaDepMasuk[0];
                $coaDepMasuk['coa_id'] = $v['coa_id'];
                $coaDepMasuk['coa_kode'] = $v['coa_kode'];
                $coaDepMasuk['coa_nama'] = $v['coa_nama'];
                $coaDepMasuk['coa_is_d_pos'] = $v['coa_is_d_pos'];
                $coaDepMasuk['coa_dk'] = $v['dk'];
        }
        return $coaDepMasuk;
    }
    
    public function GetData($jbIds = array()) {        
        return $this->Open($this->mSqlQueries['get_coa_jenis_biaya'], array($jbIds));
    }
    
    public function GetArray($jbIds) {
        $arrayCoaJenisBiaya = null;
        $jbIds = (!empty($jbIds) ? $jbIds : null);
        $dataCoa = $this->GetData($jbIds);
        if(!empty($dataCoa)) {
            /*foreach($dataCoa as $jb) {
                $arrayCoaJenisBiaya[$jb['jb_id']]['jb_pembayaran_coa_id'] = $jb['coa_id'];
                $arrayCoaJenisBiaya[$jb['jb_id']]['jb_pembayaran_coa_kode'] = $jb['coa_kode'];
                $arrayCoaJenisBiaya[$jb['jb_id']]['jb_pembayaran_coa_nama'] = $jb['coa_nama'];
                $arrayCoaJenisBiaya[$jb['jb_id']]['jb_pembayaran_dk'] = $jb['dk'];
            }*/
			//echo'<pre>';print_r($dataCoa);echo'<pre>';
            return $dataCoa;
        } else {
            return array();
        }
    }
}

?>