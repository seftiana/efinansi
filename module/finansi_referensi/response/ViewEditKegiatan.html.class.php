<?php
#doc
# package:     ViewEditKegiatan
# scope:       PUBLIC
# @created     ucil.619
# @Author      Eko Susilo
# @Created     2013-09-16
# @Modified    2013-09-16
# @Analysts    Nanang Ruswianto
# @copyright   Copyright (c) 2012 Gamatechno
#/doc
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/FinansiReferensi.class.php';

class ViewEditKegiatan extends HtmlResponse
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
     $this->SetTemplateFile('view_edit_kegiatan.html');
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
      $queryString                  = urldecode(http_build_query($queryBuild));

      if(isset($this->_GET['data_id']) && $this->_GET['data_id'] !== ''){
         $dataId                    = Dispatcher::Instance()->Decrypt($this->_GET['data_id']);
         $programRefData            = $this->mObj->ChangeKeyName($this->mObj->GetProgramRefById($dataId));
         $requestData['ta_id']      = $programRefData['ta_id'];
         $requestData['ta_name']    = $programRefData['thanggar_nama'];
         $requestData['id']         = $programRefData['id'];
         $requestData['kode']                = $programRefData['kode'];
         $requestData['nama']                = $programRefData['nama'];
         $requestData['sasaran_id']          = $programRefData['sasaran_id'];
         $requestData['sasaran_nama']        = $programRefData['sasaran'];
         $requestData['indikator_program']   = $programRefData['indikator'];
         $requestData['strategi_program']    = $programRefData['strategi'];
         $requestData['kebijakan_program']   = $programRefData['kebijakan'];
         $requestData['rkakl_kegiatan_id']   = $programRefData['rkakl_kegiatan_id'];
         $requestData['rkakl_kegiatan_kode'] = $programRefData['rkakl_kegiatan_kode'];
         $requestData['rkakl_kegiatan_nama'] = $programRefData['rkakl_kegiatan_nama'];
      }

      if($msg){
         $messengerData          = $msg[0][0];
         $messengerMessage       = $msg[0][1];
         $messengerStyle         = $msg[0][2];
         $programRefData            = $this->mObj->ChangeKeyName($this->mObj->GetProgramRefById($messengerData['data_id']));
         $requestData['ta_id']      = $programRefData['ta_id'];
         $requestData['ta_name']    = $programRefData['thanggar_nama'];
         $requestData['id']         = $programRefData['id'];

         $requestData['id']                  = $messengerData['data_id'];
         $requestData['kode']                = $messengerData['kode'];
         $requestData['nama']                = $messengerData['nama'];
         $requestData['sasaran_id']          = $messengerData['sasaran_id'];
         $requestData['sasaran_nama']        = $messengerData['sasaran_nama'];
         $requestData['indikator_program']   = $messengerData['indikator_program'];
         $requestData['strategi_program']    = $messengerData['strategi_program'];
         $requestData['kebijakan_program']   = $messengerData['kebijakan_program'];
         $requestData['rkakl_kegiatan_id']   = $messengerData['rkakl_kegiatan_id'];
         $requestData['rkakl_kegiatan_nama'] = $messengerData['rkakl_kegiatan'];
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
         'UpdateKegiatan',
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