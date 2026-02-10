<?php
class RkaklProgram extends Database {

   protected $mSqlFile= 'module/rkakl_program/business/rkaklprogram.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
   
   function GetCountRkaklProgram($kode='', $nama=''){
      $count      = $this->Open(
         $this->mSqlQueries['get_count_rkakl_program'], 
         array( 
            '%'.$kode.'%', 
            '%'.$nama.'%'
         )
      );
      return $count['0']['count'];
   }
      
   function GetRkaklProgram($kode='', $nama='', $start, $limit) {         
      return $this->Open(
         $this->mSqlQueries['get_rkakl_program'], 
         array(
            '%'.$kode.'%', 
            '%'.$nama.'%', 
            $start, 
            $limit
         )
      );
   }
   
   function GetRkaklProgramById($id) {         
      return $this->Open(
         $this->mSqlQueries['get_rkakl_program_by_id'], 
         array(
            $id
         )
      );
   }
   
   function AddRkaklProgram($kode,$nama){
      return $this->Execute(
         $this->mSqlQueries['insert_rkakl_program'], 
         array(
            $kode,
            $nama
         )
      );
   }
   
   function UpdateRkaklProgram($kode, $nama, $id){
      return $this->Execute(
         $this->mSqlQueries['update_rkakl_program'], 
         array(
            $kode,
            $nama,
            $id
         )
      );
   }
   
   function DeleteRkaklProgramById($id){
      return $this->Execute(
         $this->mSqlQueries['delete_rkakl_program'], 
         array(
            $id
         )
      );
   }
   
   function DeleteRkaklProgramByArrayId($arrId) {
      $arrId   = implode("', '", $arrId);
      $result  = $this->Execute(
         $this->mSqlQueries['delete_rkakl_program_array'], 
         array(
            $arrId
         )
      );
      //echo $this->getLastError(); exit;
      return $result;
   }
}
?>