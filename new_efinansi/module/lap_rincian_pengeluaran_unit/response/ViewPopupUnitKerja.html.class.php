<?php
/**
* ================= doc ====================
* FILENAME     : ViewPopupUnitKerja.html.class.php
* @package     : ViewPopupUnitKerja
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-09-19
* @Modified    : 2014-09-19
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/AppPopupUnitKerja.class.php';

class ViewPopupUnitKerja extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/'.Dispatcher::Instance()->mModule.'/template/');
      $this->SetTemplateFile('view_popup_unit_kerja.html');
   }

   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest(){
      $mObj          = new AppPopupUnitKerja();
      $requestData   = array();
      $arrTypeUnit   = $mObj->GetTypeUnit();

      if(isset($mObj->_POST['btnSearch'])){
         $requestData['kode']    = trim($mObj->_POST['kode']);
         $requestData['nama']    = trim($mObj->_POST['nama']);
         $requestData['tipe']    = $mObj->_POST['tipe'];
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
         $queryString      = Dispatcher::Instance()->getQueryString($requestData);
      }else{
         $query            = array();
         foreach ($requestData as $key => $value) {
            $query[$key]   = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString      = urldecode(http_build_query($query));
      }

      $offset     = 0;
      $limit      = 20;
      $page       = 0;
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
         'type_unit',
         array(
            'tipe',
            $arrTypeUnit,
            $requestData['tipe'],
            true,
            'id="cmb_tipe_unit"'
         ),
         Messenger::CurrentRequest
      );

      $return['request_data']    = $requestData;
      $return['query_string']    = $queryString;
      $return['data_list']       = $mObj->ChangeKeyName($dataList);
      $return['start']           = $offset+1;
      return $return;
   }

   function ParseTemplate($data = null){
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType
      );
      $requestData   = $data['request_data'];
      $queryString   = $data['query_string'];
      $dataList      = $data['data_list'];
      $start         = $data['start'];

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVars('content', $requestData);

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         foreach ($dataList as $list) {
            $list['nomor']             = $start;
            $list['row_style']         = '';
            if((int)$list['parent_id'] === 0){
               $list['class_name']     = 'table-common-even1';
               $list['row_style']      = 'font-weight: bold;';
            }else{
               if((int)$list['status'] <> 0){
                  $list['class_name']  = 'table-common-even2';
               }else{
                  $list['class_name']  = ($start % 2 <> 0) ? 'table-common-even' : '';
               }
            }
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $start+=1;
         }
      }
   }
}
?>