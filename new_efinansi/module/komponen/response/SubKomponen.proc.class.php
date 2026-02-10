<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/komponen/business/SubKomponen.class.php';

class ProsessSubKomponen {
   
   var $POST;
   var $KompId;

   function SetPost($param){
      $this->POST = $param;
   }
               
   // do add komponen
   function AddSubKomponen(){
      
      $ObjSubKomponen = new SubKomponen();
      $params = array($this->POST['nama_sub_komponen'],$this->POST['biaya'],$this->POST['komponen_id']);       
      if (($this->POST['nama_sub_komponen'] <> '')){
         return $ObjSubKomponen->InsertSubKomponen($params);
      }
      else{
         return false;
      }
   }
   //do update komponen
    function UpdateSubKomponen(){
      $ObjSubKomponen = new SubKomponen();
      $params = array($this->POST['nama_sub_komponen'],$this->POST['biaya']);

      $params = array($this->POST['nama_sub_komponen'],$this->POST['biaya'],$this->POST['sub_komponen_id']); 
      if(($this->POST['nama_sub_komponen'] <> '')){
         return $ObjSubKomponen->UpdateSubKomponen($params);
      }
      else
         return false;
   }

   
    //do hapus
    function DoDelete() {
      $ObjSubKomponen = new SubKomponen();

		$arrId = $this->POST['idDelete']->AsArray();
      $arrName = $this->POST['nameDelete']->AsArray();
      $jml = count($arrId);
      $dtGagal = array();
      //get kompId
      if (!empty($jml)){
         $exp_arrId = explode(',',$arrId[0]);
      }
      $this->KompId = $exp_arrId[1];

      //lakukan hapus sebanyak data yg mau dihapus
      for($i=0;$i<$jml;$i++){
         //arrid menyimpan subkompId dan kompId dgn separator koma (,) kiri utk subkompId dan kana kompId
         $delete = $ObjSubKomponen->DeleteSubKomponen(substr($arrId[$i],0,strpos($arrId[$i],',')));
         if ($delete == false){
            //simpan data yang gagal dihapus
            $dtGagal[] = $arrName[$i];
         }
      }
		return $dtGagal;
	}

   // do input data
   function InputSubKomponen() {
      //jika aksi simpan dan operasi tambah
      if ((isset($this->POST['btnsimpan'])) && ($this->POST['op'] == 'add')) {
            $rs_add = $this->AddSubKomponen();
            if ($rs_add == true){
               Messenger::Instance()->Send('komponen', 'SubKomponen', 'view', 'html', array($this->POST,'Penambahan data berhasil'),Messenger::NextRequest);
               $urlRedirect = Dispatcher::Instance()->GetUrl('komponen', 'SubKomponen', 'view', 'html');
            }else{
               Messenger::Instance()->Send('komponen', 'InputSubKomponen', 'view', 'html', array($this->POST,'Lengkapi isian data'),Messenger::NextRequest);
               $urlRedirect = Dispatcher::Instance()->GetUrl('komponen', 'InputSubKomponen', 'view', 'html');
            }
      } 
            //jika aksi simpan dan operasi ubah
      else if ((isset($this->POST['btnsimpan'])) && ($this->POST['op'] == 'edit')) { 
            $rs_update = $this->UpdateSubKomponen();
            if ($rs_update == true){
               Messenger::Instance()->Send('komponen', 'SubKomponen', 'view', 'html', array($this->POST,'Perubahan data berhasil'),Messenger::NextRequest);
               $urlRedirect = Dispatcher::Instance()->GetUrl('komponen', 'SubKomponen', 'view', 'html');
            }else{
               Messenger::Instance()->Send('komponen', 'InputSubKomponen', 'view', 'html', array($this->POST,'Lengkapi isian data'),Messenger::NextRequest);
               $urlRedirect = Dispatcher::Instance()->GetUrl('komponen', 'InputSubKomponen', 'view', 'html');
            }     
      } else if ((isset($this->POST['btnbalik']))) {
            Messenger::Instance()->Send('komponen', 'SubKomponen', 'view', 'html', array($this->POST),Messenger::NextRequest);
            $urlRedirect = Dispatcher::Instance()->GetUrl('komponen', 'SubKomponen', 'view', 'html');
      } else {
            $urlRedirect = Dispatcher::Instance()->GetUrl('komponen', 'InputSubKomponen', 'view', 'html') ;
      }
      return $urlRedirect;
    }  
    
    function DeleteSubKomponen() {
       $rs = $this->DoDelete();
       if(!empty($rs)){
            $data_gagal = implode(',',$rs);
            $pesan = 'Data '.$data_gagal.' gagal dihapus';
       }else{
            $pesan = 'Data berhasil dihapus'; 
       }
         
       //redirect ke list data
       Messenger::Instance()->Send('komponen', 'SubKomponen', 'view', 'html', array($this->POST,$pesan),Messenger::NextRequest);
       $urlRedirect = Dispatcher::Instance()->GetUrl('komponen', 'SubKomponen', 'view', 'html');

       return $urlRedirect.'&kid='.$this->KompId;
    }

}
?>