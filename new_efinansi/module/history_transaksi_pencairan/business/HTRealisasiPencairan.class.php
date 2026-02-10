<?php

class HTRealisasiPencairan extends Database
{

   protected $mSqlFile;

   public function __construct($connectionNumber=0)
   {
      $this->mSqlFile      = 'module/history_transaksi_pencairan/business/ht_realisasi_pencairan.sql.php';
      parent::__construct($connectionNumber);
   }

   public function GetData($offset, $limit, $awal, $akhir,$nomor='',$posting='',$mak_nama='')
   {
      $result        = $this->Open($this->mSqlQueries['get_data'],array(
         date('Y-m-d', strtotime($awal)),
         date('Y-m-d', strtotime($akhir)),
         '%'.$nomor.'%',
         strtoupper($posting),
         (int)($posting == '' OR strtolower($posting) == 'all'),
         '%'.$mak_nama.'%',
         $offset,
         $limit
      ));

      return $result;
   }

   public function GetCountData()
   {
      $return        = $this->Open($this->mSqlQueries['get_count_data'], array());

      if($return){
         return $return[0]['total'];
      }else{
         return 0;
      }
   }
}