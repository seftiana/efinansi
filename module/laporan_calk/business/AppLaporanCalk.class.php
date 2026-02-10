<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/finansi_laporan_builder/business/LaporanBuilder.class.php';

class AppLaporanCalk extends Database {
    protected $mSqlFile = 'module/laporan_calk/business/applaporancalk.sql.php';

    private $_mLaporanBilderObj;
    
    //kelompok laporan posisi keuangan
    private $_kelompokId = 14;
    
    public $indonesianMonth    = array(
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    );

    public function __construct($connectionNumber = 0) {
        parent::__construct($connectionNumber);
        $this->_mLaporanBilderObj = new LaporanBuilder;
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

}
