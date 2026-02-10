<?php
/**
* ================= doc ====================
* FILENAME     : ViewPopupUnitKerja.html.class.php
* @package     : ViewPopupUnitKerja
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-06
* @Modified    : 2015-03-06
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/laporan_rekap_pengeluaran_kas_bulanan/business/AppReferensi.class.php';

class ViewPopupUnitKerja extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/laporan_rekap_pengeluaran_kas_bulanan/template/');
      $this->SetTemplateFile('view_popup_unit_kerja.html');
   }

   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest(){
      $mObj       = new AppReferensi();
      $arrType    = $mObj->getTypeUnit();
      $requestData   = array();
      $queryString   = '';
      if(isset($mObj->_POST['btnSearch'])){
         $requestData['kode']    = $mObj->_POST['kode'];
         $requestData['nama']    = $mObj->_POST['nama'];
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
      $dataList         = $mObj->getUnitKerja($offset, $limit, (array)$requestData);
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
         'tipe',
         array(
            'tipe',
            $arrType,
            $requestData['tipe'],
            true,
            'id="cmb_type_unit"'
         ),
         Messenger::CurrentRequest
      );

      $return['data_list']    = $mObj->ChangekeyName($dataList);
      $return['request_data'] = $requestData;
      $return['start']        = $offset+1;
      return $return;
   }

   function ParseTemplate($data = null){
      $requestData      = $data['request_data'];
      $dataUnit         = array();
      $dataList         = $data['data_list'];
      $start            = $data['start'];
      $urlSearch        = Dispatcher::Instance()->GetUrl(
         'laporan_rekap_pengeluaran_kas_bulanan',
         'PopupUnitKerja',
         'view',
         'html'
      );

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVars('content', $requestData);

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         foreach ($dataList as $list) {
            $dataUnit[$list['id']]  = $list;
            $list['nomor']    = $start;
            if($list['parent_id'] == 0){
               $list['class_name']  = 'table-common-even1';
               $list['row_style']   = 'font-weight: bold;';
            }else{
               $list['class_name']  = ($start % 2 <> 0) ? 'table-common-even' : '';
               $list['row_style']   = '';
            }

            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $start++;
         }
      }

      $unitObject['data']     = json_encode($dataUnit);
      $this->mrTemplate->AddVars('content', $unitObject, 'UNIT_');
   }
}
?>