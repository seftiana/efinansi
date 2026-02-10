<?php
/**
* ================= doc ====================
* FILENAME     : ViewDetailRenstra.html.class.php
* @package     : ViewDetailRenstra
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-12-17
* @Modified    : 2014-12-17
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/renstra/business/Renstra.class.php';

class ViewDetailRenstra extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/renstra/template/');
      $this->SetTemplateFile('view_detail_renstra.html');
   }

   function ProcessRequest(){
      $mObj          = new Renstra();
      $data_id       = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
      $query_string  = $mObj->_getQueryString();
      $data_list     = $mObj->ChangeKeyName($mObj->GetDataDetail($data_id));
      $data_grid     = $mObj->ChangeKeyName($mObj->GetPeriodeTahunRenstra($data_id));

      $return        = compact('query_string', 'data_list', 'data_grid');
      return $return;
   }

   function ParseTemplate($data = null){
      $query_string  = $data['query_string'];
      $data_list     = $data['data_list'];
      $data_grid     = $data['data_grid'];
      $status        = (strtoupper($data_list['status']) == 'Y') ? 'ACTIVE' : 'NOT_ACTIVE';
      $urlReturn     = Dispatcher::Instance()->GetUrl(
         'renstra',
         'Renstra',
         'view',
         'html'
      ).'&search=1&'.$query_string;

      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVars('data_renstra', $data_list);
      $this->mrTemplate->AddVar('status_aktif', 'STATUS', $status);

      if(empty($data_grid)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $nomor      = 1;
         foreach ($data_grid as $grid) {
            $this->mrTemplate->clearTemplate('periode_aktif');
            $this->mrTemplate->clearTemplate('periode_open');
            $grid['nomor']    = $nomor;
            $grid['aktif']    = (strtoupper($grid['status_aktif']) == 'Y') ? 'YES' : 'NO';
            $grid['open']     = (strtoupper($grid['status_open']) == 'Y') ? 'YES' : 'NO';
            $this->mrTemplate->AddVar('periode_aktif', 'STATUS', $grid['aktif']);
            $this->mrTemplate->AddVar('periode_open', 'STATUS', $grid['open']);
            $this->mrTemplate->AddVars('data_list', $grid);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $nomor++;
         }
      }
   }
}
?>