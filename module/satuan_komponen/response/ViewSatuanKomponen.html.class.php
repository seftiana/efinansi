<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/satuan_komponen/business/SatuanKomponen.class.php';

class ViewSatuanKomponen extends HtmlResponse {
   private $Post;
   private $Pesan;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/satuan_komponen/template');
      $this->SetTemplateFile('view_satuan_komponen.html');
   }
   
   function ProcessRequest() {
      //get message
      $msg = Messenger::Instance()->Receive(__FILE__);
      $this->Post = $msg[0][0];
      $this->Pesan = $msg[0][1];

      if ($_POST['nama_satuan_komponen'] != '')
         $cari = $_POST['nama_satuan_komponen'];
      else if ($_GET['cari'] != '')
         $cari = $_GET['cari'];
      $ObjSatuanKomponen = new SatuanKomponen();
		$totalData = $ObjSatuanKomponen->JumlahListSatuanKomponen(array($cari));
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
  
		$dataSatuanKomponen = $ObjSatuanKomponen->GetLimitSatuanKomponen(array($cari,$startRec, $itemViewed));
		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&cari=' . $cari);

		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage), Messenger::CurrentRequest);

      $return['satuan_komponen'] = $dataSatuanKomponen;
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

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('satuan_komponen', 'SatuanKomponen', 'view', 'html') );

      $this->mrTemplate->AddVar('content', 'URL_ADD', Dispatcher::Instance()->GetUrl('satuan_komponen', 'InputSatuanKomponen', 'view', 'html').'&op='.'add' );

      //mulai bikin tombol delete
      $label = "Manajemen Satuan komponen";
      $urlDelete = Dispatcher::Instance()->GetUrl('satuan_komponen', 'DeleteSatuanKomponen', 'do', 'html');
      $urlReturn = Dispatcher::Instance()->GetUrl('satuan_komponen', 'SatuanKomponen', 'view', 'html');
      Messenger::Instance()->Send('confirm', 'ConfirmDelete', 'do', 'html', array($label, $urlDelete, $urlReturn),Messenger::NextRequest);
      $this->mrTemplate->AddVar('content', 'URL_DELETE', Dispatcher::Instance()->GetUrl('confirm', 'ConfirmDelete', 'do', 'html'));
      if (empty($data['satuan_komponen'])) {
         $this->mrTemplate->AddVar('satuan_komponen', 'IS_DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('satuan_komponen', 'IS_DATA_EMPTY', 'NO');
         $no = $data['startrec']+1;

         $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);
         foreach($data['satuan_komponen'] as $dt => $item) {

               $this->mrTemplate->AddVar('list_satuan_komponen', 'NUMBER', $no);
               $this->mrTemplate->AddVar('list_satuan_komponen', 'NAMA_SATUAN_KOMPONEN', $item['satkompNama']);
                             
               $this->mrTemplate->AddVar('list_satuan_komponen', 'ID', $item['satkompId']);
               //zebra tabel
               if ($no % 2 == 1) { 
                  $this->mrTemplate->AddVar('list_satuan_komponen', 'DATA_CLASS_NAME', 'table-common-even');
               } else {
                  $this->mrTemplate->AddVar('list_satuan_komponen', 'DATA_CLASS_NAME', '');
               }
               $this->mrTemplate->AddVar('list_satuan_komponen', 'URL_EDIT', Dispatcher::Instance()->GetUrl('satuan_komponen', 'InputSatuanKomponen', 'view', 'html').'&kid='.$item['satkompId'].'&op=edit');
               
               $this->mrTemplate->parseTemplate('list_satuan_komponen', 'a');                                   $no++;  
         }
          $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no-1);
      }
   }
}
?>