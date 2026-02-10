<?php

/**
 * class ViewPopupKodePenerimaanHeader
 * @description untuk menampilkan data kode penerimaan bertipe header
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright (c) 2013 Gamatechno Indonesia
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot').
	'module/kode_penerimaan/business/AppPopupKodePenerimaanHeader.class.php';

class ViewPopupKodePenerimaanHeader extends HtmlResponse 
{

	private $mPesan;

	public function TemplateModule()
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
			'module/'.Dispatcher::Instance()->mModule.'/template');
		$this->SetTemplateFile('view_popup_kode_penerimaan_header.html');
	}
	
	public function TemplateBase() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
		$this->SetTemplateFile('document-common.html');
		$this->SetTemplateFile('layout-common-popup.html');
    }
   
    public function ProcessRequest() 
    {
		$kodeObj = new AppPopupKodePenerimaanHeader();
		
		$POST = $_POST->AsArray();
		
		if(!empty($POST)) {
			$nama = $POST['nama'];
			$kode= $POST['kode'];
		} elseif(isset($_GET['cari'])) {
			$nama = Dispatcher::Instance()->Decrypt($_GET['nama']);
			$kode = Dispatcher::Instance()->Decrypt($_GET['kode']);
		} else {
			$nama="";
			$kode="";
		}
		
		
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		
		$dataList = $kodeObj->GetData($kode,$nama,$startRec, $itemViewed);
		$totalData = $kodeObj->GetCount();		
		
		$url =  Dispatcher::Instance()->GetUrl(
								Dispatcher::Instance()->mModule, 
								Dispatcher::Instance()->mSubModule, 
								Dispatcher::Instance()->mAction, 
								Dispatcher::Instance()->mType 
								. '&kode=' . Dispatcher::Instance()->Encrypt($kode)
								. '&nama=' . Dispatcher::Instance()->Encrypt($nama)
								. '&cari=' . Dispatcher::Instance()->Encrypt(1));
	
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
												'popup-subcontent'), 
										Messenger::CurrentRequest);
		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['dataList'] = $dataList;
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
											Dispatcher::Instance()->mModule, 
											'popupKodePenerimaanHeader', 
											'view', 
											'html'));
		
		if (empty($data['dataList'])) {
			$this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
		} else {
		
			$this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
			$dataList = $data['dataList'];

			for ($i=0; $i<sizeof($dataList); $i++) {
				$no = $i+$data['start'];
				$dataList[$i]['number'] = $no;
				if ($no % 2 != 0) $dataList[$i]['class_name'] = 'table-common-even';
				else $dataList[$i]['class_name'] = '';
				
				$this->mrTemplate->AddVars('data_item', $dataList[$i], 'DATA_');
				$this->mrTemplate->parseTemplate('data_item', 'a');	 
			}
		}
	}
}

?>