<?php
#doc
# package:     ViewAddOutput
# scope:       PUBLIC
# @created     ucil.619
# @Author      Eko Susilo
# @Created     2013-09-16
# @Modified    2013-09-16
# @Analysts    
# @copyright   Copyright (c) 2012 Gamatechno
#/doc
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/FinansiReferensi.class.php';

class ViewAddOutput extends HtmlResponse
{
   #   internal variables
   protected $_POST;
   protected $_GET;
   private $mObj;
   #   Constructor
   function __construct ()
   {
      $this->mObj       = new FinansiReferensi();
      if(is_object($_POST)){
         $this->_POST   = $_POST->AsArray();
      }else{
         $this->_POST   = $_POST;
      }
      if(is_object($_GET)){
         $this->_GET    = $_GET->AsArray();
      }else{
         $this->_GET    = $_GET;
      }
   }
   
   function TemplateModule(){
     $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
     'module/'.Dispatcher::Instance()->mModule.'/template/');
     $this->SetTemplateFile('view_add_output.html');
   }
   
   function ProcessRequest(){
      $tahunAnggaranAktif     = $this->mObj->GetTahunAnggaran(array('active' => true));
      $dataString             = array();
      $requestData            = array();
      $msg                    = Messenger::Instance()->Receive(__FILE__);

      $requestData['tahun_anggaran']   = Dispatcher::Instance()->Decrypt($this->_GET['tahun_anggaran']);
      $requestData['kegiatan_id']      = Dispatcher::Instance()->Decrypt($this->_GET['kegiatan_id']);
      $requestData['kegiatan']         = Dispatcher::Instance()->Decrypt($this->_GET['kegiatan']);
      $requestData['output_id']        = Dispatcher::Instance()->Decrypt($this->_GET['output_id']);
      $requestData['output']           = Dispatcher::Instance()->Decrypt($this->_GET['output']);
      $requestData['kode']             = Dispatcher::Instance()->Decrypt($this->_GET['kode']);
      $requestData['nama']             = Dispatcher::Instance()->Decrypt($this->_GET['nama']);
      $requestData['ta_label']         = Dispatcher::Instance()->Decrypt($this->_GET['ta_label']);
      $requestData['page']             = Dispatcher::Instance()->Decrypt($this->_GET['page']);

      foreach ($requestData as $key => $value) {
         $query[$key]      = Dispatcher::Instance()->Encrypt($value);
      }
      $queryString         = urldecode(http_build_query($query));

      $dataString['ta_id']          = $tahunAnggaranAktif[0]['id'];
      $dataString['ta_nama']        = $tahunAnggaranAktif[0]['name'];

      if($msg){
         $messengerData       = $msg[0][0];
         $messengerMsg        = $msg[0][1];
         $messengerStyle      = $msg[0][2];

         $dataString['kegiatan_id']       = $messengerData['kegiatan_id'];
         $dataString['kegiatan']          = $messengerData['kegiatan'];
         $dataString['kegiatan_kode']     = $messengerData['kegiatan_kode'];
         $dataString['kode']              = $messengerData['kode'];
         $dataString['nama']              = $messengerData['nama'];
         $dataString['rkakl_output_id']   = $messengerData['rkakl_output_id'];
         $dataString['rkakl_output']      = $messengerData['rkakl_output'];
      }

      $return['messenger_msg']      = $messengerMsg;
      $return['messenger_style']    = $messengerStyle;
      $return['data_string']        = $dataString;
      $return['query_string']       = $queryString;
      return $return;
   }
   
   function ParseTemplate($data = null){
      $messengerStyle   = $data['messenger_style'];
      $messengerMsg     = $data['messenger_msg'];
      $dataString       = $data['data_string'];
      $queryString      = $data['query_string'];
      $urlReturn        = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'ProgramKegiatan',
         'view',
         'html'
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;
      $urlPopupKegiatan    = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'PopupKegiatan',
         'view',
         'html'
      );
      $urlAction        = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'AddOutput',
         'do',
         'json'
      ).'&'.$queryString;
            
      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVar('content', 'URL_POPUP_KEGIATAN', $urlPopupKegiatan);
      $this->mrTemplate->AddVars('content', $dataString, '');

      if($messengerMsg){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $messengerMsg);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $messengerStyle);
      }
   }
}
?>