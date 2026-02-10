<?php
/*
   @ClassName : EarlyWarning
   @Copyright : PT Gamatechno Indonesia
   @Analyzed By : Nanang Ruswianto <nanang@gamatechno.com>
   @Author By : Dyan Galih <galih@gamatechno.com>
   @Version : 0.1
   @StartDate : 2010-09-30
   @LastUpdate : 2010-09-30
   @Description : Early Warning
*/

class EarlyWarning extends Database
{
   protected $mSqlFile;

   function __construct ($connectionNumber=0)
   {
      $this->mSqlFile = 'module/early_warning/business/early_warning.sql.php';
      parent::__construct($connectionNumber);
   }

   public function GetEarlyWarning(){
      $result = $this->Open($this->mSqlQueries['get_query_early_warning'], array());
      for ($i = 0; $i < count($result); $i++)
      {

         $total =  $this->open($result[$i]['query_sql'],array());

         if(count($total)>0){
            $arrTotal[$i]['total'] = count($total);
            $arrTotal[$i]['query_desc'] = $result[$i]['query_desc'];
         }
      }
      return $arrTotal;
   }

}
?>