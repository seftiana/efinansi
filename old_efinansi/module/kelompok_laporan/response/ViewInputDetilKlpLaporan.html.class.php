<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/kelompok_laporan/business/AppKlpLaporan.class.php';

class ViewInputDetilKlpLaporan extends HtmlResponse {
   var $Data;
   var $Pesan;
   
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 'module/kelompok_laporan/template');
      $this->SetTemplateFile('input_detil_klp_laporan.html');
   }
   
   function ProcessRequest() {
      $idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
      $Obj = new AppKelpLaporan();
      $msg = Messenger::Instance()->Receive(__FILE__);
      $this->Pesan = $msg[0][1];
      $this->Data = $msg[0][0];

      #$data_klp_lap = $Obj->GetDataDetilById($idDec);

      $return['id_klp_lap'] = $idDec;
      #$return['data_klp_lap'] = $data_klp_lap;
      return $return;
   }

   function ParseTemplate($data = NULL) {
      if ($this->Pesan) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
      }
      #$klp_lap = $data['data_klp_lap'];
      
      $this->mrTemplate->AddVar('content', 'JUDUL', 'Tambah');
      $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('kelompok_laporan', 'addDetilKlpLaporan', 'do', 'html') . "&dataId=" . Dispatcher::Instance()->Encrypt($data['id_klp_lap']));
      
      $this->mrTemplate->AddVar('content', 'URL_POP_UP_COA', Dispatcher::Instance()->GetUrl('kelompok_laporan', 'PopUpCoa', 'view', 'html'));
   }
}
?>
