<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
 'module/pengaturan_alokasi_coa_akademik/business/AppAlokasiCoaAkademik.class.php';

class ViewInputAlokasiCoaAkademik extends HtmlResponse {
   var $Data;
   var $Pesan;
   
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot') .
        'module/pengaturan_alokasi_coa_akademik/template');
      $this->SetTemplateFile('input_alokasi_coa_akademik.html');
   }
   
   function ProcessRequest() {
      $idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
      $Obj = new AppAlokasiCoaAkademik();
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
      $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('pengaturan_alokasi_coa_akademik', 'addCoa', 'do', 'html') . "&dataId=" . Dispatcher::Instance()->Encrypt($data['id_klp_lap']));
      
      $this->mrTemplate->AddVar('content', 'URL_POP_UP_COA', Dispatcher::Instance()->GetUrl('pengaturan_alokasi_coa_akademik', 'PopUpCoa', 'view', 'html'));
   }
}
?>
