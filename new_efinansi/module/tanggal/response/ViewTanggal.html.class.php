<?php

class ViewTanggal extends HtmlResponse {   
   var $mComponentParameters;
   
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot') .
         'module/tanggal/template');
      $this->SetTemplateFile('view_tanggal.html');
   }  
  
   function fillComboDate($d, $m, $ystart, $yend, $nullable=true) {            
      if(empty($ystart)) $ystart = '1928';
      if(empty($yend)) $ystart = date('Y');
      
      $xt = 0;
      for ($x=0; $x < 32; $x++) {
         if ($xt < 10) {
            $xtx = '0' . $xt;
         } else {
            $xtx = $xt;
         } 
         $arrdate[] = $xtx;         
         $xt++;
      }
      
      $xm = 0;
      for ($x1=0; $x1 < 13; $x1++) {
         
         switch($xm){
            case  0: $month ='';  break;
            case  1: $month ='Januari';  break;
            case  2: $month ='Februari'; break;
            case  3: $month ='Maret';   break;
            case  4: $month ='April';   break;
            case  5: $month ='Mei';     break;
            case  6: $month ='Juni';    break;
            case  7: $month ='Juli';    break;
            case  8: $month ='Agustus';  break;
            case  9: $month ='September';break;
            case 10: $month ='Oktober';  break;
            case 11: $month ='November'; break;
            case 12: $month ='Desember'; break;
         }        
         $arrmonth[] = $month;
         $xm++;
      }

      $yrStart = $ystart;                          
      $yrEnd = $yend;
      $arryear[] = '0000';
      for ($x1 = $yrEnd ; $x1 >= $yrStart ; $x1--) {                     
            $arryear[] = $x1;
      }
      
      // cleaning up the 0000-00-00 value if NULLABLE is set to false
      if($nullable == '0'){
         unset($arrdate[0]);
         unset($arrmonth[0]);
         unset($arryear[0]);
      }
      
      return $fillCmb = array('hari' => $arrdate, 'bulan' => $arrmonth, 'tahun' => $arryear);
   }
   
   function ProcessRequest() {
      // sample only, delete me
      $msg = Messenger::Instance()->Receive(__FILE__, $this->mComponentName);
      $this->mComponentParameters['selecteddate'] = $msg[0][0];
      $this->mComponentParameters['year_start'] = $msg[0][1];
      $this->mComponentParameters['year_end'] = $msg[0][2];
      $this->mComponentParameters['nullable'] = $msg[0][3];      
      $this->mComponentParameters['status'] = $msg[0][4];      
      $this->mComponentParameters['exclude'] = $msg[0][5];
      
      //echo "123 " . $this->mComponentParameters['exclude'];
      //print_r($msg);
      //

      $yearStart = false;      
      $selectedValue = '';
      if(isset($this->mComponentParameters['selecteddate']) && trim($this->mComponentParameters['selecteddate'])<>'') {
         $selectedValue = $this->mComponentParameters['selecteddate'];                  
      }
      elseif(trim($this->mComponentParameters['selecteddate'])==''){
         $selectedValue = $this->mComponentParameters['selecteddate'];                           
      }
      else{
         $selectedValue = date("Y-m-d");         
      } 
      
      if(isset($this->mComponentParameters['year_start']) && isset($this->mComponentParameters['year_end'])){
         $yearStart = $this->mComponentParameters['year_start'];
         $yearEnd = $this->mComponentParameters['year_end'];
      }
      else{
         $yearStart = $this->mComponentParameters['year_start'];
         $yearEnd = date("Y");
      }
      
      if(isset($this->mComponentParameters['status']) && trim($this->mComponentParameters['status'])!='')
         $status = $this->mComponentParameters['status'];
      
      $nullable = $this->mComponentParameters['nullable'];
      //added by choirul, status combo = exclude
     
      
      
      $day = date("j");
      $mo = date("n");      
      return array("comboDate" => $this->fillComboDate($day, $mo, $yearStart, $yearEnd,$nullable),
                   "selectedval" => $selectedValue,
                   "componentName" => $this->mComponentName,
                   "status" => $status,
                   "exclude" => $this->mComponentParameters['exclude']);     
   }

   function ParseTemplate($data = NULL) {                                                
      //print $data['exclude'];
      
      $selected = explode("-", $data['selectedval']);                             
      $status = $data["status"];
      
      //added by choirul in order to make optional disabled on each component of tanggal controlled by messenger      
      if(is_array($status)):         
         $this->mrTemplate->addVar('content', 'STATUS_TANGGAL', $status[0]);
         $this->mrTemplate->addVar('content', 'STATUS_BULAN', $status[1]);
         $this->mrTemplate->addVar('content', 'STATUS_TAHUN', $status[2]);   
      else:
         $this->mrTemplate->addVar('content', 'STATUS_TANGGAL', $status);
         $this->mrTemplate->addVar('content', 'STATUS_BULAN', $status);
         $this->mrTemplate->addVar('content', 'STATUS_TAHUN', $status);
      endif;   
      
      if($data['exclude'] == 'tanggal'):
         $this->mrTemplate->addVar('content', 'SET_DISPLAY', 'none');
      else:
         if (empty($data['comboDate']['hari'])) {
            $this->mrTemplate->addVar('list_date', 'IS_EMPTY', 'YES');         
         } else {
            $this->mrTemplate->addVar('list_date', 'IS_EMPTY', 'NO');         
            foreach($data['comboDate']['hari'] as $key => $value){                                 
               if($key == 0){
                  $xmd = '';
               }
               else if ($key != 0 && $key < 10) {
                  $xmd = '0' . $key;
               } else {
                  $xmd = $key;
               }
               $this->mrTemplate->addVar('item_date', "COMBO_LABEL", $xmd);
               $this->mrTemplate->addVar('item_date', "COMBO_VALUE", $value);            
               
               # for option if combo selected value is given
               if($value == $selected[2]){
                  $this->mrTemplate->addVar('item_date', "COMBO_LABEL", $selected[2]);
                  $this->mrTemplate->addVar('item_date', "COMBO_VALUE", $selected[2]);
                  $this->mrTemplate->addVar('item_date', "COMBO_SELECTED", 'selected');
               }else{
                  $this->mrTemplate->addVar('item_date', "COMBO_SELECTED", '');
               }

                  
               $this->mrTemplate->parseTemplate('item_date', 'a');           
            }
         }
      endif;
      
      
      if (empty($data['comboDate']['bulan'])) {
         $this->mrTemplate->addVar('list_month', 'IS_EMPTY', 'YES');         
      } else {
         $this->mrTemplate->addVar('list_month', 'IS_EMPTY', 'NO');         
         foreach($data['comboDate']['bulan'] as $key => $value){                                 
            //++$key;            
            if ($key < 10) {
               $xmx = '0' . $key;
            } else {
               $xmx = $key;
            }                   
            $this->mrTemplate->addVar('item_month', "COMBO_LABEL", $value);
            $this->mrTemplate->addVar('item_month', "COMBO_VALUE", $xmx);                        
            
            if($xmx == $selected[1]){
               $this->mrTemplate->addVar('item_month', "COMBO_SELECTED", 'selected');
               $this->mrTemplate->addVar('item_month', "COMBO_LABEL", $value);
               $this->mrTemplate->addVar('item_month', "COMBO_VALUE", $selected[1]);
            }else{
               $this->mrTemplate->addVar('item_month', "COMBO_SELECTED", '');               
            }   
            $this->mrTemplate->parseTemplate('item_month', 'a');                                    
         }
      }    
      
      if (empty($data['comboDate']['tahun'])) {
         $this->mrTemplate->addVar('list_year', 'IS_EMPTY', 'YES');         
      } else {
         $this->mrTemplate->addVar('list_year', 'IS_EMPTY', 'NO');         
         foreach($data['comboDate']['tahun'] as $value){
            if($value == '0000'){
               $label = '';
            }else{
               $label = $value;
            }
            $this->mrTemplate->addVar('item_year', "COMBO_LABEL", $label);
            $this->mrTemplate->addVar('item_year', "COMBO_VALUE", $value);               
            
            if($value == $selected[0]){
               $this->mrTemplate->addVar('item_year', "COMBO_SELECTED", 'selected');
               $this->mrTemplate->addVar('item_year', "COMBO_LABEL", $selected[0]);
               $this->mrTemplate->addVar('item_year', "COMBO_VALUE", $selected[0]);            
            }else{
               $this->mrTemplate->addVar('item_year', "COMBO_SELECTED", '');
            }   
            $this->mrTemplate->parseTemplate('item_year', 'a');            
         }
      }
      $this->mrTemplate->addVar('content', "COMBO_NAME", $data["componentName"]);
      $this->mrTemplate->addVar('content','COMBO_DEFAULT_VALUE',$data['selectedval']);
   }
   
}
?>
