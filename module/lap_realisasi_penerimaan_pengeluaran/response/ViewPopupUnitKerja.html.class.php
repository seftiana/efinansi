<?php
/**
* ================= doc ====================
* FILENAME     : ViewPopupUnitKerja.html.class.php
* @package     : ViewPopupUnitKerja
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-13
* @Modified    : 2015-03-13
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/lap_realisasi_penerimaan_pengeluaran/business/AppReferensi.class.php';

class ViewPopupUnitKerja extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/lap_realisasi_penerimaan_pengeluaran/template/');
      $this->SetTemplateFile('view_popup_unit_kerja.html');
   }

   function ProcessRequest(){
      $mObj          = new AppReferensi();
      $arrTipeUnit   = $mObj->getTipeUnit();
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
            $arrTipeUnit,
            $requestData['tipe'],
            true,
            'id="cmb_tipe_unit"'
         ),
         Messenger::CurrentRequest
      );

      $return['request_data']    = $requestData;
      $return['data_list']       = $dataList;
      $return['start']           = $offset+1;
      return $return;
   }

   function ParseTemplate($data = null){
      $dataList      = $data['data_list'];
      $start         = $data['start'];
      $requestData   = $data['request_data'];
      $dataUnit      = array();
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'lap_realisasi_penerimaan_pengeluaran',
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
            $list['no']             = $start;
            $list['row_style']      = '';
            if($list['parent_id'] == 0){
               $list['class_name']  = 'table-common-even1';
               $list['row_style']   = 'font-weight: bold;';
            }else{
               $list['class_name']  = ($start % 2 <> 0) ? 'table-common-even' : '';
            }

            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $start++;
         }
      }

      $dataObject['data']     = json_encode($dataUnit);
      $this->mrTemplate->AddVars('content', $dataObject, 'UNIT_');
   }
}
?>