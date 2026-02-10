<?php
/**
 * Class ViewPopupVolumeTarif
 * untuk menampilkan data volume tarif
 * @package rencana_penerimaan
 * @since 06 Februari 2012
 * @copyright 2012 gamatechno
 * @access public
 */
 
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
			'module/rencana_penerimaan/business/AppPopupVolumeTarif.class.php';

class ViewPopupVolumeTarif extends HtmlResponse 
{	
	public function TemplateModule() 
	{
		$this->SetTemplateBasedir(
					GTFWConfiguration::GetValue('application','docroot').
								'module/rencana_penerimaan/template');
								
		$this->SetTemplateFile('view_popup_volume_tarif.html');
	}
   
    public function TemplateBase() 
	{
      $this->SetTemplateBasedir(
	  				GTFWConfiguration::GetValue('application', 'docroot') . 
					  	'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
	}
	
	public function ProcessRequest() 
	{
		$popupVolumeTarif = new AppPopupVolumeTarif;
		
		if($_POST || isset($_GET['cari'])) {
			if(isset($_POST['nama'])) {
				$nama = $_POST['nama'];
			} elseif(isset($_GET['nama'])) {
				$nama = Dispatcher::Instance()->Decrypt($_GET['nama']);
			} else {
				$nama = '';
			}
		  
			if(isset($_POST['fakprodi'])) {
				$fakprodi = $_POST['fakprodi'];
			} elseif(isset($_GET['fakprodi'])) {
				$fakprodi = Dispatcher::Instance()->Decrypt($_GET['fakprodi']);
			} else {
				$fakprodi = '';
			}
		}
		
		$totalData = $popupVolumeTarif->GetCountData($nama,$fakprodi);
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		
		/**
		 * data volume tarif
		 */
		$dataVolumeTarif =  $popupVolumeTarif->GetData($startRec,$itemViewed,$nama,$fakprodi);
		
		/**
		 * untuk url paging
		 */
		$url = Dispatcher::Instance()->GetUrl(
							Dispatcher::Instance()->mModule, 
							Dispatcher::Instance()->mSubModule, 
							Dispatcher::Instance()->mAction, 
							Dispatcher::Instance()->mType . 
							'&nama=' . Dispatcher::Instance()->Encrypt($nama) . 
							'&fakprodi=' . Dispatcher::Instance()->Encrypt($fakprodi) . 
							'&cari=' . Dispatcher::Instance()->Encrypt(1));
      	$dest = "popup-subcontent";

		/**
		 * untuk membuat paging
		 */
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
				
		$return['dataVolumeTarif'] = $dataVolumeTarif;
		$return['start'] = $startRec+1;
		$return['itemViewd']=$itemViewed;
		$return['startRec']=$startRec;
		$return['search']['nama'] = $nama;
		$return['search']['fakprodi'] = $fakprodi;
		return $return;
	}
		
	public function ParseTemplate($data = NULL) 
	{
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'VOLUME_TARIF_NAMA', $search['nama']);
		$this->mrTemplate->AddVar('content', 'VOLUME_TARIF_FAKPRODI', $search['fakprodi']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
								Dispatcher::Instance()->GetUrl(
										'rencana_penerimaan', 
										'popupVolumeTarif', 
										'view', 
										'html') . 
										'&nama=' . Dispatcher::Instance()->Encrypt($nama) . 
										'&fakprodi=' . Dispatcher::Instance()->Encrypt($fakprodi) );
		/**
		 * data volume tarif
		 */								
		$dataVolume = $data['dataVolumeTarif'];
		if(empty($dataVolume)){
			$this->mrTemplate->AddVar('data_volume', 'VOLUME_TARIF_EMPTY', 'YES');	
		} else {
			$this->mrTemplate->AddVar('data_volume', 'VOLUME_TARIF_EMPTY', 'NO');
			for ($i=0; $i<sizeof($dataVolume); $i++) {
				$no = $i+$data['start'];
				$dataVolume[$i]['number'] = $no;
				if($no % 2 != 0) {
					$dataVolume[$i]['class_name'] = 'table-common-even';
				} else {
					$dataVolume[$i]['class_name'] = '';
				}
				$dataVolume[$i]['tarif'] = number_format($dataVolume[$i]['tarif'] ,0,',','.');
				$dataVolume[$i]['volume'] = number_format($dataVolume[$i]['volume'] ,0,',','.');
				$this->mrTemplate->AddVars('data_volume_item', $dataVolume[$i], 'V_');
				$this->mrTemplate->parseTemplate('data_volume_item', 'a');	 
			}
		}
		/**
		 * end volume tarif
		 */
	}
}

?>