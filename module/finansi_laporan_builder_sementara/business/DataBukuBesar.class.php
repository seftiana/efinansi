<?php

/**
 * Class DataBukuBesar
 * class ini untuk mengumpukan data data keuangan melaui buku besar
 * untuk mendapatkan nominal mutasi debet kredit serta saldo awal
 * sebagai acuan untuk menyusun laporan keuanga
 * 
 * @package finansi_laporan_builder_sementara
 * 
 * @added since Agustus 2017
 * @analyzed diyah fajar <dyah@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatecno.com>
 * 
 * 
 * @copyright (c) 2009 - 2017, Gamatechno Indonesia
 * 
 */

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
    'module/tahun_pembukuan/business/PembukuanHistory.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
    'module/tahun_pembukuan/business/TahunPembukuanPeriode.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
    'module/finansi_laporan_builder/business/AttributeUnit.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/finansi_laporan_builder_sementara/business/DataJurnal.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/finansi_laporan_builder_sementara/business/LabaRugi.class.php';

class DataBukuBesar extends Database {

    protected $mSqlFile = 'module/finansi_laporan_builder_sementara/business/data_buku_besar.sql.php';
    
    private $_mTppIdAktif;
    private $_mTppAktifTanggalAwal;
    private $_mTppAktifTanggalAkhir;
    private $_mPembukuanPeriode;

    private $_mAUnit;

    /**
     * get saldo before posting
     */
    private $_mDataJurnalObj;
    private $_mLabaRugiObj;
    
    /**
     * untuk menyimpan data pembukuan tahun periode aktif
     */
    private $_mDataPembukuanAwalTahun = array();
    private $_mDataPembukuanMutasiDK = array();
    private $_mDataPembukuanMutasiD = array();
    private $_mDataPembukuanMutasiK = array();

     /**
     * untuk menyimpan data pembukuan tahun periode aktif per sub account
     */
    private $_mDataPembukuanAwalTahunSubAcc = array();
    private $_mDataPembukuanMutasiDKSubAcc = array();
    private $_mDataPembukuanMutasiDSubAcc = array();
    private $_mDataPembukuanMutasiKSubAcc = array();
    
    private $_mDataPembukuanAkhirTahunLalu = array();
    public function __construct($connectionNumber = 0) {
        parent::__construct($connectionNumber);
        $this->_mPembukuanHistObj   = new PembukuanHistory;
        $this->_mPembukuanPeriode   = new TahunPembukuanPeriode;

        $this->_mDataJurnalObj      = new DataJurnal;
        $this->_mLabaRugiObj        = new LabaRugi;
        $this->_mAUnit = new AttributeUnit;

        $this->_mTppIdSebelumnya = $this->_mPembukuanHistObj->getAllTppIdPeriodeSebelumnya();
        $periodeAktif =  $this->_mPembukuanPeriode->GetTahunPembukuanPeriodeAktif();        
        $this->_mTppIdAktif     = $periodeAktif[0]['tppId']; // id tahun aktif

        $this->_mTppAktifTanggalAwal   = $periodeAktif[0]['tppTanggalAwal']; // tanggal awal
        $this->_mTppAktifTanggalAkhir  = $periodeAktif[0]['tppTanggalAkhir']; // tanggal akhir
    }

    public function setTppIdAktif($tppId){
        $this->_mTppIdAktif = $tppId;
    }
    
    public function setTppIdTahunSebelumnya($tppId){
        $this->_mTppIdSebelumnya =  $this->_mPembukuanHistObj->getAllTppIdPeriodeSebelumnyaById($tppId);
    }
        
    public function getTahunPembukuanAktifId(){
        return $this->_mTppIdAktif;
    }

    /**
     * PrepareDataBukuBesar
     * Menyiapkan data buku besar yang akan disajikan dalam laporan keuanga
     * data saldo awal dan mutasi debet kredit di pisah untuk mengkomodasi 
     * penyajian data dalam laporan keuangan yang tidak semua 
     * kelompok laporan/coa menghendaki data di sajikan secara total(nominal Saldo Awal + mutasi)
     * 
     * 
     * @return void
     */
    public function PrepareDataBukuBesar($tanggalAwal, $tanggalAkhir, $subAccount='') {
        // get data buku besar sementara (siap posting)
        $this->_getDataBukuBesarSementara($tanggalAwal, $tanggalAkhir,$subAccount);
        $this->_hitungSaldoAkhirTahunLalu($subAccount);
    }
    
