<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/kelompok_laporan/business/AppKlpLaporan.class.php';

class ViewDetilKlpLaporan extends HtmlResponse
{
   public $Pesan;
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
         'module/kelompok_laporan/template');
      $this->SetTemplateFile('view_detil_klp_laporan.html');
   }

   function ProcessRequest(){
      $Obj           = new AppKelpLaporan();
      $msg           = Messenger::Instance()->Receive(__FILE__);

      if ($_POST || isset($_GET['cari'])){
         if (isset($_POST['key'])){
            $key     = $_POST['key'];
         }elseif (isset($_GET['key'])){
            $key     = Dispatcher::Instance()->Decrypt($_GET['key']);
         }else{
            $key     = '';
         }
      }
      $id_kel_lap    = Dispatcher::Instance()->Decrypt($_GET['dataId']);

      //view
      $totalData     = $Obj->GetCountDetilKlpLaporan($id_kel_lap, $key);
      $itemViewed    = 20;
      $currPage      = 1;
      $startRec      = 0;

      if (isset($_GET['page'])){
         $currPage   = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $startRec   = ($currPage - 1) * $itemViewed;
      }
      $data_list     = $Obj->GetDataDetilKlpLaporan($id_kel_lap, $key, $startRec, $itemViewed);
      $return['kelompok_info']   = $Obj->GetKelompokInfo($id_kel_lap);

      //    print_r($data_list);
      $url           = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType .
         '&key=' . Dispatcher::Instance()->Encrypt($key) .
         '&cari=' . Dispatcher::Instance()->Encrypt(1)
      );

      $this->Pesan   = $msg[0][1];
      $this->css     = $msg[0][2];
      $return['detil_kelompok_laporan']   = $data_list;
      $return['start']                    = $startRec + 1;
      $return['id_kelompok_laporan']      = $id_kel_lap;
      $return['search']['key']            = $key;

      return $return;
   }

   function ParseTemplate($data = NULL){
      // print_r($data['kelompok_info']);
      $search           = $data['search'];
      $urlSearch        = Dispatcher::Instance()->GetUrl(
         'kelompok_laporan',
         'DetilKlpLaporan',
         'view',
         'html'
      );
      $urlAdd           = Dispatcher::Instance()->GetUrl(
         'kelompok_laporan',
         'inputDetilKlpLaporan',
         'view',
         'html'
      ) . '&dataId=' . Dispatcher::Instance()->Encrypt($data['id_kelompok_laporan']);
      $urlBack          = Dispatcher::Instance()->GetUrl(
         'kelompok_laporan',
         'KlpLaporan',
         'view',
         'html'
      );
      $this->mrTemplate->AddVars('content', $data['kelompok_info']);

      $this->mrTemplate->AddVar('content', 'KEY', $search['key']);
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('content', 'URL_ADD', $urlAdd);
      $this->mrTemplate->AddVar('content', 'URL_BACK', $urlBack);

      if ($this->Pesan){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
      }

      if (empty($data['detil_kelompok_laporan'])){
         $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
      }else{
         $decPage    = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
         $encPage    = Dispatcher::Instance()->Encrypt($decPage);
         $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
         $data_list  = $data['detil_kelompok_laporan'];
         //mulai bikin tombol delete
         $label      = "Manajemen Detil Kelompok Laporan";
         $urlDelete  = Dispatcher::Instance()->GetUrl(
            'kelompok_laporan',
            'deleteDetilKlpLaporan',
            'do',
            'html'
         ) . '&dataId=' . Dispatcher::Instance()->Encrypt($data['id_kelompok_laporan']);
         $urlReturn  = Dispatcher::Instance()->GetUrl(
            'kelompok_laporan',
            'detilKlpLaporan',
            'view',
            'html'
         ) . '&dataId=' . Dispatcher::Instance()->Encrypt($data['id_kelompok_laporan']);
         Messenger::Instance()->Send(
            'confirm',
            'confirmDelete',
            'do',
            'html',
            array(
               $label,
               $urlDelete,
               $urlReturn
            ) , Messenger::NextRequest);
         $this->mrTemplate->AddVar('content', 'URL_DELETE', Dispatcher::Instance()->GetUrl(
            'confirm',
            'confirmDelete',
            'do',
            'html'
         ));

         //selesai bikin tombol delete
         //mulai perulangan nulis di template

         for ($i = 0;$i < sizeof($data_list);$i++){
            $no                        = $i + $data['start'];
            $data_list[$i]['number']   = $no;

            if ($no % 2 != 0) {
               $data_list[$i]['class_name']  = 'table-common-even';
            }else {
               $data_list[$i]['class_name']  = '';
            }

            if ($i == 0) {
               $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);
            }

            if ($i == sizeof($data_list) - 1) {
               $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
            }
            $idEnc      = Dispatcher::Instance()->Encrypt($data_list[$i]['id']);
            $data_list[$i]['url_edit']    = Dispatcher::Instance()->GetUrl(
               'kelompok_laporan',
               'inputKlpLaporan',
               'view',
               'html'
            ) . '&dataId=' . $idEnc . '&page=' . $encPage . '&cari=' . $cari;
            $data_list[$i]['url_detil']   = Dispatcher::Instance()->GetUrl(
               'kelompok_laporan',
               'detilKlpLaporan',
               'view',
               'html'
            ) . '&dataId=' . $idEnc;
            $this->mrTemplate->AddVars('data_item', $data_list[$i], 'DATA_');
            $this->mrTemplate->parseTemplate('data_item', 'a');
         }
      }
   }
}
?>