<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/penerimaan_map_rkakl/business/PenerimaanMap.class.php';

class ViewPenerimaanMapRkakl extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/penerimaan_map_rkakl/template');
		$this->SetTemplateFile('view_penerimaan_map_rkakl.html');
	}
	
	function ProcessRequest() {
		$Obj = new PenerimaanMap();
      
	//view
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataPenerimaanMAP = $Obj->getData($startRec, $itemViewed);
		$totalData = $Obj->GetCountData();

		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&mulai_day=' . Dispatcher::Instance()->Encrypt($decMulaiTanggal) . '&mulai_mon=' . Dispatcher::Instance()->Encrypt($decMulaiBulan) . '&mulai_year=' . Dispatcher::Instance()->Encrypt($decMulaiTahun) . '&selesai_day=' . Dispatcher::Instance()->Encrypt($decSelesaiTanggal) . '&selesai_mon=' . Dispatcher::Instance()->Encrypt($decSelesaiBulan) . '&selesai_year=' . Dispatcher::Instance()->Encrypt($decSelesaiTahun) . '&cari=' . Dispatcher::Instance()->Encrypt(1));

		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage), Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['dataPenerimaanMAP'] = $dataPenerimaanMAP;
		$return['start'] = $startRec+1;

      return $return;
	}

	function ParseTemplate($data = NULL) {
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('penerimaan_map_rkakl', 'penerimaanMapRkakl', 'view', 'html'));
		
      if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataPenerimaanMAP'])) {
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
         
         $dataPenerimaanMAP = $data['dataPenerimaanMAP'];
			for ($i=0; $i<sizeof($dataPenerimaanMAP); $i++) {
            $idEnc = Dispatcher::Instance()->Encrypt($dataPenerimaanMAP[$i]['id']);  
                        
				$no = $i+$data['start'];
				$dataPenerimaanMAP[$i]['number'] = $no;
				if($i == 0) $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);				
				if($i == sizeof($dataPenerimaanMAP)-1) $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
				if ($no % 2 != 0) $dataPenerimaanMAP[$i]['class_name'] = 'table-common-even';
				else $dataPenerimaanMAP[$i]['class_name'] = '';

            $dataPenerimaanMAP[$i]['nominal_label'] = number_format($dataPenerimaanMAP[$i]['nominal'], 2, ',', '.');
				$dataPenerimaanMAP[$i]['url_detil'] = Dispatcher::Instance()->GetUrl('penerimaan_map_rkakl', 'detilPenerimaanMapRkakl', 'view', 'html') . '&dataId=' . $idEnc;
            $dataPenerimaanMAP[$i]['url_cetak_rtf'] = Dispatcher::Instance()->GetUrl('penerimaan_map_rkakl', 'rtfSSBP', 'print', 'rtf') . '&dataId=' . $idEnc;

				$this->mrTemplate->AddVars('data_item', $dataPenerimaanMAP[$i], 'DATA_');
				$this->mrTemplate->parseTemplate('data_item', 'a');	 
			}
         
		}
	}
}
?>
