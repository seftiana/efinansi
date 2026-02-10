<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
 'module/pengaturan_alokasi_coa_akademik/business/AppAlokasiCoaAkademik.class.php';

class ViewAlokasiCoaAkademik extends HtmlResponse
{
   public $Pesan;
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot') .
            'module/pengaturan_alokasi_coa_akademik/template');
      $this->SetTemplateFile('view_alokasi_coa_akademik.html');
   }

   function ProcessRequest(){
      $Obj           = new AppAlokasiCoaAkademik();
      $msg           = Messenger::Instance()->Receive(__FILE__);

      if ($_POST || isset($_GET['cari'])){
         if (isset($_POST)){
            $kode     = $_POST['kode'];
            $nama     = $_POST['nama'];
         }elseif (isset($_GET['key'])){
            $kode    = Dispatcher::Instance()->Decrypt($_GET['kode']);
            $nama     = Dispatcher::Instance()->Decrypt($_GET['nama']);
         }else{
           $kode = '';
           $nama = '';
         }
      }
      


      $itemViewed    = 20;
      $currPage      = 1;
      $startRec      = 0;

      if (isset($_GET['page'])){
         $currPage   = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $startRec   = ($currPage - 1) * $itemViewed;
      }
      //view
      $data_list     = $Obj->GetCoa($kode,$nama,$startRec, $itemViewed);
      $totalData     = $Obj->GetCountCoa();   
      
      $url           = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType .
         '&key=' . Dispatcher::Instance()->Encrypt($key) .
         '&cari=' . Dispatcher::Instance()->Encrypt(1)
      );

        Messenger::Instance()->SendToComponent(
                                            'paging', 
                                            'Paging', 
                                            'view', 
                                            'html', 
                                            'paging', 
                                            array(
                                                    $itemViewed,
                                                    $totalData, 
                                                    $url, 
                                                    $currPage), 
                                            Messenger::CurrentRequest);
                                            
      $this->Pesan   = $msg[0][1];
      $this->css     = $msg[0][2];
      $return['data_list']   = $data_list;
      $return['start']                    = $startRec + 1;
      
      $return['search']['nama']            = $nama;
      $return['search']['kode']            = $kode;

      return $return;
   }

   function ParseTemplate($data = NULL){
      // print_r($data['kelompok_info']);
      $search           = $data['search'];
      $urlSearch        = Dispatcher::Instance()->GetUrl(
         'pengaturan_alokasi_coa_akademik',
         'AlokasiCoaAkademik',
         'view',
         'html'
      );
      $urlAdd           = Dispatcher::Instance()->GetUrl(
         'pengaturan_alokasi_coa_akademik',
         'inputAlokasiCoaAkademik',
         'view',
         'html'
      ) ;
      $urlBack          = Dispatcher::Instance()->GetUrl(
         'pengaturan_alokasi_coa_akademik',
         'AlokasiCoaAkademik',
         'view',
         'html'
      );
     

      $this->mrTemplate->AddVar('content', 'KODE', $search['kode']);
      $this->mrTemplate->AddVar('content', 'NAMA', $search['nama']);
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('content', 'URL_ADD', $urlAdd);
      $this->mrTemplate->AddVar('content', 'URL_BACK', $urlBack);

      if ($this->Pesan){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
      }

      if (empty($data['data_list'])){
         $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
      }else{
         $decPage    = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
         $encPage    = Dispatcher::Instance()->Encrypt($decPage);
         $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
         $data_list  = $data['data_list'];
         //mulai bikin tombol delete
         $label      = "Coa";
         $urlDelete  = Dispatcher::Instance()->GetUrl(
            'pengaturan_alokasi_coa_akademik',
            'deleteCoa',
            'do',
            'html'
         ) ;
         $urlReturn  = Dispatcher::Instance()->GetUrl(
            'pengaturan_alokasi_coa_akademik',
            'AlokasiCoaAkademik',
            'view',
            'html'
         ) ;
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
               'pengaturan_alokasi_coa_akademik',
               'inputAlokasiCoaAkademik',
               'view',
               'html'
            ) . '&dataId=' . $idEnc . '&page=' . $encPage . '&cari=' . $cari;
            $data_list[$i]['url_detil']   = Dispatcher::Instance()->GetUrl(
               'pengaturan_alokasi_coa_akademik',
               'AlokasiCoaAkademik',
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