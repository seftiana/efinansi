<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/program_kegiatan/business/Kegiatan.class.php';

class ProcessKegiatan {

   protected $msg;
   protected $data;
   protected $moduleName='program_kegiatan';

    function ProcessKegiatan(){
	  if(isset($_POST['data']))
         if(is_object($_POST['data']))
	        $this->data=$_POST['data']->AsArray();
		 else
		    $this->data=$_POST['data'];

    }

    function Add () {

	   $programObj = new Kegiatan();
	   if(isset($_POST['btnsimpan'])){
	      //kalo yang diklik tombol simpan
		  if($this->validation('Penambahan')){

		    $add=$programObj->DoAdd(
			        $this->data['kegiatan']['program'],
					$this->data['kegiatan']['kode'],
					$this->data['kegiatan']['nama'],
					$this->data['kegiatan']['jenisId'],
					$this->data['kegiatan']['kode_label'],
					$this->data['kegiatan']['rkakl_output_id']
					);
            if($add) {
			   $this->msg='Penambahan data berhasil dilakukan';
			   $urlRedirect=$this->generateUrl('msg');
            } else {
			   $this->msg='Penambahan data gagal dilakukan';
			   $urlRedirect=$this->generateUrl('err');
            }

		  } else {
			//echo $this->msg;exit;

		    $urlRedirect = $this->generateUrl('err');
		  }

	   } elseif(isset($_POST['btnNomorSelanjutnya'])) {
         //jikalau onchange :
         //GET DATA NOMOR SELANJUTNYA
		   //$kode_selanjutnya = $programObj->GetKodeSelanjutnya($this->data['kegiatan']['program']);
			//Messenger::Instance()->Send('program_kegiatan', 'inputProgramKegiatan', 'view', 'html', array(),Messenger::NextRequest);
		  $urlRedirect = Dispatcher::Instance()->GetUrl('program_kegiatan', 'inputKegiatan', 'view', 'html') . '&idTahun=' . Dispatcher::Instance()->Encrypt($this->data['kegiatan']['thn_id']) . '&programId=' . Dispatcher::Instance()->Encrypt($this->data['kegiatan']['program']). '&jenisId=' . Dispatcher::Instance()->Encrypt($this->data['kegiatan']['jenisId']);
      } else {
	      //kalo yang ditekan tombol balik
		  $urlRedirect = Dispatcher::Instance()->GetUrl('program_kegiatan', 'programKegiatan', 'view', 'html') ;
	   }
	   return $urlRedirect;
	}

	function Delete () {
	    //if(isset($_GET['grp'])) {
		if(isset($_POST['idDelete'])) {
		   $grp=Dispatcher::Instance()->Decrypt($_GET['grp']);
		   $grp=$_POST['idDelete'];
		   $KegiatanObj= new Kegiatan();
		   $del=$KegiatanObj->DoDelete($grp);
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
	   $KegiatanObj = new Kegiatan();
	   if(isset($_POST['btnsimpan'])){
         if($this->validation('Perubahan')) {

		   $update=$KegiatanObj->DoUpdate($this->data['kegiatan']);
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
	   } elseif(isset($_POST['btnNomorSelanjutnya'])) {
         //jikalau onchange :
         //GET DATA NOMOR SELANJUTNYA
		   //$kode_selanjutnya = $programObj->GetKodeSelanjutnya($this->data['kegiatan']['program']);
			Messenger::Instance()->Send('program_kegiatan', 'inputProgramKegiatan', 'view', 'html', array(),Messenger::NextRequest);
		  $urlRedirect = Dispatcher::Instance()->GetUrl('program_kegiatan', 'inputKegiatan', 'view', 'html') . '&grp=' . Dispatcher::Instance()->Encrypt($this->data['kegiatan']['id']) . '&idTahun=' .Dispatcher::Instance()->Encrypt($this->data['kegiatan']['thn_id']) . '&programId=' . Dispatcher::Instance()->Encrypt($this->data['kegiatan']['program']). '&jenisId=' . Dispatcher::Instance()->Encrypt($this->data['kegiatan']['jenisId']);
	   } else {
	      //kalo yang ditekan tombol balik
		  $urlRedirect = Dispatcher::Instance()->GetUrl('program_kegiatan', 'programKegiatan', 'view', 'html') ;
	   }
	   return $urlRedirect;
	}

	function validation($action) {
	   //debug($this->data);

	    $this->msg='';
	    if(!isset($_POST['data'])) { //kalo gak ada data yang di POST apa yang mau di  validasi
		   $this->msg=$action.' data gagal dilakukan ';
		   return false;
		}

	    if(isset($this->data['kegiatan']['id']) && (trim($this->data['kegiatan']['id']) != ''))
	      $this->data['kegiatan']['id']= Dispatcher::Instance()->Decrypt($this->data['kegiatan']['id']);

		if(!isset($this->data['kegiatan']['kode']) || trim($this->data['kegiatan']['kode']) == '')
	      $this->msg.='Kode Kegiatan Tidak Boleh Kosong <br />';
#		elseif(!is_numeric($this->data['kegiatan']['kode']))
#		  $this->msg.='Kode kegiatan harus berupa angka <br />';


		if(!isset($this->data['kegiatan']['nama']) || trim($this->data['kegiatan']['nama']) == '')
	      $this->msg.='Nama Kegiatan Tidak Boleh Kosong';

		if($this->msg=='')
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

		if($type=='msg' || $isHome ) $submodule= 'programKegiatan';
		else $submodule='inputKegiatan';


		Messenger::Instance()->Send($this->moduleName, $submodule, 'view', 'html', array($this->data,$type,$this->msg),Messenger::NextRequest);
		$urlRedirect = Dispatcher::Instance()->GetUrl($this->moduleName, $submodule, 'view', 'html').$grp.'&idTahun=' . Dispatcher::Instance()->Encrypt($this->data['kegiatan']['thn_id']);
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