<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/program_kegiatan/business/RkaklOutput.class.php';

class ViewPopupRkaklOutput extends HtmlResponse 
{
	public function TemplateModule() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
				'module/program_kegiatan/template');
		$this->SetTemplateFile('view_popup_rkakl_output.html');
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
		$Obj = new RkaklOutput();
		$POST = $_POST->AsArray();
		if(isset($POST['btncari'])) {
			$kode = $POST['kode'];
			$nama = $POST['nama'];
		} elseif(isset($_GET['cari'])) {
			$kode = Dispatcher::Instance()->Decrypt($_GET['kode']);
			$nama = Dispatcher::Instance()->Decrypt($_GET['nama']);
		} else {
			$kode ='';
			$nama ='';
		}
		
		
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		$dest = "popup-subcontent";
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$data = $Obj->GetData($startRec, $itemViewed, $kode,$nama);
		$totalData = $Obj->GetCountData($kode,$nama);
		
		$url = Dispatcher::Instance()->GetUrl(
											Dispatcher::Instance()->mModule, 
											Dispatcher::Instance()->mSubModule, 
											Dispatcher::Instance()->mAction, 
											Dispatcher::Instance()->mType . 
											'&kode=' . Dispatcher::Instance()->Encrypt($kode) . 
											'&nama=' . Dispatcher::Instance()->Encrypt($nama) . 
											'&cari=' . Dispatcher::Instance()->Encrypt(1));

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

		$return['data'] = $data;
		$return['start'] = $startRec+1;

		$return['search']['nama'] = $nama;
		$return['search']['kode'] = $kode;
		
		return $return;
	}
	
	public function ParseTemplate($data = NULL) 
	{
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'NAMA', $search['nama']);
		$this->mrTemplate->AddVar('content', 'KODE', $search['kode']);
		
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
									Dispatcher::Instance()->GetUrl(
															'program_kegiatan', 
															'popupRkaklOutput', 
															'view', 
															'html'));

		if (empty($data['data'])) {
			$this->mrTemplate->AddVar('data_rkakl_output', 'RKAKL_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data_rkakl_output', 'RKAKL_EMPTY', 'NO');
			
			for ($i=0; $i<sizeof($data['data']); $i++) {
				$no = $i+$data['start'];
				$data['data'][$i]['number'] = $no;
				if ($no % 2 != 0) $data['data'][$i]['class_name'] = 'table-common-even';
				else $data['data'][$i]['class_name'] = '';

				$this->mrTemplate->AddVars('data_rkakl_output_item', $data['data'][$i], 'RKAKL_');
				$this->mrTemplate->parseTemplate('data_rkakl_output_item', 'a');	 
			}
		}
	}
}
?>