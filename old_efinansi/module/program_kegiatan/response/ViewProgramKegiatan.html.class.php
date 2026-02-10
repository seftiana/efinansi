<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/program_kegiatan/response/ProcessSubKegiatan.proc.class.php';

class ViewProgramKegiatan extends HtmlResponse 
{
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/program_kegiatan/template');
      $this->SetTemplateFile('view_program_kegiatan.html');
   }
   
   function ProcessRequest() {     
      $kegiatanObj = new SubKegiatan();     

      if(isset($_POST['btncari'])) {
         if(is_object($_POST['data'])):
            $search = $_POST['data']->AsArray();
         else:
            $search = $_POST['data']; 
         endif;
      }
      //debug($search);
      $itemViewed = 20;
      $currPage = 1;
      $startRec = 0 ;
     
      if(isset($_GET['page'])) {
         $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();  
         $startRec =($currPage-1) * $itemViewed;
      }
      
      
      if(isset($_GET['idTahun'])):
         $search['tahunAnggaran']['tahunAnggaranId'] = $_GET['idTahun'];
      endif;
      if(isset($_GET['idProgram'])):
         $search['program']['programId'] = $_GET['idProgram'];
      endif;
      if(isset($_GET['idKegiatan'])):
         $search['subkegiatan']['kegiatan_id'] = $_GET['idKegiatan'];
      endif;
      if(isset($_GET['jenisKegiatanId'])):
         $search['subkegiatan']['jeniskegiatan_id'] = $_GET['jenisKegiatanId'];
      endif;
      if(isset($_GET['kodeSubKegiatan'])):
         $search['subkegiatan']['kode'] = $_GET['kodeSubKegiatan'];
      endif;
      if(isset($_GET['namaSubKegiatan'])):
         $search['subkegiatan']['nama'] = $_GET['namaSubKegiatan'];
      endif;
      $idTahun          = $search['tahunAnggaran']['tahunAnggaranId'];
      $idProgram        = $search['program']['programId'];
      $idKegiatan       = $search['subkegiatan']['kegiatan_id'];
      $jenisKegiatanId  = $search['subkegiatan']['jeniskegiatan_id'];
      $kodeSubKegiatan  = $search['subkegiatan']['kode'];
      $namaSubKegiatan  = $search['subkegiatan']['nama'];
      
      if(empty($idTahun)):
         $idTahun = $kegiatanObj->GetTahunAnggaranAktif();
      endif;
      
      $btncari                = $_POST['btncari']->mrVariable;
      
      $return['idTahunAktif'] = $idTahun;
      $return['countAll']     = $kegiatanObj->GetDataKegiatanCountAll($idTahun);
      
      $totalData              = $kegiatanObj->GetCount(
         $idTahun,
         $idProgram,
         $idKegiatan,
         $jenisKegiatanId,
         $kodeSubKegiatan,
         $namaSubKegiatan
      );
      $dataSubKegiatan        = $kegiatanObj->GetData(
         $startRec,
         $itemViewed,
         $idTahun,
         $idProgram,
         $idKegiatan,
         $jenisKegiatanId,
         $kodeSubKegiatan,
         $namaSubKegiatan
      );
      
      $url = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule, 
         Dispatcher::Instance()->mSubModule, 
         Dispatcher::Instance()->mAction, 
         Dispatcher::Instance()->mType .
         '&idTahun=' . Dispatcher::Instance()->Encrypt($idTahun) .
         '&idProgram=' . Dispatcher::Instance()->Encrypt($idProgram) .
         '&idKegiatan=' . Dispatcher::Instance()->Encrypt($idKegiatan) .
         '&jenisKegiatanId=' . Dispatcher::Instance()->Encrypt($jenisKegiatanId) .
         '&kodeSubKegiatan=' . Dispatcher::Instance()->Encrypt($kodeSubKegiatan) .
         '&namaSubKegiatan=' . Dispatcher::Instance()->Encrypt($namaSubKegiatan)
      );//
            
      $return['url_params'] = '&idTahun=' . Dispatcher::Instance()->Encrypt($idTahun).'&idProgram=' . Dispatcher::Instance()->Encrypt($idProgram).'&idKegiatan=' . Dispatcher::Instance()->Encrypt($idKegiatan).'&jenisKegiatanId=' . Dispatcher::Instance()->Encrypt($jenisKegiatanId).'&kodeSubKegiatan=' . Dispatcher::Instance()->Encrypt($kodeSubKegiatan) .'&namaSubKegiatan=' . Dispatcher::Instance()->Encrypt($namaSubKegiatan);
      
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
         ), 
         Messenger::CurrentRequest
      ); 

   
      $arrTahunAnggaran = $kegiatanObj->GetDataTahunAnggaran();

      Messenger::Instance()->SendToComponent(
         'combobox', 
         'Combobox', 
         'view', 
         'html', 
         'data[tahunAnggaran][tahunAnggaranId]',  
         array(
            'data[tahunAnggaran][tahunAnggaranId]', 
            $arrTahunAnggaran, $idTahun, 
            '', 
            ' style="width:150px;" onchange="this.form.onsubmit()" '
         ), 
         Messenger::CurrentRequest
      );

      $arrProgram = $kegiatanObj->GetDataProgram($idTahun);
      Messenger::Instance()->SendToComponent(
         'combobox', 
         'Combobox', 
         'view', 
         'html', 
         'data[program][programId]', 
         array(
            'data[program][programId]', 
            $arrProgram, 
            $idProgram, 
            'true', 
            ' style="width:380px;" '
         ), 
         Messenger::CurrentRequest
      );
      
      

      $arr_jenis_kegiatan = $kegiatanObj->GetDataJenisKegiatan();
      Messenger::Instance()->SendToComponent(
         'combobox', 
         'Combobox', 
         'view', 
         'html', 
         'data[subkegiatan][jeniskegiatan_id]', 
         array(
            'data[subkegiatan][jeniskegiatan_id]', 
            $arr_jenis_kegiatan, 
            $jenisKegiatanId, 
            'true', 
            ' style="width:150px;" '
         ), 
         Messenger::CurrentRequest
      );       
       
      if(empty($idTahun)):
         $idTahun = $arrTahunAnggaran['0']['id'];
      endif;
      $return['idTahun'] = $idTahun;
      #print_r($idTahun);
      //start menghandle pesan yang diparsing
      $processSubKegiatan= new ProcessSubKegiatan();
      $tmp=$processSubKegiatan->parsingUrl(__FILE__);       
      if(isset($tmp['msg'])):
         $return['msg']=$tmp['msg'];
      endif;
      //end handle
       
      $return['data']   = $dataSubKegiatan;
      $return['start']  = $startRec+1;
      $return['search'] = $search; 
      return $return;
   }
   
   function ParseTemplate($data = NULL) {   

      $this->mrTemplate->AddVar(
         'content', 
         'SEARCH_KEGIATAN_NAMA', 
         $data['search']['subkegiatan']['kegiatan_nama']
      );     
      $this->mrTemplate->AddVar(
         'content', 
         'SEARCH_SUBKEGIATAN_PROGRAMID', 
         $data['search']['subkegiatan']['kegiatan_id']
      );
      $this->mrTemplate->AddVar(
         'content', 
         'SEARCH_SUBKEGIATAN_KODE', 
         $data['search']['subkegiatan']['kode']
      );     
      $this->mrTemplate->AddVar('content', 'SEARCH_SUBKEGIATAN_NAMA', $data['search']['subkegiatan']['nama']);

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', 
         Dispatcher::Instance()->GetUrl(
            'program_kegiatan', 
            'programKegiatan', 
            'view', 
            'html'
         ) 
      );
      $this->mrTemplate->AddVar('content', 'URL_EXCEL', 
         Dispatcher::Instance()->GetUrl(
            'program_kegiatan', 
            'programKegiatan', 
            'view', 
            'xls'
         ).$data['url_params']
      );
      $this->mrTemplate->AddVar('content', 'URL_ADD', 
         Dispatcher::Instance()->GetUrl(
            'program_kegiatan', 
            'inputSubKegiatan', 
            'view', 
            'html'
         )."&idTahun=".$data['idTahun']
      );     
      $this->mrTemplate->AddVar('content', 'URL_ADD_PROGRAM', 
         Dispatcher::Instance()->GetUrl(
            'program_kegiatan', 
            'inputProgram', 
            'view', 
            'html'
         )."&idTahun=".$data['idTahun']
      );
      $this->mrTemplate->AddVar('content', 'URL_ADD_KEGIATAN', 
         Dispatcher::Instance()->GetUrl(
            'program_kegiatan', 
            'inputKegiatan', 
            'view', 
            'html'
         )."&idTahun=".$data['idTahun']
      );
      
      $this->mrTemplate->AddVar('content', 'POPUP_KEGIATAN', 
         Dispatcher::Instance()->GetUrl(
            'program_kegiatan', 
            'kegiatan', 
            'popup', 
            'html'
         )."&idTahun=".$data['idTahun']
      );
      
      if ($data['idTahun'] == $data['idTahunAktif'] && $data['countAll'] == 0)
      {
         $this->mrTemplate->AddVar('tombol_kopi', 'POPUP_KOPI_PROGRAM_KEGIATAN', 
            Dispatcher::Instance()->GetUrl(
               'program_kegiatan', 
               'kopiProgramKegiatan', 
               'do', 
               'html'
            )."&idTahun=".$data['idTahun']
         );
         $this->mrTemplate->SetAttribute('tombol_kopi', 'visibility', 'visible');
      }
      
      if (isset ($data['msg'])) {        
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);
         if($data['msg']['action']=='msg'):
            $class='notebox-done';
         else:
            $class = 'notebox-warning';
         endif;
         
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $class);
      }  
          
      if (empty($data['data'])) {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
         $dataGrid = $data['data'];      
         $i=0;
         //$no=$data['start'];       
         //$program_nomor=''; //inisialisasi program
         //$kegiatan_nomor=''; //inisialisasi kegiatan
         $kodeProgram = '';
         //$no=1;
         #print_r($dataGrid);
         for ($i=0; $i<sizeof($dataGrid); $i++) {

            if($dataGrid[$i]['kodeProg']!=$kodeProgram){
                  
                  $urlAccept = 'program_kegiatan|deleteProgram|do|html-cari-'.$cari;
                  $urlReturn = 'program_kegiatan|programKegiatan|view|html-cari-'.$cari;
                  $label = Dispatcher::Instance()->Encrypt(GTFWConfiguration::GetValue( 'language', 'program'));
                  $idEnc = Dispatcher::Instance()->Encrypt($dataGrid[$i]['programId']);
                  $dataName = Dispatcher::Instance()->Encrypt($dataGrid[$i]['namaProgram']);
                  $message = 'Penghapusan Data ini akan menghapus semua '.GTFWConfiguration::GetValue( 'language', 'kegiatan').' dan '.GTFWConfiguration::GetValue( 'language', 'sub_kegiatan').' dibawahnya';
                  
                  $urlDelete = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html').'&urlDelete='. $urlAccept.'&urlReturn='.$urlReturn.'&id='.$idEnc.'&label='.$label.'&dataName='.$dataName.'&message='.$message;
                  
                  #print_r($urlDelete);exit;
                  
                  $arrProgram[$i]['kode'] = "<strong>".$dataGrid[$i]['kodeProg']."</strong>";
                  $arrProgram[$i]['nama'] = "<strong>".$dataGrid[$i]['namaProgram']."</strong>";
                  $arrProgram[$i]['class_name'] = 'table-common-even1';
                  $arrProgram[$i]['url_edit'] = Dispatcher::Instance()->GetUrl('program_kegiatan', 'inputProgram', 'view', 'html')."&grp=".$dataGrid[$i]['programId']."&idTahun=".$data['idTahun'];
                  $arrProgram[$i]['padding']= 10;
                  $arrProgram[$i]['url_delete'] = $urlDelete;
                  $arrProgram[$i]['url_edit_value'] = 'Edit '.GTFWConfiguration::GetValue( 'language', 'program');
                  $arrProgram[$i]['url_delete_value'] = 'Delete '.GTFWConfiguration::GetValue( 'language', 'program');
                  $arrProgram[$i]['icon'] = 'button-bukunutup.gif';

                  $this->mrTemplate->AddVars('data_item', $arrProgram[$i], 'DATA_');
                  $this->mrTemplate->parseTemplate('data_item', 'a');
                  $kodeProgram = $dataGrid[$i]['kodeProg']; 
            }
   
            if(($dataGrid[$i]['namaKegiatan']!=$namaKegiatan) and (!empty($dataGrid[$i]['kodeKegiatan']))){
                  $urlAccept = 'program_kegiatan|deleteKegiatan|do|html-cari-'.$cari;
                  $urlReturn = 'program_kegiatan|programKegiatan|view|html-cari-'.$cari;
                  $label = Dispatcher::Instance()->Encrypt(GTFWConfiguration::GetValue( 'language', 'kegiatan'));
                  $idEnc = Dispatcher::Instance()->Encrypt($dataGrid[$i]['subprogId']);
                  $dataName = Dispatcher::Instance()->Encrypt($dataGrid[$i]['namaKegiatan']);
                  $message = 'Penghapusan Data ini akan menghapus semua '.GTFWConfiguration::GetValue( 'language', 'sub_kegiatan').' dibawahnya';
                  
                  $urlDelete = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html').'&urlDelete='. $urlAccept.'&urlReturn='.$urlReturn.'&id='.$idEnc.'&label='.$label.'&dataName='.$dataName.'&message='.$message;

                  #$arrProgram[$i]['no']=$no;
                  $arrProgram[$i]['kode'] = $dataGrid[$i]['kodeKegiatan'];
                  $arrProgram[$i]['nama'] = $dataGrid[$i]['namaKegiatan'];
                  $arrProgram[$i]['class_name'] = 'table-common-even2';
                  $arrProgram[$i]['url_edit'] = Dispatcher::Instance()->GetUrl('program_kegiatan', 'inputKegiatan', 'view', 'html')."&grp=".$dataGrid[$i]['subprogId']."&idTahun=".$data['idTahun'];
                  $arrProgram[$i]['url_delete'] = $urlDelete;
                  $arrProgram[$i]['url_edit_value'] = 'Edit '.GTFWConfiguration::GetValue( 'language', 'kegiatan');
                  $arrProgram[$i]['url_delete_value'] = 'Delete '.GTFWConfiguration::GetValue( 'language', 'kegiatan');
                  $arrProgram[$i]['padding']= 30;
                  $arrProgram[$i]['icon']='button-clipboard.gif';
                  
                  $no =1;
                  $this->mrTemplate->AddVars('data_item', $arrProgram[$i], 'DATA_');
                  $this->mrTemplate->parseTemplate('data_item', 'a');
                  $namaKegiatan = $dataGrid[$i]['namaKegiatan']; 
            }
            if(!empty($dataGrid[$i]['kodeSubKegiatan'])){

               $urlAccept = 'program_kegiatan|deleteSubKegiatan|do|html-cari-'.$cari;
               $urlReturn = 'program_kegiatan|programKegiatan|view|html-cari-'.$cari;
               $label = Dispatcher::Instance()->Encrypt(GTFWConfiguration::GetValue( 'language', 'sub_kegiatan'));
               $idEnc = Dispatcher::Instance()->Encrypt($dataGrid[$i]['kegrefId']);
               $dataName = Dispatcher::Instance()->Encrypt($dataGrid[$i]['namaSubKegiatan']);
               $message = '';
                        
               $urlDelete = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html').'&urlDelete='. $urlAccept.'&urlReturn='.$urlReturn.'&id='.$idEnc.'&label='.$label.'&dataName='.$dataName.'&message='.$message;

               //$dataKirim['nomor']='<i>'.$no.'. <i>';
               $dataKirim['kode'] = '<i>'.$dataGrid[$i]['kodeSubKegiatan'].'</i>';
               $dataKirim['nama'] = '<i>'.$dataGrid[$i]['namaSubKegiatan'].'</i>';
               $dataKirim['jumlah_komponen'] = $dataGrid[$i]['jumlah'];
               $dataKirim['url_edit'] = Dispatcher::Instance()->GetUrl('program_kegiatan', 'inputSubKegiatan', 'view', 'html')."&grp=".$dataGrid[$i]['kegrefId']."&idTahun=".$data['idTahun'];
               $dataKirim['url_delete'] = $urlDelete;
               $dataKirim['padding']= 50;
               $dataKirim['icon']='transkrip_nilai.gif';
               $dataKirim['url_edit_value'] = 'Edit '.GTFWConfiguration::GetValue( 'language', 'sub_kegiatan');
               $dataKirim['url_delete_value'] = 'Delete '.GTFWConfiguration::GetValue( 'language', 'sub_kegiatan');
               
               if($dataGrid[$i]['subprogJeniskegId'] == '1') {
                 $dataKirim['url_komponen_kegiatan'] =Dispatcher::Instance()->GetUrl('program_kegiatan', 'komponenKegiatan', 'view', 'html')."&grp=".$dataGrid[$i]['kegrefId'];                 
                 $dataKirim['url_komponen_kegiatan'] = '<a class="xhr dest_subcontent-element" href="'.$dataKirim['url_komponen_kegiatan'].'" title="Komponen Kegiatan"><img src="images/button-detail.gif" alt="List"></a>';
               } else {
                 $dataKirim['url_komponen_kegiatan'] = '';
               }
               
               
               
               $this->mrTemplate->AddVars('data_item', $dataKirim, 'DATA_');
               $this->mrTemplate->parseTemplate('data_item', 'a');
               //$no++;
            }
         }
         //debug($dataGrid);
      
      }
   
   }
  
}
?>
