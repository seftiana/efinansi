<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
    'module/tahun_pembukuan/business/TahunPembukuan.class.php';
    
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
    'module/tahun_pembukuan/business/BukuBesar.class.php';

class ViewUpdateSaldoAwal extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/tahun_pembukuan/template');
      $this->SetTemplateFile('view_update_saldo_awal.html');
   }

   function UpdateSaldoAwal(){
      if ($_POST['btnbatal'] == 'Batal'){
          $this->RedirectTo(Dispatcher::Instance()->GetUrl('tahun_pembukuan', 'TahunPembukuan', 'view', 'html'));
      }
   }
   
   function ProcessRequest() {
   	  $TahunPembukuan = new TahunPembukuan();
   	  $Bb = new BukuBesar();
   	  
   	  	# insert
   	  if($_GET['coaid'] <> ''){
	      $id = $_REQUEST['coaid'];
	      $coa= $TahunPembukuan->GetBalancePembukuanCoa($id);
	      
	      # set default tanggal
	      $tgl = $Bb->GetTanggal($id);
	      $periode = explode('-',$tgl);
	      $awal = $periode[0] - 5;
	      $akhir = $periode[0] + 5;       
	      Messenger::Instance()->SendToComponent('tanggal','Tanggal','view','html','tanggal',array($tgl,$awal,$akhir),Messenger::CurrentRequest);
	   	  # end default tanggal
   	  }else{
   	  	  # update
   	  	  $id = $_GET['id'];
   	  	  $coa = $TahunPembukuan->GetTahunPembukuanById($id);
   	  	  $tgl = $Bb->GetTanggal($coa[0]['coaId'],$coa[0]['subacc']);
   	  	  $periode = explode('-',$tgl);
   	  	  $awal = $periode[0] - 5;
   	  	  $akhir = $periode[0] + 5;
   	  	  Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'tanggal',array($tgl,$awal,$akhir), Messenger::CurrentRequest);
   	  	  $return['id'] = $id;
   	  }
   	  
   	  $return['coa'] = $coa;
      	
      return $return;
   }

   function ParseTemplate($data = NULL) {
       if (isset ($this->Pesan)) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
      }

      if(!empty($data['id'])){
      	$url_action = Dispatcher::Instance()->GetUrl('tahun_pembukuan', 'UpdateSaldoAwal', 'do', 'json');
      	$action = 'Ubah';
      }else{
      	$url_action = Dispatcher::Instance()->GetUrl('tahun_pembukuan', 'AddSaldoAwal', 'do', 'json');
      	$action = 'Tambah';
      }

      $this->mrTemplate->AddVar('content', 'ACTION', $action);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $url_action);

      if (!empty($data['coa'])) {
               $item = $data['coa'][0];
               $this->mrTemplate->AddVar('content', 'KODE_AKUN', $item['coaKodeAkun']);
               $this->mrTemplate->AddVar('content', 'NAMA_AKUN', $item['coaNamaAkun']);
               $this->mrTemplate->AddVar('content', 'TIPE_AKUN', $item['ctrNamaTipe']);
               $this->mrTemplate->AddVar('content', 'COA_ID', $item['coaId']);
               if(!empty($data['id'])){
               	$this->mrTemplate->AddVar('data', 'IS_EDIT', 'YES');
               	$this->mrTemplate->AddVar('data', 'SUBACC', $item['subacc']);
               	$this->mrTemplate->AddVar('content', 'OP', 'edit');
               	//$nominal = ($item['debet'] == '0') ? $item['kredit'] : $item['debet'];
               	//$this->mrTemplate->AddVar('content', 'NOMINAL', $nominal);

                $this->mrTemplate->AddVar('content', 'DEBET', $item['debet']);
                $this->mrTemplate->AddVar('content', 'KREDIT', $item['kredit']);
               }else{
               	$this->mrTemplate->AddVar('data', 'IS_EDIT', 'NO');
               	$this->mrTemplate->AddVar('content', 'OP', 'tambah');
               }
               
               $this->mrTemplate->AddVar('content', 'IS_DEBET_POSITIF', $item['coaIsDebetPositif']);
               $this->mrTemplate->AddVar('content', 'SUBAKUN_DEFAULT', str_replace('9','0',GTFWConfiguration::GetValue('application','subAccFormat')));
      }
   }
}
?>
