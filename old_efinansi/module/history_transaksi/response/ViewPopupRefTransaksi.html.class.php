<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/history_transaksi/business/HTSpjPerTransaksi.class.php';

class ViewPopupRefTransaksi extends HtmlResponse 
{
   	protected $data;
   	protected $search;   
   	protected $Ref;
   
   	public function TemplateModule() 
   	{
   		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
			'module/history_transaksi/template');
      	$this->SetTemplateFile('view_popup_ref_transaksi.html');
   	}
   
	public function TemplateBase() 
	{
      	$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      	$this->SetTemplateFile('document-common-popup.html');
      	$this->SetTemplateFile('layout-common-popup.html');
   	}	
      
   	public function ProcessRequest() 
 	{	       
		$Obj = new HTSpjPerTransaksi();
		$POST = $_POST->AsArray();
		if(!empty($POST)) {
			$ref_transaksi = $POST['ref_transaksi'];
		} elseif(isset($_GET['cari'])) {
			$ref_transaksi = Dispatcher::Instance()->Decrypt($_GET['ref_transaksi']);
		} else {
			$ref_transaksi ='';
		}
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$daftar_ref_transaksi = $Obj->GetDaftarRefTransaksi($startRec, $itemViewed, $ref_transaksi);
		$url = Dispatcher::Instance()->GetUrl(
									Dispatcher::Instance()->mModule, 
									Dispatcher::Instance()->mSubModule, 
									Dispatcher::Instance()->mAction, 
									Dispatcher::Instance()->mType . 
									'&ref_transaksi=' . Dispatcher::Instance()->Encrypt($ref_transaksi) . 
									'&cari=' . Dispatcher::Instance()->Encrypt(1));
									
		$dest = "popup-subcontent";
		$totalData = $Obj->GetCountData();
	
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
														$currPage,$dest), 
												Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['daftar_ref_transaksi'] = $daftar_ref_transaksi;
		$return['start'] = $startRec+1;
		$return['search']['ref_transaksi'] = $ref_transaksi;
		return $return;
	}
	
	public function ParseTemplate($data = NULL) 
	{
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'REF_TRANSAKSI', $search['ref_transaksi']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
									Dispatcher::Instance()->GetUrl(
																'history_transaksi', 
																'popupRefTransaksi', 
																'view', 
																'html')
																);
		if (empty($data['daftar_ref_transaksi'])) {
			$this->mrTemplate->AddVar('is_ref_transaksi_empty', 'IS_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('is_ref_transaksi_empty', 'IS_EMPTY', 'NO');
			$daftar_ref = $data['daftar_ref_transaksi'];
			//print_r($daftar_map);
			for ($i=0; $i<sizeof($daftar_ref); $i++) {
				$no = $i+$data['start'];
				$daftar_ref[$i]['number'] = $no;
				if ($no % 2 != 0) $daftar_ref[$i]['class_name'] = 'table-common-even';
				else $daftar_ref[$i]['class_name'] = '';
				$this->mrTemplate->AddVars('daftar_ref_transaksi_detil_item', 
														$daftar_ref[$i], 'RT_');
				$this->mrTemplate->parseTemplate('daftar_ref_transaksi_detil_item', 'a');	 
			}
		}
	}
}