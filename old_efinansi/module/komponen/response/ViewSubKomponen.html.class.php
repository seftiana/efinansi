<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/komponen/business/SubKomponen.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/komponen/business/Komponen.class.php';

class ViewSubKomponen extends HtmlResponse {
   private $Post;
   private $Pesan;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/komponen/template');
      $this->SetTemplateFile('view_sub_komponen.html');
   }
   
   function ProcessRequest() {
      //get message
      $msg = Messenger::Instance()->Receive(__FILE__);
      $this->Post = $msg[0][0];
      $this->Pesan = $msg[0][1];

      //get komponen id
      if ($_POST['komponen_id'] != '')
         $komp_id = $_POST['komponen_id'];
      else if ($_GET['kid'] != '')
         $komp_id = $_GET['kid'];
      else
         $komp_id = $this->Post['komponen_id'];
      //get data komponen from id
      $ObjKomponen = new Komponen();
      $return['komponen'] = $ObjKomponen->GetKomponenFromId($komp_id);
      
      //get paramater pencarian
      if ($_POST['nama_sub_komponen'] != '')
         $cari = $_POST['nama_sub_komponen'];
      else if ($_GET['cari'] != '')
         $cari = $_GET['cari'];
      $ObjSubKomponen = new SubKomponen();
		$totalData = $ObjSubKomponen->JumlahListSubKomponenFromKomponen(array($komp_id,$cari));
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
  
		$dataSubKomponen = $ObjSubKomponen->GetLimitSubKomponenFromKomponen(array($komp_id,$cari,$startRec, $itemViewed));
		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&cari=' . $cari.'&kid='.$komp_id);

		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage), Messenger::CurrentRequest);

      $return['sub_komponen'] = $dataSubKomponen;
      $return['startrec'] = $startRec;

      return $return;
   }

   function ParseTemplate($data = NULL) {
      if (isset ($this->Pesan)) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
      }

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('komponen', 'SubKomponen', 'view', 'html') );
      //set data komponen
      if (!empty($data['komponen'])) {
         $this->mrTemplate->AddVar('content', 'NAMA_KOMPONEN',$data['komponen'][0]['kompNama']); 
         $this->mrTemplate->AddVar('content', 'KOMPONEN_ID',$data['komponen'][0]['kompId']); 
         $this->mrTemplate->AddVar('content', 'URL_ADD', Dispatcher::Instance()->GetUrl('komponen', 'InputSubKomponen', 'view', 'html').'&op='.'add'.'&kid='.$data['komponen'][0]['kompId']);
      }

      $this->mrTemplate->AddVar('content', 'URL_LIST_KOMPONEN', Dispatcher::Instance()->GetUrl('komponen', 'Komponen', 'view', 'html'));

      //mulai bikin tombol delete
      $label = "Sub Komponen";
      $urlDelete = Dispatcher::Instance()->GetUrl('komponen', 'DeleteSubKomponen', 'do', 'html');
      $urlReturn = Dispatcher::Instance()->GetUrl('komponen', 'SubKomponen', 'view', 'html');
      Messenger::Instance()->Send('confirm', 'ConfirmDelete', 'do', 'html', array($label, $urlDelete, $urlReturn.'&kid='.$data['komponen'][0]['kompId']),Messenger::NextRequest);
      $this->mrTemplate->AddVar('content', 'URL_DELETE', Dispatcher::Instance()->GetUrl('confirm', 'ConfirmDelete', 'do', 'html'));

      
      if (empty($data['sub_komponen'])) {
         $this->mrTemplate->AddVar('sub_komponen', 'IS_DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('sub_komponen', 'IS_DATA_EMPTY', 'NO');
         $no = $data['startrec'] + 1;
         $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);
         foreach($data['sub_komponen'] as $dt => $item) {  
               $this->mrTemplate->AddVar('list_sub_komponen', 'NUMBER', $no);
               $this->mrTemplate->AddVar('list_sub_komponen', 'NAMA_SUB_KOMPONEN', $item['subkompNama']);
               $this->mrTemplate->AddVar('list_sub_komponen', 'BIAYA_SUB_KOMPONEN', number_format($item['subkompBiaya'],0,'','.'));
               $this->mrTemplate->AddVar('list_sub_komponen', 'ID', $item['subkompId'].','.$item['subkompKompId']);
               //zebra tabel
               if ($no % 2 == 1) {
                  $this->mrTemplate->AddVar('list_sub_komponen', 'CLASS_NAME', 'table-common-even');
               } else {
                  $this->mrTemplate->AddVar('list_sub_komponen', 'CLASS_NAME', '');
               }
               $this->mrTemplate->AddVar('list_sub_komponen', 'URL_EDIT', Dispatcher::Instance()->GetUrl('komponen', 'InputSubKomponen', 'view', 'html').'&skid='.$item['subkompId'].'&kid='.$item['subkompKompId'].'&op='.'edit');
              
               $this->mrTemplate->parseTemplate('list_sub_komponen', 'a');                                   $no++;  
         }
          $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no-1);
      }
   }
}
?>