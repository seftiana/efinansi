<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/jurnal_pengeluaran/response/ProcJurnalPengeluaran.proc.class.php';

class ViewJurnalPengeluaran extends HtmlResponse {
   protected $proc;
   protected $data;
   function __construct(){
      $this->proc    = new ProcJurnalPengeluaran;
   }

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/jurnal_pengeluaran/template');
      $this->SetTemplateFile('view_jurnal_pengeluaran.html');
   }


   function ProcessRequest() {
      if(!empty($_GET['balik']) AND $_GET['balik'] == Dispatcher::Instance()->Decrypt('yes')) {
         $this->proc->BalikJurnal();
      }
      $addUrl        = "";
      if(isset($_POST['btnTampilkan']) OR isset($_GET['tampilkanSemua'])) {
         $addUrl     = "&tampilkanSemua=true";
         $tgl_awal   = date("Y-m-d");
         $tgl_akhir  = $tgl_awal;
         $itemViewed = 20;
         $currPage   = 1;
         $startRec   = 0 ;
         if(isset($_GET['page'])) {
            $currPage   = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
            $startRec   = ($currPage-1) * $itemViewed;
         }
         $this->data    = $this->proc->db->GetDataAll($startRec, $itemViewed);
         $totalData     = $this->proc->db->GetCountAll();
      }else{
         if(isset($_POST['btnFilter'])) {
            $tgl_awal      = $_POST['tanggal_awal_year']->mrVariable;
            $tgl_awal      .= '-'.$_POST['tanggal_awal_mon']->mrVariable;
            $tgl_awal      .= '-'.$_POST['tanggal_awal_day']->mrVariable;

            $tgl_akhir     = $_POST['tanggal_akhir_year']->mrVariable;
            $tgl_akhir     .= '-'.$_POST['tanggal_akhir_mon']->mrVariable;
            $tgl_akhir     .= '-'.$_POST['tanggal_akhir_day']->mrVariable;
            $referensi     = $_POST['referensi']->mrVariable;
            $posting       = $_POST['posting']->mrVariable;

         } elseif(isset($_GET['cari'])) {
            $tgl_awal      = str_replace("|", "-", Dispatcher::Instance()->Decrypt($_GET['tgl_awal']));
            $tgl_akhir     = str_replace("|", "-", Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']));
            $referensi     = Dispatcher::Instance()->Decrypt($_GET['referensi']);
            $posting       = Dispatcher::Instance()->Decrypt($_GET['posting']);
         } else {
            $tgl_awal      = date("Y-01-01");
            $tgl_akhir     = date("Y-m-d");
            $referensi     = "";
            $posting       = "T";
         }

         $itemViewed       = 20;
         $currPage         = 1;
         $startRec         = 0 ;
         if(isset($_GET['page'])) {
            $currPage      = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
            $startRec      = ($currPage-1) * $itemViewed;
         }
         $this->data       = $this->proc->db->GetData($startRec,$itemViewed, $tgl_awal, $tgl_akhir, $referensi, $posting);
         $totalData        = $this->proc->db->GetCount($tgl_awal, $tgl_akhir, $referensi, $posting);
      }
      $arr_is_posting      = array(array(id=>'Y',name=>'Sudah'), array(id=>'T',name=>'Belum'));
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'posting',
         array(
            'posting',
            $arr_is_posting,
            $posting,
            true,
            ' style="width:100px;" id="posting"'
         ), Messenger::CurrentRequest);

      $tahunpencatatan  = $this->proc->db->GetMinMaxTahunPencatatan();

      Messenger::Instance()->SendToComponent(
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'tanggal_awal',
         array(
            $tgl_awal,
            $tahunpencatatan['minTahun'],
            $tahunpencatatan['maxTahun']
         ), Messenger::CurrentRequest);

      Messenger::Instance()->SendToComponent(
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'tanggal_akhir',
         array(
            $tgl_akhir,
            $tahunpencatatan['minTahun'],
            $tahunpencatatan['maxTahun']
         ), Messenger::CurrentRequest);



      $url = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType .
         '&tgl_awal=' . Dispatcher::Instance()->Encrypt(str_replace("-", "|", $tgl_awal)) .
         '&tgl_akhir=' . Dispatcher::Instance()->Encrypt(str_replace("-", "|", $tgl_akhir)) .
         '&referensi=' . Dispatcher::Instance()->Encrypt($referensi) .
         '&posting=' . Dispatcher::Instance()->Encrypt($posting) .
         '&cari=' . Dispatcher::Instance()->Encrypt(1).
         $addUrl
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

      //start menghandle pesan yang diparsing
      $tmp     = $this->proc->parsingUrl(__FILE__);
      //print_r($tmp);
      if(isset($tmp['msg'])){
         $return['msg']    = $tmp['msg'];
      }
      //end handle

      $return['referensi'] = $referensi;
      $return['start']     = $startRec+1;
      $return['search']    = $search;

      return $return;
   }

   function ParseTemplate($data = NULL) {
      $urlSearch        = Dispatcher::Instance()->GetUrl(
         $this->proc->moduleName,
         $this->proc->moduleHome,
         'view',
         'html'
      );
      $urlAdd           = Dispatcher::Instance()->GetUrl(
         $this->proc->moduleName,
         $this->proc->moduleInput,
         'view',
         'html'
      );
      $this->mrTemplate->AddVar('content', 'REFERENSI', $data['referensi']);
      $this->mrTemplate->AddVar('content', 'URL_SEARCH',  $urlSearch);
      $this->mrTemplate->AddVar('content', 'URL_ADD', $urlAdd);

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


      if (empty($this->data)) {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
         $dataGrid         = $this->data;
         $nomor            = $data['start'];
         $urlEdit          = Dispatcher::Instance()->GetUrl(
            $this->proc->moduleName,
            'inputJurnalPengeluaran',
            'view',
            'html'
         );
         $urlJurnalBalik   = Dispatcher::Instance()->GetUrl(
            $this->proc->moduleName,
            'JurnalPengeluaran',
            'view',
            'html'
         ).'&balik='.Dispatcher::Instance()->Encrypt('yes');

         $referensi_id     = $dataGrid[0]['id'];
         $flag_i           = 0;
         $first            = true;
         //$nomor=1;
         for ($i=0; $i<sizeof($dataGrid);$i++) {
            $idEnc      =  Dispatcher::Instance()->Encrypt($dataGrid[$i]['id']);
            $dataGrid[$i]['nomor']  = $nomor;
            // $dataGrid[$i]['url_detail']= Dispatcher::Instance()->GetUrl($this->proc->moduleName, 'jurnal_pengeluaranDetail', 'view', 'html').'&grp=' .$idEnc;
            $dataGrid[$i]['url_edit']= $urlEdit .'&grp=' .$idEnc;
            $dataGrid[$i]['url_jurnalbalik'] = $urlJurnalBalik .'&grp=' .$idEnc;

            //=============start dipake componenet confirm delete ===============================
            $idDelete   = Dispatcher::Instance()->Encrypt($dataGrid[$i]['id']);
            $urlAccept  = $this->proc->moduleName.'|'.$this->proc->moduleDelete.'|do|html-cari-';
            $urlReturn  = $this->proc->moduleName.'|'.$this->proc->moduleHome.'|view|html-cari-';
            $label      = 'Delete JurnalPengeluaran';
            $dataName   = $dataGrid[$i]['referensi'];
            $dataGrid[$i]['url_delete']   = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html').'&urlDelete='. $urlAccept.'&urlReturn='.$urlReturn.'&id='.$idDelete.'&label='.$label.'&dataName='.$dataName;
            //=============end  dipake componenet confirm delete ===============================

            //menentukan tampilan debet atau kredit
            if(strtoupper($dataGrid[$i]['tipeakun']) =='D'){
               $dataGrid[$i]['debet']  = number_format($dataGrid[$i]['nilai'] ,2 ,',','.');
            }elseif(strtoupper($dataGrid[$i]['tipeakun']) =='K'){
               $dataGrid[$i]['kredit'] = number_format($dataGrid[$i]['nilai'] ,2 ,',','.');
            }
            $dataGrid[$i]['tanggal']=$this->proc->db->date2string($dataGrid[$i]['tanggal']);

            if($dataGrid[$i]['is_posting']=='Y'){
               $dataGrid[$i]['class_name']   = 'table-common-even2';
            }else{
               $dataGrid[$i]['class_name']   = '';
            }

            $dataGrid[$i]['referensi_view']  = '';
            $dataGrid[$i]['tanggal_view']    = '';
            $dataGrid[$i]['petugas_entri_view']    ='';
            $dataGrid[$i]['aksi_view']       = '';
            $dataGrid[$i]['nomor']           = '';

            if($referensi_id != $dataGrid[$i]['id']) {
               $referensi_id     = $dataGrid[$i]['id'];
               $dataGrid[$flag_i]['referensi_view']   = '<td rowspan="'.$rowspan.'">'.$dataGrid[$flag_i]['referensi'].'</td>';
               $dataGrid[$flag_i]['tanggal_view']     = '<td rowspan="'.$rowspan.'">'.$dataGrid[$flag_i]['tanggal'].'</td>';
               $dataGrid[$flag_i]['petugas_entri_view']  = '<td rowspan="'.$rowspan.'">'.$dataGrid[$flag_i]['petugas_entri'].'</td>';
               $dataGrid[$flag_i]['nomor']               = '<td rowspan="'.$rowspan.'">'.$nomor.'</td>';
               $dataGrid[$flag_i]['keterangan_view']     = '<td rowspan="'.$rowspan.'">'.$dataGrid[$flag_i]['keterangan'].'</td>';

               if($dataGrid[$flag_i]['is_posting']=='T') {
                  if($dataGrid[$flag_i]['is_approved']=='T') {
                     $dataGrid[$flag_i]['aksi_view']     = '<td class="links" align="center" rowspan="'.$rowspan.'"><a class="xhr dest_subcontent-element" href="'.$dataGrid[$flag_i]['url_edit'].'" title="Ubah"><img src="images/button-edit.gif" alt="Ubah"></a><a class="xhr dest_subcontent-element" href="'.$dataGrid[$flag_i]['url_delete'].'" title="Hapus"><img src="images/button-delete.gif" alt="Hapus"></a></td>';
                  }else{
                     $dataGrid[$flag_i]['aksi_view']     = '<td class="links" align="center" rowspan="'.$rowspan.'">Jurnal Sudah Di Approve</td>';
                  }
               } else {
                  $dataGrid[$flag_i]['aksi_view']        = '<td class="links" align="center" rowspan="'.$rowspan.'"> <a href="'.$dataGrid[$flag_i]['url_jurnalbalik'].'" onclick="if(!confirm(\'Apakah yakin akan membalik jurnal ini ?\'))return false; GtfwAjax.replaceContentWithUrl(\'subcontent-element\',\''.$dataGrid[$flag_i]['url_jurnalbalik'].'&ascomponent=1\'); return false;" title="Jurnal Balik"><img src="images/button-cancel-tindaklanjut.gif" alt="Jurnal Balik"></a></td>';
               }

               $flag_i  = $i;
               $rowspan = 1;
               $nomor++;
            } else {
               $rowspan++;
            }
         }
         $dataGrid[$flag_i]['referensi_view']      = '<td rowspan="'.$rowspan.'">'.$dataGrid[$flag_i]['referensi'].'</td>';
         $dataGrid[$flag_i]['tanggal_view']        = '<td rowspan="'.$rowspan.'">'.$dataGrid[$flag_i]['tanggal'].'</td>';
         $dataGrid[$flag_i]['petugas_entri_view']  = '<td rowspan="'.$rowspan.'">'.$dataGrid[$flag_i]['petugas_entri'].'</td>';
         $dataGrid[$flag_i]['nomor']               = '<td rowspan="'.$rowspan.'">'.$nomor.'</td>';
         $dataGrid[$flag_i]['keterangan_view']     = '<td rowspan="'.$rowspan.'">'.$dataGrid[$flag_i]['keterangan'].'</td>';
         if($dataGrid[$flag_i]['is_posting']=='T') {
            if($dataGrid[$flag_i]['is_approved']=='T') {
               $dataGrid[$flag_i]['aksi_view'] ='<td class="links" align="center" rowspan="'.$rowspan.'"> <a class="xhr dest_subcontent-element" href="'.$dataGrid[$flag_i]['url_edit'].'" title="Ubah"><img src="images/button-edit.gif" alt="Ubah"></a> <a class="xhr dest_subcontent-element" href="'.$dataGrid[$flag_i]['url_delete'].'" title="Hapus"><img src="images/button-delete.gif" alt="Hapus"></a> </td>';
            }else{
               $dataGrid[$flag_i]['aksi_view']  = '<td class="links" align="center" rowspan="'.$rowspan.'">Jurnal Sudah Di Approve</td>';
            }
         } else {
            $dataGrid[$flag_i]['aksi_view'] ='<td class="links" align="center" rowspan="'.$rowspan.'"><a href="'.$dataGrid[$flag_i]['url_jurnalbalik'].'" onclick="if(!confirm(\'Apakah yakin akan membalik jurnal ini ?\'))return false; GtfwAjax.replaceContentWithUrl(\'subcontent-element\',\''.$dataGrid[$flag_i]['url_jurnalbalik'].'&ascomponent=1\'); return false;" title="Jurnal Balik"><img src="images/button-cancel-tindaklanjut.gif" alt="Jurnal Balik"></a></td>';
         }


         //debug($dataGrid);
         foreach($dataGrid as $val) {
            $this->mrTemplate->AddVars('data_item', $val, 'DATA_');
            $this->mrTemplate->parseTemplate('data_item', 'a');
         }
      }

   }
}
?>
