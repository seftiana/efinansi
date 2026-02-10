<?php

class UraianBelanja extends Database {

   protected $mSqlFile= 'module/uraian_belanja/business/uraianbelanja.sql.php';

   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);
   }

   function GetJenisBelanja(){
      return $this->Open($this->mSqlQueries['get_jenis_belanja'], array());
   }

   function GetCountUraianBelanja($jenis){
      $count = $this->Open($this->mSqlQueries['get_count_uraian_belanja'], array( '%'.$jenis.'%'));
      return $count['0']['count'];
   }

   function GetUraianBelanja($offset, $limit, $jenis='') {
      return $this->Open($this->mSqlQueries['get_uraian_belanja'], array( '%'.$jenis.'%', $offset, $limit));
   }

   function GetUraianBelanjaById($id) {
      return $this->Open($this->mSqlQueries['get_uraian_belanja_by_id'], array($id));
   }

   function AddUraianBelanja($idJenis,$nama){

      $result = $this->Execute($this->mSqlQueries['insert_uraian_belanja'], array($idJenis,$nama));

      return $result;
   }

   function UpdateUraianBelanja($idJenis,$nama,$id){
      return $this->Execute($this->mSqlQueries['update_uraian_belanja'], array($idJenis,$nama,$id));
   }

   function DeleteUraianBelanja($id){
      return $this->Execute($this->mSqlQueries['delete_uraian_belanja'], array($id));
   }
   function CekUraianBelanja($jenis) {
      $result = $this->Open($this->mSqlQueries['cek_uraian_belanja'], array( '%'.$jenis.'%'));
	  return $result[0];
   }
}
?>