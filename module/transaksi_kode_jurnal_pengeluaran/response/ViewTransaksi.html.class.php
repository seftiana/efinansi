<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/transaksi_kode_jurnal_pengeluaran/business/AppTransaksi.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewTransaksi extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/transaksi_kode_jurnal_pengeluaran/template');
		$this->SetTemplateFile('view_transaksi.html');
	}
	
	function ProcessRequest() {
      //instance2
		$Obj = new AppTransaksi();
		$userUnitKerjaObj = new UserUnitKerja();
      
      //menerima pos dan get
      $this->_POST = $_POST->AsArray();
      $this->_GET = $_GET->AsArray();

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
		}
      //jika ini adalah edit data, liat di database dab
      if(isset($_GET['dataId'])) {
         $decDataId = Dispatcher::Instance()->Decrypt($_GET['dataId']);
         $datadb = $Obj->GetTransaksiById($decDataId);
         //tanggal2
         $due_date_selected = $datadb['due_date'];
         $tanggal_transaksi_selected = $datadb['tanggal'];
         $this->Data['jenis_transaksi'] = $datadb['jenis'];
         $this->Data['tipe_transaksi'] = $datadb['tipe'];
         $return['datadb'] = $datadb;
         //get data invoice
         $return['datadbInvoice'] = $Obj->GetTransaksiInvoice($datadb['id']);

         //get data filie
         $return['datadbFile'] = $Obj->GetTransaksiFile($datadb['id']);
         
         //jika 
         if($this->Data['jenis_transaksi'] != "1") {
            $return['datadbMAK'] = $Obj->GetTransaksiMAK($datadb['id']);
            //print_r($return['datadbMAK']);
            //echo "555";
         }
         //print_r($this->Data);
         //$return['datadbMAK'] = $Obj->GetTransaksiMAK($datadb['id']);
         //print_r($datadb);
      } else {
         //tanggal2
         $due_date_selected = $due_date_year . "-" . $due_date_mon . "-" . $due_date_day;
         $tanggal_transaksi_selected = $tanggal_transaksi_year . "-" . $tanggal_transaksi_mon . "-" . $tanggal_transaksi_day;
      }
      
      //siapa yang login?
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $role = $userUnitKerjaObj->GetRoleUser($userId);
      if($role['role_name'] == "Administrator") {
         $unit = $userUnitKerjaObj->GetSatkerUnitKerjaUserDua($userId);
         $this->Data['unitkerja'] = $unit['unit_kerja_id'];
         $this->Data['unitkerja_label'] = $unit['unit_kerja_nama'];
         if(isset($_GET['dataId'])) {         
            $this->Data['unitkerja'] = $datadb['unitkerja'];
            $this->Data['unitkerja_label'] = $datadb['unitkerja_label'];
         }
      } elseif($role['role_name'] == "OperatorUnit") {
         $unitkerja = $userUnitKerjaObj->GetSatkerUnitKerjaUserDua($userId);
         $this->Data['unitkerja'] = Dispatcher::Instance()->Decrypt($unitkerja['unit_kerja_id']);
         $this->Data['unitkerja_label'] = Dispatcher::Instance()->Decrypt($unitkerja['unit_kerja_nama']);
         if(isset($_GET['dataId'])) {         
            $this->Data['unitkerja'] = $datadb['unitkerja'];
            $this->Data['unitkerja_label'] = $datadb['unitkerja_label'];
         }
      } else {
         $unit = $userUnitKerjaObj->GetSatkerUnitKerjaUserDua($userId);
         $this->Data['unitkerja'] = $unit['unit_kerja_id'];
         $this->Data['unitkerja_label'] = $unit['unit_kerja_nama'];
      }

      $periode_awal = date("Y")-5;
      $periode_akhir = date("Y")+5;
      
      #untuk bikin otomasi nomer bukti
      if(!empty($this->_POST['check']) AND $this->_POST['check'] == 'yes') {
         $this->Data['jenis_transaksi'] = $this->_POST['jenis_transaksi'];
         $this->Data['tipe_transaksi'] = $this->_POST['tipe_transaksi'];
         $this->Data['unitkerja'] = $this->_POST['unitkerja'];
         $this->Data['unitkerja_label'] = $this->_POST['unitkerja_label'];
         $count_bukti_trans = $Obj->CountBuktiTrans($this->Data['tipe_transaksi'], date("m"), $this->Data['unitkerja']);
         $return['count_bukti_trans'] = $count_bukti_trans;
      }
      if(!empty($this->_GET['check']) AND $this->_GET['check'] == 'yes') {
         $this->Data['jenis_transaksi'] = $this->_GET['jenis_transaksi'];
         $this->Data['tipe_transaksi'] = $this->_GET['tipe_transaksi'];
         $this->Data['unitkerja'] = $this->_GET['unitkerja'];
         $this->Data['unitkerja_label'] = $this->_GET['unitkerja_label'];
         $count_bukti_trans = $Obj->CountBuktiTrans($this->Data['tipe_transaksi'], date("m"), $this->Data['unitkerja']);
         $return['count_bukti_trans'] = $count_bukti_trans;
      }

      //due_date
      Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'due_date', array($due_date_selected, $periode_awal, $periode_akhir), Messenger::CurrentRequest);

      //tanggal_transaksi
      Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'tanggal_transaksi', array($tanggal_transaksi_selected, $periode_awal, $periode_akhir), Messenger::CurrentRequest);

      //COMBO JENIS TRANSAKSI
      $arr_jenis_transaksi = $Obj->GetComboJenisTransaksi();
	  
		Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'jenis_transaksi', array('jenis_transaksi', $arr_jenis_transaksi, 1, "false", ' style="width:200px;" id="jenis_transaksi" onchange="showHideMAK(this)"'), Messenger::CurrentRequest);
      #onmousemove="showHideMAK(this)"   
      
      foreach ($arr_jenis_transaksi as $value) $jns_trans[$value['id']] = $value['name'];
      $return['jns_transaksi'] = json_encode($jns_trans);

      //COMBO TIPE TRANSAKSI
      // $arr_tipe_transaksi = $Obj->GetComboTipeTransaksi();
	  $arr_tipe_transaksi = $Obj->GetTipeTransaksi("Pengeluaran Anggaran");
		Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'tipe_transaksi', array('tipe_transaksi', $arr_tipe_transaksi, $this->Data['tipe_transaksi'], "false", ' style="width:200px; display:none;" onChange="jsCheck(this.value)" id="tipe_transaksi"'), Messenger::CurrentRequest);
         
      foreach ($arr_tipe_transaksi as $value) $tipe_trans[$value['id']] = $value['name'];
      $return['tipe_transaksi'] = json_encode($tipe_trans);

      //menerima msg
		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];
      //print_r($msg);

		if(empty($datadb['penanggung_jawab']))
			$return['datadb']['penanggung_jawab'] = Security::Instance()->mAuthentication->GetCurrentUser()->GetRealName();/*Get User Real Name login*/

		$return['tipe_transaksi']	= $arr_tipe_transaksi;
		$return['role_name'] = $role['role_name'];
		$return['no_invoice_list'] = $_SESSION['no_invoice_list'];
		$return['start'] = $startRec+1;
      $return['dataId'] = $decDataId;
      //print_r($return['datadbMAK']);
		//$return['search']['nama'] = $nama;
		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$this->mrTemplate->AddVar('content','ID_TIPE_TRANSAKSI',$data['tipe_transaksi'][0]['id']);
		$this->mrTemplate->AddVar('content','NAME_TIPE_TRANSAKSI',$data['tipe_transaksi'][0]['name']);
      $url_view = Dispatcher::Instance()->GetUrl('transaksi_kode_jurnal_pengeluaran', 'transaksi', 'view', 'html');
      $this->mrTemplate->AddVar('content', 'URL_CHECK', $url_view);
		$this->mrTemplate->AddVar('content', 'URL_DETIL', Dispatcher::Instance()->GetUrl('transaksi_kode_jurnal_pengeluaran', 'detilTransaksi', 'view', 'html'));
		$this->mrTemplate->AddVar('content', 'URL_POPUP_SKENARIO', Dispatcher::Instance()->GetUrl('transaksi_kode_jurnal_pengeluaran', 'popupSkenario', 'view', 'html'));
	   $this->mrTemplate->AddVar('content', 'URL_POPUP_MAK', Dispatcher::Instance()->GetUrl('transaksi_kode_jurnal_pengeluaran', 'popupKegiatanDetil', 'view', 'html'));
      $this->mrTemplate->AddVar('content', 'URL_POPUP_MAK_PENERIMAAN', Dispatcher::Instance()->GetUrl('transaksi_kode_jurnal_pengeluaran', 'PopupMakPenerimaan', 'view', 'html')); 
      $this->mrTemplate->AddVar('content', 'URL_POPUP_KEGIATAN_UNIT_KERJA', Dispatcher::Instance()->GetUrl('transaksi_kode_jurnal_pengeluaran', 'popupKegiatanUnitKerja', 'view', 'html')); 
	   $this->mrTemplate->AddVar('content', 'URL_POPUP_MAK', Dispatcher::Instance()->GetUrl('transaksi_kode_jurnal_pengeluaran', 'popupKegiatanDetil', 'view', 'html'));
      $this->mrTemplate->AddVar('content', 'URL_POPUP_SISA_ANGGARAN', Dispatcher::Instance()->GetUrl('transaksi_kode_jurnal_pengeluaran', 'popupSisaAnggaran', 'view', 'html'));
      $this->mrTemplate->AddVar('content', 'URL_POPUP_KODE_JURNAL', Dispatcher::Instance()->GetUrl('transaksi_kode_jurnal_pengeluaran', 'popupKodeJurnal', 'view', 'html'));
      $datadb = $data['datadb'];
	$this->mrTemplate->AddVar('content', 'URL_RESET', 
										Dispatcher::Instance()->GetUrl(
														Dispatcher::Instance()->mModule, 
														Dispatcher::Instance()->mSubModule, 
														Dispatcher::Instance()->mAction,
												  		Dispatcher::Instance()->mType));
       /**
 		 * untuk keperluan update, jika unit kerja dan tipe transaksi berubah maka mempengarui 
 		 * penomiran no bukti transaksi
 		 */
		$this->mrTemplate->AddVar('is_tambah', 'NO_KKB', $datadb['no_kkb']);
		$this->mrTemplate->AddVar('is_tambah', 'UNITKERJA_LAMA', $datadb['unitkerja']);
		$this->mrTemplate->AddVar('is_tambah', 'TIPE_TRANSAKSI_LAMA', $datadb['tipe']);
		/**
		 * end
		 */
		 
		//$this->mrTemplate->AddVar('content', 'NO_KKB', $datadb['no_kkb']);
		$this->mrTemplate->AddVar('content', 'NOMINAL', $datadb['nominal']);
		$this->mrTemplate->AddVar('content', 'CATATAN_TRANSAKSI', $datadb['catatan_transaksi']);
		$this->mrTemplate->AddVar('content', 'PENANGGUNG_JAWAB', $datadb['penanggung_jawab']);
      $this->mrTemplate->AddVar('content', 'JSON_JENIS_TRANS_LIST', $data['jns_transaksi']);
      $this->mrTemplate->AddVar('content', 'JSON_TIPE_TRANS_LIST', $data['tipe_transaksi']);
     // $this->mrTemplate->AddVar('content', 'COUNT_NO_BUKTI_COUNT', $data['count_bukti_trans']['count_trans']);
      //$this->mrTemplate->AddVar('content', 'COUNT_NO_BUKTI_UNIT', $data['count_bukti_trans']['unit_kerja_kode'].'/'.date("m").'.'.date("Y"));
