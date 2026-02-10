<?php

/**
* ================= doc ====================
* FILENAME     : ViewPopupRealisasi.html.class.php
* @package     : ViewPopupRealisasi
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-17
* @Modified    : 2015-03-17
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/

require_once GTFWConfiguration::GetValue('application','docroot').
'module/lppa/business/AppReferensi.class.php';

class ViewPopupRealisasi extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/lppa/template/');
      $this->SetTemplateFile('view_popup_realisasi.html');
   }

   function ProcessRequest(){
      $mObj       = new AppReferensi();
      $mUnitObj   = new UserUnitKerja();
      $queryRequest  = $mObj->_getQueryString();
      $requestData   = array();
      $unitId        = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
      $taId          = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
      $lppaRealId    = Dispatcher::Instance()->Decrypt($mObj->_GET['flag_id']); 
      $unitKerja     = $mUnitObj->GetUnitKerja($unitId);

      $requestData['ta_id']      = $taId  ;
      $requestData['flag_id']    = $lppaRealId  ;
      $requestData['unit_id']    = $unitKerja['unit_kerja_id'];
      $requestData['unit_kode']  = $unitKerja['unit_kerja_kode'];
      $requestData['unit_nama']  = $unitKerja['unit_kerja_nama'];
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
         $queryString      = Dispatcher::instance()->getQueryString($requestData);
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
      $url        = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1) . '&' . $queryString;

      $destination_id   = "popup-subcontent";
      $dataList         = $mObj->getDataRealisasi($offset, $limit, (array)$requestData);
      $total_data       = $mObj->Count();
      $dataDetailBelanjaFpa     = $mObj->GetDetailBelanjaFpaByTaUnit($requestData['ta_id'],$requestData['unit_id']  );
      
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

      $return['query_string']    = $queryString;
      $return['query_request']   = $queryRequest;
      $return['request_data']    = $requestData;
      $return['data_list']       = $dataList;
      $return['start']           = $offset+1;
      $return['detail_belanja_fpa']['data']  = json_encode($dataDetailBelanjaFpa);
      return $return;
   }

   function ParseTemplate($data = null){
      $queryRequest     = $data['query_request'];
      $requestData      = $data['request_data'];
      $dataList         = $data['data_list'];
      $start            = $data['start'];
      
      $dataDetailBelanjaFpa = $data['detail_belanja_fpa'];
      $this->mrTemplate->AddVars('content', $dataDetailBelanjaFpa, 'KOMP_');
      
      $dataRealisasi    = array();
      $urlSearch        = Dispatcher::Instance()->GetUrl(
         'lppa',
         'PopupRealisasi',
         'view',
         'html'
      ).'&'.$queryRequest; 
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVars('content', $requestData);

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         foreach ($dataList as $list) {
            $dataRealisasi[$list['id']]      = $list;
            $list['nomor']       = $start;
            $list['class_name']  = ($start % 2 <> 0) ? 'table-common-even' : '';
            $list['nominal_approve']   = number_format($list['nominal_approve'], 2, ',','.');
            $list['nominal_realisasi'] = number_format($list['nominal_realisasi'], 2, ',','.');
            $list['nominal_spj']       = number_format($list['nominal_spj'], 2, ',','.');
            
            if ($list['is_tercatat'] == 0 || $list['id'] ==$requestData['flag_id']) {
               $this->mrTemplate->SetAttribute('fpa_pilih', 'visibility', 'visible');
           } else {
               $this->mrTemplate->SetAttribute('fpa_pilih', 'visibility', 'hidden');
           }
           
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->AddVars('fpa_pilih', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $start++;
         }
      }

      $object['data']   = json_encode($dataRealisasi);
      $this->mrTemplate->AddVars('content', $object, 'REALISASI_');
   }
}
?>