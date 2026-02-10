<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/coa/business/Coa.class.php';

class ViewCoa extends HtmlResponse {

   var $Pesan;
   var $css;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/coa/template');
      $this->SetTemplateFile('view_coa.html');
   }
   
   function ProcessRequest() {
      //get pesan
      $msg = Messenger::Instance()->Receive(__FILE__);
      $this->Pesan = $msg[0][1];
      $this->css = 'notebox-done';      
      $Coa = new Coa();
      
      if($_GET['opp'] == 'reset'){
      	$result = $Coa->ResetCoaKodeSistem();
		if($result){
			$this->Pesan = 'Reset COA Kode Sistem Berhasil';
			$this->css = 'notebox-done'; 
		}else{
			$this->Pesan = 'Reset COA Kode Sistem Gagal';
			$this->css = 'notebox-warning';
		}
      }
      
       //get data coa
      if (!isset($_GET['coaid']) && isset($_COOKIE['csd'])) setcookie('csd','');
      if (isset($_GET['coaid'])){
         $coa_id = $_GET['coaid'];
         $return['coa_detail'] = $Coa->GetCoaFromId($coa_id);
      }

	  $return['coa'] = $Coa->GetListCoa();
	  //print_r($return['coa']);
         
      return $return;
   }

   function ParseTemplate($data = NULL) {
      
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('coa', 'Coa', 'view', 'html') );
      $this->mrTemplate->AddVar('content', 'URL_CARI', Dispatcher::Instance()->GetUrl('coa', 'ListCoa', 'view', 'html') );
      $this->mrTemplate->AddVar('content', 'URL_ADD', Dispatcher::Instance()->GetUrl('coa', 'InputCoa', 'view', 'html').'&op=add');
      $this->mrTemplate->AddVar('content', 'URL_EXCEL', Dispatcher::Instance()->GetUrl('coa', 'coa', 'view', 'xlsx'));
      
      //$urlReset = Dispatcher::Instance()->GetUrl('coa', 'Coa', 'view', 'html').'&opp=reset';
      if($urlReset <> '') $this->mrTemplate->AddVar('content', 'URL_RESET', $urlReset);
      else $this->mrTemplate->AddVar('content', 'URL_RESET_DISPLAY', 'none');

      // set message
      if (isset($this->Pesan)){         
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
      }

      //parsing detail coa
      if(!empty($data['coa_detail'])){
         $this->mrTemplate->SetAttribute('coa_detail', 'visibility', 'visible');
         $this->mrTemplate->AddVar('coa_detail', 'URL_UBAH',Dispatcher::Instance()->GetUrl('coa','inputCoa', 'view','html').'&coaid='.$data['coa_detail'][0]['coaId'].'&smpn=');
         $this->mrTemplate->AddVar('coa_detail', 'KODE_AKUN', $data['coa_detail'][0]['coaKodeAkun']);
         $this->mrTemplate->AddVar('coa_detail', 'NAMA_AKUN', $data['coa_detail'][0]['coaNamaAkun']);
      }
      //parsing list coa
      if (empty($data['coa'])) {
         $this->mrTemplate->AddVar('coa', 'COA', 'YES');
      } else {
         $this->mrTemplate->AddVar('coa', 'COA', 'NO');
         $strX = "<script type='text/javascript'>
         d = new dTree('d');
         d.add(0,-1,'Daftar Rekening');\r\n";
         $this->mrTemplate->addVar("coa_treex", "COA_TREE_STRX", $strX);
         $a=0;
         foreach($data['coa'] as $value)
         {
            $a++;
            if (!empty($value['coaKodeAkun'])) {
				$value['coaKodeAkun'] = str_replace("'","",$value['coaKodeAkun']);
				$value['coaNamaAkun'] = str_replace("'","",$value['coaNamaAkun']);
               $str=$str."d.add(".$value['coaId'].",".$value['coaParentAkun'].",'".$value['coaKodeAkun']." [".$value['coaNamaAkun']."]','" . Dispatcher::Instance()->GetUrl('coa', 'Coa', 'view', 'html').'&coaid='.$value['coaId']."');\r\n";
            }
            else {
               $str=$str."d.add(".$value['coaId'].",".$value['coaParentAkun'].",'".$value['coaKodeAkun'].$value['coaNamaAkun']."', '" . Dispatcher::Instance()->GetUrl('coa', 'Coa', 'view', 'html').'&coaid='.$value['coaId'] . "  ');\r\n";
            }	
         }
         $strY = "document.getElementById('div_coa').innerHTML =  d.toString(); </script>";      
         $str=$strX.$str.$strY;
         $this->mrTemplate->addVar("coa_tree", "COA_TREE_STR", $str);                                          
         }
   }
}
?>
