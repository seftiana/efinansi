<?php
#doc
# package:     ViewAddKegiatan
# scope:       PUBLIC
# @created     ucil.619
# @Author      Eko Susilo
# @Created     2013-09-13
# @Modified    2013-09-13
# @Analysts    Nanang Ruswianto
# @copyright   Copyright (c) 2012 Gamatechno
#/doc
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/FinansiReferensi.class.php';

class ViewAddKegiatan extends HtmlResponse
{
   #   internal variables
   private $mObj;
   protected $_POST;
   protected $_GET;
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
     $this->SetTemplateFile('view_add_kegiatan.html');
   }
   
   function ProcessRequest(){
      $msg              = Messenger::Instance()->Receive(__FILE__);
      $requestData      = array();
      $tahunAnggaran    = $this->mObj->GetTahunAnggaran(array('active' => true));

      $queryData['tahun_anggaran']  = Dispatcher::Instance()->Decrypt($this->_GET['tahun_anggaran']);
      $queryData['kegiatan_id']     = Dispatcher::Instance()->Decrypt($this->_GET['kegiatan_id']);
      $queryData['kegiatan']        = Dispatcher::Instance()->Decrypt($this->_GET['kegiatan']);
      $queryData['output_id']       = Dispatcher::Instance()->Decrypt($this->_GET['output_id']);
      $queryData['output']          = Dispatcher::Instance()->Decrypt($this->_GET['output']);
      $queryData['kode']            = Dispatcher::Instance()->Decrypt($this->_GET['kode']);
      $queryData['nama']            = Dispatcher::Instance()->Decrypt($this->_GET['nama']);
      $queryData['ta_label']        = Dispatcher::Instance()->Decrypt($this->_GET['ta_label']);

      foreach ($queryData as $key => $value) {
         $queryBuild[$key]          = Dispatcher::Instance()->Encrypt($value);
      }
      $queryString               = urldecode(http_build_query($queryBuild));
      
      $requestData['ta_id']      = $tahunAnggaran[0]['id'];
      $requestData['ta_name']    = $tahunAnggaran[0]['name'];

      if($msg){
         $messengerData          = $msg[0][0];
         $messengerMessage       = $msg[0][1];
         $messengerStyle         = $msg[0][2];

         $requestData['id']                  = $messengerData['data_id'];
         $requestData['kode']                = $messengerData['kode'];
         $requestData['nama']                = $messengerData['nama'];
         $requestData['sasaran_id']          = $messengerData['sasaran_id'];
         $requestData['sasaran_nama']        = $messengerData['sasaran_nama'];
         $requestData['indikator_program']   = $messengerData['indikator_program'];
         $requestData['strategi_program']    = $messengerData['strategi_program'];
         $requestData['kebijakan_program']   = $messengerData['kebijakan_program'];
      }

      $return['message']         = $messengerMessage;
      $return['style']           = $messengerStyle;
      $return['query_string']    = $queryString;
      $return['request_data']    = $requestData;
      return $return;
   }
   
   function ParseTemplate($data = null){
      $queryString         = $data['query_string'];
      $requestData         = $data['request_data'];
      $messengerMessage    = $data['message'];
      $messengerStyle      = $data['style'];
      $urlReturn           = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'ProgramKegiatan',
         'view',
         'html'
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;
      $urlAction     = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'AddKegiatan',
         'do',
         'json'
      ).'&'.$queryString;
      
      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVars('content', $requestData, '');

      if($messengerMessage){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $messengerMessage);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $messengerStyle);
      }
   }
}
?>