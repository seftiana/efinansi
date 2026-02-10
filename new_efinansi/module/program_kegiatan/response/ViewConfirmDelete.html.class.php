<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/sub_program/response/ProcessSubProgram.proc.class.php';

class ViewConfirmDelete extends HtmlResponse {
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot') .
         'module/sub_program/template');
      $this->SetTemplateFile('view_confirm_delete.html');
   }

   function ProcessRequest(){
      //$_GET=$_GET->AsArray();
	 
	  if(isset($_POST))
	    $_POST=$_POST->AsArray();
	  
	  
	  if(isset($_POST['data']['subprogram']['deleted']['id'])) { // kalo ada yang diceklis
	    $id='';
		$nama='';
		$len=sizeof($_POST['data']['subprogram']['deleted']['id']);
		$i=1;
		
	    foreach($_POST['data']['subprogram']['deleted']['id'] as $key => $value) {
		   $id .= Dispatcher::Instance()->Decrypt($value);
		   $nama .= '<br />'.$_POST['data']['subprogram']['deleted']['nama'][$value];
		   if($i != $len) {
		     $id .= ",";
			 $nama .= ' , ';
		   } 
		   $i++;
		}
		$return['id']=Dispatcher::Instance()->Encrypt($id);
		$return['nama']=$nama;		
	  } else { //kalo gak ada yang diceklis yaudah redirect ajah yah :D
	    /*$obj = new ProcessSubProgram();
		$obj->msg="Silahkan lakukan <i>check</i> pada data yang akan dihapus";
		$url=$obj->generateUrl('err',true);	  		
		//return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$url.'&ascomponent=1")');       		
	    $this->RedirectTo($url) ;
		return NULL;  */
	  }  
	  
	  $return['url_delete'] = Dispatcher::Instance()->GetUrl('sub_program', 'deleteSubProgram', 'do', 'html');               
	  
	  return $return;      
    }
   
   
   function ParseTemplate($data = NULL) {      
      $this->mrTemplate->AddVar('content', 'LABEL', 'Sub Program');
      $this->mrTemplate->AddVar('content', 'FORM_ACTION_URL', Dispatcher::Instance()->GetUrl('sub_program', 'deleteSubProgram', 'do', 'html') );
      $this->mrTemplate->AddVar('content', 'URL_KEMBALI', Dispatcher::Instance()->GetUrl('sub_program', 'subProgram', 'view', 'html'));
      $this->mrTemplate->AddVar( 'content', "ID", $data['id']);      
      $this->mrTemplate->AddVar('content', 'CODE', $data['nama']);  
   }
}
?>
