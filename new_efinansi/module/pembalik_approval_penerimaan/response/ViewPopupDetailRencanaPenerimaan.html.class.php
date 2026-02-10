<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/pembalik_approval_penerimaan/business/AppRencanaPenerimaan.class.php';
//require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	//'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewPopupDetailRencanaPenerimaan extends HtmlResponse 
{
	var $Data;
	var $Pesan;
	
	function TemplateModule() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
			'module/pembalik_approval_penerimaan/template');
		$this->SetTemplateFile('view_popup_detail_rencana_penerimaan.html');
	}
	
	function ProcessRequest() 
	{
		$idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$Obj = new AppRencanaPenerimaan();
		//$uukObj = new UserUnitKerja();
		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->Data = $msg[0][0];
		     
		if(isset($_REQUEST['dataId'])) {
			//update
			$data = $Obj->GetDataRencanaPenerimaanById($idDec);
			$status_id = $data[0]['approval'];
			if($status_id =='' || $status_id == 0 || $status_id > 3){
				$status_id = 4;
			}
			$status_label = $Obj->GetStatusApprovalNama($status_id);

  			$return['status_approval_label'] = $status_label['nama'];
			$return['tahun_anggaran'] = $data[0]['tahun_anggaran_id'];
			$return['tahun_anggaran_label'] = $data[0]['tahun_anggaran_label'];
			$return['unitkerja'] = $data[0]['unitkerja_id'];
            $return['unitkerja_label'] =$data[0]['unitkerja_label'];
            /**
			$unitkerja = $uukObj->GetSatkerUnitKerja($data[0]['unitkerja_id']);
			if($unitkerja['is_unit_kerja'] == true) {
				$unitkerja['label'] = $unitkerja['satker_nama'];
			} else {
				$unitkerja['label'] = $unitkerja['satker_nama'] . "/ " . $unitkerja['unit_kerja_nama'];
			}
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
			$return['sumber_dana'] = $data[0]['sumber_dana'];
			$return['sumber_dana_label'] = $data[0]['sumber_dana_label'];
  			$return['note'] = $data[0]['note'];
  			$return['satuan'] = $data[0]['satuan'];
			
			$return['decDataId'] = $idDec;
			$return['dataRencanaPenerimaan'] = $data;
			
		} 
		return $return;
	}

	function ParseTemplate($data = NULL) 
	{
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
											'approval_rencana_penerimaan', 
											$url, 
											'do', 
											'html') . 
											"&dataId=" . Dispatcher::Instance()->Encrypt($data['decDataId']));
											
		$this->mrTemplate->AddVar('content', 'DATAID', Dispatcher::Instance()->Decrypt($_GET['dataId']));
		$this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
		$this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN', $data['tahun_anggaran']);
		$this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN_LABEL', $data['tahun_anggaran_label']);
		$this->mrTemplate->AddVar('content', 'UNITKERJA', $data['unitkerja']);
		$this->mrTemplate->AddVar('content', 'UNITKERJA_LABEL', $data['unitkerja_label']);
		$this->mrTemplate->AddVar('content', 'SUMBER_DANA', $data['sumber_dana']);
		$this->mrTemplate->AddVar('content', 'SUMBER_DANA_LABEL', $data['sumber_dana_label']);
		$this->mrTemplate->AddVar('content', 'STATUS_APPROVAL_LABEL', $data['status_approval_label']);
		$this->mrTemplate->AddVar('content', 'NOTE', $data['note']);
		$this->mrTemplate->AddVar('content', 'SATUAN', $data['satuan']);

		$this->mrTemplate->AddVar('content', 'KODEPENERIMAAN', $data['kodepenerimaan']);
		$this->mrTemplate->AddVar('content', 'KODEPENERIMAAN_LABEL', $data['kodepenerimaan_label']);
		$this->mrTemplate->AddVar('content', 'NAMAPENERIMAAN_LABEL', $data['namapenerimaan_label']);
		$this->mrTemplate->AddVar('content', 'TOTALPENERIMAAN', number_format($data['totalpenerimaan'], 0, ',', '.'));
		
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
			/**
		 * data persen perbulan
		 */
		$this->mrTemplate->AddVar('content', 'PJANUARI', number_format($dataBulan['pjanuari'], 0, ',', '.').'%');
		$this->mrTemplate->AddVar('content', 'PFEBRUARI',number_format( $dataBulan['pfebruari'], 0, ',', '.').'%');
		$this->mrTemplate->AddVar('content', 'PMARET', number_format($dataBulan['pmaret'], 0, ',', '.').'%');
		$this->mrTemplate->AddVar('content', 'PAPRIL',number_format( $dataBulan['papril'], 0, ',', '.').'%');
		$this->mrTemplate->AddVar('content', 'PMEI', number_format($dataBulan['pmei'], 0, ',', '.').'%');
		$this->mrTemplate->AddVar('content', 'PJUNI', number_format($dataBulan['pjuni'], 0, ',', '.').'%');
		$this->mrTemplate->AddVar('content', 'PJULI', number_format($dataBulan['pjuli'], 0, ',', '.').'%');
		$this->mrTemplate->AddVar('content', 'PAGUSTUS', number_format($dataBulan['pagustus'], 0, ',', '.').'%');
		$this->mrTemplate->AddVar('content', 'PSEPTEMBER', number_format($dataBulan['pseptember'], 0, ',', '.').'%');
		$this->mrTemplate->AddVar('content', 'POKTOBER', number_format($dataBulan['poktober'], 0, ',', '.').'%');
		$this->mrTemplate->AddVar('content', 'PNOVEMBER', number_format($dataBulan['pnovember'], 0, ',', '.').'%');
		$this->mrTemplate->AddVar('content', 'PDESEMBER', number_format($dataBulan['pdesember'], 0, ',', '.').'%');
		$this->mrTemplate->AddVar('content', 'TPERSEN', number_format($dataBulan['tpersen'], 0, ',', '.').'%');
		$this->mrTemplate->AddVar('content', 'TNOMINAL', number_format($dataBulan['tnominal'], 0, ',', '.').'%');
	}
}
?>
