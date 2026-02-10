<?php
/**
* @package Referensi
*/
class FinansiReferensi extends Database
{
   protected $mSqlFile;
   public $_POST;
   public $_GET;
   public $method;
   protected $userId;
   public $indonesianMonth    = array(
      'N/A',
      'Januari',
      'Februari',
      'Maret',
      'April',
      'Mei',
      'Juni',
      'Juli',
      'Agustus',
      'September',
      'Oktober',
      'November',
      'Desember'
   );

   function __construct($connectionNumber = 0)
   {
      $this->mSqlFile      = 'module/'.Dispatcher::Instance()->mModule.'/business/finansi_referensi.sql.php';
      $this->_POST         = (is_object($_POST)) ? $_POST->AsArray() : $_POST;
      $this->_GET          = (is_object($_GET)) ? $_GET->AsArray() : $_GET;
      $this->method        = $_SERVER['REQUEST_METHOD'];
      $this->userId        = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      parent::__construct($connectionNumber);
   }

   public function GetTahunAnggaran($param = array())
   {
      $options       = array(
         'active' => FALSE,
         'open' => FALSE
      );

      $option        = array_merge($options, (array)$param);
      $return        = $this->Open($this->mSqlQueries['get_tahun_anggaran'], array(
         (int)($option['active'] === FALSE),
         (int)($option['open'] === FALSE)
      ));

      return $return;
   }

   /**
    * @param Array $param
    * @return Array Data Program Kegiatan
    */
   public function GetDataProgramKegiatan($param = array())
   {
     //$this->SetDebugOn();
      $opt        = array(
         'options' => array(
            'tahun_anggaran' => NULL,
            'kegiatan_id' => '',
            'output_id' => '',
            'kode' => '',
            'nama' => ''
         ), 'limit' => 20,
         'offset' => 0
      );

      $options    = array_merge($opt, $param);

      $return     = $this->Open($this->mSqlQueries['get_data_program_kegiatan'], array(
         $options['options']['tahun_anggaran'],
         (int)($options['options']['tahun_anggaran'] === NULL OR $options['options']['tahun_anggaran'] == '' OR strtolower($options['options']['tahun_anggaran']) == 'all'),
         $options['options']['kegiatan_id'],
         (int)($options['options']['kegiatan_id'] == '' OR $options['options']['kegiatan_id'] === NULL),
         $options['options']['output_id'],
         (int)($options['options']['output_id'] == '' OR $options['options']['output_id'] === NULL),
         '%'.$options['options']['kode'].'%',
         '%'.$options['options']['nama'].'%',
         (int)$options['offset'],
         (int)$options['limit']
      ));

      return (array)$return;
   }

   /**
    * @param Array $param
    * @return Int Count
    */
   public function GetCountProgramKegiatan($param = array())
   {
      $opt        = array(
         'options' => array(
            'tahun_anggaran' => NULL,
            'kegiatan_id' => '',
            'output_id' => '',
            'kode' => '',
            'nama' => ''
         )
      );

      $options    = array_merge($opt, $param);

      $return     = $this->Open($this->mSqlQueries['get_count_program_kegiatan'], array(
         $options['options']['tahun_anggaran'],
         (int)($options['options']['tahun_anggaran'] === NULL OR $options['options']['tahun_anggaran'] == '' OR strtolower($options['options']['tahun_anggaran']) == 'all'),
         $options['options']['kegiatan_id'],
         (int)($options['options']['kegiatan_id'] == '' OR $options['options']['kegiatan_id'] === NULL),
         $options['options']['output_id'],
         (int)($options['options']['output_id'] == '' OR $options['options']['output_id'] === NULL),
         '%'.$options['options']['kode'].'%',
         '%'.$options['options']['nama'].'%'
      ));

      if($return){
         return $return[0]['count'];
      }else{
         return 0;
      }
   }

   public function GetRkaklKegiatanIdByKode($kode)
   {
      $return     = $this->Open($this->mSqlQueries['get_rkakl_kegiatan_by_kode'], array(
         $kode
      ));

      if($return){
         return $return[0]['id'];
      }else{
         return null;
      }
   }

