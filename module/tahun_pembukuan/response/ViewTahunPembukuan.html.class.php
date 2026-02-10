<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'module/tahun_pembukuan/business/TahunPembukuan.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'module/tahun_pembukuan/business/TahunPembukuanPeriode.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';


class ViewTahunPembukuan extends HtmlResponse {
   private $Post;
   private $Pesan;
   private $SaldoAkhirIsUpdate;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/tahun_pembukuan/template');
      $this->SetTemplateFile('view_tahun_pembukuan.html');
   }

   function ProcessRequest() {
      //get message
      $msg = Messenger::Instance()->Receive(__FILE__);
      $this->Post = $msg[0][0];
      $this->Pesan = $msg[0][1];

      if($_POST['tanggal_awal'] != ''){
         $tglAwalSelected = $_POST['tanggal_awal'];
      }else if ($_GET['tgl_awal'] != ''){
         $tglAwalSelected = $_GET['tgl_awal'];
      }else{
         $tglAwalSelected = date('Y-m').'-01';
      }

      $TglAwalMax = date('Y')+5;
      $TglAwalMin = date('Y')-5;
      Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'tanggal_awal',
         array($tglAwalSelected, $TglAwalMin, $TglAwalMax), Messenger::CurrentRequest);

      if ($_POST['tanggal_akhir'] != '') {
         $tglAkhirSelected = $_POST['tanggal_akhir'];
      }else if ($_GET['tgl_akhir'] != '') {
         $tglAkhirSelected = $_GET['tgl_akhir'];
      }else {
         //$tglAkhirSelected = date('Y-m-d');
          switch (GTFWConfiguration::GetValue('application', 'tahun_pembukuan')){
            case 'monthly' :  $tglAkhirSelected = date('Y-m-t');
                              break;
            default        :  $tglAkhirSelected = date('Y-m-d',mktime(0, 0, 0, (date('m')), 0, date('Y')+1));
                              break;
          }
      }
      $TglAkhirMax = date('Y')+5;
      $TglAkhirMin = date('Y')-5;
      Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'tanggal_akhir',
         array($tglAkhirSelected, $TglAkhirMin, $TglAkhirMax), Messenger::CurrentRequest);

      $TahunPembukuan = new TahunPembukuan();
      $TahunPembukuanPeriode = new TahunPembukuanPeriode();
      $countTPP = $TahunPembukuanPeriode->GetCountTahunPembukuan();


      $idUnitKerja = $_POST['unitKerja'];
      if(!isset($_POST['unitKerja'])){
         $user_id = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
         $obj = new UserUnitKerja();
         $arrUnitKerja = $obj->GetUnitKerjaUser($user_id);
         $idUnitKerja = $arrUnitKerja['unit_kerja_id'];
      }

      # set default unit kerja = 1 (pusat)

      $arrUnitKerja = $TahunPembukuanPeriode->GetUnitKerja();
      Messenger::Instance()->SendToComponent('combobox', 'combobox', 'view', 'html', 'unitKerja',
         array('unitKerja', $arrUnitKerja, $idUnitKerja, '', ''),
         Messenger::CurrentRequest);


      $return['coa'] = $TahunPembukuan->GetListCoaAsTahunPembukuan($idUnitKerja);
      $return['total_aktiva'] = $TahunPembukuan->GetAktiva();
      $return['total_kewajiban'] = $TahunPembukuan->GetKewajiban();
      $return['total_modal'] = $TahunPembukuan->GetModal();
      $return['total_pasiva'] = $TahunPembukuan->GetPasiva();
      $return['tahun_aktif'] = $TahunPembukuanPeriode->GetTahunPembukuanPeriodeAktif();
      $return['count_tpp'] = $countTPP;
      $return['tpp_rolledback'] = $TahunPembukuanPeriode->GetTahunPembukuanIsRolledback();

      return $return;
   }


   function ParseTemplate($data = NULL) {
      //$this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('tahun_pembukuan', 'tahunPembukuan', 'view', 'html'));
      //jika tahun ga ada yg aktif dan balance == true siap buka tahun pembukuan baru
        if( $data['total_aktiva'] >=0) {
            $this->mrTemplate->AddVar('content', 'TOTAL_AKTIVA', number_format($data['total_aktiva'],2,',','.'));
        } else {
            $this->mrTemplate->AddVar('content', 'TOTAL_AKTIVA', '('.number_format($data['total_aktiva'] * (-1),2,',','.')).')';
        }
        
        if($data['total_kewajiban'] >= 0) {
            $this->mrTemplate->AddVar('content', 'TOTAL_KEWAJIBAN', number_format($data['total_kewajiban'],2,',','.'));
        } else {
            $this->mrTemplate->AddVar('content', 'TOTAL_KEWAJIBAN', '('. number_format($data['total_kewajiban'] * (-1),2,',','.').')');
        }
        
        if($data['total_modal'] >= 0) {
            $this->mrTemplate->AddVar('content', 'TOTAL_MODAL', number_format($data['total_modal'],2,',','.'));
        } else {
            $this->mrTemplate->AddVar('content', 'TOTAL_MODAL', '('.number_format($data['total_modal'] * (-1),2,',','.') .')');
        }
        if($data['total_pasiva'] >= 0) {
            $this->mrTemplate->AddVar('content', 'TOTAL_PASIVA', number_format($data['total_pasiva'],2,',','.'));
        } else {
            $this->mrTemplate->AddVar('content', 'TOTAL_PASIVA', '('.number_format($data['total_pasiva'] * (-1),2,',','.') .')');
        }
        
       
      if (empty($data['tahun_aktif'])){
         $this->mrTemplate->AddVar('tahun_visible', 'TAMPIL', 'tampil');
         $this->mrTemplate->AddVar('aksi_buka', 'TAMPIL', 'tampil');
         if($data['count_tpp']>0)
            $this->SaldoAkhirIsUpdate = 'Tidak';
         else
            $this->SaldoAkhirIsUpdate = 'Ya';
         //aksi
         $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('tahun_pembukuan', 'BukaBuku', 'do', 'html'));


      //jika tahun tidak aktif dan neraca tidak balance tidak bisa buka buku baru tmplkan peringatan
      }else {
         if(empty($data['tpp_rolledback'])){
            $this->mrTemplate->AddVar('btn_rollback', 'URL_KONFIRMASI_ROLLBACK',
               Dispatcher::Instance()->GetUrl(
                  'tahun_pembukuan',
                  'KonfirmasiRollback',
                  'view',
                  'html'
               )
            );

            $this->mrTemplate->SetAttribute('btn_rollback', 'visibility', 'visible');
         }

         // jika tahun ada yg aktif dan tanggal melewati tanggal akhir periode tampilkan aksi tutup buku
         if ($data['tahun_aktif'][0]['tppTanggalAkhir'] < date('Y-m-d')){
            $this->mrTemplate->AddVar('tahun_invisible', 'TAMPIL', 'tampil');
            //set tanggal awal dan tanggal akhir
            $this->mrTemplate->AddVar('tahun_invisible', 'TANGGAL_AWAL', IndonesianDate($data['tahun_aktif'][0]['tppTanggalAwal'],'yyyy-mm-dd'));
            $this->mrTemplate->AddVar('tahun_invisible', 'TANGGAL_AKHIR', IndonesianDate($data['tahun_aktif'][0]['tppTanggalAkhir'],'yyyy-mm-dd'));
            //tampilkan button aksi tutup buku
            $this->mrTemplate->AddVar('aksi_tutup', 'TAMPIL', 'tampil');
            $this->SaldoAkhirIsUpdate = 'Tidak';
            $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('tahun_pembukuan', 'TutupBuku', 'do', 'html'));
         }else
         {
            // jika ada tahun aktif dan tanggal blm melewati tanggal akhir periode tampilkan label tahun
            $this->mrTemplate->AddVar('tahun_invisible', 'TAMPIL', 'tampil');
            //set tanggal awal dan tanggal akhir
            $this->mrTemplate->AddVar('tahun_invisible', 'TANGGAL_AWAL', IndonesianDate($data['tahun_aktif'][0]['tppTanggalAwal'],'yyyy-mm-dd'));
            $this->mrTemplate->AddVar('tahun_invisible', 'TANGGAL_AKHIR', IndonesianDate($data['tahun_aktif'][0]['tppTanggalAkhir'],'yyyy-mm-dd'));
            $this->SaldoAkhirIsUpdate = 'Tidak';
         }
      }
       // tampilkan pesan eror,sukses dsb
      if (isset($this->Pesan)){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
         if($this->Post['done']=='ok')
            $class='notebox-done';
         else
            $class = 'notebox-warning';
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $class);
      }

      if (empty($data['coa'])) {
         $this->mrTemplate->AddVar('data', 'IS_DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data', 'IS_DATA_EMPTY', 'NO');
         $no = 1;
         //print_r($data['coa']);
         foreach($data['coa'] as $dt => $item) {

               $this->mrTemplate->AddVar('list_data', 'NUMBER', $no);
               $this->mrTemplate->AddVar('list_data', 'KODE_AKUN', $item['coaKodeAkun']);
               $this->mrTemplate->AddVar('list_data', 'NAMA_AKUN', $item['coaNamaAkun']);
               $this->mrTemplate->AddVar('list_data', 'TIPE_AKUN', $item['ctrNamaTipe']);
               // $this->mrTemplate->AddVar('list_data', 'SALDO_AWAL', number_format($item['tpSaldoAwal'],2,',','.'));
               $this->mrTemplate->AddVar('list_data', 'DEBET', number_format($item['debet'],2,',','.'));
               $this->mrTemplate->AddVar('list_data', 'KREDIT', number_format($item['kredit'],2,',','.'));
               
               if($item['tpSaldoAwal'] >= 0) {
                  $this->mrTemplate->AddVar('list_data', 'SALDO_AWAL', number_format($item['tpSaldoAwal'],2,',','.'));
              } else {
                  $this->mrTemplate->AddVar('list_data', 'SALDO_AWAL', '('. number_format(($item['tpSaldoAkhir'] * (-1)),2,',','.').')');
              }

               if($item['tpSaldoAkhir'] >= 0) {
                   $this->mrTemplate->AddVar('list_data', 'SALDO_AKHIR', number_format($item['tpSaldoAkhir'],2,',','.'));
               } else {
                   $this->mrTemplate->AddVar('list_data', 'SALDO_AKHIR', '('. number_format(($item['tpSaldoAkhir'] * (-1)),2,',','.').')');
               }
               
               if ($item['coaIsDebetPositif']=='1'){
                  $this->mrTemplate->AddVar('list_data', 'SALDO_NORMAL', 'Debet');
               }else{
                  $this->mrTemplate->AddVar( 'list_data', 'SALDO_NORMAL', 'Kredit');
               }
               //zebra tabel
               if ($no % 2 == 1) {
                  $this->mrTemplate->AddVar('list_data', 'DATA_CLASS_NAME', 'table-common-even');
               } else {
                  $this->mrTemplate->AddVar('list_data', 'DATA_CLASS_NAME', '');
               }

               /*if ($this->SaldoAkhirIsUpdate == 'Tidak'){
                  $this->mrTemplate->AddVar('not_ubah', 'NOT_UBAH','-');
               }else{*/
                  $this->mrTemplate->AddVar('aksi_ubah', 'URL_UBAH', Dispatcher::Instance()->GetUrl('tahun_pembukuan', 'DetilSaldoAwal', 'view', 'html').'&coaid='.$item['coaId'].'&headtxt='.$item['coaKodeAkun'].'|'.$item['coaNamaAkun']);

               //}


               $this->mrTemplate->parseTemplate('list_data', 'a');
               $no++;
         }
      }
   }
}
?>
