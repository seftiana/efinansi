<?php
require_once GTFWConfiguration::GetValue('application', 'docroot').
    'module/finansi_laporan_builder/business/LaporanBuilder.class.php';

class AppLapAktivitas extends Database {

    protected $mSqlFile = 'module/lap_aktivitas/business/lapaktifitas.sql.php';
    private $_mLaporanBilderObj;
    private $_kelompokId = 6;

    function __construct($connectionNumber = 0) {
        $this->_mLaporanBilderObj = new LaporanBuilder();
        parent::__construct($connectionNumber);
    }

    public function LaporanBuilder(){
        return $this->_mLaporanBilderObj;
    }
        
    public function Setup(){
        $this->_mLaporanBilderObj->setup($this->_kelompokId);
    }
    
    
    public function getKodeSistem() {
        return $this->_mLaporanBilderObj->getKodeSistemKelompokId($this->_kelompokId);
    }
    
    public function getSubAccountCombo(){
        return $this->Open($this->mSqlQueries['get_sub_account_combobox'],array());
    }
}
