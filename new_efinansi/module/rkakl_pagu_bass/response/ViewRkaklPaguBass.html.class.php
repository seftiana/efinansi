<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/rkakl_pagu_bass/business/RkaklPaguBass.class.php';
class ViewRkaklPaguBass extends HtmlResponse {
   var $Pesan;
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/rkakl_pagu_bass/template');
      $this->SetTemplateFile('view_rkakl_pagu_bass.html');
   }

   function ProcessRequest() {
      $Obj        = new RkaklPaguBass();
      $requestData   = array();
      $queryString   = '';

      if(isset($Obj->_POST['btncari'])){
         $requestData['kode']    = $Obj->_POST['kode'];
         $requestData['nama']    = $Obj->_POST['keterangan'];
      }elseif(isset($Obj->_GET['search'])){
         $requestData['kode']    = Dispatcher::Instance()->Decrypt($Obj->_GET['kode']);
         $requestData['nama']    = Dispatcher::Instance()->Decrypt($Obj->_GET['nama']);
      }else{
         $requestData['kode']    = '';
         $requestData['nama']    = '';
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         # @param array
         $queryString   = Dispatcher::instance()->getQueryString($requestData);
      }else{
         $query         = array();
         foreach ($requestData as $key => $value) {
            $query[$key]   = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString   = urldecode(http_build_query($query));
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
      $dataList         = $Obj->getData($offset, $limit, $requestData);
      $total_data       = $Obj->Count();

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


      /*if($_POST || isset($_GET['cari'])) {
         if(isset($_POST['kode']) || isset($_POST['keterangan'])) {
            $kode = $_POST['kode'];
            $keterangan = $_POST['keterangan'];
         } elseif(isset($_GET['kode']) || isset($_GET['keterangan'])) {
            $kode = Dispatcher::Instance()->Decrypt($_GET['kode']);
            $keterangan = Dispatcher::Instance()->Decrypt($_GET['keterangan']);
         } else {
            $kode = '';
            $keterangan = '';
         }
      }

       //view
      $totalData  = $Obj->GetCountRkaklPaguBass($kode, $keterangan);
      $itemViewed = 20;
      $currPage   = 1;
      $startRec   = 0 ;
      if(isset($_GET['page'])) {
         $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $startRec =($currPage-1) * $itemViewed;
      }
      $data   = $Obj->GetRkaklPaguBass($kode, $keterangan, $startRec, $itemViewed);

      $url    = Dispatcher::Instance()->GetUrl(
                                               Dispatcher::Instance()->mModule,
                                               Dispatcher::Instance()->mSubModule,
                                               Dispatcher::Instance()->mAction,
                                               Dispatcher::Instance()->mType .
                                               '&kode='.Dispatcher::Instance()->Encrypt($kode) .
                                               '&keterangan='.Dispatcher::Instance()->Encrypt($keterangan).
                                               '&cari='.Dispatcher::Instance()->Encrypt(1));

      Messenger::Instance()->SendToComponent(
                                            'paging',
                                            'Paging',
                                            'view',
                                            'html',
                                            'paging_top',
                                            array(
                                                  $itemViewed,
                                                  $totalData,
                                                  $url,
                                                  $currPage),
                                             Messenger::CurrentRequest);*/

      $msg            = Messenger::Instance()->Receive(__FILE__);
      if($msg){
         $message       = $msg[0][1];
         $style         = $msg[0][2];
      };

      $return['data']            = $dataList;
      $return['start']           = $startRec+1;
      $return['request_data']    = $requestData;
      $return['message']         = $message;
      $return['style']           = $style;
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $requestData      = $data['request_data'];
      $message          = $data['message'];
      $style            = $data['style'];
      $urlSearch        = Dispatcher::Instance()->GetUrl('rkakl_pagu_bass', 'RkaklPaguBass', 'view', 'html');
      $urlAdd           = Dispatcher::Instance()->GetUrl('rkakl_pagu_bass', 'inputRkaklPaguBass', 'view', 'html');
      $this->mrTemplate->AddVars('content', $requestData);

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('content', 'URL_ADD', $urlAdd);

      if($message) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }

      if (empty($data['data'])) {
         $this->mrTemplate->AddVar('data_komponen', 'RKAKL_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_komponen', 'RKAKL_EMPTY', 'NO');
         $dataK = $data['data'];
         //mulai bikin tombol delete
         $label      = GTFWConfiguration::GetValue('language', 'rkakl_pagu_bas');
         $urlDelete  = Dispatcher::Instance()->GetUrl('rkakl_pagu_bass', 'deleteRkaklPaguBass', 'do', 'html');
         $urlReturn  = Dispatcher::Instance()->GetUrl('rkakl_pagu_bass', 'RkaklPaguBass', 'view', 'html');
         Messenger::Instance()->Send('confirm', 'confirmDelete', 'do', 'html',
         array(
              $label,
              $urlDelete,
              $urlReturn),
         Messenger::NextRequest);

         $urlConfirmDelete   = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html');
         $this->mrTemplate->AddVar('content', 'URL_DELETE', $urlConfirmDelete);

         for ($i=0; $i<sizeof($dataK); $i++) {
            $dataK[$i]['number']    = $i+$data['start'];
            if ($no % 2 != 0) {
                $dataK[$i]['class_name'] = 'table-common-even';
            }else{
                $dataK[$i]['class_name'] = '';
            }
            if ($dataK[$i]['child'] <> 0)
            {
                $dataK[$i]['disabled']  = 'disabled="true"';
            }
            else
            {
                $dataK[$i]['disabled']  = '';
            }
            if($i == 0) {
                $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);
            }
            if($i == sizeof($dataK)-1){
                $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
            }
            $idEnc = Dispatcher::Instance()->Encrypt($dataK[$i]['id']);

            $dataK[$i]['url_edit']      = Dispatcher::Instance()->GetUrl(
               'rkakl_pagu_bass',
               'inputRkaklPaguBass',
               'view',
               'html&dataId='.$idEnc);

            $this->mrTemplate->AddVars('data_komponen_item', $dataK[$i], 'DATA_');
            $this->mrTemplate->parseTemplate('data_komponen_item', 'a');
         }
      }
   }
}
?>
