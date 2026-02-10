<?php
/**
* ================= doc ====================
* FILENAME     : ViewPopupProgram.html.class.php
* @package     : ViewPopupProgram
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-29
* @Modified    : 2015-04-29
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/laporan_rekap_anggaran_belanja_bulanan/business/AppReferensi.class.php';

class ViewPopupProgram extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/laporan_rekap_anggaran_belanja_bulanan/template/');
      $this->SetTemplateFile('view_popup_program.html');
   }

   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest(){
      $mObj             = new AppReferensi();
      $queryString      = $mObj->_getQueryString();
      $requestData      = array();

      if(isset($mObj->_POST['btnSearch'])){
         $requestData['kode']    = $mObj->_POST['kode'];
         $requestData['nama']    = $mObj->_POST['nama'];
         $requestData['ta_id']   = $mObj->_POST['ta_id'];
      }elseif(isset($mObj->_GET['search'])){
         $requestData['kode']    = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
         $requestData['nama']    = Dispatcher::Instance()->Decrypt($mObj->_GET['nama']);
         $requestData['ta_id']   = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
      }else{
         $requestData['kode']    = '';
         $requestData['nama']    = '';
         $requestData['ta_id']   = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
      }

      $periodeTahun              = $mObj->getDataTahunAnggaran($requestData['ta_id']);
      $requestData['ta_id']      = $periodeTahun['id'];
      $requestData['ta_nama']    = $periodeTahun['name'];

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

      $offset         = 0;
      $limit          = 20;
      $page           = 0;
      $total_data     = total_data;
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
      $dataList         = $mObj->getDataProgram($offset, $limit, $requestData);
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

      $return['query_string']    = $queryString;
      $return['request_data']    = $requestData;
      $return['data_list']       = $dataList;
      $return['start']           = $offset+1;
      return $return;
   }

   function ParseTemplate($data = null){
      $dataList         = $data['data_list'];
      $start            = $data['start'];
      $queryString      = $data['query_string'];
      $requestData      = $data['request_data'];
      $dataProgram      = array();
      $urlSearch        = Dispatcher::Instance()->GetUrl(
         'laporan_rekap_anggaran_belanja_bulanan',
         'PopupProgram',
         'view',
         'html'
      ).'&'.$queryString;

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVars('content', $requestData);

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         foreach ($dataList as $list) {
            $dataProgram[$list['id']]  = $list;
            $list['nomor']       = $start;
            $list['class_name']  = ($start % 2 <> 0) ? 'table-common-even' : '';
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $start++;
         }
      }

      $object['data']      = json_encode($dataProgram);
      $this->mrTemplate->AddVars('content', $object, 'PROGRAM_');
   }
}
?>