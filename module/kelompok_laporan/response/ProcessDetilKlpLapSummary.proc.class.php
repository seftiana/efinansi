<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/kelompok_laporan/business/AppKlpLaporan.class.php';

class ProcessDetilKlpLapSummary {

    public $_POST;
    protected $Obj;
    protected $pageView;
    protected $pageInput;
    //css hanya dipake di view
    protected $cssDone = "notebox-done";
    protected $cssFail = "notebox-warning";
    protected $return;
    protected $decId;
    protected $encId;
    protected $mProcess;
    protected $mEnProcess;

    protected $mJnsLap;
    protected $mCariId;
    
    protected $mEnJnsLap;
    protected $mEnCariId;

    //untnuk data summary
    private $_mIsSummary = 'Y';
    private $_mIsTambah = 'A';


    public function __construct() {
        $this->Obj = new AppKelpLaporan();
        $this->_POST = $_POST->AsArray();
        $this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
        $this->mProcess = Dispatcher::Instance()->Decrypt($_REQUEST['process']);        
        $this->mJnsLap = Dispatcher::Instance()->Decrypt($_REQUEST['jns_lap']);
        $this->mCariId = Dispatcher::Instance()->Decrypt($_REQUEST['cari']);
        
        $this->encId = Dispatcher::Instance()->Encrypt($this->decId);
        $this->mEnProcess = Dispatcher::Instance()->Encrypt($this->mProcess);
        $this->mEnJnsLap = Dispatcher::Instance()->Encrypt($this->mJnsLap);
        $this->mEnCariId = Dispatcher::Instance()->Encrypt($this->mCariId);
        
        $this->pageView = Dispatcher::Instance()->GetUrl(
            'kelompok_laporan', 
            'KlpLaporan', 
            'view', 
            'html'
        ).
        "&jns_lap=" . $this->mEnJnsLap .
        "&cari=" . $this->mEnCariId ;
        
        $this->pageInput = Dispatcher::Instance()->GetUrl(
            'kelompok_laporan', 
            'inputDetilKlpLapSummary', 
            'view', 
            'html'
        ) .
        "&process=" . $this->mEnProcess .
        "&jns_lap=" . $this->mEnJnsLap .
        "&cari=" . $this->mEnCariId .
        "&dataId=" . $this->encId;
    }

    public function Check() {
        if (isset($this->_POST['btnsimpan'])) {
            if ((trim($this->_POST['kellap_nama']) == "")) {
                return "empty";
            }
            return true;
        } 
        return false;
    }

    public function Add() {
        $cek = $this->Check();
        if ($cek === true) {
            $params = array(
                'kellap_parent_id' => $this->_POST['kellap_parent_id'], 
                'kellap_parent_kode_sistem' => $this->_POST['kellap_parent_kode_sistem'], 
                'kellap_parent_level' => $this->_POST['kellap_parent_level'], 
                'kellap_nama' => $this->_POST['kellap_nama'], 
                'kellap_kelompok' => $this->_POST['kellap_kelompok'],
                'kellap_tipe' => $this->_POST['kellap_tipe'],
                'kellap_parent_tipe' => $this->_POST['kellap_parent_tipe'],
                'kellap_is_tambah' => $this->_mIsTambah,
                'kellap_is_summary' => $this->_mIsSummary,
                'kellap_no_selanjutnya' => $this->_POST['kellap_no_selanjutnya'],
                'kellap_operasi_perhitungan' => $this->_POST['kellap_operasi_perhitungan'],
                'data_klp' => $this->_POST['klp'],
                
            );
            //print_r($params);exit();
            $addKlpLap = $this->Obj->DoAddDataSummary(
                   $params
            );
            
            if ($addKlpLap == true) {
                Messenger::Instance()->Send(
                    'kelompok_laporan', 
                    'KlpLaporan', 
                    'view', 
                    'html', 
                    array(
                        $this->_POST,
                        'Penambahan data Berhasil Dilakukan',
                        $this->cssDone
                    ), 
                    Messenger::NextRequest
                );
            } else {
                Messenger::Instance()->Send(
                    'kelompok_laporan', 
                    'inputDetilKlpLapSummary', 
                    'view', 
                    'html', 
                    array(
                        $this->_POST,
                        'Gagal Menambah Data',
                        $this->cssFail
                    ), 
                    Messenger::NextRequest
                );
            }
        } elseif ($cek == "empty") {
            Messenger::Instance()->Send(
                'kelompok_laporan', 
                'inputDetilKlpLapSummary', 
                'view', 
                'html', 
                array(
                    $this->_POST,
                    'Lengkapi Isian Data'
                ), 
                Messenger::NextRequest
            );
            return $this->pageInput;
        } 
        return $this->pageView;
        //return $this->pageInput;
    }

