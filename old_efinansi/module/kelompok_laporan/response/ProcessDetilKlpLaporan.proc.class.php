<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/kelompok_laporan/business/AppKlpLaporan.class.php';
class ProcessDetilKlpLaporan {

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
      $this->Obj        = new AppKelpLaporan();
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->decId      = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
      $this->encId      = Dispatcher::Instance()->Encrypt($this->decId);
      $this->pageView   = Dispatcher::Instance()->GetUrl(
         'kelompok_laporan',
         'detilKlpLaporan',
         'view',
         'html'
      ).'&dataId='.Dispatcher::Instance()->Encrypt($this->_GET['dataId']);

      $this->pageInput  = Dispatcher::Instance()->GetUrl(
         'kelompok_laporan',
         'inputDetilKlpLaporan',
         'view',
         'html'
      ).'&dataId='.Dispatcher::Instance()->Encrypt($this->_GET['dataId']);
   }

   function Check() {
      if (isset($_POST['btnsimpan'])) {
		  //print_r($this->_POST['data']['coa_lap']);
         if(empty($this->_POST['data']['coa_lap'])) {
            return "empty";
         } else {
            return true;
         }
      }
      return false;
   }

   function Add() {
      $cek = $this->Check();
      if($cek === true) {
         for($i=0; $i<sizeof($this->_POST['data']['coa_lap']['id']); $i++) {
            $addDetilKlpLap = $this->Obj->DoAddDetilData($this->_GET['dataId'], $this->_POST['data']['coa_lap']['id'][$i],$this->_POST['data']['coa_lap']['type_coa'][$i]);
         }
         #$addKlpLap = $this->Obj->DoAddData($this->_POST['klp_lap'], $this->_POST['bentuk_transaksi'], $this->_POST['is_tambah']);
         if ($addDetilKlpLap === true) {
            Messenger::Instance()->Send('kelompok_laporan', 'detilKlpLaporan', 'view', 'html'.'&dataId='.Dispatcher::Instance()->Encrypt($this->_GET['dataId']), array($this->_POST,'Penambahan data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
         } else {
            Messenger::Instance()->Send('kelompok_laporan', 'detilKlpLaporan', 'view', 'html'.'&dataId='.Dispatcher::Instance()->Encrypt($this->_GET['dataId']), array($this->_POST,'Gagal Menambah Data', $this->cssFail),Messenger::NextRequest);
         }
      } elseif($cek == "empty") {
         Messenger::Instance()->Send('kelompok_laporan', 'inputDetilKlpLaporan', 'view', 'html'.'&dataId='.Dispatcher::Instance()->Encrypt($this->_GET['dataId']), array($this->_POST,'Anda belum memilih coa'),Messenger::NextRequest);
         return $this->pageInput;
      }
      return $this->pageView;
   }

   function Delete() {
      $arrId         = $this->_POST['idDelete'];
      $deleteArrData = $this->Obj->DoDeleteDetilDataByArrayId($arrId);

      if((bool)$deleteArrData == true) {
         Messenger::Instance()->Send(
            'kelompok_laporan',
            'DetilKlpLaporan',
            'view',
            'html',
            array(
               NULL,
               'Penghapusan Data berhasil Dilakukan',
               $this->cssDone
            ), Messenger::NextRequest
         );
      } else {
         //jika masuk disini, berarti PASTI ada salah satu atau lebih data yang gagal dihapus
         for($i=0;$i<sizeof($arrId);$i++) {
            $deleteData    = false;
            $deleteData    = $this->Obj->DoDeleteDetilDataById($arrId[$i]);
            if($deleteData === false){
               $err[]      = 'Data dengan ID '.$arrId[$i].' Gagal di hapus';
            }
         }

         if(isset($err)){
            $message       = implode('<br />', $err);
            $style         = $this->cssFail;
         }else{
            $message       = 'Penghapusan data berhasil di jalankan';
            $style         = $this->cssDone;
         }
         Messenger::Instance()->Send(
            'kelompok_laporan',
            'DetilKlpLaporan',
            'view',
            'html',
            array(
               null,
               $message,
               $style
            ),Messenger::NextRequest);
      }
      return $this->pageView;
   }
}
?>
