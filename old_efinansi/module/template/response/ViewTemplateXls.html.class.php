<?php

require_once $this->mrConfig->mApplication['docroot'] . 'module/report/business/Report.class.php';
require_once $this->mrConfig->mApplication['docroot'] . 'module/user/business/AppUser.class.php';

class ViewTemplateXls extends HtmlResponse {

   function TemplateBase() {
      $this->SetTemplateBasedir($this->mrConfig->mApplication['docroot'] . 'main/template/');  
	   $this->SetTemplateFile('document-common-blank.html');
      $this->SetTemplateFile('layout-common-blank.html');
   }

   function TemplateModule() {
      $this->SetTemplateBasedir($this->mrConfig->mApplication['docroot'] . 'module/template/template');
      $this->SetTemplateFile('xls_template.html');
   }

   function ProcessRequest() {
      $rep = new Report();
      $userObj = new AppUser();
      $dataUser = $userObj->GetDataUserById(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
  	  $data['tanggal'] = Dispatcher::Instance()->Decrypt($_GET['tanggal']);
	  $data['bulan'] = Dispatcher::Instance()->Decrypt($_GET['bulan']);
	  $data['tahun'] = Dispatcher::Instance()->Decrypt($_GET['tahun']);
	  
      header ('Expires: Tue, 27 Jan 1981 09:00:00 GMT');
		header ('Last-Modified: ' . gmdate('D,d M YH:i:s') . ' GMT');
		header ('Cache-Control: no-cache, must-revalidate');
		header ('Pragma: no-cache');
		header ('Content-type: application/vnd.ms-excel');
		header ('Content-Disposition: attachment; filename="laporan.xls"');
		header ("Content-Transfer-Encoding: binary"); 
      
      if (!$rep->CekLayout($_GET['lay_id'], $dataUser[0]['group_id'])) {
         echo 'Request denied, insufficient access permission!';
         exit;
      }  elseif (isset($_GET['lay_id'])) {
         $data['data'] = $rep->GetLayoutById(Dispatcher::Instance()->Decrypt($_GET['lay_id']));
         $data['graphic'] = $rep->GetGraphicByIdLayout(Dispatcher::Instance()->Encrypt($_GET['lay_id']));
         //$data = $rep->GetLayoutById($_GET['lay_id']);
      } else
         $data = $rep->GetTableById($_GET['tab_id']);
      return $data;
   }

   function ParseTemplate($data = NULL) {      
      $rep = new Report();
      $this->mrTemplate->addVar('body', 'TANGGAL_CETAK', $rep->SetTanggal(date('Y-m-d, h:i:s')));
      $judul = $data['data']['layout_judul'];
      eval($data['data']['table_php_code']);

      $this->mrTemplate->addVar('content', 'JUDUL', $judul.'<br>'.$addJudul);    	
      $this->mrTemplate->addVar('content', 'HASIL', $data['tabel']);    	

      $urlGrafik = GTFWConfiguration::GetValue( 'application', 'baseaddress').Dispatcher::Instance()->GetUrl('template', 
         'reportGraphic', 'view', 'img').'&tab_id='.$data['graphic']['GRAPHIC_TABLE_ID'].$addUrlGraph;
      $this->mrTemplate->addVar('button_grafik', 'GRAFIK', $rep->Grafik($urlGrafik));

    	//$image = $this->mrConfig->mApplication['baseaddress'].$this->mrConfig->mApplication['basedir'].'image/logousu_.jpg';
      $this->mrTemplate->addVar('body', 'LOGO', $image);
   }
}
?>
