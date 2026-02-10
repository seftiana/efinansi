<?php
/**
* ================= doc ====================
* FILENAME     : ViewReferensiCoa.html.class.php
* @package     : ViewReferensiCoa
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-13
* @Modified    : 2015-04-13
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_jurnal_pengeluaran/business/AppReferensi.class.php';

class ViewReferensiCoa extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_jurnal_pengeluaran/template/');
      $this->SetTemplateFile('view_referensi_coa.html');
   }

   function ProcessRequest(){
      $mObj          = new AppReferensi();
      $requestData   = array();
      $queryString   = '';
      $requestQuery  = $mObj->_getQueryString();
      $requestQuery  = preg_replace('/\&page=[a-zA-Z0-9_-]+/', '', $requestQuery);
      $requestQuery  = preg_replace('/search=[a-zA-Z0-9_-]+/', '', $requestQuery);

      $requestData['tipe']       = $mObj->_GET['tipe'];
      if(isset($mObj->_POST['btncari'])){
         $requestData['nama']    = trim($mObj->_POST['nama']);
         $requestData['kode']    = trim($mObj->_POST['kode']);
      }elseif(isset($mObj->_GET['search'])){
         $requestData['nama']    = Dispatcher::Instance()->Decrypt($mObj->_GET['nama']);
         $requestData['kode']    = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
      }else{
         $requestData['nama']    = '';
         $requestData['kode']    = '';
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         # @param array
         $queryString   = Dispatcher::instance()->getQueryString($requestData);
      }else{
         $query         = array();
         foreach ($requestData as $key => $value) {
            $query[$key]   = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString   = urldecode(http_build_query($query));
      }

      $offset           = 0;
      $limit            = 20;
      $page             = 0;
      if(isset($_GET['page'])){
         $page          = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset        = ($page - 1) * $limit;
      }
      #paging url
      $url              = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$requestQuery.'&'.$queryString;

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

      $return['data_list']       = $mObj->ChangeKeyName($dataList);
      $return['request_data']    = $requestData;
      $return['start']           = $offset+1;
      $return['request_query']   = $requestQuery;
      return $return;
   }

   function ParseTemplate($data = null){
      $requestData      = $data['request_data'];
      $dataList         = $data['data_list'];
      $start            = $data['start'];
      $requestQuery     = $data['request_query'];
      $dataCoa          = array();
      $urlSearch        = Dispatcher::Instance()->GetUrl(
         'finansi_jurnal_pengeluaran',
         'ReferensiCoa',
         'view',
         'html'
      ).'&'.$requestQuery;

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVars('content', $requestData);

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'IS_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'IS_EMPTY', 'NO');
         foreach ($dataList as $list) {
            $this->mrTemplate->clearTemplate('content_links');
            $dataCoa[$list['id']]   = $list;
            $list['nomor']          = $start;
            $list['class_name']     = ($start % 2 <> 0) ? 'table-common-even' : '';
            $this->mrTemplate->AddVar('content_links', 'TIPE', strtoupper($requestData['tipe']));
            $this->mrTemplate->AddVar('content_links', 'ID', $list['id']);
            $this->mrTemplate->AddVars('data_item', $list);
            $this->mrTemplate->parseTemplate('data_item', 'a');
            $start++;
         }
      }

      $dataJson['data']       = json_encode($dataCoa);
      $this->mrTemplate->AddVars('content', $dataJson, 'COA_');
   }
}
?>