<?php
/**
* @package PenyesuaianSetting
* @copyright Copyright (c) PT Gamatechno Indonesia
* @author Dyan Galih <galih@gamatechno.com>
* @version 0.1
* @startDate 2010-09-11
* @lastUpdate 2010-09-11
* @description Penyesuaian Setting
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/penyesuaian_setting/business/PenyesuaianSetting.class.php';

class PenyesuaianSettingProc {
   var $cssDone = "notebox-done";
   var $cssFail = "notebox-warning";

   function __construct() {
      $this->_POST = $_POST->AsArray();
      $this->pageView = Dispatcher::Instance()->GetUrl('penyesuaian_setting', 'penyesuaianSetting', 'view', 'html');
      $this->obj = new PenyesuaianSetting;
   }

   function Check() {

		if (isset($_POST['btnsimpan'])) {
         if (!isset($this->_POST['COA'])) return "COA";
		   foreach ($this->_POST['COA'] as $coaId => $value)
         {
            if ($value['typeRekening'] == 'debet') $total_debet += $value['nominal'];
            elseif ($value['typeRekening'] == 'kredit') $total_kredit += $value['nominal'];
         }
         if ($total_debet != $total_kredit) return 'debetkredit';
         elseif (empty($this->_POST['nominal'])) $this->_POST['nominal'] = $total_debet;

         return true;
      }
      return false;
   }

   public function Add()
   {
      if(isset($_POST['cancel']))
         return $this->pageView;
      $cek = $this->Check();
		if($cek == true) {
		   $sett['kode'] = $_POST['setPenyesuaianKode'];
		   $sett['nama'] = $_POST['setPenyesuaianNama'];
		   $sett['jml'] = $_POST['setPenyesuaianTotalPenyesuaian'];
		   $sett['sisa'] = $_POST['setPenyesuaianTotalPenyesuaian'];
		   $sett['total'] = $this->_POST['nominal'];
		   $sett['user'] = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
         $this->obj->StartTrans();

		   $this->obj->InputSettingPenyesuaian($sett);

		   $id = $this->obj->GetMaxMstId();

		   $detil = $this->_POST['COA'];

		   $this->obj->InputSettingPenyesuaianDetil($detil,$id);
		   $this->obj->EndTrans(true);
		   Messenger::Instance()->Send('penyesuaian_setting', 'penyesuaianSetting', 'view', 'html', array($this->_POST, 'Penambahan Data Setting Jurnal Penyesuaian Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);

      }elseif($cek === "debetkredit") {
         //echo "666";
         Messenger::Instance()->Send('penyesuaian_setting', 'penyesuaianSetting', 'view', 'html', array($this->_POST,'Debet kredit tidak sama!', $this->cssFail),Messenger::NextRequest);
      }elseif($cek === "COA") {
         //echo "666";
         Messenger::Instance()->Send('penyesuaian_setting', 'penyesuaianSetting', 'view', 'html', array($this->_POST,'Kode Jurnal belum dipilih!', $this->cssFail),Messenger::NextRequest);
      }else {
         //gagal masukin data
         Messenger::Instance()->Send('penyesuaian_setting', 'penyesuaianSetting', 'view', 'html', array($this->_POST,'Gagal Menambah Data', $this->cssFail),Messenger::NextRequest);
      }
      return $this->pageView;
   }

   public function Update()
   {
      if(isset($_POST['cancel']))
         return $this->pageView;
      $cek = $this->Check();
		if($cek == true) {
		   $sett['kode'] = $_POST['setPenyesuaianKode'];
		   $sett['nama'] = $_POST['setPenyesuaianNama'];
		   $sett['jml'] = $_POST['setPenyesuaianTotalPenyesuaian'];
		   $sett['sisa'] = $_POST['setPenyesuaianTotalPenyesuaian'];
		   $sett['total'] = $this->_POST['nominal'];
		   $sett['user'] = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
		   $sett['id'] = Dispatcher::Instance()->Decrypt($_GET['id']);
         $this->obj->StartTrans();

		   $this->obj->UpdateSettingPenyesuaian($sett);

		   $detil = $this->_POST['COA'];

		   $this->obj->InputSettingPenyesuaianDetil($detil,$sett['id']);
		   $result = $this->obj->EndTrans(true);
		   if($result)
   		   Messenger::Instance()->Send('penyesuaian_setting', 'penyesuaianSetting', 'view', 'html', array($this->_POST, 'Perubahan Data Setting Jurnal Penyesuaian Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
         else
            Messenger::Instance()->Send('penyesuaian_setting', 'penyesuaianSetting', 'view', 'html', array($this->_POST,'Gagal Mengubah Data', $this->cssFail),Messenger::NextRequest);

      }elseif($cek === "debetkredit") {
         //echo "666";
         Messenger::Instance()->Send('penyesuaian_setting', 'penyesuaianSetting', 'view', 'html', array($this->_POST,'Debet kredit tidak sama!', $this->cssFail),Messenger::NextRequest);
      }elseif($cek === "COA") {
         //echo "666";
         Messenger::Instance()->Send('penyesuaian_setting', 'penyesuaianSetting', 'view', 'html', array($this->_POST,'Kode Jurnal belum dipilih!', $this->cssFail),Messenger::NextRequest);
      }else {
         //gagal masukin data
         Messenger::Instance()->Send('penyesuaian_setting', 'penyesuaianSetting', 'view', 'html', array($this->_POST,'Gagal Mengubah Data', $this->cssFail),Messenger::NextRequest);
      }
      return $this->pageView;
   }

   public function Delete()
   {
      $id = $_POST['idDelete'];

      $result = $this->obj->DeleteSettingJurnalPenyesuaian($id);

      if($result)
		   Messenger::Instance()->Send('penyesuaian_setting', 'penyesuaianSetting', 'view', 'html', array($this->_POST, 'Delete Data Setting Jurnal Penyesuaian Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
      else
         Messenger::Instance()->Send('penyesuaian_setting', 'penyesuaianSetting', 'view', 'html', array($this->_POST,'Gagal Menghapus Data', $this->cssFail),Messenger::NextRequest);

      return $this->pageView;
   }
}
?>