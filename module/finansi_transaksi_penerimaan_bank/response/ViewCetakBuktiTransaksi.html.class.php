<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/finansi_transaksi_penerimaan_bank/business/TransaksiPenerimaanBank.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'main/function/terbilang.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'main/function/date.php';


class ViewCetakBuktiTransaksi extends HtmlResponse 
{
   function TemplateModule() 
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/finansi_transaksi_penerimaan_bank/template');
      $this->SetTemplateFile('view_cetak_bukti_transaksi.html');
   }

   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print-custom-header.html');
      $this->SetTemplateFile('layout-common-print-custom-header.html');
   }

   function ProcessRequest() {
      $mObj       = new TransaksiPenerimaanBank();
      $mNumber    = new Number();
      $transaksi_id     = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
      $data_transaksi   = $mObj->getTransaksiDetil($transaksi_id);
      $data_transaksi['terbilang']  = $mNumber->Terbilang($data_transaksi['nominal'], 3);
      $transaksi_detail = $mObj->getListTransaksiDetail($transaksi_id);
      $tglMon        = date('m', strtotime($data_transaksi['tanggal']));
      $tglDay        = date('d', strtotime($data_transaksi['tanggal']));
      $tglYear       = date('Y', strtotime($data_transaksi['tanggal']));
      $time          = gmmktime(0,0,0, $tglMon, $tglDay, $tglYear);
      $tanggalCetak  = IndonesianDate(date('Y-m-d', time()),'YYYY-MM-DD');
      $kota          = GTFWConfiguration::GetValue('organization', 'city');

      $return['data_transaksi']     = $data_transaksi;
      $return['transaksi_detail']   = $transaksi_detail;
      $return['tanggal_cetak']      = $tanggalCetak;
      $return['kota']               = $kota;
      return $return;
   }

   function ParseTemplate($data = NULL) {     
      $data_transaksi      = $data['data_transaksi'];
      $transaksi_detail    = $data['transaksi_detail'];
      $tanggalCetak        = $data['tanggal_cetak'];
      $kota                = $data['kota'];

      if (empty($data_transaksi)) {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         
         for($i=0; $i < count($transaksi_detail); $i++){
            $transaksi['nama']                = $transaksi_detail[$i]['nama'];
            $transaksi['keterangan_detail']   = $transaksi_detail[$i]['keterangan'];
            $transaksi['nominal']             = number_format($transaksi_detail[$i]['nominal'], 0, ',', '.');
            $transaksi['nominal_total']       += $transaksi_detail[$i]['nominal'];
            $transaksi['keterangan']          = str_replace("\n", "<br/>", $data_transaksi['keterangan']);

            $this->mrTemplate->AddVars('transaksi_detail', $transaksi);
            $this->mrTemplate->parseTemplate('transaksi_detail', 'a');
         }

         $this->mrTemplate->AddVar('content', 'BPKB', $data_transaksi['bpkb']);
         $this->mrTemplate->AddVar('content', 'TGL_TRANSAKSI', IndonesianDate($data_transaksi['tanggal'], 'yyyy-mm-dd'));
         $this->mrTemplate->AddVar('content', 'NAMA_PENYETOR', ucwords($data_transaksi['nama_penyetor']));
         $this->mrTemplate->AddVar('content', 'BANK_PENERIMA', ucwords($data_transaksi['bank_penerima']));
         $this->mrTemplate->AddVar('content', 'TERBILANG', strtoupper($data_transaksi['terbilang']));
         $this->mrTemplate->AddVar('content', 'KOTA', $kota);
         $this->mrTemplate->AddVar('content', 'TGL_CETAK', $tanggalCetak);

         $this->mrTemplate->AddVar('transaksi_detail', 'KETERANGAN', $transaksi['keterangan']);

         $this->mrTemplate->AddVar('transaksi_detail', 'NAMA', $transaksi['nama']); 
         $this->mrTemplate->AddVar('transaksi_detail', 'KETERANGAN_DETAIL', $transaksi['keterangan_detail']); 
         $this->mrTemplate->AddVar('transaksi_detail', 'NOMINAL', $transaksi['nominal']); 
         $this->mrTemplate->AddVar('nominal_total', 'NOMINAL_TOTAL', number_format($transaksi['nominal_total'], 0, ',', '.')); 
         
      }
      
   }
}
?>
