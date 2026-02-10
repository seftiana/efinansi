<?php
// require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
// 'module/periode_tahun/response/ProcessPeriodeTahun.proc.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/periode_tahun/business/PeriodeTahun.class.php';

class ViewPeriodeTahun extends HtmlResponse
{

   /*protected $proc;

   public function ViewPeriodeTahun()
   {
     $this->proc = new ProcessPeriodeTahun();
   }*/

   public function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/periode_tahun/template');
      $this->SetTemplateFile('view_periode_tahun.html');
   }

   public function ProcessRequest()
   {
      $mObj          = new PeriodeTahun();
      $messenger     = Messenger::Instance()->Receive(__FILE__);
      $requestData   = array();
      $queryString   = '';
      $message       = $style = NULL;
      $arrRenstra    = $mObj->getRenstra();
      $renstra       = $mObj->getRenstra(true); // mendapatkan data renstra yang aktif

      if(isset($mObj->_POST['btncari'])){
         $requestData['renstra_id']    = $mObj->_POST['renstra'];
         $requestData['nama']          = trim($mObj->_POST['nama']);
      }elseif (isset($mObj->_GET['search'])) {
         $requestData['renstra_id']    = Dispatcher::Instance()->Decrypt($mObj->_GET['renstra_id']);
         $requestData['nama']          = Dispatcher::Instance()->Decrypt($mObj->_GET['nama']);
      }else{
         $requestData['renstra_id']    = (empty($renstra)) ? NULL : $renstra[0]['id'];
         $requestData['nama']          = '';
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

      $destination_id   = "subcontent-element";
      $dataList         = $mObj->getData($offset, $limit, (array)$requestData);
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

      // parse messenger
      if($messenger){
         $message       = $messenger[0][1];
         $style         = $messenger[0][2];
      }

      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'cmb_renstra',
         array(
            'renstra',
            $arrRenstra,
            $requestData['renstra_id'],
            true,
            'id="cmb_renstra"'
         ),
         Messenger::CurrentRequest
      );

      $return['request_data']    = $requestData;
      $return['query_string']    = $queryString;
      $return['data_list']       = $mObj->ChangeKeyName($dataList);
      $return['start']           = $offset+1;
      $return['message']         = $message;
      $return['style']           = $style;

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
         'periode_tahun',
         'PeriodeTahun',
         'view',
         'html'
      );
      $urlAdd           = Dispatcher::Instance()->GetUrl(
         'periode_tahun',
         'inputPeriodeTahun',
         'view',
         'html'
      ).'&'.$queryString;

      $urlEdit          = Dispatcher::Instance()->GetUrl(
         'periode_tahun',
         'EditPeriodeTahun',
         'view',
         'html'
      ).'&'.$queryString;

      $urlSetAktif      = Dispatcher::Instance()->GetUrl(
         'periode_tahun',
         'SetAktif',
         'do',
         'json'
      ).'&'.$queryString;

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('content', 'URL_ADD', $urlAdd);
      $this->mrTemplate->AddVars('content', $requestData);

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }

      $parseUrl      = parse_url($queryString);
      $urlExploded   = explode('&', $parseUrl['path']);
      $urlIndex      = 0;
      foreach ($urlExploded as $url) {
         list($urlKey[$urlIndex], $urlValue[$urlIndex]) = explode('=', $url);
         $patern     = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/';
         $patern1    = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/';
         if((preg_match($patern, $urlValue[$urlIndex]) || preg_match($patern1, $urlValue[$urlIndex])) && strtotime($urlValue[$urlIndex]) !== false){
            $urlValue[$urlIndex]    = date('Y/m/d', strtotime($urlValue[$urlIndex]));
         }
         $urlIndex   += 1;
      }
      unset($urlIndex);
      $keyUrl     = implode('|', $urlKey);
      $valueUrl   = implode('|', $urlValue);

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         foreach ($dataList as $list) {
            $this->mrTemplate->clearTemplate('status_open');
            $this->mrTemplate->clearTemplate('status_aktif');
            $this->mrTemplate->clearTemplate('link_status');
            $list['nomor']       = $start;
            $list['class_name']  = ($start % 2 <> 0) ? 'table-common-even' : '';
            $list['row_style']   = (strtoupper($list['status_aktif']) == 'Y') ? 'font-weight: bold;' : '';
            $list['url_edit']    = $urlEdit.'&data_id='.Dispatcher::Instance()->Encrypt($list['id']);
            $urlAccept           = 'periode_tahun|deletePeriodeTahun|do|html-search|'.$keyUrl.'-1|'.$valueUrl;
            $urlReturn           = 'periode_tahun|PeriodeTahun|view|html-search|'.$keyUrl.'-1|'.$valueUrl;
            $label               = 'Periode Tahun';
            $message             = 'Penghapusan Data ini akan menghapus Data secara permanen.';
            $list['url_delete']  = Dispatcher::Instance()->GetUrl(
               'confirm',
               'confirmDelete',
               'do',
               'html'
            ).'&urlDelete='. $urlAccept
            .'&urlReturn='.$urlReturn
            .'&id='.$list['id']
            .'&label='.$label
            .'&dataName='.$list['nama']
            .'&message='.$message;

            if(strtoupper($list['status_renstra']) == 'Y' AND $list['status_aktif'] == 'Y'){
               $this->mrTemplate->AddVar('status_aktif', 'AKTIF', 'YES');
            }elseif(strtoupper($list['status_renstra']) == 'Y' AND strtoupper($list['status_aktif']) == 'T'){
               $this->mrTemplate->AddVar('status_aktif', 'AKTIF', 'NO');
               $this->mrTemplate->AddVar('status_aktif', 'URL_AKTIF', $urlSetAktif);
               $this->mrTemplate->AddVar('status_aktif', 'DATA_ID', $list['id']);
            }elseif(strtoupper($list['status_renstra']) == 'T' AND strtoupper($list['status_aktif']) == 'T'){
               $this->mrTemplate->AddVar('status_aktif', 'AKTIF', 'NOT_ACTIVE');
            }elseif(strtoupper($list['status_renstra']) == 'T'  AND $list['status_aktif'] == 'Y'){
               $this->mrTemplate->AddVar('status_aktif', 'AKTIF', 'ACTIVE');
            }

            if($list['status_open'] == 'Y'){
               $this->mrTemplate->AddVar('status_open', 'OPEN', 'YES');
            }else{
               $this->mrTemplate->AddVar('status_open', 'OPEN', 'NO');
            }

            if(strtoupper($list['status_renstra']) == 'Y' && strtoupper($list['status_aktif']) == 'Y'){
               $this->mrTemplate->AddVar('link_status', 'ENABLE', 'ONLY_EDIT');
            }elseif(strtoupper($list['status_renstra']) == 'Y'){
               $this->mrTemplate->AddVar('link_status', 'ENABLE', 'YES');
            }elseif(strtoupper($list['status_renstra']) == 'T'){
               $this->mrTemplate->AddVar('link_status', 'ENABLE', 'NO');
            }else{
               $this->mrTemplate->clearTemplate('link_status');
            }

            $this->mrTemplate->AddVars('link_status', $list);
            $this->mrTemplate->AddVars('data_item', $list);
            $this->mrTemplate->parseTemplate('data_item', 'a');
            $start++;
         }
      }
   }
}
?>