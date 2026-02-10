<?php
class AppPopupProgram extends Database
{
   protected $mSqlFile;
   function __construct($connectionNumber=0) {
      $this->mSqlFile   = 'module/lap_rekap_anggaran_program/business/apppopupprogram.sql.php';
      parent::__construct($connectionNumber);
   }

   function GetDataProgram ($offset, $limit, $tahun_anggaran, $program='', $kode='') {
      $result     = $this->Open($this->mSqlQueries['get_data_program'], array(
         $tahun_anggaran,
         '%'.$program.'%',
         '%'.$kode.'%',
         $offset,
         $limit
      ));
      return $result;
   }

   function GetCountDataProgram ($tahun_anggaran, $program='', $kode='') {
      $result = $this->Open($this->mSqlQueries['get_count_data_program'], array(
         $tahun_anggaran,
         '%'.$program.'%',
         '%'.$kode.'%'
      ));
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }
}
?>