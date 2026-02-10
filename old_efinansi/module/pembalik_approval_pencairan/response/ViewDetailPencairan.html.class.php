<?php
/**
* ================= doc ====================
* FILENAME     : ViewDetailPencairan.html.class.php
* @package     : ViewDetailPencairan
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-09
* @Modified    : 2015-04-09
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/pembalik_approval_pencairan/business/AppPembalikApprovalPencairan.class.php';

class ViewDetailPencairan extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/pembalik_approval_pencairan/template/');
      $this->SetTemplateFile('view_detail_pencairan.html');
   }

   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest(){
      $mObj       = new AppPembalikApprovalPencairan();
      $dataId     = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
      $dataApproval     = $mObj->getDataDetail($dataId);
      $dataKomponen     = $mObj->getKomponenPencairan($dataId);

      $return['data_realisasi']  = $dataApproval;
      $return['komponen']['data']   = json_encode((array)$dataKomponen);
      return $return;
   }

   function ParseTemplate($data = null){
      $dataRealisasi       = $data['data_realisasi'];
      $dataKomponen        = $data['komponen'];
      $this->mrTemplate->AddVar('content_status', 'STATUS', strtoupper($dataRealisasi['status']));
      if($dataRealisasi['nominal'] < 0){
         $dataRealisasi['nominal_label']  = number_format(abs($dataRealisasi['nominal']), 2, ',','.');
      }else{
         $dataRealisasi['nominal_label']  = number_format($dataRealisasi['nominal'], 2, ',', '.');
      }

      $this->mrTemplate->AddVars('content', $dataRealisasi);
      $this->mrTemplate->AddVars('content', $dataKomponen, 'KOMPONEN_');
   }
}
?>