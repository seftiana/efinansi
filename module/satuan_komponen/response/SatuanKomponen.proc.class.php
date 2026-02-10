<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/satuan_komponen/business/SatuanKomponen.class.php';

class ProsessSatuanKomponen {
   
   var $POST;

   function SetPost($param){
      $this->POST = $param;
   }
               
   // do add satuan komponen
   function AddSatuanKomponen(){    
      $ObjSatuanKomponen = new SatuanKomponen();
      $params = array($this->POST['nama_satuan_komponen']);       
      if ($this->POST['nama_satuan_komponen'] <> ''){
		$cek = $ObjSatuanKomponen->CekSatuanKomponen(trim(strtolower($this->POST['nama_satuan_komponen'])));
		//print_r($cek);print_r(trim(strtolower($this->POST['nama_satuan_komponen'])));exit();
		if($cek['nama'] == trim(strtolower($this->POST['nama_satuan_komponen'])) && $cek['id'] !="") {
			return "exist";
		 } else {
			return $ObjSatuanKomponen->InsertSatuanKomponen($params);
		 }
      }
      else{
         return false;
      }
   }
   //do update satuan komponen
    function UpdateSatuanKomponen(){
      $ObjSatuanKomponen = new SatuanKomponen();
      $params = array($this->POST['nama_satuan_komponen'],$this->POST['satuan_komponen_id']); 
      if($this->POST['nama_satuan_komponen'] <> ''){
		 $cek = $ObjSatuanKomponen->CekSatuanKomponen(trim(strtolower($this->POST['nama_satuan_komponen'])));
		//print_r($cek);exit();
		if($cek['nama'] == trim(strtolower($this->POST['nama_satuan_komponen'])) && $cek['id'] != $this->POST['satuan_komponen_id']) {
			return "exist";
		 } else {
			return $ObjSatuanKomponen->UpdateSatuanKomponen($params);
		 }
      }
      else
         return false;
   }

   
    //do hapus
    function DoDelete() {
      $ObjSatuanKomponen = new SatuanKomponen();

		$arrId = $this->POST['idDelete']->AsArray();
      $arrName = $this->POST['nameDelete']->AsArray();
      $jml = count($arrId);
      $dtGagal = array();
      //lakukan hapus sebanyak data yg mau dihapus
      for($i=0;$i<$jml;$i++){
         $delete = $ObjSatuanKomponen->DeleteSatuanKomponen($arrId[$i]);
         if ($delete == false){
            //simpan data yang gagal dihapus
            $dtGagal[] = $arrName[$i];
         }
      }
		return $dtGagal;
	}

   // do input data
   function InputSatuanKomponen() {
      //jika aksi simpan dan operasi tambah
      if ((isset($this->POST['btnsimpan'])) && ($this->POST['op'] == 'add')) {
            $rs_add = $this->AddSatuanKomponen();
			//print_r($rs_add); exit();
            if ($rs_add === true){
               Messenger::Instance()->Send('satuan_komponen', 'SatuanKomponen', 'view', 'html', array($this->POST,'Penambahan Data Berhasil'),Messenger::NextRequest);
               $urlRedirect = Dispatcher::Instance()->GetUrl('satuan_komponen', 'SatuanKomponen', 'view', 'html');
            }else if ($rs_add === "exist") {
			    Messenger::Instance()->Send('satuan_komponen', 'SatuanKomponen', 'view', 'html', array($this->POST,'Penambahan Data Gagal, Nama Satuan Sudah Ada'),Messenger::NextRequest);
               $urlRedirect = Dispatcher::Instance()->GetUrl('satuan_komponen', 'SatuanKomponen', 'view', 'html');
			}else{
               Messenger::Instance()->Send('satuan_komponen', 'InputSatuanKomponen', 'view', 'html', array($this->POST,'Lengkapi Isian Data'),Messenger::NextRequest);
               $urlRedirect = Dispatcher::Instance()->GetUrl('satuan_komponen', 'InputSatuanKomponen', 'view', 'html');
            }
      } 
            //jika aksi simpan dan operasi ubah
      else if ((isset($this->POST['btnsimpan'])) && ($this->POST['op'] == 'edit')) { 
            $rs_update = $this->UpdateSatuanKomponen();
            if ($rs_update === true){
               Messenger::Instance()->Send('satuan_komponen', 'SatuanKomponen', 'view', 'html', array($this->POST,'Perubahan data berhasil'),Messenger::NextRequest);
               $urlRedirect = Dispatcher::Instance()->GetUrl('satuan_komponen', 'SatuanKomponen', 'view', 'html');
            } else if ($rs_update === "exist") {
			    Messenger::Instance()->Send('satuan_komponen', 'SatuanKomponen', 'view', 'html', array($this->POST,'Perubahan Data Gagal, Nama Satuan Sudah Ada'),Messenger::NextRequest);
               $urlRedirect = Dispatcher::Instance()->GetUrl('satuan_komponen', 'SatuanKomponen', 'view', 'html');
			}else{
               Messenger::Instance()->Send('satuan_komponen', 'InputSatuanKomponen', 'view', 'html', array($this->POST,'Lengkapi Isian Data'),Messenger::NextRequest);
               $urlRedirect = Dispatcher::Instance()->GetUrl('satuan_komponen', 'InputSatuanKomponen', 'view', 'html');
            }     
      } else if ((isset($this->POST['btnbalik']))) {
            $urlRedirect = Dispatcher::Instance()->GetUrl('satuan_komponen', 'SatuanKomponen', 'view', 'html');
      } else {
            $urlRedirect = Dispatcher::Instance()->GetUrl('satuan_komponen', 'InputSatuanKomponen', 'view', 'html') ;
      }
      return $urlRedirect;
    }  
    
    function DeleteSatuanKomponen() {
       $rs = $this->DoDelete();
       if(!empty($rs)){
            $data_gagal = implode(',',$rs);
            $pesan = 'Data '.$data_gagal.' gagal dihapus';
       }else{
            $pesan = 'Data berhasil dihapus'; 
       }
         
       //redirect ke list data
       Messenger::Instance()->Send('satuan_komponen', 'SatuanKomponen', 'view', 'html', array($this->POST,$pesan),Messenger::NextRequest);
       $urlRedirect = Dispatcher::Instance()->GetUrl('satuan_komponen', 'SatuanKomponen', 'view', 'html');

       return $urlRedirect;
    }

}
?>