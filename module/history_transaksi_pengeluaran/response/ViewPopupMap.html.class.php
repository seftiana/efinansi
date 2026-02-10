<?php
/**
 * Class ViewPopupMap
 * untuk menampilkan data map
 * @package history_transaksi
 * @since 14 Februari 2012
 * @copyright 2012 gamatechno
 * @access public
 */
 
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/history_transaksi_pengeluaran/business/HTRealisasiPenerimaan.class.php';

class ViewPopupMap extends HtmlResponse 
{
	public function TemplateModule() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
			'module/history_transaksi_pengeluaran/template');
		$this->SetTemplateFile('view_popup_map.html');
	}
   
    public function TemplateBase() 
	{
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   	}
	
	public function ProcessRequest() 
	{
		$Obj = new HTRealisasiPenerimaan();
		$POST = $_POST->AsArray();
		if(!empty($POST)) {
			$nama = $POST['nama'];
		} elseif(isset($_GET['cari'])) {
			$nama = Dispatcher::Instance()->Decrypt($_GET['nama']);
		} else {
			$nama="";
		}
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$daftar_map = $Obj->GetDaftarMap($startRec, $itemViewed, $nama);
		$url = Dispatcher::Instance()->GetUrl(
												Dispatcher::Instance()->mModule, 
												Dispatcher::Instance()->mSubModule, 
												Dispatcher::Instance()->mAction, 
												Dispatcher::Instance()->mType . 
												'&nama=' . Dispatcher::Instance()->Encrypt($nama) . 
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

		$return['daftar_map'] = $daftar_map;
		$return['start'] = $startRec+1;
		$return['search']['nama'] = $nama;
		return $return;
	}
	
	public function ParseTemplate($data = NULL) 
	{
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'NAMA', $search['nama']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
									Dispatcher::Instance()->GetUrl(
																'history_transaksi_pengeluaran', 
																'popupMap', 
																'view', 
																'html')
																);
		if (empty($data['daftar_map'])) {
			$this->mrTemplate->AddVar('is_map_empty', 'MAP_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('is_map_empty', 'MAP_EMPTY', 'NO');
			$daftar_map = $data['daftar_map'];
			//print_r($daftar_map);
			for ($i=0; $i<sizeof($daftar_map); $i++) {
				$no = $i+$data['start'];
				$daftar_map[$i]['number'] = $no;
				if ($no % 2 != 0) $daftar_map[$i]['class_name'] = 'table-common-even';
				else $daftar_map[$i]['class_name'] = '';
				$this->mrTemplate->AddVars('daftar_map_detil_item', 
														$daftar_map[$i], 'MAP_');
				$this->mrTemplate->parseTemplate('daftar_map_detil_item', 'a');	 
			}
		}
	}
}


?>