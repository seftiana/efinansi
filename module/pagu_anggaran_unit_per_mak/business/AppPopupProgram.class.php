<?php 
#doc
# package:     AppPopupProgram
# scope:       PUBLIC
# @created     ucil.619
# @Author      Eko Susilo
# @Created     2013-02-12
# @Modified    2013-02-12
# @Analysts    Nanang Ruswianto
# @copyright   Copyright (c) 2012 Gamatechno
#/doc

class AppPopupProgram extends Database
{
   #   internal variables
   protected $mSqlFile = 'module/pagu_anggaran_unit_per_mak/business/app_popup_program.sql.php';
   #   Constructor
   function __construct ($connectionNumber = 0)
   {
      # code...
      parent::__construct($connectionNumber);
   }
   function GetProgram($kode = '', $offset, $limit)
   {
      $return     = $this->Open($this->mSqlQueries['get_program'], array(
         '%'.$kode.'%', 
         '%'.$kode.'%', 
         $offset, 
         $limit
      ));

      return $return;
   }
   function GetCount()
   {
      $result   = $this->Open(
         $this->mSqlQueries['get_count'],
         array()
      );
   
      if($result){
         return $result[0]['total'];
      }else{
         return 0;
      }
   }
}
?>