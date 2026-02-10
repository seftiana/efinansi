<?php
/*
   @ClassName : UploadRABFile
   @Copyright : PT Gamatechno Indonesia
   @Analyzed By : Nanang Ruswianto <nanang@gamatechno.com>
   @Author By : Dyan Galih <galih@gamatechno.com>
   @Version : 0.1
   @StartDate : 2010-01-06
   @LastUpdate : 2014-06-30
   @modified by : Eko Susilo
   @Description :
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/'.Dispatcher::Instance()->mModule.'/business/Upload.class.php';

class PopupUploadRABFile extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot').
         'module/rencana_pengeluaran/template');
      $this->SetTemplateFile('upload_popup.html');
   }

   function ProcessRequest() {
      $mObj          = new Upload();
      $dataId        = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
      $queryString   = $mObj->_getQueryString();

      $return['query_string']    = $queryString;
      $return['data_id']         = $dataId;
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $dataId        = $data['data_id'];
      $queryString   = $data['query_string'];
      $urlAction     = Dispatcher::Instance()->GetUrl(
         'rencana_pengeluaran',
         'uploadFile',
         'do',
         'html'
      ).'&'.$queryString;
      $this->mrTemplate->AddVar('content', 'ACTION_URL', $urlAction);
      $this->mrTemplate->AddVar('content', 'DATA_ID', $dataId);
   }
}
?>
