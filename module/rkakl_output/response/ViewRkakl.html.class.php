<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') 
. 'module/rkakl_output/business/RkaklOutput.class.php';

class ViewRkakl extends HtmlResponse {

   var $Pesan;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot')
      .'module/rkakl_output/template');
      $this->SetTemplateFile('view_rkakl.html');
   }
   
   function ProcessRequest() {
      $Obj        = new RkaklOutput();
      $_POST      = $_POST->AsArray();
      if($_POST || isset($_GET['cari'])) {
         if(isset($_POST['kode']) || isset($_POST['nama'])) {
            $kode       = trim($_POST['kode']);
            $nama       = trim($_POST['nama']);
            $kegiatan   = trim($_POST['kegiatan']);
         } elseif(isset($_GET['kode']) || isset($_GET['nama'])) {
            $kode       = Dispatcher::Instance()->Decrypt($_GET['kode']);
            $nama       = Dispatcher::Instance()->Decrypt($_GET['nama']);
            $kegiatan   = Dispatcher::Instance()->Decrypt($_GET['kegiatan']);
         } else {
            $kode       = '';
            $nama       = '';
            $kegiatan   = '';
         }
      }
      
      //view
      $totalData     = $Obj->GetCountRkaklOutput($kode, $nama, $kegiatan);
      $itemViewed    = 20;
      $currPage      = 1;
      $startRec      = 0 ;
      if(isset($_GET['page'])) {
         $currPage   = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $startRec   =($currPage-1) * $itemViewed;
      }
      
      $data          = $Obj->GetRkaklOutput(
         $kode, 
         $nama, 
         $kegiatan, 
         $startRec, 
         $itemViewed
      );
      
      $url           = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule, 
         Dispatcher::Instance()->mAction, 
         Dispatcher::Instance()->mType . '&kode=' . 
         Dispatcher::Instance()->Encrypt($kode) . '&nama=' . 
         Dispatcher::Instance()->Encrypt($nama) .'&cari=' . 
         Dispatcher::Instance()->Encrypt(1) .'&kegiatan='.
         Dispatcher::Instance()->Encrypt($kegiatan)
      );

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
         ),Messenger::CurrentRequest);
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
         ),Messenger::CurrentRequest);

      $msg           = Messenger::Instance()->Receive(__FILE__);
      $this->Pesan   = $msg[0][1];
      $this->css     = $msg[0][2];

      $return['data']            = $data;
      $return['start']           = $startRec+1;
      $return['search']['kode']  = $kode;
      $return['search']['nama']  = $nama;
      $return['search']['kegiatan'] = $kegiatan;
      return $return;
   }
   
   function ParseTemplate($data = NULL) {
      $search     = $data['search'];
      $this->mrTemplate->AddVar('content', 'KODE', $search['kode']);
      $this->mrTemplate->AddVar('content', 'NAMA', $search['nama']);
      $this->mrTemplate->AddVar('content', 'KEGIATAN', $search['kegiatan']);
      $url_edit      = Dispatcher::Instance()->GetUrl(
         'rkakl_output', 
         'inputRkakl', 
         'view', 
         'html'
      );

      $this->mrTemplate->AddVar(
         'content', 
         'URL_SEARCH', 
         Dispatcher::Instance()->GetUrl(
            'rkakl_output', 
            'rkakl', 
            'view', 
            'html'
         )
      );
      $this->mrTemplate->AddVar(
         'content', 
         'URL_ADD', 
         Dispatcher::Instance()->GetUrl(
            'rkakl_output', 
            'inputRkakl', 
            'view', 
            'html'
         )
      );
      
      if($this->Pesan) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
      }

      //mulai bikin tombol delete
      $label         = "RKAKL Output";
      $urlDelete     = Dispatcher::Instance()->GetUrl(
         'rkakl_output', 
         'deleteRkakl', 
         'do', 
         'html'
      );
      $urlReturn     = Dispatcher::Instance()->GetUrl(
         'rkakl_output', 
         'rkakl', 
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
      $this->mrTemplate->AddVar(
         'content', 
         'URL_DELETE', 
         Dispatcher::Instance()->GetUrl(
            'confirm', 
            'confirmDelete', 
            'do', 
            'html'
         )
      );
      $this->mrTemplate->AddVar('content', 'CONTROL_ACTION', $urlDelete);
      $this->mrTemplate->AddVar('content', 'CONTROL_LABEL', $label);
      $this->mrTemplate->AddVar('content', 'CONTROL_RETURN', $urlReturn);
      
      if (empty($data['data'])) {
         $this->mrTemplate->AddVar('data_komponen', 'RKAKL_EMPTY', 'YES');
      } else {

         $this->mrTemplate->AddVar('data_komponen', 'RKAKL_EMPTY', 'NO');
         $dataK         = $data['data'];
      
         
         $dataLists        = array();
         $i                = 0;
         $index            = 0;
         $kegiatanId       = '';
         $programId        = '';
         $no               = 0;
         $start            = $data['start'];
         $firstNumber      = $data['start'];
         $lastNumber       = ($data['start'] + (count($dataK)-1));

         for($i = 0; $i < count($dataK);){
            if($programId == $dataK[$i]['program_id'] && $kegiatanId == $dataK[$i]['kegiatan_id']){
               $dataLists[$index]['id']         = $dataK[$i]['id'];
               $dataLists[$index]['kode']       = $dataK[$i]['kode'];
               $dataLists[$index]['nama']       = $dataK[$i]['nama'];
               $dataLists[$index]['number']     = ($start+$i);
               if(($start+$no) % 2 == 0){
                  $dataLists[$index]['class_name'] = 'table-common-even';
               }else{
                  $dataLists[$index]['class_name'] = '';
               }
               $dataLists[$index]['type']          = 'output';
               $dataLists[$index]['url_edit']      = $url_edit.'&dataId=' . Dispatcher::Instance()->Encrypt($dataK[$i]['id']);
               $i++;
               $no++;
            }elseif($programId == $dataK[$i]['program_id'] && $kegiatanId != $dataK[$i]['kegiatan_id']){
               // kegiatan 
               $kegiatanId       = $dataK[$i]['kegiatan_id'];
               $dataLists[$index]['id']         = $dataK[$i]['kegiatan_id'];
               $dataLists[$index]['kode']       = $dataK[$i]['kode_kegiatan'];
               $dataLists[$index]['nama']       = $dataK[$i]['kegiatan_nama'];
               $dataLists[$index]['class_name'] = 'table-common-even2';
               $dataLists[$index]['type']       = 'kegiatan';
               $dataLists[$index]['style']      = 'font-weight: bold;';
               $dataLists[$index]['checkDisabled']    = 'disabled=true;';
               $dataLists[$index]['checkboxStyle']    = 'display: none;';
               $dataLists[$index]['linkStyle']        = 'display: none;';
               unset($no);
            }elseif($programId != $dataK[$i]['program_id']){
               // program
               $programId        = $dataK[$i]['program_id'];
               $dataLists[$index]['id']         = $dataK[$i]['program_id'];
               $dataLists[$index]['kode']       = $dataK[$i]['program_kode'];
               $dataLists[$index]['nama']       = $dataK[$i]['program_nama'];
               $dataLists[$index]['class_name'] = 'table-common-even1';
               $dataLists[$index]['type']       = 'program';
               $dataLists[$index]['style']      = 'font-weight: bold;';
               $dataLists[$index]['checkDisabled']    = 'disabled=true;';
               $dataLists[$index]['checkboxStyle']    = 'display: none;';
               $dataLists[$index]['linkStyle']        = 'display: none;';
               unset($no);
            }

            $index++;
         }

         unset($i);

         $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $firstNumber);
         $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $lastNumber);
         
         for($i = 0; $i < count($dataLists); $i++){
            $this->mrTemplate->AddVars('data_komponen_item', $dataLists[$i], 'DATA_');
            $this->mrTemplate->parseTemplate('data_komponen_item', 'a');
         }

         /*for ($i=0; $i<sizeof($dataK); $i++) {
            $no                  = $i+$data['start'];
            $dataK[$i]['number'] = $no;
            if ($no % 2 != 0) {
               $dataK[$i]['class_name']   = 'table-common-even';
            }else {
               $dataK[$i]['class_name']   = '';
            }
            
            if($i == 0) {
               $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);  
            }         
            if($i == sizeof($dataK)-1) {
               $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
            }

            $idEnc      = Dispatcher::Instance()->Encrypt($dataK[$i]['id']);

            $dataK[$i]['url_edit'] = Dispatcher::Instance()->GetUrl(
               'rkakl_output', 
               'inputRkakl', 
               'view', 
               'html'
            ) . '&dataId=' . $idEnc;
            
            $this->mrTemplate->AddVars('data_komponen_item', $dataK[$i], 'DATA_');
            $this->mrTemplate->parseTemplate('data_komponen_item', 'a');    
         }*/
      }
   }
}
?>
