<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/history_transaksi_keuangan/business/AppTransaksi.class.php';
		
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewTransaksiDetil extends HtmlResponse {

	var $Pesan;
	//var $cssAlert = "notebox-alert";
	
	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
				'module/history_transaksi_keuangan/template');
		$this->SetTemplateFile('view_transaksi_detil.html');
	}
	
	function ProcessRequest() {
      //instance2
		$Obj = new AppTransaksi();
		$userUnitKerjaObj = new UserUnitKerja();
      
      //menerima get
      $this->_GET = $_GET->AsArray();

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

         //get data file
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
		//tahun pembukuan periode
		$thnB = $Obj->GetTahunPembukuan();
		$return['thnb'] = $thnB[0]['tppId'];

      
  		//COMBO JENIS TRANSAKSI
  		/**
      	$arr_jenis_transaksi = $Obj->GetComboJenisTransaksi();
      	foreach ($arr_jenis_transaksi as $value) $jns_trans[$value['id']] = $value['name'];
      	$return['jns_transaksi'] = json_encode($jns_trans);
   		*/
      	//COMBO TIPE TRANSAKSI
      	$arr_tipe_transaksi = $Obj->GetComboTipeTransaksi();
     	foreach ($arr_tipe_transaksi as $value) {
      		//$tipe_trans[$value['id']] = $value['name'];
      		//$return['tipe_transaksi'] = json_encode($tipe_trans);
			  if($value['id'] == $datadb['tipe'] ){
			  	$return['tipe_transaksi'] = $value['name'];
			  }	
      	}

      //menerima msg
		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];
      //print_r($msg);

		if(empty($datadb['penanggung_jawab']))
			$return['datadb']['penanggung_jawab'] = 
				Security::Instance()->mAuthentication->GetCurrentUser()->GetRealName();
				/*Get User Real Name login*/
		
		$return['role_name'] = $role['role_name'];
		$return['no_invoice_list'] = $_SESSION['no_invoice_list'];
		$return['start'] = $startRec+1;
      	$return['dataId'] = $decDataId;
		return $return;
	}
	
	function ParseTemplate($data = NULL) 
	{
      	$datadb = $data['datadb'];
		$this->mrTemplate->AddVar('content', 'URL_DETIL', 
									Dispatcher::Instance()->GetUrl(
														'history_transaksi_keuangan', 
														'detilTransaksi', 
														'view', 
														'html'));
		
  		$this->mrTemplate->AddVar('content', 'URL_TAMBAH', 
									Dispatcher::Instance()->GetUrl(
														'history_transaksi_keuangan', 
														'Transaksi', 
														'view', 
														'html'));
														
		$this->mrTemplate->AddVar('is_tambah', 'NO_KKB', $datadb['no_kkb']);
		$this->mrTemplate->AddVar('content', 'NOMINAL', 
									number_format($datadb['nominal'],2,',','.'));
		$this->mrTemplate->AddVar('content', 'CATATAN_TRANSAKSI', $datadb['catatan_transaksi']);
		
		$this->mrTemplate->AddVar('content', 'PENANGGUNG_JAWAB', $datadb['penanggung_jawab']);
      	//$this->mrTemplate->AddVar('content', 'JSON_JENIS_TRANS_LIST', $data['jns_transaksi']);
      	//$this->mrTemplate->AddVar('content', 'JSON_TIPE_TRANS_LIST', $data['tipe_transaksi']);
      	$this->mrTemplate->AddVar('content','NAME_TIPE_TRANSAKSI',$data['tipe_transaksi']);
      	$this->mrTemplate->AddVar('content', 'TANGGAL', $datadb['tanggal']);
      	$this->mrTemplate->AddVar('content', 'DUE_DATE', $datadb['due_date']);
      
	  //jika edit, lihat jenis transaksi, jika jenis=umum, set mak == disabled

		if($data['role_name'] == "Administrator") {
			$this->mrTemplate->AddVar('role', 'WHOAMI', 'ADMINISTRATOR');
		 	$this->mrTemplate->AddVar('role', 'UNITKERJA', $this->Data['unitkerja']);
         	$this->mrTemplate->AddVar('role', 'UNITKERJA_LABEL', $this->Data['unitkerja_label']);
		} elseif($data['role_name'] == "OperatorUnit") {
			$this->mrTemplate->AddVar('role', 'WHOAMI', 'OPERATORUNIT');
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
         $this->mrTemplate->AddVar('content', 'URL_EDIT', 
		 					Dispatcher::Instance()->GetUrl(
							 			'history_transaksi_keuangan', 
										 'Transaksi', 
										 'view', 
										 'html') . 
										 '&dataId=' . Dispatcher::Instance()->Encrypt($_GET['dataId']));
										 
         if($this->Data['jenis_transaksi'] != "1") {
		      $this->mrTemplate->AddVar('content', "MAK_LABEL", $data['datadbMAK']['nama']);
         } else {
            $this->mrTemplate->AddVar('content', 'MAK_IS_DISABLED', 'disabled="disabled"');
		      $this->mrTemplate->AddVar('content', 'MAK', "");
         }
      } else {
         	$this->mrTemplate->addVar('is_tambah','IS_TAMBAH','YES');
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
		if($data['thnb']==0){
			$msg = "Belum ada tahun pembukuan periode yang aktif";
			$this->mrTemplate->AddVar('content', 'ISI_MSG', $msg);
			$this->mrTemplate->AddVar('content', 'CLASS_MSG', "notebox-alert");
			$this->mrTemplate->AddVar('content', 'DISABLED', "disabled");
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