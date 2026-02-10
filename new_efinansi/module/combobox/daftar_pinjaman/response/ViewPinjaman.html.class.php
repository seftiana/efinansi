<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/daftar_pinjaman/business/AppPinjaman.class.php';

class ViewPinjaman extends HtmlResponse{
	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot').'module/daftar_pinjaman/template');
		$this->SetTemplateFile('view_pinjaman.html');
	}
   function ProcessRequest() {
		$propObj = new AppPinjaman();
		if($_POST || isset($_GET['cari'])) {
			if((isset($_POST['pinjaman_kode']))or(isset($_POST['pinjaman_nama']))){
				$pinjamanKode = $_POST['pinjaman_kode'];
				$pinjamanNama = $_POST['pinjaman_nama'];
				$Jumlah = $_POST['pinjaman_jumlah'];
				$Angsuran = $_POST['pinjaman_angsuran'];
			} elseif((isset($_GET['pinjaman_kode']))or(isset($_GET['pinjaman_nama']))) {
				$pinjamanKode = Dispatcher::Instance()->Decrypt($_GET['pinjaman_kode']);
				$pinjamanNama=Dispatcher::Instance()->Decrypt($_GET['pinjaman_nama']);
				$Jumlah=Dispatcher::Instance()->Decrypt($_GET['pinjaman_jumlah']);
				$Angsuran=Dispatcher::Instance()->Decrypt($_GET['pinjaman_angsuran']);
			} else {
				$pinjamanKode = '';
				$pinjamanNama='';
				$Jumlah='';
				$Angsuran='';
			}
		}
		$totalData = $propObj->GetCountDataPinjaman($pinjamanKode,$pinjamanNama,$Jumlah,$Angsuran);
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataPinjaman=$propObj->GetDataPinjaman($pinjamanKode,$pinjamanNama,$Jumlah,$Angsuran,$startRec,$itemViewed);
		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&pinjaman_kode=' . Dispatcher::Instance()->Encrypt($pinjamanKode) . 
		'&pinjaman_nama=' . Dispatcher::Instance()->Encrypt($pinjamanNama) .
		'&pinjaman_jumlah=' . Dispatcher::Instance()->Encrypt($Jumlah) .
		'&pinjaman_angsuran=' . Dispatcher::Instance()->Encrypt($Angsuran) .

		'&pinjaman_nama_singkat=' . Dispatcher::Instance()->Encrypt($pinjamanNamaSingkat) . '&cari=' . Dispatcher::Instance()->Encrypt(1));
      
		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage), Messenger::CurrentRequest);
		$msg = Messenger::Instance()->Receive(__FILE__);
		
		$return['pesan'] = $msg[0][1];
		$return['css'] = $msg[0][2];
		
		$return['siaPesan'] = $msg[1][1];
		$return['siaCss'] = $msg[1][2];
		
		$return['dataPinjaman'] = $dataPinjaman;
		$return['start'] = $startRec+1;
		$return['search']['pinjaman_kode'] = $pinjamanKode;
		$return['search']['pinjaman_nama']=$pinjamanNama;
		$return['search']['pinjaman_jumlah']=$Jumlah;
		$return['search']['pinjaman_angsuran']=$Angsuran;
		return $return;
   }

   function ParseTemplate($data = NULL) {
	   // echo"<pre>";print_r($data['dataPinjaman']);echo"</pre>";
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'PINJAMAN_KODE', $search['pinjaman_kode']);
		$this->mrTemplate->AddVar('content', 'PINJAMAN_NAMA', $search['pinjaman_nama']);
		$this->mrTemplate->AddVar('content', 'PINJAMAN_JUMLAH', $search['pinjaman_jumlah']);
		$this->mrTemplate->AddVar('content', 'PINJAMAN_ANGSURAN', $search['pinjaman_angsuran']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('daftar_pinjaman', 'Pinjaman', 'view', 'html'));
		$this->mrTemplate->AddVar('content', 'URL_ADD', Dispatcher::Instance()->GetUrl('daftar_pinjaman', 'InputPinjaman', 'view', 'html'));
		
		if($data['pesan']) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['pesan']);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $data['css']);
		}
		
		if($data['siaPesan']) {
			$this->mrTemplate->SetAttribute('warning_connection', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_connection', 'ISI_PESAN', $data['siaPesan']);
			$this->mrTemplate->AddVar('warning_connection', 'CLASS_PESAN', $data['siaCss']);
		}
      
		if (empty($data['dataPinjaman'])) {
			$this->mrTemplate->AddVar('data_pinjaman', 'PINJAMAN_EMPTY', 'YES');
		} else {
			$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
			$encPage = Dispatcher::Instance()->Encrypt($decPage);
			$this->mrTemplate->AddVar('data_pinjaman', 'PINJAMAN_EMPTY', 'NO');
			$dataPinjaman = $data['dataPinjaman'];
        
			$label = "Data Pinjaman";
			$urlDelete = Dispatcher::Instance()->GetUrl('daftar_pinjaman', 'DeletePinjaman', 'do', 'json');
			$urlReturn = Dispatcher::Instance()->GetUrl('daftar_pinjaman', 'Pinjaman', 'view', 'html');
			Messenger::Instance()->Send('confirm', 'confirmDelete', 'do', 'html', array($label, $urlDelete, $urlReturn),Messenger::NextRequest);
			$this->mrTemplate->AddVar('content', 'URL_DELETE', Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html'));
	      
			for ($i=0; $i<sizeof($dataPinjaman); $i++) {
				$no = $i+$data['start'];
				$dataPinjaman[$i]['number'] = $no;
				if ($no % 2 != 0) $dataPinjaman[$i]['class_name'] = 'table-common-even';
				else $dataPinjaman[$i]['class_name'] = '';
				
				if($i == 0) $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);				
				if($i == sizeof($dataPinjaman)-1) $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
				$idEnc = Dispatcher::Instance()->Encrypt($dataPinjaman[$i]['pinjaman_kode']);
				$urlAccept = 'daftar_pinjaman|DeletePinjaman|do|html-cari-'.$cari;
				$urlReturn = 'daftar_pinjaman|pinjaman|view|html-cari-'.$cari;
				$dataPinjaman[$i]['url_edit'] = Dispatcher::Instance()->GetUrl('daftar_pinjaman', 'InputPinjaman', 'view', 'html') . '&dataId=' . $idEnc . '&page=' . $encPage . '&cari='.$cari;
				$dataPinjaman[$i]['url_delete'] =  Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html').'&urlDelete='. $urlAccept.'&urlReturn='.$urlReturn.'&id='.$dataPinjaman[$i]['pinjaman_kode'].'&label='.$label.'&dataName='.$dataPinjaman[$i]['pinjaman_nama'];
				$dataPinjaman[$i]['pinjaman_jumlah'] = 'Rp. '.number_format($dataPinjaman[$i]['pinjaman_jumlah'], 0, ',','.');
				$dataPinjaman[$i]['pinjaman_angsuran'] ='Rp. '.number_format($dataPinjaman[$i]['pinjaman_angsuran'], 0, ',','.');
				$this->mrTemplate->AddVars('data_pinjaman_item', $dataPinjaman[$i], 'PINJAMAN_');
				$this->mrTemplate->parseTemplate('data_pinjaman_item', 'a');	 
			}
		}
   }
}
?>
