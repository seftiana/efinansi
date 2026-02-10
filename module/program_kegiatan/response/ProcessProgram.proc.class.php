<?php
/**
* @module program
* @author Rendi Kasigi,
* @mail to rkasigi@gmail.com
* @copyright 2008&copy;Gamatechno
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/program_kegiatan/business/Program.class.php';

class ProcessProgram {
   
   var $msg;
   var $data;
   protected $moduleName = 'program_kegiatan';
   
    function ProcessProgram(){
	  if(isset($_POST['data']))
	    if(is_object($_POST['data'])) {
		  $this->data = $_POST['data']->AsArray();
		} else 
	      $this->data=$_POST['data'];		 
    }
   
    function Add () {
	   $programObj = new Program();
	   if(isset($_POST['btnsimpan'])){
	      //kalo yang diklik tombol simpan
		  if($this->validation('Penambahan')){
		    $add=$programObj->DoAddProgram($this->data['program']);	
            if($add) {
			   $this->msg='Penambahan data berhasil dilakukan';
			   $urlRedirect=$this->generateUrl('msg');
            } else {
			   $this->msg='Penambahan data gagal dilakukan';
			   $urlRedirect=$this->generateUrl('err');
            }			
			
		  } else {			
		    $urlRedirect = $this->generateUrl('err');
		  }
		  
	   } elseif ($this->data['flag']['renstra']) { //submit dari combox
	      Messenger::Instance()->Send($this->moduleName, 'inputProgram', 'view', 'html', array($this->data,$type,''),Messenger::NextRequest);				
		  $urlRedirect = Dispatcher::Instance()->GetUrl($this->moduleName, 'inputProgram', 'view', 'html');		     
	   
	   } else {  //kalo yang ditekan tombol balik	     
		  $urlRedirect = Dispatcher::Instance()->GetUrl('program_kegiatan', 'programKegiatan', 'view', 'html') ;
	   }
	   return $urlRedirect;
	}
	
	function Delete () {
	    
		if(isset($_POST['idDelete'])) {
		   
		   $grp=$_POST['idDelete'];
		   $programObj= new Program();
		   $del=$programObj->DoDeleteProgram($grp);
		   if($del) {
		     $this->msg='Penghapusan data berhasil dilakukan';
		     $urlRedirect = $this->generateUrl('msg',true);
		     
		   } else {
		     $this->msg='Penghapusan data gagal dilakukan';
		     $urlRedirect = $this->generateUrl('err',true);
		   }
		} else {
		   $this->msg='Penghapusan data gagal dilakukan';
		   $urlRedirect = $this->generateUrl('err',true);
		}
		return $urlRedirect;
	}
	
	function Update() {
	    $programObj = new Program();
		
	   if(isset($_POST['btnsimpan'])){
         if($this->validation('Perubahan')) {
		   
		   $update=$programObj->DoUpdateProgram($this->data['program']);
			if($update) {
			  $this->msg='Perubahan data berhasil dilakukan'; 		   
		      $urlRedirect = $this->generateUrl('msg');		  
			} else {
			  $this->msg='Perubahan data gagal dilakukan silahkan ulangi lagi'; 		   
		      $urlRedirect = $this->generateUrl('err');		  
			}	   
		 } else {		    		   
		   $urlRedirect = $this->generateUrl('err');
		 }		  
	   } elseif ($this->data['flag']['renstra']) { //submit dari combox
	      Messenger::Instance()->Send($this->moduleName, 'inputProgram', 'view', 'html', array($this->data,$type,''),Messenger::NextRequest);				
		  $urlRedirect = Dispatcher::Instance()->GetUrl($this->moduleName, 'inputProgram', 'view', 'html');		     
	   
	   } else {
	      //kalo yang ditekan tombol balik
		  $urlRedirect = Dispatcher::Instance()->GetUrl('program_kegiatan', 'programKegiatan', 'view', 'html') ;
	   }
	   return $urlRedirect;
	}
	
	function validation($action) {	   
	   
	    $this->msg='';
	    if(!isset($_POST['data'])) {	       
		   $this->msg=$action.' data gagal dilakukan ';
		   return false;
		}			
	  
	    if(isset($this->data['program']['id']))
	      $this->data['program']['id']= Dispatcher::Instance()->Decrypt($this->data['program']['id']);
	      
        if(trim($this->data['program']['ta_id'])=='')	   
		  $this->msg .= 'Tahun Anggaran tidak boleh kosong <br />';
	    
		if(!isset($this->data['program']['nomor']) || trim($this->data['program']['nomor']) == '')
	      $this->msg.='Nomor Program Tidak Boleh Kosong <br />';
	    /*else if(!is_numeric($this->data['program']['nomor']))
	            $this->msg.='Nomor Program Harus Berupa Angka <br />';
		*/
	    if(!isset($this->data['program']['nama']) || trim($this->data['program']['nama']) == '')
	      $this->msg.='Nama Program Tidak Boleh Kosong';
		 
      		 
		if($this->msg == '') { //validasi apakah duplicate atau tidak
		   $programObj= new Program();		   
		   if($programObj->isDuplicateNomor($this->data['program']['id'],$this->data['program']['nomor'],$this->data['program']['ta_id']))
		      $this->msg .='Nomor sudah ada dalam database silahkan gunakan yang lain';
		}
		 
		if($this->msg=='')
   		   return true;
		else 
		   return false; 			   
	}
	
	function generateUrl($type,$isHome=false){
	    //parameter isHome ditujukan bahwa url diredirect ke home module apapun bentuk pesannya
	    if(isset($_GET['grp']))
	      $grp='&grp='.Dispatcher::Instance()->Encrypt($this->data['program']['id']);
	    else
		  $grp='';
		
		if($type=='msg' || $isHome ) $submodule='programKegiatan';
		else $submodule='inputProgram';
		
		//echo $submodule;exit;
		Messenger::Instance()->Send($this->moduleName, $submodule, 'view', 'html', array($this->data,$type,$this->msg),Messenger::NextRequest);				
		$urlRedirect = Dispatcher::Instance()->GetUrl($this->moduleName, $submodule, 'view', 'html').$grp;
		
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
