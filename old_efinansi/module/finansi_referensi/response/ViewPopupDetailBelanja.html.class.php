<?php
/**
* ================= doc ====================
* FILENAME     : ViewPopupDetailBelanja.html.class.php
* @package     : ViewPopupDetailBelanja
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-01-07
* @Modified    : 2014-01-07
* @Analysts    : Dyah Fajar
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/AppReferensi.class.php';

class ViewPopupDetailBelanja extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/'.Dispatcher::Instance()->mModule.'/template/');
      $this->SetTemplateFile('view_popup_detail_belanja.html');
   }

   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest(){
      $mObj       = new AppReferensi();

      $requestData['ref_id']     = $mObj->_GET['komponen_id'];
      if(isset($mObj->_POST['btnSearch'])){
         $requestData['kode']    = trim($mObj->_POST['kode']);
         $requestData['nama']    = trim($mObj->_POST['nama']);
      }elseif(isset($mObj->_GET['search'])){
         $requestData['kode']    = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
         $requestData['nama']    = Dispatcher::Instance()->Decrypt($mObj->_GET['nama']);
      }else{
         $requestData['kode']    = '';
         $requestData['nama']    = '';
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         $queryString            = Dispatcher::Instance()->getQueryString($requestData);
      }else{
         foreach ($requestData as $key => $value) {
            $query[$key]         = Dispatcher::Instance()->Encrypt($value);
         }

         $queryString            = urldecode(http_build_query($query));
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
      $dataList         = $mObj->GetDetailBelanja($offset, $limit, (array)$requestData);
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

      $return['request_data']    = $requestData;
      $return['data_list']       = $mObj->ChangeKeyName($dataList);
      $return['start']           = $offset+1;
      return $return;
   }

   function ParseTemplate($data = null){
      $requestData      = $data['request_data'];
      $urlSearch        = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType
      ).'&komponen_id='.$requestData['ref_id'];
      $dataList         = $data['data_list'];
      $start            = $data['start'];

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVars('content', $requestData);
      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         foreach ($dataList as $list) {
            $list['nomor']          = $start;
            $list['class_name']     = ($start % 2 <> 0) ? 'table-common-even' : '';
            $list['nominal_label']  = number_format($list['nominal'], 0, ',','.');
            if(is_null($list['referensi_id'])){
               $this->mrTemplate->AddVar('link_status', 'SHOW', 'YES');
               $this->mrTemplate->AddVars('link_status', $list);
            }else{
               $this->mrTemplate->AddVar('link_status', 'SHOW', 'NO');
            }
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $start++;
         }
      }
   }
}
?>