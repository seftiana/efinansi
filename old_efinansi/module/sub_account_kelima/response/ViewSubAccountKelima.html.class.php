<?php
/**
 * class ViewSubAccountKelima
 * @package sub_account_kelima
 * @copyright 2011 gamatechno
 */

require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/sub_account_kelima/business/SubAccountKelima.class.php';

/**
 * class ViewSubAccountKelima
 * untuk menangani tampilan sub account kelima
 * @access public
 */
class ViewSubAccountKelima extends HtmlResponse
{
   public function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/sub_account_kelima/template');
      $this->SetTemplateFile('view_sub_account_kelima.html');
   }

   public function ProcessRequest()
   {
      $messenger     = Messenger::Instance()->Receive(__FILE__);
      $mObj          = new SubAccountKelima();
      $requestData   = array();
      $queryString   = '';
      $message       = $style = NULL;

      if(isset($mObj->_POST['btncari'])){
         $requestData['kode']    = $mObj->_POST['kode'];
      }elseif(isset($mObj->_GET['search'])){
         $requestData['kode']    = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
      }else{
         $requestData['kode']    = '';
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         # @param array
         $queryString      = Dispatcher::instance()->getQueryString($requestData);
      }else{
         $query            = array();
         foreach ($requestData as $key => $value) {
            $query[$key]   = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString      = urldecode(http_build_query($query));
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
      $destination_id   = "subcontent-element";
      $dataList         = $mObj->getData($offset, $limit, $requestData['kode']);
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

      if($messenger){
         $message       = $messenger[0][1];
         $style         = $messenger[0][2];
      }

      $return['data_list']    = $mObj->ChangeKeyName($dataList);
      $return['query_string'] = $queryString;
      $return['request_data'] = $requestData;
      $return['start']        = $offset+1;
      $return['message']      = $message;
      $return['style']        = $style;
      return $return;
   }

   public function ParseTemplate($data = NULL)
   {
      $requestData      = $data['request_data'];
      $queryString      = $data['query_string'];
      $dataList         = $data['data_list'];
      $start            = $data['start'];
      $message          = $data['message'];
      $style            = $data['style'];
      $urlSearch        = Dispatcher::Instance()->GetUrl(
         'sub_account_kelima',
         'subAccountKelima',
         'view',
         'html'
      );

      $urlAdd           = Dispatcher::Instance()->GetUrl(
         'sub_account_kelima',
         'inputSubAccountKelima',
         'view',
         'html'
      ).'&'.$queryString;

      $urlEdit          = Dispatcher::Instance()->GetUrl(
         'sub_account_kelima',
         'EditSubAccountKelima',
         'view',
         'html'
      ).'&'.$queryString;

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('content', 'URL_ADD', $urlAdd);
      $this->mrTemplate->SetAttribute('button_delete', 'visibility', 'hidden');

      if($message) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }

      $label      = "Kode Sub Account Kelima";
      $urlDelete  = Dispatcher::Instance()->GetUrl(
         'sub_account_kelima',
         'deleteSubAccountKelima',
         'do',
         'html'
      ).'&'.$queryString;
      $urlReturn = Dispatcher::Instance()->GetUrl(
         'sub_account_kelima',
         'subAccountKelima',
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
            $urlDelete,
            $urlReturn
         ),
      Messenger::NextRequest);
      $this->mrTemplate->AddVar('content', 'CONTROL_LABEL', $label);
      $this->mrTemplate->AddVar('content', 'CONTROL_RETURN', $urlReturn);
      $this->mrTemplate->AddVar('content', 'CONTROL_ACTION',$urlDelete);
      $this->mrTemplate->AddVar('content', 'URL_DELETE', Dispatcher::Instance()->GetUrl(
         'confirm',
         'confirmDelete',
         'do',
         'html'
      ));

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $this->mrTemplate->SetAttribute('button_delete', 'visibility', 'visible');

         foreach ($dataList as $list) {
            $list['url_edit']          = $urlEdit.'&data_id='.$list['kode'];
            if(strtoupper($list['nama']) === 'DEFAULT'){
               $list['class_name']     = 'table-common-even1';
               $list['row_style']      = 'font-weight: bold';
               $this->mrTemplate->AddVar('content_checkbox', 'ENABLE', 'NO');
               $this->mrTemplate->AddVar('content_links', 'STATUS', 'HIDE');
            }else{
               $list['row_style']      = '';
               $list['class_name']     = ($start % 2 <> 0) ? 'table-common-even' : '';
               $this->mrTemplate->AddVar('content_checkbox', 'ENABLE', 'YES');
               $this->mrTemplate->AddVar('content_links', 'STATUS', 'SHOW');
               $this->mrTemplate->AddVar('content_links', 'URL_EDIT', $list['url_edit']);
            }
            $list['number']            = $start;
            $this->mrTemplate->AddVars('content_checkbox', $list);
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $start++;
         }
      }
   }
}
?>