    public function getTP(){
        return $this->_mTppIdAktif;
    }

    public function _hitungSaldoAkhirTahunLalu($subAccount) {

        $bbAkhirTahunLalu = $this->open($this->mSqlQueries['get_data_buku_besar_saldo_akhir_tahun_lalu'], array(
                $this->_mTppIdAktif,
                 $subAccount.'%',
                 ($subAccount == "all" || $subAccount == "")  ? 1 : 0
            )
        );
        
        if (!empty($bbAkhirTahunLalu)) {
            foreach ($bbAkhirTahunLalu as $itemBB) {                
                $this->_mDataPembukuanAkhirTahunLalu[$itemBB['coa_id']][$itemBB['sub_acc']] = $itemBB['saldo_awal'];
            }
        }
    }
    
    /**
     * _getDataBukuBesarSementara
     * untuk menghitung saldo buku besar yang siap posting di tahun aktif
     * 
     * @param date $tanggalAwal
     * @param date $tanggalAkhir
     * 
     * @return void
     */
    private function _getDataBukuBesarSementara($tanggalAwal, $tanggalAkhir, $subAccount=''){
        
        // build Data Buku Besar His
        // hitung saldo mutasi pada jurnal approved       
        $this->_mDataJurnalObj->prepareDataJurnal($tanggalAwal, $tanggalAkhir, $subAccount);
        $dataBukuBesarHis = $this->_mDataJurnalObj->getDataBukuBesarHis();

        // build laba rugi
        // hitung laba rugi dari jurnal approved
        $dataLabaRugi = $this->_mDataJurnalObj->getDataBukuBesarHisLabaRugi();        
        $this->_mLabaRugiObj->prepareLabaRugi($dataLabaRugi);
        $dataBBhisLabaRugi = $this->_mLabaRugiObj->getBBHisLabaRugi();

        $bbAwalTahun = $this->open($this->mSqlQueries['get_data_buku_besar_saldo_awal_tahun'], array(
            $this->_mTppIdAktif,
                $subAccount.'%',
                ($subAccount == "all" || $subAccount == "")  ? 1 : 0
            )
        );

        if (!empty($bbAwalTahun)) {
            foreach ($bbAwalTahun as $itemBB) {              
                $this->_mDataPembukuanAwalTahun[$itemBB['coa_id']] = $itemBB['saldo_awal'];
                $this->_mDataPembukuanAwalTahunSubAcc[$itemBB['coa_id']][$itemBB['sub_acc']] = $itemBB['saldo_awal'];
            }
        }
        
        //get data buku besar mutasi saldo (DK)
        $bbMutasiDK = $this->open($this->mSqlQueries['get_data_buku_besar_saldo_mutasi_sudah_posting'], array(
                $this->_mTppIdAktif,
                date('Y-m-d',  strtotime($tanggalAwal)),
                date('Y-m-d',  strtotime($tanggalAkhir)),
                $subAccount.'%',
                ($subAccount == "all" || $subAccount == "")  ? 1 : 0
            )
        );

        /**
         * merger data
         * 
         * $dataBukuBesarHis = data jurnal approved / saldo mutasi (belum posting)
         * $dataBBhisLabaRugi = data laba rugi (belum posting)
         * $bbMutasiDK = data buku besar his (sudah posting)
         */
        $_mergeDataSaldoMutasi = array_merge($dataBukuBesarHis, $dataBBhisLabaRugi, $bbMutasiDK);

        if (!empty($_mergeDataSaldoMutasi)) {
            foreach ($_mergeDataSaldoMutasi as $itemBB) {
                $this->_mDataPembukuanMutasiD[$itemBB['coa_id']]    += $itemBB['saldo_mutasi_d']; // saldo mutasi debet
                $this->_mDataPembukuanMutasiK[$itemBB['coa_id']]    += $itemBB['saldo_mutasi_d']; // saldo mutasi kredit
                $this->_mDataPembukuanMutasiDK[$itemBB['coa_id']]   += $itemBB['saldo_mutasi_dk']; // saldo mutasi debet kredit

                $this->_mDataPembukuanMutasiDKSubAcc[$itemBB['coa_id']][$itemBB['sub_acc']] += $itemBB['saldo_mutasi_dk'];
                $this->_mDataPembukuanMutasiDSubAcc[$itemBB['coa_id']][$itemBB['sub_acc']] += $itemBB['saldo_mutasi_d'];
                $this->_mDataPembukuanMutasiKSubAcc[$itemBB['coa_id']][$itemBB['sub_acc']] += $itemBB['saldo_mutasi_k'];
            }
        }
    }

