<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/'.Dispatcher::Instance()->mModule.'/business/AppPopupUnitkerja.class.php';

class ViewPopupUnitkerja extends HtmlResponse
{
   function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/'.Dispatcher::Instance()->mModule.'/template');
      $this->SetTemplateFile('view_popup_unitkerja.html');
   }

   function TemplateBase()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
         'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest()
   {
      $mObj          = new AppPopupUnitkerja();
      $arrUnitType   = $mObj->GetDataTipeUnit();
      $requestData   = array();

      if(isset($mObj->_POST['btncari'])){
         $requestData['kode']    = trim($mObj->_POST['kode']);
         $requestData['nama']    = trim($mObj->_POST['nama']);
         $requestData['tipe']    = trim($mObj->_POST['tipe']);
      }elseif(isset($mObj->_GET['search'])){
         $requestData['kode']    = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
         $requestData['nama']    = Dispatcher::Instance()->Decrypt($mObj->_GET['nama']);
         $requestData['tipe']    = Dispatcher::Instance()->Decrypt($mObj->_GET['tipe']);
      }else{
         $requestData['kode']    = '';
         $requestData['nama']    = '';
         $requestData['tipe']    = '';
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         $queryString         = Dispatcher::Instance()->getQueryString($requestData);
      }else{
         $query               = array();
         foreach ($requestData as $key => $value) {
            $query[$key]      = Dispatcher::Instance()->Encrypt($value);
         }

         $queryString         = urldecode(http_build_query($query));
      }

      $offset        = 0;
      $limit         = 20;
      $page          = 0;
      if(isset($_GET['page'])){
         $page       = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset     = ($page - 1) * $limit;
      }
      #paging url
      $url           = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;

      $destination_id   = "popup-subcontent";
      $dataList         = $mObj->GetData($offset, $limit, (array)$requestData);
      $total_data       = $mObj->Count();

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

      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'tipeunit',
         array(
            'tipe',
            $arrUnitType,
            $requestData['tipe'],
            true,
            'id="cmb_type_unit" style="width: 200px;"'
         ),
         Messenger::CurrentRequest
      );

      $return['request_data']    = $requestData;
      $return['start']           = $offset+1;
      $return['data_list']       = $mObj->ChangeKeyName($dataList);
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $requestData         = $data['request_data'];
      $start               = $data['start'];
      $dataList            = $data['data_list'];
      $urlSearch           = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         'view',
         'html'
      );
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         foreach ($dataList as $list) {
            $list['nomor']    = $start;
            $list['link']     = str_replace("'","\'",$list['nama']);
            if($list['parent_id'] == 0){
               $list['class_name']     = 'table-common-even1';
               $list['row_style']      = 'font-weight: bold;';
            }elseif($list['child'] <> 0){
               $list['class_name']     = 'table-common-even2';
            }
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $start++;
         }
      }
   }
}
?>