<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/kelompok_laporan/business/AppKlpLaporan.class.php';

class ProcessKlpLaporan {

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
    
    //untuk input non summary
    
    private $_mIsSummary = 'T';
    
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
            'inputKlpLaporan', 
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
            $addKlpLap = $this->Obj->DoAddData(
                    $this->_POST['kellap_parent_id'], 
                    $this->_POST['kellap_parent_kode_sistem'], 
                    $this->_POST['kellap_parent_level'], 
                    $this->_POST['kellap_nama'], 
                    $this->_POST['kellap_kelompok'],
                    $this->_POST['kellap_tipe'],
                    $this->_POST['kellap_parent_tipe'],
                    $this->_POST['kellap_is_tambah'],
                    $this->_mIsSummary,
                    $this->_POST['kellap_no_selanjutnya']
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
                    'KlpLaporan', 
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
                'inputKlpLaporan', 
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
            $updateKlpLap = $this->Obj->DoUpdateData(
                $this->_POST['kellap_parent_id'],
                $this->_POST['kellap_nama'],
                $this->_POST['kellap_kelompok'],
                $this->_POST['kellap_is_tambah'],
                $this->_mIsSummary,
                $this->_POST['kellap_no_selanjutnya'],
                $this->decId
            );
            
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
                    'KlpLaporan', 
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
                'inputKlpLaporan', 
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
                'inputKlpLaporan', 
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