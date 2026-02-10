<?php
/**
* ================= doc ====================
* FILENAME     : ViewPopupCoa.html.class.php
* @package     : ViewPopupCoa
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-27
* @Modified    : 2015-03-27
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_mata_anggaran/business/AppReferensi.class.php';

class ViewPopupCoa extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_mata_anggaran/template/');
      $this->SetTemplateFile('view_popup_coa.html');
   }

   function ProcessRequest(){
      $mObj          = new AppReferensi();
      $requestData   = array();

      if(isset($mObj->_POST['btnSearch'])){
         $requestData['kode']    = $mObj->_POST['kode'];
         $requestData['nama']    = $mObj->_POST['nama'];
      }elseif(isset($mObj->_GET['search'])){
         $requestData['kode']    = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
         $requestData['nama']    = Dispatcher::Instance()->Decrypt($mObj->_GET['nama']);
      }else{
         $requestData['kode']    = '';
         $requestData['nama']    = '';
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         # @param array
         $queryString   = Dispatcher::instance()->getQueryString($requestData);
      }else{
         $query         = array();
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
      $url        = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;

      $destination_id   = "popup-subcontent";
      $dataList         = $mObj->getDataCoa($offset, $limit, (array)$requestData);
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
      $return['data_list']       = $dataList;
      $return['start']           = $offset+1;
      return $return;
   }

   function ParseTemplate($data = null){
      $start         = $data['start'];
      $dataList      = $data['data_list'];
      $requestData   = $data['request_data'];
      $coa           = array();
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'finansi_mata_anggaran',
         'PopupCoa',
         'view',
         'html'
      );

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         foreach ($dataList as $list) {
            $coa[$list['akun_id']]  = $list;
            $list['nomor']       = $start;
            $list['class_name']  = ($start % 2 <> 0) ? 'table-common-even' : '';
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $start++;
         }
      }

      $object['data']      = json_encode($coa);
      $this->mrTemplate->AddVars('content', $object, 'COA_');
   }
}
?>