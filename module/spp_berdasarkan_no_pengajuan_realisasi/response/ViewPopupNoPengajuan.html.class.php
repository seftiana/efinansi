<?php

/**
 * @package realisasi_pencairan_2
 * @sub_package response
 * @class ViewPopupPengajuanRealisasi
 * @description untuk view popup pengajuan realisasi
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @copyright 2013 Gamatechno Indonesia
 */
 
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/realisasi_pencairan_2/business/AppPopupNoPengajuan.class.php';
	
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'main/function/date.php';

class ViewPopupNoPengajuan extends HtmlResponse 
{
	public $Pesan;   
	protected $mDBObj;

	public function TemplateModule() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
		'module/realisasi_pencairan_2/template');
		$this->SetTemplateFile('view_popup_no_pengajuan.html');
	}

	public function TemplateBase() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
		$this->SetTemplateFile('document-common-popup.html');
		$this->SetTemplateFile('layout-common-popup.html');
	}

	public function ProcessRequest() 
	{
		$this->mDBObj    = new AppPopupNoPengajuan();
		$POST                   = $_POST->AsArray();
		$unitKerjaId = Dispatcher::Instance()->Decrypt($_GET['unit_kerja_id']);
		$programId = Dispatcher::Instance()->Decrypt($_GET['program_id']);
		
		if(!empty($POST)) {
			$noPengajuan   = $POST['no_pengajuan'];
		} elseif(isset($_GET['cari'])) {
			$noPengajuan   = Dispatcher::Instance()->Decrypt($_GET['no_pengajuan']);
		} else {
			$noPengajuan='';
		}		
		
		$itemViewed    = 20;
		$currPage      = 1;
		$startRec      = 0 ;
		if(isset($_GET['page'])) {
			$currPage   = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec   =($currPage-1) * $itemViewed;
		}
      
		$dataPengajuan  = $this->mDBObj->GetData($unitKerjaId,$programId,$noPengajuan,$startRec,$itemViewed);
		
		$totalData  = $this->mDBObj->GetCountData();
		
		$url = Dispatcher::Instance()->GetUrl(
							Dispatcher::Instance()->mModule, 
							Dispatcher::Instance()->mSubModule, 
							Dispatcher::Instance()->mAction, 
							Dispatcher::Instance()->mType . 		
							'&unit_kerja_id=' . Dispatcher::Instance()->Encrypt($unitKerjaId) . 
							'&no_pengajuan=' . Dispatcher::Instance()->Encrypt($noPengajuan) . 
							'&cari=' . Dispatcher::Instance()->Encrypt(1)
							);
		
		$dest             = "popup-subcontent";
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
											$dest
										), 
										Messenger::CurrentRequest);	
				
		$return['dataPengajuan']  = $dataPengajuan;
		$return['unitKerjaId']  = $unitKerjaId;
		$return['programId']  = $programId;
		$return['noPengajuan']  = $noPengajuan;
		$return['start'] = $startRec+1;
		return $return;
	}

	public function ParseTemplate($data = NULL) 
	{
		$urlSearch  = Dispatcher::Instance()->GetUrl(
									'realisasi_pencairan_2', 
									'PopupNoPengajuan', 
									'view', 
									'html'
									).
									'&unit_kerja_id=' . Dispatcher::Instance()->Encrypt($data['unitKerjaId']).
									'&program_id=' . Dispatcher::Instance()->Encrypt($data['programId']);
	
	
		$this->mrTemplate->AddVar('content', 'NO_PENGAJUAN', $data['noPengajuan']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);				
		
	
		if (empty($data['dataPengajuan'])) {
			$this->mrTemplate->AddVar('data_pengajuan', 'IS_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data_pengajuan', 'IS_EMPTY', 'NO');
			$dataPengajuan = $data['dataPengajuan'];
			
			$klpId = 0;
			$n=0;
			for ($i=0; $i < sizeof($dataPengajuan); ) {
				if($dataPengajuan[$i]['pengajuan_id'] == $klpId){
					$no = $i+$data['start'];
					$dataP[$n]['number'] = $no;
					$dataP[$n]['id_pengajuan'] = $dataPengajuan[$i]['pengajuan_id'];
					$dataP[$n]['id_spp'] = $dataPengajuan[$i]['spp_id'];
					$dataP[$n]['id_pengajuan_detail'] = $dataPengajuan[$i]['pengajuan_detail_id'];
					$dataP[$n]['tanggal_pengajuan'] ='';//$dataPengajuan[$i]['pengajuan_tgl'];
					$dataP[$n]['tanggal_pengajuan_label'] ='';//IndonesianDate($dataPengajuan[$i]['pengajuan_tgl'],"YYYY-MM-DD");
					$dataP[$n]['kode_ma'] = $dataPengajuan[$i]['kode_ma'];
					$dataP[$n]['keterangan'] = $dataPengajuan[$i]['nama_index'];
					$dataP[$n]['nominal_realisasi'] = $dataPengajuan[$i]['pengajuan_realisasi'];
					$dataP[$n]['nominal_realisasi_approve'] = $dataPengajuan[$i]['pengajuan_realisasi_approve'];
					$dataP[$n]['nominal_total_spp'] = $dataPengajuan[$i]['total_approve'];
					
					$dataP[$n]['nominal_realisasi_f'] = number_format($dataPengajuan[$i]['pengajuan_realisasi'], 0, ',', '.');
					$dataP[$n]['nominal_realisasi_approve_f'] = number_format($dataPengajuan[$i]['pengajuan_realisasi_approve'], 0, ',', '.');
					$dataP[$n]['nominal_total_spp_f'] = number_format($dataPengajuan[$i]['total_approve'], 0, ',', '.');
					
					$this->mrTemplate->AddVar('action','DATA_ID_PENGAJUAN',$dataP[$n]['id_pengajuan']);
					$this->mrTemplate->AddVar('action','DATA_ID_PENGAJUAN_DETAIL',$dataP[$n]['id_pengajuan_detail']);
					$this->mrTemplate->AddVar('action','DATA_NOMOR_PENGAJUAN',$dataPengajuan[$i]['pengajuan_nomor']);
					$this->mrTemplate->AddVar('action','DATA_TANGGAL_PENGAJUAN',$dataPengajuan[$i]['pengajuan_tgl']);
					$this->mrTemplate->AddVar('action','DATA_TANGGAL_PENGAJUAN_LABEL',IndonesianDate($dataPengajuan[$i]['pengajuan_tgl'],"YYYY-MM-DD"));
					$this->mrTemplate->AddVar('action','DATA_NOMINAL_TOTAL_SPP', $dataPengajuan[$i]['total_approve']);
					$this->mrTemplate->AddVar('action','DATA_NOMINAL_TOTAL_SPP_F',$dataP[$n]['nominal_total_spp_f']);
					$this->mrTemplate->AddVar('action','DATA_KODE_MA' , $dataPengajuan[$i]['kode_ma']);
					$this->mrTemplate->AddVar('action','DATA_NAMA_INDEX' , $dataPengajuan[$i]['nama_index']);
					$this->mrTemplate->SetAttribute('action', 'visibility', 'visible');
				
					$this->mrTemplate->AddVars('data_pengajuan_item',$dataP[$n], 'DATA_');		
					$this->mrTemplate->parseTemplate('data_pengajuan_item', 'a');
					$i++;
				} elseif($dataPengajuan[$i]['pengajuan_id'] != $klpId ){
					$klpId = $dataPengajuan[$i]['pengajuan_id'];			
				
					$dataP[$n]['id_pengajuan'] = '';
					$dataP[$n]['id_spp'] = '';
					$dataP[$n]['id_pengajuan_detail'] = '';
					$dataP[$n]['tanggal_pengajuan'] =$dataPengajuan[$i]['pengajuan_tgl'];
					$dataP[$n]['tanggal_pengajuan_label'] =IndonesianDate($dataPengajuan[$i]['pengajuan_tgl'],"YYYY-MM-DD");
					$dataP[$n]['kode_ma'] = '';
					$dataP[$n]['keterangan'] = '<b>'.$dataPengajuan[$i]['pengajuan_nomor'].'</b>';
					$dataP[$n]['nominal_realisasi'] = $dataPengajuan[$i]['pengajuan_realisasi'];
					$dataP[$n]['nominal_realisasi_approve'] = $dataPengajuan[$i]['pengajuan_realisasi_approve'];
					$dataP[$n]['nominal_total_spp'] = $dataPengajuan[$i]['total_approve'];
					
					$dataP[$n]['nominal_realisasi_f'] = number_format($dataPengajuan[$i]['pengajuan_realisasi'], 0, ',', '.');
					$dataP[$n]['nominal_realisasi_approve_f'] = number_format($dataPengajuan[$i]['pengajuan_realisasi_approve'], 0, ',', '.');
					$dataP[$n]['nominal_total_spp_f'] = number_format($dataPengajuan[$i]['total_approve'], 0, ',', '.');
										
					$this->mrTemplate->AddVar('action','DATA_ID_PENGAJUAN','');
					$this->mrTemplate->AddVar('action','DATA_ID_PENGAJUAN_DETAIL','');
					$this->mrTemplate->AddVar('action','DATA_NOMOR_PENGAJUAN','');
					$this->mrTemplate->AddVar('action','DATA_TANGGAL_PENGAJUAN','');
					$this->mrTemplate->AddVar('action','DATA_TANGGAL_PENGAJUAN_LABEL','');
					$this->mrTemplate->AddVar('action','DATA_NOMINAL_TOTAL_SPP','');
					$this->mrTemplate->AddVar('action','DATA_NOMINAL_TOTAL_SPP_F','');
					$this->mrTemplate->AddVar('action','DATA_KODE_MA','');
					$this->mrTemplate->AddVar('action','DATA_NAMA_INDEX','');
					$this->mrTemplate->SetAttribute('action', 'visibility', 'hidden');		
								
					$this->mrTemplate->AddVars('data_pengajuan_item',$dataP[$n], 'DATA_');					
					$this->mrTemplate->parseTemplate('data_pengajuan_item', 'a');
				}
				$n++;
         }
      }
   }
}
?>