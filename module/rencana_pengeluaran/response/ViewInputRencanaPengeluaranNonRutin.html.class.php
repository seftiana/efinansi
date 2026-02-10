<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/rencana_pengeluaran/response/ProcessRencanaPengeluaran.proc.class.php';

class ViewInputRencanaPengeluaranNonRutin extends HtmlResponse 
{
   
   protected $data;
   protected $proc;
   
   function ViewInputRencanaPengeluaranNonRutin() {
     $this->proc = new ProcessRencanaPengeluaran();   
   }

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/rencana_pengeluaran/template');
      $this->SetTemplateFile('input_rencana_pengeluaran_non_rutin.html');
   }
   
   function ProcessRequest() {        
     if(isset($_POST['data'])) {
	    if(is_object($_POST))
		   $this->data = $_POST['data']->AsArray();
		else
		   $this->data = $_POST['data'];
		
		$this->data['action']='add';
	 }elseif(isset($_GET['cari']))	{ 
	  //sebenernya variable cari ini adalah lemparan variable dari module confirm delete, bukan variable cari dalam arti sebenarnya
	  //tapi variable cari ini isinya adalah kegiatandetail_id,subkegiatan_id , subkegiatan_nama	  
	  if(is_object($_GET['cari'])) 
	     $kegiatan = $_GET['cari']->mrVariable;
	  else
	     $kegiatan = $_GET['cari'];
	  
	  $kegiatan = Dispatcher::Instance()->Decrypt($kegiatan);
	  $kegiatan = explode('*',$kegiatan);	  
	  $this->data['kegiatan']['kegiatandetail_id'] = $kegiatan[0];
	  $this->data['kegiatan']['subkegiatan_id'] = $kegiatan[1];
	  $this->data['kegiatan']['subkegiatan_nama'] = $kegiatan[2];
	  
	  $this->data['action']='add';
	  
	   
	 } elseif(isset($_GET['grp'])) { //action pengeditan inih	   
	   if(is_object($_GET['grp'])) 
	     $grp = $_GET['grp']->mrVariable;
	   else
	     $grp = $_GET['grp'];
	   
	   $grp = Dispatcher::Instance()->Decrypt($grp);	   
	   $data = $this->proc->RencanaPengeluaran->GetDataById($grp);
	   if($data) {
	      $this->data['kegiatan']['kegiatandetail_id'] = $data['kegiatandetail_id'];
	      $this->data['kegiatan']['subkegiatan_id'] = $data['subkegiatan_id'];
	      $this->data['kegiatan']['subkegiatan_nama'] = $data['subkegiatan_nama'];		  
		  $this->data['tambah'] = $data;		  
		  $this->data['action']='edit';
		  if($this->data['satuan_komponen_id']=='NULL') {
		     
		  }
	   }
	   
	 }
	 
	 if(isset($_GET['par'])) {
	    if(is_object($_GET['par']))
		   $par = $_GET['par']->mrVariable;
		else
		   $par = $_GET['par'];
        
		$par = explode('|',$par);
		$this->data['kegiatan']['kegiatandetail_id'] = $par[0];
		$this->data['kegiatan']['subkegiatan_id'] = $par[1];
		$this->data['kegiatan']['subkegiatan_nama'] = $par[2];
		//$this->data['action'] = $par[3];
         		
     }	 
	 
	 
     
     	  
      $redir=$this->proc->parsingUrl(__FILE__);
	  
	  if(isset($redir['data'])) { //merupakan bentuk redirect dari halaman sebelumnya
	     $this->data = $redir['data'];		 
		 $ret['msg'] = $redir['msg'];
			
	  }
	  
	  if(!isset($this->data['komponen'])){
		//$this->data['komponen'] = $this->proc->RencanaPengeluaran->GetKomponen($this->data['kegiatan']['kegiatandetail_id'],'edit');
		$this->data['komponen'] = $this->proc->RencanaPengeluaran->GetKomponenNonRutin(
													$this->data['kegiatan']['kegiatandetail_id']); 		
      }
	 
	  $arr_sk = $this->proc->RencanaPengeluaran->GetDataSatuanKomponen();
	  Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'data[tambah][satuan]', 
	     array('data[tambah][satuan]', $arr_sk, $this->data['tambah']['satuan'], 'kosong', ' style="width:150px;" '), 
		 Messenger::CurrentRequest);
		 
	
	    
      return $ret;
   }

   function ParseTemplate($data = NULL) {  
      
   
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
      
      
      if ($this->data['action']=='edit') {
         $url="updateRencanaPengeluaran";
         $tambah="Ubah";         
		 $label_action = 'Update';
      } else {	
         $url="addRencanaPengeluaran";
         $tambah="Tambah";	  
		 $label_action = 'Tambah';
         
      }
	  //debug($this->data['komponen']);	  
	  $this->mrTemplate->AddVar('content', 'DATA_KEGIATANDETAIL_ID', $this->data['kegiatan']['kegiatandetail_id']);
     $this->mrTemplate->AddVar('content', 'DATA_SUBKEGIATAN_NAMA', $this->data['kegiatan']['subkegiatan_nama']);
	  $this->mrTemplate->AddVar('content', 'DATA_SUBKEGIATAN_ID', $this->data['kegiatan']['subkegiatan_id']);
	  $this->mrTemplate->AddVar('content', 'DATA_ACTION', $this->data['action']);
	  $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('rencana_pengeluaran', $url, 'do', 'html'));
	  $this->mrTemplate->AddVar('content', 'URL_KEMBALI', Dispatcher::Instance()->GetUrl('rencana_pengeluaran', 'rencanaPengeluaran', 'view', 'html'));
	  $this->mrTemplate->AddVar('content', 'LABEL_ACTION', $label_action);
	  $this->mrTemplate->AddVar('content', 'TAMBAH_ID', $this->data['tambah']['id']);
	  $this->mrTemplate->AddVar('content', 'TAMBAH_KODE', $this->data['tambah']['kode']);
	  $this->mrTemplate->AddVar('content', 'TAMBAH_NAMA', $this->data['tambah']['nama']);
	  $this->mrTemplate->AddVar('content', 'TAMBAH_DESKRIPSI', $this->data['tambah']['deskripsi']);
	  $this->mrTemplate->AddVar('content', 'TAMBAH_SATUAN', $this->data['tambah']['satuan']);
	  $this->mrTemplate->AddVar('content', 'TAMBAH_JUMLAH', $this->data['tambah']['jumlah']);
	  $this->mrTemplate->AddVar('content', 'TAMBAH_BIAYA', $this->data['tambah']['biaya']);
	  $this->mrTemplate->AddVar('content', 'TAMBAH_TERBILANG', $this->proc->terbilang($this->data['tambah']['biaya']));
	  
	  
	  if (empty($this->data['komponen'])) {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
         $dataGrid = $this->data['komponen'];				 		  
		//debug($dataGrid);
		
         for ($i=0; $i<sizeof($dataGrid);$i++) {   		          
		  $dataGrid[$i]['nomor']=$i+1;
		  $dataGrid[$i]['index']=$i;
		  $idEnc = Dispatcher::Instance()->Encrypt($dataGrid[$i]['rencanapengeluaran_id']);
		  
          if(($i % 2) == '0')		  
		     $dataGrid[$i]['class_name']='table-common-even2';
		  else
		     $dataGrid[$i]['class_name']='';
          		  
		  $dataGrid[$i]['url_edit']=Dispatcher::Instance()->GetUrl('rencana_pengeluaran', 'inputRencanaPengeluaranNonRutin', 'view', 'html').'&grp='.$idEnc;
		  
		  
		  //dipake componenet confirm delete
		 $kirimVar = $this->data['kegiatan']['kegiatandetail_id'].'*'.$this->data['kegiatan']['subkegiatan_id'].'*'.$this->data['kegiatan']['subkegiatan_nama'];
		 
		 $idDelete = $dataGrid[$i]['rencanapengeluaran_id'].'*'.$kirimVar;
		 $idDelete = Dispatcher::Instance()->Encrypt($idDelete);
		 
		 $kirimVar = Dispatcher::Instance()->Encrypt($kirimVar);
	     $urlAccept = 'rencana_pengeluaran|deleteKomponen|do|html-cari-'.$kirimVar;
         $urlReturn = 'rencana_pengeluaran|inputRencanaPengeluaranNonRutin|view|html-cari-'.$kirimVar;
	     $label = 'Rencana Pengeluaran';
	     $dataName = $dataGrid[$i]['nama'];
				 
         $dataGrid[$i]['url_delete'] = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html').'&urlDelete='. $urlAccept.'&urlReturn='.$urlReturn.'&id='.$idDelete.'&label='.$label.'&dataName='.$dataName;               			   
	     $dataGrid[$i]['biaya']=number_format($dataGrid[$i]['biaya'], 0, ',', '.');
              
		  
          $this->mrTemplate->AddVars('data_item', $dataGrid[$i], 'DATA_');
          $this->mrTemplate->parseTemplate('data_item', 'a');			   
		} //end for dataGrid
		//debug($dataGrid);		
	} //end if empty
	
	$this->mrTemplate->AddVar('content', 'CEKLIS_MAX', $i-1); 

   }
}
?>
