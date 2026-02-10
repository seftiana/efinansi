<?php
class AppPinjaman extends Database {
	protected $mSqlFile;

	function __construct($connectionNumber=0) {
		$this->mSqlFile   = 'module/daftar_pinjaman/business/apppinjaman.sql.php';
		parent::__construct($connectionNumber);
		//#$this->SetDebugOn();
	}
	function GetCountDataPinjaman($kodePinj,$namaPinj,$Jumlah,$Angsuran){
      $result =$this->Open($this->mSqlQueries['get_count_data_pinjaman'], array('%'.$kodePinj.'%', '%'.$namaPinj.'%', 
	  '%'.$Jumlah.'%','%'.$Angsuran.'%'));
	   if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
   }
   function GetDataPinjaman($kodePinj,$namaPinj,$Jumlah,$Angsuran,$offset,$limit){
      $result=$this->Open($this->mSqlQueries['get_data_pinjaman'],array('%'.$kodePinj.'%','%'.$namaPinj.'%','%'.$Jumlah.'%','%'.$Angsuran.'%',$offset,$limit));
      return $result;
   }
   function GetDataPinjamanById($kodePinj){
      $result=$this->Open($this->mSqlQueries['get_data_pinjaman_by_id'],array($kodePinj));
      //print($this->GetLastError());
      return $result;
   }
//==do===
   function DoAddPinjaman($kodePinj,$namaPinj,$Jumlah,$Angsuran){
      $result=$this->Execute($this->mSqlQueries['do_add_pinjaman'],array($kodePinj,$namaPinj,$Jumlah,$Angsuran));
      return $result;
   }
   function DoUpdatePinjaman($kodePinj,$namaPinj,$Jumlah,$Angsuran){
      $result=$this->Execute($this->mSqlQueries['do_update_pinjaman'],array($kodePinj,$namaPinj,$Jumlah,$Angsuran,$kodePinj));
      return $result;
   }
   function DoDeletePinjamanById($kodePinj){
      $result=$this->Execute($this->mSqlQueries['do_delete_pinjaman_by_id'],array($kodePinj));
      return $result;   
   }
   function DoDeletePinjamanByArrayId($arrKodePinj){
      $kodePinj=implode("', '", $arrKodePinj);
      $result=$this->Execute($this->mSqlQueries['do_delete_pinjaman_by_array_id'],array($kodePinj));
      return $result;   
   }
}
?>
