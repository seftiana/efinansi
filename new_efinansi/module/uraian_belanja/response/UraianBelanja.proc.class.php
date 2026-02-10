<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/uraian_belanja/business/UraianBelanja.class.php';

class ProsessUraianBelanja {
      
   function InputUraianBelanja() {

      $Obj = new UraianBelanja();
      
      $arrData['uraianBelanja'] = $_POST['uraianBelanja'];
      $arrData['jenisBelanja'] = $_POST['jenisBelanja'];
      $arrData['nama_uraian_belanja'] = $_POST['nama_uraian_belanja'];
      
      $str = implode("|",$arrData);
      
      $additionalUrl = "";

      if(isset($_POST['btnsimpan']) || isset($_POST['btnubah'])){
      
         if($arrData['jenisBelanja']==''){
               $additionalUrl = "jenis belanja";
               return Dispatcher::Instance()->GetUrl('uraian_belanja', 'inputUraianBelanja', 'view', 'html') . 
                     '&pilih=' . Dispatcher::Instance()->Encrypt($additionalUrl).
                     '&data='. Dispatcher::Instance()->Encrypt($str);
            }
            
         if($arrData['nama_uraian_belanja']==''){
               $additionalUrl = "uraian belanja";
               return Dispatcher::Instance()->GetUrl('uraian_belanja', 'inputUraianBelanja', 'view', 'html') . 
                     '&err=' . Dispatcher::Instance()->Encrypt($additionalUrl).
                     '&data='. Dispatcher::Instance()->Encrypt($str);
            }
   
         if (isset($_POST['btnsimpan'])) { 
            //empty check
			$cekUraianBelanja = $Obj->CekUraianBelanja(trim(strtolower($_POST['nama_uraian_belanja'])));
			if($cekUraianBelanja['nama'] == trim(strtolower($_POST['nama_uraian_belanja'])) && $cekUraianBelanja['id'] != "") {
				$additionalUrl = "add|fail";
				$urlRedirect = Dispatcher::Instance()->GetUrl('uraian_belanja', 'uraianBelanja', 'view', 'html') . 
				   '&err=' . Dispatcher::Instance()->Encrypt($additionalUrl);
			} else {
				$addUraianBelanja = $Obj->AddUraianBelanja($_POST['jenisBelanja'],$_POST['nama_uraian_belanja']);
				if ($addUraianBelanja === true) $additionalUrl = "add|";
				else $additionalUrl = "add|fail";
				$urlRedirect = Dispatcher::Instance()->GetUrl('uraian_belanja', 'uraianBelanja', 'view', 'html') . 
				   '&err=' . Dispatcher::Instance()->Encrypt($additionalUrl);
			}
         }
         else{
			$cekUraianBelanja = $Obj->CekUraianBelanja(trim(strtolower($_POST['nama_uraian_belanja'])));
			if($cekUraianBelanja['nama'] == trim(strtolower($_POST['nama_uraian_belanja'])) && $cekUraianBelanja['id'] != $_POST['uraianBelanja']) {
				$additionalUrl = "update|fail";
				$urlRedirect = Dispatcher::Instance()->GetUrl('uraian_belanja', 'uraianBelanja', 'view', 'html') . 
				   '&err=' . Dispatcher::Instance()->Encrypt($additionalUrl);
			} else {
				$updateUraianBelanja = $Obj->UpdateUraianBelanja($_POST['jenisBelanja'],$_POST['nama_uraian_belanja'],$_POST['uraianBelanja']);
				if ($updateUraianBelanja === true) $additionalUrl = "update|";
				else $additionalUrl = "update|fail";
				$urlRedirect = Dispatcher::Instance()->GetUrl('uraian_belanja', 'uraianBelanja', 'view', 'html') . 
				   '&err=' . Dispatcher::Instance()->Encrypt($additionalUrl);
			}
         }
      } else {
         $urlRedirect = Dispatcher::Instance()->GetUrl('uraian_belanja', 'uraianBelanja', 'view', 'html') ;
      }
      return $urlRedirect;
    }   
}
?>
