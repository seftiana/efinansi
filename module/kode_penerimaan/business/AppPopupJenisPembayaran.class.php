<?php

class AppPopupJenisPembayaran extends Database 
{

   protected $mSqlFile= 'module/kode_penerimaan/business/app_popup_jenis_pembayaran.sql.php';
   
   public function __construct($connectionNumber=0) 
   {
      parent::__construct($connectionNumber);       
   }
      
   //popup sumber dana
   public function GetData($offset, $limit, $nama) 
   {
		$result = $this->Open($this->mSqlQueries['get_data'], array('%'.$nama.'%', $offset, $limit));
		//echo $this->getLastError();
		return $result;
   }

   public function GetCountData($nama) 
   {
     $result = $this->Open($this->mSqlQueries['get_count'], array('%'.$nama.'%'));
     if (!$result)
       return 0;
     else
       return $result[0]['total'];
   }
}

?>