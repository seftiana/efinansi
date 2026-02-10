<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/program_kegiatan/business/KomponenKegiatan.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/program_kegiatan/business/SubKegiatan.class.php';

class ProcessKomponenKegiatan {
   
   var $msg;
   protected $data;
   protected $moduleName='program_kegiatan';
   protected $SubKegiatan ;
   
   public $KomponenKegiatan;
   
    function ProcessKomponenKegiatan(){ //constructor
	  if(isset($_POST['data']))
         if(is_object($_POST['data']))	  
	        $this->data=$_POST['data']->AsArray();		 
		 else
		    $this->data=$_POST['data'];		 
	  
	  
	  $this->KomponenKegiatan = new KomponenKegiatan;
	  $this->SubKegiatan = new SubKegiatan;
    }
   
    function Add () {	    
	   if(isset($_POST['btnsimpan'])){
	      //kalo yang diklik tombol simpan		  
		  if($this->validation('Penambahan')){
		    $add=$this->KomponenKegiatan->DoAdd($this->data['komponen_id'],
			                                    $this->data['kegref_id'],
												$this->data['komponen_nominal']
			                                   );											
            if($add) {
            	
             //cek , jika input data unit kerja tidak kosong maka simpan unit kerja
             $kompId  =$this->data['komponen_id'];
			   if(count($this->data['unitkerjaid']) != 0){
			   		//cek isi tabel finansi_pa_komponen_unit_kerja
			   		if($this->KomponenKegiatan->GetCountKomponenUnitKerja($kompId) > 0){
			   			//jika ada data maka hapus dulu
		   				$this->KomponenKegiatan->DoDeleteKomponenUnitKerja($kompId);
		   			}
			   		
		   			foreach($this->data['unitkerjaid'] as $key => $value)
	   				{
		   				$this->KomponenKegiatan->DoAddKomponenUnitKerja($kompId,$value);
		   			}
		   		} else {
		   			//jika data input unit kerja kosong maka cek tabel
					// finansi_pa_komponen_unit_kerja
		   			if($this->KomponenKegiatan->GetCountKomponenUnitKerja($kompId) > 0){
		   					//jika data input check unit kerja  kossong maka hapus data
		   				$this->KomponenKegiatan->DoDeleteKomponenUnitKerja($kompId);
		   			}
		   		}
		   		
		   		
		   		//end unit kerja
		   		
			   $this->msg['message']='Penambahan data berhasil dilakukan';
			   $this->msg['action']='msg';		   
			   
            } else {
			   $this->msg['message'] ='Penambahan data gagal dilakukan <br />';
			   $this->msg['message'] .= $pesan;
			   $this->msg['action']  ='err';
			   
            }			
			
		  } else {		    
			$add = false;
		  }	
		  /*	  
		  echo '<pre>';
		  print_r($this->data);
		  echo '</pre>';
		  */
	   } 
	   return $add;
	}
	
