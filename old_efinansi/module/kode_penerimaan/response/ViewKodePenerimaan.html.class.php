<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/kode_penerimaan/response/ProcessKodePenerimaan.proc.class.php';

class ViewKodePenerimaan extends HtmlResponse {

   protected $proc;

   function ViewKodePenerimaan(){
      $this->proc = new ProcessKodePenerimaan();
   }
   
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/kode_penerimaan/template');
      $this->SetTemplateFile('view_kode_penerimaan.html');
   } 
   
   
   function ProcessRequest() { 
	  if(isset($_POST['btncari'])) { //pasti dari form pencarian :p	  
			$kode = $_POST['kode'];
            $nama = $_POST['nama'];   
      } elseif(isset($_GET['cari'])){
		    $kode=Dispatcher::Instance()->Decrypt($_GET['kode']);
			$nama=Dispatcher::Instance()->Decrypt($_GET['nama']);
        } else{
             $kode='';
			 $nama='';
        } 
	        
      $itemViewed = 20;
      $currPage = 1;
      $startRec = 0 ;
      if(isset($_GET['page'])) {
         $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();  
         $startRec =($currPage-1) * $itemViewed;
      }
	  
	  $search['kodepenerimaan']['kode']=$kode;
	  $search['kodepenerimaan']['nama']=$nama;
	  $totalData = $this->proc->KodePenerimaan->GetCount($search['kodepenerimaan']);
	  $dataList = $this->proc->KodePenerimaan->GetData($startRec,$itemViewed, $search['kodepenerimaan']);
	    
	
      $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 
               Dispatcher::Instance()->mSubModule, 
               Dispatcher::Instance()->mAction, 
               Dispatcher::Instance()->mType. 
               '&kode=' .Dispatcher::Instance()->Encrypt($kode) . 
               '&nama=' . Dispatcher::Instance()->Encrypt($nama) .
               '&cari=' . Dispatcher::Instance()->Encrypt(1));
			   
      Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', 
         array($itemViewed,$totalData, $url, $currPage), 
         Messenger::CurrentRequest);    
	 

        //start menghandle pesan yang diparsing
	    
		$tmp=$this->proc->parsingUrl(__FILE__);		 
		if(!empty($tmp))
		  $return['msg']=$tmp['msg'];  
		//end handle
       
      $return['data'] = $dataList;
      $return['start'] = $startRec+1;
	  $return['kode'] = $kode;
      $return['nama'] = $nama;
      
	return $return;
   }
   
   function ParseTemplate($data = NULL) {   	  
	   
	  $this->mrTemplate->AddVar('content', 'SEARCH_KP_KODE', $data['kode']);
	  $this->mrTemplate->AddVar('content', 'SEARCH_KP_NAMA', $data['nama']);	  
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('kode_penerimaan', 'kodePenerimaan', 'view', 'html') );
      $this->mrTemplate->AddVar('content', 'URL_ADD', Dispatcher::Instance()->GetUrl('kode_penerimaan', 'inputKodePenerimaan', 'view', 'html') );	  	  
	  
	  if (isset ($data['msg'])) {	     
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);
		 if($data['msg']['action']=='msg') 
		   $class='notebox-done';
		 else
		   $class = 'notebox-warning';
		 
		 $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $class);
		 
      }  
	  
	  	  	  
      if (empty($data['data'])) {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
         $dataGrid = $data['data'];		
		 $i=0;		 
		 $program_nomor='';
		 $no=$data['start'];
		
		 //$counter_nomor=$data['counter_nomor'];
		 
         for ($i=0; $i<sizeof($dataGrid);) { 
                $dataGrid[$i]['nomor']=$no;
				$no++;
		         
				 
			     $idEnc = Dispatcher::Instance()->Encrypt($dataGrid[$i]['id']);
			     
			   
                 $dataGrid[$i]['url_edit'] = Dispatcher::Instance()->GetUrl('kode_penerimaan', 'inputKodePenerimaan', 'view', 'html') . 
                  '&grp=' . $idEnc.
                  '&kode='.$data['kode'].
                  '&nama='.$data['nama'].
                  '&cari='.Dispatcher::Instance()->Encrypt(1);
    			 $dataGrid[$i]['url_edit']='<a class="xhr dest_subcontent-element" href="'.$dataGrid[$i]['url_edit'].'" title="Ubah"><img src="images/button-edit.gif" alt="Ubah"/></a>'; 
				 
				 				 
				 //dipake componenet confirm delete
			     $urlAccept = 'kode_penerimaan|deleteKodePenerimaan|do|html-kode|nama|cari-'.
										$data['kode'].'|'.$data['nama'].'|'.Dispatcher::Instance()->Encrypt(1);
                 $urlReturn = 'kode_penerimaan|kodePenerimaan|view|html-kode|nama|cari-'.
										$data['kode'].'|'.$data['nama'].'|'.Dispatcher::Instance()->Encrypt(1);
			     $label = 'Kode Penerimaan';
			     $dataName = $dataGrid[$i]['nama'];
				 
				 if($dataGrid[$i]['total_child'] == 0) {;
					$dataGrid[$i]['url_delete'] = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html').'&urlDelete='. $urlAccept.'&urlReturn='.$urlReturn.'&id='.$idEnc.'&label='.$label.'&dataName='.$dataName;
					$dataGrid[$i]['url_delete']='<a class="xhr dest_subcontent-element" href="'.$dataGrid[$i]['url_delete'].'" title="Hapus"><img src="images/button-delete.gif" alt="Hapus"/></a>';
				 } else {
					 $dataGrid[$i]['url_delete']= '';
				 }
              if ($dataGrid[$i]['tipe'] == 'header'){
			         $dataGrid[$i]['class_name']='table-common-even1';
			         $dataGrid[$i]['style']='bold';
              }else{
                  $dataGrid[$i]['class_name']='';
                  $dataGrid[$i]['style']='normal';
				} 
				 $dataKirim = $dataGrid[$i];
			     $i++;
				 
				 $this->mrTemplate->AddVars('data_item', $dataKirim, 'DATA_');
                 $this->mrTemplate->parseTemplate('data_item', 'a');
			  
			   }				 
               			   
		}
		
	}
	
}
?>