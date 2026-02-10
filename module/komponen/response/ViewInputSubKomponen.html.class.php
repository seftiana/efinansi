<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/komponen/business/SubKomponen.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/komponen/business/Komponen.class.php';

class ViewInputSubKomponen extends HtmlResponse {
   var $Data;
   var $Pesan;
   var $Op;
   var $Post;
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/komponen/template');
      $this->SetTemplateFile('input_sub_komponen.html');
   }

   function PrepareData(){
      //get data dari detail data atau dari form
      if ($_POST['komponen_id'] != '')
         $komp_id = $_POST['komponen_id'];
      else if ($_GET['kid'] != '')
         $komp_id = $_GET['kid'];
      if ($_GET['skid'] != ''){
         $id = $_GET['skid'];
         $ObjSubKomponen = new SubKomponen();
         $rs = $ObjSubKomponen->GetSubKomponenFromId($id);
         //print_r($rs);
         $this->Op = $_GET['op'];
         $this->Data = array($rs[0]['subkompId'],$rs[0]['subkompNama'],$rs[0]['subkompBiaya'],$rs[0]['subkompKompId']);
      }else
      {
         $msg = Messenger::Instance()->Receive(__FILE__);
         $post = $msg[0][0];
         $this->Post = $post;
         $this->Op = $post['op'];
         //print_r($post);
         $this->Pesan = $msg[0][1];
         $this->Data = array($post['sub_komponen_id'],$post['sub_nama_komponen'],$post['biaya'],$post['komponen_id']); 
      }
   }

   function ProcessRequest() {
      //get data detail data
      $this->PrepareData();
      if ($_POST['komponen_id'] != '')
         $komp_id = $_POST['komponen_id'];
      else if ($_GET['kid'] != '')
         $komp_id = $_GET['kid'];
      else
         $komp_id = $this->Post['komponen_id'];

      //get data komponen from id
      $ObjKomponen = new Komponen();
      $return['komponen'] = $ObjKomponen->GetKomponenFromId($komp_id);

      return $return;
   }

   function ParseTemplate($data = NULL) {
      if (isset ($this->Pesan)) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
      }

      //set aksi input
      $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('komponen', 'InputSubKomponen', 'do', 'html'));

      $this->mrTemplate->AddVar('content', 'URL_LIST_KOMPONEN', Dispatcher::Instance()->GetUrl('komponen', 'Komponen', 'view', 'html'));

      if(!empty($data['komponen'])){
         $this->mrTemplate->AddVar('content', 'NAMA_KOMPONEN', $data['komponen'][0]['kompNama']);
         $this->mrTemplate->AddVar('content', 'SK_KOMPONEN', $data['komponen'][0]['kompSK']);
         $this->mrTemplate->AddVar('content', 'URL_LIST_SUB_KOMPONEN', Dispatcher::Instance()->GetUrl('komponen', 'SubKomponen', 'view', 'html').'&kid='.$data['komponen'][0]['kompId']);
         $this->mrTemplate->AddVar('content', 'KOMPONEN_ID', $data['komponen'][0]['kompId']);
      }

      if (($_REQUEST['op']=='add') || ($this->Op == 'add')) {
         $this->mrTemplate->AddVar('content', 'OPERASI', 'add');
         $operasi="Tambah";
      } else {
         $this->mrTemplate->AddVar('content', 'OPERASI', 'edit');
         $operasi="Ubah";     
      }
      //set title  
      $this->mrTemplate->AddVar('content', 'JUDUL', $operasi);

      $this->mrTemplate->AddVar('content', 'SUB_KOMPONEN_ID', $this->Data[0]);
      $this->mrTemplate->AddVar('content', 'NAMA_SUB_KOMPONEN', $this->Data[1]);
      $this->mrTemplate->AddVar('content', 'BIAYA', $this->Data[2]);
    }
}
?>
