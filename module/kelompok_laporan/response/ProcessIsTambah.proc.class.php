<?php

/**
 * 
 * 
 * @modified by noor hadi <noor.hadi@gamatechno.com>
 * menambahkan fungsi set is tambah
 * 
 * 
 * @copyright (c) 2011 - 2017, Gamatechno Indonesia
 */

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/kelompok_laporan/business/SetIsTambah.class.php';

class ProsessIsTambah {

    var $_POST;
    var $Obj;
    var $pageView;
    var $pageInput;
    //css hanya dipake di view
    var $cssDone = "notebox-done";
    var $cssFail = "notebox-warning";
    var $return;
    var $decId;
    var $encId;

    function __construct() {
        $this->Obj = new SetIsTambah();
        $this->_POST = $_POST->AsArray();
        
        $this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);       
        $this->mJnsLap = Dispatcher::Instance()->Decrypt($_REQUEST['jns_lap']);
        $this->mCariId = Dispatcher::Instance()->Decrypt($_REQUEST['cari']);
        
        $this->encId = Dispatcher::Instance()->Encrypt($this->decId);
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
    }


    public function SetIsTambah() {
        $return['url'] = $this->pageView;
        $return['data'] = NULL;

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $return['message'] = 'You don\'t have permission';
            $return['style'] = $this->cssFail;
        } else {
            $id = $this->_POST['id'];
            $process = $this->Obj->setIsTambah($id);
            $nama = $this->Obj->getLaporanNama($id);
            if ($process === true) {
                $return['message'] = 'Fungsi penambah sub Kelompok  " ' . $nama . ' " telah diaktifkan.';
                $return['style'] = $this->cssDone;
            } else {
                $return['message'] = 'Gagal mengaktifkan gungsi penambah sub Kelompok  " ' . $nama . ' "';
                $return['style'] = $this->cssFail;
            }
        }

        return $return;
    }

    public function UnsetIsTambah() {
        $return['url'] = $this->pageView;
        $return['data'] = NULL;

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $return['message'] = 'You don\'t have permission';
            $return['style'] = $this->cssFail;
        } else {
            $id = $this->_POST['id'];
            $nama = $this->Obj->getLaporanNama($id);
            $process = $this->Obj->unsetIsTambah($id);
            if ($process === true) {
                $return['message'] = 'Fungsi pengurang sub Kelompok  " ' . $nama . ' " telah diaktifkan.';
                $return['style'] = $this->cssDone;
            } else {
                $return['message'] = 'Gagal mengaktifkan gungsi penguran sub Kelompok  " ' . $nama . ' "';
                $return['style'] = $this->cssFail;
            }
        }

        return $return;
    }

}

?>