<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/transaksi_penyusutan/business/AppTransaksiPenyusutanAsper.class.php';

class ViewPopupPenyusutanKib extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/transaksi_penyusutan/template');
		$this->SetTemplateFile('view_popup_penyusutan_kib.html');
	}

    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

	function ProcessRequest() {
		$Obj = new AppTransaksiPenyusutanAsper();
      $_GET = $_GET->AsArray();
		if(isset($_POST['kib'])) {
			$kib_id = $_POST['kib'];
		} elseif(isset($_GET['kib'])) {
			$kib_id = Dispatcher::Instance()->Decrypt($_GET['kib']);
		} else {
			$kib_id = '';
		}


      $itemViewed = 20;
      $currPage = 1;
      $startRec = 0 ;
      if(isset($_GET['page'])) {
         $currPage = (string)$_GET['page'];
         $startRec =($currPage-1) * $itemViewed;
      }
      $data = $Obj->GetDetailPenyusutan($kib_id, $startRec, $itemViewed);
      $totalData = $Obj->GetCount();
      $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType .  '&kib=' . Dispatcher::Instance()->Encrypt($kib_id) . '&cari=' . Dispatcher::Instance()->Encrypt(1));
		$dest = "popup-subcontent";
      Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage, $dest), Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['data'] = $data;
		$return['start'] = $startRec+1;
		$return['search']['keyword'] = $keyword;
		$return['decUnitkerja'] = $decUnitkerja;
		return $return;
	}

	function ParseTemplate($data = NULL) {
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'KEYWORD', $search['keyword']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('transaksi_penyusutan', 'PopupKib', 'view', 'html'));
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['data'])) {
			$this->mrTemplate->AddVar('data_kib', 'KIB_EMPTY', 'YES');
		} else {;
			$this->mrTemplate->AddVar('data_kib', 'KIB_EMPTY', 'NO');
			$kib = $data['data'];
			for ($i=0; $i<sizeof($kib); $i++) {
				$no = $i+$data['start'];
            $kib[$i]['number'] = $no;
            $kib[$i]['nilai_perolehan'] = number_format($kib[$i]['nilai_perolehan'], 2, ',', '.');
            $kib[$i]['nilai_penyusutan_rp'] = number_format($kib[$i]['nilai_penyusutan'], 2, ',', '.');
				if ($no % 2 == 1) {
					$kib[$i]['class_name'] = 'table-common-even';
				}
				$this->mrTemplate->AddVars('data_kib_item', $kib[$i], '');
				$this->mrTemplate->parseTemplate('data_kib_item', 'a');
			}
		}
	}
}
?>