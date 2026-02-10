<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/jurnal_kode/business/KodeJurnal.class.php';

class ProcessKodeJurnal {

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
        $this->Obj = new KodeJurnal();
        $this->_POST = $_POST->AsArray();
        $this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
        $this->encId = Dispatcher::Instance()->Encrypt($this->decId);
        $this->pageView = Dispatcher::Instance()->GetUrl('jurnal_kode', 'kodeJurnal', 'view', 'html');
        $this->pageInput = Dispatcher::Instance()->GetUrl('jurnal_kode', 'inputKodeJurnal', 'view', 'html');
    }

    function Check() {
        if (isset($_POST['btnsimpan'])) {
            if (trim($this->_POST['kode']) == "") {
                return "empty";
            } elseif (trim($this->_POST['nama']) == "") {
                return "empty";
            } elseif (trim($this->_POST['jenis_biaya_id']) == "") {
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
            $tmbhKodeJurnal = $this->Obj->DoAddData(
                    strtoupper($this->_POST['kode']), 
                    $this->_POST['nama'], 
                    $this->getPOST(),
                    $this->_POST['status_aktif'],
                    $this->_POST['jenis_biaya_id'],
                    $this->_POST['jenis_biaya_nama'],
                    $this->_POST['metode_catat']
                );
            if ($tmbhKodeJurnal === true) {
                Messenger::Instance()->Send('jurnal_kode', 'kodeJurnal', 'view', 'html', array($this->_POST, 'Penambahan data Berhasil Dilakukan', $this->cssDone), Messenger::NextRequest);
            } else {
                Messenger::Instance()->Send('jurnal_kode', 'kodeJurnal', 'view', 'html', array($this->_POST, 'Gagal Menambah Data', $this->cssFail), Messenger::NextRequest);
            }
        } elseif ($cek == "empty") {
            Messenger::Instance()->Send('jurnal_kode', 'inputKodeJurnal', 'view', 'html', array($this->_POST, 'Lengkapi Isian Data'), Messenger::NextRequest);
            return $this->pageInput;
        }
        return $this->pageView;
    }

    function Update() {
        $cek = $this->Check();
        if ($cek === true) {
            $updateKodeJurnal = $this->Obj->DoUpdateData(
                strtoupper($this->_POST['kode']), 
                $this->_POST['nama'], 
                $this->decId, 
                $this->getPOST(), 
                $this->_POST['status_aktif'],
                $this->_POST['jenis_biaya_id'],
                $this->_POST['jenis_biaya_nama'],
                $this->_POST['metode_catat']
            );
            if ($updateKodeJurnal === true) {
                Messenger::Instance()->Send('jurnal_kode', 'kodeJurnal', 'view', 'html', array($this->_POST, 'Perubahan Data Berhasil Dilakukan', $this->cssDone), Messenger::NextRequest);
            } else {
                Messenger::Instance()->Send('jurnal_kode', 'kodeJurnal', 'view', 'html', array($this->_POST, 'Perubahan Data Gagal Dilakukan', $this->cssFail), Messenger::NextRequest);
            }
        } elseif ($cek == "empty") {
            Messenger::Instance()->Send('jurnal_kode', 'inputKodeJurnal', 'view', 'html', array($this->_POST, 'Lengkapi Isian Data'), Messenger::NextRequest);
            return $this->pageInput . "&dataId=" . $this->encId;
        }
        return $this->pageView;
    }

    function Delete() {
        $arrId = $this->_POST['idDelete'];
        //print_r($this->_POST);
        $deleteArrData = $this->Obj->DoDeleteDataByArrayId($arrId);
        if ($deleteArrData === true) {
            Messenger::Instance()->Send('jurnal_kode', 'kodeJurnal', 'view', 'html', array($this->_POST, 'Penghapusan Data Berhasil Dilakukan', $this->cssDone), Messenger::NextRequest);
        } else {
            //jika masuk disini, berarti PASTI ada salah satu atau lebih data yang gagal dihapus
            for ($i = 0; $i < sizeof($arrId); $i++) {
                $deleteData = false;
                $deleteData = $this->Obj->DoDeleteDataById($arrId[$i]);
                if ($deleteData === true)
                    $sukses += 1;
                else
                    $gagal += 1;
            }
            Messenger::Instance()->Send('jurnal_kode', 'kodeJurnal', 'view', 'html', array($this->_POST, $gagal . ' Data Tidak Dapat Dihapus.', $this->cssFail), Messenger::NextRequest);
        }
        return $this->pageView;
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