<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rencana_pengeluaran/response/ProcessRencanaPengeluaran.proc.class.php';

class ViewCetakRencanaPengeluaran extends HtmlResponse {
   
   protected $RencanaPengeluaran;
   protected $data;
   
   function ViewCetakRencanaPengeluaran() {
     $obj = new ProcessRencanaPengeluaran;
     $this->RencanaPengeluaran = $obj->RencanaPengeluaran;   
	 unset($obj);
   }
   
   
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/rencana_pengeluaran/template');
      $this->SetTemplateFile('view_cetak_rencana_pengeluaran.html');
   }
   
   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }
   
   function ProcessRequest() {  
     
	 if(isset($_GET['grp'])) { 
	     if(is_object($_GET['grp']))
		    $this->data['kegiatandetail_id'] = $_GET['grp']->mrVariable;
		 else
		    $this->data['kegiatandetail_id'] = $_GET;        
      }
	  
	 $return['data'] = $this->RencanaPengeluaran->GetDataCetak($this->data['kegiatandetail_id']) ;
	 //debug($return['data'] );
	 
	 
      
	return $return;
   }
   
   function ParseTemplate($data = NULL) {    
     
     
   $nomor_program =''; //inisialisasi nomor program
	$nomor_kegiatan =''; //inisialisasi nomor kegiatan
	$detail = $data['data'][0];
	
	$this->mrTemplate->AddVar('content', 'TA_NAMA', $detail['ta_nama']);
	/**
	if($detail['subunit_parentid'] <= 0){
		$detail['unit_kode'] = $detail['subunit_kode'];
		$detail['unit_nama'] = $detail['subunit_nama'];
		$detail['unit_pimpinan'] = $detail['subunit_pimpinan'];
	}  
	*/ 
	$this->mrTemplate->AddVar('content', 'UNIT_KODE', $detail['unit_kode']);   	 
	$this->mrTemplate->AddVar('content', 'UNIT_NAMA', $detail['unit_nama']);   
	$this->mrTemplate->AddVar('content', 'UNIT_PIMPINAN', $detail['unit_pimpinan']);
	/**
	if($detail['subunit_parentid'] <= 0){
		$detail['subunit_kode'] = "";
		$detail['subunit_nama'] = "";
		$detail['subunit_pimpinan'] = "";
	}
	$this->mrTemplate->AddVar('content', 'SUBUNIT_KODE', $detail['subunit_kode']);   
	$this->mrTemplate->AddVar('content', 'SUBUNIT_NAMA', $detail['subunit_nama']);   
	$this->mrTemplate->AddVar('content', 'SUBUNIT_PIMPINAN', $detail['subunit_pimpinan']);
	*/   
	$this->mrTemplate->AddVar('content', 'PROGRAM_KODE', $detail['program_kode']);   	 
	$this->mrTemplate->AddVar('content', 'PROGRAM_NAMA', $detail['program_nama']);   
	$this->mrTemplate->AddVar('content', 'KEGIATAN_KODE', $detail['kegiatan_kode']);  
	$this->mrTemplate->AddVar('content', 'KEGIATAN_NAMA', $detail['kegiatan_nama']);  
	$this->mrTemplate->AddVar('content', 'KEGIATANDETAIL_DESKRIPSI', $detail['kegiatandetail_deskripsi']);  
	$this->mrTemplate->AddVar('content', 'SUBKEGIATAN_KODE', $detail['subkegiatan_kode']);  
	$this->mrTemplate->AddVar('content', 'SUBKEGIATAN_NAMA', $detail['subkegiatan_nama']);  
	$this->mrTemplate->AddVar('content', 'KODE_MATA_ANGGARAN', $detail['kode_mata_anggaran']);  
	 
	 
	$i=0;
	if (empty($data['data'])) {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
         $dataGrid = $data['data'];			 
		
		//data sudah siap ditampilkan... kirim ke pat-template
		$total_anggaran =0;
		for($j=0;$j < sizeof($dataGrid);$j++) {
		   $total_anggaran += (int) $dataGrid[$j]['jumlah'];
		   $dataGrid[$j]['nomor'] = $j + 1;
		   $dataGrid[$j]['nilai_satuan'] =number_format($dataGrid[$j]['nilai_satuan'], 0, '', '.');
		   $dataGrid[$j]['kuantitas'] =number_format($dataGrid[$j]['kuantitas'], 0, ',', '.');
		   $dataGrid[$j]['jumlah'] =number_format($dataGrid[$j]['jumlah'], 0, ',', '.');
		   $this->mrTemplate->AddVars('data_item', $dataGrid[$j], 'DATA_');
         $this->mrTemplate->parseTemplate('data_item', 'a');			   
		}
		$total_anggaran =number_format($total_anggaran, 2, ',', '.');
		$this->mrTemplate->AddVar('content', 'TOTAL_ANGGARAN', $total_anggaran);
   }
   
  
   }
  
}
?>