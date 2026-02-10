<?php
/**
* ================= doc ====================
* FILENAME     : ViewEditSubAccountKeempat.html.class.php
* @package     : ViewEditSubAccountKeempat
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-02-18
* @Modified    : 2015-02-18
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/sub_account_keempat/business/SubAccountKeempat.class.php';

class ViewEditSubAccountKeempat extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/sub_account_keempat/template/');
      $this->SetTemplateFile('view_edit_sub_account_keempat.html');
   }

   function ProcessRequest(){
      $messenger     = Messenger::Instance()->Receive(__FILE__);
      $mObj          = new SubAccountKeempat();
      $queryString   = $mObj->_getQueryString();
      $message       = $style   = $messengerData = NULL;
      $requestData   = array();
      $defaultData   = $mObj->doCheckDefaultSubAccount();
      $dataId        = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
      $dataSubAkun   = $mObj->ChangeKeyName($mObj->getDataDetail($dataId));
      $requestData['patern']  = $defaultData['patern'];
      $requestData['type']    = 'add';
      if($defaultData['result'] === false){
         $requestData['nama'] = 'default';
         $requestData['type'] = 'default';
      }
      if(!empty($dataSubAkun)){
         $requestData['id']   = $dataSubAkun['id'];
         $requestData['kode'] = $dataSubAkun['kode'];
         $requestData['nama'] = $dataSubAkun['nama'];

         if(preg_match('/\b(default)\b/', strtolower($dataSubAkun['nama']))){
            $requestData['nama'] = 'default';
            $requestData['type'] = 'default';
         }
      }
      if($messenger){
         $messengerData       = $messenger[0][0];
         $message             = $messenger[0][1];
         $style               = $messenger[0][2];
         $requestData['id']   = $messengerData['dataId'];
         $requestData['kode'] = $messengerData['kode'];
         $requestData['nama'] = $messengerData['nama'];
      }

      $return['query_string']    = $queryString;
      $return['message']         = $message;
      $return['style']           = $style;
      $return['request_data']    = $requestData;

      return $return;
   }

   function ParseTemplate($data = null){
      $queryString         = $data['query_string'];
      $message             = $data['message'];
      $style               = $data['style'];
      $requestData         = $data['request_data'];

      $urlReturn           = Dispatcher::Instance()->GetUrl(
         'sub_account_keempat',
         'SubAccountKeempat',
         'view',
         'html'
      ).'&search=1&'.$queryString;
      $urlAction           = Dispatcher::Instance()->GetUrl(
         'sub_account_keempat',
         'updateSubAccountKeempat',
         'do',
         'json'
      ).'&'.$queryString;

      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('content_type', 'DATA_TYPE', strtoupper($requestData['type']));
      $this->mrTemplate->AddVar('content_type', 'NAMA', $requestData['nama']);

      if ($message) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }
   }
}
?>