    /**
     * getSaldoAkhirTahunLalu
     * untuk mendapatkan nilai saldo akhir tahun lalu (saldo awal tahun ini dari saldo akhir tahun lalu)
     * 
     * @param number $tppId id tahun pembukuan lalu
     * @param number $coaId id coa
     * @return number
     */
    public function getSaldoAkhirTahunLalu($coaId,$subAcc) {
        $nominal = 0;
        
        if(!array_key_exists($coaId,  $this->_mDataPembukuanAwalTahun)){
            if(array_key_exists($coaId,  $this->_mDataPembukuanAkhirTahunLalu)){
                $nominal = $this->_mDataPembukuanAkhirTahunLalu[$coaId][$subAcc];
            }
        }
        
        return $nominal;
    }

    /**
     * getSaldoAwalTahun
     * untuk mendapatkan nilai saldo awal tahun
     * 
     * @param number $tppId id tahun pembukuan 
     * @param number $coaId id coa
     * @return number
     */
    public function getSaldoAwaltahun($coaId) {
        $nominal = 0;
        
        if(array_key_exists($coaId,  $this->_mDataPembukuanAwalTahun)){
            $nominal = $this->_mDataPembukuanAwalTahun[$coaId];
        }
        
        return $nominal;
    }

    /**
     * getSaldoMutasiDK
     * untuk mendapatkan nominal mutasi debet/kredit
     * 
     * @param number $tppId id tahun pembukuan 
     * @param number $coaId id coa
     * @param string $status default kosong (D untuk Debet dan K untuk kredit) 
     * kosong mengambil semua
     * @return number
     */
    public function getSaldoMutasiDK($coaId,$status = '') {
        $nominal = 0;
        if($status == 'D') {
            if(array_key_exists($coaId,  $this->_mDataPembukuanMutasiD)){
                $nominal = $this->_mDataPembukuanMutasiD[$coaId];
            }
        }elseif($status == 'K') {
            if(array_key_exists($coaId,  $this->_mDataPembukuanMutasiK)){
                $nominal = $this->_mDataPembukuanMutasiK[$coaId];
            }
        } else {            
            if(array_key_exists($coaId,  $this->_mDataPembukuanMutasiDK)){
                $nominal = $this->_mDataPembukuanMutasiDK[$coaId];
            }   
        }     
        return $nominal;
    }

    public function getSaldoAwaltahunSubAcc($coaId,$subAcc) {
        $nominal = 0;
        
        if(array_key_exists($coaId,  $this->_mDataPembukuanAwalTahunSubAcc)){            
            $nominal = $this->_mDataPembukuanAwalTahunSubAcc[$coaId][$subAcc];            
        }       
        
        return $nominal;
    }

    /**
     * getSaldoMutasiDKSubAcc
     * untuk mendapatkan nominal mutasi debet/kredit
     *
     * @param number $coaId id coa
     * @param number $subAcc sub account
     * @param string $status default kosong (D untuk Debet dan K untuk kredit) 
     * kosong mengambil semua
     * @return number
     */
    public function getSaldoMutasiDKSubAcc($coaId,$subAcc,$status = '') {
        $nominal = 0;
        if($status == 'D') {
            if(array_key_exists($coaId,  $this->_mDataPembukuanMutasiDSubAcc)){                
                $nominal = $this->_mDataPembukuanMutasiDSubAcc[$coaId][$subAcc];
            }
        }elseif($status == 'K') {
            if(array_key_exists($coaId,  $this->_mDataPembukuanMutasiKSubAcc)){
                $nominal = $this->_mDataPembukuanMutasiKSubAcc[$coaId][$subAcc];
            }
        } else {            
            if(array_key_exists($coaId,  $this->_mDataPembukuanMutasiDKSubAcc)){
                $nominal = $this->_mDataPembukuanMutasiDKSubAcc[$coaId][$subAcc];
            }   
        }     
        return $nominal;
    }
    
}
?>
