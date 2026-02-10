<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/transaksi_realisasi_kode_jurnal/business/AppPopupKegiatanUnitKerja.class.php';

class ViewPopupKegiatanUnitKerja extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/transaksi_realisasi_kode_jurnal/template');
		$this->SetTemplateFile('view_popup_kegiatan_unitkerja.html');
	}
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
	
	function ProcessRequest() {
		$Obj = new AppPopupKegiatanUnitKerja();
		$POST = $_POST->AsArray();
		if(!empty($POST)) {
			$kegiatan_detil = $POST['kegiatan_detil'];
		} elseif(isset($_GET['cari'])) {
			$kegiatan_detil = Dispatcher::Instance()->Decrypt($_GET['kegiatan_detil']);
		} else {
			$kegiatan_detil="";
		}
		#$this->decUnitkerjaId = Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
		#$this->encUnitkerjaId = Dispatcher::Instance()->Encrypt($this->decUnitkerjaId);

		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$data_kegiatan_detil = $Obj->getData($startRec, $itemViewed, $kegiatan_detil);
		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&kegiatan_detil=' . Dispatcher::Instance()->Encrypt($kegiatan_detil) . '&cari=' . Dispatcher::Instance()->Encrypt(1));
		$dest = "popup-subcontent";
		$totalData = $Obj->GetCountData();
		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage), Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['data_kegiatan_detil'] = $data_kegiatan_detil;
		$return['start'] = $startRec+1;
		$return['search']['kegiatan_detil'] = $kegiatan_detil;
		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'KEGIATAN_DETIL', $search['kegiatan_detil']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('transaksi_realisasi_kode_jurnal', 'popupKegiatanDetil', 'view', 'html'));
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['data_kegiatan_detil'])) {
			$this->mrTemplate->AddVar('data_kegiatan_detil', 'KEGIATAN_DETIL_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data_kegiatan_detil', 'KEGIATAN_DETIL_EMPTY', 'NO');
			$data_kegiatan_detil = $data['data_kegiatan_detil'];
			for ($i=0; $i<sizeof($data_kegiatan_detil); $i++) {
				$no = $i+$data['start'];
				$data_kegiatan_detil[$i]['number'] = $no;
				if ($no % 2 != 0) $data_kegiatan_detil[$i]['class_name'] = 'table-common-even';
				else $data_kegiatan_detil[$i]['class_name'] = '';
            $data_kegiatan_detil[$i]['nominal_aprove_hidden'] = $data_kegiatan_detil[$i]['nominal_aprove'];   
            $data_kegiatan_detil[$i]['nominal_aprove'] = number_format($data_kegiatan_detil[$i]['nominal_aprove'], 2, ',', '.');
				$this->mrTemplate->AddVars('data_kegiatan_detil_item', $data_kegiatan_detil[$i], 'KEGIATAN_DETIL_');
				$this->mrTemplate->parseTemplate('data_kegiatan_detil_item', 'a');	 
			}
		}
	}
}
?>
