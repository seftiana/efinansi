<?php

/**
 * ================= doc ====================
 * FILENAME     : LaporanPosisiKeuangan.class.php
 * @package     : LaporanPosisiKeuangan
 * scope        : PUBLIC
 * @Author      : Eko Susilo
 * @Created     : 2015-02-26
 * @Modified    : 2015-02-26
 * @Analysts    : Dyah Fajar N
 * @copyright   : Copyright (c) 2012 Gamatechno
 * ================= doc ====================
 */

require_once GTFWConfiguration::GetValue('application', 'docroot') .
    'module/finansi_laporan_builder_sementara/business/LaporanBuilder.class.php';

class LaporanPosisiKeuangan extends Database {
    # internal variables
    protected $mSqlFile;
    
    private $_mLaporanBilderObj;
    //kelompok laporan posisi keuangan
    private $_kelompokId = 14;
    
    //range tanggal
    private $_mRangeTanggal = array();

    public $_POST;
    public $_GET;

    # Constructor

    function __construct($connectionNumber = 0) {
        $this->mSqlFile = 'module/lap_posisi_keuangan_sementara/business/laporan_posisi_keuangan.sql.php';
        $this->_POST = is_object($_POST) ? $_POST->AsArray() : $_POST;
        $this->_GET = is_object($_GET) ? $_GET->AsArray() : $_GET;
        $this->_mLaporanBilderObj = new LaporanBuilder();
        parent::__construct($connectionNumber);
    }

    public function LaporanBuilder(){        
        return $this->_mLaporanBilderObj;
    }

    public function Setup(){
        $this->_mLaporanBilderObj->setup($this->_kelompokId);
    }
    
    public function getSubAccountCombo(){
        return $this->Open($this->mSqlQueries['get_sub_account_combobox'],array());
    }

    public function getPaternSubAccount() {
        $return = $this->Open($this->mSqlQueries['get_patern_sub_account'], array());
        if ($return && !empty($return)) {
            $return['patern'] = $return[0]['patern'];
            $return['regex'] = '/^' . $return[0]['regex'] . '$/';
        } else {
            $return['patern'] = GTFWConfiguration::GetValue('application', 'subAccFormat');
            $return['regex'] = '/^([a-zA-Z0-9]{1,2})-([a-zA-Z0-9]{1,2})-([a-zA-Z0-9]{1,2})-([a-zA-Z0-9]{1,2})-([a-zA-Z0-9]{1,2})-([a-zA-Z0-9]{1,2})-([a-zA-Z0-9]{1,2})$/';
        }

        return $return;
    }
    
    public function getPeriodePembukuan(){
        $dataPeriode = array();
        $ret = $this->open($this->mSqlQueries['get_periode_pembukuan'], array());
        if(!empty($ret)) {
            foreach($ret as $v){
                $dataPeriode[] = array(
                    'id' => $v['tpp_id'],
                    'name' => $v['nama_periode'] . 
                        ' ('.
                        IndonesianDate($v['tanggal_awal'], 'yyyy-mm-dd') . ' s/d '.
                        IndonesianDate($v['tanggal_akhir'], 'yyyy-mm-dd') .
                        ')'
                );
                $this->_mRangeTanggal[$v['tpp_id']]['tanggal_awal'] = $v['tanggal_awal'];
                $this->_mRangeTanggal[$v['tpp_id']]['tanggal_akhir'] = $v['tanggal_akhir'];
            }
        }
        return $dataPeriode;
    }

}

?>
