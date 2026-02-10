<?php
/**
* ================= doc ====================
* FILENAME     : ViewDetailSppu.html.class.php
* @package     : ViewDetailSppu
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-08
* @Modified    : 2015-04-08
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_sppu/business/Sppu.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'main/function/terbilang.php';

class ViewDetailSppu extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_sppu/template/');
      $this->SetTemplateFile('view_detail_sppu.html');
   }

   function ProcessRequest(){
      $messenger     = Messenger::Instance()->Receive(__FILE__);
      $mObj          = new Sppu();
      $queryString   = $mObj->_getQueryString();
      $queryString   = preg_replace('/(search=[\d]+)/', '', $queryString);
      $queryString   = preg_replace('/(data_id=[\d]+)/', '', $queryString);
      $queryString   = preg_replace('/\&[\&]+/', '&', $queryString);
      $queryString   = preg_replace('/[\&]$/', '', $queryString);
      $queryReturn   = ($queryString == '') ? '' : '&search=1&'.$queryString;
      $dataId        = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
      $message       = $style = $messengerData = NULL;
      $dataSppu      = $mObj->getDataDetailSppu($dataId);
      $dataList      = $mObj->getDataSppuItems($dataId);

      if($messenger){
         $messengerData    = $messenger[0][0];
         $message          = $messenger[0][1];
         $style            = $messenger[0][2];
      }

      $return['data_sppu']    = $dataSppu;
      $return['data_list']    = $dataList;
      $return['message']      = $message;
      $return['style']        = $style;
      $return['query_return'] = $queryReturn;
      return $return;
   }

   function ParseTemplate($data = null){
      $mNumber       = new Number();
      $queryString   = $data['query_return'];
      $dataSppu      = $data['data_sppu'];
      $dataList      = $data['data_list'];
      $message       = $data['message'];
      $style         = $data['style'];
      $urlSppu       = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'Sppu',
         'view',
         'html'
      );

      $urlExportExcel   = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'ExportExcelSppu',
         'view',
         'xlsx'
      ).'&data_id='.Dispatcher::Instance()->Encrypt($dataSppu['id']);

      $urlListSppu      = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'listSppu',
         'view',
         'html'
      ) . $queryString;

      $urlExportBp = Dispatcher::Instance()->GetUrl(
            'finansi_sppu',
            'CetakSppu',
            'do',
            'html'
      ).'&data_id='.Dispatcher::Instance()->Encrypt($dataSppu['id']);

      // $urlExportBp      = Dispatcher::Instance()->GetUrl(
      //    'finansi_sppu',
      //    'ExportExcelBp',
      //    'view',
      //    'xlsx'
      // ).'&data_id='.Dispatcher::Instance()->Encrypt($dataSppu['id']);

      $urlExportCr   = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'ExportExcelCr',
         'view',
         'xlsx'
      ).'&data_id='.Dispatcher::Instance()->Encrypt($dataSppu['id']);


      $dataSppu['terbilang']     = $mNumber->Terbilang($dataSppu['nominal'], 3).' Rupiah';
      $dataSppu['nominal_label'] = number_format($dataSppu['nominal'], 2, ',','.');
      $this->mrTemplate->AddVars('content_data', $dataSppu);
      $this->mrTemplate->AddVar('content', 'URL_SPPU', $urlSppu);
      $this->mrTemplate->AddVar('content', 'URL_EXPORT', $urlExportExcel);
      $this->mrTemplate->AddVar('content', 'URL_LIST_SPPU', $urlListSppu);
      
      $this->mrTemplate->AddVar('content', 'EXPORT_BP', $urlExportBp);
      $this->mrTemplate->AddVar('content', 'EXPORT_CR', $urlExportCr);
      

      if(strtoupper($dataSppu['bank_payment']) == 'Y'){
         $this->mrTemplate->AddVar('bank_payment','STATUS', 'YES');
      }else{
         $this->mrTemplate->AddVar('bank_payment','STATUS', 'NO');
      }

      if(strtoupper($dataSppu['cash_receipt']) == 'Y'){
         $this->mrTemplate->AddVar('cash_receipt','STATUS', 'YES');
      }else{
         $this->mrTemplate->AddVar('cash_receipt','STATUS', 'NO');
      }

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
         $this->mrTemplate->clearTemplate('tfoot');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $this->mrTemplate->AddVar('tfoot', 'NOMINAL_TOTAL', $dataSppu['nominal_label']);
         $nomor      = 1;
         foreach ($dataList as $list) {
            $list['nomor']    = $nomor;
            $list['nominal_label']  = number_format($list['nominal'], 2, ',','.');
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $nomor+=1;
         }
      }

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }
   }
}
?>