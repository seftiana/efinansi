<?php
/**
* ================= doc ====================
* FILENAME     : ViewUpdateJurnal.html.class.php
* @package     : ViewUpdateJurnal
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-02-24
* @Modified    : 2015-02-24
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/posting/business/AppPosting.class.php';

class ViewUpdateJurnal extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/posting/template/');
      $this->SetTemplateFile('view_update_jurnal.html');
   }

   function ProcessRequest(){
      $mObj             = new AppPosting();
      $dateParameter    = $mObj->getParameterDate();
      $transaksiId      = Dispatcher::Instance()->Decrypt($mObj->_GET['transaksi_id']);
      $pembukuanId      = Dispatcher::Instance()->Decrypt($mObj->_GET['pembukuan_id']);
      $dataJurnal       = $mObj->getJurnalDetail($transaksiId, $pembukuanId);
      $minYear          = date('Y', strtotime($dateParameter['last_posting']));
      $maxYear          = date('Y', strtotime($dateParameter['last_posting']));
      # GTFW Tanggal
      $requestData['tanggal']   = date('Y-m-d', strtotime($dateParameter['last_posting']));
      Messenger::Instance()->SendToComponent(
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'tanggal',
         array(
            $requestData['tanggal'],
            $minYear,
            $maxYear,
            false,
            false,
            false
         ),
         Messenger::CurrentRequest
      );

      $return['data_jurnal']  = $mObj->ChangeKeyName($dataJurnal);
      return $return;
   }

   function ParseTemplate($data = null){
      $dataJurnal       = $data['data_jurnal'];
      $urlAction        = Dispatcher::Instance()->GetUrl(
         'posting',
         'updateJurnal',
         'do',
         'json'
      );

      $this->mrTemplate->AddVars('content', $dataJurnal);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);

   }
}
?>