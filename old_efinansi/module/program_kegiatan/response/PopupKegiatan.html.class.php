<?php
/**
* @module popup kegiatan
* @author Galih,
* @mail to rkasigi@gmail.com
* @copyright 2008&copy;program_kegiatan
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/program_kegiatan/response/ProcessSubKegiatan.proc.class.php';

class PopupKegiatan extends HtmlResponse {
   
   protected $data;
   protected $search;   

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/program_kegiatan/template');
      $this->SetTemplateFile('popup_kegiatan.html');
   }
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
   
   
   
   function ProcessRequest() {
      $kegiatanObj = new SubKegiatan();	  

	  if(isset($_GET['idProgram']))
		$idProgram = $_GET['idProgram'];
	   
if (isset($_GET['idTahun']))
      {
         if($_GET['idTahun']!='')
		 {
		 $idTahun=Dispatcher::Instance()->Decrypt($_GET['idTahun']);
		 }else
		 {$idTahun='';}
      }
	  
	  
	if(isset($_POST['data'])) {
		if(is_object($_POST['data'])) 
			$this->data=$_POST['data']->AsArray();			
		else 
			$this->data = $_POST['data'];	 
		
		$ta_id_selected=$this->data['kegiatan']['nama'];
		$idProgram=$this->data['kegiatan']['idProgram'];
		$idTahun=$this->data['kegiatan']['idTahun'];
		 
		$return['search']= $this->data;		 
		   
	}
	
	if(isset($_GET['search']) || !empty($_GET['search'])){
		$ta_id_selected = $_GET['search'];
	}
	 	
	  $totalData = $kegiatanObj->GetCountDataWhereTA($idProgram,$idTahun, $ta_id_selected);

     $itemViewed = 20;
      $currPage = 1;
      $startRec = 0 ;
      if(isset($_GET['page'])) {
         $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();  
         $startRec =($currPage-1) * $itemViewed;
      }
		$dataProgram = $kegiatanObj->GetDataWhereTa($startRec, $itemViewed, $idProgram,$idTahun, $ta_id_selected);
		//done
      $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 
               Dispatcher::Instance()->mSubModule, 
               Dispatcher::Instance()->mAction, 
               Dispatcher::Instance()->mType
               ). 
               "&idTahun=$idTahun&idProgram=$idProgram".
               '&search=' . Dispatcher::Instance()->Encrypt($ta_id_selected);
		$dest = "popup-subcontent";	   
      Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', 
         array($itemViewed,$totalData, $url, $currPage, $dest), 
         Messenger::CurrentRequest);       
      $return['searchId'] = $ta_id_selected;
		$return['idProgram'] = $idProgram;
		$return['idTahun'] = $idTahun;
      $return['dataProgram'] = $dataProgram;
      $return['start'] = $startRec+1;
   
	return $return;
   }
   
   function ParseTemplate($data = NULL) {      
	  //debug($data);
		$this->mrTemplate->AddVar('content', 'ID_PROGRAM',$data['idProgram']);
		$this->mrTemplate->AddVar('content', 'ID_TAHUN',$data['idTahun']);
		$this->mrTemplate->AddVar('content', 'SEARCH_KEGIATAN_NAMA',$data['searchId']);		

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('program_kegiatan', 'kegiatan', 'popup', 'html'));      
	  
      if (empty($data['dataProgram'])) {	     
         $this->mrTemplate->AddVar('data_grid', 'IS_EMPTY', 'YES');
      } else {
	  
			
			$this->mrTemplate->AddVar('data_grid', 'IS_EMPTY', 'NO');
			$dataProgram = $data['dataProgram'];
			$i=0;
			$no=$data['start'];

			for ($i=0; $i<sizeof($dataProgram); $i++) {              			   
				
				if($dataProgram[$i]['kodeProgram']!=$kodeProgram){
					#$arrProgram[$i]['no']=$no;
					$arrProgram[$i]['class_name'] = '';
					$arrProgram[$i]['kode'] = $dataProgram[$i]['kodeProgram'];
					$arrProgram[$i]['nama'] = $dataProgram[$i]['namaProgram'];
					$arrProgram[$i]['class_name'] = 'table-common-even1';
					$this->mrTemplate->setAttribute('url_choose','visibility','hidden');
					$this->mrTemplate->AddVars('data_item', $arrProgram[$i], 'DATA_');
					$this->mrTemplate->parseTemplate('data_item', 'a');
					$kodeProgram = $dataProgram[$i]['kodeProgram']; 
					
					
					#$no++;
				}
				$dataProgram[$i]['nama_program'] 	= $dataProgram[$i]['namaProgram'];
				$dataProgram[$i]['link'] 			= str_replace("'","\'",$dataProgram[$i]['namaProgram']);
				$dataProgram[$i]['linknama']		= str_replace("'","\'",
														$dataProgram[$i]['nama']);
				$this->mrTemplate->AddVars('url_choose', $dataProgram[$i], 'DATA_');
				$this->mrTemplate->setAttribute('url_choose','visibility','visible');
				$dataProgram[$i]['no']=$no;
				$no++;
				$dataProgram[$i]['class_name'] = '';
				
				//$dataProgram[$i]['link']	= str_replace("'","\'",
				//									  $dataProgram[$i]['nama_program']);
				
				
				$this->mrTemplate->AddVars('data_item', $dataProgram[$i], 'DATA_');
				$this->mrTemplate->parseTemplate('data_item', 'a'); 			   
			}
		}
	
   } 
}
?>
