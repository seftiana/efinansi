<?php
/**
* ================= doc ====================
* FILENAME     : ViewDetailApprovalPencairan.html.class.php
* @package     : ViewDetailApprovalPencairan
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-30
* @Modified    : 2015-03-30
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/approval_pencairan/business/AppApprovalPencairan.class.php';

class ViewDetailApprovalPencairan extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/approval_pencairan/template/');
      $this->SetTemplateFile('view_detail_approval_pencairan.html');
   }

   function ProcessRequest(){
      $mObj       = new AppApprovalPencairan();
      $dataId     = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
      $dataApproval  = $mObj->getDataDetail($dataId);
      $dataKomponen  = $mObj->getKomponenPencairan($dataId);

      $return['data_list']    = $dataApproval;
      $return['komponen']     = $dataKomponen;
      return $return;
   }

   function ParseTemplate($data = null){
      $dataList      = $data['data_list'];
      $dataKomponen  = $data['komponen'];

      if($dataList['nominal'] < 0){
         $dataList['nominal']    = number_format(abs($dataList['nominal']), 2, ',','.');
      }else{
         $dataList['nominal']    = number_format($dataList['nominal'], 2, ',','.');
      }
      if($dataList['nominal_setuju'] < 0){
         $dataList['nominal_setuju']   = number_format(abs($dataList['nominal_setuju']), 2, ',','.');
      }else{
         $dataList['nominal_setuju']   = number_format($dataList['nominal_setuju'], 2, ',','.');
      }
      $this->mrTemplate->AddVars('content', $dataList);

      if(empty($dataKomponen)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         foreach ($dataKomponen as $komponen) {
            if($komponen['nominal_budget'] < 0){
               $komponen['nominal_budget']   = number_format(abs($komponen['nominal_budget']), 2, ',','.');
            }else{
               $komponen['nominal_budget']   = number_format($komponen['nominal_budget'], 2, ',','.');
            }

            if($komponen['nominal_approve'] < 0){
               $komponen['nominal_approve']  = number_format(abs($komponen['nominal_approve']), 2, ',','.');
            }else{
               $komponen['nominal_approve']  = number_format($komponen['nominal_approve'], 2, ',','.');
            }
            $this->mrTemplate->AddVars('data_list', $komponen);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
      }
   }
}
?>