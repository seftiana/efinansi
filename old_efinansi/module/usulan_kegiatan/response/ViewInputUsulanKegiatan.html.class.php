<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/usulan_kegiatan/business/AppUsulanKegiatan.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewInputUsulanKegiatan extends HtmlResponse {
	var $Data;
	var $Pesan;
	var $Css;
	var $Role;
	var $usulanKegiatanObj;
	
	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 'module/usulan_kegiatan/template');
		$this->SetTemplateFile('input_usulan_kegiatan.html');
	}
	
	function ProcessRequest() {
		$_POST = $_POST->AsArray();
		$idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$this->usulanKegiatanObj = new AppUsulanKegiatan();
		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan    = $msg[0][1];
		$this->Data     = $msg[0][0];
		$this->Css      = $msg[0][2];

		$userUnitKerja = new UserUnitKerja();
		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		//$this->Role = $userUnitKerja->GetRoleUser($userId);
		$unit = $userUnitKerja->GetUnitKerjaUser($userId);
		$tahun_anggaran = $this->usulanKegiatanObj->GetTahunAnggaranAktif();
		if($_REQUEST['dataId'] =='') {
			/**
			 * if($this->Role['role_name'] == "Administrator") {
	 		 */
	 		 	$return['is_edit'] ='NO';
	 		 	$this->Data = $_POST;
				//tahun anggaran dari $_POST dan $_GET, unitkerja dan sub-unit dari $_POST dan $_GET
				//$tahun_anggaran = $this->usulanKegiatanObj->GetTahunAnggaranAktif();
				$this->Data['tahun_anggaran'] = $tahun_anggaran['id'];
				$this->Data['tahun_anggaran_label'] = $tahun_anggaran['name'];
				/**
				$arr_tahun_anggaran = $this->usulanKegiatanObj->GetComboTahunAnggaran();
				Messenger::Instance()->SendToComponent(
									'combobox', 
									'Combobox', 
									'view', 
									'html', 
									'tahun_anggaran', 
									array(
											'tahun_anggaran', 
											$arr_tahun_anggaran, 
											$this->Data['tahun_anggaran'], '-', 
											' style="width:200px;" id="tahun_anggaran"'), 
									Messenger::CurrentRequest);
				*/
				
				/**
				 * added
				 * since 5 Januari 2012
				 */
				$this->Data['satker'] = $unit['unit_kerja_parent_id'];
				$this->Data['satker_label'] = $unit['satker_nama'];
				$this->Data['unitkerja'] = $unit['unit_kerja_id'];
				$this->Data['unitkerja_label'] = $unit['unit_kerja_nama'];
				/**
				 * end
				 */
			
			/**	
			} elseif($this->Role['role_name'] == "OperatorUnit") {
				$this->Data = $_POST;
				//tahun anggaran dan unitkerja dari database, subunit dari $_POST dan $_GET
				$tahun_anggaran = $this->usulanKegiatanObj->GetTahunAnggaranAktif();
				$this->Data['tahun_anggaran'] = $tahun_anggaran['id'];
				$this->Data['tahun_anggaran_label'] = $tahun_anggaran['name'];
				
				$satker = $userUnitKerja->GetSatkerUnitKerjaUser($userId);
				$this->Data['satker'] = $satker['satker_id'];
				$this->Data['satker_label'] = $satker['satker_nama'];
				$this->Data['unitkerja'] = $satker['unit_kerja_id'];
				$this->Data['unitkerja_label'] = $satker['unit_kerja_nama'];
			} elseif($this->Role['role_name'] == "OperatorSubUnit") {
				$this->Data = $_POST;
				//tahun anggaran, unitkerja, dan subunit dari database
				$tahun_anggaran = $this->usulanKegiatanObj->GetTahunAnggaranAktif();
				$this->Data['tahun_anggaran'] = $tahun_anggaran['id'];
				$this->Data['tahun_anggaran_label'] = $tahun_anggaran['name'];
				$satker = $userUnitKerja->GetSatkerUnitKerjaUser($userId);
				$this->Data['satker'] = $satker['satker_id'];
				$this->Data['satker_label'] = $satker['satker_nama'];
				$this->Data['unitkerja'] = $satker['unit_kerja_id'];
				$this->Data['unitkerja_label'] = $satker['unit_kerja_nama'];
			}
			*/
		} else {
			//edit data
			$return['is_edit']='YA';
			$this->Data= $this->usulanKegiatanObj->GetDataUsulanKegiatanById($idDec);
			
			$satker_pimpinan = $this->usulanKegiatanObj->GetSatkerPimpinanById($idDec);
			$this->Data['satker_pimpinan_label'] = $satker_pimpinan['satker_pimpinan_label'];
			$this->Data['satker_pimpinan'] = $satker_pimpinan['satker_pimpinan'];
			
			$unitkerja_pimpinan = $this->usulanKegiatanObj->GetUnitKerjaPimpinanById($idDec);
			$this->Data['unitkerja_pimpinan_label'] = $unitkerja_pimpinan['unitkerja_pimpinan_label'];
			$this->Data['unitkerja_pimpinan'] = $unitkerja_pimpinan['unitkerja_pimpinan'];
		}
		
		/**
		 * tahun anggaran
		 */
		$arr_tahun_anggaran = $this->usulanKegiatanObj->GetComboTahunAnggaran();
				Messenger::Instance()->SendToComponent(
									'combobox', 
									'Combobox', 
									'view', 
									'html', 
									'tahun_anggaran', 
									array(
											'tahun_anggaran', 
											$arr_tahun_anggaran, 
											$this->Data['tahun_anggaran'], '-', 
											' style="width:200px;" id="tahun_anggaran"'), 
									Messenger::CurrentRequest);
		//print_r($this->Data);
		/*
		if(empty($_POST))
			$this->Data= $this->usulanKegiatanObj->GetDataUsulanKegiatanById($idDec);
		*/

		$return['decDataId'] = $idDec;
		$return['unitUserKerjaId']=$unit['unit_kerja_id'];
		//$return['dataUsulanKegiatan'] = $dataUsulanKegiatan;
		return $return;
	}

	function ParseTemplate($data = NULL) {
		if ($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS', $this->Css);
		}
		$dataUsulanKegiatan = $data['dataUsulanKegiatan'];
		$this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
		
		//if($_REQUEST['dataId']=='') {
			//add data
			/**
			 * if($this->Role['role_name'] == "Administrator") {
		 	 */
		 	 
		 	 	/**
		 	 	 * cek jumlah anak dari unit yang aktif
		 	 	 */
 	 	 		if($this->usulanKegiatanObj->GetTotalSubUnit($data['unitUserKerjaId']) > 0 ){
	 	 			 $this->mrTemplate->AddVar('is_parent', 'IS_PARENT','YA');
		 	 	 } else {
		 	 	 	$this->mrTemplate->AddVar('is_parent', 'IS_PARENT','NO');
		 	 	 }
				//PAK ADMIN, tahun_anggaran:combo, unitkerja-subunitkerja:textbox
			//	$this->mrTemplate->AddVar('role', 'WHOAMI', 'ADMINISTRATOR');
				$this->mrTemplate->AddVar('is_parent', 'URL_POPUP_UNITKERJA', 
						Dispatcher::Instance()->GetUrl('usulan_kegiatan', 'popupSatker', 'view', 'html'));
				//$this->mrTemplate->AddVar('content', 'URL_POPUP_UNITKERJA', 
				//		Dispatcher::Instance()->GetUrl('usulan_kegiatan', 'Unitkerja', 'popup', 'html'));
			/**
			} elseif($this->Role['role_name'] == "OperatorUnit") {
				//OP UNIT, tahun_anggaran:label, unitkerja:label, subunitkerja:textbox
				$this->mrTemplate->AddVar('role', 'WHOAMI', 'OPERATORUNIT');
				$this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN', $this->Data['tahun_anggaran']);
				$this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN_LABEL', 
						$this->Data['tahun_anggaran_label']);
				$this->mrTemplate->AddVar('role', 'URL_POPUP_SATKER', 
						Dispatcher::Instance()->GetUrl('usulan_kegiatan', 'popupSatker', 'view', 'html'));
				$this->mrTemplate->AddVar('role', 'URL_POPUP_UNITKERJA', 
						Dispatcher::Instance()->GetUrl('usulan_kegiatan', 'Unitkerja', 'popup', 'html'));
			} elseif($this->Role['role_name'] == "OperatorSubUnit") {
				//echo "asdf";
				//print_r($this->Data);
				//OP SUB UNIT, tahun_anggaran:label, unitkerja:label, subunitkerja:label
				$this->mrTemplate->AddVar('role', 'WHOAMI', 'OPERATORSUBUNIT');
				$this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN', $this->Data['tahun_anggaran']);
				$this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN_LABEL', 
						$this->Data['tahun_anggaran_label']);
			}
			*/
		//}
		
		
		 if($data['is_edit']=='YA'){
			$this->mrTemplate->AddVar('is_edit', 'IS_EDIT','YA');
		} else {
			$this->mrTemplate->AddVar('is_edit', 'IS_EDIT','NO');
		}
			//$this->mrTemplate->AddVar('role', 'WHOAMI', 'ALL_EDIT');
		$this->mrTemplate->AddVar('is_edit', 'TAHUN_ANGGARAN', $this->Data['tahun_anggaran']);
		$this->mrTemplate->AddVar('is_edit', 'TAHUN_ANGGARAN_LABEL',$this->Data['tahun_anggaran_label']);
		
		
		//$this->mrTemplate->AddVar('content', 'SATKER', $this->Data['satker']);
		$this->mrTemplate->AddVar('content', 'UNITKERJA', $this->Data['unitkerja']);
		// ###############Penyatuan Unit dan Sub Unit Kerja ################################
		//$this->mrTemplate->AddVar('is_parent', 'SATKER_LABEL',
		//	(($this->Data['unitkerja_label'] != '-')? $this->Data['unitkerja_label'] :
		//	 $this->Data['satker_label']));
		 // ################################################################################
		$this->mrTemplate->AddVar('is_parent', 'UNITKERJA_LABEL', $this->Data['unitkerja_label']);
		
		$this->mrTemplate->AddVar('content', 'URL_POPUP_SATKER_PIMPINAN', 
				Dispatcher::Instance()->GetUrl('usulan_kegiatan', 'popupSatkerPimpinan', 'view', 'html'));
		
		/**
		$this->mrTemplate->AddVar('content', 'URL_POPUP_UNITKERJA_PIMPINAN', 
				Dispatcher::Instance()->GetUrl(
									'usulan_kegiatan', 
									'popupUnitkerjaPimpinan', 
									'view', 
									'html'));
		*/
		$this->mrTemplate->AddVar('content', 'KEGIATAN_ID', $this->Data['kegiatan_id']);
		$this->mrTemplate->AddVar('content', 'PROGRAM', $this->Data['program']);
		$this->mrTemplate->AddVar('content', 'PROGRAM_LABEL', $this->Data['program_label']);
		$this->mrTemplate->AddVar('content', 'LATARBELAKANG', $this->Data['latarbelakang']);
		$this->mrTemplate->AddVar('content', 'INDIKATOR', $this->Data['indikator']);
		$this->mrTemplate->AddVar('content', 'BASELINE', $this->Data['baseline']);
		$this->mrTemplate->AddVar('content', 'FINAL', $this->Data['final']);
		$this->mrTemplate->AddVar('content', 'NAMA_PIC', $this->Data['nama_pic']);
		$this->mrTemplate->AddVar('content', 'SATKER_PIMPINAN', $this->Data['satker_pimpinan']);
		$this->mrTemplate->AddVar('content', 'SATKER_PIMPINAN_LABEL', 
							$this->Data['satker_pimpinan_label']);
		$this->mrTemplate->AddVar('content', 'UNITKERJA_PIMPINAN', $this->Data['unitkerja_pimpinan']);
		$this->mrTemplate->AddVar('content', 'UNITKERJA_PIMPINAN_LABEL', 
							$this->Data['unitkerja_pimpinan_label']);
		$this->mrTemplate->AddVar('content', 'URL_POPUP_PROGRAM', 
							Dispatcher::Instance()->GetUrl(
										'usulan_kegiatan', 
										'popupProgram', 
										'view', 
										'html'));
		$this->mrTemplate->AddVar('role', 'URL_POPUP_UNITKERJA', 
							Dispatcher::Instance()->GetUrl(
										'usulan_kegiatan', 
										'Unitkerja', 
										'popup', 
										'html'));

		if ($_REQUEST['dataId']=='') {
			$url="addUsulanKegiatan";
			$tambah="Tambah";
		} else {
			$url="updateUsulanKegiatan";
			$tambah="Ubah";
		}

		$this->mrTemplate->AddVar('content', 'URL_ACTION', 
							Dispatcher::Instance()->GetUrl(
											'usulan_kegiatan', 
											$url, 
											'do', 
											'html') . 
											"&dataId=" . 
											Dispatcher::Instance()->Encrypt($data['decDataId']));
		$this->mrTemplate->AddVar('content', 'JUDUL', $tambah);

		$this->mrTemplate->AddVar('content', 'DATAID', Dispatcher::Instance()->Decrypt($_GET['dataId']));
		$this->mrTemplate->AddVar('content', 'PAGE', Dispatcher::Instance()->Decrypt($_GET['page']));
	}
}
?>
