<?php

/**
 * class ViewInputSubAccountKedua
 * @package sub_account_kedua
 * @copyright 2011 gamatechno
 */

require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/sub_account_kedua/business/SubAccountKedua.class.php';

/**
 * class ViewInputSubAccountKedua
 * untuk menangani tampilan input sub account kedua
 * @access public
 */
class ViewInputSubAccountKedua extends HtmlResponse
{
   protected $mData;
   protected $mPesan;

   public function TemplateModule()
   {
      $this->SetTemplateBasedir($this->mrConfig->mApplication['docroot'].
            'module/sub_account_kedua/template');
      $this->SetTemplateFile('input_sub_account_kedua.html');
   }

   public function ProcessRequest()
   {
      $messenger     = Messenger::Instance()->Receive(__FILE__);
      $mObj          = new SubAccountKedua();
      $queryString   = $mObj->_getQueryString();
      $message       = $style   = $messengerData = NULL;
      $requestData   = array();
      $defaultData   = $mObj->doCheckDefaultSubAccount();
      $requestData['patern']  = $defaultData['patern'];
      $requestData['type']    = 'add';
      if($defaultData['result'] === false){
         $requestData['nama'] = 'default';
         $requestData['type'] = 'default';
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

   public function ParseTemplate($data = NULL)
   {
      $queryString         = $data['query_string'];
      $message             = $data['message'];
      $style               = $data['style'];
      $requestData         = $data['request_data'];

      $urlReturn           = Dispatcher::Instance()->GetUrl(
         'sub_account_kedua',
         'SubAccountKedua',
         'view',
         'html'
      ).'&search=1&'.$queryString;
      $urlAction           = Dispatcher::Instance()->GetUrl(
         'sub_account_kedua',
         'addSubAccountKedua',
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