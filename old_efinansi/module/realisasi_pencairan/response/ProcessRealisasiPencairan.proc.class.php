<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/realisasi_pencairan/business/RealisasiPencairan.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user_unit_kerja/business/UserUnitKerja.class.php';


class ProcessRealisasiPencairan {
   
   protected $msg;
   protected $data;
   
   protected $moduleName  = 'realisasi_pencairan';
   protected $inputModule = 'inputRealisasiPencairan';
   protected $homeModule  = 'realisasiPencairan';
   
   
   public $RealisasiPencairan;
   public $UserUnitKerja;
   
   
    function ProcessRealisasiPencairan(){ //constructor
	  if(isset($_POST['data']))
         if(is_object($_POST['data']))	  
	        $this->data=$_POST['data']->AsArray();		 
		 else
		    $this->data=$_POST['data'];
			
			
	  
	    $this->data['tanggal'] = $_POST['tanggal_year'].'-'.
		                         $_POST['tanggal_mon'].'-'.
				                 $_POST['tanggal_day'];
								 
	  $this->UserUnitKerja = new UserUnitKerja;
	  $this->RealisasiPencairan = new RealisasiPencairan;
	  
    }   
   
    function Add() {	     
	   
	   if(isset($_POST['btnsimpan'])){
	      //kalo yang diklik tombol simpan
		  if($this->validation('Penambahan')){
		    $add=$this->RealisasiPencairan->DoAdd($this->data);	
            if($add) {
			   $this->msg='Penambahan data berhasil dilakukan';
			   $urlRedirect=$this->generateUrl('msg');
            } else {
			   $this->msg='Penambahan data gagal dilakukan <br />';
			   $this->msg.= $pesan;
			   $urlRedirect=$this->generateUrl('err');
            }			
			
		  } else {		    
			//echo $this->msg;exit;			
		    $urlRedirect = $this->generateUrl('err');
		  }
		  
	   } else {
	      //kalo yang ditekan tombol balik
		  $urlRedirect = Dispatcher::Instance()->GetUrl($this->moduleName, $this->homeModule, 'view', 'html') ;
	   }
	   return $urlRedirect;
	}
	
	
	
