<?php

/**
 *
 * class ViewKelompokLaporanAnggaran
 * @package kelompok_laporan_anggaran
 * @subpackage business
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @since 6 september 2012
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/kelompok_laporan_anggaran/business/KelompokLaporanAnggaran.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/kelompok_laporan_anggaran/business/KelompokJenisLaporanAnggaran.class.php';

class ViewKelompokLaporanAnggaran extends HtmlResponse 
{

	protected $mPesan;
	protected $mCss;

	protected $mKLA;
	protected $mKJLA;
	
	public function TemplateModule() 
    {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
                'module/kelompok_laporan_anggaran/template');
		$this->SetTemplateFile('view_kelompok_laporan_anggaran.html');
	}
	
	public function ProcessRequest() 
    {
        $msg = Messenger::Instance()->Receive(__FILE__);
		$this->mPesan = $msg[0][1];
		$this->mCss = $msg[0][2];
		
		$this->mKLA = new KelompokLaporanAnggaran();
		$this->mKJLA = new KelompokJenisLaporanAnggaran();
		
		if($_POST || isset($_GET['cari'])) {
			if(isset($_POST['key'])) {
				$key = $_POST['key'];
			} elseif(isset($_GET['key'])) {
				$key = Dispatcher::Instance()->Decrypt($_GET['key']);
			} else {
				$key = '';
			}
            
            if(isset($_POST['jns_lap'])) {
				$jns_lap = $_POST['jns_lap'];
			} elseif(isset($_GET['jns_lap'])) {
				$jns_lap = Dispatcher::Instance()->Decrypt($_GET['jns_lap']);
			} else {
				$jns_lap = '';
			}
            
		}
		
		//view
		$totalData = $this->mKLA->GetCountData($key,$jns_lap);
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$data_list = $this->mKLA->GetData($startRec, $itemViewed, $key,$jns_lap);
//		print_r($data_list);
		$url = Dispatcher::Instance()->GetUrl(
                                                Dispatcher::Instance()->mModule, 
                                                Dispatcher::Instance()->mSubModule, 
                                                Dispatcher::Instance()->mAction, 
                                                Dispatcher::Instance()->mType . 
                                                '&key=' . Dispatcher::Instance()->Encrypt($key) .
                                                '&jns_lap=' . Dispatcher::Instance()->Encrypt($jns_lap) . 
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
                                                        $currPage), 
                                                Messenger::CurrentRequest);
        
        $jenisLaporan = $this->mKJLA->GetJenisLaporanCombo();
		Messenger::Instance()->SendToComponent(
                                                'combobox', 
                                                'Combobox', 
                                                'view', 
                                                'html', 
                                                'jns_lap', 
                                                array(
                                                        'jns_lap', 
                                                        $jenisLaporan, 
                                                        $jns_lap, 
                                                        true, 
                                                        ''), 
                                                Messenger::CurrentRequest);
         

		$return['kelompok_laporan_anggaran'] = $data_list;
		$return['start'] = $startRec+1;

		$return['search']['key'] = $key;
		return $return;
	}
	
	public function ParseTemplate($data = NULL) 
    {
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'KEY', $search['key']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
                                        Dispatcher::Instance()->GetUrl(
                                                                'kelompok_laporan_anggaran', 
                                                                'KelompokLaporanAnggaran', 
                                                                'view', 
                                                                'html'));
                                                                
		$this->mrTemplate->AddVar('content', 'URL_ADD', 
                                        Dispatcher::Instance()->GetUrl(
                                                                'kelompok_laporan_anggaran', 
                                                                'inputKelompokLaporanAnggaran', 
                                                                'view', 
                                                                'html'));
		if($this->mPesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->mPesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->mCss);
		}

		if (empty($data['kelompok_laporan_anggaran'])) {
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
		} else {
			$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
			$encPage = Dispatcher::Instance()->Encrypt($decPage);
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
			$data_list = $data['kelompok_laporan_anggaran'];
        
            //mulai bikin tombol delete
			$label = GTFWConfiguration::GetValue('language','kelompok_laporan_anggaran');
			$urlDelete = Dispatcher::Instance()->GetUrl('kelompok_laporan_anggaran', 'deleteKelompokLaporanAnggaran', 'do', 'html');
			$urlReturn = Dispatcher::Instance()->GetUrl('kelompok_laporan_anggaran', 'KelompokLaporanAnggaran', 'view', 'html');
			
			Messenger::Instance()->Send('confirm', 'confirmDelete', 'do', 'html', 
                                        array($label, $urlDelete, $urlReturn),Messenger::NextRequest);
			$this->mrTemplate->AddVar('content', 'URL_DELETE', 
                                            Dispatcher::Instance()->GetUrl(
                                                                'confirm', 
                                                                'confirmDelete', 
                                                                'do', 
                                                                'html'));
            //selesai bikin tombol delete
            //mulai perulangan nulis di template
			for ($i=0; $i<sizeof($data_list); $i++) {
				$no = $i+$data['start'];
				$data_list[$i]['number'] = $no;
				if ($no % 2 != 0) $data_list[$i]['class_name'] = 'table-common-even';
				else $data_list[$i]['class_name'] = '';
				
				if($i == 0) $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);				
				if($i == sizeof($data_list)-1) $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
				$idEnc = Dispatcher::Instance()->Encrypt($data_list[$i]['id']);

				$data_list[$i]['url_edit'] = Dispatcher::Instance()->GetUrl(
                                                                'kelompok_laporan_anggaran', 
                                                                'inputKelompokLaporanAnggaran', 
                                                                'view', 
                                                                'html') . 
                                                                '&dataId=' . $idEnc;
                                                                
                $data_list[$i]['url_detil'] = Dispatcher::Instance()->GetUrl(
                                                                'kelompok_laporan_anggaran', 
                                                                'detilKelompokLaporanAnggaran', 
                                                                'view', 
                                                                'html') . 
                                                                '&dataId=' . $idEnc;
				$sUrlDelete = 'kelompok_laporan_anggaran|deleteKelompokLaporanAnggaran|do|html';
                $sUrlReturn = 'kelompok_laporan_anggaran|KelompokLaporanAnggaran|view|html';
                
                
                $data_list[$i]['url_delete'] = Dispatcher::Instance()->GetUrl(
																'confirm', 
																'confirmDelete', 
																'do', 
																'html').
																'&urlDelete='. $sUrlDelete.
																'&urlReturn='.$sUrlReturn.
																'&id='.$idEnc.
																'&label='.$label.
																'&dataName='.$data_list[$i]['nama'];
				$this->mrTemplate->AddVars('data_item', $data_list[$i], 'DATA_');
				$this->mrTemplate->parseTemplate('data_item', 'a');	 
			}
		}
	}
}
