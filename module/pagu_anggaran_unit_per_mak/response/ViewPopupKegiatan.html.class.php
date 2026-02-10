<?php
/**
* @package ViewPopupRkaklKegiatan
* @copyright Copyright (c) PT Gamatechno Indonesia
* @Analyzed By Dyan Galih <galih@gamatechno.com>
* @author Dyan Galih <galih@gamatechno.com>
* @version 0.1
* @startDate 2011-01-01
* @lastUpdate 2011-01-01
* @description View Popup Rkakl Kegiatan
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/pagu_anggaran_unit_per_mak/business/RkaklKegiatan.class.php';

class ViewPopupKegiatan extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot').'module/pagu_anggaran_unit_per_mak/template');
      $this->SetTemplateFile('view_popup_kegiatan.html');
   }

   function ProcessRequest() {
      $objKegiatan = new RkaklKegiatan();

      $kode = $_POST['nama'];

      if(isset($_GET['nama']))
         $kode = Dispatcher::Instance()->Decrypt($_GET['nama']);

      #set default pagging
      $limit = 20;
      $page = 0;
      $offset = 0;

      if(isset($_GET['page'])){
         $page = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset = ($page - 1) * $limit;
      }

      #fetch data
      $return['data'] = $objKegiatan->GetRkaklKegiatan($kode, $kode, $offset, $limit);

      #fethc numrows
      $numrows = $objKegiatan->GetCount();// fetch here;

      #pagging url
      $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType)
         .'&nama='.Dispatcher::Instance()->Encrypt($kode)
         ;

      $destination_id = "popup-subcontent"; # options: {popup-subcontent,subcontent-element}

      #send data to pagging component
      Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top',
      array($limit,$numrows, $url, $page, $destination_id),
      Messenger::CurrentRequest);

      #send data to parse method
      $return['start'] = $offset+1;
      $return['page'] = $page;
      $return['numrows'] = $numrows;

      return $return;
   }

   function ParseTemplate($data = NULL) {

      $urlKegiatan = Dispatcher::Instance()->GetUrl('rkakl_transaksi', 'popupKegiatan', 'view', 'html');
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlKegiatan);

      if (empty($data['data'])) {
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
         } else {
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');

            $dataList = $data['data'];

         for ($i=0; $i<sizeof($dataList); $i++) {
            $no = $i+$data['start'];
            $dataList[$i]['no'] = $no;

            if ($no % 2 != 0)
               $dataList[$i]['class_name'] = 'table-common-even';
            else
               $dataList[$i]['class_name'] = '';
			
			//$dataList[$i]['nama']			= "nama'ku";
			$dataList[$i]['link']			= str_replace("'","\'",$dataList[$i]['nama']);
            $this->mrTemplate->AddVars('data_item', $dataList[$i], '');
            $this->mrTemplate->parseTemplate('data_item', 'a');
         }
      }
   }
}
?>