<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/transaksi_penyusutan/business/AppTransaksiPenyusutanAsper.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/transaksi_penyusutan/business/AppTransaksiPenyusutan.class.php';

class ViewTransaksiPenyusutan extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/transaksi_penyusutan/template');
		$this->SetTemplateFile('view_transaksi_penyusutan.html');
	}
	
	function ProcessRequest() {
      $ObjAsper = new AppTransaksiPenyusutanAsper(); 
		$Obj = new AppTransaksiPenyusutan();
      $this->_POST = $_POST->AsArray();
      $this->_GET = $_GET->AsArray();
      //jika ini adalah edit data, liat di database dab
      if($this->_GET['dataId'] != '') {
         $decDataId = Dispatcher::Instance()->Decrypt($this->_GET['dataId']);
         $datadb = $Obj->GetTransaksiById($decDataId);
         //tanggal2
         $due_date_selected = $datadb['due_date'];
         $tanggal_transaksi_selected = $datadb['tanggal'];
         $this->Data['jenis_transaksi'] = $datadb['jenis'];
         $this->Data['tipe_transaksi'] = $datadb['tipe'];
         $return['datadb'] = $datadb;

         //get data filie
         $return['datadbFile'] = $Obj->GetTransaksiFile($datadb['id']);

         #print_r($datadb);
      }
      //$this->_FILES = $_FILES->AsArray();
		if($_POST || isset($_GET['cari'])) {
			if(isset($_POST['periode_penyusutan_mon'])) {
				$periode_mon = $_POST['periode_penyusutan_mon'];
				$periode_year = $_POST['periode_penyusutan_year'];

				$periode_penyusutan_day = $_POST['periode_penyusutan_day'];
				$periode_penyusutan_mon = $_POST['periode_penyusutan_mon'];
				$periode_penyusutan_year = $_POST['periode_penyusutan_year'];

				$tanggal_transaksi_day = $_POST['tanggal_transaksi_day'];
				$tanggal_transaksi_mon = $_POST['tanggal_transaksi_mon'];
				$tanggal_transaksi_year = $_POST['tanggal_transaksi_year'];
				$no_kkb = $_POST['no_kkb'];

				$catatan_transaksi = $_POST['catatan_transaksi'];
				$penanggung_jawab = $_POST['penanggung_jawab'];
				$skenario_label = $_POST['skenario_label'];
				$skenario = $_POST['skenario'];
            $post_kib = $_POST['kib'];

			} elseif(isset($_GET['periode_penyusutan_mon'])) {
				$periode_mon = Dispatcher::Instance()->Decrypt($_GET['periode_penyusutan_mon']);
				$periode_year = Dispatcher::Instance()->Decrypt($_GET['periode_penyusutan_year']);

				$periode_penyusutan_day = Dispatcher::Instance()->Decrypt($_GET['periode_penyusutan_day']);
				$periode_penyusutan_mon = Dispatcher::Instance()->Decrypt($_GET['periode_penyusutan_mon']);
				$periode_penyusutan_year = Dispatcher::Instance()->Decrypt($_GET['periode_penyusutan_year']);

				$tanggal_transaksi_day = Dispatcher::Instance()->Decrypt($_GET['tanggal_transaksi_day']);
				$tanggal_transaksi_mon = Dispatcher::Instance()->Decrypt($_GET['tanggal_transaksi_mon']);
				$tanggal_transaksi_year = Dispatcher::Instance()->Decrypt($_GET['tanggal_transaksi_year']);
				$no_kkb = Dispatcher::Instance()->Decrypt($_GET['no_kkb']);

				$catatan_transaksi = Dispatcher::Instance()->Decrypt($_GET['catatan_transaksi']);
				$penanggung_jawab = Dispatcher::Instance()->Decrypt($_GET['penanggung_jawab']);
				$skenario_label = Dispatcher::Instance()->Decrypt($_GET['skenario_label']);
				$skenario = Dispatcher::Instance()->Decrypt($_GET['skenario']);
            $post_kib = Dispatcher::Instance()->Decrypt($_GET['kib']);

			} else {
				$periode_mon = date("m");
				$periode_year = date("Y");

				$periode_penyusutan_day = date("d");
				$periode_penyusutan_mon = date("m");
				$periode_penyusutan_year = date("Y");

				$tanggal_transaksi_day = date("d");
				$tanggal_transaksi_mon = date("m");
				$tanggal_transaksi_year = date("Y");
				$no_kkb = '';

				$catatan_transaksi = '';
				$penanggung_jawab = '';
				$skenario_label = '';
				$skenario = '';
            $post_kib = '';
			}
		}

      $periode_day = date("d");
      $periode_selected = $periode_year . "-" . $periode_mon . "-" . $periode_day;
      $periode_penyusutan_selected = $periode_penyusutan_year . "-" . $periode_penyusutan_mon . "-" . $periode_penyusutan_day;
      $tanggal_transaksi_selected = $tanggal_transaksi_year . "-" . $tanggal_transaksi_mon . "-" . $tanggal_transaksi_day;
      //print_r($this->_POST);
      //print_r($this->_GET);
      //print_r($_FILES);
      //print_r($_FILES);

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];
      //print_r($msg);

		//$periode = $Obj->GetMinMaxPeriode();
      $periode_awal = date("Y")-5;
      $periode_akhir = date("Y")+5;
      //periode_penyusutan
		Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'periode_penyusutan', array($periode_penyusutan_selected, $periode_awal, $periode_akhir, $status, 'tanggal'), Messenger::CurrentRequest);

      //periode
      #Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'periode_penyusutan', array($periode_penyusutan_selected, $periode_awal, $periode_akhir), Messenger::CurrentRequest);

      //tanggal_transaksi
      Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'tanggal_transaksi', array($tanggal_transaksi_selected, $periode_awal, $periode_akhir), Messenger::CurrentRequest);
      
      //get combo kib
      $arr_kib = $ObjAsper->GetComboKib();
      Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'kib', array('kib', $arr_kib, $post_kib, '-', 'id="kib"'), Messenger::CurrentRequest);

		$return['total_penyusutan_pegawai'] = $total_penyusutan_pegawai;
		$return['start'] = $startRec+1;
      $return['dataId'] = $this->_GET['dataId'];
		$return['search']['nama'] = $nama;
		return $return;
	}
	
	function ParseTemplate($data = NULL) {
      $url_view = Dispatcher::Instance()->GetUrl('transaksi_penyusutan', 'transaksiPenyusutan', 'view', 'html');
      
      if($this->_GET['dataId'] != '') {
         $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('transaksi_penyusutan', 'UpdateTransaksiPenyusutan', 'do', 'html'));
         $this->mrTemplate->AddVar('content', 'ID_TRANS', $data['dataId']);
      }
      else
         $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('transaksi_penyusutan', 'AddTransaksiPenyusutan', 'do', 'html'));
         
      $this->mrTemplate->AddVar('content', 'URL_DETIL_PENYUSUTAN', Dispatcher::Instance()->GetUrl('transaksi_penyusutan', 'ListPenyusutan', 'view', 'html'));
		$this->mrTemplate->AddVar('content', 'URL_DETIL', Dispatcher::Instance()->GetUrl('transaksi_penyusutan', 'detilTransaksiPenyusutan', 'view', 'html'));
		$this->mrTemplate->AddVar('content', 'URL_POPUP_KIB', Dispatcher::Instance()->GetUrl('transaksi_penyusutan', 'PopupKib', 'view', 'html'));
      $this->mrTemplate->AddVar('content', 'URL_POPUP_PENYUSUTAN_KIB', Dispatcher::Instance()->GetUrl('transaksi_penyusutan', 'PopupPenyusutanKib', 'view', 'html'));
      $this->mrTemplate->AddVar('content', 'URL_POPUP_SKENARIO', Dispatcher::Instance()->GetUrl('transaksi', 'popupSkenario', 'view', 'html'));
      $this->mrTemplate->AddVar('content', 'SKENARIO', 'manual');
      $this->mrTemplate->AddVar('content', 'SKENARIO_LABEL', 'Manual');
      
      $datadb = $data['datadb'];
      $this->mrTemplate->AddVar('content', 'NO_KKB', $datadb['no_kkb']);
      $this->mrTemplate->AddVar('content', 'NOMINAL_PENYUSUTAN_RP', $datadb['nominal']);
      $this->mrTemplate->AddVar('content', 'CATATAN', $datadb['catatan_transaksi']);
      $this->mrTemplate->AddVar('content', 'PENANGGUNG_JAWAB', $datadb['penanggung_jawab']);
      
      $datadbFile = $data['datadbFile'];
      if(empty($datadbFile)) {
         $this->mrTemplate->AddVar('tpl_file_list', 'FILE_LIST_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('tpl_file_list', 'FILE_LIST_EMPTY', 'NO');
         $no_file_list = array();
         $no_file_list = $data['no_file_list'];
         $number=1;
         //print_r($datadbFile);
         for($i=0;$i<sizeof($datadbFile);$i++) {
            $datadbFile[$i]['number'] = ($i+1);
            $datadbFile[$i]['id_delete'] = $datadbFile[$i]['id'];
            //$datadbFile[$i]['url_delete'] = $url_view . "&no_file_delete=" . Dispatcher::Instance()->Encrypt($nilai);
            $this->mrTemplate->AddVars('tpl_file_item', $datadbFile[$i], 'DATA_');
            $this->mrTemplate->parseTemplate('tpl_file_item', 'a');
         }
      }

		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}
	}
}
?>
