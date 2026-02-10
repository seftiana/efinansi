<?php

class TahunPembukuanPeriode extends Database {

   protected $mSqlFile= 'module/tahun_pembukuan/business/tahun_pembukuan_periode.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);     
   }

   function GetTahunPembukuanPeriodeAktif() {
      return $this->Open($this->mSqlQueries['get_tahun_pembukuan_periode_aktif'], array());
   }

   function SetNonAktifTahunPembukuanPeriode($params) {
      return $this->Execute($this->mSqlQueries['set_non_aktif_tahun_pembukuan_periode'], array($params));
   }

   function InsertTahunPembukuanPeriode($params) {
      return $this->Execute($this->mSqlQueries['insert_tahun_pembukuan_periode'], $params);
   }

   function UpdateTahunPembukuan($params,$is_debet) {
      if ($is_debet=='1'){
         return $this->Execute($this->mSqlQueries['update_debet_tahun_pembukuan'], $params);
      }else{
         return $this->Execute($this->mSqlQueries['update_kredit_tahun_pembukuan'], $params);
      }
   }

   function GetTahunPembukuanFromCoa($params) {
      return $this->Open($this->mSqlQueries['get_tahun_pembukuan_from_coa'], array($params));
   }
   
   function GetUnitKerja(){
      return $this->Open($this->mSqlQueries['get_unit_kerja'],array());
   }
   
   function GetCountTahunPembukuan(){
      $result = $this->Open($this->mSqlQueries['get_count_tahun_pembukuan'],array());
      return $result['0']['total'];
   }
}
?>
