<?php
/**
* ================= doc ====================
* FILENAME     : ViewPopupSkenarioJurnal.html.class.php
* @package     : ViewPopupSkenarioJurnal
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-05
* @Modified    : 2015-03-05
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_realisasi_pencairan/business/AppPopupSkenario.class.php';

class ViewPopupSkenarioJurnal extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_realisasi_pencairan/template/');
      $this->SetTemplateFile('view_popup_skenario_jurnal.html');
   }

   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest(){
      $mObj          = new AppPopupSkenario();
      $requestData   = array();
      $queryString   = '';
      $arrJenis      = array(0 => array(
         'id' => 'auto',
         'name' => 'Auto'
      ), array(
         'id' => 'manual',
         'name' => 'Manual'
      ));

      if(isset($mObj->_POST['btnSearch'])){
         $requestData['jenis']   = $mObj->_POST['jenis'];
         $requestData['nama']    = $mObj->_POST['nama'];
      }elseif(isset($mObj->_GET['search'])){
         $requestData['jenis']   = Dispatcher::Instance()->Decrypt($mObj->_GET['jenis']);
         $requestData['nama']    = Dispatcher::Instance()->Decrypt($mObj->_GET['nama']);
      }else{
         $requestData['jenis']   = 'manual';
         $requestData['nama']    = '';
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
      $dataList         = $mObj->getData($requestData);
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
         'jenis',
         array(
            'jenis',
            $arrJenis,
            $requestData['jenis'],
            false,
            'id="cmb_jenis_skenario"'
         ),
         Messenger::CurrentRequest
      );

      $return['request_data']    = $requestData;
      $return['data_list']       = $dataList;
      return $return;
   }

   function ParseTemplate($data = null){
      $requestData      = $data['request_data'];
      $dataList         = $data['data_list'];
      $skenario         = array();
      $urlSearch        = Dispatcher::Instance()->GetUrl(
         'finansi_realisasi_pencairan',
         'PopupSkenarioJurnal',
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
            $skenario[$list['id']]    = $list;
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
      }

      $objSkeanrio['data'] = json_encode($skenario);
      $this->mrTemplate->AddVars('content', $objSkeanrio, 'SKENARIO_');
   }
}
?>