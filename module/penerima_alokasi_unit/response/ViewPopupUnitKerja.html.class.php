<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/penerima_alokasi_unit/business/PopupUnitKerja.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewPopupUnitKerja extends HtmlResponse 
{

	protected $Pesan;
	protected $unitkerjaObj;
    protected $userUnitKerjaObj;
    protected $mModuleName ='penerima_alokasi_unit';
    
    protected $mUnitId;

	public function __construct()
	{
		parent::__construct();
		$this->mTipe  = $_GET['tipe'];
		$this->mUnitId  = $_GET['unit_id'];
	}
	public function TemplateModule() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
				'module/'.$this->mModuleName.'/template');
		
		$this->SetTemplateFile('view_popup_unit_kerja_unit.html');
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
		$this->unitkerjaObj = new PopupUnitKerja();
		$this->userUnitKerjaObj = new UserUnitKerja();
		//$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		
		//$unitkerjaUserId = $this->userUnitKerjaObj->GetUnitKerjaUser($userId);
		if($_POST || isset($_GET['cari'])) {
			if(isset($_POST['unitkerja_kode'])) {
				$kode = $_POST['unitkerja_kode'];
			} elseif(isset($_GET['kode'])) {
				$kode = Dispatcher::Instance()->Decrypt($_GET['kode']);
			} else {
				$kode = '';
			}
		  
			if(isset($_POST['unitkerja'])) {
				$unitkerja = $_POST['unitkerja'];
			} elseif(isset($_GET['unitkerja'])) {
				$unitkerja = Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
			} else {
				$unitkerja = '';
			}

			if($_POST['tipeunit'] != "all") {
				$tipeunit = $_POST['tipeunit'];
			} elseif(isset($_GET['tipeunit'])) {
				$tipeunit = Dispatcher::Instance()->Decrypt($_GET['tipeunit']);
			} else {
				$tipeunit = '';
			}
		}
		
	//view
		$totalData = $this->unitkerjaObj->GetCountDataUnitkerja(
											$kode, 
											$unitkerja, 
											$tipeunit,
											$this->mUnitId
											);
											//$unitkerjaUserId['unit_kerja_id']);
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataUnitkerja = $this->unitkerjaObj->getDataUnitkerja(
									$startRec, 
									$itemViewed, 
									$kode, 
									$unitkerja, 
									$tipeunit, 
									$this->mUnitId );
									//$unitkerjaUserId['unit_kerja_id']);
		$url = Dispatcher::Instance()->GetUrl(
								Dispatcher::Instance()->mModule, 
								Dispatcher::Instance()->mSubModule, 
								Dispatcher::Instance()->mAction, 
								Dispatcher::Instance()->mType . 
								'&kode=' . Dispatcher::Instance()->Encrypt($kode) . 
								'&unitkerja=' . Dispatcher::Instance()->Encrypt($unitkerja) . 
								'&tipeunit=' . Dispatcher::Instance()->Encrypt($tipeunit) . 
								'&unit_id=' . Dispatcher::Instance()->Encrypt($this->mUnitId) . 
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

		$arr_tipeunit = $this->unitkerjaObj->GetDataTipeunit();

		Messenger::Instance()->SendToComponent(
								'combobox', 
								'Combobox', 
								'view', 
								'html', 
								'tipeunit', 
								array(
										'tipeunit', 
										$arr_tipeunit, 
										$tipeunit, 
										'true', 
										' style="width:200px;" '), 
								Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);

		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];
				
		$return['dataUnitkerja'] = $dataUnitkerja;
		$return['tahunAnggaranId'] = $tahunAnggaranId;
		
		$return['start'] = $startRec+1;

		$return['search']['kode'] = $kode;
		$return['search']['unitkerja'] = $unitkerja;
		$return['search']['tipeunit'] = $tipeunit;

		return $return;
	}
	
	function ParseTemplate($data = NULL) {
	
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'UNITKERJA_KODE', $search['kode']);
		$this->mrTemplate->AddVar('content', 'UNITKERJA', $search['unitkerja']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
				Dispatcher::Instance()->GetUrl(
						$this->mModuleName, 
						'PopupUnitKerja', 
						'view', 
						'html'));
				
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataUnitkerja'])) {
			$this->mrTemplate->AddVar('data_unitkerja', 'UNITKERJA_EMPTY', 'YES');
		} else {
			//$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
			//$encPage = Dispatcher::Instance()->Encrypt($decPage);
			$this->mrTemplate->AddVar('data_unitkerja', 'UNITKERJA_EMPTY', 'NO');
			$dataUnitkerja = $data['dataUnitkerja'];

			for ($i=0; $i<sizeof($dataUnitkerja); $i++) {
				$dataUnitkerja[$i]['enc_unitkerja_id'] = Dispatcher::Instance()->Encrypt(
															$dataUnitkerja[$i]['unitkerja_id']);
				$dataUnitkerja[$i]['enc_unitkerja_nama'] = Dispatcher::Instance()->Encrypt(
															$dataUnitkerja[$i]['unitkerja_nama']);
			}
			for ($i=0; $i<sizeof($dataUnitkerja); $i++) {
				$no = $i+$data['start'];
				$dataUnitkerja[$i]['number'] = $no;
				$dataUnitkerja[$i]['link'] = str_replace("'","\'",$dataUnitkerja[$i]['unit']);
                $dataUnitkerja[$i]['satker'] = str_replace("'","\'",$dataUnitkerja[$i]['satker']);
				//if ($no % 2 != 0) $dataUnitkerja[$i]['class_name'] = 'table-common-even';
				//else $dataUnitkerja[$i]['class_name'] = '';
				//if($dataUnitkerja[$i]['parentId'] == 0) {
				if($this->userUnitKerjaObj->GetTotalSubUnitKerja($dataUnitkerja[$i]['id']) > 0) {
					$dataUnitkerja[$i]['class_name'] = 'table-common-even1';
				}

				$this->mrTemplate->AddVars('data_unitkerja_item', $dataUnitkerja[$i], 'UNITKERJA_');
				$this->mrTemplate->parseTemplate('data_unitkerja_item', 'a');	 
			}
		}		
	}
}
?>