   /**
    * @return Array $return : Data Rkakl Program
    */
   public function GetRkaklProgram()
   {
      $return        = $this->Open($this->mSqlQueries['get_rkakl_program'], array());

      return (array)$return;
   }

   /**
    * @param String $kode
    * @param Int $id
    * @return Boolean, TRUE if not Exists, False Is exists
    */
   public function CheckProgramRefByKode($kode, $taId = null, $id = null)
   {
      $return     = $this->Open($this->mSqlQueries['check_program_ref_by_kode'], array(
         $kode,
         $taId,
         (int)($taId === NULL OR $taId == ''),
         (int)$id,
         (int)($id === null OR $id == '')
      ));

      if($return){
         if($return[0]['count'] <> 0){
            return false;
         }else{
            return true;
         }
      }else{
         return false;
      }
   }

   public function DoSaveProgramRef($param = array())
   {
      $rkaklProgram     = $this->GetRkaklProgram();
      $rkaklProgramId   = $rkaklProgram[0]['id'];
      $result           = true;
      $this->StartTrans();
      if(!is_array($param)){
         $result     &= false;
      }

      $checkRkaklKegiatan  = $this->GetRkaklKegiatanIdByKode($param['kode']);
      $rkaklKegiatanId     = (int)$checkRkaklKegiatan;
      // jika tidak ditemukan id rkakl kegiatan maka input rkakl kegiatan baru
      if($checkRkaklKegiatan === NULL){
         $result           &= $this->Execute($this->mSqlQueries['do_insert_rkakl_kegiatan'], array(
            $rkaklProgramId,
            $param['kode'],
            $param['nama']
         ));

         $rkaklKegiatanId  = $this->LastInsertId();
      }

      // insert ke dalam  table program ref
      $result     &= $this->Execute($this->mSqlQueries['do_insert_program_ref'], array(
         $param['kode'],
         $param['nama'],
         $param['ta_id'],
         $rkaklKegiatanId,
         $param['indikator'],
         $param['strategi'],
         $param['kebijakan']
      ));

      return $this->EndTrans($result);
   }

   public function DoUpdateProgramRef($param = array())
   {
      $rkaklProgram     = $this->GetRkaklProgram();
      $rkaklProgramId   = $rkaklProgram[0]['id'];
      $result           = true;
      $this->StartTrans();
      if(!is_array($param)){
         $result        &= false;
      }
      $checkRkaklKegiatan  = $this->GetRkaklKegiatanIdByKode($param['kode']);
      $rkaklKegiatanId     = $param['rkakl_kegiatan_id'];
      if($rkaklKegiatanId == '' OR $param['rkakl_kegiatan_id'] != $checkRkaklKegiatan){
         $rkaklKegiatanId  = (int)$checkRkaklKegiatan;
      }

      if((int)$param['rkakl_kegiatan_id'] === (int)$checkRkaklKegiatan && $param['rkakl_kegiatan_id'] != ''){
         // update rkakl kegiatan
         $result           &= $this->Execute($this->mSqlQueries['do_update_rkakl_kegiatan'], array(
            $param['kode'],
            $param['nama'],
            $param['rkakl_kegiatan_id']
         ));
      }

      if($checkRkaklKegiatan === NULL AND $rkaklKegiatanId == ''){
         // jika rkakl kegiatan tidak terdifinisikan insert dan tidak di temukan data rkakl kegiatan
         // insert rkakl kegiatan yang baru
         $result           &= $this->Execute($this->mSqlQueries['do_insert_rkakl_kegiatan'], array(
            $rkaklProgramId,
            $param['kode'],
            $param['nama']
         ));

         $rkaklKegiatanId  = $this->LastInsertId();
      }

      // update data program_ref
      $result              &= $this->Execute($this->mSqlQueries['do_update_program_ref'], array(
         $param['kode'],
         $param['nama'],
         $param['ta_id'],
         $rkaklKegiatanId,
         $param['indikator'],
         $param['strategi'],
         $param['kebijakan'],
         $param['id']
      ));

      return $this->EndTrans($result);
   }

