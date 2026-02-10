<?php

/**
 * class PembukuanHistory
 * untuk menyiapkan data pembukuan tahun sebelum nya
 * 
 * @since 9 Mei 2017
 * 
 * @package tahun_pembukuan
 * 
 * diturunkan dari eanggaran pertamina
 * 
 * @analyzed tim rms-1 ecampuz
 * @author noor hadi <noor.hadi@gamatecno.com>
 * 
 * 
 * @copyright (c) 2011 - 2021, Gamatechno Indonesia
 * 
 */

class PembukuanHistory extends Database {

    protected $mSqlFile;    
    //setting jumlah sub account
    protected $mSubAccJml;
    
    //nilai default unit tertinggi
    private $_mUnitKerjaId = 1;
    //array untuk data Pembukuan History
    private $_mNecaraSaldoTahunSebelumnya = array();
    //tahun pembukuan id sebelumnya
    private $_mTppIdSebelumnya = null;

    public function __construct($connectionNumber = 0) {
        $this->mSqlFile = 'module/tahun_pembukuan/business/pembukuan_history.sql.php';
        parent::__construct($connectionNumber);        
        $this->mSubAccJml = ((GTFWConfiguration::GetValue('application', 'subAccJml') == NULL) ? 7 : GTFWConfiguration::GetValue('application', 'subAccJml'));
        
        $this->_mTppIdSebelumnya = $this->_getTppIdPeriodeSebelumnya();
        $this->_mUnitKerjaId =  1 ;
    }

    private function _getTppIdPeriodeSebelumnya() {
        $periodeSebelumnya = $this->Open($this->mSqlQueries['get_tpp_id_periode_sebelumnya'], array());
        return $periodeSebelumnya[0]['tpp_id'];
    }

    private function _getTppIdPeriodeSebelumnyaById($tppId) {
        $periodeSebelumnya = $this->Open($this->mSqlQueries['get_tpp_id_periode_sebelumnya_by_id'], array($tppId));
        return $periodeSebelumnya[0]['tpp_id'];
    }
    
    /**
     * getTppIdPeriodeSebelumnya
     * untuk mendapatkan periode tahun pembukuan id tahun sebelumnya
     * @return int
     */
    public function getTppIdPeriodeSebelumnya(){
        return $this->_getTppIdPeriodeSebelumnya();
    }
    
    public function getTppIdPeriodeSebelumnyaById($tppId){
        return $this->_getTppIdPeriodeSebelumnyaById($tppId);
    }
    /**
     * prepareNecaraSaldoTahunSebelumnya
     * untuk mengambil data neraca saldo akhir tahun (tahun sebelum nya)
     * 
     * @access public
     * @return void
     */
    public function prepareNecaraSaldoTahunSebelumnya() {
        $saldoAkhirTahun = $this->Open($this->mSqlQueries['get_neraca_saldo_tahun_sebelumnya'], array(
            $this->_mUnitKerjaId,
            $this->_mTppIdSebelumnya
        ));
        if (!empty($saldoAkhirTahun)) {
            foreach ($saldoAkhirTahun as $saldoAkhirTahunItem  ) {                
                $this->_mNecaraSaldoTahunSebelumnya[$saldoAkhirTahunItem['coa_id']]['saldo'] = $saldoAkhirTahunItem['saldo_akhir'];
            }
        }        
    }

    /**
     * prepareNecaraSaldoTahunSebelumnyaByCoaId
     * untuk mengambil data neraca saldo akhir tahun (tahun sebelum nya)
     * 
     * @access public
     * @return void
     */
    public function prepareNecaraSaldoTahunSebelumnyaByCoaId($coaId) {
        $saldoAkhirTahun = $this->Open($this->mSqlQueries['get_neraca_saldo_tahun_sebelumnya_by_coa_id'], array(
            $this->_mUnitKerjaId,
            $this->_mTppIdSebelumnya,
            $coaId
        ));
        if (!empty($saldoAkhirTahun)) {
            $jmlSubAcc = $this->mSubAccJml;
            foreach ($saldoAkhirTahun as $saldoAkhirTahunItem  ) {
                $subAcc = '';
                if($jmlSubAcc > 0) {
                    $getSubAcc = $saldoAkhirTahunItem['sub_acc'];
                    $subAcc = $saldoAkhirTahunItem['coa_id'].'-'.implode('-',array_slice(explode('-',$getSubAcc),0,($jmlSubAcc)));
                } else {
                    $subAcc = $saldoAkhirTahunItem['coa_id'];
                }
                
                $this->_mNecaraSaldoTahunSebelumnya[$subAcc]['saldo']  = $saldoAkhirTahunItem['saldo_akhir'];
            }
        }        
    }
    /**
     * getNecaraSaldoTahunSebelumnya
     * get data laba rugi yang siap di posting ke buku besar
     * @return array()
     */
    public function getNecaraSaldoTahunSebelumnya() {
        return $this->_mNecaraSaldoTahunSebelumnya;
    }
    
}

?>
