<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/transaksi_pengendalian/business/AppTransaksiPengendalianAsper.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewInputTransaksiPengendalian extends HtmlResponse {

   var $Pesan;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/transaksi_pengendalian/template');
      $this->SetTemplateFile('view_input_transaksi_pengendalian.html');
   }
   
   function ProcessRequest() {
      $Obj = new AppTransaksiPengendalianAsper();
      $_POST = $_POST->AsArray();
      $decDataId = Dispatcher::Instance()->Decrypt($_GET['dataId']);
      $decStatusId = Dispatcher::Instance()->Decrypt($_GET['status']);
      $detil = $Obj->GetDetilPengendalianBrg($decDataId, $decStatusId);
      if($_POST || isset($_GET['cari'])) {
         if(isset($_POST['periode_mon'])) {
            $due_date_day = $_POST['due_date_day'];
            $due_date_mon = $_POST['due_date_mon'];
            $due_date_year = $_POST['due_date_year'];

            $tanggal_transaksi_day = $_POST['tanggal_transaksi_day'];
            $tanggal_transaksi_mon = $_POST['tanggal_transaksi_mon'];
            $tanggal_transaksi_year = $_POST['tanggal_transaksi_year'];
            $no_kkb = $_POST['no_kkb'];

            $catatan_transaksi = $_POST['catatan_transaksi'];
            $penanggung_jawab = $_POST['penanggung_jawab'];
            $skenario_label = $_POST['skenario_label'];
            $skenario = $_POST['skenario'];

         } elseif(isset($_GET['periode_mon'])) {
            $due_date_day = Dispatcher::Instance()->Decrypt($_GET['due_date_day']);
            $due_date_mon = Dispatcher::Instance()->Decrypt($_GET['due_date_mon']);
            $due_date_year = Dispatcher::Instance()->Decrypt($_GET['due_date_year']);

            $tanggal_transaksi_day = Dispatcher::Instance()->Decrypt($_GET['tanggal_transaksi_day']);
            $tanggal_transaksi_mon = Dispatcher::Instance()->Decrypt($_GET['tanggal_transaksi_mon']);
            $tanggal_transaksi_year = Dispatcher::Instance()->Decrypt($_GET['tanggal_transaksi_year']);
            $no_kkb = Dispatcher::Instance()->Decrypt($_GET['no_kkb']);

            $catatan_transaksi = Dispatcher::Instance()->Decrypt($_GET['catatan_transaksi']);
            $penanggung_jawab = Dispatcher::Instance()->Decrypt($_GET['penanggung_jawab']);
            $skenario_label = Dispatcher::Instance()->Decrypt($_GET['skenario_label']);
            $skenario = Dispatcher::Instance()->Decrypt($_GET['skenario']);

         } else {
            $due_date_day = date("d");
            $due_date_mon = date("m");
            $due_date_year = date("Y");

            $tanggal_transaksi_day = date("d");
            $tanggal_transaksi_mon = date("m");
            $tanggal_transaksi_year = date("Y");
            $no_kkb = '';

            $catatan_transaksi = '';
            $penanggung_jawab = '';
            $skenario_label = '';
            $skenario = '';
         }
      } else {
            $due_date_day = date("d");
            $due_date_mon = date("m");
            $due_date_year = date("Y");

            $tanggal_transaksi_day = date("d");
            $tanggal_transaksi_mon = date("m");
            $tanggal_transaksi_year = date("Y");
            $no_kkb = '';

            $catatan_transaksi = '';
            $penanggung_jawab = '';
            $skenario_label = '';
            $skenario = '';
      }
      $periode_awal = date("Y")-5;
      $periode_akhir = date("Y")+5;
      $due_date_selected = $due_date_year . "-" . $due_date_mon . "-" . $due_date_day;
      $tanggal_transaksi_selected = $tanggal_transaksi_year . "-" . $tanggal_transaksi_mon . "-" . $tanggal_transaksi_day;
         
      //due_date
      Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'due_date', array($due_date_selected, $periode_awal, $periode_akhir), Messenger::CurrentRequest);

      //tanggal_transaksi
      Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'tanggal_transaksi', array($tanggal_transaksi_selected, $periode_awal, $periode_akhir), Messenger::CurrentRequest);
      
      $msg = Messenger::Instance()->Receive(__FILE__);
      $this->Pesan = $msg[0][1];
      $this->css = $msg[0][2];
      
      $return['detil'] = $detil;
      $return['status'] = $decStatusId;
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $this->mrTemplate->AddVar("content", "URL_BACK", Dispatcher::Instance()->GetUrl('transaksi_pengendalian', 'TransaksiPengendalian', 'view', 'html'));
      $this->mrTemplate->AddVar("content", "URL_ACTION", Dispatcher::Instance()->GetUrl('transaksi_pengendalian', 'AddTransaksiPengendalian', 'do', 'html'));
      $this->mrTemplate->AddVar('content', 'URL_POPUP_SKENARIO', Dispatcher::Instance()->GetUrl('transaksi', 'popupSkenario', 'view', 'html'));
      $this->mrTemplate->AddVar('content', 'SKENARIO', 'manual');
      $this->mrTemplate->AddVar('content', 'SKENARIO_LABEL', 'Manual');
      if($data['status'] == '1') {
         $this->mrTemplate->AddVar('content', 'ID_TIPE_TRANSAKSI', '2');
         $this->mrTemplate->AddVar('content', 'TIPE_TRANSAKSI', 'Pengeluaran');
      }elseif($data['status'] == '2') {
         $this->mrTemplate->AddVar('content', 'ID_TIPE_TRANSAKSI', '');
         $this->mrTemplate->AddVar('content', 'TIPE_TRANSAKSI', 'Penerimaan');
      }
      if($this->Pesan) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
      }

      if (empty($data['detil'])) {
         $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
         $detil = $data['detil'];
         #print_r(detil);
         $this->mrTemplate->AddVar("content", "UNITKERJA", $detil[0]['unitkerja']);
         $this->mrTemplate->AddVar("content", "RUANG", $detil[0]['ruang']);
         $this->mrTemplate->AddVar("content", "TGL", IndonesianDate($detil[0]['tgl'], 'yyyy-mm-dd'));
         $this->mrTemplate->AddVar("content", "BA", $detil[0]['BA']);
         $this->mrTemplate->AddVar("content", "PIC", $detil[0]['pic']);
         $this->mrTemplate->AddVar("content", "KETERANGAN", $detil[0]['keterangan']);
         $nomor = 1;
         for ($i=0; $i<sizeof($detil); $i++) {
            $nomor = $i + 1;
            $this->mrTemplate->AddVar("data_item", "NOMOR", $nomor);
            $this->mrTemplate->AddVar("data_item", "STATUS_PENG_BRG", $detil[$i]['status_peng_brg']);
            $this->mrTemplate->AddVar("data_item", "BRG_KODE", $detil[$i]['brg_kode']);
            $this->mrTemplate->AddVar("data_item", "BRG_NAMA", $detil[$i]['brg_nama']);
            $this->mrTemplate->AddVar("data_item", "HARGA_PERKIRAAN", number_format($detil[$i]['harga_perkiraan'],2,',','.'));
            if ($i % 2 != 0) 
               $pengendalian[$i]['class_name'] = 'table-common-even';
            else 
               $pengendalian[$i]['class_name'] = '';
            #$this->mrTemplate->AddVar('data_item', $pengendalian[$i], '');
            $this->mrTemplate->parseTemplate('data_item', 'a');
         }
      }
   }
}
?>
