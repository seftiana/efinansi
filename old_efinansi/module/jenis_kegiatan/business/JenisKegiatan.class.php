<?php
/*
   @ClassName : JenisKegiatan
   @Copyright : PT Gamatechno Indonesia
   @Analyzed By : Nanang Ruswianto <nanang@gamatechno.com>
   @Author By : Dyan Galih <galih@gamatechno.com>
   @Version : 0.1
   @StartDate : 2010-10-17
   @LastUpdate : 2010-10-17
   @Description : Jenis Kegiatan
*/

class JenisKegiatan extends Database
{
   protected $mSqlFile;

   function __construct ($connectionNumber=0)
   {
      $this->mSqlFile = 'module/jenis_kegiatan/business/jenis_kegiatan.sql.php';
      parent::__construct($connectionNumber);
   }

   public function GetListJenisKegiatan(){
      $result = $this->Open($this->mSqlQueries['get_jenis_kegiatan'], array());
      return $result;
   }
}
?>