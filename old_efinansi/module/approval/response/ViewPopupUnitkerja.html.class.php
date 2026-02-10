<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/approval/business/AppPopupUnitkerja.class.php';

class ViewPopupUnitkerja extends HtmlResponse
{
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/approval/template');
      $this->SetTemplateFile('view_popup_unitkerja.html');
   }

   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
         'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest() {
      $mObj          = new AppPopupUnitkerja();
      $requestData   = array();
      $queryString   = '';
      $arrTypeUnit   = $mObj->GetDataTipeUnit();

      if(isset($mObj->_POST['btncari'])){
         $requestData['kode']    = trim($mObj->_POST['kode']);
         $requestData['nama']    = trim($mObj->_POST['nama']);
         $requestData['tipe']    = $mObj->_POST['type'];
      }elseif(isset($mObj->_GET['search'])){
         $requestData['kode']    = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
         $requestData['nama']    = Dispatcher::Instance()->Decrypt($mObj->_GET['nama']);
         $requestData['tipe']    = Dispatcher::Instance()->Decrypt($mObj->_GET['tipe']);
      }else{
         $requestData['kode']    = '';
         $requestData['nama']    = '';
         $requestData['tipe']    = '';
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


      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'tipeunit',
         array(
            'type',
            $arrTypeUnit,
            $requestData['tipe'],
            true,
            'id="cmb_unit_tipe"'
         ),
         Messenger::CurrentRequest
      );
      $return['request_data']    = $requestData;
      $return['query_string']    = $queryString;
      $return['data_list']       = $mObj->ChangeKeyName($dataList);
      $return['start']           = $offset+1;

      /*$this->unitkerjaObj     = new AppPopupUnitkerja();
      $this->userUnitKerjaObj    = new UserUnitKerja();

      $userId        = trim(Security::Instance()->mAuthentication->
                       GetCurrentUser()->GetUserId());

      $role             = $this->userUnitKerjaObj->GetRoleUser($userId);
      $unitkerjaUserId  = $this->userUnitKerjaObj->GetUnitKerjaUser($userId);

      if($_POST || isset($_GET['cari'])) {
         if(isset($_POST['unitkerja_kode'])) {
            $kode       = $_POST['unitkerja_kode'];
         } elseif(isset($_GET['kode'])) {
            $kode    = Dispatcher::Instance()->Decrypt($_GET['kode']);
         } else {
            $kode       = '';
         }
         if(isset($_POST['unitkerja'])) {
            $unitkerja  = $_POST['unitkerja'];
         } elseif(isset($_GET['unitkerja'])) {
            $unitkerja  = Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
         } else {
            $unitkerja  = '';
         }

         if($_POST['tipeunit'] != "all") {
            $tipeunit   = $_POST['tipeunit'];
         } elseif(isset($_GET['tipeunit'])) {
            $tipeunit   = Dispatcher::Instance()->Decrypt($_GET['tipeunit']);
         } else {
            $tipeunit   = '';
         }
      }

   //view
      $totalData        = $this->unitkerjaObj->GetCountDataUnitkerja(
                                    $kode,
                                    $unitkerja,
                                    $tipeunit,
                                    $unitkerjaUserId['unit_kerja_id']);
      $itemViewed       = 20;
      $currPage         = 1;
      $startRec         = 0 ;
      if(isset($_GET['page'])) {
         $currPage      = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $startRec      =($currPage-1) * $itemViewed;
      }
      $dataUnitkerja       = $this->unitkerjaObj->getDataUnitkerja(
                                    $startRec,
                                    $itemViewed,
                                    $kode,
                                    $unitkerja,
                                       $tipeunit,
                                       $unitkerjaUserId['unit_kerja_id']);

      $url           = Dispatcher::Instance()->GetUrl(
                       Dispatcher::Instance()->mModule,
                       Dispatcher::Instance()->mSubModule,
                       Dispatcher::Instance()->mAction,
                       Dispatcher::Instance()->mType .
                       '&kode=' . Dispatcher::Instance()->Encrypt($kode) .
                       '&unitkerja=' . Dispatcher::Instance()->Encrypt($unitkerja) .
                       '&tipeunit=' . Dispatcher::Instance()->Encrypt($tipeunit) .
                       '&cari=' . Dispatcher::Instance()->Encrypt(1));
      $dest             = "popup-subcontent";
      Messenger::Instance()->SendToComponent
               ('paging', 'Paging', 'view', 'html', 'paging_top',
               array($itemViewed,$totalData, $url, $currPage, $dest),
               Messenger::CurrentRequest);

      $arr_tipeunit     = $this->unitkerjaObj->GetDataTipeunit();

      Messenger::Instance()->SendToComponent
               ('combobox', 'Combobox', 'view', 'html', 'tipeunit',
               array('tipeunit', $arr_tipeunit, $tipeunit,
               'true', ' style="width:200px;" '), Messenger::CurrentRequest);

      $msg           = Messenger::Instance()->Receive(__FILE__);

      $this->Pesan      = $msg[0][1];
      $this->css        = $msg[0][2];

      $return['dataUnitkerja']      = $dataUnitkerja;
      $return['start']           = $startRec+1;

      $return['search']['kode']     = $kode;
      $return['search']['unitkerja']   = $unitkerja;
      $return['search']['tipeunit']    = $tipeunit;*/

      return $return;
   }

   function ParseTemplate($data = NULL) {
       
      $requestData      = $data['request_data'];
      $start            = $data['start'];
      $dataList         = $data['data_list'];
      $urlSearch        = Dispatcher::Instance()->GetUrl(
         'approval',
         'popupUnitkerja',
         'view',
         'html'
      );

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVars('content', $requestData);

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         foreach ($dataList as $list) {
            $list['nomor']    = $start;
            if($list['parent_id'] == 0){
               $list['class_name']     = 'table-common-even1';
               $list['row_style']      = 'font-weight: bold;';
            }else{
               $list['class_name']     = ($start % 2 <> 0) ? 'table-common-even' : '';
               $list['row_style']      = '';
            }
            $list['name']     = str_replace("'","\'",$list['nama']);
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $start++;
         }
      }
   }
}
?>
