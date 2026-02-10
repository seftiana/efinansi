<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') .
    'module/finansi_laporan_builder/business/LaporanBuilder.class.php';

class AppLaporanKonsolidasi extends Database {

    protected $mSqlFile = 'module/laporan_konsolidasi/business/applaporankonsolidasi.sql.php';
    private $_mLaporanBilderObj;

    private $_kelompokId = 14;
    private $_mRangeTanggal = array();

    function __construct($connectionNumber = 0) {
        parent::__construct($connectionNumber);
        $this->_mLaporanBilderObj = new LaporanBuilder();
    }

    public function LaporanBuilder(){        
        return $this->_mLaporanBilderObj;
    }
    
    public function Setup($id = 14){
        $this->_kelompokId = $id;

        $this->_mLaporanBilderObj->setup($this->_kelompokId);
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
    
    public function getTanggal($tppId){
        if(array_key_exists($tppId,  $this->_mRangeTanggal)){
            return $this->_mRangeTanggal[$tppId];
        } else {
            return array(
                'tanggal_awal' => '',
                'tanggal_akhir' => ''
            );
        }
    }
    
    public function getKodeSistem() {
        return $this->_mLaporanBilderObj->getKodeSistemKelompokId($this->_kelompokId);
    }

}

?>