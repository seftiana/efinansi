<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/referensi_mak/business/ReferensiMak.class.php';

class ViewReferensiMak extends HtmlResponse {

   var $Pesan;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/referensi_mak/template');
      $this->SetTemplateFile('view_referensi_mak.html');
   }

   function ProcessRequest() {
      $mObj       = new ReferensiMak();
      $Obj = new ReferensiMak();

      if($_POST || isset($_GET['cari'])) {
         if(isset($_POST['kode']) || isset($_POST['nama'])) {
            $kode = $_POST['kode'];
            $nama = $_POST['nama'];
         } elseif(isset($_GET['kode']) || isset($_GET['nama'])) {
            $kode = Dispatcher::Instance()->Decrypt($_GET['kode']);
            $nama = Dispatcher::Instance()->Decrypt($_GET['nama']);
         } else {
            $kode = '';
            $nama = '';
         }
      }

   //view
      $totalData = $Obj->CountRefMak($kode, $nama);
      $itemViewed = 20;
      $currPage = 1;
      $startRec = 0 ;
      if(isset($_GET['page'])) {
         $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $startRec =($currPage-1) * $itemViewed;
      }
      $data    = $Obj->GetRefMak($kode, $nama, $startRec, $itemViewed);

      $url  = Dispatcher::Instance()->GetUrl(
              Dispatcher::Instance()->mModule,
              Dispatcher::Instance()->mSubModule,
              Dispatcher::Instance()->mAction,
              Dispatcher::Instance()->mType .
              '&kode=' . Dispatcher::Instance()->Encrypt($kode) .
              '&nama=' . Dispatcher::Instance()->Encrypt($nama) .
              '&cari=' . Dispatcher::Instance()->Encrypt(1));

      Messenger::Instance()->SendToComponent
            ('paging', 'Paging', 'view', 'html', 'paging_top',
            array($itemViewed, $totalData, $url, $currPage),
            Messenger::CurrentRequest);

      $msg  = Messenger::Instance()->Receive(__FILE__);
      $this->Pesan   = $msg[0][1];
      $this->css     = $msg[0][2];

      $return['data']         = $data;
      $return['start']        = $startRec+1;
      $return['search']['kode']  = $kode;
      $return['search']['nama']  = $nama;

      return $return;
   }

   function ParseTemplate($data = NULL) {
      $search = $data['search'];
      $this->mrTemplate->AddVar('content', 'KODE', $search['kode']);
      $this->mrTemplate->AddVar('content', 'NAMA', $search['nama']);

      $this->mrTemplate->AddVar('content', 'URL_SEARCH',
      Dispatcher::Instance()->GetUrl('referensi_mak', 'ReferensiMak', 'view', 'html'));
      $this->mrTemplate->AddVar('content', 'URL_ADD',
      Dispatcher::Instance()->GetUrl('referensi_mak', 'inputReferensiMak', 'view', 'html'));

      if($this->Pesan) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
      }

      if (empty($data['data'])) {
         $this->mrTemplate->AddVar('data_komponen', 'RKAKL_EMPTY', 'YES');
      } else {
         //$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
         //$encPage = Dispatcher::Instance()->Encrypt($decPage);
         $this->mrTemplate->AddVar('data_komponen', 'RKAKL_EMPTY', 'NO');
         $dataK = $data['data'];

         //mulai bikin tombol delete
         $label = "Referensi MAK";
         $urlDelete = Dispatcher::Instance()->GetUrl
         ('referensi_mak', 'deleteReferensiMak', 'do', 'html');
         $urlReturn = Dispatcher::Instance()->GetUrl
         ('referensi_mak', 'ReferensiMak', 'view', 'html');
         Messenger::Instance()->Send('confirm', 'confirmDelete', 'do', 'html',
         array($label, $urlDelete, $urlReturn),Messenger::NextRequest);
         $this->mrTemplate->AddVar('content', 'URL_DELETE',
         Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html'));

         for ($i=0; $i<sizeof($dataK); $i++) {
            $no = $i+$data['start'];
            $dataK[$i]['number'] = $no;
            if ($no % 2 != 0) $dataK[$i]['class_name'] = 'table-common-even';
            else $dataK[$i]['class_name'] = '';

            if($i == 0) $this->mrTemplate->AddVar
            ('content', 'FIRST_NUMBER', $no);
            if($i == sizeof($dataK)-1) $this->mrTemplate->AddVar
            ('content', 'LAST_NUMBER', $no);
            $idEnc = Dispatcher::Instance()->Encrypt($dataK[$i]['id']);

            $dataK[$i]['url_edit'] = Dispatcher::Instance()->
            GetUrl('referensi_mak', 'inputReferensiMak', 'view', 'html') . '&dataId=' .
            $idEnc/* . '&page=' . $encPage*/;

            $this->mrTemplate->AddVars('data_komponen_item', $dataK[$i], 'DATA_');
            $this->mrTemplate->parseTemplate('data_komponen_item', 'a');
         }
      }
   }
}
?>