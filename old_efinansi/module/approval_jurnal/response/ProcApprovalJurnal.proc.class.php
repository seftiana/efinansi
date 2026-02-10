<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/approval_jurnal/business/ApprovalJurnal.class.php';

class ProcApprovalJurnal {
   
   protected $msg;
   public $data;  
   
   public $moduleName = 'approval_jurnal';
   public $moduleHome = 'approvalJurnal';
   //public $moduleInput = 'inputApprovalJurnal';
   public $moduleAdd = 'addApprovalJurnal';
   //public $moduleUpdate = 'updateApprovalJurnal';
   //public $moduleDelete = 'deleteApprovalJurnal';
   
   
   public $db;  //yang berhubungan ke database
   
    function ProcApprovalJurnal(){ //constructor	  	  
	   $this->db = new ApprovalJurnal;
    }
       
    function Add() {
       $data = $_POST->AsArray();
       $grp='';
       $urlRedirect=$this->generateUrl('err',false,null,$grp);
       if(empty($data['id'])) {
			   $this->msg='Silakan Pilih Salah Satu Data';
       } else {
            $add = $this->db->DoAdd();
            if($add) {
               //berhasil
			      $this->msg='Approval berhasil dilakukan';
			      $urlRedirect=$this->generateUrl('msg',false,null,$grp);
            } else {
               //gagal
			      $this->msg='Approval gagal dilakukan';
            }
       }
       return $urlRedirect;
	}
		
	
	function generateUrl($type,$isHome=false,$url = null , $additional=null){		 
		 
	  if(!is_null($url))
	     $submodule = $url;
	  elseif($type=='msg' || $isHome ) $submodule=$this->moduleHome;
	  else $submodule= $this->moduleInput;				
	  
	  
	  Messenger::Instance()->Send($this->moduleName, $submodule, 'view', 'html', array($this->data,$type,$this->msg),Messenger::NextRequest);				
	  $urlRedirect = Dispatcher::Instance()->GetUrl($this->moduleName, $submodule, 'view', 'html');
	  if(!is_null($additional))
	     $urlRedirect .= $additional;
	  return $urlRedirect;
	}	
	
	function parsingUrl($file) {
	    $msg = Messenger::Instance()->Receive($file);	
		
		if(!empty($msg)) {		
		   $tmp['data']=$msg[0][0];
		   $tmp['msg']['action']=$msg[0][1];
		   $tmp['msg']['message']=$msg[0][2];
		   return $tmp;
		} else {
		  return false;
		}	    
	}
}
?>