   /**
    * @param Int $id : Id program_ref
    * @return Array $return : Data program ref by id
    */
   public function GetProgramRefById($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_program_ref_by_id'], array(
         (int)$id
      ));

      if($return){
         return (array)$return[0];
      }else{
         return null;
      }
   }

   public function DeleteRelatedProgramRef($id)
   {
      $result     = true;
      $this->StartTrans();
      for ($i=0; $i < count($id); $i++) {
         $result  &= $this->Execute($this->mSqlQueries['delete_related_rkakl_program_ref'], array(
            $id[$i]
         ));
         $result  &= $this->Execute($this->mSqlQueries['delete_related_program_ref'], array(
            $id[$i]
         ));
      }
      return $this->EndTrans($result);
   }

   public function CheckSubprogByKode($kode, $progId, $taId, $id = null)
   {
      $return     = $this->Open($this->mSqlQueries['check_subprog_by_kode'], array(
         $kode,
         $progId,
         $taId,
         $id,
         (int)($id === null or $id == '')
      ));

      if($return){
         if($return[0]['count'] <> 0){
            return false;
         }else{
            return true;
         }
      }else{
         return false;
      }
   }

   public function GetRkaklOutputIdByKode($kode)
   {
      $return     = $this->Open($this->mSqlQueries['get_rkakl_output_by_kode'], array(
         $kode
      ));

      if($return){
         return $return[0]['id'];
      }else{
         return null;
      }
   }

   public function DoAddSubProgram($param = array())
   {
      $userId           = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $rkaklProgram     = $this->GetRkaklProgram();
      $rkaklProgramId   = $rkaklProgram[0]['id'];
      $result           = true;
      $this->StartTrans();
      // pengecekan rkakl kegiatan
      $checkRkaklKegiatan  = $this->GetRkaklKegiatanIdByKode($param['kegiatan_kode']);
      // pengecekan rkakl output
      $checkRkaklOutput    = $this->GetRkaklOutputIdByKode($param['kode']);
      $rkaklOutputId       = $param['output_id'];
      if($rkaklOutputId == ''){
         $rkaklOutputId    = $checkRkaklOutput;
      }

      if($param['output_id'] == '' AND $checkRkaklOutput === null){
         // jika tidak ada data rkakl output
         // input rkakl output baru
         $rkaklKegiatanId     = $checkRkaklKegiatan;
         if($rkaklKegiatanId === null){
            // jika belum ada rkakl kegiatan sebagai parent output nya
            // input rkakl kegiatan
            $result           &= $this->Execute($this->mSqlQueries['do_insert_rkakl_kegiatan'], array(
               $rkaklProgramId,
               $param['kegiatan_kode'],
               $param['kegiatan']
            ));
            // dapatkan rkakl kegiatan id dari LastInsertId()
            $rkaklKegiatanId  = $this->LastInsertId();
         }
         // insert rkakl output baru jika belum ada rkakl output berdasarkan form input
         $result              &= $this->Execute($this->mSqlQueries['do_insert_rkakl_output'], array(
            $rkaklKegiatanId,
            $param['kode'],
            $param['nama'],
            $userId
         ));
         // get rkakl output id
         $rkaklOutputId       = $this->LastInsertId();
      }

      // insert table sub program
      $result           &= $this->Execute($this->mSqlQueries['do_insert_sub_program'], array(
         $param['kegiatan_id'],
         $param['kode'],
         $param['nama'],
         $rkaklOutputId
      ));
      return $this->EndTrans($result);
   }

   /**
    * @param int $id
    * @return array $return Array data sub program
    */
   public function GetSubProgramById($id)
   {
      $return        = $this->Open($this->mSqlQueries['get_sub_program_by_id'], array(
         $id
      ));

      if($return){
         return $return[0];
      }else{
         return null;
      }
   }

   public function DoUpdateSubProgram($param)
   {
      $userId           = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $rkaklProgram     = $this->GetRkaklProgram();
      $rkaklProgramId   = $rkaklProgram[0]['id'];
      $result           = true;
      $this->StartTrans();
      // pengecekan rkakl kegiatan
      $checkRkaklKegiatan  = $this->GetRkaklKegiatanIdByKode($param['kegiatan_kode']);
      // pengecekan rkakl output
      $checkRkaklOutput    = $this->GetRkaklOutputIdByKode($param['kode']);
      $rkaklOutputId       = $param['output_id'];
      if($rkaklOutputId == '' OR $rkaklOutputId != $checkRkaklOutput){
         $rkaklOutputId    = $checkRkaklOutput;
      }

      if($param['output_id'] == '' OR $checkRkaklOutput === null){
         // jika tidak ada data rkakl output
         // input rkakl output baru
         $rkaklKegiatanId     = $checkRkaklKegiatan;
         if($rkaklKegiatanId === null){
            // jika belum ada rkakl kegiatan sebagai parent output nya
            // input rkakl kegiatan
            $result           &= $this->Execute($this->mSqlQueries['do_insert_rkakl_kegiatan'], array(
               $rkaklProgramId,
               $param['kegiatan_kode'],
               $param['kegiatan']
            ));
            // dapatkan rkakl kegiatan id dari LastInsertId()
            $rkaklKegiatanId  = $this->LastInsertId();
         }
         // insert rkakl output baru jika belum ada rkakl output berdasarkan form input
         $result              &= $this->Execute($this->mSqlQueries['do_insert_rkakl_output'], array(
            $rkaklKegiatanId,
            $param['kode'],
            $param['nama'],
            $userId
         ));
         // get rkakl output id
         $rkaklOutputId       = $this->LastInsertId();
      }

      if($param['output_id'] == $checkRkaklOutput AND $param['output_id'] != ''){
         $result              &= $this->Execute($this->mSqlQueries['do_update_rkakl_output'], array(
            $checkRkaklKegiatan,
            $param['kode'],
            $param['nama'],
            $userId,
            $rkaklOutputId
         ));
      }

      $result              &= $this->Execute($this->mSqlQueries['do_update_sub_program'], array(
         $param['kegiatan_id'],
         $param['kode'],
         $param['nama'],
         $rkaklOutputId,
         $param['id']
      ));

      return $this->EndTrans($result);
   }

   public function DeleteRelatedSubProgram($id)
   {
      $result     = true;
      $this->StartTrans();
      for ($i=0; $i < count($id); $i++) {
         $result  &= $this->Execute($this->mSqlQueries['do_delete_related_rkakl_output'], array(
            $id[$i]
         ));
         $result  &= $this->Execute($this->mSqlQueries['do_delete_related_sub_program'], array(
            $id[$i]
         ));
      }
      return $this->EndTrans($result);
   }

   public function GetUnitKerjaRef()
   {
      $return     = $this->Open($this->mSqlQueries['get_unit_kerja_ref'], array());

      return $return;
   }

   public function CheckKegiatanRef($kode, $taId, $id = null, $parentId = null)
   {
      $return     = $this->Open($this->mSqlQueries['check_kegiatan_ref'], array(
         $kode,
         $id,
         (int)($id === NULL OR $id == ''),
         $parentId,
         (int)($parentId === null OR $parentId == ''),
         $taId
      ));

      if($return){
         if($return[0]['count'] <> 0){
            return false;
         }else{
            return true;
         }
      }else{
         return false;
      }
   }

   public function GetRkaklSubKegiatan($kode)
   {
      $return     = $this->Open($this->mSqlQueries['get_rkakl_sub_kegiatan'], array(
         $kode
      ));

      if($return){
         return $return[0]['id'];
      }else{
         return null;
      }
   }

   public function DoSaveKegiatanRef($param = array())
   {
      $result        = true;
      $this->StartTrans();
      $rkaklSubKegiatanId  = $this->GetRkaklSubKegiatan($param['kode']);
      if(is_null($rkaklSubKegiatanId)){
         $result     &= $this->Execute($this->mSqlQueries['insert_rkakl_sub_kegiatan'], array(
            $param['kode'],
            $param['nama']
         ));

         $rkaklSubKegiatanId  = $this->LastInsertId();
      }

      $result        &= $this->Execute($this->mSqlQueries['do_insert_kegiatan_ref'], array(
         $param['output_id'],
         $param['kode'],
         $param['nama'],
         $rkaklSubKegiatanId
      ));

      $kegrefId      = $this->LastInsertId();
      if(!empty($param['unit'])){
         foreach ($param['unit'] as $unit) {
            $result  &= $this->Execute($this->mSqlQueries['do_insert_keg_ref_unit'], array(
               $kegrefId,
               $unit['id']
            ));
         }
      }
      return $this->EndTrans($result);
   }

   public function DoUpdateKegiatanRef($param = array())
   {
      $result        = true;
      $this->StartTrans();
      $rkaklSubKegiatanId  = $this->GetRkaklSubKegiatan($param['kode']);

      if($param['rkakl_subkegiatan_id'] == '' OR $param['rkakl_subkegiatan_id'] != $rkaklSubKegiatanId){
         $rkaklSubKegiatanId  = $rkaklSubKegiatanId;
      }

      if($param['rkakl_subkegiatan_id'] == '' AND $rkaklSubKegiatanId === NULL){
         // jika tidak ada rkakl sub kegiatan yang di temukan dan atau tidak di definisikan
         // insert rkakl sub kegiatan baru

         $result     &= $this->Execute($this->mSqlQueries['insert_rkakl_sub_kegiatan'], array(
            $param['kode'],
            $param['nama']
         ));

         $rkaklSubKegiatanId  = $this->LastInsertId();
      }

      if($param['rkakl_subkegiatan_id'] != '' AND $param['rkakl_subkegiatan_id'] == $rkaklSubKegiatanId){
         // update rkakl sub kegiatan jika ada pendefinisian dan data sama dengan pengecekan data
         $result     &= $this->Execute($this->mSqlQueries['update_rkakl_sub_kegiatan'], array(
            $param['kode'],
            $param['nama'],
            $rkaklSubKegiatanId
         ));
      }

      // update kegiatan ref
      // delete semua unit kegitan ref yang sudah ada
      $result        &= $this->Execute($this->mSqlQueries['do_delete_keg_ref_unit'], array(
         $param['id']
      ));
      // update kegiatan ref
      $result        &= $this->Execute($this->mSqlQueries['do_update_kegiatan_ref'], array(
         $param['output_id'],
         $param['kode'],
         $param['nama'],
         $rkaklSubKegiatanId,
         $param['id']
      ));
      // insert/definisikan unit kerja kegiatan dengan data yang baru
      if(!empty($param['unit'])){
         foreach ($param['unit'] as $unit) {
            $result  &= $this->Execute($this->mSqlQueries['do_insert_keg_ref_unit'], array(
               $param['id'],
               $unit['id']
            ));
         }
      }

      return $this->EndTrans($result);
   }

   public function DoDeleteKegiatanRef($id)
   {
      $result        = true;
      $this->StartTrans();
      $subKegiatan   = $this->Open($this->mSqlQueries['get_rkakl_subkegiatan_id'], array($id));
      $deleteRkakl   = false;
      if($subKegiatan){
         $subKegiatanId    = $subKegiatan[0]['id'];
         $checkSubKegiatan = $this->Open($this->mSqlQueries['check_rkakl_sub_kegiatan'], array(
            $subKegiatanId
         ));

         if($checkSubKegiatan){
            if($checkSubKegiatan[0]['count'] === 1){
               $deleteRkakl   = true;
            }
         }
      }else{
         $subKegiatanId    = NULL;
      }
      $result        &= $this->Execute($this->mSqlQueries['do_bulk_delete_related_komponen_unit'], array(
         $id
      ));
      $result        &= $this->Execute($this->mSqlQueries['do_delete_keg_ref_unit'], array(
         $id
      ));

      if(!is_null($subKegiatanId) AND $deleteRkakl === true){
         $result     &= $this->Execute($this->mSqlQueries['do_delete_related_rkakl_subkegiatan'], array(
            $subKegiatanId
         ));
      }

      $result        &= $this->Execute($this->mSqlQueries['do_delete_kegiatan_ref'], array(
         $id
      ));

      return $this->EndTrans($result);
   }
   public function GetKegiatanRefById($id)
   {
      $return        = $this->Open($this->mSqlQueries['get_kegiatan_ref_by_id'], array(
         $id
      ));
      $kegrefUnit    = $this->Open($this->mSqlQueries['get_keg_ref_unit'], array(
         $id
      ));

      if($return){
         $result['data']   = self::ChangeKeyName((array)$return[0]);
         $result['unit']   = self::ChangeKeyName((array)$kegrefUnit);
         return (array)$result;
      }else{
         return null;
      }
   }

   public function GetDataDetailBelanja($offset, $limit, $param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_detail_belanja'], array(
         $param['ref_id'],
         $offset,
         $limit
      ));

      return $return;
   }

   public function Count()
   {
      $return     = $this->Open($this->mSqlQueries['count'], array());

      if($return){
         return $return[0]['count'];
      }else{
         return 0;
      }
   }

   public function DoInsertKomponenKegiatan($param = array())
   {
      $result     = true;
      $this->StartTrans();
      if(!is_array($param)){
         $result  &= false;
      }

      $result     &= $this->Execute($this->mSqlQueries['do_insert_komponen_kegiatan'], array(
         $param['komponen_id'],
         $param['kegref_id'],
         $param['nominal']
      ));

      if(!empty($param['unit_kerja'])){
         foreach ($param['unit_kerja'] as $unitKerja) {
            $result  &= $this->Execute($this->mSqlQueries['do_insert_komponen_unit_kerja'], array(
               $param['komponen_id'],
               $param['kegref_id'],
               $unitKerja['id'],
               $param['nominal']
            ));
         }
      }

      return $this->EndTrans($result);
   }

   public function GetDetailBelanjaDetail($kegrefId, $id)
   {
      $return     = $this->Open($this->mSqlQueries['get_detail_belanja_detail'], array(
         $kegrefId,
         $id
      ));

      return $return[0];
   }

   public function GetDataKomponenUnit($kegrefId, $id)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_komponen_unit'], array(
         $kegrefId,
         $id
      ));

      return $return;
   }

   public function DoUpdateDetailBelanja($param = array())
   {
      $result     = true;
      $this->StartTrans();
      if(!is_array($param)){
         $result  &= false;
      }

      // hapus semua komponen unit kerja
      $result     &= $this->Execute($this->mSqlQueries['delete_komponen_unit'], array(
         $param['kegref_id'],
         $param['komponen_id']
      ));

      // update komponen kegiatan
      $result     &= $this->Execute($this->mSqlQueries['do_update_komponen_kegiatan'], array(
         $param['komponen_id'],
         $param['kegref_id'],
         $param['nominal'],
         $param['komponen_id'],
         $param['kegref_id']
      ));

      if(!empty($param['unit_kerja'])){
         foreach ($param['unit_kerja'] as $unitKerja) {
            $result  &= $this->Execute($this->mSqlQueries['do_insert_komponen_unit_kerja'], array(
               $param['komponen_id'],
               $param['kegref_id'],
               $unitKerja['id'],
               $param['nominal']
            ));
         }
      }

      return $this->EndTrans($result);
   }

   public function CheckDetailBelanja($kegrefId, $id)
   {
      $return     = $this->Open($this->mSqlQueries['check_detail_belanja'], array(
         $id,
         $kegrefId
      ));

      if($return){
         if($return[0]['count'] <> 0){
            return false;
         }else{
            return true;
         }
      }else{
         return false;
      }
   }

   public function DoDeleteKomponenKegiatan($kegrefId, $id)
   {
      $result     = true;
      $this->StartTrans();
      $result     &= $this->Execute($this->mSqlQueries['delete_komponen_unit'], array(
         $kegrefId,
         $id
      ));
      $result     &= $this->Execute($this->mSqlQueries['delete_komponen_kegiatan'], array(
         $id,
         $kegrefId
      ));
      return $this->EndTrans($result);
   }

   public function createTree(&$list, $parent){
      $tree = array();
      foreach ($parent as $k=>$l){
         if(isset($list[$l['id']])){
            $l['item'] = $this->createTree($list, $list[$l['id']]);
         }
         $tree[] = $l;
      }
      return $tree;
   }

   /*
    * @param string $camelCasedWord Camel-cased word to be "underscorized"
    * @param string $case case type, uppercase, lowercase
    * @return string Underscore-syntaxed version of the $camelCasedWord
    */
   public static function humanize($camelCasedWord, $case = 'upper')
   {
      switch ($case) {
         case 'upper':
            $return     = strtoupper(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
            break;
         case 'lower':
            $return     = strtolower(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
            break;
         case 'title':
            $return     = ucwords(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
            break;
         case 'sentences':
            $return     = ucfirst(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
            break;
         default:
            $return     = strtoupper(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
            break;
      }
      return $return;
   }

   /*
    * @desc change key name from input data
    * @param array $input
    * @param string $case based on humanize method
    * @return array
    */
   public function ChangeKeyName($input = array(), $case = 'lower')
   {
      if(!is_array($input))
      {
         return $input;
      }

      foreach ($input as $key => $value) {
         if(is_array($value))
         {
            foreach ($value as $k => $v) {
               $array[$key][self::humanize($k, $case)] = $v;
            }
         }
         else
         {
            $array[self::humanize($key, $case)]  = $value;
         }
      }

      return (array)$array;
   }

   /**
    * @param string  path_info url to be parsed, default null
    * @return string parsed_url based on Dispatcher::Instance()->getQueryString();
    */
   public function __getQueryString($pathInfo = null)
   {
      $parseUrl            = is_null($pathInfo) ? parse_url($_SERVER['QUERY_STRING']) : parse_url($pathInfo);
      $explodedUrl         = explode('&', $parseUrl['path']);
      $requestData         = '';
      foreach ($explodedUrl as $path) {
         if(preg_match('/^mod=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^sub=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^act=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^typ=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^ascomponent=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/uniqid=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }

         list($key, $value)   = explode('=', $path);
         $requestData[$key]   = Dispatcher::Instance()->Decrypt($value);
      }
      if(method_exists(Dispatcher::Instance(), 'getQueryString') === true){
         $queryString         = Dispatcher::Instance()->getQueryString($requestData);
      }else{
         foreach ($requestData as $key => $value) {
            $query[$key]      = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString         = urldecode(http_build_query($query));
      }
      return $queryString;
   }

   public function indonesianDate($date, $format = 'long')
   {
      $timeFormat          = '%02d:%02d:%02d';
      $patern              = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/';
      $patern1             = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/';
      switch ($format) {
         case 'long':
            $dateFormat    = '%02d %s %04d';
            break;
         case 'short':
            $dateFormat    = '%02d-%s-%04d';
            break;
         default:
            $dateFormat    = '%02d %s %04d';
            break;
      }

      if(preg_match($patern, $date, $matches)){
         $year    = (int)$matches[1];
         $month   = (int)$matches[2];
         $day     = (int)$matches[3];
         $hour    = (int)$matches[4];
         $minute  = (int)$matches[5];
         $second  = (int)$matches[6];
         $mon     = $this->indonesianMonth[$month];

         $date    = sprintf($dateFormat, $day, $mon, $year);
         $time    = sprintf($timeFormat, $hour, $minute, $second);
         $result  = $date.' '.$time;

      }elseif(preg_match($patern1, $date, $matches)){
         $year    = (int)$matches[1];
         $month   = (int)$matches[2];
         $day     = (int)$matches[3];
         $mon     = $this->indonesianMonth[$month];

         $date    = sprintf($dateFormat, $day, $mon, $year);

         $result  = $date;
      }else{
         $date    = getdate();
         $year    = $date['year'];
         $month   = $date['mon'];
         $day     = $date['mday'];
         $hour    = $date['hours'];
         $minute  = $date['minutes'];
         $second  = $date['seconds'];
         $mon     = $this->indonesianMonth[$month];

         $date    = sprintf($dateFormat, $day, $mon, $year);
         $time    = sprintf($timeFormat, $hour, $minute, $second);
         $result  = $date.' '.$time;
      }

      return $result;
   }
}
?>