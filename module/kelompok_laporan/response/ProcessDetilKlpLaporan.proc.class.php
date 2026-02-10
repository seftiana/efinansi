<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/kelompok_laporan/business/AppKlpLaporan.class.php';

class ProcessDetilKlpLaporan {

    protected $_POST;
    protected $Obj;
    protected $pageView;
    protected $pageInput;
    //css hanya dipake di view
    protected $cssDone = "notebox-done";
    protected $cssFail = "notebox-warning";
    protected $cssAlert = "notebox-alert";
    protected $return;
    protected $decId;
    protected $encId;

    protected $mJnsLap;
    protected $mCariId;
    
    protected $mEnJnsLap;
    protected $mEnCariId;
    
    protected $mGetCoa;

    public function __construct() {
        $this->Obj = new AppKelpLaporan();
        $this->_POST = is_object($_POST) ? $_POST->AsArray() : $_POST;
        $this->_GET = is_object($_GET) ? $_GET->AsArray() : $_GET;
        
        //date get dekrip
        $this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
        $this->mProcess = Dispatcher::Instance()->Decrypt($_REQUEST['process']);
        $this->mJnsLap = Dispatcher::Instance()->Decrypt($_REQUEST['jns_lap']);
        $this->mCariId = Dispatcher::Instance()->Decrypt($_REQUEST['cari']);
        
        //date get enkrip
        $this->encId = Dispatcher::Instance()->Encrypt($this->decId);
        $this->mEnProcess = Dispatcher::Instance()->Encrypt($this->mProcess);
        $this->mEnJnsLap = Dispatcher::Instance()->Encrypt($this->mJnsLap);
        $this->mEnCariId = Dispatcher::Instance()->Encrypt($this->mCariId);
        
        $this->pageView = Dispatcher::Instance()->GetUrl(
            'kelompok_laporan', 
            'KlpLaporan', 
            'view', 
            'html'
        ) .
        "&jns_lap=" . $this->mEnJnsLap .
        "&cari=" . $this->mEnCariId ;

        $this->pageInput = Dispatcher::Instance()->GetUrl(
            'kelompok_laporan', 
            'inputDetilKlpLaporan', 
            'view', 
            'html'
        ) .
        "&jns_lap=" . $this->mEnJnsLap .
        "&cari=" . $this->mEnCariId .
        "&dataId=" . $this->encId;
    }

    public function Check() {
        if (isset($_POST['btnsimpan'])) {
            $statusCoa = 0;
            $statusKlp = 0;
            
            //print_r($this->_POST['coa']); exit();
            if (!empty($this->_POST['coa'])) {
                $statusCoa = 1;
                foreach ($this->_POST['coa'] as $itemCoa){
                    if( (!isset($itemCoa['is_mutasi_dk']) && 
                            !isset($itemCoa['is_mutasi_d']) && 
                                !isset($itemCoa['is_mutasi_k'])) 
                                    && !isset($itemCoa['is_saldo_awal']) ) {
                        $statusCoa = 1000;
                        $getCoa[] = $itemCoa;
                    }
                } 
            }
            
            $this->mGetCoa = $getCoa;
            
            if (!empty($this->_POST['klp'])) {
                $statusKlp = 1;
            }
            $total = $statusCoa  + $statusKlp;
            
           
            if ($total === 0) {
                return "empty";
            } elseif($total >= 1000 && !empty($getCoa)) {
                return "cekMutasi";
            }else {
                return true;
            }
        }
        return false;
    }

    public function Add() {
        $cek = $this->Check();
        if ($cek === true) {           
         
            $addDetilKlpLap = $this->Obj->DoAddDetilData(
                $this->_POST['kellap_id'], 
                $this->_POST['coa'],
                $this->_POST['klp']
            );           
            
            if ($addDetilKlpLap == true) {
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
                    'inputDetilKlpLaporan', 
                    'view', 
                    'html',
                    array(
                        $this->_POST, 
                        'Gagal Melakukan Penyimpanan Data', 
                        $this->cssFail
                    ), 
                    Messenger::NextRequest
                );
                return $this->pageInput;
            }
        } elseif ($cek == "empty") {
            Messenger::Instance()->Send(
                'kelompok_laporan', 
                'inputDetilKlpLaporan', 
                'view', 
                'html',
                array(
                    $this->_POST, 
                    'Anda belum memilih coa / referensi kelompok laporan',
                    $this->cssAlert
                ), 
                Messenger::NextRequest
            );
            return $this->pageInput;
        } elseif($cek =="cekMutasi") {
            $msg = '';
            if(!empty($this->mGetCoa)) {
                foreach ($this->mGetCoa  as $itemCoa) {
                    $msg .= '<b style="padding-left:15px">* '.$itemCoa['kode'].' - '.$itemCoa['nama'] .'</b></br>';
                }
            }
            Messenger::Instance()->Send(
                'kelompok_laporan', 
                'inputDetilKlpLaporan', 
                'view', 
                'html',
                array(
                    $this->_POST, 
                    'Perhitungan Saldo Awal atau Mutasi Akun dibawah ini belum disetting : <br />'.
                    $msg,
                    $this->cssAlert
                ), 
                Messenger::NextRequest
            );
            return $this->pageInput;
        }
        return $this->pageView;
    }

    public function Delete() {
        $arrId = $this->_POST['idDelete'];
        $deleteArrData = $this->Obj->DoDeleteDetilDataByArrayId($arrId);

        if ((bool) $deleteArrData == true) {
            Messenger::Instance()->Send(
                'kelompok_laporan', 
                'DetilKlpLaporan', 
                'view', 
                'html', 
                array(
                    NULL,
                    'Penghapusan Data berhasil Dilakukan',
                    $this->cssDone
                ), 
                Messenger::NextRequest
            );
        } else {
            Messenger::Instance()->Send(
                'kelompok_laporan', 
                'DetilKlpLaporan', 
                'view', 
                'html', 
                array(
                    null,
                    'Gagal Melakukan Penghapusan data',
                    $this->cssFail
                ), 
                Messenger::NextRequest
            );
        }
        return $this->pageView;
    }

}

?>