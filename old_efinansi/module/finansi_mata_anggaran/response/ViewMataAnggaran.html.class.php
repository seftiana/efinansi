<?php
/**
* ================= doc ====================
* FILENAME     : ViewMataAnggaran.html.class.php
* @package     : ViewMataAnggaran
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-26
* @Modified    : 2015-03-26
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_mata_anggaran/business/MataAnggaran.class.php';

class ViewMataAnggaran extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_mata_anggaran/template/');
      $this->SetTemplateFile('view_mata_anggaran.html');
   }

   function ProcessRequest(){
      $mObj          = new MataAnggaran();
      $messenger     = Messenger::Instance()->Receive(__FILE__);
      $requestData   = array();
      $queryString   = '';
      if(isset($mObj->_POST['btnSearch'])){
         $requestData['bas_kode']   = trim($mObj->_POST['bas']);
         $requestData['kode']       = trim($mObj->_POST['kode']);
         $requestData['nama']       = trim($mObj->_POST['nama']);
      }elseif(isset($mObj->_GET['search'])){
         $requestData['bas_kode']   = Dispatcher::Instance()->Decrypt($mObj->_GET['bas_kode']);
         $requestData['kode']       = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
         $requestData['nama']       = Dispatcher::Instance()->Decrypt($mObj->_GET['nama']);
      }else{
         $requestData['bas_kode']   = '';
         $requestData['kode']       = '';
         $requestData['nama']       = '';
      }

      if(method_exists(Dispatcher::instance(), 'getQueryString')){
         # @param array
         $queryString   = Dispatcher::instance()->getQueryString($requestData);
      }else{
         $query         = array();
         foreach ($requestData as $key => $value) {
            $query[$Key]   = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString   = urldecode(http_build_query($query));
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

      $destination_id      = "subcontent-element";
      $dataList            = $mObj->getData($offset, $limit, (array)$requestData);
      $total_data          = $mObj->Count();

      if($messenger){
         $message       = $messenger[0][1];
         $style         = $messenger[0][2];
      }
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

      $return['data_list']    = $dataList;
      $return['start']        = $offset+1;
      $return['request_data'] = $requestData;
      $return['query_string'] = $queryString;
      $return['message']      = $message;
      $return['style']        = $style;
      return $return;
   }

   function ParseTemplate($data = null){
      $requestData      = $data['request_data'];
      $queryString      = $data['query_string'];
      $dataList         = $data['data_list'];
      $start            = $data['start'];
      $message          = $data['message'];
      $style            = $data['style'];
      $contentDelete    = 0;
      $urlSearch        = Dispatcher::Instance()->GetUrl(
         'finansi_mata_anggaran',
         'MataAnggaran',
         'view',
         'html'
      );

      $urlAdd           = Dispatcher::Instance()->GetUrl(
         'finansi_mata_anggaran',
         'AddMataAnggaran',
         'view',
         'html'
      ). '&' . $queryString;

      $urlEdit          = Dispatcher::Instance()->GetUrl(
         'finansi_mata_anggaran',
         'EditMataAnggaran',
         'view',
         'html'
      ) . '&' . $queryString;

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('content', 'URL_ADD', $urlAdd);

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         # delete
         $label         = "Label";
         $url_delete    = Dispatcher::Instance()->GetUrl(
            'finansi_mata_anggaran',
            'DeleteMataAnggaran',
            'do',
            'json'
         ).'&'.$queryString;
         $url_return    = Dispatcher::Instance()->GetUrl(
            'finansi_mata_anggaran',
            'MataAnggaran',
            'view',
            'html'
         ).'&search=1&'.$queryString;

         Messenger::Instance()->Send(
            'confirm',
            'confirmDelete',
            'do',
            'html',
         array(
            $label,
            $url_delete,
            $url_return
         ),
         Messenger::NextRequest
         );

         $this->mrTemplate->AddVar('content', 'URL_DELETE', Dispatcher::Instance()->GetUrl(
            'confirm',
            'confirmDelete',
            'do',
            'html'
         ));
         $this->mrTemplate->AddVar('content', 'control_return', $url_return);
         $this->mrTemplate->AddVar('content', 'control_label', $label);
         $this->mrTemplate->AddVar('content', 'control_action', $url_delete);

         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         foreach ($dataList as $list) {
            $this->mrTemplate->clearTemplate('checkbox');
            $this->mrTemplate->clearTemplate('status');
            $list['nomor']    = $start;
            $list['class_name']  = ($start % 2 <> 0) ? 'table-common-even' : '';
            if((int)$list['komponen'] <> 0){
               $this->mrTemplate->AddVar('checkbox', 'DISABLED', 'YES');
            }else{
               $this->mrTemplate->AddVar('checkbox', 'DISABLED', 'NO');
               $this->mrTemplate->AddVar('checkbox', 'ID', $list['id']);
               $this->mrTemplate->AddVar('checkbox', 'NAME', $list['kode'].' - '.$list['nama']);
               $contentDelete+=1;
            }

            if(strtoupper($list['status']) == 'Y'){
               $this->mrTemplate->AddVar('status', 'AKTIF', 'YES');
            }else{
               $this->mrTemplate->AddVar('status', 'AKTIF', 'NO');
            }

            $list['url_edit']    = $urlEdit.'&data_id='.Dispatcher::Instance()->Encrypt($list['id']);
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $start++;
         }
      }

      if((int)$contentDelete <> 0){
         $this->mrTemplate->setAttribute('btn_delete', 'visibility', 'visible');
      }

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }
   }
}
?>