<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/program_kegiatan/business/SubKegiatan.class.php';

class ProcessSubKegiatan 
{

   protected $msg;
   protected $data;
   protected $moduleName='program_kegiatan';
   protected $SubKegiatan;

    function ProcessSubKegiatan()
    {
	  if(isset($_POST['data']))
         if(is_object($_POST['data']))
	        $this->data=$_POST['data']->AsArray();
		 else
		    $this->data=$_POST['data'];
   		$this->SubKegiatan = new SubKegiatan;
    }

    function Add () 
    {
	   
	   if(isset($_POST['btnsimpan'])){
	      //kalo yang diklik tombol simpan
		  if($this->validation('Penambahan')){
		  	
			//$add=$this->SubKegiatan->DoAdd($this->data['subkegiatan']);
            $add=$this->SubKegiatan->DoAdd($this->data['subkegiatan']);

            if($add) {
		   		$this->msg='Penambahan data berhasil dilakukan ';//.$sql;
		        $urlRedirect=$this->generateUrl('msg');
            } else {
			   if(mysql_errno() == 1062) //rendi said : gk support untuk semua  jenis DBMS, kalo DBMS nya bukan mysql gimana?
			     $this->msg='Penambahan data gagal dilakukan karena duplikasi kode sub kegiatan ';//.$sql;
			   else
			     $this->msg='Penambahan data gagal dilakukan ';//.$sql;

			   $urlRedirect=$this->generateUrl('err');
            }

		  } else {
			//echo $this->msg;exit;

		    $urlRedirect = $this->generateUrl('err');
		  }

	   } elseif(isset($_POST['btnNomorSelanjutnya'])) {
         //jikalau :
         //GET DATA NOMOR SELANJUTNYA
		   //$kode_selanjutnya = $programObj->GetKodeSelanjutnya($this->data['kegiatan']['program']);
			//Messenger::Instance()->Send('program_kegiatan', 
            //'inputProgramKegiatan', 'view', 'html', array(),Messenger::NextRequest);

		  $urlRedirect = Dispatcher::Instance()->GetUrl('program_kegiatan', 'inputSubKegiatan', 'view', 'html') .
           '&idTahun=' . Dispatcher::Instance()->Encrypt($this->data['subkegiatan']['ta_id']) .
           '&program_label=' . Dispatcher::Instance()->Encrypt($this->data['subkegiatan']['program_label']) .
           '&kegiatan_id=' . Dispatcher::Instance()->Encrypt($this->data['subkegiatan']['kegiatan_id']) .
           '&kegiatan_nama=' . Dispatcher::Instance()->Encrypt($this->data['subkegiatan']['kegiatan_nama']);

	   } else {
	      //kalo yang ditekan tombol balik
		  $urlRedirect = Dispatcher::Instance()->GetUrl($this->moduleName, 'programKegiatan', 'view', 'html') ;
	   }
	   return $urlRedirect;
	}

	function Delete () {
	    //if(isset($_GET['grp'])) {
		if(isset($_POST['idDelete'])) {
		   $grp=Dispatcher::Instance()->Decrypt($_GET['grp']);
		   $grp=$_POST['idDelete'];
		   //$KegiatanObj= new SubKegiatan();
		   $del=$this->SubKegiatan->DoDelete($grp);
		   if($del) {
		      /**
		    //cek isi tabel finansi_pa_kegiatan_ref_unit_kerja
			if($this->SubKegiatan->GetCountUnitKerjaRef($grp) > 0){
			   			//jika ada data maka hapus dulu
		   		$this->SubKegiatan->DoDeleteUnitKerjaRef($grp);
   			 } 
             */
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
	   
	   if(isset($_POST['btnsimpan'])){
         if($this->validation('Perubahan')) {

		   $update=$this->SubKegiatan->DoUpdate($this->data['subkegiatan']);
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
         //jikalau :
         //GET DATA NOMOR SELANJUTNYA
		   //$kode_selanjutnya = $programObj->GetKodeSelanjutnya($this->data['kegiatan']['program']);
			//Messenger::Instance()->Send('program_kegiatan', 'inputProgramKegiatan', 'view', 'html', array(),Messenger::NextRequest);

		  $urlRedirect = Dispatcher::Instance()->GetUrl('program_kegiatan', 'inputSubKegiatan', 'view', 'html') .
           '&idTahun=' . Dispatcher::Instance()->Encrypt($this->data['subkegiatan']['ta_id']) .
           '&grp=' . Dispatcher::Instance()->Encrypt($this->data['subkegiatan']['id']) .
           '&program_label=' . Dispatcher::Instance()->Encrypt($this->data['subkegiatan']['program_label']) .
           '&kegiatan_id=' . Dispatcher::Instance()->Encrypt($this->data['subkegiatan']['kegiatan_id']) .
           '&kegiatan_nama=' . Dispatcher::Instance()->Encrypt($this->data['subkegiatan']['kegiatan_nama']);
	   } else {
	      //kalo yang ditekan tombol balik
		  $urlRedirect = Dispatcher::Instance()->GetUrl($this->moduleName, 'programKegiatan', 'view', 'html') ;
	   }
	   return $urlRedirect;
	}

	function validation($action) {

	    $this->msg='';
	    if(!isset($_POST['data'])) { //kalo gak ada data yang di POST apa yang mau di  validasi
		   $this->msg=$action.' data gagal dilakukan ';
		   return false;
		}

	    if(isset($this->$data['subkegiatan']['id']) && (trim($this->data['subkegiatan']['id']) != ''))
	      $this->data['subkegiatan']['id']= Dispatcher::Instance()->Decrypt($this->data['subkegiatan']['id']);


	    if(!isset($this->data['subkegiatan']['kegiatan_id']) || trim($this->data['subkegiatan']['kegiatan_id']) == '')
	      $this->msg.='Nama Kegiatan Tidak Boleh Kosong <br />';


		if(!isset($this->data['subkegiatan']['kode']) || trim($this->data['subkegiatan']['kode']) == '')
	      $this->msg.='Kode Sub Kegiatan Tidak Boleh Kosong <br />';
#		elseif(!is_numeric($this->data['subkegiatan']['kode']))
#		  $this->msg.='Kode Sub Kegiatan Harus Berupa Angka <br />';

		if(!isset($this->data['subkegiatan']['nama']) || trim($this->data['subkegiatan']['nama']) == '')
	      $this->msg.='Sub Kegiatan Tidak Boleh Kosong';

		if($this->msg=='')
   		   return true;
		else
		   return false;
	}


	function generateUrl($type,$isHome=false)
    {
	    //parameter isHome ditujukan bahwa url diredirect ke home module apapun bentuk pesannya
	    if(isset($_GET['grp']))
	      $grp='&grp='.Dispatcher::Instance()->Encrypt($this->data['periodetahun']['id']);
	    else
		  $grp='';

		if($type=='msg' || $isHome ) $submodule='programKegiatan';
		else $submodule='inputSubKegiatan';


		Messenger::Instance()->Send(
                                    $this->moduleName, 
                                    $submodule, 
                                    'view', 
                                    'html', 
                                    array(
                                            $this->data,
                                            $type,
                                            $this->msg),
                                    Messenger::NextRequest);
                                    
		$urlRedirect = Dispatcher::Instance()->GetUrl(
                                                        $this->moduleName, 
                                                        $submodule, 
                                                        'view', 
                                                        'html').$grp .
                                                        '&idTahun=' . 
                                                        Dispatcher::Instance()->Encrypt(
                                                                $this->data['subkegiatan']['ta_id']);
                                                                
		return $urlRedirect;
	}

	function parsingUrl($file) 
    {
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