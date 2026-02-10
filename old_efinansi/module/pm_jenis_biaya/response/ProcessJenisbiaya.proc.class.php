<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/pm_jenis_biaya/business/Jenisbiaya.class.php';

class ProcessJenisbiaya {

    var $POST;
    var $GET;
    var $Obj;
    var $pageView;
    var $pageInput;
    //css hanya dipake di view
    var $cssDone = "notebox-done";
    var $cssFail = "notebox-warning";
    var $return;
    var $decId;
    var $encId;

    public function __construct() {

        $this->POST = is_object($_POST) ? $_POST->AsArray() : $_POST;
        $this->GET = is_object($_GET) ? $_GET->AsArray() : $_GET;

        $prodi = Dispatcher::Instance()->Decrypt($this->GET['prodi']);

        if ($prodi == 'reguler') {
            $connectionId = 1;
        } else {
            $connectionId = 2;
        }

        $this->Obj = new Jenisbiaya($connectionId);

        $this->pageView = Dispatcher::Instance()->GetUrl('pm_jenis_biaya', 'jenisbiaya', 'view', 'html');
        $this->pageView .= '&prodi=' . Dispatcher::Instance()->Encrypt($prodi);
    }

    public function Check() {
        if (isset($this->POST['btnsimpan'])) {
            if (trim($this->_POST['id_coa']) == "") {
                $this->_POST['id_coa'] = null;
            }
            if (trim($this->_POST['jenisbiaya_kode']) == "" or trim($this->_POST['jenisbiaya_nama']) == "" or trim($this->_POST['jeniskeljns_nama']) == "") {
                return "empty";
            } else {
                return true;
            }
        }
        return false;
    }

    public function Order() {
        if ($this->decOrderId != '' && $this->decId != '') {
            $OrderMax = $this->Obj->GetMaxOrder();
            $OrderMin = $this->Obj->GetMinOrder();
            if ($_GET['order'] == 'up') {
                if ($this->decOrderId != $OrderMin['min_order']) {
                    $NewOrder = $this->decOrderId - 1;
                    $getOrder = $this->Obj->GetOrder($NewOrder);
                    $OldOrder = $getOrder['order_id'] + 1;
                }
            } elseif ($_GET['order'] == 'down') {
                if ($this->decOrderId != $OrderMax['max_order']) {
                    $NewOrder = $this->decOrderId + 1;
                    $getOrder = $this->Obj->GetOrder($NewOrder);
                    $OldOrder = $getOrder['order_id'] - 1;
                }
            }

            if ($NewOrder != '' OR $NewOrder != 0) {
                $this->Obj->StartTrans();
                $updateOrder = $this->Obj->UpdateOrder($NewOrder, $this->decId);
                $updateOrderOld = $this->Obj->UpdateOrder($OldOrder, $getOrder['id']);
                $update = $updateOrder & $updateOrderOld;
                if ($update) {
                    $msg = array(
                        1 => 'Set Order Jenis Biaya Berhasil',
                        $this->cssDone);
                    Messenger::Instance()->Send('pm_jenis_biaya', 'jenisbiaya', 'view', 'html', $msg, Messenger::NextRequest);
                } else {
                    $msg = array(
                        1 => 'Set Order Jenis Biaya Gagal',
                        $this->cssFail);
                    Messenger::Instance()->Send('pm_jenis_biaya', 'jenisbiaya', 'view', 'html', $msg, Messenger::NextRequest);
                }
                $this->Obj->EndTrans($update);
            }
            return $this->pageView;
        } else {
            $msg = array(
                1 => 'Set Order Jenis Biaya Gagal',
                $this->cssFail);
            Messenger::Instance()->Send('pm_jenis_biaya', 'jenisbiaya', 'view', 'html', $msg, Messenger::NextRequest);
        }
        return $this->pageView;
    }

    public function Update() {
        #echo '<pre>';
        #print_r($this->POST['jb']);
        #echo '</pre>';
        
        if($this->POST['btnsimpan']){
            $updateJenisbiaya = $this->Obj->DoUpdateJenisbiaya(
                $this->POST['jb']
            );

            if ($updateJenisbiaya === true) {
                Messenger::Instance()->Send('pm_jenis_biaya', 'jenisbiaya', 'view', 'html', array($this->_POST, 'Berhasil Melakukan Setting Tipe Pencatatan', $this->cssDone), Messenger::NextRequest);
            } else {
                Messenger::Instance()->Send('pm_jenis_biaya', 'jenisbiaya', 'view', 'html', array($this->_POST, 'Gagal Melakukan Setting Tipe Pencatatan', $this->cssFail), Messenger::NextRequest);
            }
        } 
        
        return $this->pageView;
    }

}

?>