<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot')
.'module/pagu_anggaran_unit_per_mak/business/PopupMak.class.php';

class ViewPopupMak extends HtmlResponse {

   var $Pesan;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/pagu_anggaran_unit_per_mak/template');
      $this->SetTemplateFile('view_popup_mak.html');
   }

    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest() {
      $popupMakObj      = new Mak();
      $POST             = $_POST->AsArray();
      if(isset($POST['btncari'])){
         $nama          = trim($POST['nama']);
         $bas           = trim($POST['bas']);
      }elseif(isset($_GET['cari'])){
         $nama          = Dispatcher::Instance()->Decrypt($_GET['nama']);
         $bas           = Dispatcher::Instance()->Decrypt($_GET['bas']);
      }else{
         $nama          = "";
         $bas           = '';
      }
      $exclude    = array('41','42');
      $itemViewed = 20;
      $currPage   = 1;
      $startRec   = 0 ;
      $dest       = "popup-subcontent";
      if(isset($_GET['page'])) {
         $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $startRec =($currPage-1) * $itemViewed;
      }

      $dataMak    = $popupMakObj->GetDataMak($startRec, $itemViewed, $nama, $bas, $exclude);
      $totalData  = $popupMakObj->GetCountDataMak();

      $url        = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType
         . '&nama=' .  Dispatcher::Instance()->Encrypt($nama)
         . '&bas=' . Dispatcher::Instance()->Encrypt($bas)
         . '&cari=' . Dispatcher::Instance()->Encrypt(1));

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
            $currPage,
            $dest
         ), Messenger::CurrentRequest);

      $msg           = Messenger::Instance()->Receive(__FILE__);
      $this->Pesan   = $msg[0][1];
      $this->css     = $msg[0][2];

      $return['dataMak']         = $popupMakObj->ChangeKeyName($dataMak);
      $return['start']           = $startRec+1;

      $return['search']['nama']  = $nama;
      $return['search']['bas']   = $bas;
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $search     = $data['search'];
      $dataList   = $data['dataMak'];
      $url_search = Dispatcher::Instance()->GetUrl(
         'pagu_anggaran_unit_per_mak',
         'popupMak',
         'view',
         'html'
      );

      $this->mrTemplate->AddVar('content', 'NAMA', $search['nama']);
      $this->mrTemplate->AddVar('content', 'BAS', $search['bas']);
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $url_search);

      if($this->Pesan) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
      }

      if (empty($dataList)) {
         $this->mrTemplate->AddVar('data_mak', 'DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_mak', 'DATA_EMPTY', 'NO');
         $basId      = '';
         $dataLists  = array();
         $i          = 0;
         $index      = 0;
         $no         = 0;
         $start      = $data['start'];

         for ($i=0; $i < count($dataList);) {
            if($basId == $dataList[$i]['bas_id']){
               $dataLists[$index]['id']      = $dataList[$i]['akun_id'];
               $dataLists[$index]['kode']    = $dataList[$i]['akun_kode'];
               $dataLists[$index]['nama']    = $dataList[$i]['akun_nama'];
               $dataLists[$index]['nomor']   = ($start+$i);
               if(($no+$start) % 2 == 0){
                  $dataLists[$index]['class_name']    = 'table-common-even';
               }else{
                  $dataLists[$index]['class_name']    = '';
               }
               $dataLists[$index]['link_nama']        = str_replace("'","\'",$dataList[$i]['akun_nama']);
               $no++;
               $i++;
            }else{
               $basId      = $dataList[$i]['bas_id'];
               $dataLists[$index]['id']      = $dataList[$i]['bas_id'];
               $dataLists[$index]['kode']    = $dataList[$i]['bas_kode'];
               $dataLists[$index]['nama']    = $dataList[$i]['bas_nama'];
               $dataLists[$index]['class_name']    = 'table-common-even1';
               $dataLists[$index]['style']         = 'font-weight: bold;';
               $dataLists[$index]['link']          = 'display: none;';
               unset($no);
            }
            $index++;
         }

         unset($i);
         for ($i=0; $i < count($dataLists); $i++) {
            $this->mrTemplate->AddVars('data_item', $dataLists[$i], 'MK_');
            $this->mrTemplate->parseTemplate('data_item', 'a');
         }
      }
   }
}
?>