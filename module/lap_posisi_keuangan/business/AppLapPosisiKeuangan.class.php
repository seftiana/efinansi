<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/user_unit_kerja/business/UserUnitKerja.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/finansi_laporan_builder/business/LaporanBuilder.class.php';

class AppLapPosisiKeuangan extends Database {
    protected $mSqlFile = 'module/lap_posisi_keuangan/business/lapposisikeuangan.sql.php';

    private $_mLaporanBilderObj;
    private $_mUnitObj;
    
    //kelompok laporan posisi keuangan
    private $_kelompokId = 14;
    
    //range tanggal
    private $_mRangeTanggal = array();
    
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
        $this->_mUnitObj = new UserUnitKerja();
    }

    public function LaporanBuilder(){
        return $this->_mLaporanBilderObj;
    }
    
    public function changeTppId($tppId) {
        $this->_mLaporanBilderObj->setTppIdAktif($tppId);
        $this->_mLaporanBilderObj->setTppIdTahunSebelumnya($tppId);
    }
    
    public function Setup($kelompokId = null){
        $this->_kelompokId = $kelompokId != null ? $kelompokId : $this->_kelompokId;
        $this->_mLaporanBilderObj->setup($this->_kelompokId);
    }
    
    public function getSubAccountCombo(){
        return $this->Open($this->mSqlQueries['get_sub_account_combobox'],array());
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
    
    public function getUserUnitKerjaInfo(){                
        $userId = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
        $unitKerja = $this->_mUnitObj->GetUnitKerjaRefUser($userId);
        return $unitKerja; 
    }

}

?>