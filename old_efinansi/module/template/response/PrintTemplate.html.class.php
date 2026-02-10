<?php
 
require_once $this->mrConfig->mApplication['docroot'] . 'module/report/business/Report.class.php';

class PrintTemplate extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir($this->mrConfig->mApplication['docroot'].'module/template/template');
      $this->SetTemplateFile('print_template.html');
   }
   
   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }   

   function ProcessRequest() {
      $rep = new Report();
      $data['data'] = $rep->GetLayoutById(Dispatcher::Instance()->Decrypt($_GET['lay_id']));
      $data['graphic'] = $rep->GetGraphicByIdLayout(Dispatcher::Instance()->Encrypt($_GET['lay_id']));
	  $data['tanggal'] = Dispatcher::Instance()->Decrypt($_GET['tanggal']);
	  $data['bulan'] = Dispatcher::Instance()->Decrypt($_GET['bulan']);
	  $data['tahun'] = Dispatcher::Instance()->Decrypt($_GET['tahun']);
      return $data;
   }

   function ParseTemplate($data = NULL) {   	  
      $rep = new Report();

      $judul = $data['data']['layout_judul'];
      eval($data['data']['table_param']);
      eval($data['data']['table_php_code']);

      $this->mrTemplate->addVar('content', 'JUDUL', $judul.'<br /><h3>'.$addJudul.'</h3>');
      $this->mrTemplate->addVar('content', 'HASIL', $data['tabel']);
      
      $urlGrafik = Dispatcher::Instance()->GetUrl('template', 'reportGraphic', 'view', 'img').'&tab_id='.
         $data['graphic']['graphic_table_id'].$addUrlGraph;
      $this->mrTemplate->addVar('button_grafik', 'GRAFIK', $rep->Grafik($urlGrafik));
      //$this->mrTemplate->addVar('button_grafik', 'GRAFIK', $rep->Grafik($urlGrafik));
   }
}
?>
