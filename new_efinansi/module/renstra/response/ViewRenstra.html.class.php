<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/renstra/business/Renstra.class.php';

class ViewRenstra extends HtmlResponse
{
   function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/renstra/template');
      $this->SetTemplateFile('view_renstra.html');
   }

   function ProcessRequest()
   {
      $msg              = Messenger::Instance()->Receive(__FILE__);
      $mObj             = new Renstra();
      $message = $style = $messenger_data = $buildUri    = NULL;
      $request_data     = array();
      $setYear          = $mObj->GetRangeTahun();
      $year             = $setYear['result'];
      $startYear        = $year['start_year'];
      $endYear          = $year['end_year'];
      $arrTahun         = $setYear['range_year'];
      // handle messenger
      if($msg){
         $message          = $msg[0][1];
         $style            = $msg[0][2];
         $messenger_data   = $msg[0][0];
      }

      // end handle messenger
      if(isset($mObj->_POST['btncari'])){
         $request_data['nama']         = trim($mObj->_POST['nama']);
         $request_data['start_year']   = $mObj->_POST['startYear'];
         $request_data['end_year']     = $mObj->_POST['endYear'];
      }elseif(isset($mObj->_GET['search'])){
         $request_data['nama']         = Dispatcher::Instance()->Decrypt($mObj->_GET['nama']);
         $request_data['start_year']   = Dispatcher::Instance()->Decrypt($mObj->_GET['start_year']);
         $request_data['end_year']     = Dispatcher::Instance()->Decrypt($mObj->_GET['end_year']);
      }else{
         $request_data['nama']         = '';
         $request_data['start_year']   = $startYear;
         $request_data['end_year']     = $endYear;
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         $buildUri            = Dispatcher::Instance()->getQueryString((array)$request_data);
      }else{
         $query      = array();
         foreach ($request_data as $key => $value) {
            $query[$key]      = Dispatcher::Instance()->Encrypt($value);
         }
         $buildUri            = urldecode(http_build_query($query));
      }

      $offset        = 0;
      $limit         = 20;
      $page          = 0;
      if(isset($_GET['page'])){
         $page       = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset     = ($page - 1) * $limit;
      }
      #paging url
      $url           = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$buildUri;

      $destination_id   = "subcontent-element";
      $data_list        = $mObj->GetDataRenstra($offset, $limit, (array)$request_data);
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

      Messenger::Instance()->SendToComponent(
         'paging',
         'Paging',
         'view',
         'html',
         'paging_bottom',
         array(
            $limit,
            $total_data,
            $url,
            $page,
            $destination_id
         ),
         Messenger::CurrentRequest
      );


      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'startYear',
         array(
            'startYear',
            $arrTahun,
            $request_data['start_year'],
            false,
            'id="cmb_start-year"'
         ),
         Messenger::CurrentRequest
      );

      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'endYear',
         array(
            'endYear',
            $arrTahun,
            $request_data['end_year'],
            false,
            'id="cmb_end-year"'
         ),
         Messenger::CurrentRequest
      );

      $return['start']           = $offset+1;
      $return['request_data']    = $request_data;
      $return['dataList']        = $mObj->ChangeKeyName($data_list);
      $return['message']         = $message;
      $return['style']           = $style;
      $return['query_string']    = $buildUri;
      return $return;
   }

   function ParseTemplate($data = NULL)
   {
      $start               = $data['start'];
      $dataList            = $data['dataList'];
      $request_data        = $data['request_data'];
      $query_string        = $data['query_string'];
      $message             = $data['message'];
      $style               = $data['style'];

      $parseUrl      = parse_url($query_string);
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

      $urlSearch           = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType
      );
      $urlAdd              = Dispatcher::Instance()->GetUrl(
         'renstra',
         'inputRenstra',
         'view',
         'html'
      ).'&'.$query_string;
      $urlAktif            = Dispatcher::Instance()->GetUrl(
         'renstra',
         'SetAktifRenstra',
         'do',
         'json'
      ).'&'.$query_string;

      $urlEdit             = Dispatcher::Instance()->GetUrl(
         'renstra',
         'EditRenstra',
         'view',
         'html'
      ).'&'.$query_string;

      $urlDetail           = Dispatcher::Instance()->GetUrl(
         'renstra',
         'DetailRenstra',
         'view',
         'html'
      ).'&'.$query_string;

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('content', 'URL_ADD',  $urlAdd);
      $this->mrTemplate->AddVars('content', $request_data);

      if (isset ($message)) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }

      if (empty($dataList)) {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
         foreach ($dataList as $list) {
            //dipake componenet confirm delete
            $urlAccept  = 'renstra|deleteRenstra|do|html-search|'.$keyUrl.'-1|'.$valueUrl;
            $urlReturn  = 'renstra|renstra|view|html-search|'.$keyUrl.'-1|'.$valueUrl;
            $label      = 'Renstra';
            $dataName   = $list['nama'];
            $list['url_delete']     = Dispatcher::Instance()->GetUrl(
               'confirm',
               'confirmDelete',
               'do',
               'html'
            ).'&urlDelete='. $urlAccept
            .'&urlReturn='.$urlReturn
            .'&id='.Dispatcher::Instance()->Encrypt($list['id'])
            .'&label='.$label.'&dataName='.$dataName;
            $list['url_detail']     = $urlDetail.'&data_id='.Dispatcher::Instance()->Encrypt($list['id']);

            $list['nomor']          = $start;
            $list['url_edit']       = $urlEdit.'&data_id='.Dispatcher::Instance()->Encrypt($list['id']);
            if(strtoupper($list['status']) === 'Y'){
               $this->mrTemplate->AddVar('status_aktif', 'IS_AKTIF', 'YES');
               $this->mrTemplate->AddVar('status', 'IS_AKTIF', 'YES');
            }else{
               $this->mrTemplate->AddVar('status_aktif', 'IS_AKTIF', 'NO');
               $this->mrTemplate->AddVar('status', 'IS_AKTIF', 'NO');
               $this->mrTemplate->AddVar('status', 'DATA_URL_DELETE', $list['url_delete']);
               $this->mrTemplate->AddVar('status_aktif', 'DATA_URL_AKTIF', $urlAktif.'&grp=' . Dispatcher::Instance()->Encrypt($list['id']));
               $this->mrTemplate->AddVar('status_aktif', 'DATA_ID', $list['id']);
            }
            $this->mrTemplate->AddVars('data_item', $list, 'DATA_');
            $this->mrTemplate->parseTemplate('data_item', 'a');
            $start++;
         }
      }
   }
}
?>