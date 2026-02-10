<?php
/**
* ================= doc ====================
* FILENAME     : ViewFinansiDipa.html.class.php
* @package     : ViewFinansiDipa
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-12-07
* @Modified    : 2014-12-07
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_dipa/business/FinansiDipa.class.php';

class ViewFinansiDipa extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_dipa/template/');
      $this->SetTemplateFile('view_finansi_dipa.html');
   }

   function ProcessRequest(){
      $message = $style = null;
      $mObj             = new FinansiDipa();
      $messenger        = Messenger::Instance()->Receive(__FILE__);
      $request_data     = array();

      // messenger
      if($messenger){
         $message       = $messenger[0][1];
         $style         = $messenger[0][2];
      }

      if(isset($mObj->_POST['btnSearch'])){
         $request_data['kode']      = trim($mObj->_POST['kode']);
      }elseif (isset($mObj->_GET['search'])) {
         $request_data['kode']      = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
      }else{
         $request_data['kode']      = '';
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         $query_string     = Dispatcher::Instance()->getQueryString($request_data);
      }else{
         $query            = array();
         foreach ($request_data as $key => $value) {
            $query[$key]   = Dispatcher::Instance()->Encrypt($value);
         }

         $query_string     = urldecode(http_build_query($query));
      }

      $offset     = 0;
      $limit      = 20;
      $page       = 0;
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
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$query_string;

      $destination_id   = "subcontent-element";
      $data_list        = $mObj->GetData($offset, $limit, $request_data);
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
      $start            = $offset+1;
      $page             = $page;
      $return           = compact('request_data', 'query_string', 'message', 'style', 'data_list', 'start');
      return $return;
   }

   function ParseTemplate($data = null){
      $page             = 1;
      if(isset($_GET['page'])){
         $page          = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
      }
      $request_data     = $data['request_data'];
      $query_string     = $data['query_string'];
      $data_list        = $data['data_list'];
      $start            = $data['start'];
      $message          = $data['message'];
      $style            = $data['style'];
      $count_data       = 0;
      $urlSearch        = Dispatcher::Instance()->GetUrl(
         'finansi_dipa',
         'FinansiDipa',
         'view',
         'html'
      );

      $urlAdd           = Dispatcher::Instance()->GetUrl(
         'finansi_dipa',
         'addDipa',
         'view',
         'html'
      ).'&'.$query_string;

      $urlEdit          = Dispatcher::Instance()->GetUrl(
         'finansi_dipa',
         'editDipa',
         'view',
         'html'
      ).'&'.$query_string;

      $urlSetStatus     = Dispatcher::Instance()->GetUrl(
         'finansi_dipa',
         'setStatus',
         'do',
         'json'
      ).'&'.$query_string;


      $this->mrTemplate->AddVars('content', $request_data);
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('content', 'URL_ADD', $urlAdd);

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }
      if(empty($data_list)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         foreach ($data_list as $list) {
            if(strtoupper($list['status']) == 'NOT_ACTIVE'){
               $count_data+=1;
            }
            $this->mrTemplate->clearTemplate('dipa_status');
            $this->mrTemplate->clearTemplate('checkbox');
            $list['number']         = $start;
            $id_enc                 = Dispatcher::Instance()->Encrypt($list['id']);
            if($list['nominal'] < 0){
               $list['nominal']     = '('.number_format(abs($list['nominal']), 2, ',','.').')';
            }else{
               $list['nominal']     = number_format($list['nominal'], 2, ',','.');
            }
            $list['url_edit']       = $urlEdit.'&data_id='.$id_enc;
            $list['url_set_status'] = $urlSetStatus;
            $this->mrTemplate->AddVar('dipa_status', 'STATUS', strtoupper($list['status']));
            $this->mrTemplate->AddVar('dipa_status', 'url_set_status', $list['url_set_status']);
            $this->mrTemplate->AddVar('dipa_status', 'ID', $list['id']);
            $this->mrTemplate->AddVar('checkbox', 'STATUS', strtoupper($list['status']));
            $this->mrTemplate->AddVars('checkbox', $list);
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $start+=1;
         }
      }

      // Process delete, tombol tampil jika memang ada data yang bisa dihapus
      if($count_data <> 0){
         $this->mrTemplate->SetAttribute('action_delete', 'visibility', 'visible');
         # delete
         $label         = GTFWConfiguration::GetValue('language', 'dipa');
         $url_delete    = Dispatcher::Instance()->GetUrl(
            'finansi_dipa',
            'DeleteDipa',
            'do',
            'html'
         ).'&'.$query_string;

         $url_return    = Dispatcher::Instance()->GetUrl(
            'finansi_dipa',
            'FinansiDipa',
            'view',
            'html'
         ).'&search=1&'.$query_string;
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

         $this->mrTemplate->AddVar(
            'content',
            'URL_DELETE',
            Dispatcher::Instance()->GetUrl(
               'confirm',
               'confirmDelete',
               'do',
               'html'
            )
         );

         $this->mrTemplate->AddVar('content', 'CONTROL_LABEL', $label);
         $this->mrTemplate->AddVar('content', 'CONTROL_ACTION', $url_delete);
         $this->mrTemplate->AddVar('content', 'CONTROL_RETURN', $url_return);
      }
   }
}
?>