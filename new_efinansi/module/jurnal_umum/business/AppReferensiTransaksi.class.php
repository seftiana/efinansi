<?php
/**
* @module skenario
* @author Rendi Kasigi,
* @mail to rkasigi@gmail.com
* @copyright 2008&copy;Gamatechno
*/

class AppReferensiTransaksi extends Database {

   protected $mSqlFile= 'module/jurnal_umum/business/app_popup_referensi_transaksi.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);     
   }
//==GET==   
  
   function GetData($startRec,$itemViewed,$nama) {        
        $ret=$this->open($this->mSqlQueries['get_data'],array('%'.$nama.'%',$startRec,$itemViewed));				
		return $ret; 
   }
   
   function GetCount($nama) {
      $ret = $this->open($this->mSqlQueries['get_count'],array('%'.$nama.'%'));
	  if($ret)
	     return $ret[0]['total'];
	  else
	    return 0;
   }
   function date2string($date) {
	   $bln = array(
	                1  => 'Januari',
					2  => 'Februari',
					3  => 'Maret',
					4  => 'April',
					5  => 'Mei',
					6  => 'Juni',
					7  => 'Juli',
					8  => 'Agustus',
					9  => 'September',
					10 => 'Oktober',
					11 => 'November',
					12 => 'Desember'					
	               );
	   $arrtgl = explode('-',$date);
	   return $arrtgl[2].' '.$bln[(int) $arrtgl[1]].' '.$arrtgl[0];
	   
	}

   

//===DO==
   
   
}
?>
