<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/adjustment_pengeluaran/business/AppInputAdjustmentPengeluaran.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewInputAdjustmentPengeluaran extends HtmlResponse {

	var $Pesan;
	var $decDataId;
	var $encDataId;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/adjustment_pengeluaran/template');
		$this->SetTemplateFile('view_input_adjustment_pengeluaran.html');
	}

	function ProcessRequest() {

		$this->decDataId = Dispatcher::Instance()->Decrypt($_GET['dataId']);
		$this->encDataId = Dispatcher::Instance()->Encrypt($this->decDataId);

		$Obj = new AppInputAdjustmentPengeluaran();
		$info = $Obj->GetInformasi(Dispatcher::Instance()->Decrypt($_GET['dataId']));
		if($info['is_unit_kerja'] == false) {
			$info['unitkerja_label'] = $info['satker_nama'] . "/ " . $info['unit_kerja_nama'];
		} else {
			$info['unitkerja_label'] = $info['satker_nama'];
		}

	//view
		$totalData = $Obj->GetCountData($this->decDataId);
		$itemViewed = 40;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$data = $Obj->GetData($this->decDataId);
		//print_r($data);
		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&dataId=' . $this->encDataId . '&cari=' . Dispatcher::Instance()->Encrypt(1));

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		//$return['role_name'] = $role['role_name'];
		$return['info'] = $info;
		//$return['unitkerja'] = $unitkerja;
		$return['data'] = $data;
		//print_r($dataDetilApproval);
		$return['start'] = $startRec+1;
		return $return;
	}

	function tambahNol($str="0", $jml_char=2) {
		while(strlen($str) < $jml_char) {
			$str = "0" . $str;
		}
		return $str;
	}

	function ParseTemplate($data = NULL) {
		//$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('adjustment_pengeluaran', 'inputAdjustmentPengeluaran', 'do', 'html') . "&dataId=" . $this->encDataId);
		$this->mrTemplate->AddVar('content', 'URL_KEMBALI', Dispatcher::Instance()->GetUrl('adjustment_pengeluaran', 'AdjustmentPengeluaran', 'view', 'html'));
		$this->mrTemplate->AddVar('content', 'URL_REFRESH', Dispatcher::Instance()->GetUrl('adjustment_pengeluaran', 'inputAdjustmentPengeluaran', 'view', 'html') . '&dataId=' . $this->encDataId);

		$this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN_LABEL', $data['info']['tahun_anggaran_label']);
		$this->mrTemplate->AddVar('content', 'UNITKERJA_LABEL', $data['info']['unitkerja_label']);
		$this->mrTemplate->AddVar('content', 'PROGRAM_LABEL', $data['info']['program_label']);
		$this->mrTemplate->AddVar('content', 'KEGIATAN_LABEL', $data['info']['kegiatan_label']);
		$this->mrTemplate->AddVar('content', 'SUBKEGIATAN_LABEL', $data['info']['subkegiatan_label']);

		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['data'])) {
			$this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
			$data_detil = $data['data'];
			for ($i=0; $i<sizeof($data_detil); $i++) {
				$no = $i+$data['start'];
        $data_detil[$i]['number'] = $no;
        $data_detil[$i]['format_nominal_usulan'] = number_format($data_detil[$i]['nominal_usulan'], 2, ',', '.');
        $data_detil[$i]['format_jumlah_usulan'] = number_format($data_detil[$i]['jumlah_usulan'], 2, ',', '.');
        $data_detil[$i]['format_jumlah_setuju'] = number_format($data_detil[$i]['jumlah_setuju'], 2, ',', '.');
        $data_detil[$i]['nominal_setuju'] = number_format($data_detil[$i]['nominal_setuju'], 0, '', '');
        $data_detil[$i]['class_name'] = 'table-common-even';
        $this->mrTemplate->SetAttribute('cekbok', 'visibility', 'visible');
        $this->mrTemplate->AddVar('cekbok', 'DATA_ID', $data_detil[$i]['id']);
		$this->mrTemplate->AddVar('cekbok', 'DATA_NUMBER', $no);
		$this->mrTemplate->AddVar('cekbok', 'DATA_NAMA', $data_detil[$i]['nama']);
        $this->mrTemplate->AddVar('nominal', 'APPROVAL', 'SUDAH');
        $this->mrTemplate->AddVar('nominal', 'DATA_FORMAT_NOMINAL_SETUJU', number_format($data_detil[$i]['nominal_setuju'], 2, ',', '.'));
        $this->mrTemplate->AddVar('nominal', 'DATA_NOMINAL_SETUJU', $data_detil[$i]['nominal_setuju']);
		$this->mrTemplate->AddVar('nominal', 'DATA_FORMULA', $data_detil[$i]['hasil_formula']);
        $this->mrTemplate->AddVar('nominal', 'DATA_ID', $data_detil[$i]['id']);
        $this->mrTemplate->AddVar('satuan', 'DATA_ID', $data_detil[$i]['id']);
        $this->mrTemplate->AddVar('satuan', 'APPROVAL', 'SUDAH');
        $this->mrTemplate->AddVar('satuan', 'DATA_FORMAT_SATUAN_SETUJU', $data_detil[$i]['satuan_setuju']);


				if($i == 0) $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);
				if($i == sizeof($data_detil)-1) $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);

				//$idEnc = Dispatcher::Instance()->Encrypt($data_detil[$i]['id']);
				//$data_detil[$i]['url_edit'] = Dispatcher::Instance()->GetUrl('usulan_kegiatan', 'inputUsulanKegiatan', 'view', 'html') . '&dataId=' . $idEnc . '&page=' . $encPage;

				//$programIdEnc = Dispatcher::Instance()->Encrypt($dataUsulanKegiatan[$i]['program_id']);
				//$data_detil[$i]['url_detil'] = Dispatcher::Instance()->GetUrl('usulan_kegiatan', 'detilUsulanKegiatan', 'view', 'html') . '&kegiatanId=' . $idEnc;

				$this->mrTemplate->AddVar('data_items','BYK_DATA',sizeof($data_detil));
            $this->mrTemplate->AddVars('data_items', $data_detil[$i], 'DATA_');
				$this->mrTemplate->parseTemplate('data_items', 'a');
			}

			$total_disetujui = 0;
         for ($j=0; $j<sizeof($data_detil); $j++){
            $total_disetujui = $total_disetujui + $data_detil[$j]['jumlah_setuju'];

         }
         $this->mrTemplate->AddVar('content','TOTAL_SETUJU',number_format($total_disetujui,2,',','.'));
		}
	}
}
?>