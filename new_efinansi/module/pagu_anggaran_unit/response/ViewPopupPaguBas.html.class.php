<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/pagu_anggaran_unit/business/PopupPaguBas.class.php';

class ViewPopupPaguBas extends HtmlResponse 
{

	protected $mPesan;
	protected $mPaguBas;

	public function TemplateModule() 
    {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
				'module/pagu_anggaran_unit/template');
		$this->SetTemplateFile('view_popup_pagu_bas.html');
	}
   
    public function TemplateBase() 
    {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 
	  			'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
	
	public function ProcessRequest() 
    {
		$this->mPaguBas = new PopupPaguBas();
        
		if($_POST || isset($_GET['cari'])) {
			if(isset($_POST['kode'])) {
				$kode = $_POST['kode'];
			} elseif(isset($_GET['kode'])) {
				$kode = Dispatcher::Instance()->Decrypt($_GET['kode']);
			} else {
				$kode = '';
			}
		  
			if(isset($_POST['nama'])) {
				$nama = $_POST['nama'];
			} elseif(isset($_GET['nama'])) {
				$nama = Dispatcher::Instance()->Decrypt($_GET['nama']);
			} else {
				$nama = '';
			}

		}
		
	//view
		$totalData = $this->mPaguBas->GetCountPaguBas($kode,$nama);
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataPaguBas = $this->mPaguBas->GetPaguBas($startRec,$itemViewed,$kode,$nama);
        
		$url = Dispatcher::Instance()->GetUrl(
								Dispatcher::Instance()->mModule, 
								Dispatcher::Instance()->mSubModule, 
								Dispatcher::Instance()->mAction, 
								Dispatcher::Instance()->mType . 
								'&kode=' . Dispatcher::Instance()->Encrypt($kode) . 
								'&nama=' . Dispatcher::Instance()->Encrypt($nama). 
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
				
		$return['dataPaguBas'] = $dataPaguBas;
		$return['start'] = $startRec+1;

		$return['search']['kode'] = $kode;
		$return['search']['nama'] = $nama;

		return $return;
	}
	
	public function ParseTemplate($data = NULL) 
    {
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'KODE', $search['kode']);
		$this->mrTemplate->AddVar('content', 'NAMA', $search['nama']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
				Dispatcher::Instance()->GetUrl(
						'pagu_anggaran_unit', 
						'PopupPaguBas', 
						'view', 
						'html'));
				
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataPaguBas'])) {
			$this->mrTemplate->AddVar('data_pagu_bas', 'PAGU_BAS_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data_pagu_bas', 'PAGU_BAS_EMPTY', 'NO');


			for ($i=0; $i<sizeof($data['dataPaguBas']); $i++) {
				$no = $i+$data['start'];
				$data['dataPaguBas'][$i]['number'] = $no;
				$data['dataPaguBas'][$i]['link'] = str_replace("'","\'",$data['dataPaguBas'][$i]['unit']);
				
				if($no % 2 == 0) {
					$dataUnitkerja[$i]['class_name'] = 'table-common-even1';
				}

				$this->mrTemplate->AddVars('data_pagu_bas_item', $data['dataPaguBas'][$i], 'BAS_');
				$this->mrTemplate->parseTemplate('data_pagu_bas_item', 'a');	 
			}
		}		
	}
}