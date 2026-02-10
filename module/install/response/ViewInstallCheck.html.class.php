<?php
/**
* @package ViewInstallCheck
* @copyright Copyright (c) PT Gamatechno Indonesia
* @author Dyan Galih <galih@gamatechno.com>
* @version 0.1
* @startDate 2010-10-25
* @lastUpdate 2010-10-25
* @description View Install Check
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/install/business/Install.class.php';

class ViewInstallCheck extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot').'module/install/template');
      $this->SetTemplateFile('view_install_check.html');
   }

   function ProcessRequest() {

      $objInstall = new Install();

      $return['tableReferensi'] = $objInstall->GetCountDataReferensi();
#      if(!$mhsObjFinansi->addConn)
      $arrDbConn = GTFWConfiguration::GetValue('application','db_conn');

      $arrModule = $objInstall->GetModule();
      #
      $j=0;
      foreach ($arrModule as $key => $value)
      {
         $file = GTFWConfiguration::GetValue('application','docroot').'module/'.
         $value['Module'].'/response/'.
         ucfirst($value['Action']).
         ucfirst($value['SubModule']).'.'.$value['Type'].'.class.php';
         if(!file_exists($file)){
            $return['filesNotFound'][$j]['file'] = $file;
            $return['filesNotFound'][$j]['id'] = $value['ModuleId'];
         }
         $j++;
      }


      for ($i=1;$i<count($arrDbConn);$i++)
      {
         $objInstallation[$i] = new Install($i);

         if(!$objInstallation[$i]->addConn){
            $status = "Gagal";
         }else{
            $status = "Sukses";
         }
         $return['koneksi'][$i-1]['info'] = "Koneksi ke $i ";
         $return['koneksi'][$i-1]['status'] = $status;
      }

      $return['database'] = $objInstall->CheckTriggers();

      return $return;
   }

   function ParseTemplate($data = NULL) {

      $this->mrTemplate->AddVar('check', 'CHECK_VALUE', 'Cek Database :');
      #check databases
      foreach ($data['database'] as $key => $value)
      {
         $this->mrTemplate->AddVar('check_list', 'CHECK_INFO', 'Kesalahan Definer berjumlah ');
         $this->mrTemplate->AddVar('check_list', 'CHECK_STATUS', $data['database'][0]['DEFINER']);
         $this->mrTemplate->parseTemplate('check_list', 'a');
      }
      $this->mrTemplate->parseTemplate('check', 'a');

      $this->mrTemplate->AddVar('check', 'CHECK_VALUE', 'Cek Konfigurasi Koneksi :');
      $this->mrTemplate->ClearTemplate('check_list');

      #koneksi
      if(!empty($data['koneksi'])){

         foreach ($data['koneksi'] as $key => $value)
         {
            $this->mrTemplate->AddVar('check_list', 'CHECK_INFO', $value['info']);
            $this->mrTemplate->AddVar('check_list', 'CHECK_STATUS', $value['status']);
            $this->mrTemplate->parseTemplate('check_list', 'a');
         }
      }

      $this->mrTemplate->parseTemplate('check', 'a');

      if(count($data['filesNotFound'])>0){
         $this->mrTemplate->AddVar('check', 'CHECK_VALUE', 'Cek File Yang Tidak Ada :');
         $this->mrTemplate->ClearTemplate('check_list');
         #file not found
         foreach ($data['filesNotFound'] as $key => $value)
         {
            $this->mrTemplate->AddVar('check_list', 'CHECK_INFO', 'Kesalahan Register Module ');
            $this->mrTemplate->AddVar('check_list', 'CHECK_STATUS', '['.$value['id'].'] - '.$value['file']);
            $this->mrTemplate->parseTemplate('check_list', 'a');
            $idModule[] = $value['id'];
         }
         $this->mrTemplate->parseTemplate('check', 'a');
         $idModule = implode("','",$idModule);
         $sql = "DELETE FROM gtfw_module WHERE ModuleId IN('".$idModule."')";
         $this->mrTemplate->AddVar('check', 'CHECK_VALUE', 'Query Delete nya :');
         $this->mrTemplate->ClearTemplate('check_list');
         $this->mrTemplate->AddVar('check_list', 'CHECK_INFO', 'Query ');
         $this->mrTemplate->AddVar('check_list', 'CHECK_STATUS', $sql);
         $this->mrTemplate->parseTemplate('check', 'a');
      }

      $this->mrTemplate->AddVar('check', 'CHECK_VALUE', 'Cek Empty Data Referensi :');
      $this->mrTemplate->ClearTemplate('check_list');

      foreach ($data['tableReferensi'] as $key => $value)
      {
         if($value['total']==0){
            $this->mrTemplate->AddVar('check_list', 'CHECK_INFO', 'Jumlah data table '.$value['nama_table'].' adalah');
            $this->mrTemplate->AddVar('check_list', 'CHECK_STATUS', ' '.$value['total']);
            $this->mrTemplate->parseTemplate('check_list', 'a');
         }
      }
      $this->mrTemplate->parseTemplate('check', 'a');
   }
}
?>