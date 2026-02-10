<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/lppa/business/Lppa.class.php';

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ProcessLppa
{

    protected $_POST;
    protected $Obj;
    protected $pageView;
    protected $pageInput;
    //css hanya dipake di view
    protected $cssDone = "notebox-done";
    protected $cssFail = "notebox-warning";

    protected $return;
    protected $decId;
    protected $encId;

    public $maxFileSize;
    public $fileTypeAllowed;
    public $typeAccepted;
    public $uploadDir;

    protected $message = '';

    function __construct() 
    {
        $this->Obj = new Lppa;
        $this->_POST = $this->Obj->_POST;
        $this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
        $this->encId = Dispatcher::Instance()->Encrypt($this->decId);
        $this->pageView = Dispatcher::Instance()->GetUrl('lppa', 'Lppa', 'view', 'html');
        $this->pageInput = Dispatcher::Instance()->GetUrl('lppa', 'AddLppa', 'view', 'html');
        $userUnitKerja = new UserUnitKerja();
        $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
        $this->Role = $userUnitKerja->GetRoleUser($userId);

        $this->maxFileSize            = 8 * 1024 * 1024;
        $this->fileTypeAllowed        = array(
            "application/x-gzip",
            "application/x-rar",
            "application/zip",
            "application/rtf",
            "application/msword",
            "application/wps-office.doc",
            "application/wps-office.docx",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "application/vnd.ms-excel",
            "application/wps-office.xls",
            "application/wps-office.xlsx",
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "application/pdf",
            "image/jpeg",
            "image/pjpeg",
            "image/jpeg",
            "image/pjpeg",
            "image/png"
        );
        $this->typeAccepted  = array('doc', 'docx', 'xls', 'xlsx', 'pdf', 'rar', 'zip', 'rtf', 'jpg', 'jpeg', 'png');
        $this->uploadDir     = realpath(GTFWConfiguration::GetValue('application', 'docroot') . "document/lppa/");

        // set data        
        $tanggalDay     = $this->_POST['tanggal_day'];
        $tanggalMon     = $this->_POST['tanggal_mon'];
        $tanggalYear    = $this->_POST['tanggal_year'];
        $this->_POST['tanggal'] = date('Y-m-d', mktime(0,0,0, $tanggalMon, $tanggalDay, $tanggalYear));
        //end
    }


    function Check() {
        // print_r($this->_POST);die();
        /*
        if(!$this->_POST['unitkerja'] || trim($this->_POST['unitkerja']=='-')) 
        $this->_POST['unitkerja'] = $this->_POST['satker'];
        
       */
       if (isset($_POST['btnsimpan'])) {
           
                $_lppaFile['file_name']        = $_FILES['lppa_file']['name'];
                $_lppaFile['file_tmp']         = $_FILES['lppa_file']['tmp_name'];
                $_lppaFile['file_size']        = $_FILES['lppa_file']['size'];
                $_lppaFile['file_type']        = $_FILES['lppa_file']['type'];
          
                if($this->_POST['unit_kerja_id'] == '') {
                    $this->message ='Definisikan Unit Kerja';
                    return "empty";         
                } elseif($this->_POST['realisasi_no'] == '') {
                    $this->message ='Definisikan FPA';
                    return "empty";             
                } elseif($this->_POST['penanggung_jawab'] == '') {                    
                    $this->message ='Isikan Penangung Jawab';
                    return "empty";             
                } elseif($this->_POST['mengetahui'] == '') {
                    $this->message ='Isikan Mengetahui';
                    return "empty";             
                }

                // Attachment
                if(empty($this->_POST['lppa_file_exist']) && empty($this->_POST['lppa_id'])) {
                    if(empty($_lppaFile['file_tmp'])){
                        $this->message ='File Attachment belum disertakan';
                        return "empty";
                    }
                }
          
                if(!empty($_lppaFile['file_tmp'])) {
                    if($_lppaFile['file_size'] > 0 && $dataList['file_size'] > $this->maxFileSize){
                        return "oversize";
                    }
            
                    if(!in_array($_lppaFile['file_type'], $this->fileTypeAllowed)){
                        return "file_type_not_allowed";
                    }
            
                    if(!is_writable($this->uploadDir)){
                        return "directory_not_available";
                    }
                }
                // end - Attachmen
            
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

                $_lppaFile['file_name']        = $_FILES['lppa_file']['name'];
                $_lppaFile['file_tmp']         = $_FILES['lppa_file']['tmp_name'];
                $_lppaFile['file_size']        = $_FILES['lppa_file']['size'];
                $_lppaFile['file_type']        = $_FILES['lppa_file']['type'];

                $getMaxId   = $this->Obj->GetMaxId();
                $dataId     = $getMaxId != NULL ? $getMaxId['max_id'] : 0;
    
                // Upload File
                foreach (glob($this->uploadDir.'/LPPA_'.$dataId.'_*') as $filename) {
                   unlink($filename);
                }
                $timestamp  = date('YmdHis', time());
                $ext        = pathinfo($_lppaFile['file_name'], PATHINFO_EXTENSION);
                $newFile    = 'LPPA_' . $dataId . '_' . $timestamp .'.' . $ext;
    
                if(move_uploaded_file($_lppaFile['file_tmp'], $this->uploadDir.'/'.$newFile)) {
                   $processUpload    = $this->Obj->DoUpdateFile($newFile, $dataId);
                } else {
                   $processUpload    = false;
                }
                // end - Upload File
            }
             
            if($addLppa === true && $processUpload === true) {
                Messenger::Instance()->Send('lppa', 'Lppa', 'view', 'html', 
                array($this->_POST,'Penambahan data berhasil dilakukan : '.$cekRowData, 
                $this->cssDone),Messenger::NextRequest);

            } elseif($addLppa === true && $processUpload === false) {
                Messenger::Instance()->Send('lppa', 'Lppa', 'view', 'html', 
                array($this->_POST,'Penambahan data berhasil dilakukan<br/> Proses upload file gagal dilakukan',
                $this->cssDone),Messenger::NextRequest);
                
            } else {
                Messenger::Instance()->Send('lppa', 'Lppa', 'view', 'html', 
                array($this->_POST,'Proses penambahan data gagal', $this->cssFail),Messenger::NextRequest);
            }
            
        } elseif($cek == "empty") {            
            Messenger::Instance()->Send('lppa', 'addLppa', 'view', 'html', 
            array($this->_POST, $this->message),Messenger::NextRequest);
            return $this->pageInput;

        } elseif($cek == "oversize") {
            Messenger::Instance()->Send('lppa', 'addLppa', 'view', 'html', 
            array($this->_POST,'Ukuran file yang Anda upload terlalu besar.'),Messenger::NextRequest);
            return $this->pageInput;
            
        } elseif($cek == "file_type_not_allowed") {
            Messenger::Instance()->Send('lppa', 'addLppa', 'view', 'html', 
            array($this->_POST,'File yang bisa di upload adalah : '. implode(',', $this->typeAccepted)),Messenger::NextRequest);
            return $this->pageInput;
            
        } elseif($cek == "directory_not_available") {
            Messenger::Instance()->Send('lppa', 'addLppa', 'view', 'html', 
            array($this->_POST,'Cek directory tujuan upload. Pastikan directory tujuan upload writable dan readable'),Messenger::NextRequest);
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

                $_lppaFile['file_name']        = $_FILES['lppa_file']['name'];
                $_lppaFile['file_tmp']         = $_FILES['lppa_file']['tmp_name'];
                $_lppaFile['file_size']        = $_FILES['lppa_file']['size'];
                $_lppaFile['file_type']        = $_FILES['lppa_file']['type'];

                $dataId     = $this->_POST['lppa_id'];
    
                // Upload File
                foreach (glob($this->uploadDir.'/LPPA_'.$dataId.'_*') as $filename) {
                   unlink($filename);
                }
                $timestamp  = date('YmdHis', time());
                $ext        = pathinfo($_lppaFile['file_name'], PATHINFO_EXTENSION);
                $newFile    = 'LPPA_' . $dataId . '_' . $timestamp .'.' . $ext;
                
                if(!empty($_lppaFile['file_tmp'])) {
                    if(move_uploaded_file($_lppaFile['file_tmp'], $this->uploadDir.'/'.$newFile)) {
                        $processUpload    = $this->Obj->DoUpdateFile($newFile, $dataId);
                    } else {
                        $processUpload    = false;
                    }
                } else {
                    $processUpload    = true;
                }
                // end - Upload File
            }
             
            if($updateLppa === true && $processUpload === true) {
                Messenger::Instance()->Send('lppa', 'Lppa', 'view', 'html', 
                array($this->_POST,'Perubahan data berhasil dilakukan', 
                $this->cssDone),Messenger::NextRequest);

            } elseif($updateLppa === true && $processUpload === false) {
                Messenger::Instance()->Send('lppa', 'Lppa', 'view', 'html', 
                array($this->_POST,'Perubahan data berhasil dilakukan<br/> Proses upload file gagal dilakukan',
                $this->cssDone),Messenger::NextRequest);
                
            } else {
                Messenger::Instance()->Send('lppa', 'Lppa', 'view', 'html', 
                array($this->_POST,'Proses perubahan data gagal', $this->cssFail),Messenger::NextRequest);
            }

        } elseif($cek == "empty") {
            Messenger::Instance()->Send('lppa', 'addLppa', 'view', 'html', 
            array($this->_POST, $this->message),Messenger::NextRequest);
            return $this->pageInput . "&dataId=" . $this->encId;
            
        } elseif($cek == "oversize") {
            Messenger::Instance()->Send('lppa', 'addLppa', 'view', 'html', 
            array($this->_POST,'Ukuran file yang Anda upload terlalu besar.'),Messenger::NextRequest);
            return $this->pageInput . "&dataId=" . $this->encId;
            
        } elseif($cek == "file_type_not_allowed") {
            Messenger::Instance()->Send('lppa', 'addLppa', 'view', 'html', 
            array($this->_POST,'File yang bisa di upload adalah : '. implode(',', $this->typeAccepted)),Messenger::NextRequest);
            return $this->pageInput . "&dataId=" . $this->encId;
            
        } elseif($cek == "directory_not_available") {
            Messenger::Instance()->Send('lppa', 'addLppa', 'view', 'html', 
            array($this->_POST,'Cek directory tujuan upload. Pastikan directory tujuan upload writable dan readable'),Messenger::NextRequest);
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
            array($this->_POST,'Penghapusan data berhasil dilakukan', $this->cssDone),Messenger::NextRequest);
        } else {
            Messenger::Instance()->Send('lppa', 'Lppa', 'view', 'html', 
            array($this->_POST, 'Proses penghapusan data gagal', $this->cssFail),Messenger::NextRequest);
        }
        return $this->pageView;
    }
}

?>