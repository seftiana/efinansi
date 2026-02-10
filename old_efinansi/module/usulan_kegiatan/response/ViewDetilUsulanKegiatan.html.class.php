<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/usulan_kegiatan/business/AppDetilUsulanKegiatan.class.php';
//require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewDetilUsulanKegiatan extends HtmlResponse {

	var $Pesan;
	var $Data;
	var $Search;
	var $Obj;
	var $decKegiatanId;
	var $encKegiatanId;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/usulan_kegiatan/template');
		$this->SetTemplateFile('view_detil_usulan_kegiatan.html');
	}

	function ProcessRequest() {
		if($_POST || isset($_GET['cari'])) {
			if(isset($_POST['subprogram'])) {
				$subprogram = $_POST['subprogram'];
				$subprogram_label = $_POST['subprogram_label'];
				$jenis_kegiatan = $_POST['jenis_kegiatan'];
			} elseif(isset($_GET['subprogram'])) {
				$subprogram = Dispatcher::Instance()->Decrypt($_GET['subprogram']);
				$subprogram_label = Dispatcher::Instance()->Decrypt($_GET['subprogram_label']);
				$jenis_kegiatan = Dispatcher::Instance()->Decrypt($_GET['jenis_kegiatan']);
			} else {
				$subprogram = '';
				$subprogram_label = '';
				$jenis_kegiatan = '';
			}
		}

		$this->decKegiatanId = Dispatcher::Instance()->Decrypt($_GET['kegiatanId']);
		$this->encKegiatanId = Dispatcher::Instance()->Encrypt($this->decKegiatanId);

		$this->Obj = new AppDetilUsulanKegiatan();
		$this->dataKegiatan = $this->Obj->GetDataUsulanKegiatanById($this->decKegiatanId);
      //print_r($this->dataKegiatan);

		//$totalData = $this->Obj->GetCountDataDetilUsulanKegiatan($this->decKegiatanId, $subprogram, $jenis_kegiatan);
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$arr_jenis_kegiatan = $this->Obj->GetComboJenisKegiatan();
		Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'jenis_kegiatan', array('jenis_kegiatan', $arr_jenis_kegiatan , $jenis_kegiatan, true, ' style="width:200px;" id="jenis_kegiatan"'), Messenger::CurrentRequest);
		$dataDetilUsulanKegiatan = $this->Obj->GetDataDetilUsulanKegiatan($startRec, $itemViewed, $this->decKegiatanId, $subprogram, $jenis_kegiatan);
      $totalData = $this->Obj->GetCountDataDetilUsulanKegiatan();
		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&kegiatanId=' . Dispatcher::Instance()->Encrypt($this->decKegiatanId) . '&subprogram=' . Dispatcher::Instance()->Encrypt($subprogram) . '&subprogram_label=' . Dispatcher::Instance()->Encrypt($subprogram_label) . '&jenis_kegiatan=' . Dispatcher::Instance()->Encrypt($jenis_kegiatan) . '&cari=' . Dispatcher::Instance()->Encrypt(1));

		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage), Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['start'] = $startRec+1;
		$return['subprogram'] = $subprogram;
		$return['subprogram_label'] = $subprogram_label;
		$return['dataDetilUsulanKegiatan'] = $dataDetilUsulanKegiatan;
		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN_LABEL', $this->dataKegiatan['tahun_anggaran_label']);
		$this->mrTemplate->AddVar('content', 'SATKER_LABEL', $this->dataKegiatan['satker_label']);
		$this->mrTemplate->AddVar('content', 'UNITKERJA_LABEL', $this->dataKegiatan['unitkerja_label']);
		$this->mrTemplate->AddVar('content', 'PROGRAM_LABEL', $this->dataKegiatan['program_label']);
		$this->mrTemplate->AddVar('content', 'SUBPROGRAM', $data['subprogram']);
		$this->mrTemplate->AddVar('content', 'SUBPROGRAM_LABEL', $data['subprogram_label']);
		$this->mrTemplate->AddVar('content', 'JENIS_KEGIATAN_LABEL', $this->dataKegiatan['jenis_kegiatan']);

		$this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('usulan_kegiatan', 'detilUsulanKegiatan', 'view', 'html') . '&kegiatanId=' . $this->encKegiatanId);
		$this->mrTemplate->AddVar('content', 'URL_ADD', Dispatcher::Instance()->GetUrl('usulan_kegiatan', 'inputDetilUsulanKegiatan', 'view', 'html') . '&kegiatanId=' . $this->encKegiatanId);
		$this->mrTemplate->AddVar('content', 'URL_KEGIATAN', Dispatcher::Instance()->GetUrl('usulan_kegiatan', 'usulanKegiatan', 'view', 'html'));
		$this->mrTemplate->AddVar('content', 'URL_POPUP_SUBPROGRAM', Dispatcher::Instance()->GetUrl('usulan_kegiatan', 'popupSubProgram', 'view', 'html') . '&kegiatanId=' . $this->encKegiatanId . '&programId=' . Dispatcher::Instance()->Encrypt($this->dataKegiatan['program']));
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}
		if (empty($data['dataDetilUsulanKegiatan'])) {
			$this->mrTemplate->AddVar('data_detilusulankegiatan', 'DETILUSULANKEGIATAN_EMPTY', 'YES');
		} else {
			$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
			$encPage = Dispatcher::Instance()->Encrypt($decPage);
			$this->mrTemplate->AddVar('data_detilusulankegiatan', 'DETILUSULANKEGIATAN_EMPTY', 'NO');
			$dataDetilUsulanKegiatan = $data['dataDetilUsulanKegiatan'];
			//tombol delete
			$label = "Manajemen Usulan Kegiatan";
			$urlDelete = Dispatcher::Instance()->GetUrl('usulan_kegiatan', 'deleteDetilUsulanKegiatan', 'do', 'html') . '&kegiatanId=' . $this->encKegiatanId;
			$urlReturn = Dispatcher::Instance()->GetUrl('usulan_kegiatan', 'detilUsulanKegiatan', 'view', 'html') . '&kegiatanId=' . $this->encKegiatanId;
			Messenger::Instance()->Send('confirm', 'confirmDelete', 'do', 'html', array($label, $urlDelete, $urlReturn),Messenger::NextRequest);
			$this->mrTemplate->AddVar('content', 'URL_DELETE', Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html') . '&kegiatanId=' . $this->encKegiatanId);
			//end of tombol delete;
			for ($i=0; $i<sizeof($dataDetilUsulanKegiatan); $i++) {
				$no = $i+$data['start'];
				$dataDetilUsulanKegiatan[$i]['number'] = $no;
				if ($no % 2 != 0) $dataDetilUsulanKegiatan[$i]['class_name'] = 'table-common-even';
				else $dataDetilUsulanKegiatan[$i]['class_name'] = '';

				if($dataDetilUsulanKegiatan[$i]['subprogId'] == $dataDetilUsulanKegiatan[$i-1]['subprogId']) {
					$dataDetilUsulanKegiatan[$i]['kegiatan'] = "";
				}
				//if($dataDetilUsulanKegiatan[$i]['kegrefId'] == $dataDetilUsulanKegiatan[$i-1]['kegrefId']) {
				//	$dataDetilUsulanKegiatan[$i]['subkegiatan'] = "";
				//}
				if($dataDetilUsulanKegiatan[$i]['approval'] == "Belum") {
					//$dataDetilUsulanKegiatan[$i]['kegiatan'] = "";
					$this->mrTemplate->AddVar('detilusulankegiatanisapproved', "DETILUSULANKEGIATAN_IS_APPROVED", "BELUM");
					$this->mrTemplate->AddVar('detilusulankegiatanisapproved', "DETILUSULANKEGIATAN_NUMBER", $no);
					$this->mrTemplate->AddVar('detilusulankegiatanisapproved', "DETILUSULANKEGIATAN_ID", $dataDetilUsulanKegiatan[$i]['id']);
					$this->mrTemplate->AddVar('detilusulankegiatanisapproved', "DETILUSULANKEGIATAN_SUBKEGIATAN", $dataDetilUsulanKegiatan[$i]['subkegiatan']);
               $url_edit = Dispatcher::Instance()->GetUrl('usulan_kegiatan', 'inputDetilUsulanKegiatan', 'view', 'html') . '&kegiatanId=' . $this->encKegiatanId . '&dataId=' . Dispatcher::Instance()->Encrypt($dataDetilUsulanKegiatan[$i]['id']);
               $dataDetilUsulanKegiatan[$i]['url_edit'] = '<a class="xhr dest_subcontent-element" href="'.$url_edit.'" title="Ubah"><img src="images/button-edit.gif" alt="Ubah"/></a>';
				} else {
					$dataDetilUsulanKegiatan[$i]['class_name'] = 'table-common-even1';
               $dataDetilUsulanKegiatan[$i]['url_edit'] = '';
				}
				if($i == 0) $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);				
				if($i == sizeof($dataDetilUsulanKegiatan)-1) $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);

				$this->mrTemplate->AddVars('data_detilusulankegiatan_item', $dataDetilUsulanKegiatan[$i], 'DETILUSULANKEGIATAN_');
				$this->mrTemplate->parseTemplate('data_detilusulankegiatan_item', 'a');	 
			}
		}
	}
}
?>