    public function Update() {
        $cek = $this->Check();
       //print_r($this->_POST);
        if ($cek === true) {
            $params = array(
                'kellap_parent_id' => $this->_POST['kellap_parent_id'], 
                'kellap_nama' => $this->_POST['kellap_nama'], 
                'kellap_kelompok' => $this->_POST['kellap_kelompok'],
                'kellap_is_tambah' => $this->_mIsTambah,
                'kellap_is_summary' => $this->_mIsSummary,
                'kellap_no_selanjutnya' => $this->_POST['kellap_no_selanjutnya'],
                'kellap_operasi_perhitungan' => $this->_POST['kellap_operasi_perhitungan'],
                'data_klp' => $this->_POST['klp'],
                'kellap_id' => $this->decId
            );
            $updateKlpLap = $this->Obj->DoUpdateDataSummary($params);
            
            if ($updateKlpLap === true) {
                Messenger::Instance()->Send(
                    'kelompok_laporan',
                    'KlpLaporan',
                    'view',
                    'html',
                    array(
                        $this->_POST,
                        'Perubahan Data Berhasil Dilakukan',
                        $this->cssDone
                    ), 
                    Messenger::NextRequest
                );
            } else {
                Messenger::Instance()->Send(
                    'kelompok_laporan', 
                    'inputDetilKlpLapSummary', 
                    'view', 
                    'html', 
                    array(
                        $this->_POST,
                        'Perubahan Data Gagal Dilakukan',
                        $this->cssFail
                    ), 
                    Messenger::NextRequest
                );
            }
        } elseif ($cek == "empty") {
            Messenger::Instance()->Send(
                'kelompok_laporan', 
                'inputDetilKlpLapSummary', 
                'view', 
                'html', 
                array(
                    $this->_POST,
                    'Lengkapi Isian Data'
                ), 
                Messenger::NextRequest
            );
            return $this->pageInput;
        } elseif ($cek == "is_number") {
            Messenger::Instance()->Send(
                'kelompok_laporan', 
                'inputDetilKlpLapSummary', 
                'view', 
                'html', 
                array(
                    $this->_POST,
                    'No Urutan harus angka'
                ), 
                Messenger::NextRequest
            );
            return $this->pageInput;
        }
        return $this->pageView;
    }

    public function Delete() {
        $idDelete = $this->_POST['idDelete'];
        //print_r($this->_POST);
        $deleteStatus = $this->Obj->DoDeleteDataById($idDelete);
        if ($deleteStatus == true) {
            Messenger::Instance()->Send(
                'kelompok_laporan', 
                'KlpLaporan', 
                'view', 
                'html', 
                array(
                    $this->_POST,
                    'Penghapusan Data Berhasil Dilakukan',
                    $this->cssDone
                ), 
                Messenger::NextRequest
            );
        } else {            
            Messenger::Instance()->Send(
                'kelompok_laporan', 
                'KlpLaporan', 
                'view', 
                'html', 
                array(
                    $this->_POST,
                    'Data Tidak Dapat Dihapus',
                    $this->cssFail
                ), 
                Messenger::NextRequest
            );
        }
        return $this->pageView;
    }

}

?>