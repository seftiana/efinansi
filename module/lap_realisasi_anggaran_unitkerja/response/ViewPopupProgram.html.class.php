<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/'.Dispatcher::Instance()->mModule.'/business/AppPopupProgram.class.php';

class ViewPopupProgram extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/'.Dispatcher::Instance()->mModule.'/template');
      $this->SetTemplateFile('view_popup_program.html');
   }

    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest() {
      $mObj       = new AppPopupProgram();
      $taId       = Dispatcher::Instance()->Decrypt($mObj->_GET['tahun_anggaran']);
      $taNama     = Dispatcher::Instance()->Decrypt($mObj->_GET['tahun_anggaran_label']);
      $requestData   = array();

      $requestData['tahun_anggaran']         = $taId;
      $requestData['tahun_anggaran_label']   = $taNama;
      if(isset($mObj->_POST['btncari'])){
         $requestData['kode']    = trim($mObj->_POST['kode']);
      }elseif (isset($mObj->_GET['search'])) {
         $requestData['kode']    = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
      }else{
         $requestData['kode']    = '';
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         $queryString      = Dispatcher::Instance()->getQueryString($requestData);
      }else{
         $query            = array();
         foreach ($requestData as $key => $value) {
            $query[$key]   = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString      = urldecode(http_build_query($query));
      }

      $offset         = 0;
      $limit          = 20;
      $page           = 0;
      if(isset($_GET['page'])){
         $page    = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset  = ($page - 1) * $limit;
      }
      #paging url
      $url    = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1);

      $destination_id = "popup-subcontent";
      $dataList         = $mObj->GetDataProgram($offset, $limit, $requestData);
      $total_data       = $mObj->GetCountDataProgram();
      #send data to pagging component
      Messenger::Instance()->SendToComponent(
         'paging',
         'Paging',
         'view',
         'html',
         'paging_top',
         array(
            $limit,
            $total_data,
            $url,
            $page,
            $destination_id
         ),
         Messenger::CurrentRequest
      );

      $return['request_data']    = $requestData;
      $return['query_string']    = $queryString;
      $return['data_list']       = $mObj->ChangeKeyName($dataList);
      $return['start']           = $offset+1;
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $requestData      = $data['request_data'];
      $queryString      = $data['query_string'];
      $start            = $data['start'];
      $dataList         = $data['data_list'];
      $urlSearch        = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         'view',
         'html'
      ).'&'.$queryString;

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         foreach ($dataList as $list) {
            $list['nomor']       = $start;
            $list['class_name']  = $start % 2 <> 0 ? 'table-common-even' : '';
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $start++;
         }
      }
   }
}
?>