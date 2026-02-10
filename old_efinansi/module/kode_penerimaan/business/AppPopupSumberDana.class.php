<?php

class AppPopupSumberDana extends Database 
{

   protected $mSqlFile= 'module/kode_penerimaan/business/app_popup_sumber_dana.sql.php';
   
   public function __construct($connectionNumber=0) 
   {
      parent::__construct($connectionNumber);       
   }
      
   //popup sumber dana
   public function GetDataKodeSumberDana($offset, $limit, $nama) 
   {
		$result = $this->Open($this->mSqlQueries['get_data_sumber_dana'], array('%'.$nama.'%', $offset, $limit));
		//echo $this->getLastError();
		return $result;
   }

   public function GetCountKodeSumberDana($nama) 
   {
     $result = $this->Open($this->mSqlQueries['get_count_sumber_dana'], array('%'.$nama.'%'));
     if (!$result)
       return 0;
     else
       return $result[0]['total'];
   }
}

?>