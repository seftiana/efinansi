<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/lppa_approval/business/Lppa.class.php';

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
        $this->pageView = Dispatcher::Instance()->GetUrl('lppa_approval', 'Lppa', 'view', 'html');
        $this->pageInput = Dispatcher::Instance()->GetUrl('lppa_approval', 'AddLppa', 'view', 'html');
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
           
           /*
                if($this->_POST['unitkerja'] == '') {
                    return "empty";         
                } elseif(trim($this->_POST['jumlah_kelas'])==""  || trim($this->_POST['jumlah_kelas'])== 0 ) {
                    return "empty";             
                } else {
                    if(($this->_POST['unitkerja'] !== $this->_POST['unitkerja_old']) || 
                       ($this->_POST['tahun_anggaran_old'] !== $this->_POST['tahun_anggaran'])) {
                            $cekRowData = $this->Obj->GetCountRowData($this->_POST['tahun_anggaran'], $this->_POST['unitkerja']);               
                            if($cekRowData > 0) {
                                return "exist";             
                            } else {
                                return true;
                            } 
                    } else {
                        return true;
                    }
               }
            * 
            */
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
                Messenger::Instance()->Send('lppa_approval', 'Lppa', 'view', 'html', 
                array($this->_POST,'Penambahan data Berhasil Dilakukan : '.$cekRowData, 
                $this->cssDone),Messenger::NextRequest);
            } else {
                Messenger::Instance()->Send('lppa_approval', 'Lppa', 'view', 'html', 
                array($this->_POST,'Gagal Menambah Data', $this->cssFail),Messenger::NextRequest);
            }
            
        } elseif($cek == "empty") {
            Messenger::Instance()->Send('lppa_approval', 'addLppa', 'view', 'html', 
            array($this->_POST,'Lengkapi Isian Data'),Messenger::NextRequest);
            return $this->pageInput;
        } elseif($cek == "exist") {
            Messenger::Instance()->Send('lppa_approval', 'addLppa', 'view', 'html', 
            array($this->_POST,'Data sudah ada'),Messenger::NextRequest);
            return $this->pageInput;
        }
        
        return $this->pageView;
    }

    function Update() {
        $cek = $this->Check();
        
        if($cek === true) {         
            $updateLppa = $this->Obj->UpdateApprovalLppa($this->_POST);
            
            if ($updateLppa === true) {
                Messenger::Instance()->Send('lppa_approval', 'Lppa', 'view', 'html', 
                array($this->_POST,'Approval  Data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
            } else {
                Messenger::Instance()->Send('lppa_approval', 'Lppa', 'view', 'html', 
                array($this->_POST,'Approval Data Gagal Dilakukan', $this->cssFail),Messenger::NextRequest);
            }
        } elseif($cek == "empty") {
            Messenger::Instance()->Send('lppa_approval', 'addLppa', 'view', 'html', 
            array($this->_POST,'Lengkapi Isian Data'),Messenger::NextRequest);
            return $this->pageInput . "&dataId=" . $this->encId;
        }elseif($cek == "exist") {
            Messenger::Instance()->Send('lppa_approval', 'addLppa', 'view', 'html', 
            array($this->_POST,'Data sudah ada'),Messenger::NextRequest);
            return $this->pageInput;
        }
        return $this->pageView;
    }

}

?>