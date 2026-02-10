<?php
require_once GTFWConfiguration::GetValue('application','docroot').
			 'module/rkakl_program/business/RkaklProgram.class.php';
class ViewPopupProgram extends HtmlResponse{
	function TemplateModule(){
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
				'module/kinerja_utama/template');
		$this->SetTemplateFile('view_popup_program.html');
	}
	function ProcessRequest(){
		$objProgram 	= new RkaklProgram();
		
		if(isset($_POST)):
			$kode 	= trim($_POST['kode']);
			$nama	= trim($_POST['nama']);
		elseif(isset($_GET)):
			$kode 	= Dispatcher::Instance()->Decrypt($_GET['kode']);
			$nama	= Dispatcher::Instance()->Decrypt($_GET['nama']);
		else:
			$kode	= "";
			$nama	= "";
		endif;
		
		//echo 'Kode : '.$kode."<br />";
		//echo 'Nama : '.$nama;
		
		$total_data		= $objProgram->GetCountRkaklProgram($kode,$nama);
		$limit			= 20;
		if(isset($_GET['page'])):
			$currPage 	= (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec 	= ($currPage-1) * $itemViewed;
		else:
			$currPage	= 1;
			$startRec	= 0;
		endif;
		
		$data			= $objProgram->GetRkaklProgram($kode,$nama,$startRec,$limit);
		$url 			= Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 
						  Dispatcher::Instance()->mSubModule, 
						  Dispatcher::Instance()->mAction, 
						  Dispatcher::Instance()->mType . '&kode=' . 
						  Dispatcher::Instance()->Encrypt($kode) . '&nama=' . 
						  Dispatcher::Instance()->Encrypt($nama));
						  
		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 
				   'paging_top', array($limit, $totalData, $url, $currPage,"popup-subcontent"), 
				   Messenger::CurrentRequest);
		
		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 
		'paging_top', array($limit, $totalData, $url, $currPage), 
		Messenger::CurrentRequest);
		
		$return['data'] 			= $data;
		$return['start'] 			= $startRec+1;
		$return['search']['kode'] 	= $kode;
		$return['search']['nama'] 	= $nama;
		
		return $return;
	}
	function ParseTemplate($data = null){
		$dataP		= $data['data'];
		$url_search	= Dispatcher::Instance()->
					  GetUrl('kinerja_utama','PopupProgram','view','html');
		$this->mrTemplate->AddVar('content','URL_SEARCH',$url_search);
		if (empty($dataP)):
			$this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
		else:
		
			$this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
			
			for($i=0;$i<sizeof($dataP);$i++):
            $no = $i+$data['start'];
            $dataP[$i]['no'] = $no;

            if ($no % 2 != 0)
               $dataP[$i]['class_name'] = 'table-common-even';
            else
               $dataP[$i]['class_name'] = '';
			
			//$dataList[$i]['nama']			= "nama'ku";
			$dataP[$i]['link']			= str_replace("'","\'",$dataP[$i]['nama']);
            $this->mrTemplate->AddVars('data_item', $dataP[$i], '');
            $this->mrTemplate->parseTemplate('data_item', 'a');
			endfor;
		endif;
	}
}
?>