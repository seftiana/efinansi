<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/pph_ref_gpp/business/PphGpp.class.php';

class ProsessPphGpp {
   
   var $POST;

   function SetPost($param){
      $this->POST = $param;
   }
               
   // do add satuan komponen
   function AddPphGpp(){    
      $ObjPphGpp = new PphGpp();
      $params = array($this->POST['nama_pph_gpp']);       
      if ($this->POST['nama_pph_gpp'] <> ''){
         return $ObjPphGpp->InsertPphGpp($params);
      }
      else{
         return false;
      }
   }
   //do update satuan komponen
   function UpdatePphGpp(){
      $ObjPphGpp = new PphGpp();
      $params = array($this->POST['nama_pph_gpp'],$this->POST['pph_gpp_id']); 
      if($this->POST['nama_pph_gpp'] <> ''){
         return $ObjPphGpp->UpdatePphGpp($params);
      }
      else
         return false;
   }
   
   //do hapus
   function DoDelete() {
      $ObjPphGpp = new PphGpp();

		$arrId = $this->POST['idDelete']->AsArray();
      $arrName = $this->POST['nameDelete']->AsArray();
      $jml = count($arrId);
      $dtGagal = array();
      //lakukan hapus sebanyak data yg mau dihapus
      for($i=0;$i<$jml;$i++){
         $delete = $ObjPphGpp->DeletePphGpp($arrId[$i]);
         if ($delete == false){
            //simpan data yang gagal dihapus
            $dtGagal[] = $arrName[$i];
         }
      }
		return $dtGagal;
	}

   // do input data
   function InputPphGpp() {
      //jika aksi simpan dan operasi tambah
      if ((isset($this->POST['btnsimpan'])) && ($this->POST['op'] == 'add')) {
            $rs_add = $this->AddPphGpp();
            if ($rs_add == true){
               Messenger::Instance()->Send('pph_ref_gpp', 'PphGpp', 'view', 'html', array($this->POST,'Penambahan data berhasil'),Messenger::NextRequest);
               $urlRedirect = Dispatcher::Instance()->GetUrl('pph_ref_gpp', 'PphGpp', 'view', 'html');
            }else{
               Messenger::Instance()->Send('pph_ref_gpp', 'InputPphGpp', 'view', 'html', array($this->POST,'Lengkapi isian data'),Messenger::NextRequest);
               $urlRedirect = Dispatcher::Instance()->GetUrl('pph_ref_gpp', 'InputPphGpp', 'view', 'html');
            }
      } 
            //jika aksi simpan dan operasi ubah
      else if ((isset($this->POST['btnsimpan'])) && ($this->POST['op'] == 'edit')) { 
            $rs_update = $this->UpdatePphGpp();
            if ($rs_update == true){
               Messenger::Instance()->Send('pph_ref_gpp', 'PphGpp', 'view', 'html', array($this->POST,'Perubahan data berhasil'),Messenger::NextRequest);
               $urlRedirect = Dispatcher::Instance()->GetUrl('pph_ref_gpp', 'PphGpp', 'view', 'html');
            }else{
               Messenger::Instance()->Send('pph_ref_gpp', 'InputPphGpp', 'view', 'html', array($this->POST,'Lengkapi isian data'),Messenger::NextRequest);
               $urlRedirect = Dispatcher::Instance()->GetUrl('pph_ref_gpp', 'InputPphGpp', 'view', 'html');
            }     
      } else if ((isset($this->POST['btnbalik']))) {
            $urlRedirect = Dispatcher::Instance()->GetUrl('pph_ref_gpp', 'PphGpp', 'view', 'html');
      } else {
            $urlRedirect = Dispatcher::Instance()->GetUrl('pph_ref_gpp', 'InputPphGpp', 'view', 'html') ;
      }
      return $urlRedirect;
    }  
    
    function DeletePphGpp() {
       $rs = $this->DoDelete();
       if(!empty($rs)){
            $data_gagal = implode(',',$rs);
            $pesan = 'Data '.$data_gagal.' gagal dihapus';
       }else{
            $pesan = 'Data berhasil dihapus'; 
       }
         
       //redirect ke list data
       Messenger::Instance()->Send('pph_ref_gpp', 'PphGpp', 'view', 'html', array($this->POST,$pesan),Messenger::NextRequest);
       $urlRedirect = Dispatcher::Instance()->GetUrl('pph_ref_gpp', 'PphGpp', 'view', 'html');

       return $urlRedirect;
    }

}
?>