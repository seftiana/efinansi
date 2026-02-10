<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/skenario/business/Skenario.class.php';

class ProcSkenario {
   
   protected $msg;
   public $data;  
   
   public $moduleName = 'skenario';
   public $moduleHome = 'skenario';
   public $moduleInput = 'inputSkenario';
   public $moduleAdd = 'addSkenario';
   public $moduleUpdate = 'updateSkenario';
   public $moduleDelete = 'deleteSkenario';
   
   
   public $db;  //yang berhubungan ke database
   
    function ProcSkenario(){ //constructor	  	  
	  $this->db = new Skenario;	  	  
	  $this->data = $this->getPOST();
    }
    
    function getPOST() {
	$data=false;
	
	 if(isset($_POST['data'])) {
         if(is_object($_POST['data']))	  
	        $data=$_POST['data']->AsArray();		 
		 else
		    $data=$_POST['data'];	
         			
	     
		 if(isset($data['debet']['tambah'])) {
		    $i=0;
		    foreach($data['debet']['tambah']['id'] as $key => $val) {
			   $data['debet']['tambah'][$i]['id']=$val;
			   $data['debet']['tambah'][$i]['kode']=$data['debet']['tambah']['kode'][$key];
			   $data['debet']['tambah'][$i]['nama']=$data['debet']['tambah']['nama'][$key];			   
			   $data['debet']['tambah'][$i]['prosentase']=$data['debet']['tambah']['prosentase'][$key];			   
			   $i++;
			}
			unset($data['debet']['tambah']['id']);
			unset($data['debet']['tambah']['kode']);
			unset($data['debet']['tambah']['nama']);
			unset($data['debet']['tambah']['prosentase']);
		 }
		 if(isset($data['kredit']['tambah'])) {
		    $i=0;
		    foreach($data['kredit']['tambah']['id'] as $key => $val) {
			   $data['kredit']['tambah'][$i]['id']=$val;
			   $data['kredit']['tambah'][$i]['kode']=$data['kredit']['tambah']['kode'][$key];
			   $data['kredit']['tambah'][$i]['nama']=$data['kredit']['tambah']['nama'][$key];
			   $data['kredit']['tambah'][$i]['prosentase']=$data['kredit']['tambah']['prosentase'][$key];			   
			   $i++;
			}
			unset($data['kredit']['tambah']['id']);
			unset($data['kredit']['tambah']['kode']);
			unset($data['kredit']['tambah']['nama']);
			unset($data['kredit']['tambah']['prosentase']);
		 }
	   }
	   
	   return $data;
    }    
   
    function Add() {
	      
		  if(isset($this->data['skenario']['id']))
		     $grp = '&grp='.Dispatcher::Instance()->Encrypt($this->data['skenario']['id']);
		  else
		     $grp='';
			
		  if($this->validation('Penambahan')){
		    $add=$this->db->DoAdd($this->data,$msg);	
            if($add) {
			   $this->msg='Penambahan data berhasil dilakukan';
			   $urlRedirect=$this->generateUrl('msg',false,null,$grp);			   
            } else {
			   $this->msg='Penambahan data gagal dilakukan <br />';
			   if(is_array($msg))
			      $this->msg.= $msg['debet']['msg'].$msg['kredit']['msg'];
			   else
			      $this->msg.= $msg;
				
			   $urlRedirect=$this->generateUrl('err',false,null,$grp);
			   //$ret = false;
            }			
			
		  } else {				
		    $urlRedirect = $this->generateUrl('err',false,null,$grp);		
		  }	
		  
	   
	   return $urlRedirect;
	}
	
	
	
	function Delete () {	    
		if(isset($_POST['idDelete'])) {
		   $grp=Dispatcher::Instance()->Decrypt($_POST['idDelete']);		   
		   		   
		   $del=$this->db->DoDelete($grp);
		   if($del) {
		     $this->msg='Penghapusan data berhasil dilakukan';
		     $urlRedirect = $this->generateUrl('msg',false,$this->moduleHome);
		     
		   } else {
		     $this->msg='Penghapusan data gagal dilakukan';
		     $urlRedirect = $this->generateUrl('err',false,$this->moduleHome);
		   }
		} else {
		   $this->msg='Penghapusan data gagal dilakukan';
		   $urlRedirect = $this->generateUrl('err',false,$this->moduleHome);
		}
		return $urlRedirect;
	}
	
