<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/satuan_komponen/business/SatuanKomponen.class.php';

class ViewInputSatuanKomponen extends HtmlResponse {
   var $Data;
   var $Pesan;
   var $Op;
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/satuan_komponen/template');
      $this->SetTemplateFile('input_satuan_komponen.html');
   }

   function PrepareData(){
      //get data dari detail data atau dari form
      if ($_GET['kid'] != ''){
         $id = $_GET['kid'];
         $ObjSatuanKomponen = new SatuanKomponen();
         $rs = $ObjSatuanKomponen->GetSatuanKomponenFromId($id);
         $this->Op = $_GET['op'];
         $this->Data = array($rs[0]['satkompId'],$rs[0]['satkompNama']);
      }else
      {
         $msg = Messenger::Instance()->Receive(__FILE__);
         $post = $msg[0][0];
         $this->Op = $post['op'];
         //print_r($post);
         $this->Pesan = $msg[0][1];
         $this->Data = array($post['satuan_komponen_id'],$post['nama_satuan_komponen']);   
      }
   }

   function ProcessRequest() {
      //get data detail data
      $this->PrepareData();
       
      return $return;
   }

   function ParseTemplate($data = NULL) {
      //echo gethostbyname($_SERVER['REMOTE_ADDR']);
      if (isset ($this->Pesan)) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
      }

      //set aksi input
      $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('satuan_komponen', 'InputSatuanKomponen', 'do', 'html'));

      if (($_REQUEST['op']=='add') || ($this->Op == 'add')) {
         $this->mrTemplate->AddVar('content', 'OPERASI', 'add');
         $operasi="Tambah";
      } else {
         $this->mrTemplate->AddVar('content', 'OPERASI', 'edit');
         $operasi="Ubah";     
      }
      //set title  
      $this->mrTemplate->AddVar('content', 'JUDUL', $operasi);

      $this->mrTemplate->AddVar('content', 'SATUAN_KOMPONEN_ID', $this->Data[0]);
      $this->mrTemplate->AddVar('content', 'NAMA_SATUAN_KOMPONEN', $this->Data[1]);
    }
}
?>
