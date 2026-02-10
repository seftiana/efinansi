<?php

class AppPopupNoPengajuan extends Database 
{

   protected $mSqlFile= 'module/realisasi_pencairan_2/business/apppopupnopengajuan.sql.php';

   public function __construct($connectionNumber=0) 
   {
      parent::__construct($connectionNumber);
   }

   public function GetData($unitKerjaId,$programId,$noPengajuan,$startRec,$itemViewed)
   {     
	 // $this->SetDebugOn()  ;
      $result = $this->Open($this->mSqlQueries['get_data'], 
									array(
											$unitKerjaId,
											$programId,
											'%'.$noPengajuan.'%',
											$startRec,
											$itemViewed
										));
	  
      return $result;
   }

   public function GetCountData() 
   {
      $result = $this->Open($this->mSqlQueries['get_count_data'], array());
      
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }
   
  
}
?>