//print_r($data);
//print_r($this->Data);
      //jika edit, lihat jenis transaksi, jika jenis=umum, set mak == disabled

		if($data['role_name'] == "Administrator") {
			$this->mrTemplate->AddVar('role', 'WHOAMI', 'ADMINISTRATOR');
			$this->mrTemplate->AddVar('role', 'URL_POPUP_UNITKERJA', Dispatcher::Instance()->GetUrl('transaksi_kode_jurnal_pengeluaran', 'popupUnitkerja', 'view', 'html'));
         $this->mrTemplate->AddVar('role', 'UNITKERJA', $this->Data['unitkerja']);
         $this->mrTemplate->AddVar('role', 'UNITKERJA_LABEL', $this->Data['unitkerja_label']);
		} elseif($data['role_name'] == "OperatorUnit") {
			$this->mrTemplate->AddVar('role', 'WHOAMI', 'OPERATORUNIT');
			$this->mrTemplate->AddVar('role', 'URL_POPUP_UNITKERJA', Dispatcher::Instance()->GetUrl('transaksi_kode_jurnal_pengeluaran', 'popupUnitkerja', 'view', 'html'));
         $this->mrTemplate->AddVar('role', 'UNITKERJA', $this->Data['unitkerja']);
         $this->mrTemplate->AddVar('role', 'UNITKERJA_LABEL', $this->Data['unitkerja_label']);
		} else {
			$this->mrTemplate->AddVar('role', 'WHOAMI', 'OPERATORSUBUNIT');
         $this->mrTemplate->AddVar('role', 'UNITKERJA', $this->Data['unitkerja']);
         $this->mrTemplate->AddVar('role', 'UNITKERJA_LABEL', $this->Data['unitkerja_label']);
		}
      //$this->mrTemplate->AddVar('content', 'MAK_LABEL', $data['datadbMAK']['nama']);

      if(isset($_GET['dataId']) AND $_GET['dataId']->Integer()->Raw() > 0) {
      	 $this->mrTemplate->addVar('is_tambah','IS_TAMBAH','NO');
         $this->mrTemplate->AddVar('content', "MODE_20080109", 'Ubah');
         $this->mrTemplate->AddVar('content', "DATA_ID", Dispatcher::Instance()->Encrypt($_GET['dataId']));
         $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('transaksi_kode_jurnal_pengeluaran', 'updateTransaksi', 'do', 'html') . '&dataId=' . Dispatcher::Instance()->Encrypt($_GET['dataId']));
         if($this->Data['jenis_transaksi'] != "1") {
            //print_r($data['datadbMAK']);
		      $this->mrTemplate->AddVar('content', "MAK_LABEL", $data['datadbMAK']['nama']);
		      $this->mrTemplate->AddVar('content', "MAK", $data['datadbMAK']['kode']);
		      $this->mrTemplate->AddVar('content', "MAK_LAMA", $data['datadbMAK']['kode']);
		      $this->mrTemplate->AddVar('content', "MAK_LAMA_ID", $data['datadbMAK']['id']);
         } else {
            $this->mrTemplate->AddVar('content', 'MAK_IS_DISABLED', 'disabled="disabled"');
		      $this->mrTemplate->AddVar('content', 'MAK', "");
         }
      } else {
      	 $this->mrTemplate->addVar('is_tambah','IS_TAMBAH','YES');
         $this->mrTemplate->AddVar('content', "MODE_20080109", 'Tambah');
		   $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('transaksi_kode_jurnal_pengeluaran', 'addTransaksi', 'do', 'html'));
      }
      $this->mrTemplate->AddVar('content', 'SKENARIO', 'manual');
      $this->mrTemplate->AddVar('content', 'SKENARIO_LABEL', 'Manual');

      $datadbInvoice = $data['datadbInvoice'];
      if(empty($datadbInvoice)) {
			$this->mrTemplate->AddVar('tpl_invoice_list', 'INVOICE_LIST_EMPTY', 'YES');
      } else {
			$this->mrTemplate->AddVar('tpl_invoice_list', 'INVOICE_LIST_EMPTY', 'NO');
         $no_invoice_list = array();
			$no_invoice_list = $data['no_invoice_list'];
         $number=1;
         //print_r($datadbInvoice);
         for($i=0;$i<sizeof($datadbInvoice);$i++) {
            $datadbInvoice[$i]['number'] = ($i+1);
            $datadbInvoice[$i]['id_delete'] = $datadbInvoice[$i]['id'];
				$this->mrTemplate->AddVars('tpl_invoice_item', $datadbInvoice[$i], 'DATA_');
				$this->mrTemplate->parseTemplate('tpl_invoice_item', 'a');
         }
      }

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
      //$this->mrTemplate->DisplayParsedTemplate();
	}
}
?>
