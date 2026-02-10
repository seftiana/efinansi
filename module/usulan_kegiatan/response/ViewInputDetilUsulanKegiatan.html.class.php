<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/usulan_kegiatan/business/AppDetilUsulanKegiatan.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewInputDetilUsulanKegiatan extends HtmlResponse {
	var $Data;
	var $Pesan;
	var $Role;
	var $Obj;
	var $encKegiatanId;
	var $decKegiatanId;
	var $encDataId;
	var $decDataId;
	
	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
		'module/usulan_kegiatan/template');
		$this->SetTemplateFile('input_detil_usulan_kegiatan.html');
	}
	
	function ProcessRequest() {
		$_POST = $_POST->AsArray();
		$this->Obj = new AppDetilUsulanKegiatan();

		$this->decDataId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$this->encDataId = Dispatcher::Instance()->Encrypt($this->decDataId);
		$this->decKegiatanId = Dispatcher::Instance()->Decrypt($_GET['kegiatanId']);
		$this->encKegiatanId = Dispatcher::Instance()->Encrypt($this->decKegiatanId);
		$this->dataKegiatan = $this->Obj->GetDataUsulanKegiatanById($this->decKegiatanId);
		$this->idSatker = Dispatcher::Instance()->Encrypt($this->dataKegiatan['satker']);
		$this->idUnitKerja = Dispatcher::Instance()->Encrypt($this->dataKegiatan['unitkerja']);
		
		//print_r($this->encDataKegiatan);
		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->data = $msg[0][0];
        
		if($_GET['dataId'] != '') {
			$this->data= $this->Obj->GetDataDetilUsulanKegiatanById($this->decDataId);
			$idPrioritas=$this->data['kegdetPrioritasId'];
         	$waktu_pelaksanaan_mulai['selected'] = $this->data['waktu_mulai'];
         	$waktu_pelaksanaan_selesai['selected'] = $this->data['waktu_selesai'];
        }elseif(isset($msg[0][0])){
            $idPrioritas=$this->data['prioritas'];
            $waktu_pelaksanaan_mulai['selected'] = $this->data['waktu_mulai'];
            $waktu_pelaksanaan_selesai['selected'] = $this->data['waktu_selesai'];
		} else {
			$Y = date("Y")+1;
            $waktu_pelaksanaan_mulai['selected'] = date("$Y-m-d");
            $waktu_pelaksanaan_selesai['selected'] = date("$Y-m-d");
        }

      $waktu_pelaksanaan['awal'] = date("Y")-5;
      $waktu_pelaksanaan['akhir'] = date("Y")+5;
      Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'waktu_pelaksanaan_mulai', array($waktu_pelaksanaan_mulai['selected'], $waktu_pelaksanaan['awal'], $waktu_pelaksanaan['akhir'], '', '', 'tanggal'), Messenger::CurrentRequest);

      Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'waktu_pelaksanaan_selesai', array($waktu_pelaksanaan_selesai['selected'], $waktu_pelaksanaan['awal'], $waktu_pelaksanaan['akhir'], '', '', 'tanggal'), Messenger::CurrentRequest);

      //print_r($this->data);
      
		//$return['decDataId'] = $idDec;
	
	$getPrioritas = $this->Obj->GetComboPrioritas(); 
		Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'prioritas', 
        array('prioritas',$getPrioritas,$idPrioritas,'false',''), Messenger::CurrentRequest);
		return $return;
	}

	function ParseTemplate($data = NULL) {
		if ($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
		}
		//$dataDetilUsulanKegiatan = $data['dataDetilUsulanKegiatan'];
		
		$this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN_LABEL', $this->dataKegiatan['tahun_anggaran_label']);
		$this->mrTemplate->AddVar('content', 'SATKER_LABEL', $this->dataKegiatan['satker_label']);
		$this->mrTemplate->AddVar('content', 'UNITKERJA_LABEL', $this->dataKegiatan['unitkerja_label']);
		$this->mrTemplate->AddVar('content', 'PROGRAM_LABEL', $this->dataKegiatan['program_label']);
		//add
		$this->mrTemplate->AddVar('content', 'PROGRAM', $this->dataKegiatan['program']);

        $this->mrTemplate->AddVar('content', 'IK_ID', $this->data['ik_id']);
        $this->mrTemplate->AddVar('content', 'IK_NAMA', $this->data['ik_nama']);
		$this->mrTemplate->AddVar('content', 'SUBPROGRAM', $this->data['subprogram']);
		$this->mrTemplate->AddVar('content', 'SUBPROGRAM_LABEL', $this->data['subprogram_label']);
		$this->mrTemplate->AddVar('content', 'DESKRIPSI', $this->data['deskripsi']);
		$this->mrTemplate->AddVar('content', 'CATATAN', $this->data['catatan']);
		$this->mrTemplate->AddVar('content', 'URL_POPUP_SUBPROGRAM', 
								Dispatcher::Instance()->GetUrl(
										'usulan_kegiatan', 
										'popupSubProgram', 
										'view', 
										'html') . 
										'&kegiatanId=' . $this->encKegiatanId . 
										'&programId=' . 
										Dispatcher::Instance()->Encrypt($this->dataKegiatan['program']));
		
		//popup list kegiatan
		//----- di ubah, isi idUnitKerja= adalah program id (khusus menampilkan popup saja)
		//( penyesuaian variable)
		/*
		$this->mrTemplate->AddVar('content', 'URL_POPUP_KEGIATANREF', Dispatcher::Instance()->GetUrl('usulan_kegiatan', 'popupKegiatanRef', 'view', 'html'). '&idSatker=' . $this->idSatker . '&idUnitKerja=' . $this->dataKegiatan['program']);//$this->idUnitKerja); berdasarkan program yang dipilih
		//----- end
		*/
		
		$this->mrTemplate->AddVar('content', 'URL_POPUP_KEGIATANREF', 
								Dispatcher::Instance()->GetUrl(
										'usulan_kegiatan', 
										'popupKegiatanRef', 
										'view', 
										'html'). 
										'&idSatker=' . $this->idSatker . 
										'&idUnitKerja=' . $this->idUnitKerja.
										'&idProgram='.$this->dataKegiatan['program']);
										 
		/**/
		$this->mrTemplate->AddVar('content', 'URL_POPUP_IKK', 
								Dispatcher::Instance()->GetUrl(
									'usulan_kegiatan', 
									'popupIkk', 
									'view', 
									'html'));
									
		$this->mrTemplate->AddVar('content', 'URL_POPUP_IKU', 
								Dispatcher::Instance()->GetUrl(
									'usulan_kegiatan', 
									'popupIku', 
									'view', 
									'html'));
									
		$this->mrTemplate->AddVar('content', 'URL_POPUP_OUTPUT_RKAKL', 
								Dispatcher::Instance()->GetUrl(
									'usulan_kegiatan', 
									'popupOutput', 
									'view', 
									'html'));
		
		$this->mrTemplate->AddVar('content', 'URL_POPUP_TUPOKSI', 
								Dispatcher::Instance()->GetUrl(
									'usulan_kegiatan', 
									'popupTupoksi', 
									'view', 
									'html'));
				
      $this->mrTemplate->AddVar('content', 'KEGIATANREF', $this->data['kegiatanref']);
		$this->mrTemplate->AddVar('content', 'KEGIATANREF_LABEL', $this->data['kegiatanref_label']);
		$this->mrTemplate->AddVar('content', 'IKK', $this->data['ikk']);
		$this->mrTemplate->AddVar('content', 'IKK_LABEL', $this->data['ikk_nama']);
		$this->mrTemplate->AddVar('content', 'IKU', $this->data['iku_id']);
		$this->mrTemplate->AddVar('content', 'IKU_LABEL', $this->data['iku_nama']);
		$this->mrTemplate->AddVar('content', 'OUTPUT_RKAKL', $this->data['output_id']);
		$this->mrTemplate->AddVar('content', 'OUTPUT_RKAKL_LABEL', $this->data['output_nama']);
		$this->mrTemplate->AddVar('content', 'OUTPUT', $this->data['output']);
		$this->mrTemplate->AddVar('content', 'MASTUK', $this->data['kegdetMasTUK']);
		$this->mrTemplate->AddVar('content', 'MASTK', $this->data['kegdetMasTk']);
		$this->mrTemplate->AddVar('content', 'KELTUK', $this->data['kegdetKelTUK']);
		$this->mrTemplate->AddVar('content', 'KELTK', $this->data['kegdetKelTk']);
		$this->mrTemplate->AddVar('content', 'TUPOKSI_ID', $this->data['tupoksi_id']);
		$this->mrTemplate->AddVar('content', 'TUPOKSI_NAMA', $this->data['tupoksi_nama']);

      if($this->data['jenis'] == 'Pengembangan') {
		   $this->mrTemplate->AddVar('content', 'DISPLAY_MULAI', "");
		   $this->mrTemplate->AddVar('content', 'DISPLAY_SELESAI', "");
		   $this->mrTemplate->AddVar('content', 'DISPLAY_OUTPUT', "");
      } else {
		   $this->mrTemplate->AddVar('content', 'DISPLAY_MULAI', "");
		   $this->mrTemplate->AddVar('content', 'DISPLAY_SELESAI', "");
		   $this->mrTemplate->AddVar('content', 'DISPLAY_OUTPUT', "");
      }

		if ($_GET['dataId']=='') {
			$url="addDetilUsulanKegiatan";
			$tambah="Tambah";
		} else {
			$url="updateDetilUsulanKegiatan";
			$tambah="Ubah";
		}

		$this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('usulan_kegiatan', $url, 'do', 'html') . '&kegiatanId=' . $this->encKegiatanId . "&dataId=" . $this->decDataId);
		$this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
		//$this->mrTemplate->AddVar('content', 'DATAID', $this->decDataId);
		//$this->mrTemplate->AddVar('content', 'PAGE', Dispatcher::Instance()->Decrypt($_GET['page']));
	}
}
?>
