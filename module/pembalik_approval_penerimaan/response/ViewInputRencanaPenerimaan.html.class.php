<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/pembalik_approval_penerimaan/business/AppRencanaPenerimaan.class.php';
//require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
//		'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewInputRencanaPenerimaan extends HtmlResponse {
	var $Data;
	var $Pesan;
	
	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
				'module/pembalik_approval_penerimaan/template');
		$this->SetTemplateFile('input_rencana_penerimaan.html');
	}
	
	function ProcessRequest() {
		$idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$Obj = new AppRencanaPenerimaan();
		//$uukObj = new UserUnitKerja();
		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->Data = $msg[0][0];
		     
		if(isset($_REQUEST['dataId'])) {
			//update
			$data = $Obj->GetDataRencanaPenerimaanById($idDec);
			$arr_approval = $Obj->GetStatusApproval();
			Messenger::Instance()->SendToComponent(
									'combobox', 
									'Combobox', 
									'view', 
									'html', 
									'approval', 
									array(
											'approval', 
											$arr_approval, 
											$data[0]['approval'], 
											false, 
											' style="width:150px;" id="approval"'), 
									Messenger::CurrentRequest);
			
			$return['tahun_anggaran'] = $data[0]['tahun_anggaran_id'];
			$return['tahun_anggaran_label'] = $data[0]['tahun_anggaran_label'];
			$return['unitkerja'] = $data[0]['unitkerja_id'];
			//$unitkerja = $uukObj->GetUnitKerja($data[0]['unitkerja_id']);
			/**
			 * diganti
			if($unitkerja['is_unit_kerja'] == true) {
				$unitkerja['label'] = $unitkerja['satker_nama'];
			} else {
				$unitkerja['label'] = $unitkerja['satker_nama'] . "/ " . $unitkerja['unit_kerja_nama'];
			}
			*/
			
			/**
			 * ganti dengan ini
			 */
			$unitkerja['label'] = $unitkerja['unit_kerja_nama'];
			/**
			 * end
			 */
			$return['kodepenerimaan'] = $data[0]['penerimaan_id'];
			$return['kodepenerimaan_label'] = $data[0]['kode_penerimaan'];
			$return['namapenerimaan_label'] = $data[0]['nama_penerimaan'];
			$return['totalpenerimaan'] = $data[0]['total'];			
			$return['volume'] = $data[0]['volume'];
			$return['tarif'] = $data[0]['tarif'];
			$return['totalterima'] = $data[0]['totalterima'];
			$return['pagu'] = $data[0]['pagu'];
			$return['totalpagu'] = $data[0]['totalpagu'];
			$return['keterangan'] = $data[0]['keterangan'];
			//print_r($this->Data);
			$return['unitkerja_label'] = $data[0]['unitkerja_label'];//$unitkerja['label'];
			$return['decDataId'] = $idDec;
			$return['dataRencanaPenerimaan'] = $data;
		} 
		return $return;
	}

	function ParseTemplate($data = NULL) {
		if ($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
		}
		$dataRencanaPenerimaan = $data['dataRencanaPenerimaan'];
		
		if (isset($_REQUEST['dataId'])) {
			$url="UpdateRencanaPenerimaan";
			$tambah="Detail";
		} /*else {
			$url="AddRencanaPenerimaan";
			$tambah="Tambah";
		}*/

		$this->mrTemplate->AddVar('content', 'URL_ACTION', 
							Dispatcher::Instance()->GetUrl(
											'pembalik_approval_penerimaan', 
											$url, 
											'do', 
											'html') . 
											"&dataId=" . 
											Dispatcher::Instance()->Encrypt($data['decDataId']));
											
		$this->mrTemplate->AddVar('content', 'DATAID', Dispatcher::Instance()->Decrypt($_GET['dataId']));
		$this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
		$this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN', $data['tahun_anggaran']);
		$this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN_LABEL', $data['tahun_anggaran_label']);
		$this->mrTemplate->AddVar('content', 'UNITKERJA', $data['unitkerja']);
		$this->mrTemplate->AddVar('content', 'UNITKERJA_LABEL', $data['unitkerja_label']);

		$this->mrTemplate->AddVar('content', 'KODEPENERIMAAN', $data['kodepenerimaan']);
		$this->mrTemplate->AddVar('content', 'KODEPENERIMAAN_LABEL', $data['kodepenerimaan_label']);
		$this->mrTemplate->AddVar('content', 'NAMAPENERIMAAN_LABEL', $data['namapenerimaan_label']);
		$this->mrTemplate->AddVar('content', 'TOTALPENERIMAAN',
											 number_format($data['totalpenerimaan'], 0, ',', '.'));
		
		$this->mrTemplate->AddVar('content', 'VOLUME', $data['volume']);
		$this->mrTemplate->AddVar('content', 'TARIF', number_format($data['tarif'], 0, ',', '.'));
		$this->mrTemplate->AddVar('content', 'TOTALTERIMA', number_format($data['totalterima'], 0, ',', '.'));
		$this->mrTemplate->AddVar('content', 'PAGU', number_format($data['pagu'], 0, ',', '.'));
		$this->mrTemplate->AddVar('content', 'TOTALPAGU', number_format($data['totalpagu'], 0, ',', '.'));
		$this->mrTemplate->AddVar('content', 'KETERANGAN', $data['keterangan']);
		
		$dataBulan=$data['dataRencanaPenerimaan'][0];			
		$this->mrTemplate->AddVar('content', 'JANUARI', number_format($dataBulan['januari'], 0, ',', '.'));
		$this->mrTemplate->AddVar('content', 'FEBRUARI', number_format($dataBulan['februari'], 0, ',', '.'));
		$this->mrTemplate->AddVar('content', 'MARET', number_format($dataBulan['maret'], 0, ',', '.'));
		$this->mrTemplate->AddVar('content', 'APRIL', number_format($dataBulan['april'], 0, ',', '.'));
		$this->mrTemplate->AddVar('content', 'MEI', number_format($dataBulan['mei'], 0, ',', '.'));
		$this->mrTemplate->AddVar('content', 'JUNI', number_format($dataBulan['juni'], 0, ',', '.'));
		$this->mrTemplate->AddVar('content', 'JULI', number_format($dataBulan['juli'], 0, ',', '.'));
		$this->mrTemplate->AddVar('content', 'AGUSTUS', number_format($dataBulan['agustus'], 0, ',', '.'));
		$this->mrTemplate->AddVar('content', 'SEPTEMBER', number_format($dataBulan['september'], 0, ',', '.'));
		$this->mrTemplate->AddVar('content', 'OKTOBER', number_format($dataBulan['oktober'], 0, ',', '.'));
		$this->mrTemplate->AddVar('content', 'NOVEMBER', number_format($dataBulan['november'], 0, ',', '.'));
		$this->mrTemplate->AddVar('content', 'DESEMBER', number_format($dataBulan['desember'], 0, ',', '.'));
		
		//$this->mrTemplate->AddVar('content', 'PAGE', Dispatcher::Instance()->Decrypt($_GET['page']));
	}
}
?>
