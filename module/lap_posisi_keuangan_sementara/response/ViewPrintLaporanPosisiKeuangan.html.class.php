<?php
/**
* ================= doc ====================
* FILENAME     : ViewPrintLaporanPosisiKeuangan.html.class.php
* @package     : ViewPrintLaporanPosisiKeuangan
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-02-27
* @Modified    : 2015-02-27
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/lap_posisi_keuangan_sementara/business/LaporanPosisiKeuangan.class.php';

class ViewPrintLaporanPosisiKeuangan extends HtmlResponse
{
   protected $mObj;
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/lap_posisi_keuangan_sementara/template/');
      $this->SetTemplateFile('view_print_laporan_posisi_keuangan.html');
   }

   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }

   function ProcessRequest(){
      $this->mObj       = new LaporanPosisiKeuangan();
      $this->mObj->Setup();
      $get =  is_object($_GET) ? $_GET->AsArray() : $_GET;
      $tglAwal =  Dispatcher::Instance()->Decrypt($get['tanggal_awal']);
      $tglAkhir =  Dispatcher::Instance()->Decrypt($get['tanggal_akhir']);
      $subAccount = Dispatcher::Instance()->Decrypt($get['sub_account']);
      
      //prepare data posisi keuangan
      $this->mObj->LaporanBuilder()->PrepareData($tglAwal, $tglAkhir,$subAccount);
      $getKelompokLaporan = $this->mObj->LaporanBuilder()->laporanView();
      $return['get_kelompok_laporan'] = $getKelompokLaporan;
      $return['periode_nama'] = $this->mObj->LaporanBuilder()->getPeriodeNama();
      $return['periode_nama_ts'] = $this->mObj->LaporanBuilder()->getPeriodeNamaTs();
      $return['tgl_awal']= $tglAwal;
      $return['tgl_akhir']= $tglAkhir;

      return $return;
   }

   function ParseTemplate($data = null){
      $this->mrTemplate->AddVar('content', 'TGL_AWAL', IndonesianDate($data['tgl_awal'], 'yyyy-mm-dd'));
      $this->mrTemplate->AddVar('content', 'TGL_AKHIR', IndonesianDate($data['tgl_akhir'], 'yyyy-mm-dd'));
      
      $posisiKeuangan = $data['get_kelompok_laporan'];
      foreach ($posisiKeuangan as $key => $itemLaporan) {
         $itemLaporan['padding'] = ($itemLaporan['level'] - 1) * 15;
         if ($itemLaporan['is_summary'] == 'Y') {

            $itemLaporan['style'] = 'font-weight:bold';
            $pengali = 1;
            $jumlahSaldoKlp = $itemLaporan['saldo_summary'] * $pengali;
            $this->mrTemplate->AddVar('status', 'KELLAP_STATUS', 'SUMMARY');
         } else {
            $pengali = ($itemLaporan['is_tambah'] == 'T') ? -1 : 1;
            $itemLaporan['style'] = '';
            $jumlahSaldoKlp = $itemLaporan['saldo'] * $pengali;

            if ($itemLaporan['is_child'] == '0') {
               switch ($itemLaporan['level']) {
                  case '2': $title = '<h2>' . strtoupper($itemLaporan['nama']) . '</h2>';
                     $labelTotalAliranKas[] = strtoupper($itemLaporan['nama']);
                     break;
                  default : $title = '<b>' . $itemLaporan['nama'] . '</b>';
                     break;
               }
               $itemLaporan['nama'] = $title;
               $this->mrTemplate->AddVar('status', 'KELLAP_STATUS', 'PARENT');
            } else {
               if ($itemLaporan['level'] == 2) {
                  $title = '<b>' . $itemLaporan['nama'] . '</b>';
               } else {
                  $title = $itemLaporan['nama'];
               }
               $itemLaporan['nama'] = $title;
               $itemLaporan['url_detail'] = $urlDetil . '&kellap_id=' . $itemLaporan['id'];
               $this->mrTemplate->AddVar('status', 'KELLAP_STATUS', 'CHILD');
            }
         }


         if ($jumlahSaldoKlp >= 0) {
            $itemLaporan['nominal_saldo'] = number_format($jumlahSaldoKlp, 2, ',', '.');
         } else {
            $itemLaporan['nominal_saldo'] = '(' . number_format($jumlahSaldoKlp * (-1), 2, ',', '.') . ' )';
         }

         $this->mrTemplate->AddVar('status', 'KELLAP_NOMINAL_SALDO', $itemLaporan['nominal_saldo']);
         $this->mrTemplate->AddVars('posisi_keuangan', $itemLaporan, 'KELLAP_');
         $this->mrTemplate->parseTemplate('posisi_keuangan', 'a');
      }
   }
}
?>