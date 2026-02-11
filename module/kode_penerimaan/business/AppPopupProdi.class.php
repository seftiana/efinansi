<?php

class AppPopupProdi extends Database 
{

   protected $mSqlFile= 'module/kode_penerimaan/business/app_popup_prodi.sql.php';
   
   public function __construct($connectionNumber=1) 
   {
      parent::__construct($connectionNumber);       
   }
      

   public function GetData($offset, $limit, $nama) 
   {
		$result = $this->Open($this->mSqlQueries['get_data'], array('%'.$nama.'%', $offset, $limit));
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