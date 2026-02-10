<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/history_transaksi_pengeluaran/business/AppPopupSkenario.class.php';

class ViewPopupSkenario extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
		'module/history_transaksi_pengeluaran/template');
		$this->SetTemplateFile('view_popup_skenario.html');
	}
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
	
	function ProcessRequest() {
		$Obj = new AppPopupSkenario();

      if(isset($_POST['nama'])) {
         $jenis = $_POST['jenis'];
         $nama = $_POST['nama'];
      } elseif(isset($_GET['nama'])) {
         $jenis = Dispatcher::Instance()->Decrypt($_GET['jenis']);
         $nama = Dispatcher::Instance()->Decrypt($_GET['nama']);
      } else {
         $jenis = '';
         $nama = '';
      }
		
	//view
		$totalData = $Obj->GetCountData($nama);
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataSkenario = $Obj->getData($startRec, $itemViewed, $nama);
		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&jenis=' . Dispatcher::Instance()->Encrypt($jenis) . '&nama=' . Dispatcher::Instance()->Encrypt($kode) . '&cari=' . Dispatcher::Instance()->Encrypt(1));
		$dest = "popup-subcontent";
		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage), Messenger::CurrentRequest);

//combo jenis
		$arrJenis = array(array("id" => "auto", "name" => "Auto" ), array("id" => "manual", "name" => "Manual" ));
		Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'jenis', array('jenis', $arrJenis, $jenis, 'false', "style=\"width:150px;\" id=\"jenis\" onchange=\"cekJenis(this)\""), Messenger::CurrentRequest);

        $msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];
				
		$return['dataSkenario'] = $dataSkenario;
		$return['start'] = $startRec+1;
		$return['search']['nama'] = $nama;
		$return['search']['jenis'] = $jenis;

		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$this->mrTemplate->AddVar('content', 'NAMA', $data['search']['nama']);
      if($data['search']['jenis'] == "auto") {
		   $this->mrTemplate->AddVar('content', 'NAMA_IS_DISABLED', "");
		   $this->mrTemplate->AddVar('content', 'IS_AUTO_DISPLAY', "");
      } else {
		   $this->mrTemplate->AddVar('content', 'NAMA_IS_DISABLED', "disabled=\"disabled\"");
		   $this->mrTemplate->AddVar('content', 'IS_AUTO_DISPLAY', "none");
      }
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('history_transaksi_pengeluaran', 'popupSkenario', 'view', 'html'));
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataSkenario'])) {
			$this->mrTemplate->AddVar('data_skenario', 'SKENARIO_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data_skenario', 'SKENARIO_EMPTY', 'NO');
			$dataSkenario = $data['dataSkenario'];
			for ($i=0; $i<sizeof($dataSkenario); $i++) {
				$no = $i+$data['start'];
				$dataSkenario[$i]['number'] = $no;
				if ($no % 2 != 0) $dataSkenario[$i]['class_name'] = 'table-common-even';
				else $dataSkenario[$i]['class_name'] = '';

				if($i == 0) $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);				
				if($i == sizeof($dataSkenario)-1) $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);

				$this->mrTemplate->AddVars('data_item', $dataSkenario[$i], 'DATA_');
				$this->mrTemplate->parseTemplate('data_item', 'a');	 
			}
		}
	}
}

?>