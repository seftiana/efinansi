<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/penerimaan_map_rkakl/business/PenerimaanMap.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewDetilPenerimaanMapRkakl extends HtmlResponse {

   var $Pesan;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/penerimaan_map_rkakl/template');
      $this->SetTemplateFile('view_detil_penerimaan_map_rkakl.html');
   }
   
   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
   
   function ProcessRequest() {
      $Obj = new PenerimaanMap();
      
      $dataId = Dispatcher::Instance()->Decrypt($_GET['dataId']);
      $data_list = $Obj->GetDataDetil($dataId);
      
      $msg = Messenger::Instance()->Receive(__FILE__);
      $this->Pesan = $msg[0][1];
      $this->css = $msg[0][2];

      $return['detil'] = $data_list;
      $return['start'] = $startRec+1;
 
      return $return;
   }
   
   function ParseTemplate($data = NULL) {
      
      if (empty($data['detil'])) {
         $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
         $data_list = $data['detil'];

         for ($i=0; $i<sizeof($data_list); $i++) {
            $no = $i+$data['start'];
            $data_list[$i]['number'] = $no;
            if ($no % 2 != 0) $data_list[$i]['class_name'] = 'table-common-even';
            else $data_list[$i]['class_name'] = '';
            
            if($i == 0) $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);           
            if($i == sizeof($data_list)-1) $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
            $idEnc = Dispatcher::Instance()->Encrypt($data_list[$i]['id']);
			
				if($data_list[$i]['nominal'] < 0)
					$data_list[$i]['nominal'] = '('.number_format(str_replace('-','',$data_list[$i]['nominal']), 2, ',', '.').')';
				else
					$data_list[$i]['nominal'] = number_format($data_list[$i]['nominal'], 2, ',', '.');
					
				$data_list[$i]['tanggal'] = IndonesianDate($data_list[$i]['tanggal'],'yyyy-mm-dd'); 	
            $this->mrTemplate->AddVars('data_item', $data_list[$i], 'DATA_');
            $this->mrTemplate->parseTemplate('data_item', 'a');    
         }
      }
   }
}
?>