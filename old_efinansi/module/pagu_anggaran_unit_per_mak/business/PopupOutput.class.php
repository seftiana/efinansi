<?php
   class PopupOutput extends Database{
      protected $mSqlFile = 'module/pagu_anggaran_unit_per_mak/business/popup_output.sql.php';
      
      function __construct($connectionNumber = 0){
         parent::__construct($connectionNumber);
      }
      
      function GetData($kode,$kegiatan, $offset,$limit){
         $return = $this->Open($this->mSqlQueries['get_output'], array(
            '%'.$kode.'%',
            '%'.$kode.'%',
            (int)($kode == '' OR $kode == null), 
            '%'.$kegiatan.'%', 
            '%'.$kegiatan.'%', 
            (int)($kegiatan == '' OR $kegiatan == null),
            $offset,
            $limit
         ));
         
         return $return;
      }
      
      function CountData($kode, $kegiatan){
         $return = $this->Open($this->mSqlQueries['count_output'], array(
            '%'.$kode.'%',
            '%'.$kode.'%',
            (int)($kode == '' OR $kode == null), 
            '%'.$kegiatan.'%', 
            '%'.$kegiatan.'%', 
            (int)($kegiatan == '' OR $kegiatan == null),
         ));
         
         return $return[0]['total'];
      }
   }
?>
