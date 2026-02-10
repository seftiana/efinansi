<?php
/**
* ================= doc ====================
* FILENAME     : ViewPopupRencanaPenerimaan.html.class.php
* @package     : ViewPopupRencanaPenerimaan
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-05
* @Modified    : 2015-03-05
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_realisasi_penerimaan/business/AppRencanaPenerimaan.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewPopupRencanaPenerimaan extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_transaksi_realisasi_penerimaan/template/');
      $this->SetTemplateFile('view_popup_rencana_penerimaan.html');
   }

   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest(){
      $mObj          = new AppRencanaPenerimaan();
      $mUnitObj      = new UserUnitKerja();
      $queryRequest  = $mObj->_getQueryString();
      $queryRequest  = preg_replace('/\&search=(\W+)/', '', $queryRequest);
      $queryRequest  = ($queryRequest != '') ? '&'.$queryRequest : '';
      $requestData   = array();
      $queryString   = '';

      if(isset($mObj->_POST['btncari'])){
         $requestData['nama']    = trim($mObj->_POST['nama']);
         $requestData['unit_id'] = $mObj->_POST['unit_id'];
      }elseif (isset($mObj->_GET['search'])) {
         $requestData['nama']    = Dispatcher::Instance()->Decrypt($mObj->_GET['nama']);
         $requestData['unit_id'] = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
      }else{
         $requestData['nama']    = '';
         $requestData['unit_id'] = Dispatcher::Instance()->Decrypt($mObj->_GET['unitkerja']);
      }
      $unitKerja     = $mUnitObj->GetUnitKerja($requestData['unit_id']);
      $requestData['unit_kode']  = $unitKerja['unit_kerja_kode'];
      $requestData['unit_nama']  = $unitKerja['unit_kerja_nama'];

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
      $dataList         = $mObj->ChangeKeyName($mObj->GetData($offset, $limit, $requestData));
      $total_data       = $mObj->GetCountData();
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
      $return['request_query']   = $queryRequest;
      $return['data_list']       = $dataList;
      $return['start']           = $offset+1;
      return $return;
   }

   function ParseTemplate($data = null){
      $queryRequest     = $data['request_query'];
      $queryString      = $data['query_string'];
      $requestData      = $data['request_data'];
      $dataList         = $data['data_list'];
      $start            = $data['start'];
      $dataMap          = array();
      $urlSearch        = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_realisasi_penerimaan',
         'PopupRencanaPenerimaan',
         'view',
         'html'
      ) . $queryRequest;

      $this->mrTemplate->AddVar('content', 'URL_SEARCH',  $urlSearch);
      $this->mrTemplate->AddVars('content', $requestData);

      if (empty($dataList)) {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         foreach($dataList as $list){
            $dataMap[$list['id']]      = $list;
            $list['number']            = $start;
            $list['nominal']           = number_format($list['nominal_aprove'], 2, ',',',');
            $list['nominal_realisasi'] = number_format($list['nominal_realisasi'], 2, ',','.');
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $start+=1;
         }
      }

      $dataRealisasi['data']     = json_encode($dataMap);
      $this->mrTemplate->AddVars('content', $dataRealisasi, 'MAP_');
   }
}
?>