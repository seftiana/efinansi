<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/lppa/business/Lppa.class.php';

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ProcessLppa
{

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

    function __construct() 
    {
        $this->Obj = new Lppa;
        $this->_POST = $_POST->AsArray();
        $this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
        $this->encId = Dispatcher::Instance()->Encrypt($this->decId);
        $this->pageView = Dispatcher::Instance()->GetUrl('lppa', 'Lppa', 'view', 'html');
        $this->pageInput = Dispatcher::Instance()->GetUrl('lppa', 'AddLppa', 'view', 'html');
        $userUnitKerja = new UserUnitKerja();
        $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
        $this->Role = $userUnitKerja->GetRoleUser($userId);
    }


    function Check() {
        //print_r($this->_POST);
        /*
        if(!$this->_POST['unitkerja'] || trim($this->_POST['unitkerja']=='-')) 
        $this->_POST['unitkerja'] = $this->_POST['satker'];
        
       */
       if (isset($_POST['btnsimpan'])) {
           
          
                if($this->_POST['unit_kerja_id'] == '') {
                    return "empty";         
                } elseif($this->_POST['realisasi_no'] == '') {
                    return "empty";             
                } elseif($this->_POST['penanggung_jawab'] == '') {
                    return "empty";             
                } elseif($this->_POST['mengetahui'] == '') {
                    return "empty";             
                }
            
             return true;
        }
        return false;
    }

    function Add() {
        $cek = $this->Check();
        if($cek === true) {
            $addLppa = $this->Obj->AddLppa($this->_POST);
            // $cekRowData = $this->Obj->GetCountRowData($this->_POST['tahun_anggaran'], $this->_POST['unitkerja']);
            if ($addLppa === true) {
                Messenger::Instance()->Send('lppa', 'Lppa', 'view', 'html', 
                array($this->_POST,'Penambahan data Berhasil Dilakukan : '.$cekRowData, 
                $this->cssDone),Messenger::NextRequest);
            } else {
                Messenger::Instance()->Send('lppa', 'Lppa', 'view', 'html', 
                array($this->_POST,'Gagal Menambah Data', $this->cssFail),Messenger::NextRequest);
            }
            
        } elseif($cek == "empty") {
            Messenger::Instance()->Send('lppa', 'addLppa', 'view', 'html', 
            array($this->_POST,'Lengkapi Isian Data'),Messenger::NextRequest);
            return $this->pageInput;
        } elseif($cek == "exist") {
            Messenger::Instance()->Send('lppa', 'addLppa', 'view', 'html', 
            array($this->_POST,'Data sudah ada'),Messenger::NextRequest);
            return $this->pageInput;
        }
        
        return $this->pageView;
    }

    function Update() {
        $cek = $this->Check();
        
        if($cek === true) {         
            $updateLppa = $this->Obj->UpdateLppa($this->_POST);
            
            if ($updateLppa === true) {
                Messenger::Instance()->Send('lppa', 'Lppa', 'view', 'html', 
                array($this->_POST,'Perubahan Data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
            } else {
                Messenger::Instance()->Send('lppa', 'Lppa', 'view', 'html', 
                array($this->_POST,'Perubahan Data Gagal Dilakukan', $this->cssFail),Messenger::NextRequest);
            }
        } elseif($cek == "empty") {
            Messenger::Instance()->Send('lppa', 'addLppa', 'view', 'html', 
            array($this->_POST,'Lengkapi Isian Data'),Messenger::NextRequest);
            return $this->pageInput . "&dataId=" . $this->encId;
        }elseif($cek == "exist") {
            Messenger::Instance()->Send('lppa', 'addLppa', 'view', 'html', 
            array($this->_POST,'Data sudah ada'),Messenger::NextRequest);
            return $this->pageInput;
        }
        return $this->pageView;
    }


    function Delete() {
        $arrId = $this->_POST['idDelete'];
        //print_r($this->_POST);
        $deleteArrData = $this->Obj->DeleteLppa($arrId);
        if($deleteArrData === true) {
            Messenger::Instance()->Send('lppa', 'Lppa', 'view', 'html', 
            array($this->_POST,'Penghapusan Data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
        } else {
            Messenger::Instance()->Send('lppa', 'Lppa', 'view', 'html', 
            array($this->_POST, ' Data Tidak Dapat Dihapus.', $this->cssFail),Messenger::NextRequest);
        }
        return $this->pageView;
    }
}

?>