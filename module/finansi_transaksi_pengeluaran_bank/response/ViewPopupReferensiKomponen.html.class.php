<?php
/**
* ================= doc ====================
* FILENAME     : ViewReferensiTransaksi.html.class.php
* @package     : ViewReferensiTransaksi
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-13
* @Modified    : 2015-04-13
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_pengeluaran_bank/business/AppReferensi.class.php';

class ViewPopupReferensiKomponen extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_transaksi_pengeluaran_bank/template/');
      $this->SetTemplateFile('view_popup_referensi_komponen.html');
   }

   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest(){
      $mObj             = new AppReferensi();
      $requestData      = array();
      $tahunPembukuan   = $mObj->getTahunPembukuan(true);
      $tahunAnggaran    = $mObj->getTahunAnggaran(array('active' => true));

      if(isset($mObj->_POST['btnSearch'])){
         $requestData['kode']    = trim($mObj->_POST['kode']);
      }elseif(isset($mObj->_GET['search'])){
         $requestData['kode']    = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
      }else{
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
      ).'&search='.Dispatcher::Instance()->Encrypt(1) .'&'.$queryString;

      $destination_id   = "popup-subcontent";
      $dataList         = $mObj->getReferensiSppu($offset, $limit, (array)$requestData);
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

      // $getCoaSppu         =  $mObj->GetDataCoaSppu(); 

      $return['coa_sppu']        = $getCoaSppu;
      $return['query_string']    = $queryString;
      $return['request_data']    = $requestData;
      $return['data_list']       = $dataList;
      $return['start']           = $offset+1;
      return $return;
   }

   function ParseTemplate($data = null){
      $requestData   = $data['request_data'];
      $dataList      = $data['data_list'];
      $start         = $data['start'];
      $dataCoaSppu   = $data['coa_sppu'];
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_pengeluaran_bank',
         'PopupReferensiKomponen',
         'view',
         'html'
      );


      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVars('content', $requestData);
      $dataCoa = array();
      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $index      = 0;
         foreach ($dataList as $list) {
            $dataCoa[$list['id']] = $dataCoaSppu[$list['id']];
            $list['nomor']       = $start;
            $list['class_name']  = ($start % 2 <> 0) ? 'table-common-even' : '';
            $list['nominal_f']   = number_format($list['nominal'], 2, ',','.');
            $list['keterangan']  = str_replace(array("\r", "\n", '"'), '', $list['keterangan']);  
            
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $start++;
            $index++;
         }
      }
   
      if(!empty($dataCoa)) {
        $this->mrTemplate->AddVar('content', 'DATA_COA', json_encode($dataCoa));
      } else {
        $this->mrTemplate->AddVar('content',  'DATA_COA','null');
      }
   }
}
?>