	function Delete () {
	    $this->inputModule = 'inputRencanaPengeluaranNonRutin';
		if(isset($_POST['idDelete'])) {
		   $grp=Dispatcher::Instance()->Decrypt($_POST['idDelete']);		   
		   		   
		   $del=$this->RealisasiPencairan->DoDelete($grp);
		   if($del) {
		     $this->msg='Penghapusan data berhasil dilakukan';
		     $urlRedirect = $this->generateUrl('msg');
		     
		   } else {
		     $this->msg='Penghapusan data gagal dilakukan';
		     $urlRedirect = $this->generateUrl('err');
		   }
		} else {
		   $this->msg='Penghapusan data gagal dilakukan';
		   $urlRedirect = $this->generateUrl('err');
		}
		return $urlRedirect;
	}
	
	
	
	
	function Update() {	   
	   
	   if(isset($_POST['btnsimpan'])){
         if($this->validation('Perubahan')) {
		   
		   $update=$this->RealisasiPencairan->DoUpdate($this->data);
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
	   } else {
	      //kalo yang ditekan tombol balik
		  $urlRedirect = Dispatcher::Instance()->GetUrl($this->moduleName, $this->homeModule, 'view', 'html') ;
	   }
	   return $urlRedirect;
	}
	
	
	
	function validation($action) {
	   
	    $this->msg='';
	    if(!isset($_POST['data'])) { //kalo gak ada data yang di POST apa yang mau di  validasi
		   $this->msg=$action.' data gagal dilakukan ';
		   return false;
		}		      
		  
	      if(trim($this->data['unit_id'])=='')
		     $this->msg .= 'Unit Kerja Tidak Boleh Kosong <br />';		  
		  
		  if(trim($this->data['program_id'])=='')
		     $this->msg .= 'Program Tidak Boleh Kosong <br />';
		  
		  if(trim($this->data['kegiatan_id'])=='')
		     $this->msg .= 'Kegiatan Tidak Boleh Kosong <br />';
		  
		  if(trim($this->data['subkegiatan_id'])=='')
		     $this->msg .= 'Sub Kegiatan Tidak Boleh Kosong <br />';  
		 
		  
		  $tgl = $this->data['tanggal'];
		  $tgl = explode('-',$tgl);
		  
		  
		  if(!checkdate($tgl[1],$tgl[2],$tgl[0]))
		     $this->msg .= 'Tanggal Tidak Valid <br />';
		  
		  
		  
		  
		  if(trim($this->data['nominal'])=='')
		     $this->msg .= 'Nominal Tidak Boleh Kosong <br />';
		  elseif(!is_numeric($this->data['nominal']))
		     $this->msg .= 'Nominal harus berupa angka <br />'; 		
		
		if($this->msg=='') {
		   if(isset($this->data['id']) && trim($this->data['id']) !='') //klo edit
		      $isNominalValid = $this->RealisasiPencairan->isNominalValid($this->data['kegiatandetail_id'],$this->data['nominal'],$max_nominal,$this->data['id']);
		   else
		      $isNominalValid = $this->RealisasiPencairan->isNominalValid($this->data['kegiatandetail_id'],$this->data['nominal'],$max_nominal);
		   
		   if($isNominalValid)
		      return true;
		   else {
		      if($max_nominal == 0)
			    $this->msg = 'Anda tidak dapat menambah data realisasi lagi, karena nilai nominal data sudah dimasukan semua <br />
				              Silahkan kembali';
		      else
			    $this->msg .= 'Nilai Nominal Max yang bisa anda masukan adalah '.number_format($max_nominal);
			  
			  return false;
		   }
		} else 
		   return false; 
	}
	
	
	
	
	function generateUrl($type,$isHome=false){	
	  if($type=='msg' || $isHome ) $submodule=$this->homeModule;
	  else $submodule= $this->inputModule;				
		
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
	
	function kekata($x) {
		$x = abs($x);
		$angka = array("", "satu", "dua", "tiga", "empat", "lima",
		"enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
		$temp = "";
		if ($x <12) {
			$temp = " ". $angka[$x];
		} else if ($x <20) {
			$temp = $this->kekata($x - 10). " belas";
		} else if ($x <100) {
			$temp = $this->kekata($x/10)." puluh". $this->kekata($x % 10);
		} else if ($x <200) {
			$temp = " seratus" . $this->kekata($x - 100);
		} else if ($x <1000) {
			$temp = $this->kekata($x/100) . " ratus" . $this->kekata($x % 100);
		} else if ($x <2000) {
			$temp = " seribu" . $this->kekata($x - 1000);
		} else if ($x <1000000) {
			$temp = $this->kekata($x/1000) . " ribu" . $this->kekata($x % 1000);
		} else if ($x <1000000000) {
			$temp = $this->kekata($x/1000000) . " juta" . $this->kekata($x % 1000000);
		} else if ($x <1000000000000) {
			$temp = $this->kekata($x/1000000000) . " milyar" . $this->kekata(fmod($x,1000000000));
		} else if ($x <1000000000000000) {
			$temp = $this->kekata($x/1000000000000) . " trilyun" . $this->kekata(fmod($x,1000000000000));
		}
		return $temp;
   }
   
	function terbilang($x, $style=4) {
		if($x<0) {
			$hasil = "minus ". trim($this->kekata($x));
		} else {
			$hasil = trim($this->kekata($x));
		}
		switch ($style) {
			case 1:
				$hasil = strtoupper($hasil);
				break;
			case 2:
				$hasil = strtolower($hasil);
				break;
			case 3:
				$hasil = ucwords($hasil);
				break;
			default:
				$hasil = ucfirst($hasil);
				break;
		}
		return $hasil.' Rupiah' ;
	}
}
?>