	function Delete () {
	    $add=false;
		if(isset($_POST['idDelete'])) {
		   $grp=Dispatcher::Instance()->Decrypt($_POST['idDelete']);
           $grp = explode(',',$grp);		   
		   
		   $del=$this->KomponenKegiatan->DoDelete($grp[1],$grp[0]);
		   if($del) {
		   		 //cek isi tabel finansi_pa_komponen_unit_kerja
				if($this->KomponenKegiatan->GetCountKomponenUnitKerja($grp[1]) > 0){
			   			//jika ada data maka hapus dulu
		   			$this->KomponenKegiatan->DoDeleteKomponenUnitKerja($grp[1]);
   			 	} 
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
		
		
		return $urlRedirect.'&grp='.$grp[0];
	}
	
	function Update() {
	   
	   if(isset($_POST['btnsimpan'])){
         if($this->validation('Perubahan')) {
		   
		   $update=$this->KomponenKegiatan->DoUpdate($this->data['komponen_id'],
		                                        $this->data['komponen_id_old'],
			                                    $this->data['kegref_id'],
												$this->data['komponen_nominal']
			                                   );
			if($update) {
				
				 //cek , jika input data unit kerja tidak kosong maka simpan unit kerja
             $kompId = $this->data['komponen_id_old'];
			   if(count($this->data['unitkerjaid']) != 0){
			   		//cek isi tabel finansi_pa_komponen_unit_kerja
			   		if($this->KomponenKegiatan->GetCountKomponenUnitKerja($kompId) > 0){
			   			//jika ada data maka hapus dulu
		   				$this->KomponenKegiatan->DoDeleteKomponenUnitKerja($kompId);
		   			}
   			$kompIdNew = $this->data['komponen_id'];
		   			foreach($this->data['unitkerjaid'] as $key => $value)
	   				{
		   				$this->KomponenKegiatan->DoAddKomponenUnitKerja($kompIdNew,$value);
		   			}
		   		} else {
		   			//jika data input unit kerja kosong maka cek tabel
					// finansi_pa_komponen_unit_kerja
		   			if($this->KomponenKegiatan->GetCountKomponenUnitKerja($kompId) > 0){
		   					//jika data input check unit kerja  kosong maka hapus data
		   				$this->KomponenKegiatan->DoDeleteKomponenUnitKerja($kompId);
		   			}
		   		}
		   		
		   		
		   		//end unit kerja
			  $this->msg['message']='Perubahan data berhasil dilakukan'; 		   
		      $this->msg['action']  ='msg';  
			} else {
			  $this->msg['message']='Perubahan data gagal dilakukan silahkan ulangi lagi'; 		   
		      $this->msg['action']  ='err';	  
			}	   
		 } else {		    		   
		   $update=false;
		 }		  
	   } 
	   return $update;
	}
	
	function validation($action) {	   
	   
	   
	    $this->msg='';
	    if(!isset($_POST['data'])) { //kalo gak ada data yang di POST apa yang mau di  validasi
		   $this->msg['message'] =$action.' data gagal dilakukan ';
		   return false;
		}				
	  	   
	    if(!isset($this->data['kegref_id']) || trim($this->data['kegref_id']) == '')
	      $this->msg['message'] .='Id Kegiatan Tidak Terdefinisi <br />';
		
		if(!isset($this->data['komponen_id']) || trim($this->data['komponen_id']) == '')
	      $this->msg['message'] .='Anda Belum Memilih Komponen <br />';
		
		
					$koma=explode(",",$this->data['komponen_nominal']);
					$this->data['komponen_nominal']=$koma[0];
		
		if(!isset($this->data['komponen_nominal']) || trim($this->data['komponen_nominal']) == '')
	      $this->msg['message'] .='Anda Belum Mengisi Nilai Nominal <br />';
		elseif(!is_numeric($this->data['komponen_nominal']))
		  $this->msg['message'] .='Nilai Nominal harus berupa angka <br />';		

		if(!isset($this->msg['message']))
   		   return true;
		else 
		   return false; 			   
	}
	
	
	function generateUrl($type,$isHome=false){
	    //parameter isHome ditujukan bahwa url diredirect ke home module apapun bentuk pesannya
	    if(isset($_GET['grp']))
	      $grp='&grp='.Dispatcher::Instance()->Encrypt($this->data['periodetahun']['id']);
	    else
		  $grp='';
		
		if($type=='msg' || $isHome ) $submodule='komponenKegiatan';
		else $submodule='komponenKegiatan';	
		
		
		Messenger::Instance()->Send($this->moduleName, $submodule, 'view', 'html', array($this->data,$type,$this->msg),Messenger::NextRequest);				
		$urlRedirect = Dispatcher::Instance()->GetUrl($this->moduleName, $submodule, 'view', 'html').$grp;
		return $urlRedirect;
	}
    
    function ClearVar(){
	
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
