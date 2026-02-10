<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
			 'module/rencana_pengeluaran/business/AppPopupSubUnitKerja.class.php';

class PopupSubUnitkerja extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
			   'module/rencana_pengeluaran/template');
		$this->SetTemplateFile('popup_subunit_kerja.html');
	}
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 
	  		 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
	
	function ProcessRequest() {
		
		$unitkerjaObj = new AppPopupSubUnitKerja();
		//$satker = Dispatcher::Instance()->Decrypt($_GET['satker']);
		//$satker_label = Dispatcher::Instance()->Decrypt($_GET['satker_label']);
		
		$satker			= $_GET['satker'];
		
		$satker_label	= str_replace("\'","'",$_GET['satker_label']);		
		
		//cek data dari form yang di input
		#foreach($_POST AS $field=>$val):
		#	echo "<pre>";
		#	echo $field."\t : ".$val."<br />";
		#	echo "</pre>";
		#endforeach;
		
		if(isset($_POST['btncari'])):
			$kode		= trim($_POST['unitkerja_kode']);
			$unitkerja	= trim($_POST['unitkerja']);
			if($_POST['tipeunit'] != "all"):
				$tipeunit	= trim($_POST['tipeunit']);
			else:
				$tipeunit	= "";
			endif;
		elseif(isset($_GET['cari'])):
			$kode		= Dispatcher::Instance()->Decrypt($_GET['kode']);
			$unitkerja	= Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
			$tipeunit	= Dispatcher::Instance()->Decrypt($_GET['tipeunit']);
		else:
			$kode		= "";
			$unitkerja	= "";
			$tipeunit	= "";
		endif;
		#echo "<strong>Data</strong>";
		#echo "<pre>";
		#echo "Data Satker \t : ".$satker."<br />";
		#echo "Satker Label \t : ".$satker_label."<br />";
		#echo "Kode Unit \t : ".$kode."<br />";
		#echo "Unit Kerja \t : ".$unitkerja."<br />";
		#echo "Tipe Unit \t : ".$tipeunit."<br />";
		#echo "</pre>";
		
		#if($_POST || isset($_GET['cari'])) {
		#	if(isset($_POST['unitkerja_kode'])) {
		#		$kode = $_POST['unitkerja_kode'];
		#	} elseif(isset($_GET['kode'])) {
		#		$kode = Dispatcher::Instance()->Decrypt($_GET['kode']);
		#	} else {
		#		$kode = '';
		#	}
		#  
		#	if(isset($_POST['unitkerja'])) {
		#		$unitkerja = $_POST['unitkerja'];
		#	} elseif(isset($_GET['unitkerja'])) {
		#		$unitkerja = Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
		#	} else {
		#		$unitkerja = '';
		#	}

		#	if($_POST['tipeunit'] != "all") {
		#		$tipeunit = $_POST['tipeunit'];
		#	} elseif(isset($_GET['tipeunit'])) {
		#		$tipeunit = Dispatcher::Instance()->Decrypt($_GET['tipeunit']);
		#	} else {
		#		$tipeunit = '';
		#	}
		#}
		
	//view
		$totalData 	= $unitkerjaObj->GetCountDataUnitkerja($satker, $kode, $unitkerja, $tipeunit);
		$itemViewed = 20;
		$currPage 	= 1;
		$startRec 	= 0 ;
		
		if(isset($_GET['page'])) {
			$currPage 	= (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec 	=($currPage-1) * $itemViewed;
		}
		
		$dataUnitkerja 	= $unitkerjaObj->getDataUnitkerja
						  ($startRec, $itemViewed, $satker, $kode, $unitkerja, $tipeunit);
		
		$url 			= Dispatcher::Instance()->GetUrl(
						  Dispatcher::Instance()->mModule, 
						  Dispatcher::Instance()->mSubModule, 
						  Dispatcher::Instance()->mAction, 
						  Dispatcher::Instance()->mType . 
						  '&satker=' . Dispatcher::Instance()->Encrypt($satker) . 
						  '&satker_label=' . Dispatcher::Instance()->Encrypt($satker_label) . 
						  '&kode=' . Dispatcher::Instance()->Encrypt($kode) . 
						  '&unitkerja=' . Dispatcher::Instance()->Encrypt($unitkerja) . 
						  '&tipeunit=' . Dispatcher::Instance()->Encrypt($tipeunit) . 
						  '&cari=' . Dispatcher::Instance()->Encrypt(1));
		
		$dest 			= "popup-subcontent";
		
		Messenger::Instance()->SendToComponent
							   ('paging', 'Paging', 'view', 'html', 'paging_top', 
							   array($itemViewed,$totalData, $url, $currPage, $dest), 
							   Messenger::CurrentRequest);

		$arr_tipeunit 	= $unitkerjaObj->GetDataTipeunit();

		Messenger::Instance()->SendToComponent
							   ('combobox', 'Combobox', 'view', 'html', 'tipeunit', 
							   array('tipeunit', $arr_tipeunit, $tipeunit, 'true', 
							   ' style="width:200px;" '), Messenger::CurrentRequest);

		$msg 			= Messenger::Instance()->Receive(__FILE__);

		$this->Pesan 	= $msg[0][1];
		$this->css 		= $msg[0][2];
				
		$return['dataUnitkerja'] 			= $dataUnitkerja;
		$return['start'] 					= $startRec+1;

		$return['search']['satker'] 		= $satker;
		$return['search']['satker_label'] 	= $satker_label;
		$return['search']['kode'] 			= $kode;
		$return['search']['unitkerja'] 		= $unitkerja;
		$return['search']['tipeunit'] 		= $tipeunit;
		
		#echo "<p><strong>Result</strong><br />";
		#echo "<pre>";
		#echo "Total data \t : ".$totalData;
		#echo "</pre>";		
		#echo "</p>";

		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$search 		= $data['search'];
		$this->mrTemplate->AddVar('content', 'SATKER_LABEL', 
						   Dispatcher::Instance()->Decrypt($_GET['satker_label']));
		$this->mrTemplate->AddVar('content', 'UNITKERJA_KODE', $search['kode']);
		$this->mrTemplate->AddVar('content', 'UNITKERJA', $search['unitkerja']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
						   Dispatcher::Instance()->
						   GetUrl('rencana_pengeluaran', 'subUnitKerja', 'popup', 'html') . 
						   "&satker=" . 
						   Dispatcher::Instance()->Encrypt($search['satker']) . 
						   "&satker_label=" . 
						   Dispatcher::Instance()->Encrypt($search['satker_label']));
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataUnitkerja'])){
			$this->mrTemplate->AddVar('data_unitkerja', 'UNITKERJA_EMPTY', 'YES');
		}else{
			//$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
			//$encPage = Dispatcher::Instance()->Encrypt($decPage);
			$this->mrTemplate->AddVar('data_unitkerja', 'UNITKERJA_EMPTY', 'NO');
			$dataUnitkerja 		= $data['dataUnitkerja'];

			for ($i=0; $i<sizeof($dataUnitkerja); $i++):
				$dataUnitkerja[$i]['enc_unitkerja_id'] 	= Dispatcher::Instance()->
														  Encrypt($dataUnitkerja[$i]
														  ['unitkerja_id']);
				$dataUnitkerja[$i]['enc_unitkerja_nama'] = Dispatcher::Instance()->
														   Encrypt($dataUnitkerja[$i]
														   ['unitkerja_nama']);
			endfor;
			
			for ($i=0; $i<sizeof($dataUnitkerja); $i++):
				$no 							= $i+$data['start'];
				$dataUnitkerja[$i]['number'] 	= $no;
				$dataUnitkerja[$i]['link'] 		= str_replace("'","\'",
												  $dataUnitkerja[$i]['unitkerja_nama']);
				if ($no%2 != 0):
					$dataUnitkerja[$i]['class_name'] = 'table-common-even';
				else: 
					$dataUnitkerja[$i]['class_name'] = '';
				endif;

				$this->mrTemplate->AddVars('data_unitkerja_item', $dataUnitkerja[$i], 
										   'UNITKERJA_');
				$this->mrTemplate->parseTemplate('data_unitkerja_item', 'a');	 
			endfor;
		}
		
		
	}
}
?>
