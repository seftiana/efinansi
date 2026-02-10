<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/transaksi_kode_jurnal/business/KodeJurnal.class.php';

class ViewPopupKodeJurnal extends HtmlResponse {

   var $Pesan;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/transaksi_kode_jurnal/template');
      $this->SetTemplateFile('view_popup_kode_jurnal.html');
   }

    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest() {
      $kodeJurnalObj = new KodeJurnal();
      if($_POST || isset($_GET['cari'])) {
         if(isset($_POST['nama'])) {
            $nama = $_POST['nama'];
         } elseif(isset($_GET['nama'])) {
            $nama = Dispatcher::Instance()->Decrypt($_GET['nama']);
         } else {
            $nama = '';
         }
      }

   //view
      $totalData = $kodeJurnalObj->GetCountData($nama);
      $itemViewed = 20;
      $currPage = 1;
      $startRec = 0 ;
      if(isset($_GET['page'])) {
         $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $startRec =($currPage-1) * $itemViewed;
      }
      $dataKodeJurnal = $kodeJurnalObj->getData($startRec, $itemViewed, $nama);
      $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&nama=' . Dispatcher::Instance()->Encrypt($nama) . '&cari=' . Dispatcher::Instance()->Encrypt(1));

      Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage, 'popup-subcontent'), Messenger::CurrentRequest);


      $kodeJurnalDetail          = $kodeJurnalObj->getDetailKodeJurnal($dataKodeJurnal);
      // foreach ($dataKodeJurnal as $jurnal) {
      //    echo $jurnal['id'].'<br />';
      // }
      $return['dataKodeJurnal']              = $dataKodeJurnal;
      $return['start']           = $startRec+1;
      $return['search']['nama']  = $nama;
      $return['detail']['data']  = json_encode($kodeJurnalDetail);
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $search           = $data['search'];
      $dataObject       = $data['detail'];
      $dataJurnal       = array();
      $urlSearch        = Dispatcher::Instance()->GetUrl(
         'transaksi_kode_jurnal',
         'popupKodeJurnal',
         'view',
         'html'
      );
      $this->mrTemplate->AddVar('content', 'NAMA', $search['nama']);
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      // $this->mrTemplate->AddVar('content', 'JURNAL_DETAIL', $dataObject['data']);

      if (empty($data['dataKodeJurnal'])) {
         $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
      } else {
         $decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
         $encPage = Dispatcher::Instance()->Encrypt($decPage);
         $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
         $dataKodeJurnal = $data['dataKodeJurnal'];

         for ($i=0; $i<sizeof($dataKodeJurnal); $i++) {
            $dataJurnal[$dataKodeJurnal[$i]['id']]    = $dataKodeJurnal[$i];
            $no               = $i+$data['start'];
            $dataKodeJurnal[$i]['number'] = $no;
            if ($no % 2 == 0) {
               $dataKodeJurnal[$i]['class_name']   = 'table-common-even';
            }else{
               $dataKodeJurnal[$i]['class_name']   = '';
            }

            $idEnc               = Dispatcher::Instance()->Encrypt($dataKodeJurnal[$i]['id']);

            $this->mrTemplate->AddVars('data_item', $dataKodeJurnal[$i], 'DATA_');
            $this->mrTemplate->parseTemplate('data_item', 'a');
         }
      }

      $this->mrTemplate->AddVars('content', $dataObject, 'JURNAL_');
   }
}
?>
