<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
        'module/usulan_kegiatan/business/AppPopupKegiatanRef.class.php';

class ViewPopupKegiatanRef extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
                'module/usulan_kegiatan/template');
		$this->SetTemplateFile('view_popup_kegiatan_ref.html');
	}
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
	
	function ProcessRequest() {
		$popupKegiatanRefObj = new AppPopupKegiatanRef();
		$POST = $_POST->AsArray();
		if(!empty($POST)) {
			$kegiatanref = $POST['kegiatanref'];
            $ik = $_POST['ik'];
			$kode = Dispatcher::Instance()->Decrypt($_GET['idUnitKerja']);
			$satKer = Dispatcher::Instance()->Decrypt($_GET['idSatker']);
			$programId =Dispatcher::Instance()->Decrypt($_GET['idProgram']);
		} elseif(isset($_GET['cari'])) {
			$kegiatanref = Dispatcher::Instance()->Decrypt($_GET['kegiatanref']);
            $ik = Dispatcher::Instance()->Decrypt($_GET['ik']);
			$kode = Dispatcher::Instance()->Decrypt($_GET['idUnitKerja']);
			$satKer = Dispatcher::Instance()->Decrypt($_GET['idSatker']);
			$programId =Dispatcher::Instance()->Decrypt($_GET['idProgram']);
		} else {
			$kegiatanref = Dispatcher::Instance()->Decrypt($_GET['kegiatanref']);
            $ik = Dispatcher::Instance()->Decrypt($_GET['ik']);
			$kode = Dispatcher::Instance()->Decrypt($_GET['idUnitKerja']);
			$satKer = Dispatcher::Instance()->Decrypt($_GET['idSatker']);
			$programId =Dispatcher::Instance()->Decrypt($_GET['idProgram']);
		}
		//$this->decSubProgramId = Dispatcher::Instance()->Decrypt($_GET['subprogramId']);
		//$this->encSubProgramId = Dispatcher::Instance()->Encrypt($this->decSubProgramId);

		//$totalData = $popupKegiatanRefObj->GetCountDataKegiatanRef($this->decSubProgramId, $kegiatanref, $kode);
		$totalData = $popupKegiatanRefObj->GetCountDataKegiatanRef($kegiatanref);

		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		/**
        $dataKegiatanRef = $popupKegiatanRefObj->getDataKegiatanRef(
                                                            $startRec, 
                                                            $itemViewed, 
                                                            $this->decSubProgramId, 
                                                            $kegiatanref, 
                                                            $kode);
        */
		$dataKegiatanRef = $popupKegiatanRefObj->getDataKegiatanRef(
                                                            $startRec, 
                                                            $itemViewed,
                                                            $kegiatanref,
                                                            $programId,
                                                            $ik);//, $kode);
		$url = Dispatcher::Instance()->GetUrl(
                                                Dispatcher::Instance()->mModule, 
                                                Dispatcher::Instance()->mSubModule, 
                                                Dispatcher::Instance()->mAction, 
                                                Dispatcher::Instance()->mType .
                                                '&kegiatanref=' . Dispatcher::Instance()->Encrypt($kegiatanref) .
                                                '&ik=' . Dispatcher::Instance()->Encrypt($ik). 
                                                '&idUnitKerja=' . Dispatcher::Instance()->Encrypt($kode). 
                                                '&idSatKer=' . Dispatcher::Instance()->Encrypt($satKer). 
                                                '&idProgram=' . Dispatcher::Instance()->Encrypt($programId). 
                                                '&cari=' . Dispatcher::Instance()->Encrypt(1));

		$dest = "popup-subcontent";
		Messenger::Instance()->SendToComponent(
                                                'paging', 
                                                'Paging', 
                                                'view', 
                                                'html', 
                                                'paging_top', 
                                                array(
                                                        $itemViewed,
                                                        $totalData, 
                                                        $url, 
                                                        $currPage, 
                                                        $dest), 
                                                Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['dataKegiatanRef'] = $dataKegiatanRef;
		$return['start'] = $startRec+1;

		$return['search']['kegiatanref'] = $kegiatanref;
		$return['search']['kode'] = $kode;
		$return['search']['idSatKer'] = $satKer;
        $return['search']['idProgram'] = $programId;
        $return['search']['ik'] = $ik;
		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$search = $data['search'];
        $this->mrTemplate->AddVar('content', 'IK', $search['ik']);
		$this->mrTemplate->AddVar('content', 'KEGIATANREF', $search['kegiatanref']);
		$this->mrTemplate->AddVar('content', 'KODE', $search['kode']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
                        Dispatcher::Instance()->GetUrl(
                                                        'usulan_kegiatan', 
                                                        'popupKegiatanRef', 
                                                        'view', 
                                                        'html'). 
                                                        '&ik=' . $data['search']['ik'] .
                                                        '&kegiatanref=' . $data['search']['kegiatanref'] .
                                                        '&idProgram=' .$data['search']['idProgram'] .
                                                        '&idSatker=' . $data['search']['idSatKer'] . 
                                                        '&idUnitKerja=' . $data['search']['kode']);
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataKegiatanRef'])) {
			$this->mrTemplate->AddVar('data_kegiatanref', 'KEGIATANREF_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data_kegiatanref', 'KEGIATANREF_EMPTY', 'NO');
			$dataKegiatanRef = $data['dataKegiatanRef'];
			for($i=0;$i<sizeof($dataKegiatanRef);$i++) {
				$dataKegiatanRef[$i]['enc_kegiatanref_id'] = Dispatcher::Instance()->Encrypt(
                                                                                    $dataKegiatanRef[$i]['id']);
				$dataKegiatanRef[$i]['enc_kegiatanref_nama'] = Dispatcher::Instance()->Encrypt(
                                                                                    $dataKegiatanRef[$i]['nama']);
			}

			for ($i=0; $i<sizeof($dataKegiatanRef); $i++) {
				
				$no = $i+$data['start'];
				$dataKegiatanRef[$i]['number'] = $no;
				if ($no % 2 != 0) $dataKegiatanRef[$i]['class_name'] = 'table-common-even';
				else $dataKegiatanRef[$i]['class_name'] = '';

				$this->mrTemplate->AddVars('data_kegiatanref_item', $dataKegiatanRef[$i], 'KEGIATANREF_');
				$this->mrTemplate->parseTemplate('data_kegiatanref_item', 'a');	 
			}
		}
	}
}
?>
