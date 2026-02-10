<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/rencana_penerimaan/business/SumberDana.class.php';

class ViewPopupSumberDana extends HtmlResponse
{
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/rencana_penerimaan/template');
      $this->SetTemplateFile('view_popup_sumber_dana.html');
   }

    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest() {
      $mObj          = new SumberDana();
      $requestData   = array();

      if(isset($mObj->_POST['btncari'])){
         $requestData['nama']    = trim($mObj->_POST['nama']);
      }elseif(isset($mObj->_GET['search'])){
         $requestData['nama']    = Dispatcher::Instance()->Decrypt($mObj->_GET['nama']);
      }else{
         $requestData['nama']    = '';
      }

      if(method_exists(Dispatcher::Instance(), 'getQuerystring')){
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
      $url    = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;

      $destination_id   = "popup-subcontent";
      $dataList         = $mObj->getData($offset, $limit, $requestData['nama']);
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

      /*$popupSumberDanaObj = new SumberDana();
      $POST = $_POST->AsArray();
      if(!empty($POST)) {
         $nama = $POST['nama_sumber_dana'];
      } elseif(isset($_GET['cari'])) {
         $nama = Dispatcher::Instance()->Decrypt($_GET['nama_sumber_dana']);
      } else {
         $nama="";
      }

      $totalData = $popupSumberDanaObj->GetCountDataSumberDana($nama);

      $itemViewed = 20;
      $currPage = 1;
      $startRec = 0 ;
      $dest = "popup-subcontent";
      if(isset($_GET['page'])) {
         $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $startRec =($currPage-1) * $itemViewed;
      }
      $dataSumberDana = $popupSumberDanaObj->GetDataSumberDana($startRec, $itemViewed, $nama);

      $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&kode=' . Dispatcher::Instance()->Encrypt($kode) . '&nama=' . Dispatcher::Instance()->Encrypt($nama) . '&cari=' . Dispatcher::Instance()->Encrypt(1));

      Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage, $dest), Messenger::CurrentRequest);

      $msg = Messenger::Instance()->Receive(__FILE__);
      $this->Pesan = $msg[0][1];
      $this->css = $msg[0][2];

      $return['dataSumberDana'] = $dataSumberDana;
      $return['start'] = $startRec+1;

      $return['search']['nama'] = $nama;
      $return['search']['kode'] = $kode;*/

      $return['data_list']    = $mObj->ChangeKeyName($dataList);
      $return['request_data'] = $requestData;
      $return['start']        = $offset+1;
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $requestData      = $data['request_data'];
      $start            = $data['start'];
      $dataList         = $data['data_list'];
      $urlSearch        = Dispatcher::Instance()->GetUrl(
         'rencana_penerimaan',
         'popupSumberDana',
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
            $list['number']      = $start;
            $list['class_name']  = ($start % 2 <> 0) ? 'table-common-even' : '';
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $start++;
         }
      }

      /*$search = $data['search'];
      $this->mrTemplate->AddVar('content', 'NAMA', $search['nama']);
      $this->mrTemplate->AddVar('content', 'KODE', $search['kode']);
      $this->mrTemplate->AddVar('content', 'URL_SEARCH',
               Dispatcher::Instance()->GetUrl('pagu_anggaran_unit', 'popupSumberDana', 'view', 'html'));
      if($this->Pesan) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
      }

      if (empty($data['dataSumberDana'])) {
         $this->mrTemplate->AddVar('data_sumber_dana_subkegiatan', 'SUMBER_DANA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_sumber_dana_subkegiatan', 'SUMBER_DANA_EMPTY', 'NO');
         $dataSumberDana = $data['dataSumberDana'];
         //for($i=0;$i<sizeof($dataRkaklKegiatan);$i++) {
         //}

         for ($i=0; $i<sizeof($dataSumberDana); $i++) {
            $dataSumberDana[$i]['enc_sumber_dana_id'] = Dispatcher::Instance()->Encrypt($dataSumberDana[$i]['id']);
            $dataSumberDana[$i]['enc_sumber_dana_nama'] = Dispatcher::Instance()->Encrypt($dataSumberDana[$i]['nama']);

            $dataSumberDana[$i]['linknama']     = str_replace("'","\'",$dataSumberDana[$i]['nama']);
            $no = $i+$data['start'];
            $dataSumberDana[$i]['number'] = $no;
            if ($no % 2 != 0) $dataSumberDana[$i]['class_name'] = 'table-common-even';
            else $dataSumberDana[$i]['class_name'] = '';

            $this->mrTemplate->AddVars('data_sumber_dana_item', $dataSumberDana[$i], 'SD_');
            $this->mrTemplate->parseTemplate('data_sumber_dana_item', 'a');
         }
      }*/
   }
}
?>
