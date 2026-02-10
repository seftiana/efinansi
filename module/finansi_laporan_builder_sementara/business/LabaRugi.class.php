<?php

/**
 * class LabaRugi
 * untuk menyiapkan koleksi data laba rugi dari jurnal yang belum diposting
 * ke buku besar / buku besar histori
 * 
 * pecahaan prosess dari class AppPosting
 * @since 4 Mei 2017
 * 
 * @package posting
 * 
 * @analyzed dyah fajar n <dyah@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatecno.com>
 * 
 * 
 * cara menggunakan class ini :
 * 1. Definisikan class
 * 2. siapkan data array laba rugi dari buku besar
 * 3. panggil method prepareLabaRugi($dataLabaRugi)
 *    untuk menyiapkan data LR berupa array
 * 4. panggil method getBBLabaRugi() 
 *    untuk mendapatkan data LR yang siap di posting ke buku besar
 * 5. panggil method getBBHisLabaRugi() 
 *    untuk mendapatkan data LR yang siap di posting ke buku besar history
 * 
 * @copyright (c) 2011 - 2017, Gamatechno Indonesia
 * 
 */

class LabaRugi extends Database {

    protected $mSqlFile;
    
    //array untuk data saldo laba rugi
    private $_mSaldoLabaRugi = array();
    
    //array untuk data LR yang akan di posting ke buku besar
    private $_mBBLabaRugi = array();
    
    //array untuk data LR yang akan di posting ke buku besar his
    private $_mBBHisLabaRugi = array();

    public function __construct($connectionNumber = 0) {
        $this->mSqlFile = 'module/finansi_laporan_builder_sementara/business/laba_rugi.sql.php';
        parent::__construct($connectionNumber);
    }

    /**
     * getCoaLabaRugi
     * untuk cek apakah coa laba rugi sudah di setting
     * @return boolean
     */
    public function getCoaLabaRugi(){
        $getCoaLabaRugi = $this->Open($this->mSqlQueries['get_coa_laba_rugi'],array());
        if(!(empty($getCoaLabaRugi))) {
            return $getCoaLabaRugi[0];
        } else {
            return null;
        }
    }

    /**
     * prepareLabaRugi
     * untuk menyiapkan data laba rugi yang siap di posting ke buku besar / buku besar history
     * 
     * @param type $dataLabaRugi
     * @return void
     */
    public function prepareLabaRugi($dataLabaRugi) {
        $coaLR = $this->getCoaLabaRugi();

        if (!empty($dataLabaRugi)) {
            foreach($dataLabaRugi as $key => $value){                
                $this->_mBBHisLabaRugi[$key] = $value;
                
                $this->_mBBHisLabaRugi[$key]['coa_id'] = $coaLR['coa_id'];
                $this->_mBBHisLabaRugi[$key]['coa_nama'] = $coaLR['coa_nama'];
                $this->_mBBHisLabaRugi[$key]['saldo_normal'] = $coaLR['saldo_normal'];
                $this->_mBBHisLabaRugi[$key]['akun_kelompok'] = $coaLR['akun_kelompok'];

                if($value['akun_kelompok'] == 'BIAYA'){
                    $this->_mBBHisLabaRugi[$key]['saldo_mutasi_dk'] = $value['saldo_mutasi_dk'] * -1;
                }
            }
        }
    }
    
    /**
     * getBBLabaRugi
     * get data laba rugi yang siap di posting ke buku besar
     * @return array()
     */
    public function getBBLabaRugi() {
        return $this->_mBBLabaRugi;
    }
    
    /**
     * getBBHisLabaRugi
     * get data laba rugi yang siap di posting ke buku besar history
     * @return array()
     */
    public function getBBHisLabaRugi() {
        return $this->_mBBHisLabaRugi;
    }
}
?>