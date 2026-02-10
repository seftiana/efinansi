<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/pembalik_approval_penerimaan/business/AppRencanaPenerimaan.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewRencanaPenerimaan extends HtmlResponse {

   var $Pesan;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/pembalik_approval_penerimaan/template');
      $this->SetTemplateFile('view_rencana_penerimaan.html');
   }

   function ProcessRequest() {
      $messenger  = Messenger::Instance()->Receive(__FILE__);
      $userId     = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $mObj       = new AppRencanaPenerimaan();
      $mUnitObj   = new UserUnitKerja();
      $dataUnit   = $mUnitObj->GetUnitKerjaRefUser($userId);
      $arrPeriodeTahun        = $mObj->GetPeriodeTahun();
      $periodeTahun           = $mObj->GetPeriodeTahun(array(
         'active' => true
      ));
      $arrStatusApproval      = $mObj->GetStatusApproval();
      $requestData            = array();

      if(isset($mObj->_POST['btncari'])){
         $requestData['ta_id']      = $mObj->_POST['tahun_anggaran'];
         $requestData['unit_id']    = $mObj->_POST['unitkerja'];
         $requestData['unit_nama']  = $mObj->_POST['unitkerja_label'];
         $requestData['status']     = $mObj->_POST['approval'];
         $requestData['kode']       = $mObj->_POST['kode'];
      }elseif(isset($mObj->_GET['search'])){
         $requestData['ta_id']      = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
         $requestData['unit_id']    = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
         $requestData['unit_nama']  = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
         $requestData['status']     = Dispatcher::Instance()->Decrypt($mObj->_GET['status']);
         $requestData['kode']       = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
      }else{
         $requestData['ta_id']      = $periodeTahun[0]['id'];
         $requestData['unit_id']    = $dataUnit['id'];
         $requestData['unit_nama']  = $dataUnit['nama'];
         $requestData['status']     = '';
         $requestData['kode']       = '';
      }

      foreach ($arrPeriodeTahun as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama'] = $ta['name'];
         }
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         $queryString         = Dispatcher::Instance()->getQueryString($requestData);
      }else{
         $query      = array();
         foreach ($requestData as $key => $value) {
            $query[$key]      = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString         = urldecode(http_build_query($query));
      }

      $offset         = 0;
      $limit          = 20;
      $page           = 0;
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
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;

      $destination_id   = "subcontent-element";
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

      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'tahun_anggaran',
         array(
            'tahun_anggaran',
            $arrPeriodeTahun,
            $requestData['ta_id'],
            false,
            ' style="width:200px;" id="cmb_tahun_anggaran"'
         ), Messenger::CurrentRequest);

      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'approval',
         array(
            'approval',
            $arrStatusApproval,
            $requestData['status'],
            true,
            ' style="width:150px;" id="cmb_approval"'
         ), Messenger::CurrentRequest);

      if($messenger){
         $messengerMsg        = $messenger[0][1];
         $messengerStyle      = $messenger[0][2];
      }
      $return['message']      = $messengerMsg;
      $return['style']        = $messengerStyle;
      $return['request_data'] = $requestData;
      $return['data_unit']    = $mObj->ChangeKeyName($dataUnit);
      $return['query_string'] = $queryString;
      $return['data_list']    = $mObj->ChangeKeyName($dataList);
      $return['start']        = $offset+1;
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $page             = 0;
      if(isset($_GET['page'])){
         $page          = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
      }
      $dataUnit         = $data['data_unit'];
      $requestData      = $data['request_data'];
      $start            = $data['start'];
      $queryString      = $data['query_string'].'&page='.$page;
      $dataList         = $data['data_list'];
      $message          = $data['message'];
      $style            = $data['style'];
      $urlSearch        = Dispatcher::Instance()->GetUrl(
         'pembalik_approval_penerimaan',
         'rencanaPenerimaan',
         'view',
         'html'
      );
      $urlPopupUnit     = Dispatcher::Instance()->GetUrl(
         'pembalik_approval_penerimaan',
         'PopupUnitkerja',
         'view',
         'html'
      );
      $urlInputPenerimaan  = Dispatcher::Instance()->GetUrl(
         'pembalik_approval_penerimaan',
         'InputRencanaPenerimaan',
         'view',
         'html'
      ).'&'.$queryString;
      $urlDetail           = Dispatcher::Instance()->GetUrl(
         'pembalik_approval_penerimaan',
         'PopupDetailRencanaPenerimaan',
         'view',
         'html'
      ).'&'.$queryString;

      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('data_unit', 'STATUS', strtoupper($dataUnit['status']));
      $this->mrTemplate->AddVar('data_unit', 'URL_POPUP_UNIT', $urlPopupUnit);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_ID', $requestData['unit_id']);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_NAMA', $requestData['unit_nama']);
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);

      if($message) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }

      if (empty($dataList)) {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');

         // inisialisasi data
         $penerimaanUnit      = array();
         $unit                = '';
         $dataGrid            = array();
         $index               = 0;
         for ($i=0; $i < count($dataList);) {
            if((int)$unit === (int)$dataList[$i]['unit_id']){
               $penerimaanUnit[$unit]['nominal']   += $dataList[$i]['nominal'];
               $dataGrid[$index]['nomor'] = $start;
               $dataGrid[$index]['id']       = $dataList[$i]['id'];
               $dataGrid[$index]['kode']     = $dataList[$i]['kode'];
               $dataGrid[$index]['nama']     = $dataList[$i]['nama'];
               $dataGrid[$index]['nominal']  = $dataList[$i]['nominal'];
               $dataGrid[$index]['status']   = strtoupper($dataList[$i]['status_nama']);
               $dataGrid[$index]['level']    = 'DATA';
               $dataGrid[$index]['class_name']  = $start % 2 <> 0 ? 'table-common-even' : '';
               $dataGrid[$index]['row_style']   = '';
               $start++;
               $i++;
            }else{
               $unit          = (int)$dataList[$i]['unit_id'];
               $kodeSistem    = $dataList[$i]['unit_id'];
               $penerimaanUnit[$kodeSistem]['nominal']   = 0;
               $dataGrid[$index]['id']       = $dataList[$i]['unit_id'];
               $dataGrid[$index]['kode']     = $dataList[$i]['unit_kode'];
               $dataGrid[$index]['nama']     = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['level']    = 'UNIT';
               $dataGrid[$index]['status']   = 'UNIT';
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['class_name']  = 'table-common-even1';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
            }
            $index++;
         }

         foreach ($dataGrid as $list) {
            switch (strtoupper($list['level'])) {
               case 'UNIT':
                  $list['nominal'] = number_format($penerimaanUnit[$list['kode_sistem']]['nominal'], 0, ',','.');
                  break;
               case 'DATA':
                  $list['nominal'] = number_format($list['nominal'], 0, ',','.');
                  break;
               default:
                  $list['nominal'] = number_format($list['nominal'], 0, ',','.');
                  break;
            }
            $this->mrTemplate->AddVar('status', 'STATUS', strtoupper($list['status']));
            $this->mrTemplate->AddVar('link_status', 'STATUS', strtoupper($list['status']));
            $this->mrTemplate->AddVar('link_status', 'URL_PEMBALIK', $urlInputPenerimaan.'&dataId='.Dispatcher::Instance()->Encrypt($list['id']));
            $this->mrTemplate->AddVar('link_status', 'URL_DETAIL', $urlDetail.'&dataId='.Dispatcher::Instance()->Encrypt($list['id']));
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
      }
   }
}
?>