<?php

/**
 *
 * class ViewPopupPaguBas
 * @package kelompok_laporan_anggaran
 * @subpackage business
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @since 6 september 2012
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot').
	'module/kelompok_laporan_anggaran/business/PopupPaguBas.class.php';

class ViewPopupPaguBas extends HtmlResponse 
{

	public function TemplateModule()
    {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
			'module/'.Dispatcher::Instance()->mModule.'/template');
		$this->SetTemplateFile('view_popup_pagu_bas.html');
	}
	
	public function TemplateBase() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
		$this->SetTemplateFile('document-common.html');
		$this->SetTemplateFile('layout-common-popup.html');
    }
   
    public function ProcessRequest() 
    {
		$kodeObj = new PopupPaguBas();
		
		if($_POST || isset($_GET['cari'])) {
			if(isset($_POST['nama'])) {
				$namaMak = $_POST['nama'];				
			} elseif(isset($_GET['nama'])) {
				$namaMak = Dispatcher::Instance()->Decrypt($_GET['nama']);				
			} else {
				$namaMak = '';
			}
		}
		
		$totalData = $kodeObj->GetCountDataPaguBasMak($namaMak);		
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		
		$dataList = $kodeObj->GetDataPaguBasMak($startRec, $itemViewed, $namaMak);

		$url = Dispatcher::Instance()->GetUrl(
								Dispatcher::Instance()->mModule,
								Dispatcher::Instance()->mSubModule, 
								Dispatcher::Instance()->mAction, 
								Dispatcher::Instance()->mType . 
								'&nama=' . Dispatcher::Instance()->Encrypt($namaMak). 
								'&cari=' . Dispatcher::Instance()->Encrypt(1));
	
		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed, $totalData, $url, $currPage, 'popup-subcontent'), Messenger::CurrentRequest);
		
		$return['dataList'] = $dataList;
		$return['start'] = $startRec+1;

		$return['search']['nama'] = $namaMak;
		return $return;
	}
	
	public function ParseTemplate($data = NULL) 
	{
		$search = $data['search'];

		$this->mrTemplate->AddVar('content', 'NAMA', $search['nama']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
						Dispatcher::Instance()->GetUrl(
										Dispatcher::Instance()->mModule, 
										'PopupPaguBas', 
										'view', 
										'html'));

		if (empty($data['dataList'])) {
			$this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
		} else {
			$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
			$encPage = Dispatcher::Instance()->Encrypt($decPage);
			$this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
			$dataList = $data['dataList'];

			for ($i=0; $i<sizeof($dataList); $i++) {
				$no = $i+$data['start'];
				$dataList[$i]['number'] = $no;
				if ($no % 2 != 0) $dataList[$i]['class_name'] = 'table-common-even';
				else $dataList[$i]['class_name'] = '';
				
				if($i == 0) $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);				
				if($i == sizeof($dataList)-1) $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
				$idEnc = Dispatcher::Instance()->Encrypt($dataList[$i]['id']);

				$this->mrTemplate->AddVars('data_item', $dataList[$i], 'DATA_');
				$this->mrTemplate->parseTemplate('data_item', 'a');	 
			}
		}
	}
}
