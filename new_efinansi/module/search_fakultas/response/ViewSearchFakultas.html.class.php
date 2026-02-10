<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/search_fakultas/business/SearchFakultas.class.php';

class ViewSearchFakultas extends HtmlResponse{
	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot').'module/search_fakultas/template');
		$this->SetTemplateFile('view_search.html');
	}
   
   function ProcessRequest(){
   
      $pencarianObj=new SearchFakultas();
      $msg = Messenger::Instance()->Receive(__FILE__, $this->mComponentName);
      
      $isFakultas = $msg[0][0];
       
      $isShow = $msg[0][1];
      $prodiFak=$msg[0][2][0];  
        if($isFakultas == true){      
            //print_r($prodiFak);
            $prodi=$prodiFak['prodi'];
            $fakultas=$prodiFak['fakProdi'];
            $dataProdi=$pencarianObj->GetDataProdi();
            $dataFakultas=$pencarianObj->GetFakultas();
            foreach($dataProdi as $value ){
               if (isset($dataProdi[0])) $dataProdi = array();
               $dataProdi[$value['prodiFakultasId']][] = array($value['id'],$value['name']);
               
               if ($value['prodiFakultasId'] == $fakultas)
                  $arrProdi[] = array('id'=>$value['id'], 'name'=>$value['name']);
            }
            $return['json']['prodi']=json_encode($dataProdi);
            Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'program_studi',
               array('program_studi',$arrProdi,$prodi,'true','id="comboProdi" '), Messenger::CurrentRequest);
               
            Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'fakultas',array('fakultas',$dataFakultas,$fakultas,'false','id="fakultas"  onchange="updateProdi();"'), Messenger::CurrentRequest);
            $prodiShow=true;
            
         }else{
            $prodi=$prodiFak['prodi'];
            $dataProdi=$pencarianObj->GetDataProdiAll();
            $return['json']['prodi']=json_encode($dataProdi);
            Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'program_studi',array('program_studi',$dataProdi,$prodi,'true','id="comboProdi"'), Messenger::CurrentRequest);
            $prodiShow=true;            
         }
         
      $return['show']['prodiShow']=$prodiShow;
      $return['isFakultas']=$isFakultas;
      return $return;
   }
   
   function ParseTemplate($data=null){
      
		$this->mrTemplate->AddVars('content', $data['json'], 'JSON_');
      $this->mrTemplate->AddVar('content','URL_SEARCH',$urlSearch);
      $show=$data['show'];
      
      if($show['prodiShow']==true){
         $this->mrTemplate->SetAttribute('prodi', 'visibility', 'visible');
      }  
      
      if($data['isFakultas']!==true){
         $this->mrTemplate->AddVar('prodi', 'DISPLAY_FAK', 'display:none');
      }
      
   }

}
?>