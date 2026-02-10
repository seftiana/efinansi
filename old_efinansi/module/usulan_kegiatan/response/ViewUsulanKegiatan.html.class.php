<?php
		/*
		Keterangan Modifikasi 7-11-2011
		
		Sebelumnya ada form pencarian unit dan sub unit
		kemudian pencarian diubah hanya menjadi unit / sub unit
		
		untuk role sebagai administrator
			+dapat memilih tahun
			+dapat melihat semua unit
			+dapat memilih program
		
		untuk role sebagai operator unit
			+dapat memilih sub unit
			+dapat memilih program
			
		untuk role sebagai operator sub unit
			+hanya dapat memilih program		
		*/
		
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
			'module/usulan_kegiatan/business/AppUsulanKegiatan.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
			'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewUsulanKegiatan extends HtmlResponse {

	var $Pesan;
	var $Data;
	var $Search;
	var $usulankegiatanObj;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
					'module/usulan_kegiatan/template');
		$this->SetTemplateFile('view_usulan_kegiatan.html');
	}

	function ProcessRequest() {
	
		$_POST = $_POST->AsArray();		
		$this->usulankegiatanObj = new AppUsulanKegiatan();			
		$userUnitKerja = new UserUnitKerja();
		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$role = $userUnitKerja->GetRoleUser($userId);	

		
	
		// AMBIL DATA POST / GET / Dari database (untuk user yang memiliki role selain admin) 
		/**
		if($role['role_name'] == "Administrator") {
		*/
			//tahun anggaran dari $_POST dan $_GET, unitkerja dan sub-unit dari $_POST dan $_GET
			if($_POST['btnTampilkan']) {
				$this->Data = $_POST;
				//print_r($this->Data);
			} elseif($_GET['cari'] != "") {
				$this->Data = $_GET;				
			} else {
				$tahun_anggaran = $this->usulankegiatanObj->GetTahunAnggaranAktif();
				//print_r($tahun_anggaran);
				$this->Data = $_POST;
				$this->Data['tahun_anggaran'] = $tahun_anggaran['id'];
				$this->Data['tahun_anggaran_label'] = $tahun_anggaran['name'];
			}
			$arr_tahun_anggaran = $this->usulankegiatanObj->GetComboTahunAnggaran();
			Messenger::Instance()->SendToComponent(
						'combobox', 'Combobox', 'view', 'html', 'tahun_anggaran', 
						array(
								'tahun_anggaran', $arr_tahun_anggaran, 
								$this->Data['tahun_anggaran'], '-', 
								' style="width:200px;" id="tahun_anggaran"'), 
						Messenger::CurrentRequest);
			
			/**
		} elseif($role['role_name'] == "OperatorUnit") {

			//tahun anggaran dan unitkerja dari database, subunit dari $_POST dan $_GET
			if(isset($_POST['btnTampilkan'])) {
				$this->Data = $_POST;
			} elseif(isset($_GET['cari'])) {
				$this->Data = $_GET;	
			} else {
				$this->Data = $_POST;
			}
			$tahun_anggaran = $this->usulankegiatanObj->GetTahunAnggaranAktif();
			$this->Data['tahun_anggaran'] = $tahun_anggaran['id'];
			$this->Data['tahun_anggaran_label'] = $tahun_anggaran['name'];
			
		} else {
			//tahun anggaran, unitkerja, dan subunit dari database
			if(isset($_POST['btnTampilkan'])) {
				$this->Data = $_POST;
			} elseif(isset($_GET['cari'])) {
				$this->Data = $_GET;	
			} else {
				$this->Data = $_POST;
			}
			$tahun_anggaran = $this->usulankegiatanObj->GetTahunAnggaranAktif();
			$this->Data['tahun_anggaran'] = $tahun_anggaran['id'];
			$this->Data['tahun_anggaran_label'] = $tahun_anggaran['name'];
			
		}
		*/
		// END AMBIL DATA POST / GET / Dari database (untuk user yang memiliki role selain admin) 
		
		
		
			
		//kalau masih awal, form pencarian untuk unit/subunit masih kosong, maka pengisian pada form pencarian diisi sebagai berikut 
		$unit = $this->usulankegiatanObj->GetUnit();			
		if(!isset($this->Data['unit_kode_sistem'])){
			$this->Data['unit_kode_sistem'] = $unit[0]['kodeSistem'];
		}
		
		if(!isset($this->Data['id_unit'])){
			$this->Data['id_unit'] = $unit[0]['id'];	
		}
		
		if(!isset($this->Data['nama_unit'])){
			$this->Data['nama_unit'] = $unit[0]['nama'];	
		}
		//end kode kalau masih awal, form pencarian untuk unit/subunit masih kosong, maka pengisian pada form pencarian diisi sebagai berikut 
		
		
		
		$totalData = $this->usulankegiatanObj->GetCountDataUsulanKegiatan(
						$this->Data['tahun_anggaran'], 
						$this->Data['id_unit'],
						$this->Data['unit_kode_sistem'], 
						$this->Data['program'],
						$this->Data['kodenama']);
						
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataUsulanKegiatan = $this->usulankegiatanObj->GetDataUsulanKegiatan(
									$startRec, 
									$itemViewed, 
									$this->Data['tahun_anggaran'], 
									$this->Data['id_unit'],
									$this->Data['unit_kode_sistem'], 
									$this->Data['program'],
									$this->Data['kodenama']);
									
		$url = Dispatcher::Instance()->GetUrl(
					Dispatcher::Instance()->mModule, 
					Dispatcher::Instance()->mSubModule, 
					Dispatcher::Instance()->mAction, 
					Dispatcher::Instance()->mType . 
					'&kodenama=' . Dispatcher::Instance()->Encrypt($this->Data['kodenama']) . 
					'&tahun_anggaran=' . Dispatcher::Instance()->Encrypt($this->Data['tahun_anggaran']).
					'&program=' . Dispatcher::Instance()->Encrypt($this->Data['program']) . 
					'&nama_unit=' . $this->Data['nama_unit'] . 
					'&id_unit=' . $this->Data['id_unit']. 
					'&unit_kode_sistem='.$this->Data['unit_kode_sistem']. 
					'&program_label=' . Dispatcher::Instance()->Encrypt($this->Data['program_label']) . 
					'&cari=' . Dispatcher::Instance()->Encrypt(1));

		Messenger::Instance()->SendToComponent(
					'paging', 
					'Paging', 
					'view', 
					'html', 
					'paging_top', 
					array(
							$itemViewed,
							$totalData, 
							$url, 
							$currPage), 
					Messenger::CurrentRequest);

		$return['role_name'] = $role['role_name'];

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];
		
		
		$return['dataUsulanKegiatan'] = $dataUsulanKegiatan;
		$return['start'] = $startRec+1;
		$return['unitKerja'] = $this->Data;
		$return['userUnitKerjaId']= $unit[0]['id'];
		$return['search']['usulan_kegiatan'] = $usulankegiatan;
		$return['search']['tahun_anggaran'] = $tahun_anggaran;
		$return['search']['arr_tahun_anggaran'] = $arr_tahun_anggaran;
		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$urlUnitKerja = Dispatcher::Instance()->GetUrl(
											'usulan_kegiatan',
											'unitKerja',
											'popup',
											'html');
											
		$this->mrTemplate->AddVar('content','URL_POPUP_UNIT_KERJA',$urlUnitKerja);	
		
		/**
		 * cek apakah unit yang aktif memiliki sub unit atau tidak
		 */
	 	if($this->usulankegiatanObj->GetTotalSubUnit($data['userUnitKerjaId']) > 0){
	 		$this->mrTemplate->AddVar('is_parent','IS_PARENT','YES');
	 	} else {
	 		$this->mrTemplate->AddVar('is_parent','IS_PARENT','NO');
	 	}
	 	
		$search = $data['search'];
		$this->mrTemplate->AddVar('content','KODENAMA',$this->Data['kodenama']);	
		//Mengisi form pencarian untuk unit / sub unit pertama kali menu dipilih
		$this->mrTemplate->AddVar('content', 'UNITKODESISTEM',$data['unitKerja']['unit_kode_sistem'] );
		$this->mrTemplate->AddVar('content', 'UNITID',$data['unitKerja']['id_unit'] );
		$this->mrTemplate->AddVar('is_parent', 'UNITNAMA',$data['unitKerja']['nama_unit'] );
		//end Mengisi form pencarian untuk unit / sub unit pertama kali menu dipilih
		/**		
		if($data['role_name'] == "Administrator") {
			//PAK ADMIN, tahun_anggaran:combo, unitkerja-subunitkerja:textbox
			$this->mrTemplate->AddVar('role', 'WHOAMI', 'ADMINISTRATOR');
		} else if($data['role_name'] == "OperatorUnit") {
					//OP UNIT, tahun_anggaran:label, unitkerja:label, subunitkerja:textbox
					$this->mrTemplate->AddVar('role', 'WHOAMI', 'OPERATORUNIT');
					$this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN', $this->Data['tahun_anggaran']);
					$this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN_LABEL', $this->Data['tahun_anggaran_label']);
		} else {
			//OP SUB UNIT, tahun_anggaran:label, unitkerja:label, subunitkerja:label
			$this->mrTemplate->AddVar('role', 'WHOAMI', 'OPERATORSUBUNIT');
			$this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN', $this->Data['tahun_anggaran']);
			$this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN_LABEL', $this->Data['tahun_anggaran_label']);
		}
		*/
		$this->mrTemplate->AddVar('content', 'SATKER', $this->Data['satker']);
		$this->mrTemplate->AddVar('content', 'SATKER_LABEL', $this->Data['satker_label']);
		$this->mrTemplate->AddVar('content', 'UNITKERJA', $this->Data['unitkerja']);
		$this->mrTemplate->AddVar('content', 'UNITKERJA_LABEL', $this->Data['unitkerja_label']);
		$this->mrTemplate->AddVar('content', 'PROGRAM', $this->Data['program']);
		$this->mrTemplate->AddVar('content', 'PROGRAM_LABEL', $this->Data['program_label']);
		$this->mrTemplate->AddVar('content', 'LATARBELAKANG', $this->Data['latarbelakang']);

		$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
				Dispatcher::Instance()->GetUrl(
												'usulan_kegiatan', 
												'usulanKegiatan', 
												'view', 
												'html'));
		$this->mrTemplate->AddVar('content', 'URL_ADD', 
				Dispatcher::Instance()->GetUrl(
												'usulan_kegiatan', 
												'inputUsulanKegiatan', 
												'view', 
												'html'));
		$this->mrTemplate->AddVar('content', 'URL_POPUP_PROGRAM', 
				Dispatcher::Instance()->GetUrl(
												'usulan_kegiatan', 
												'popupProgram', 
												'view', 
												'html'));

		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataUsulanKegiatan'])) {
			$this->mrTemplate->AddVar('data_usulankegiatan', 'USULANKEGIATAN_EMPTY', 'YES');
		} else {
			$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
			$encPage = Dispatcher::Instance()->Encrypt($decPage);
			$this->mrTemplate->AddVar('data_usulankegiatan', 'USULANKEGIATAN_EMPTY', 'NO');
			$dataUsulanKegiatan = $data['dataUsulanKegiatan'];

			$label = "Usulan Program";
			$urlDelete = Dispatcher::Instance()->GetUrl(
									'usulan_kegiatan', 
									'deleteUsulanKegiatan', 
									'do', 
									'html');
									
			$urlReturn = Dispatcher::Instance()->GetUrl(
									'usulan_kegiatan', 
									'usulanKegiatan', 
									'view', 
									'html');
									
			Messenger::Instance()->Send(
								'confirm', 
								'confirmDelete', 
								'do', 
								'html', 
								array($label, $urlDelete, $urlReturn),
								Messenger::NextRequest);
			$this->mrTemplate->AddVar('content', 'URL_DELETE', 
							Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html'));
							
			for ($i=0; $i<sizeof($dataUsulanKegiatan); $i++) {
				$no = $i+$data['start'];
				$dataUsulanKegiatan[$i]['number'] = $no;
				//if ($no % 2 != 0) $dataUsulanKegiatan[$i]['class_name'] = 'table-common-even';
				if($this->usulankegiatanObj->GetTotalSubUnit($dataUsulanKegiatan[$i]['idunit']) > 0) 
					$dataUsulanKegiatan[$i]['class_name'] = 'table-common-even1';
				else $dataUsulanKegiatan[$i]['class_name'] = '';
				
				if($i == 0) $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);				
				if($i == sizeof($dataUsulanKegiatan)-1) 
					$this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
				
				/**
				if($dataUsulanKegiatan[$i]['idsatker'] == $dataUsulanKegiatan[$i-1]['idsatker']) {
					$dataUsulanKegiatan[$i]['kodesatker']="";
					$dataUsulanKegiatan[$i]['SATKER']="";
				}
				*/
				
				if($dataUsulanKegiatan[$i]['idunit'] == $dataUsulanKegiatan[$i-1]['idunit']) {
					$dataUsulanKegiatan[$i]['kodeunit']="";
					$dataUsulanKegiatan[$i]['unit']="";
				} 
				if($dataUsulanKegiatan[$i]['idunit'] == '-') {
					$dataUsulanKegiatan[$i]['colspan']= ' colspan="3" ';
				} else {
					$dataUsulanKegiatan[$i]['colspan']= '';
				}

				$idEnc = Dispatcher::Instance()->Encrypt($dataUsulanKegiatan[$i]['id']);

				$dataUsulanKegiatan[$i]['url_edit'] = Dispatcher::Instance()->GetUrl(
															'usulan_kegiatan', 
															'inputUsulanKegiatan', 
															'view', 'html') . 
															'&dataId=' . $idEnc . 
															'&page=' . $encPage;
															
				$dataUsulanKegiatan[$i]['url_cetak'] = Dispatcher::Instance()->GetUrl(
															'usulan_kegiatan', 
															'cetakUsulanKegiatan', 
															'view', 'html') . 
															'&dataId=' . $idEnc;
															
				$dataUsulanKegiatan[$i]['url_detil'] = Dispatcher::Instance()->GetUrl(
															'usulan_kegiatan', 
															'detilUsulanKegiatan', 
															'view', 
															'html') . 
															'&kegiatanId=' . $idEnc;

				$this->mrTemplate->AddVars('data_usulankegiatan_item', 
							$dataUsulanKegiatan[$i], 'USULANKEGIATAN_');
				$this->mrTemplate->parseTemplate('data_usulankegiatan_item', 'a');	 
			}
		}
	}
}