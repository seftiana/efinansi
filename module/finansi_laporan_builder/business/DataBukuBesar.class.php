<?php

/**
 * Class DataBukuBesar
 * class ini untuk mengumpukan data data keuangan melaui buku besar
 * untuk mendapatkan nominal mutasi debet kredit serta saldo awal
 * sebagai acuan untuk menyusun laporan keuanga
 * 
 * @package finansi_laporan_builder
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

class DataBukuBesar extends Database {

    protected $mSqlFile = 'module/finansi_laporan_builder/business/data_buku_besar.sql.php';
    
    private $_mTppIdAktif;
    private $_mTppIdSebelumnya;    
    private $_mPembukuanHistObj;
    private $_mPembukuanPeriode;
    
    private $_mAUnit;
    
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

    /**
     * untuk menyimpan data bulan lalu
     */
    private $_mDataPembukuanAwalBulanLalu = array();
    private $_mDataPembukuanMutasiDKBulanLalu = array();
    private $_mDataPembukuanMutasiDBulanLalu = array();
    private $_mDataPembukuanMutasiKBulanLalu = array();
    
    private $_mDataPembukuanAwalBulanLaluSubAcc = array();
    private $_mDataPembukuanMutasiDKBulanLaluSubAcc = array();
    private $_mDataPembukuanMutasiDBulanLaluSubAcc = array();
    private $_mDataPembukuanMutasiKBulanLaluSubAcc = array();

    private $_mDataPembukuanMutasiDKTransBulanLaluSubAcc = array();
    private $_mDataPembukuanMutasiDTransBulanLaluSubAcc = array();
    private $_mDataPembukuanMutasiKTransBulanLaluSubAcc = array();
    
    private $_mDataPembukuanMutasiDKAkumSubAcc = array();
    private $_mDataPembukuanMutasiDAkumSubAcc = array();
    private $_mDataPembukuanMutasiKAkumSubAcc = array();
    
    private $_mDataPembukuanAkhirTahunLalu = array();
    private $_mDataPembukuanAkhirTahunBulanLalu = array();

    public function __construct($connectionNumber = 0) {
        parent::__construct($connectionNumber);
        $this->_mPembukuanHistObj = new PembukuanHistory;
        $this->_mTppIdSebelumnya = $this->_mPembukuanHistObj->getTppIdPeriodeSebelumnya();
        $this->_mPembukuanPeriode = new TahunPembukuanPeriode;
        $periodeAktif =  $this->_mPembukuanPeriode->GetTahunPembukuanPeriodeAktif();        
        $this->_mTppIdAktif = $periodeAktif[0]['tppId'];
        
        //
        $this->_mAUnit = new AttributeUnit;
    }
    
    
    public function setTppIdAktif($tppId){
        $this->_mTppIdAktif = $tppId;
    }
    
    public function setTppIdTahunSebelumnya($tppId){
        $this->_mTppIdSebelumnya =  $this->_mPembukuanHistObj->getTppIdPeriodeSebelumnyaById($tppId);
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
    public function PrepareDataBukuBesar($tanggalAwal, $tanggalAkhir,$subAccount='') {
        #Periode dari pencarian tanggal
        $_periodeSelected = $this->_mPembukuanPeriode->GetTahunPembukuanPeriodeSelected(
            $tanggalAwal, $tanggalAkhir
        );
        $_tppIdSelected      = $_periodeSelected[0]['tpp_id'];
        
        if(!is_null($_tppIdSelected)) {
            $this->_mTppIdAktif = $_tppIdSelected;
        }

        //get data buku besar tahun aktif
        $this->_getDataBukuBesarTahunAktif($tanggalAwal, $tanggalAkhir,$subAccount);

        //get data bulan lalu
        $tanggalAkhirBlnLalu = date("Y-m-t", strtotime("first day of $tanggalAkhir -1 month"));
        $tanggalAwalBlnLalu = date("Y-01-01", strtotime($tanggalAkhirBlnLalu));

        $this->_getDataBukuBesarBulanLalu($tanggalAwalBlnLalu, $tanggalAkhirBlnLalu, $subAccount);
        
        //get data buku besar tahun lalu
        // $this->_getDataBukuBesarTahunLalu($subAccount);

        $this->_hitungSaldoAkhirTahunLalu($subAccount);
        $this->_hitungSaldoAkhirTahunBulanLalu($tanggalAwalBlnLalu, $tanggalAkhirBlnLalu,$subAccount);
        
    }
    /**
     * hanya untuk debug saja
    public function getDK(){
         return $this->_mDataPembukuanMutasiDK;
    }
    public function getSado(){
         return $this->_mDataPembukuanAwalTahun;
    }
    */
    public function getTP(){
        return $this->_mTppIdAktif;
    }
    
    //saldo akhir sebelum tpp aktif
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

    //saldo akhir bulan lalu
    public function _hitungSaldoAkhirTahunBulanLalu($tanggalAwal,$tanggalAkhir,$subAccount='') {
        $tahunPembukuan = $this->_mTppIdAktif;

        $_periodeSelected = $this->_mPembukuanPeriode->GetTahunPembukuanPeriodeSelected(
            $tanggalAwal, $tanggalAkhir
        );
        $_tppIdSelected      = $_periodeSelected[0]['tpp_id'];
        
        if(!is_null($_tppIdSelected)) {
            $tahunPembukuan = $_tppIdSelected;
        }

        $bbAkhirTahunLalu = $this->open($this->mSqlQueries['get_data_buku_besar_saldo_akhir_tahun_lalu'], array(
                $tahunPembukuan,
                $subAccount.'%',
                ($subAccount == "all" || $subAccount == "")  ? 1 : 0
            )
        );
        
        if (!empty($bbAkhirTahunLalu)) {
            foreach ($bbAkhirTahunLalu as $itemBB) {                
                $this->_mDataPembukuanAkhirTahunBulanLalu[$itemBB['coa_id']][$itemBB['sub_acc']] = $itemBB['saldo_awal'];
            }
        }
    }

    /**
     * _getDataBukuBesarTahunAktif
     * untuk menyiapkan data buku besar di tahun aktif
     * 
     * @param date $tanggalAwal
     * @param date $tanggalAkhir
     * 
     * @return void
     */
    private function _getDataBukuBesarTahunAktif($tanggalAwal,$tanggalAkhir,$subAccount=''){
        $bbAwalTahun = $this->open($this->mSqlQueries['get_data_buku_besar_saldo_awal_tahun'], array(
                $this->_mTppIdAktif,
                $subAccount.'%',
                ($subAccount == "all" || $subAccount == "")  ? 1 : 0
            )
        );
        
        if (!empty($bbAwalTahun)) {
            foreach ($bbAwalTahun as $itemBB) {      
                //per coa id
                $this->_mDataPembukuanAwalTahun[$itemBB['coa_id']] += $itemBB['saldo_awal'];
                //per coa id sub account
                $this->_mDataPembukuanAwalTahunSubAcc[$itemBB['coa_id']][$itemBB['sub_acc']] = $itemBB['saldo_awal'];
            }
        }
        
        //get data buku besar mutasi saldo (DK)
        $bbMutasiDK = $this->open($this->mSqlQueries['get_data_buku_besar_saldo_mutasi'], array(
                $this->_mTppIdAktif,
                date('Y-m-d',  strtotime($tanggalAwal)),
                date('Y-m-d',  strtotime($tanggalAkhir)),
                $subAccount.'%',
                ($subAccount == "all" || $subAccount == "")  ? 1 : 0
            )
        );

        if (!empty($bbMutasiDK)) {
            foreach ($bbMutasiDK as $itemBB) {                
                //per coa id
                $this->_mDataPembukuanMutasiDK[$itemBB['coa_id']] += $itemBB['saldo_mutasi_dk'];
                $this->_mDataPembukuanMutasiD[$itemBB['coa_id']] += $itemBB['saldo_mutasi_d'];
                $this->_mDataPembukuanMutasiK[$itemBB['coa_id']] += $itemBB['saldo_mutasi_k'];
                
                //per coa id sub account
                $this->_mDataPembukuanMutasiDKSubAcc[$itemBB['coa_id']][$itemBB['sub_acc']] = $itemBB['saldo_mutasi_dk'];
                $this->_mDataPembukuanMutasiDSubAcc[$itemBB['coa_id']][$itemBB['sub_acc']] = $itemBB['saldo_mutasi_d'];
                $this->_mDataPembukuanMutasiKSubAcc[$itemBB['coa_id']][$itemBB['sub_acc']] = $itemBB['saldo_mutasi_k'];
            }
        }
         
        
    }

    private function _getDataBukuBesarBulanLalu($tanggalAwal,$tanggalAkhir,$subAccount=''){
        $tahunPembukuan = $this->_mTppIdAktif;

        $_periodeSelected = $this->_mPembukuanPeriode->GetTahunPembukuanPeriodeSelected(
            $tanggalAwal, $tanggalAkhir
        );
        $_tppIdSelected      = $_periodeSelected[0]['tpp_id'];
        
        if(!is_null($_tppIdSelected)) {
            $tahunPembukuan = $_tppIdSelected;
        }

        $bbAwalTahun = $this->open($this->mSqlQueries['get_data_buku_besar_saldo_awal_tahun'], array(
                $tahunPembukuan,
                $subAccount.'%',
                ($subAccount == "all" || $subAccount == "")  ? 1 : 0
            )
        );
        
        if (!empty($bbAwalTahun)) {
            foreach ($bbAwalTahun as $itemBB) {
                $this->_mDataPembukuanAwalBulanLalu[$itemBB['coa_id']] += $itemBB['saldo_awal'];
                $this->_mDataPembukuanAwalBulanLaluSubAcc[$itemBB['coa_id']][$itemBB['sub_acc']] = $itemBB['saldo_awal'];
            }
        }
        
        //get data buku besar mutasi saldo (DK)
        $bbMutasiDK = $this->open($this->mSqlQueries['get_data_buku_besar_saldo_mutasi'], array(
                $tahunPembukuan,
                date('Y-m-d',  strtotime($tanggalAwal)),
                date('Y-m-d',  strtotime($tanggalAkhir)),
                $subAccount.'%',
                ($subAccount == "all" || $subAccount == "")  ? 1 : 0
            )
        );

        if (!empty($bbMutasiDK)) {
            foreach ($bbMutasiDK as $itemBB) {
                //per coa id
                $this->_mDataPembukuanMutasiDKBulanLalu[$itemBB['coa_id']] += $itemBB['saldo_mutasi_dk'];
                $this->_mDataPembukuanMutasiDBulanLalu[$itemBB['coa_id']] += $itemBB['saldo_mutasi_d'];
                $this->_mDataPembukuanMutasiKBulanLalu[$itemBB['coa_id']] += $itemBB['saldo_mutasi_k'];
                
                //per coa id sub account
                $this->_mDataPembukuanMutasiDKBulanLaluSubAcc[$itemBB['coa_id']][$itemBB['sub_acc']] = $itemBB['saldo_mutasi_dk'];
                $this->_mDataPembukuanMutasiDBulanLaluSubAcc[$itemBB['coa_id']][$itemBB['sub_acc']] = $itemBB['saldo_mutasi_d'];
                $this->_mDataPembukuanMutasiKBulanLaluSubAcc[$itemBB['coa_id']][$itemBB['sub_acc']] = $itemBB['saldo_mutasi_k'];
            }
        }
        
        $bbTransBulanDK = $this->open($this->mSqlQueries['get_data_buku_besar_saldo_mutasi'], array(
            $tahunPembukuan,
            date('Y-m-01',  strtotime($tanggalAkhir)),
            date('Y-m-d',  strtotime($tanggalAkhir)),
            $subAccount.'%',
            ($subAccount == "all" || $subAccount == "")  ? 1 : 0
        ));

        if (!empty($bbTransBulanDK)) {
            foreach ($bbTransBulanDK as $transBB) {
                $this->_mDataPembukuanMutasiDKTransBulanLaluSubAcc[$transBB['coa_id']][$transBB['sub_acc']] = $transBB['saldo_mutasi_dk'];
                $this->_mDataPembukuanMutasiDTransBulanLaluSubAcc[$transBB['coa_id']][$transBB['sub_acc']] = $transBB['saldo_mutasi_d'];
                $this->_mDataPembukuanMutasiKTransBulanLaluSubAcc[$transBB['coa_id']][$transBB['sub_acc']] = $transBB['saldo_mutasi_k'];
            }
        }
        
        $bbAkumDK = $this->open($this->mSqlQueries['get_data_buku_besar_saldo_mutasi'], array(
            $tahunPembukuan,
            date('Y-01-01',  strtotime($tanggalAwal)),
            $tanggalAkhir,
            $subAccount.'%',
            ($subAccount == "all" || $subAccount == "")  ? 1 : 0
        ));

        if (!empty($bbAkumDK)) {
            foreach ($bbAkumDK as $akumBB) {
                $this->_mDataPembukuanMutasiDKAkumSubAcc[$akumBB['coa_id']][$akumBB['sub_acc']] = $akumBB['saldo_mutasi_dk'];
                $this->_mDataPembukuanMutasiDAkumSubAcc[$akumBB['coa_id']][$akumBB['sub_acc']] = $akumBB['saldo_mutasi_d'];
                $this->_mDataPembukuanMutasiKAkumSubAcc[$akumBB['coa_id']][$akumBB['sub_acc']] = $akumBB['saldo_mutasi_k'];
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

    public function getSaldoAkhirTahunBulanLalu($coaId,$subAcc) {
        $nominal = 0;
        
        if(!array_key_exists($coaId,  $this->_mDataPembukuanAwalTahun)){
            if(array_key_exists($coaId,  $this->_mDataPembukuanAkhirTahunBulanLalu)){
                $nominal = $this->_mDataPembukuanAkhirTahunBulanLalu[$coaId][$subAcc];
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
 
    /**
     * getSaldoAwal Bulan Lalu
     * untuk mendapatkan nilai saldo awal tahun
     * 
     * @param number $tppId id tahun pembukuan 
     * @param number $coaId id coa
     * @return number
     */
    public function getSaldoAwaltahunBulanLalu($coaId) {
        $nominal = 0;
        
        if(array_key_exists($coaId,  $this->_mDataPembukuanAwalBulanLalu)){
            $nominal = $this->_mDataPembukuanAwalBulanLalu[$coaId];
        }
        
        return $nominal;
    }

    /**
     * getSaldoMutasiDKBulanLalu
     * untuk mendapatkan nominal mutasi debet/kredit
     * 
     * @param number $tppId id tahun pembukuan 
     * @param number $coaId id coa
     * @param string $status default kosong (D untuk Debet dan K untuk kredit) 
     * kosong mengambil semua
     * @return number
     */
    public function getSaldoMutasiDKBulanLalu($coaId,$status = '') {
        $nominal = 0;
        if($status == 'D') {
            if(array_key_exists($coaId,  $this->_mDataPembukuanMutasiDBulanLalu)){
                $nominal = $this->_mDataPembukuanMutasiDBulanLalu[$coaId];
            }
        }elseif($status == 'K') {
            if(array_key_exists($coaId,  $this->_mDataPembukuanMutasiKBulanLalu)){
                $nominal = $this->_mDataPembukuanMutasiKBulanLalu[$coaId];
            }
        } else {
            if(array_key_exists($coaId,  $this->_mDataPembukuanMutasiDKBulanLalu)){
                $nominal = $this->_mDataPembukuanMutasiDKBulanLalu[$coaId];
            }
        }
        return $nominal;
    }
    
    /**************************************************************************
     * untuk get nominal per coa sub acccount
     **************************************************************************/

    /**
     * getSaldoAwaltahunSubAcc
     * untuk mendapatkan nilai saldo awal tahun
     *
     * @param number $coaId id coa
     * @param number $subAcc sub account
     * @return number
     */
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

    /**
     * getSaldoAwaltahunSubAcc
     * untuk mendapatkan nilai saldo awal tahun
     *
     * @param number $coaId id coa
     * @param number $subAcc sub account
     * @return number
     */
    public function getSaldoAwaltahunBulanLaluSubAcc($coaId,$subAcc) {
        $nominal = 0;
        
        if(array_key_exists($coaId,  $this->_mDataPembukuanAwalBulanLaluSubAcc)){
            $nominal = $this->_mDataPembukuanAwalBulanLaluSubAcc[$coaId][$subAcc];
        }
        
        return $nominal;
    }

    /**
     * getSaldoMutasiDKBulanLaluSubAcc
     * untuk mendapatkan nominal mutasi bulan lalu debet/kredit
     *
     * @param number $coaId id coa
     * @param number $subAcc sub account
     * @param string $status default kosong (D untuk Debet dan K untuk kredit) 
     * kosong mengambil semua
     * @return number
     */
    public function getSaldoMutasiDKBulanLaluSubAcc($coaId,$subAcc,$status = '') {
        $nominal = 0;
        if($status == 'D') {
            if(array_key_exists($coaId,  $this->_mDataPembukuanMutasiDBulanLaluSubAcc)){
                $nominal = $this->_mDataPembukuanMutasiDBulanLaluSubAcc[$coaId][$subAcc];
            }
        }elseif($status == 'K') {
            if(array_key_exists($coaId,  $this->_mDataPembukuanMutasiKBulanLaluSubAcc)){
                $nominal = $this->_mDataPembukuanMutasiKBulanLaluSubAcc[$coaId][$subAcc];
            }
        } else {
            if(array_key_exists($coaId,  $this->_mDataPembukuanMutasiDKBulanLaluSubAcc)){
                $nominal = $this->_mDataPembukuanMutasiDKBulanLaluSubAcc[$coaId][$subAcc];
            }
        }
        return $nominal;
    }

    /**
     * getSaldoMutasiDKTransBulanLaluSubAcc
     * untuk mendapatkan nominal mutasi bulan lalu debet/kredit
     *
     * @param number $coaId id coa
     * @param number $subAcc sub account
     * @param string $status default kosong (D untuk Debet dan K untuk kredit) 
     * kosong mengambil semua
     * @return number
     */
    public function getSaldoMutasiDKTransBulanLaluSubAcc($coaId,$subAcc,$status = '') {
        $nominal = 0;
        if($status == 'D') {
            if(array_key_exists($coaId,  $this->_mDataPembukuanMutasiDTransBulanLaluSubAcc)){
                $nominal = $this->_mDataPembukuanMutasiDTransBulanLaluSubAcc[$coaId][$subAcc];
            }
        }elseif($status == 'K') {
            if(array_key_exists($coaId,  $this->_mDataPembukuanMutasiKTransBulanLaluSubAcc)){
                $nominal = $this->_mDataPembukuanMutasiKTransBulanLaluSubAcc[$coaId][$subAcc];
            }
        } else {
            if(array_key_exists($coaId,  $this->_mDataPembukuanMutasiDKTransBulanLaluSubAcc)){
                $nominal = $this->_mDataPembukuanMutasiDKTransBulanLaluSubAcc[$coaId][$subAcc];
            }
        }
        return $nominal;
    }

    public function getSaldoMutasiDKAkumSubAcc($coaId,$subAcc,$status = '') {
        $nominal = 0;
        if($status == 'D') {
            if(array_key_exists($coaId,  $this->_mDataPembukuanMutasiDAkumSubAcc)){
                $nominal = $this->_mDataPembukuanMutasiDAkumSubAcc[$coaId][$subAcc];
            }
        }elseif($status == 'K') {
            if(array_key_exists($coaId,  $this->_mDataPembukuanMutasiKAkumSubAcc)){
                $nominal = $this->_mDataPembukuanMutasiKAkumSubAcc[$coaId][$subAcc];
            }
        } else {
            if(array_key_exists($coaId,  $this->_mDataPembukuanMutasiDKAkumSubAcc)){
                $nominal = $this->_mDataPembukuanMutasiDKAkumSubAcc[$coaId][$subAcc];
            }
        }
        return $nominal;
    }
}
