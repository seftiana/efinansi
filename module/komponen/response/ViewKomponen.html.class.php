<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'module/komponen/business/Komponen.class.php';

class ViewKomponen extends HtmlResponse
{
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/komponen/template');
      $this->SetTemplateFile('view_komponen.html');
   }

   function ProcessRequest() {
      $ObjKomponen      = new Komponen();
      //get message
      $msg              = Messenger::Instance()->Receive(__FILE__);
      $requestData      = array();
      $queryString      = '';

      if(isset($ObjKomponen->_POST['btncari'])){
         $requestData['nama']    = trim($ObjKomponen->_POST['nama_komponen']);
      }elseif(isset($ObjKomponen->_GET['search'])){
         $requestData['nama']    = Dispatcher::Instance()->Decrypt($ObjKomponen->_GET['nama']);
      }else{
         $requestData['nama']    = '';
      }


      $offset        = 0;
      $limit         = 20;
      $page          = 0;
      if(isset($_GET['page'])){
         $page       = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset     = ($page - 1) * $limit;         
      } 

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         $queryString      = Dispatcher::Instance()->getQueryString($requestData);
      }else{
         $query            = array();
         foreach ($requestData as $key => $value) {
            $query[$key]   = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString      = urldecode(http_build_query($query));
      }
      
      #paging url
      $url           = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;

      $destination_id   = "subcontent-element";
      $dataList         = $ObjKomponen->GetData($offset, $limit, $requestData);
      $total_data       = $ObjKomponen->Count();
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

      if($msg){
         $message       = $msg[0][1];
         $style         = $msg[0][2];
      }

      $return['query_string']    = $queryString;
      $return['request_data']    = $requestData;
      $return['data_list']       = $ObjKomponen->ChangeKeyName($dataList);
      $return['start']           = $offset+1;
      $return['message']         = $message;
      $return['style']           = $style;
      $return['page']           = $_GET['page'];
      return $return;
   }

   function ParseTemplate($data = NULL) {
       $page = $data['page'];
      $requestData         = $data['request_data'];
      $queryString         = $data['query_string'];
      $dataList            = $data['data_list'];
      $start               = $data['start'];
      $message             = $data['message'];
      $style               = $data['style'];
      $urlSearch           = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType
      );
      $urlAdd              = Dispatcher::Instance()->GetUrl(
         'komponen',
         'InputKomponen',
         'view',
         'html'
      ).'&'.$queryString.'&page='.$page;
      $urlExcel            = Dispatcher::Instance()->GetUrl(
         'komponen',
         'Komponen',
         'view',
         'xlsx'
      ).'&'.$queryString;

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('content', 'URL_ADD', $urlAdd);
      $this->mrTemplate->AddVar('content', 'URL_EXCEL', $urlExcel);
      $this->mrTemplate->AddVars('content', $requestData);

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }

      //mulai bikin tombol delete
      $label         = GTFWConfiguration::GetValue('language', 'manajemen_komponen');
      $urlDelete     = Dispatcher::Instance()->GetUrl(
         'komponen',
         'DeleteKomponen',
         'do',
         'html'
      ).'&'.$queryString;
      $urlReturn     = Dispatcher::Instance()->GetUrl(
         'komponen',
         'Komponen',
         'view',
         'html'
      ).'&'.$queryString;
      Messenger::Instance()->Send(
         'confirm',
         'ConfirmDelete',
         'do',
         'html',
         array(
            $label,
            $urlDelete,
            $urlReturn
         ),Messenger::NextRequest);
     $this->mrTemplate->AddVar('content', 'URL_DELETE', Dispatcher::Instance()->GetUrl('confirm', 'ConfirmDelete', 'do', 'html'));
     $this->mrTemplate->AddVar('content', 'CONTROL_LABEL', $label);
     $this->mrTemplate->AddVar('content', 'CONTROL_ACTION', $urlDelete);
     $this->mrTemplate->AddVar('content', 'CONTROL_RETURN', $urlReturn);

     if (empty($dataList)) {
       $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
     } else {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');

         foreach ($dataList as $list) {
            $this->mrTemplate->clearTemplate('komponen');
            $list['number']         = $start;
            $list['harga_satuan']   = number_format($list['harga_satuan'], 0, ',','.');
            $list['class_name']     = ($start % 2 <> 0) ? 'table-common-even' : '';
            $list['url_edit']       = $urlAdd.'&dataId='.Dispatcher::Instance()->Encrypt($list['id']);
            if((int)$list['komponen'] <> 0){
               $this->mrTemplate->AddVar('komponen', 'DISABLED', 'YES');
            }else{
               $this->mrTemplate->AddVar('komponen', 'DISABLED', 'NO');
               $this->mrTemplate->AddVar('komponen', 'ID', $list['id']);
               $this->mrTemplate->AddVar('komponen', 'NAMA', $list['nama']);
            }
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $start++;
         }
      }
   }
}
?>