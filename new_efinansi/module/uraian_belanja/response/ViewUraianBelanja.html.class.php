<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/uraian_belanja/business/UraianBelanja.class.php';

class ViewUraianBelanja extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/uraian_belanja/template');
      $this->SetTemplateFile('view_uraian_belanja.html');
   }
   
   function ProcessRequest() {
      
      $Obj = new UraianBelanja();
      
      if (isset($_POST['uraian_belanja'])) $uraianBelanja = $_POST['uraian_belanja']; 
         elseif (isset($_GET['uraian_belanja'])) $uraianBelanja = Dispatcher::Instance()->Decrypt($_GET['uraian_belanja']);
      
      $totalData = $Obj->GetCountUraianBelanja($uraianBelanja);
      
      $itemViewed = 20;
      $currPage = 1;
      $startRec = 0 ;
      
      if(isset($_GET['page'])) {
         $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();  
         $startRec =($currPage-1) * $itemViewed;
      }
      
		$dataUraianBelanja = $Obj->GetUraianBelanja($startRec,$itemViewed, $uraianBelanja);

      if(!empty($dataUraianBelanja)){
         $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 
                  Dispatcher::Instance()->mSubModule, 
                  Dispatcher::Instance()->mAction, 
                  Dispatcher::Instance()->mType. 
                  '&uraianBelanja=' . Dispatcher::Instance()->Encrypt($uraianBelanja)
                  );
         Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', 
            array($itemViewed,$totalData, $url, $currPage), 
            Messenger::CurrentRequest);
      }
      if (isset ($_GET['err'])) {
         $err = explode('|',Dispatcher::Instance()->Decrypt($_GET['err']));
         $return['actionResult']['action'] = $err[0];
         $return['actionResult']['err'] = $err[1];
      }
            
      $return['dataUraianBelanja'] = $dataUraianBelanja;
      $return['start'] = $startRec+1;
      $return['search']['uraianBelanja'] = $uraianBelanja;
      return $return;
   }

   function ParseTemplate($data = NULL) {
      
      $cari=$data['search']['uraianBelanja'];
      $this->mrTemplate->AddVar('content', 'URAIAN_BELANJA', $data['search']['uraianBelanja']);

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('uraian_belanja', 'uraianBelanja', 'view', 'html') );
      $this->mrTemplate->AddVar('content', 'URL_ADD', Dispatcher::Instance()->GetUrl('uraian_belanja', 'inputUraianBelanja', 'view', 'html') );
      if (isset($data['actionResult'])){
         if ($data['actionResult']['err'] == "") {
            $class = 'notebox-done';
            if($data['actionResult']['action'] == 'add') 
               $isiPesan = 'Penambahan';
            else if($data['actionResult']['action'] == 'delete') 
               $isiPesan = 'Penghapusan';  
            else 
               $isiPesan = 'Pengubahan';
            
            $isiPesan .= ' data jenis belanja berhasil dilakukan.';
         } else {
            $class = 'notebox-warning';
            #print_r($data['actionResult']['err']);exit;
            if ($data['actionResult']['err'] == "emptyUraianBelanja") {
               $isiPesan = 'Uraian Belanja Tidak Boleh Kosong.';
            }else{
               if($data['actionResult']['action'] == 'add') 
                  $isiPesan = 'Penambahan';
               else if($data['actionResult']['action'] == 'delete') 
                  $isiPesan = 'Penghapusan';            
               else 
                  $isiPesan = 'Pengubahan';
                  
               $isiPesan .= ' data Uraian Belanja tidak berhasil.';
            }
         }          
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $isiPesan);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $class);
      }
      if (empty($data['dataUraianBelanja'])) {
         $this->mrTemplate->AddVar('data_uraian_belanja', 'URAIAN_BELANJA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_uraian_belanja', 'URAIAN_BELANJA_EMPTY', 'NO');
         $dataUraianBelanja = $data['dataUraianBelanja'];
         $len = sizeof($dataUraianBelanja);
         
         for ($i=0; $i<$len; $i++) {
               $no = $i+$data['start'];
               $dataUraianBelanja[$i]['number'] = $no;
               if ($no % 2 != 0) {
                  $dataUraianBelanja[$i]['class_name'] = 'table-common-even';
               } else {
                  $dataUraianBelanja[$i]['class_name'] = '';
               }

               $idEnc = Dispatcher::Instance()->Encrypt($dataUraianBelanja[$i]['id_uraian_belanja']);
               $dataUraianBelanja[$i]['url_edit'] = Dispatcher::Instance()->GetUrl('uraian_belanja', 'inputUraianBelanja', 'view', 'html') .'&uraianBelanja=' .$idEnc;
     
               $urlAccept = 'uraian_belanja|deleteUraianBelanja|do|html-cari-'.$cari;
               $urlReturn = 'uraian_belanja|uraianBelanja|view|html-cari-'.$cari;
               $label = 'Uraian Belanja';
               $dataName = $dataUraianBelanja[$i]['nama_uraian_belanja'];
               $dataUraianBelanja[$i]['url_delete'] = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html').'&urlDelete='. $urlAccept.'&urlReturn='.$urlReturn.'&id='.$idEnc.'&label='.$label.'&dataName='.$dataName;
               $this->mrTemplate->AddVars('data_uraian_belanja_item', $dataUraianBelanja[$i], '');
               $this->mrTemplate->parseTemplate('data_uraian_belanja_item', 'a');                                            
         }
      } 
   }
}
?>
