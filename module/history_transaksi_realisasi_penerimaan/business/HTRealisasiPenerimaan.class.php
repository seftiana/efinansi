<?php

class HTRealisasiPenerimaan extends Database
{

   protected $mSqlFile;
   protected $mUserId   = null;
   public $_POST;
   public $_GET;

   public function __construct($connectionNumber=0)
   {
      $this->mSqlFile   = 'module/history_transaksi_realisasi_penerimaan/business/ht_realisasi_penerimaan.sql.php';
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      parent::__construct($connectionNumber);
   }

   private function setUserId()
   {
      if(class_exists('Security')){
         if(method_exists(Security::Instance(), 'GetUserId')){
            $this->mUserId    = Security::Instance()->GetUserId();
         }else{
            $this->mUserId    = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
         }
      }
   }

   public function getUserid()
   {
      $this->setUserId();
      return (int)$this->mUserId;
   }

   public function GetData($offset, $limit, $awal, $akhir,$nomor='',$map_nama='',$posting='')
   {
      if($posting=='all') $posting='';
      if($map_nama != ''){
         $map_sql=" AND mp.map_nama LIKE '%".$map_nama."%' ";
      } else {
         $map_sql ='';
      }
      $query = sprintf($this->mSqlQueries['get_data'],
                                          $awal,
                                          $akhir,
                                          '%'.$nomor.'%',
                                          '%'.$posting.'%',
                                          $map_sql,
                                          $offset,
                                          $limit);
      $result = $this->Open($query,array());
      return $result;
   }

   public function GetCountData() {
      $result = $this->Open($this->mSqlQueries['get_count_data'], array());
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }

   public function GetDaftarMap($offset, $limit, $nama='')
   {
      $result = $this->Open($this->mSqlQueries['get_daftar_map'],
                                       array(
                                             '%'.$nama.'%',
                                             $offset,
                                             $limit));
      return $result;
   }
}


?>