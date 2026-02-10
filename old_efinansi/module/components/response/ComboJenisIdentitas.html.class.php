<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/components/business/JenisIdentitas.class.php';

class ComboJenisIdentitas extends HtmlResponse {
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot') .
         'module/components/template');
      $this->SetTemplateFile('combo_jenis_identitas.html');
   }

   function ProcessRequest() {
      $objBusiness = new JenisIdentitas();      
      $msg = Messenger::Instance()->Receive(__FILE__, $this->mComponentName);
      
      $selectedValue = '';
      $status = '';
      
      if(!empty($msg)) {
         $selectedValue = $msg[0][0];         
         $status = $msg[0][1];
      } else {        
         $selectedValue = $this->mComponentParameters['selected'];         
         $status = $this->mComponentParameters['status'];
      }      
     
      
      return array("result" => $objBusiness->GetAllJenisIdentitas(),
                   "selectedval" => $selectedValue,
                   "status" => $status
                   );
   }

   function ParseTemplate($data = NULL) {           
      
      //$this->mrTemplate->addVar('content', 'COMBO_STATUS', $data['status']); 
      
      if (empty($data['result'])) {
         $this->mrTemplate->addVar('list_jenis_identitas', 'IS_EMPTY', 'YES');         
      } else {
         $this->mrTemplate->addVar('list_jenis_identitas', 'IS_EMPTY', 'NO');      
         
         if($data['selectedval'] == 'unreg') $this->mrTemplate->addVar('content', "COMBO_SELECTED", 'selected');               
         
         foreach($data['result'] as $key => $value) {            
            $this->mrTemplate->addVar('item_jenis_identitas', "COMBO_LABEL", $value['Nama']);
            $this->mrTemplate->addVar('item_jenis_identitas', "COMBO_VALUE", $value['Id']);            
            
            if($value['Id'] == $data['selectedval'])
               $this->mrTemplate->addVar('item_jenis_identitas', "COMBO_SELECTED", 'selected');
            else
               $this->mrTemplate->addVar('item_jenis_identitas', "COMBO_SELECTED", '');
               
            
            
            $this->mrTemplate->parseTemplate('item_jenis_identitas', 'a');
         }
      }   
   }
}
?>
