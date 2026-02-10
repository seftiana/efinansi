<?php

require_once $this->mrConfig->mApplication['docroot'] . 'module/report/business/Report.class.php';
require_once $this->mrConfig->mApplication['docroot'] . 'module/user/business/AppUser.class.php';

class ViewTemplate extends HtmlResponse {
   
   function TemplateModule() {
      $this->SetTemplateBasedir($this->mrConfig->mApplication['docroot'] . 'module/template/template');
      $this->SetTemplateFile('view_template.html');
   }

   function ProcessRequest() {
      $rep = new Report();//print_r($_SESSION);
      $userObj = new AppUser();
      $dataUser = $userObj->GetDataUserById(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      
      if (isset($_GET['tab_id'])) {
         $data['data'] = $rep->GetTableById(Dispatcher::Instance()->Decrypt($_GET['tab_id']));         
      } elseif (!$rep->CekLayout($_GET['lay_id'], $dataUser[0]['group_id'])) {
         echo 'Request denied, insufficient access permission!';
         exit;
      } else {
         $data['data'] = $rep->GetLayoutById(Dispatcher::Instance()->Decrypt($_GET['lay_id']));
         $data['graphic'] = $rep->GetGraphicByIdLayout(Dispatcher::Instance()->Encrypt($_GET['lay_id']));
         
      }
//print_r($data);
	  $tanggal = Dispatcher::Instance()->Encrypt($_POST['tanggal']);
	  $bulan = Dispatcher::Instance()->Encrypt($_POST['bulan']);
	  $tahun = Dispatcher::Instance()->Encrypt($_POST['tahun']);
	  $data['tanggal'] = $tanggal;
	  $data['bulan'] = $bulan;
	  $data['tahun'] = $tahun;
      return $data;
   }

   function ParseTemplate($data = NULL) {
      $rep = new Report();
      
      $judul = $data['data']['layout_judul'];
      eval($data['data']['table_param']);
      eval($data['data']['table_php_code']);
	  //print_r($data);exit();
      $this->mrTemplate->addVar('content', 'JUDUL', $judul);
      $this->mrTemplate->addVar('button_cetak', 'JUDUL', $judul.'<br>'.$addJudul);
      $this->mrTemplate->addVar('button_cetak', 'FILTER', $filter);
      $this->mrTemplate->addVar('button_view', 'HASIL', $data['tabel']);
      //print_r($filter);exit();
      $urlGrafik = Dispatcher::Instance()->GetUrl('template', 'reportGraphic', 'view', 'img').'&tab_id='.
         $data['graphic']['graphic_table_id'].$addUrlGraph;
      $urlCetak = Dispatcher::Instance()->GetUrl('template', 'template', 'print', 'html').'&lay_id='.
         $_GET['lay_id'].'&tanggal='.$data['tanggal'].'&bulan='.$data['bulan'].'&tahun='.$data['tahun'].$addUrlGraph;
      $urlExcel = Dispatcher::Instance()->GetUrl('template', 'templateXls', 'view', 'html').'&lay_id='.
         $_GET['lay_id'].'&tanggal='.$data['tanggal'].'&bulan='.$data['bulan'].'&tahun='.$data['tahun'].$addUrlGraph;
      $urlAction = Dispatcher::Instance()->GetUrl('template', 'template', 'view', 'html').'&lay_id='.
         $_GET['lay_id'].$addUrlGraph;
      $setRetribusi = Dispatcher::Instance()->GetUrl('retribusi', 'retribusi', 'popup', 'html').'&izin='.$data['izin'];
      
      if (isset($_GET['tab_id'])) {
         $this->mrTemplate->setAttribute('button_cetak', 'visibility', 'hidden');
         $this->mrTemplate->setAttribute('button_grafik', 'visibility', 'hidden');
         $this->mrTemplate->setAttribute('balik', 'visibility', '');
         $this->mrTemplate->addVar('balik', 'URL_BALIK', Dispatcher::Instance()->GetUrl('template', 'template', 
            'view', 'html').$addUrl);
      } 
      if (isset($data['cetak'])) $this->mrTemplate->setAttribute('button_cetak', 'visibility', 'hidden');
      if ($data['none'] AND $data['tabel']!='') $none=''; else $none='none';
      $this->mrTemplate->addVar('content', 'URL_RETRIBUSI', $setRetribusi);
      $this->mrTemplate->addVar('content', 'NONE', $none);
      $this->mrTemplate->addVar('button_grafik', 'GRAFIK', $rep->Grafik($urlGrafik));
      $this->mrTemplate->addVar('button_cetak', 'URL_CETAK', $urlCetak);
      $this->mrTemplate->addVar('button_cetak', 'URL_EXCEL', $urlExcel);
      $this->mrTemplate->addVar('button_cetak', 'URL_ACTION', $urlAction);
   }
}
?>
