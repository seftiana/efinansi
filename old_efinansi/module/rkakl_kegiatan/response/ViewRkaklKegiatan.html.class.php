<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') 
. 'module/rkakl_kegiatan/business/RkaklKegiatan.class.php';

class ViewRkaklKegiatan extends HtmlResponse {

   var $Pesan;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot')
      .'module/rkakl_kegiatan/template');
      $this->SetTemplateFile('view_rkakl_kegiatan.html');
   }
   
   function ProcessRequest() {
      $Obj        = new RkaklKegiatan();
      $_POST      = $_POST->AsArray();
      if(isset($_POST['btncari'])):
         $kode    = trim($_POST['kode']);
         $nama    = trim($_POST['nama']);
      elseif(isset($_GET['kode']) AND $_GET['nama']):
         $kode    = Dispatcher::Instance()->Decrypt($_GET['kode']);
         $nama    = Dispatcher::Instance()->Decrypt($_GET['nama']);
      else:
         $kode    = "";
         $nama    = "";
      endif;
      
      //view
      $totalData     = $Obj->GetCountRkaklKegiatan($kode, $nama);
      
      $itemViewed    = 20; //limit
      
      if(isset($_GET['page'])):
         $currPage   = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $startRec   = ($currPage-1)*$itemViewed;
      else:
         $currPage   = 1;
         $startRec   = 0;
      endif;
      
      $data          = $Obj->GetRkaklKegiatan($kode, $nama, $startRec, $itemViewed);
      
      $url           = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule, 
         Dispatcher::Instance()->mAction, 
         Dispatcher::Instance()->mType 
         . '&kode=' . Dispatcher::Instance()->Encrypt($kode) 
         . '&nama=' . Dispatcher::Instance()->Encrypt($nama) 
         . '&cari=' . Dispatcher::Instance()->Encrypt(1)
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
      $search     = $data['search'];
      $this->mrTemplate->AddVar('content', 'KODE', $search['kode']);
      $this->mrTemplate->AddVar('content', 'NAMA', $search['nama']);
      $url_edit   = Dispatcher::Instance()->GetUrl(
         'rkakl_kegiatan', 
         'inputRkaklKegiatan', 
         'view', 
         'html'
      );

      $this->mrTemplate->AddVar(
         'content', 
         'URL_SEARCH', 
         Dispatcher::Instance()->GetUrl(
            'rkakl_kegiatan', 
            'RkaklKegiatan', 
            'view', 'html'
         )
      );
      $this->mrTemplate->AddVar(
         'content', 
         'URL_ADD', 
         Dispatcher::Instance()->GetUrl(
            'rkakl_kegiatan', 
            'inputRkaklKegiatan', 
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
      $label      = "RKAKL Kegiatan";
      $urlDelete  = Dispatcher::Instance()->GetUrl(
         'rkakl_kegiatan', 
         'deleteRkaklKegiatan', 
         'do', 
         'html'
      );
      $urlReturn  = Dispatcher::Instance()->GetUrl(
         'rkakl_kegiatan', 
         'RkaklKegiatan', 
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
      $this->mrTemplate->AddVar('content', 'CONTROL_LABEL', $label);
      $this->mrTemplate->AddVar('content', 'CONTROL_ACTION', $urlDelete);
      $this->mrTemplate->AddVar('content', 'CONTROL_RETURN', $urlReturn);
      
      if (empty($data['data'])) {
         $this->mrTemplate->AddVar('data_komponen', 'RKAKL_EMPTY', 'YES');
      } else {
         
         $this->mrTemplate->AddVar('data_komponen', 'RKAKL_EMPTY', 'NO');
         $dataK      = $data['data'];
      
         
         $i          = 0;
         $dataList   = array();
         $program    = '';
         $index      = 0;
         $nomor      = 0;
         $start      = $data['start'];
         $firstNumber   = $data['start'];
         $lastNumber    = ($data['start']+(count($dataK)-1));

         for($i = 0; $i < count($dataK);){
            if($program == $dataK[$i]['program_id']){
               $dataList[$index]['id']    = $dataK[$i]['id'];
               $dataList[$index]['kode']  = $dataK[$i]['kode'];
               $dataList[$index]['nama']  = $dataK[$i]['nama'];
               $dataList[$index]['number']   = ($start+$i);
               if(($start+$nomor) % 2 == 0){
                  $dataList[$index]['class_name']     = 'table-common-even';
               }else{
                  $dataList[$index]['class_name']     = '';
               }
               if($dataK[$i]['has_output']){
                  $dataList[$index]['checkboxDisabled']   = 'disabled = true;';
               }else{
                  $dataList[$index]['checkboxDisabled']   = '';
               }
               $i++;
               $nomor++;
            }else{
               $program             = $dataK[$i]['program_id'];
               $dataList[$index]['id']    = $dataK[$i]['program_id'];
               $dataList[$index]['kode']  = $dataK[$i]['kode_program'];
               $dataList[$index]['nama']  = $dataK[$i]['program_nama'];
               $dataList[$index]['class_name']     = 'table-common-even1';
               $dataList[$index]['checkbox']       = 'display: none;';
               $dataList[$index]['links']          = 'display: none;';
               $dataList[$index]['style']          = 'font-weight: bold;';
               unset($nomor);
            }
            $index++;
         }
         $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $firstNumber);
         $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $lastNumber);
         unset($i);

         foreach($dataList as $list){
            $idEnc               = Dispatcher::Instance()->Encrypt($list['id']);
            $list['url_edit']    = $url_edit. '&dataId=' .  $idEnc;
            $this->mrTemplate->AddVars('data_komponen_item', $list, 'DATA_');
            $this->mrTemplate->parseTemplate('data_komponen_item', 'a');   
         }

         /*for ($i=0; $i<sizeof($dataK); $i++) {
            $no                  = $i+$data['start'];
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

            if($dataK[$i]['has_output']){
               $dataK[$i]['checkboxDisabled']      = 'disabled = true;';
            }else{
               $dataK[$i]['checkboxDisabled']      = '';
            }
            $idEnc      = Dispatcher::Instance()->Encrypt($dataK[$i]['id']);

            $dataK[$i]['url_edit']  = $url_edit. '&dataId=' .  $idEnc;
            
            $this->mrTemplate->AddVars('data_komponen_item', $dataK[$i], 'DATA_');
            $this->mrTemplate->parseTemplate('data_komponen_item', 'a');    
         }*/
      }
   }
}
?>