	function DeleteDetail () {	    
		if(isset($_POST['idDelete'])) {
		   $tmpgrp=Dispatcher::Instance()->Decrypt($_POST['idDelete']);		   
		   $tmpgrp = explode('*',$tmpgrp);
		   $grp= $tmpgrp[0];
		   $id = Dispatcher::Instance()->Encrypt($tmpgrp[1]);
		   		   
		   $del=$this->db->SkenarioDetail->DoDelete($grp);
		   if($del) {
		     $this->msg='Penghapusan data berhasil dilakukan';
		     $urlRedirect = $this->generateUrl('msg',false,'skenarioDetail','&grp='.$id);
		     
		   } else {
		     $this->msg='Penghapusan data gagal dilakukan';
		     $urlRedirect = $this->generateUrl('err',false,'skenarioDetail','&grp='.$id);
		   }
		} else {
		   $this->msg='Penghapusan data gagal dilakukan';
		   $urlRedirect = $this->generateUrl('err',false,'skenarioDetail','&grp='.$id);
		}		
		return $urlRedirect;
	}
	
	
	
	
	function Update() {	   
         if($this->validation('Perubahan')) {	
           $this->data['tambah']['id']= Dispatcher::Instance()->Decrypt($this->data['tambah']['id']);
		   $update=$this->db->DoUpdate($this->data['tambah']);
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
	  
	   return $urlRedirect;
	}
	
	
	
	function validation($action) {
	   
	    $this->msg='';
	    if(!isset($_POST['data'])) { //kalo gak ada data yang di POST apa yang mau di  validasi
		   $this->msg=$action.' data gagal dilakukan ';
		   return false;
		}

        if(trim($this->data['skenario']['nama'])=='')
           $this->msg .= 'Nama Skenario tidak boleh kosong <br />';		
		
		$debetdatalist=0;//menghitung prosentase debet yang sudah ada di database sebelumnya
		if(isset($this->data['debet']['datalist']['prosentase']) && is_array($this->data['debet']['datalist']['prosentase'])) {
		   foreach($this->data['debet']['datalist']['prosentase'] as $val){
		      $debetdatalist += $val;
		   }
		}
		
		   $totaldebet=0;
		   $errdebet = false;
		   if(isset($this->data['debet']['tambah'])) {
		      foreach($this->data['debet']['tambah'] as $key => $val) {	
			    
                
			     if(trim($val['prosentase'])=='') {
				    $this->msg .= 'Prosentase Debet Akun <b>'.$val['nama'].'</b>  tidak boleh Kosong <br />';
					$errordebet =true;
				 } elseif(!is_numeric($val['prosentase'])) {
				    $this->msg .= 'Prosentase Debet Akun <b>'.$val['nama'].'</b>  harus berupa angka <br />';
					$errordebet = true;
				 }
				 $totaldebet += $val['prosentase'];
			  }
		   }
		   
		   //jumlahkan semua prosentase dari data yang ada dan data yang akan ditambahkan
		   $totaldebet += $debetdatalist;
		   
		   if(!$errordebet) 
		      if($totaldebet != 100 )
			     $this->msg .= 'Prosentase Debet harus 100% <br />';
		   
		   
		   $kreditdatalist=0;//menghitung prosentase debet yang sudah ada di database sebelumnya
		   if(isset($this->data['kredit']['datalist']['prosentase']) && is_array($this->data['kredit']['datalist']['prosentase'])) {
		      foreach($this->data['kredit']['datalist']['prosentase'] as $val){
		         $kreditdatalist += $val;
		      }
		   }
		   
		   $totalkredit=0;
		   $errkredit = false;
		   if(isset($this->data['kredit']['tambah'])) {
		      foreach($this->data['kredit']['tambah'] as $key => $val) {
			     if(trim($val['prosentase'])=='') {
				    $this->msg .= 'Prosentase Kredit Akun <b>'.$val['nama'].'</b>  tidak boleh Kosong <br />';
					$errorkredit =true;
				 } elseif(!is_numeric($val['prosentase'])) {
				    $this->msg .= 'Prosentase Kredit Akun <b>'.$val['nama'].'</b>  harus berupa angka <br />';
					$errorkredit =true;
				 }
				 $totalkredit += $val['prosentase'];
			  }
		   }
		   
		   //jumlahkan semua prosentase dari data yang ada dan data yang akan ditambahkan
		   $totalkredit += $kreditdatalist;
		   
		   if(!$errorkredit) 
		      if($totalkredit != 100 )
			     $this->msg .= 'Prosentase Kredit harus 100% <br />';
		//}      
          
		
		if($this->msg=='')
   		   return true;
		else 
		   return false; 
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
