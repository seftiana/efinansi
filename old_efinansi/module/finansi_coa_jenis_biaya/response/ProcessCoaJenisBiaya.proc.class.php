<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
    'module/finansi_coa_jenis_biaya/business/CoaJenisBiaya.class.php';

class ProcessCoaJenisBiaya {

    var $_POST;
    var $Obj;
    var $pageView;
    var $pageInput;
    //css hanya dipake di view
    var $cssAlert = "notebox-alert";
    var $cssDone = "notebox-done";
    var $cssFail = "notebox-warning";
    var $return;
    var $decId;
    var $encId;

    function __construct() {
        $this->Obj = new CoaJenisBiaya();
        $this->_POST = $_POST->AsArray();
        $this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
        $this->encId = Dispatcher::Instance()->Encrypt($this->decId);
        $this->pageView = Dispatcher::Instance()->GetUrl('finansi_coa_jenis_biaya', 'CoaJenisBiaya', 'view', 'html');
        $this->pageInput = Dispatcher::Instance()->GetUrl('finansi_coa_jenis_biaya', 'CoaJenisBiaya', 'view', 'html');
    }

    function Check() {
        if (isset($_POST['btnsimpan'])) {
            if (trim($this->_POST['jenis_biaya_id']) == "") {
                return "empty";
            } elseif (trim($this->_POST['jenis_biaya_nama']) == "") {
                return "empty";
            } elseif (trim($this->_POST['jenis_biaya_pembayaran_coa_id']) == "") {
                return "empty";
            } elseif (trim($this->_POST['jenis_biaya_potongan_coa_id']) == "") {
                return "empty";
            } elseif (trim($this->_POST['jenis_biaya_deposit_coa_id']) == "") {
                return "empty";
            } elseif (trim($this->_POST['jenis_biaya_piutang_coa_id']) == "") {
                return "empty";
            } else {
                /*
                  if(ereg("[0-9]", $this->_POST['kode'])) {
                  return "hurufonly";
                  } elseif(strlen(trim($this->_POST['kode'])) != 3) {
                  return "panjangkurang";
                  }
                 */
                return true;
            }
        }
        return false;
    }

    function Add() {
        $cek = $this->Check();
        if ($cek === true) {
            $tmbhCoaJenisBiaya = $this->Obj->DoAddData(
                    $this->_POST
                );
            if ($tmbhCoaJenisBiaya === true) {
                Messenger::Instance()->Send('finansi_coa_jenis_biaya', 'CoaJenisBiaya', 'view', 'html', 
                    array(NULL, 'Penambahan data Berhasil Dilakukan', $this->cssDone), 
                    Messenger::NextRequest
                );
            } else {
                Messenger::Instance()->Send('finansi_coa_jenis_biaya', 'CoaJenisBiaya', 'view', 'html', 
                    array($this->_POST, 'Gagal Menambah Data', $this->cssFail), 
                   Messenger::NextRequest
                );
            }
        } elseif ($cek == "empty") {
            Messenger::Instance()->Send('finansi_coa_jenis_biaya', 'CoaJenisBiaya', 'view', 'html', 
                array($this->_POST, 'Lengkapi Isian Data',  $this->cssAlert),
                Messenger::NextRequest);
            return $this->pageInput;
        }
        return $this->pageInput;
    }

    function Update() {
        $cek = $this->Check();
        if ($cek === true) {
            $updateCoaJenisBiaya = $this->Obj->DoUpdateData(
                    $this->_POST
            );
            if ($updateCoaJenisBiaya === true) {
                Messenger::Instance()->Send('finansi_coa_jenis_biaya', 'CoaJenisBiaya', 'view', 'html', 
                    array(null, 'Perubahan Data Berhasil Dilakukan', $this->cssDone), 
                    Messenger::NextRequest
                );
            } else {
                Messenger::Instance()->Send('finansi_coa_jenis_biaya', 'CoaJenisBiaya', 'view', 'html', 
                    array($this->_POST, 'Perubahan Data Gagal Dilakukan', $this->cssFail), 
                    Messenger::NextRequest
                );
            }
        } elseif ($cek == "empty") {
            Messenger::Instance()->Send('finansi_coa_jenis_biaya', 'CoaJenisBiaya', 'view', 'html', 
                array($this->_POST, 'Lengkapi Isian Data',  $this->cssAlert), Messenger::NextRequest);
            return $this->pageInput . "&dataId=" . $this->encId;
        }
        return $this->pageInput;
    }

    function Delete() {
        $arrId = $this->_POST['idDelete'];
        //print_r($this->_POST);
        $deleteArrData = $this->Obj->DoDeleteData($arrId);
        if ($deleteArrData === true) {
            Messenger::Instance()->Send(
                'finansi_coa_jenis_biaya', 
                'CoaJenisBiaya', 
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
                'finansi_coa_jenis_biaya', 
                'CoaJenisBiaya', 
                'view', 
                'html', 
                array(
                    $this->_POST, 
                    'Data Tidak Dapat Dihapus.',
                    $this->cssFail
                ), 
                Messenger::NextRequest);
        }
        return $this->pageInput;
    }

    function getPOST() {
        $data = false;

        if (isset($_POST['data'])) {
            if (is_object($_POST['data']))
                $data = $_POST['data']->AsArray();
            else
                $data = $_POST['data'];

            if (isset($data['datalist'])) {
                $i = 0;
                foreach ($data['datalist']['id'] as $key => $val) {
                    $data['datalist'][$i]['id'] = $val;
                    $data['datalist'][$i]['nama'] = $data['datalist']['nama'][$key];
                    $data['datalist'][$i]['kode'] = $data['datalist']['kode'][$key];
                    $data['datalist'][$i]['detail_id'] = $data['datalist']['detail_id'][$key];
                    $data['datalist'][$i]['isdebet'] = $data['datalist']['isdebet'][$key];
                    $i++;
                }
                unset($data['datalist']['id']);
                unset($data['datalist']['detail_id']);
                unset($data['datalist']['nama']);
                unset($data['datalist']['kode']);
                unset($data['datalist']['isdebet']);
            }//end if issetdata list		 

            if (isset($data['tambah'])) {
                $i = 0;
                foreach ($data['tambah']['id'] as $key => $val) {
                    $data['tambah'][$i]['id'] = $val;
                    $data['tambah'][$i]['nama'] = $data['tambah']['nama'][$key];
                    $data['tambah'][$i]['kode'] = $data['tambah']['kode'][$key];
                    $data['tambah'][$i]['detail_id'] = $data['tambah']['detail_id'][$key];
                    $data['tambah'][$i]['isdebet'] = $data['tambah']['isdebet'][$key];

                    $i++;
                }
                unset($data['tambah']['id']);
                unset($data['tambah']['detail_id']);
                unset($data['tambah']['nama']);
                unset($data['tambah']['kode']);
                unset($data['tambah']['isdebet']);
            }//end ifisset tambah
        }//end if isset post

        return $data;
    }

}

?>