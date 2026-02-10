<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/rencana_penerimaan/business/AppPopupKodePenerimaan.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewPopupKodePenerimaan extends HtmlResponse
{
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/rencana_penerimaan/template');
      $this->SetTemplateFile('view_popup_kodepenerimaan.html');
   }

   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest() {
      $mObj       = new AppPopupKodePenerimaan();
      $mUnitObj   = new UserUnitKerja();
      $requestQuery  = $mObj->_getQueryString();
      $requestData   = array();
      $queryString   = '';
      $requestData['unit_id']    = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
      $requestData['kode']       = '';
      $requestData['nama']       = '';

      if(isset($mObj->_POST['btncari'])){
         $requestData['kode']    = trim($mObj->_POST['kode']);
         $requestData['nama']    = trim($mObj->_POST['nama']);
         $requestData['unit_id'] = $mObj->_POST['unit_id'];
      }elseif(isset($mObj->_GET['search'])){
         $requestData['kode']    = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
         $requestData['nama']    = Dispatcher::Instance()->Decrypt($mObj->_GET['nama']);
         $requestData['unit_id'] = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
      }

      $unitKerja        = $mUnitObj->GetUnitKerja($requestData['unit_id']);
      $requestData['unit_id']    = $unitKerja['unit_kerja_id'];
      $requestData['unit_kode']  = $unitKerja['unit_kerja_kode'];
      $requestData['unit_nama']  = $unitKerja['unit_kerja_nama'];

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

      $destination_id   = "popup-subcontent";
      // reset unit id, Kode penerimaan ndak pake alokasi unit
      $requestData['unit_id'] = NULL;
      $dataList         = $mObj->GetData($offset, $limit, (array)$requestData);
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


      $return['query_string']    = $queryString;
      $return['request_data']    = $requestData;
      $return['request_query']   = $requestQuery;
      $return['data_list']       = $mObj->ChangeKeyName($dataList);
      $return['start']           = $offset+1;

      return $return;
   }

   function ParseTemplate($data = NULL)
   {
      $requestData         = $data['request_data'];
      $requestQuery        = $data['request_query'];
      $queryString         = $data['query_string'];
      $dataList            = $data['data_list'];
      $start               = $data['start'];
      $dataReferensi       = array();
      $urlSearch           = Dispatcher::Instance()->GetUrl(
         'rencana_penerimaan',
         'popupKodePenerimaan',
         'view',
         'html').'&'.$requestQuery;
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         foreach($dataList as $list){
            $dataReferensi[$list['id']]   = $list;
            $this->mrTemplate->clearTemplate('show_button_pilih');
            if(strtoupper($list['tipe']) == 'HEADER' and (int)$list['child'] <> 0){
               $this->mrTemplate->AddVar('show_button_pilih', 'IS_SHOW', 'NO');
               $list['row_style']      = 'font-weight: bold;';
               $list['alokasi_unit']   = '-';
               $list['alokasi_pusat']  = '-';
               $list['satuan']         = '';
            }else{
               $this->mrTemplate->AddVar('show_button_pilih', 'IS_SHOW', 'YES');
               $this->mrTemplate->AddVar('show_button_pilih', 'ID', $list['id']);
               $list['row_style']      = '';
               if($list['alokasi_id'] !== NULL){
                  $list['alokasi_unit']   = number_format($list['alokasi_unit'], 0, ',','.');
               }else{
                  $list['alokasi_unit']   = '-';
               }

               if($list['alokasi_pusat_id'] !== NULL){
                  $list['alokasi_pusat']  = number_format($list['alokasi_pusat'], 0, ',','.');
               }else{
                  $list['alokasi_pusat']  = '-';
               }
            }

            $list['nomor']          = $start;
            $list['class_name']     = ($start % 2 <> 0)? 'table-common-even' : '';
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $start++;
         }
      }

      $json['data']     = json_encode($dataReferensi);
      $this->mrTemplate->AddVars('content', $json, 'REFERENSI_');
   }
}
?>