<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/jurnal/response/ProcJurnal.proc.class.php';

class ViewJurnal extends HtmlResponse
{

   protected $proc;

   protected $data;
   function ViewJurnal()
   {
      $this->proc = new ProcJurnal;
   }
   function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'module/jurnal/template');
      $this->SetTemplateFile('view_jurnal.html');
   }
   function ProcessRequest()
   {

      if (!empty($_GET['balik']) AND $_GET['balik'] == Dispatcher::Instance()->Decrypt('yes'))
      {
         $this->proc->BalikJurnal();
      }
      $addUrl = "";

      $bulan = date('m');
      $tahun = date('Y');

      if (isset($_POST['btnTampilkan']) OR isset($_GET['tampilkanSemua']))
      {
         $addUrl = "&tampilkanSemua=".Dispatcher::Instance()->Encrypt('true');
         $periode =  $this->proc->db->GetPeriodePembukuanAktif();
         $tgl_awal = $periode['tanggal_awal'];
         $tgl_akhir = $periode['tanggal_akhir'];
         $itemViewed = 20;
         $currPage = 1;
         $startRec = 0;

         if (isset($_GET['page']))
         {
            $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
            $startRec = ($currPage - 1) * $itemViewed;
         }
         $this->data = $this->proc->db->GetDataAll( $tgl_awal, $tgl_akhir,$startRec, $itemViewed);
         $totalData = $this->proc->db->GetCountAll();
      }
      else
      {

         if (isset($_POST['btnFilter']))
         {
            $no_referensi = $_POST['no_referensi'];
            $bulan = $_POST['bulan_mon'];
            $tahun = $_POST['bulan_year'];
         }
         elseif (isset($_GET['cari']))
         {
            $no_referensi = Dispatcher::Instance()->Decrypt($_GET['no_referensi']);
            $bulan = Dispatcher::Instance()->Decrypt($_GET['bulan']);
            $tahun = Dispatcher::Instance()->Decrypt($_GET['tahun']);
         }

         $itemViewed = 20;
         $currPage = 1;
         $startRec = 0;

         if (isset($_GET['page']))
         {
            $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
            $startRec = ($currPage - 1) * $itemViewed;
         }
         $this->data = $this->proc->db->GetData($no_referensi,$bulan, $tahun, $startRec, $itemViewed);
         $totalData = $this->proc->db->GetCount($no_referensi,$bulan, $tahun);
      }
      $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType) .
         '&mulai_day=' . Dispatcher::Instance()->Encrypt($decMulaiTanggal) .
         '&mulai_mon=' . Dispatcher::Instance()->Encrypt($decMulaiBulan) .
         '&mulai_year=' . Dispatcher::Instance()->Encrypt($decMulaiTahun) .
         '&selesai_day=' . Dispatcher::Instance()->Encrypt($decSelesaiTanggal) .
         '&selesai_mon=' . Dispatcher::Instance()->Encrypt($decSelesaiBulan) .
         '&selesai_year=' . Dispatcher::Instance()->Encrypt($decSelesaiTahun) .
         '&no_referensi=' . Dispatcher::Instance()->Encrypt($no_referensi) .
         '&bulan=' . Dispatcher::Instance()->Encrypt($bulan) .
         '&tahun=' . Dispatcher::Instance()->Encrypt($tahun) .
         '&cari=' . Dispatcher::Instance()->Encrypt(1).
         $addUrl;
      Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array(
         $itemViewed,
         $totalData,
         $url,
         $currPage
      ) , Messenger::CurrentRequest);

      Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'bulan',array($tahun.'-'.$bulan,date('Y')-1, date('Y')+1, false,'','tanggal'), Messenger::CurrentRequest);

      //start menghandle pesan yang diparsing
      $tmp = $this->proc->parsingUrl(__FILE__);

      if (isset($tmp['msg'])) $return['msg'] = $tmp['msg'];

      //end handle
      $return['start'] = $startRec + 1;
      $return['search'] = $search;
      $return['no_referensi'] = $no_referensi;
      $return['bulan'] = $bulan;
      $return['tahun'] = $tahun;
      $return['url_tampilkan_semua'] = $addUrl;
      return $return;
   }
   function ParseTemplate($data = NULL)
   {
      $this->mrTemplate->AddVar('content', 'NO_REFERENSI', $data['no_referensi']);
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl($this->proc->moduleName, $this->proc->moduleHome, 'view', 'html'));
      $this->mrTemplate->AddVar('content', 'URL_SEARCH_ALL', Dispatcher::Instance()->GetUrl($this->proc->moduleName, $this->proc->moduleHome, 'view', 'html'));
      $urlCetak = Dispatcher::Instance()->GetUrl(
        'jurnal', 
        'cetakListJurnal', 
        'view', 
        'html') . 
        '&no_referensi=' .Dispatcher::Instance()->Encrypt($data['no_referensi']) . 
        '&tahun=' . Dispatcher::Instance()->Encrypt($data['tahun']) . 
        '&bulan=' . Dispatcher::Instance()->Encrypt($data['bulan']) .
        $data['url_tampilkan_semua'];
      
      $this->mrTemplate->AddVar('content', 'URL_CETAK', $urlCetak);
      $urlXls  = Dispatcher::Instance()->GetUrl(
         'jurnal',
         'listJurnal',
         'view',
         'xlsx'
      ) .
      '&no_referensi='.Dispatcher::Instance()->Encrypt($data['no_referensi']).
      '&tahun='.Dispatcher::Instance()->Encrypt($data['tahun']).
      '&bulan='.Dispatcher::Instance()->Encrypt($data['bulan']) .
        $data['url_tampilkan_semua'];
      
      $this->mrTemplate->AddVar('content', 'URL_XLS', $urlXls);

      if (isset($data['msg']))
      {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);

         if ($data['msg']['action'] == 'msg') $class = 'notebox-done';
         else $class = 'notebox-warning';
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $class);
      }

      if (empty($this->data))
      {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
      }
      else
      {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
         $dataGrid = $this->data;
         $nomor = $data['start'];
         $referensi_id = $dataGrid[0]['id'];
         $flag_i = 0;
         $first = true;
         $nomor = $data['start'];
        
         $refId = '';
         $dataGridx = array();
         $ix = 0;
         
         for ($i = 0;$i < sizeof($dataGrid);)
         {
             
            $idEnc = Dispatcher::Instance()->Encrypt($dataGrid[$i]['id']);
            $id = Dispatcher::Instance()->Encrypt($dataGrid[$i]['transId']);
            if($refId == $dataGrid[$i]['id']) {
                
               
                $dataGridx[$ix]['nomor'] = $nomor;
                $dataGridx[$ix]['rekening_kode'] = $dataGrid[$i]['rekening_kode'];
                $dataGridx[$ix]['rekening_nama'] = $dataGrid[$i]['rekening_nama'];            
          
                //menentukan tampilan debet atau kredit

                if (strtoupper($dataGrid[$i]['tipeakun']) == 'D') $dataGridx[$ix]['debet'] = number_format($dataGrid[$i]['nilai'], 2, ',', '.');
                elseif (strtoupper($dataGrid[$i]['tipeakun']) == 'K') $dataGridx[$ix]['kredit'] = number_format($dataGrid[$i]['nilai'], 2, ',', '.');
                $dataGridx[$ix]['tanggal'] = $this->proc->db->date2string($dataGrid[$i]['tanggal']);

                if ($dataGrid[$i]['is_posting'] == 'Y') 
                    $dataGridx[$ix]['class_name'] = 'table-common-even2';
                else 
                    $dataGridx[$ix]['class_name'] = '';
            
                $dataGridx[$ix]['referensi_view'] = '';
                $dataGridx[$ix]['tanggal_view'] = '';
                $dataGridx[$ix]['petugas_entri_view'] = '';
                $dataGridx[$ix]['aksi_view'] = '';
                $dataGridx[$ix]['nomor'] = '';
                $dataGridx[$ix]['font'] = 'normal';
                $dataGridx[$ix]['type'] = 'coa';
                if ($dataGrid[$i]['is_posting'] == 'Y') { 
                    $dataGridx[$ix]['class_name'] = 'table-common-even2';
                } else { 
                    $dataGridx[$ix]['class_name'] = '';
                }
                $i++;

            } elseif($refId != $dataGrid[$i]['id']){
                $rowspan = 1;
                $refId = $dataGrid[$i]['id'];
                $dataGridx[$ix]['nomor'] = $nomor ;
                $dataGridx[$ix]['rekening_kode'] =$this->proc->db->date2string($dataGrid[$i]['tanggal']);
                if(!empty($dataGrid[$i]['catatan'])) {
                    $keterangan = $dataGrid[$i]['catatan'];
                } else {
                    $keterangan = '-';
                }
                
                $dataGridx[$ix]['referensi_view'] = $dataGrid[$i]['referensi'];
                $dataGridx[$ix]['rekening_nama'] = $keterangan;
                $dataGridx[$ix]['url_cetak'] = '<a href="javascript:cetak(\'' . Dispatcher::Instance()->GetUrl($this->proc->moduleName, 'cetakJurnal', 'view', 'html') . '&id=' . $id . '&prid='.$idEnc.'\')" ><img src="images/button-print.gif" alt="Cetak"></a>';
                $dataGridx[$ix]['url_edit'] = Dispatcher::Instance()->GetUrl($this->proc->moduleName, 'inputJurnal', 'view', 'html') . '&grp=' . $idEnc;
                $dataGridx[$ix]['url_jurnalbalik'] = Dispatcher::Instance()->GetUrl('jurnal', 'Jurnal', 'view', 'html') . '&balik=' . Dispatcher::Instance()->Encrypt('yes') . '&grp=' . $idEnc;
                $dataGridx[$ix]['aksi_view'] =  $dataGridx[$ix]['url_cetak'];
                $dataGridx[$ix]['petugas_entri_view'] = $dataGrid[$i]['petugas_entri'];
               
                    //=============start dipake componenet confirm delete ===============================
                $idDelete = Dispatcher::Instance()->Encrypt($dataGrid[$i]['id']);
                $urlAccept = $this->proc->moduleName . '|' . $this->proc->moduleDelete . '|do|html-cari-';
                $urlReturn = $this->proc->moduleName . '|' . $this->proc->moduleHome . '|view|html-cari-';
                $label = 'Delete Jurnal';
                $dataName = $dataGrid[$i]['referensi'];
                $dataGrid[$i]['url_delete'] = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html') . '&urlDelete=' . $urlAccept . '&urlReturn=' . $urlReturn . '&id=' . $idDelete . '&label=' . $label . '&dataName=' . $dataName;

                //=============end  dipake componenet confirm delete ===============================
                $dataGridx[$ix]['debet'] = '';
                $dataGridx[$ix]['kredit'] = '';
                $dataGridx[$ix]['font'] = 'bold';
                $dataGridx[$ix]['type'] = 'grup';
                if ($dataGrid[$i]['is_posting'] == 'Y') {
                    $dataGridx[$ix]['class_name'] = 'table-common-even2';
                } else { 
                    $dataGridx[$ix]['class_name'] = '';
                }
                $nomor++;                
            }

            
            $ix++;
         }

       

         //debug($dataGrid);
       
         foreach($dataGridx as $key => $val)
         {
             if($val['type'] == 'grup') {
                if($key > 0) {
                    $this->mrTemplate->SetAttribute('space', 'visibility', 'visible');
                } else {
                    $this->mrTemplate->SetAttribute('space', 'visibility', 'hidden');
                }
                $this->mrTemplate->AddVars('grup', $val, 'DATA_');
                $this->mrTemplate->SetAttribute('grup', 'visibility', 'visible');
                $this->mrTemplate->SetAttribute('coa', 'visibility', 'hidden');
             } else {
                $this->mrTemplate->AddVars('coa', $val, 'DATA_');
                $this->mrTemplate->SetAttribute('grup', 'visibility', 'hidden');
                $this->mrTemplate->SetAttribute('coa', 'visibility', 'visible');
                $this->mrTemplate->SetAttribute('space', 'visibility', 'hidden');
             }
                          
            //$this->mrTemplate->AddVars('data_item', $val, 'DATA_');
            $this->mrTemplate->parseTemplate('data_item', 'a');
         }
      }
   }
}
?>