<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') .
	'module/finansi_laporan_builder/business/LaporanBuilder.class.php';

class AppLapAliranKas extends Database
{
	
	protected $mSqlFile = 'module/lap_alirankas/business/lapalirankas.sql.php';

    private $_mLaporanBilderObj;
    private $_kelompokId = 2;

	function __construct($connectionNumber = 0) 
	{
		parent::__construct($connectionNumber);
        $this->_mLaporanBilderObj = new LaporanBuilder;
	}
	
	public function LaporanBuilder(){        
        return $this->_mLaporanBilderObj;
    }
    
    public function changeTppId($tppId) {
        $this->_mLaporanBilderObj->setTppIdAktif($tppId);
        $this->_mLaporanBilderObj->setTppIdTahunSebelumnya($tppId);
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
?>
