<?php
class ProgramKegiatan extends Database
{

   protected $mSqlFile;

   function __construct ($connectionNumber=0)
   {
      $this->mSqlFile = 'module/'.Dispatcher::Instance()->mModule.'/business/ProgramKegiatan.sql.php';
      parent::__construct($connectionNumber);
   }

   function GetDataExcel ($idTahun,$idProgram,$idKegiatan,$jenisKegiatanId,$kodeSubKegiatan,$namaSubKegiatan)
   {
      #$this->SetDebugOn();
      if($jenisKegiatanId==''){
         $jenisKegiatanId = 'all';
      }
      if($idProgram==''){
         $idProgram = 'all';
      }
      $result = $this->Open($this->mSqlQueries['get_data_excel'], array($idKegiatan, (int)($idKegiatan==''), $jenisKegiatanId, (int)($jenisKegiatanId=='all'), $idTahun, $idProgram,(int)($idProgram=='all'), '%'.$kodeSubKegiatan.'%', '%'.$namaSubKegiatan.'%'));
      #exit;
      return $result;
   }

   function KopiProgramKegiatan ($id)
   {
      $this->Execute($this->mSqlQueries['kopi_program_ref'], array($id));
      $this->Execute($this->mSqlQueries['kopi_sub_program'], array($id));
      $this->Execute($this->mSqlQueries['kopi_kegiatan_ref'], array($id));
      $this->Execute($this->mSqlQueries['copy_kegref_unitkerja'], array($id));
      $this->Execute($this->mSqlQueries['kopi_komponen_kegiatan'], array($id));
   }
}
?>
