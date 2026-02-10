<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/transaksi_pengendalian/business/AppTransaksiPengendalianAsper.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewTransaksiPengendalian extends HtmlResponse {

   var $Pesan;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/transaksi_pengendalian/template');
      $this->SetTemplateFile('view_transaksi_pengendalian.html');
   }
   
   function ProcessRequest() {
      $Obj = new AppTransaksiPengendalianAsper();
      $_POST = $_POST->AsArray();
      if(isset($_POST['mulai_day'])) {
         $decMulaiTanggal = Dispatcher::Instance()->Decrypt($_POST['mulai_day']);
         $decMulaiBulan = Dispatcher::Instance()->Decrypt($_POST['mulai_mon']);
         $decMulaiTahun = Dispatcher::Instance()->Decrypt($_POST['mulai_year']);

         $decSelesaiTanggal = Dispatcher::Instance()->Decrypt($_POST['selesai_day']);
         $decSelesaiBulan = Dispatcher::Instance()->Decrypt($_POST['selesai_mon']);
         $decSelesaiTahun = Dispatcher::Instance()->Decrypt($_POST['selesai_year']);

      } elseif(isset($_GET['mulai_day'])) {
         $decMulaiTanggal = Dispatcher::Instance()->Decrypt($_GET['mulai_day']);
         $decMulaiBulan = Dispatcher::Instance()->Decrypt($_GET['mulai_mon']);
         $decMulaiTahun = Dispatcher::Instance()->Decrypt($_GET['mulai_year']);

         $decSelesaiTanggal = Dispatcher::Instance()->Decrypt($_GET['selesai_day']);
         $decSelesaiBulan = Dispatcher::Instance()->Decrypt($_GET['selesai_mon']);
         $decSelesaiTahun = Dispatcher::Instance()->Decrypt($_GET['selesai_year']);

      } else {
         $decMulaiTanggal = date("01");
         $decMulaiBulan = date("01");
         $decMulaiTahun = date("Y");

         $decSelesaiTanggal = date("d");
         $decSelesaiBulan = date("m");
         $decSelesaiTahun = date("Y");
      }
      $key = $_POST['key'];
      $status_brg = $_POST['status_brg'];
      if(empty($_POST['unitkerja'])) {
         $return['unitkerja'] = '';
         $return['unitkerja_label'] = 'Semua';
      }else {
         $return['unitkerja'] = $_POST['unitkerja'];
         $return['unitkerja_label'] = $_POST['unitkerja_label'];
      }
      $mulai_selected = $decMulaiTahun . "-" . $decMulaiBulan . "-" . $decMulaiTanggal;
      $selesai_selected = $decSelesaiTahun . "-" . $decSelesaiBulan . "-" . $decSelesaiTanggal;
      
      $itemViewed = 20;
      $currPage = 1;
      $startRec = 0 ;
      if(isset($_GET['page'])) {
         $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $startRec =($currPage-1) * $itemViewed;
      }
      $pengendalian = $Obj->GetListPengendalian($key, $mulai_selected, $selesai_selected, $startRec, $itemViewed);
      $totalData = $Obj->CountDataPengendalian($key, $mulai_selected, $selesai_selected);

      $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&mulai_day=' . Dispatcher::Instance()->Encrypt($decMulaiTanggal) . '&mulai_mon=' . Dispatcher::Instance()->Encrypt($decMulaiBulan) . '&mulai_year=' . Dispatcher::Instance()->Encrypt($decMulaiTahun) . '&selesai_day=' . Dispatcher::Instance()->Encrypt($decSelesaiTanggal) . '&selesai_mon=' . Dispatcher::Instance()->Encrypt($decSelesaiBulan) . '&selesai_year=' . Dispatcher::Instance()->Encrypt($decSelesaiTahun) . '&key=' . Dispatcher::Instance()->Encrypt($key) . '&cari=' .  Dispatcher::Instance()->Encrypt(1));

      Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage), Messenger::CurrentRequest);
      
      $arr_status = $Obj->ComboStatusBrg();
      Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'status_brg', array('status_brg', $arr_status, $status_brg, "true", 'id="status_brg"'), Messenger::CurrentRequest);

      $msg = Messenger::Instance()->Receive(__FILE__);
      $this->Pesan = $msg[0][1];
      $this->css = $msg[0][2];

      $tahun['start'] = date("Y")-5;
      $tahun['end'] = date("Y")+5;
      Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'mulai', array($mulai_selected, $tahun['start'], $tahun['end'], '', '', 'mulai'), Messenger::CurrentRequest);
      Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'selesai', array($selesai_selected, $tahun['start'], $tahun['end'], '', '', 'selesai'), Messenger::CurrentRequest);
      
      $return['pengendalian'] = $pengendalian;
      $return['start'] = $startRec+1;
      $return['key'] = $key;
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('transaksi_pengendalian', 'TransaksiPengendalian', 'view', 'html'));
      $this->mrTemplate->AddVar('content', 'URL_POPUP_UNITKERJA', Dispatcher::Instance()->GetUrl('transaksi_pengendalian', 'PopupUnitkerja', 'view', 'html'));
      $this->mrTemplate->AddVar('content', 'UNITKERJA', $data['unitkerja']);
      $this->mrTemplate->AddVar('content', 'UNITKERJA_LABEL', $data['unitkerja_label']);
      $this->mrTemplate->AddVar('content', 'KEY', $data['key']);
      if($this->Pesan) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
      }

      if (empty($data['pengendalian'])) {
         $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
         $pengendalian = $data['pengendalian'];
         #print_r($pengendalian);
         for ($i=0; $i<sizeof($pengendalian); $i++) {
            $pengendalian[$i]['id_pengendalian'] = Dispatcher::Instance()->Encrypt($pengendalian[$i]['id']);
            $pengendalian[$i]['number'] = $i+$data['start'];
            if ($i % 2 != 0) 
               $pengendalian[$i]['class_name'] = 'table-common-even';
            else 
               $pengendalian[$i]['class_name'] = '';
            $pengendalian[$i]['peng_tgl'] = IndonesianDate($pengendalian[$i]['peng_tgl'], 'yyyy-mm-dd');
            $pengendalian[$i]['url_input_biaya'] = Dispatcher::Instance()->GetUrl('transaksi_pengendalian', 'InputTransaksiPengendalian', 'view', 'html') . '&dataId=' . Dispatcher::Instance()->Encrypt($pengendalian[$i]['id'])  . '&status=' . Dispatcher::Instance()->Encrypt($pengendalian[$i]['status']);
            $this->mrTemplate->AddVars('data_item', $pengendalian[$i], '');
            $this->mrTemplate->parseTemplate('data_item', 'a');
         }
      }
   }
}
?>
