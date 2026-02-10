<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/uraian_belanja/business/UraianBelanja.class.php';

class ViewInputUraianBelanja extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/uraian_belanja/template');
      $this->SetTemplateFile('input_uraian_belanja.html');
   }
   
   function ProcessRequest() {
      $Obj = new UraianBelanja();

      if(isset($_REQUEST['uraianBelanja'])){
         $idDec = Dispatcher::Instance()->Decrypt($_REQUEST['uraianBelanja']);
         
         $dataUraianBelanja = $Obj->GetUraianBelanjaById($idDec);   
         $idJenis = $dataUraianBelanja['0']['jenis_belanja'];
         $return['uraianBelanja'] = $dataUraianBelanja['0'];      
         $return['idJenis'] = $idDec;
      }            
      $return['jenisBelanja'] = $dataJenisBelanja;
      
      if(isset($_GET['data'])){
         $data = Dispatcher::Instance()->Decrypt($_REQUEST['data']);  
         $data = explode('|',$data);
         $return['idJenis'] = $data['0'];
         $idJenis = $data['1'];
         $return['uraianBelanja'] = array("nama_uraian_belanja"=>$data['2']);
      }

      $dataJenisBelanja = $Obj->GetJenisBelanja();
      
      Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'jenisBelanja', 
         array('jenisBelanja',$dataJenisBelanja,$idJenis,'false',''), Messenger::CurrentRequest);
      
      if (isset($_GET['err']) ) {
         $return['error'] = Dispatcher::Instance()->Decrypt($_GET['err']);
      }
      
      if (isset($_GET['pilih']) ) {
         $return['pilih'] = Dispatcher::Instance()->Decrypt($_GET['pilih']);
      }
      return $return;
   }

   function ParseTemplate($data = NULL) {
      if (isset ($data['pilih'])) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', 'Data '. $data['pilih'] .' harus dipilih.');
      }
      
      if (isset ($data['error'])) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', 'Data '. $data['error'] .' tidak boleh kosong.');
      }
      
      
      
      $dataUraianBelanja = $data['uraianBelanja'];

      if (empty($data['idJenis'])) {
         $tambah="Tambah";
         $this->mrTemplate->AddVar('content', 'BTN_SUBMIT', 'btnsimpan');
      } else {
         $tambah="Ubah";     
         $this->mrTemplate->AddVar('content', 'BTN_SUBMIT', 'btnubah');
      }
          
      $this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
      $this->mrTemplate->AddVar('content', 'URAIAN_BELANJA', $dataUraianBelanja['nama_uraian_belanja']);
      $this->mrTemplate->AddVar('content', 'ID', $data['idJenis']);
      
      $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('uraian_belanja', 'inputUraianBelanja', 'do', 'html') );
      $this->mrTemplate->AddVar('content', 'URL_VIEW', Dispatcher::Instance()->GetUrl('uraian_belanja', 'uraianBelanja', 'view', 'html') ); 
   }
}
?>
