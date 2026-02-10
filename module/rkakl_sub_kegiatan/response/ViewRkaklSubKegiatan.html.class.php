<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rkakl_sub_kegiatan/business/RkaklSubKegiatan.class.php';

class ViewRkaklSubKegiatan extends HtmlResponse {

   var $Pesan;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/rkakl_sub_kegiatan/template');
      $this->SetTemplateFile('view_rkakl_sub_kegiatan.html');
   }
   
   function ProcessRequest() {
      $Obj = new RkaklSubKegiatan();
      
      if(isset($_POST['btncari'])):
         $kode = trim($_POST['kode']);
         $nama = trim($_POST['nama']);
      elseif(isset($_GET['kode']) OR isset($_GET['nama'])):
         $kode = Dispatcher::Instance()->Decrypt($_GET['kode']);
         $nama = Dispatcher::Instance()->Decrypt($_GET['nama']);
      else:
         $kode = "";
         $nama = "";
      endif;
      
      //echo $kode."<br />";
      //echo $nama;
      //echo "<hr />";
      
   //view
      $totalData  = $Obj->GetCountRkaklSubKegiatan($kode, $nama);
      //echo $totalData;
      
      $itemViewed = 20; //limit
      
      if(isset($_GET['page'])):
         $currPage   = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $startRec   = ($currPage-1)*$itemViewed;
      else:
         $currPage   = 1;
         $startRec   = 0;
      endif;
      
      $data          = $Obj->GetRkaklSubKegiatan($kode, $nama, $startRec, $itemViewed);
      
      $url           = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction, 
         Dispatcher::Instance()->mType . '&kode=' . 
         Dispatcher::Instance()->Encrypt($kode) . '&nama=' . 
         Dispatcher::Instance()->Encrypt($nama) .'&cari=' . 
         Dispatcher::Instance()->Encrypt(1
      ));

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
            $currPage
         ), Messenger::CurrentRequest);

      Messenger::Instance()->SendToComponent(
         'paging', 
         'Paging', 
         'view', 
         'html', 
         'paging_bottom',
         array(
            $itemViewed, 
            $totalData, 
            $url, 
            $currPage
         ), Messenger::CurrentRequest);

      $msg           = Messenger::Instance()->Receive(__FILE__);
      $this->Pesan   = $msg[0][1];
      $this->css     = $msg[0][2];

      $return['data']            = $data;
      $return['start']           = $startRec+1;
      $return['search']['kode']  = $kode;
      $return['search']['nama']  = $nama;
      
      return $return;
   }
   
   function ParseTemplate($data = NULL) {
      if($this->Pesan) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
      }
      $search = $data['search'];
      $this->mrTemplate->AddVar('content', 'KODE', $search['kode']);
      $this->mrTemplate->AddVar('content', 'NAMA', $search['nama']);
      
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl(
         'rkakl_sub_kegiatan', 
         'RkaklSubKegiatan', 
         'view', 
         'html'
      ));
      $this->mrTemplate->AddVar('content', 'URL_ADD', Dispatcher::Instance()->GetUrl(
         'rkakl_sub_kegiatan', 
         'inputRkaklSubKegiatan', 
         'view', 
         'html'
      ));
   
      //mulai bikin tombol delete
      $label      = "RKAKL SubKegiatan";
      $urlDelete  = Dispatcher::Instance()->GetUrl(
         'rkakl_sub_kegiatan', 
         'deleteRkaklSubKegiatan', 
         'do', 
         'html'
      );
      $urlReturn  = Dispatcher::Instance()->GetUrl(
         'rkakl_sub_kegiatan', 
         'RkaklSubKegiatan', 
         'view', 
         'html'
      );
      Messenger::Instance()->Send(
         'confirm', 
         'confirmDelete', 
         'do', 
         'html', 
         array(
            $label, 
            $urlDelete, 
            $urlReturn
         ),Messenger::NextRequest);
      $this->mrTemplate->AddVar('content', 'URL_DELETE', Dispatcher::Instance()->GetUrl(
         'confirm', 
         'confirmDelete', 
         'do', 
         'html'
      ));

      $this->mrTemplate->AddVar('content', 'CONTROL_ACTION', $urlDelete);
      $this->mrTemplate->AddVar('content', 'CONTROL_LABEL', $label);
      $this->mrTemplate->AddVar('content', 'CONTROL_RETURN', $urlReturn);

      if (empty($data['data'])) {
         $this->mrTemplate->AddVar('data_komponen', 'RKAKL_EMPTY', 'YES');
      } else {
         //$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
         //$encPage = Dispatcher::Instance()->Encrypt($decPage);
         $this->mrTemplate->AddVar('data_komponen', 'RKAKL_EMPTY', 'NO');
         $dataK      = $data['data'];
      

         for ($i=0; $i<sizeof($dataK); $i++) {
            $no      = $i+$data['start'];
            $dataK[$i]['number'] = $no;
            if ($no % 2 != 0) {
               $dataK[$i]['class_name'] = 'table-common-even';
            }else {
               $dataK[$i]['class_name'] = '';
            }
            
            if($i == 0) {
               $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);
            }
            if($i == sizeof($dataK)-1) {
               $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
            }
            $idEnc = Dispatcher::Instance()->Encrypt($dataK[$i]['id']);

            $dataK[$i]['url_edit']  = Dispatcher::Instance()->GetUrl(
               'rkakl_sub_kegiatan', 
               'inputRkaklSubKegiatan', 
               'view', 
               'html'
            ) . '&dataId=' . $idEnc/* . '&page=' . $encPage*/;
            
            $this->mrTemplate->AddVars('data_komponen_item', $dataK[$i], 'DATA_');
            $this->mrTemplate->parseTemplate('data_komponen_item', 'a');    
         }
      }
   }
}
?>