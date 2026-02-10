<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/transaksi_penyusutan/business/AppTransaksiPenyusutanAsper.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewCetakListPenyusutan extends HtmlResponse {
   #var $Pesan;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/transaksi_penyusutan/template');
      $this->SetTemplateFile('view_cetak_list_penyusutan.html');
   }
   
   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }
   
   function ProcessRequest() {
      $Obj = new AppTransaksiPenyusutanAsper();
      if(isset($_GET['cetak'])) {
         $tgl_awal = Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);
         $tgl_akhir = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);
      }
      
      //view
      if(strlen($tgl_awal) == 10) {
         $tgl_awal = substr($tgl_awal,0,8);
         $tgl_akhir = substr($tgl_akhir,0,8);
      }
      $dataListPenyusutan = $Obj->GetCetakListPenyusutan($tgl_awal, $tgl_akhir);
      
      $msg = Messenger::Instance()->Receive(__FILE__);
      $this->Pesan = $msg[0][1];
      $this->css = $msg[0][2];

      $return['list_penyusutan'] = $dataListPenyusutan;
      $return['tgl_awal'] = $tgl_awal;
      $return['tgl_akhir'] = $tgl_akhir;
      return $return;
   }
   
   function ParseTemplate($data = NULL) {
      $search = $data['search'];
      $this->mrTemplate->AddVar('content', 'TGL_AWAL', IndonesianDate($data['tgl_awal'], 'yyyy-mm-dd'));
      $this->mrTemplate->AddVar('content', 'TGL_AKHIR', IndonesianDate($data['tgl_akhir'], 'yyyy-mm-dd'));

      if (empty($data['list_penyusutan'])) {
         $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
      } else {
         $decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
         $encPage = Dispatcher::Instance()->Encrypt($decPage);
         $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
         $list_penyusutan = $data['list_penyusutan'];
         
         for ($i=0; $i<sizeof($list_penyusutan); $i++) {
            $no = $i+$data['start'];
            $list_penyusutan[$i]['number'] = $no;
            if ($no % 2 != 0) $list_penyusutan[$i]['class_name'] = 'table-common-even';
            else $list_penyusutan[$i]['class_name'] = '';
            $list_penyusutan[$i]['periode_penyusutan'] =  IndonesianDate($list_penyusutan[$i]['periode_penyusutan'], 'yyyy-mm-dd');
            $list_penyusutan[$i]['nilai_penyusutan'] = number_format($list_penyusutan[$i]['nilai_penyusutan'],2,',','.');
            $list_penyusutan[$i]['akumulasi_penyusutan'] = number_format($list_penyusutan[$i]['akumulasi_penyusutan'],2,',','.');
            $list_penyusutan[$i]['nilai_buku'] = number_format($list_penyusutan[$i]['nilai_buku'],2,',','.');
            $list_penyusutan[$i]['url_log'] = Dispatcher::Instance()->GetUrl('ListPenyusutan', 'LogPenyusutan', 'view', 'html') . '&dataId=' . $idEnc . '&page=' . $encPage . '&cari='.$cari;
            $this->mrTemplate->AddVars('data_item', $list_penyusutan[$i], '');
            $this->mrTemplate->parseTemplate('data_item', 'a');    
         }
      }
   }
}
?>