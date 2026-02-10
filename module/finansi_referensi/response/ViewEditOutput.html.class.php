<?php
#doc
# package:     ViewEditOutput
# scope:       PUBLIC
# @created     ucil.619
# @Author      Eko Susilo
# @Created     2013-09-19
# @Modified    2013-09-19
# @Analysts    
# @copyright   Copyright (c) 2012 Gamatechno
#/doc
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/FinansiReferensi.class.php';

class ViewEditOutput extends HtmlResponse
{
   #   internal variables
   protected $_POST;
   protected $_GET;
   private $mObj;
   private $queryString    = '';
   #   Constructor
   function __construct ()
   {
      $this->mObj          = new FinansiReferensi();
      if(is_object($_POST)){
         $this->_POST      = $_POST->AsArray();
      }else{
         $this->_POST      = $_POST;
      }
      if(is_object($_GET)){
         $this->_GET       = $_GET->AsArray();
      }else{
         $this->_GET       = $_GET;
      }
      $this->queryString   = self::__getQueryString();
   }
   
   function TemplateModule(){
     $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
     'module/'.Dispatcher::Instance()->mModule.'/template/');
     $this->SetTemplateFile('view_edit_output.html');
   }
   
   function ProcessRequest(){
      $msg           = Messenger::Instance()->Receive(__FILE__);
      $dataId        = $this->_GET['data_id'];
      $outputData    = $this->mObj->ChangeKeyName($this->mObj->GetSubProgramById($dataId));
      $dataString    = array();
      
      $dataString['id']                = $outputData['id'];
      $dataString['ta_id']             = $outputData['thanggar_id'];
      $dataString['ta_nama']           = $outputData['thanggar_nama'];
      $dataString['kegiatan_id']       = $outputData['kegiatan_id'];
      $dataString['kegiatan']          = $outputData['kegiatan_nama'];
      $dataString['kegiatan_kode']     = $outputData['kegiatan_kode'];
      $dataString['kode']              = $outputData['kode'];
      $dataString['nama']              = $outputData['nama'];
      $dataString['rkakl_output_id']   = $outputData['rkakl_output_id'];
      $dataString['rkakl_output']      = $outputData['rkakl_output_nama'];

      if($msg){
         $messengerData          = $msg[0][0];
         $messengerMsg           = $msg[0][1];
         $messengerStyle         = $msg[0][2];
         $dataString['id']                = $messengerData['data_id'];
         $dataString['kegiatan_id']       = $messengerData['kegiatan_id'];
         $dataString['kegiatan']          = $messengerData['kegiatan'];
         $dataString['kegiatan_kode']     = $messengerData['kegiatan_kode'];
         $dataString['kode']              = $messengerData['kode'];
         $dataString['nama']              = $messengerData['nama'];
         $dataString['rkakl_output_id']   = $messengerData['rkakl_output_id'];
         $dataString['rkakl_output']      = $messengerData['rkakl_output'];
      }
      $return['data_string']        = $dataString;
      $return['messenger_msg']      = $messengerMsg;
      $return['messenger_style']    = $messengerStyle;
      return $return;
   }
   
   function ParseTemplate($data = null){
      $dataString       = $data['data_string'];
      $messengerStyle   = $data['messenger_style'];
      $messengerMsg     = $data['messenger_msg'];
      $urlReturn     = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'ProgramKegiatan',
         'view',
         'html'
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$this->queryString;
      $urlAction     = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'UpdateOutput',
         'do',
         'json'
      ).'&'.$this->queryString;
      $urlPopupKegiatan     = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'PopupKegiatan',
         'view',
         'html'
      ).'&'.$this->queryString;
      
      $this->mrTemplate->AddVar('content', 'URL_POPUP_KEGIATAN', $urlPopupKegiatan);
      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVars('content', $dataString, '');

      if($messengerMsg){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $messengerMsg);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $messengerStyle);
      }
   }

   /**
    * @param string  path_info url to be parsed, default null
    * @return string parsed_url based on Dispatcher::Instance()->getQueryString();
    */
   public function __getQueryString($pathInfo = null)
   {
      $parseUrl            = is_null($pathInfo) ? parse_url($_SERVER['QUERY_STRING']) : parse_url($pathInfo);
      $explodedUrl         = explode('&', $parseUrl['path']);
      foreach ($explodedUrl as $path) {
         if(preg_match('/^mod=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^sub=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^act=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^typ=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^ascomponent=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/uniqid=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
   
         list($key, $value)   = explode('=', $path);
         $requestData[$key]   = Dispatcher::Instance()->Decrypt($value);
      }
      if(method_exists(Dispatcher::Instance(), 'getQueryString') === true){
         $queryString         = Dispatcher::Instance()->getQueryString($requestData);
      }else{
         foreach ($requestData as $key => $value) {
            $query[$key]      = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString         = urldecode(http_build_query($query));
      }
      return $queryString;
   }
}
?>