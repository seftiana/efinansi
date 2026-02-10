<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
    'module/unitkerja_tree/business/AppUnitkerja.class.php';

class ViewUnitKerjaTree extends HtmlResponse 
{

   protected $Pesan;
   protected $mUnitKerja;

   public function TemplateModule() 
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
        'module/unitkerja_tree/template');
      $this->SetTemplateFile('view_unitkerja_tree.html');
   }
   
   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-blank.html');
      $this->SetTemplateFile('layout-common-blank.html');
   }
   
   public function ProcessRequest() 
   {
      //get pesan
      $msg = Messenger::Instance()->Receive(__FILE__);
      $this->Pesan = $msg[0][1];      
      $this->mUnitKerja = new AppUnitkerja();
      ///$kodeUnit = Dispatcher::Instance()->Decrypt($_GET['kode']);
      ///$namaUnit = Dispatcher::Instance()->Decrypt($_GET['nama']);
      ///$tipeUnit =  Dispatcher::Instance()->Decrypt($_GET['tipeunit']);
      $id = $_POST['dir'];
      //$return['unit_kerja'] = $this->mUnitKerja->GetUnitKerjaByParentId($id,$namaUnit,$tipeUnit,$kodeUnit);
      $return['unit_kerja'] = $this->mUnitKerja->GetUnitKerjaByParentId($id);
      return $return;
   }

   public function ParseTemplate($data = NULL) 
   {
      if(!empty($data['unit_kerja']))
      {
          $count = count($data['unit_kerja']);
          for($i = 0 ; $i < $count ; $i++){
                
                $this->mrTemplate->AddVar('unit_kerja', 'URL_ADD_SUB_UNIT',
                               Dispatcher::Instance()->GetUrl(
				   									            'unitkerja_tree', 
			        								            'inputUnitkerja', 
													            'view', 
                                                                'html') . 
                                                                '&jenis=subunit'.
													            '&parentUnitId=' . 
                                                                $data['unit_kerja'][$i]['unit_id']);
                if($data['unit_kerja'][$i]['parent_id'] == 0){
                    $url_edit=Dispatcher::Instance()->GetUrl(
				  			                    	'unitkerja_tree', 
													'inputUnitkerja', 
													'view', 
													'html') . 
													'&jenis=' . 
													Dispatcher::Instance()->Encrypt('unit') . 
													'&dataId=' . $data['unit_kerja'][$i]['unit_id'];
                } else {
                    $url_edit= Dispatcher::Instance()->GetUrl(
				  			                    	'unitkerja_tree', 
													'inputUnitkerja', 
													'view', 
													'html') . 
													'&jenis=' . 
													Dispatcher::Instance()->Encrypt('subunit') . 
													'&dataId=' . $data['unit_kerja'][$i]['unit_id'];
                }
                 $this->mrTemplate->AddVar('unit_kerja', 'URL_EDIT',$url_edit);                                               
                if($this->mUnitKerja->GetCountChild($data['unit_kerja'][$i]['unit_id']) > 0){
					$this->mrTemplate->addVar('unit_kerja', 'IS_PARENT', 'YES');
					$this->mrTemplate->AddVars('unit_kerja', $data['unit_kerja'][$i], '');
   					$this->mrTemplate->parseTemplate('unit_kerja', 'a');
                } else {
                    $this->mrTemplate->addVar('unit_kerja', 'IS_PARENT', 'NO');
					$this->mrTemplate->AddVars('unit_kerja', $data['unit_kerja'][$i], '');
   					$this->mrTemplate->parseTemplate('unit_kerja', 'a');
                }
                	
          }  
      }
   }
}
