<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/pph_ref_gpp/business/PphGpp.class.php';

class ViewPphGpp extends HtmlResponse {
   private $Post;
   private $Pesan;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/pph_ref_gpp/template');
      $this->SetTemplateFile('view_pph_gpp.html');
   }
   
   function ProcessRequest() {
      //get message
      $msg = Messenger::Instance()->Receive(__FILE__);
      $this->Post = $msg[0][0];
      $this->Pesan = $msg[0][1];		
		if ($_POST['nama_pph_gpp'] != '')
			$cari = $_POST['nama_pph_gpp'];
      else if ($_GET['cari'] != '')
         $cari = $_GET['cari'];
      $ObjPphGpp = new PphGpp();
		$totalData = $ObjPphGpp->JumlahListPphGpp(array($cari));
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
  
		$dataPphGpp = $ObjPphGpp->GetLimitPphGpp(array($cari,$startRec, $itemViewed));
		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&cari=' . $cari);

		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage), Messenger::CurrentRequest);

      $return['pph_gpp'] = $dataPphGpp;
      $return['startrec'] = $startRec;
	   $return['search']['cari'] = $cari;
      return $return;
   }

   function ParseTemplate($data = NULL) {
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'CARI', $search['cari']);
      
		if (isset ($this->Pesan)) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
      }

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('pph_ref_gpp', 'PphGpp', 'view', 'html') );

      $this->mrTemplate->AddVar('content', 'URL_ADD', Dispatcher::Instance()->GetUrl('pph_ref_gpp', 'InputPphGpp', 'view', 'html').'&op='.'add' );

      //mulai bikin tombol delete
      $label = "Golongan Penerima Penghasilan";
      $urlDelete = Dispatcher::Instance()->GetUrl('pph_ref_gpp', 'DeletePphGpp', 'do', 'html');
      $urlReturn = Dispatcher::Instance()->GetUrl('pph_ref_gpp', 'PphGpp', 'view', 'html');
      Messenger::Instance()->Send('confirm', 'ConfirmDelete', 'do', 'html', array($label, $urlDelete, $urlReturn),Messenger::NextRequest);
      $this->mrTemplate->AddVar('content', 'URL_DELETE', Dispatcher::Instance()->GetUrl('confirm', 'ConfirmDelete', 'do', 'html'));
      if (empty($data['pph_gpp'])) {
         $this->mrTemplate->AddVar('pph_gpp', 'IS_DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('pph_gpp', 'IS_DATA_EMPTY', 'NO');
         $no = $data['startrec']+1;

         $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);
         foreach($data['pph_gpp'] as $dt => $item) {

               $this->mrTemplate->AddVar('list_pph_gpp', 'NUMBER', $no);
               $this->mrTemplate->AddVar('list_pph_gpp', 'NAMA_PPH_GPP', $item['gppNama']);
                             
               $this->mrTemplate->AddVar('list_pph_gpp', 'ID', $item['gppId']);
               //zebra tabel
               if ($no % 2 == 1) { 
                  $this->mrTemplate->AddVar('list_pph_gpp', 'DATA_CLASS_NAME', 'table-common-even');
               } else {
                  $this->mrTemplate->AddVar('list_pph_gpp', 'DATA_CLASS_NAME', '');
               }
               $this->mrTemplate->AddVar('list_pph_gpp', 'URL_EDIT', Dispatcher::Instance()->GetUrl('pph_ref_gpp', 'InputPphGpp', 'view', 'html').'&kid='.$item['gppId'].'&op=edit');
               
               $this->mrTemplate->parseTemplate('list_pph_gpp', 'a');                                   $no++;  
         }
          $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no-1);
      }
   }
}
?>