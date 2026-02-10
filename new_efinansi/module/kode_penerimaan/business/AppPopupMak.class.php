<?php
#doc
# package:     AppPopupMak
# scope:       PUBLIC
# @created     ucil.619
# @Author      Eko Susilo
# @Created     2012-09-12
# @Modified    2012-09-12
# @Analysts    Nanang Ruswianto
# @copyright   Copyright (c) 2012 Gamatechno
#/doc

class AppPopupMak extends Database
{
   #   internal variables
   protected $mSqlFile     = 'module/kode_penerimaan/business/app_popup_mak.sql.php';
   #   Constructor
   function __construct ($connectionNumber = 0)
   {
      # code...
      parent::__construct($connectionNumber);
   }
   
   
   public function GetData($kode, $offset, $limit)
   {
      $result     = $this->Open(
         $this->mSqlQueries['get_data'], 
         array(
            '%'.$kode.'%', 
            '%'.$kode.'%', 
            $offset, 
            $limit
         )
      );
      
      return $result;
   }
   
   public function Count()
   {
      $result     = $this->Open(
         $this->mSqlQueries['count_data'], 
         array()
      );
      
      return $result[0]['total'];
   }
}
?>