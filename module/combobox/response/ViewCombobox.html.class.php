<?php

class ViewCombobox extends HtmlResponse {

   var $mComponentParameters;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot') .
         'module/combobox/template');
      $this->SetTemplateFile('view_combobox.html');
   }

   function ProcessRequest() {
		
		$msg = Messenger::Instance()->Receive(__FILE__,$this->mComponentName);
		$return['idNama'] = $msg[0][0];
		$return['arrData'] = $msg[0][1];
		$return['id'] = $msg[0][2];
		$return['all'] = $msg[0][3];
		$return['action'] =$msg[0][4];
      return $return;
   }

   function ParseTemplate($data = NULL) {
		
      if(!empty($data)) {
			$all = $data["all"];
			$mTemplate = "combolist";
			$mTemplateID = "COMBO";
			$mArray = $data["arrData"];
			$mId = $data["id"];
			
			
			$this->mrTemplate->addVar("combobox", "COMBO_NAME", $data["idNama"]);
			$this->mrTemplate->addVar("combobox", "ACTION", $data["action"]);
	
         if ($all == "true") {
					$this->mrTemplate->addVar("$mTemplate", "$mTemplateID", "-- SEMUA --");
					$this->mrTemplate->addVar("$mTemplate", $mTemplateID."_VALUE", "all");
               $this->mrTemplate->parseTemplate("$mTemplate","a");
			} else if ($all == "false") {
						$this->mrTemplate->addVar("$mTemplate", "$mTemplateID", "-- PILIH --");
                  $this->mrTemplate->parseTemplate("$mTemplate","a");
			}
				
				
				//print_r($mArray);exit;
		
				for ($i=0;$i<sizeof($mArray);$i++) {
					if (($mArray[$i]['id'] == trim($mId)) && ($mId != "")) {				
						$this->mrTemplate->addVar("$mTemplate", $mTemplateID."_SELECTED", "SELECTED");
					}
					else {
						$this->mrTemplate->addVar("$mTemplate", $mTemplateID."_SELECTED", "");
					}
		
					$this->mrTemplate->addVar("$mTemplate", $mTemplateID."_VALUE", $mArray[$i]['id']);
					$this->mrTemplate->addVar("$mTemplate", "$mTemplateID", $mArray[$i]['name']);
		
					$this->mrTemplate->parseTemplate("$mTemplate","a");
				}
			}
      }
   }
?>
