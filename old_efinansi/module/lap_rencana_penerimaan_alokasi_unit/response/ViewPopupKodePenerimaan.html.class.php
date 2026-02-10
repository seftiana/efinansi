<?php

/**
 * 
 * class ViewPopupKodePenerimaan
 * @since 11 November 2012
 * @analyst nanang_ruswianto<nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/lap_rencana_penerimaan_alokasi_unit_v2/business/PopupKodePenerimaan.class.php';

class ViewPopupKodePenerimaan extends HtmlResponse 
{

	protected $Pesan;
	protected $namaModule = 'lap_rencana_penerimaan_alokasi_unit_v2';

	public function TemplateModule() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
			'module/'.$this->namaModule.'/template');
		$this->SetTemplateFile('view_popup_kode_penerimaan.html');
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
		$Obj = new PopupKodePenerimaan();
        
       	if($_POST || isset($_GET['cari'])) {
		  if(isset($_POST)) {
		      	$kode = $_POST['kode'];
		      	$nama = $_POST['nama'];
		  } elseif(isset($_GET)) {
		      	$kode = Dispatcher::Instance()->Decrypt($_GET['kode']);
		      	$nama = Dispatcher::Instance()->Decrypt($_GET['nama']);
		  } else {
		      	$kode = '';
		      	$nama = '';
		  }
       }
		
	//view
		$totalData = $Obj->GetCountData($kode,$nama);
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$data = $Obj->GetData($startRec, $itemViewed, $kode,$nama);
		$url = Dispatcher::Instance()->GetUrl(
									Dispatcher::Instance()->mModule, 
									Dispatcher::Instance()->mSubModule, 
									Dispatcher::Instance()->mAction, 
									Dispatcher::Instance()->mType . 
									'&kode=' . Dispatcher::Instance()->Encrypt($kode) .
                                    '&nama=' . Dispatcher::Instance()->Encrypt($nama) .
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
				
		$return['data'] = $data;
		$return['start'] = $startRec+1;
		$return['search']['kode'] = $kode;
		$return['search']['nama'] = $nama;
		$return['decUnitkerja'] = $decUnitkerja;
        $return['decThAnggar'] = $decThAnggar;
        
		return $return;
	}
	
	public function ParseTemplate($data = NULL) 
	{
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'KODE', $search['kode']);
		$this->mrTemplate->AddVar('content', 'NAMA', $search['nama']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH',
								Dispatcher::Instance()->GetUrl(
													$this->namaModule,
													'popupKodePenerimaan', 
													'view', 
													'html') . 
													'&kode=' . 
													Dispatcher::Instance()->Encrypt($search['kode']).
													'&nama=' . 
													Dispatcher::Instance()->Encrypt($search['nama'])
													);
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['data'])) {
			$this->mrTemplate->AddVar('data_kodepenerimaan', 'KODEPENERIMAAN_EMPTY', 'YES');
		} else {;
			$this->mrTemplate->AddVar('data_kodepenerimaan', 'KODEPENERIMAAN_EMPTY', 'NO');
			$dataKodePenerimaan = $data['data'];
			for ($i=0; $i<sizeof($dataKodePenerimaan); $i++) {
				$no = $i+$data['start'];
				$dataKodePenerimaan[$i]['number'] = $no;

				if ($dataKodePenerimaan[$i]['tipe'] == "header") {
					$dataKodePenerimaan[$i]['class_name'] = 'table-common-even';
				}
				else {
					$dataKodePenerimaan[$i]['class_name'] = '';
					$this->mrTemplate->AddVar('show_button_pilih', 'IS_SHOW', 'YES');
					$this->mrTemplate->AddVar('show_button_pilih', 'DATA_ID', $dataKodePenerimaan[$i]['id']);
					$this->mrTemplate->AddVar('show_button_pilih', 'DATA_KODE', $dataKodePenerimaan[$i]['kode']);
					$this->mrTemplate->AddVar('show_button_pilih', 'DATA_NAMA', $dataKodePenerimaan[$i]['nama']);
					$this->mrTemplate->AddVar('show_button_pilih', 'DATA_SATUAN', 
								$dataKodePenerimaan[$i]['satuan']);
					$this->mrTemplate->AddVar('show_button_pilih', 'DATA_ALOKASI_UNIT', 
								$dataKodePenerimaan[$i]['alokasi_unit']);
					$this->mrTemplate->AddVar('show_button_pilih', 'DATA_ALOKASI_PUSAT', 
								$dataKodePenerimaan[$i]['alokasi_pusat']);
				}

				$this->mrTemplate->AddVars('data_kodepenerimaan_item', $dataKodePenerimaan[$i], 'DATA_');
				$this->mrTemplate->parseTemplate('data_kodepenerimaan_item', 'a');	 
			}
		}
	}
}

?>