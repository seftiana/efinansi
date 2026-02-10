<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class RencanaPengeluaran extends Database
{
   protected $mSqlFile;
   protected $mUserLogedId = '';
   public $_POST;
   public $_GET;
      public $indonesianMonth    = array(
      0 => array(
         'id' => 1,
         'name' => 'Januari'
      ), array(
         'id' => 2,
         'name' => 'Februari'
      ), array(
         'id' => 3,
         'name' => 'Maret'
      ), array(
         'id' => 4,
         'name' => 'April'
      ), array(
         'id' => 5,
         'name' => 'Mei'
      ), array(
         'id' => 6,
         'name' => 'Juni'
      ), array(
         'id' => 7,
         'name' => 'Juli'
      ), array(
         'id' => 8,
         'name' => 'Agustus'
      ), array(
         'id' => 9,
         'name' => 'September'
      ), array(
         'id' => 10,
         'name' => 'Oktober'
      ), array(
         'id' => 11,
         'name' => 'November'
      ), array(
         'id' => 12,
         'name' => 'Desember'
      )
   );
      
   function __construct($connectionNumber=0)
   {
      $this->_POST         = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET          = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->mSqlFile      = 'module/angsuran_detil/business/rencana_pengeluaran.sql.php';
      $this->mUserLogedId  = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      parent::__construct($connectionNumber);
   }

   /**
    * @param Array $param; BOOLEAN active, BOOLEAN open
    * @param Array $return
    */
   public function GetPeriodeTahun($param = array())
   {
      $default       = array(
         'active' => false,
         'open' => false
      );
      $option        = array_merge($default, (array)$param);
      $return        = $this->Open($this->mSqlQueries['get_periode_tahun'], array(
         (int)((bool)$option['active'] === false OR $option['active'] === NULL),
         (int)((bool)$option['open'] === false OR $option['open'] === NULL)
      ));

      return $return;
   }

   //==GET==
   function GetData ($offset, $limit, $param = array())
   {
       if(empty($param['bulan']) || ($param['bulan']=='all')) {
           $flag = 1;
       } else {
           $flag = 0;
       }
      $return        = $this->Open($this->mSqlQueries['get_data'], array(
         $param['ta_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['jenis_kegiatan'],
         (int)($param['jenis_kegiatan'] == '' OR $param['jenis_kegiatan'] === NULL OR strtolower($param['jenis_kegiatan']) == 'all'),
         '%'.$param['kode'].'%',
         '%'.$param['nama'].'%',
         $param['bulan'],$flag,
         $offset,
         $limit,
         $param['pengadaan'],
         (int)($param['pengadaan'] === NULL OR $param['pengadaan'] == '' OR strtolower($param['pengadaan']) == 'all')
      ));

      return $return;
   }

   function GetCount ($param = array())
   {
       if(empty($param['bulan']) || ($param['bulan']=='all')) {
           $flag = 1;
       } else {
           $flag = 0;
       }
            
      $return     = $this->Open($this->mSqlQueries['get_count'], array(
         $param['ta_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['jenis_kegiatan'],
         (int)($param['jenis_kegiatan'] == '' OR $param['jenis_kegiatan'] === NULL OR strtolower($param['jenis_kegiatan']) == 'all'),
         '%'.$param['kode'].'%',
         '%'.$param['nama'].'%',
         $param['pengadaan'],
         (int)($param['pengadaan'] === NULL OR $param['pengadaan'] == '' OR strtolower($param['pengadaan']) == 'all'),
         $param['bulan'],$flag
      ));

      if($return){
         return $return[0]['count'];
      }else{
         return 0;
      }
   }

   function GetDataResume($param = array())
   {
       if(empty($param['bulan']) || ($param['bulan']=='all')) {
           $flag = 1;
       } else {
           $flag = 0;
       }
              
      $return        = $this->Open($this->mSqlQueries['get_data_resume'], array(
         $param['ta_id'],
         '%'.$param['kode'].'%',
         '%'.$param['nama'].'%',
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['jenis_kegiatan'],
         (int)($param['jenis_kegiatan'] == '' OR $param['jenis_kegiatan'] === NULL OR strtolower($param['jenis_kegiatan']) == 'all'),
         $param['pengadaan'],
         (int)($param['pengadaan'] === NULL OR $param['pengadaan'] == '' OR strtolower($param['pengadaan']) == 'all'),
         $param['bulan'],$flag
      ));

      return $return;
   }

   /**
    * @param Int $id
    * @return Array $result[0]
    * @description untuk menampilkan sub kegiatan detail
    * kegiatan detail
    */
   public function GetSubKegiatanDetail($id)
   {
      $result  = $this->Open($this->mSqlQueries['get_sub_kegiatan_detail'],array(
         $id
      ));

      return $result[0];
   }

   /**
    * @param Int $id; id kegdetId
    * @param Array $return
    */
   public function GetDataDetailBelanja($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_detail_belanja'], array(
         $id
      ));

      return $return;
   }

   /**
    * @param Int $taId; ID Tahun Anggaran
    * @param Int $unitId; ID Unit Kerja
    * @param Int $id; ID kegiatan detail (RKAT)
    * @return Float Nominal total pengeluaran berdasarkan unit kerja dan tahun anggaran, exclude RKAT bersangkutan
    */
   public function GetNominalTotalPengeluaran($taId, $unitId, $id)
   {
      $return     = $this->Open($this->mSqlQueries['get_nominal_total_pengeluaran'], array(
         $unitId,
         $taId,
         $id
      ));

      if($return){
         return $return[0]['nominal'];
      }else{
         return 0;
      }
   }

   /**
    * @param Int $taId; ID Tahun Anggaran
    * @param Int $unitId; ID Unit Kerja
    * @return Float Nominal Total rencana penerimaan by tahun anggaran dan unit kerja
    */
   public function GetNominalRencanaPenerimaan($taId, $unitId)
   {
      $return     = $this->Open($this->mSqlQueries['get_nominal_rencana_penerimaan'], array(
         $unitId,
         $taId
      ));

      if($return){
         return $return[0]['nominal'];
      }else{
         return 0;
      }
   }

   function DoAddRencanaPengeluaranRutin($data = array()) {
      $result     = true;
      $this->StartTrans();
      $dataKegiatan  = $data['kegiatan'];

      foreach ($data['komponen'] as $komp) {
         $result  &= $this->Execute($this->mSqlQueries['do_add_rencana_pengeluaran_rutin'], array(
            $dataKegiatan['id'],
            $komp['kode'],
            $komp['nama'],
            $komp['jumlah'],
            $komp['satuan'],
            $komp['biaya'],
            $komp['nominal_total'],
            ($komp['deskripsi'] == '' ? '-' : $komp['deskripsi']),
            $komp['formula'],
            $komp['biaya'],
            // $komp['jumlah'],
            0,
            // $komp['nominal_total'],
            0,
            $komp['mak_id'],
            $komp['mak_id'],
            $komp['sumber_dana_id'],
            $komp['sumber_dana_id'],
            $komp['sumber_dana_id'],
            $this->mUserLogedId,
            $komp['status'],
            $komp['id']
         ));

         $result     &= $this->Execute($this->mSqlQueries['do_insert_komponen_kegiatan'], array(
            $komp['id'],
            $dataKegiatan['subkeg_id'],
            $komp['biaya']
         ));
      }

      return $this->EndTrans($result);
   }

   public function DoUpdateRencanaPengeluaranRutin($data)
   {
      $result     = true;
      $this->StartTrans();
      $dataKegiatan  = $data['kegiatan'];
      // hapus semua rencana pengeluaran yg sudah ada berdasarkan rkat (kegiatan)
      $result     &= $this->Execute($this->mSqlQueries['do_delete_rencana_pengeluaran_kegiatan'], array(
         $dataKegiatan['id']
      ));
      // insert kembali
      foreach ($data['komponen'] as $komp) {
         $result  &= $this->Execute($this->mSqlQueries['do_add_rencana_pengeluaran_rutin'], array(
            $dataKegiatan['id'],
            $komp['kode'],
            $komp['nama'],
            $komp['jumlah'],
            $komp['satuan'],
            $komp['biaya'],
            $komp['nominal_total'],
            ($komp['deskripsi'] == '' ? '-' : $komp['deskripsi']),
            $komp['formula'],
             // $komp['nominal_approve'],
            $komp['biaya'],
            // $komp['jumlah'],
            $komp['satuan_approve'],
            // $komp['nominal_total'],
            $komp['total_approve'],
            $komp['mak_id'],
            $komp['mak_id'],
            $komp['sumber_dana_id'],
            $komp['sumber_dana_id'],
            $komp['sumber_dana_id'],
            $this->mUserLogedId,
            $komp['status'],
            $komp['id']
						
         ));

         $result     &= $this->Execute($this->mSqlQueries['do_insert_komponen_kegiatan'], array(
            $komp['id'],
            $dataKegiatan['subkeg_id'],
            $komp['biaya']
         ));
      }

      return $this->EndTrans($result);
   }
   // =========================== edited =======================================
   function GetDataCetak ($id) {
      $result = $this->Open($this->mSqlQueries['get_data_cetak'], array($id));
     return $result;
   }

   function GetDataCetakApproved ($id) {
      $result = $this->Open($this->mSqlQueries['get_data_cetak_approved'], array($id));
      //$this->mdebug();
     return $result;
   }

   function GetDataById($id) {
      $result = $this->Open($this->mSqlQueries['get_data_by_id'], array($id));

     if($result)
        return $result[0];
     else
        return $result;
   }


   function GetDataTahunAnggaran(&$idaktif) {
      if(trim($idaktif)=='') {
       $id = $this->Open($this->mSqlQueries['get_ta_aktif'],array());
      if($id) {
         $idaktif = $id[0]['id'];
      }
      }
      $result = $this->Open($this->mSqlQueries['get_data_ta'],array());
     return $result;
   }

   function GetCountUnitKerja ($nama,$parent_id) {
     $result = $this->Open($this->mSqlQueries['get_count_unit_kerja'], array($parent_id,'%'.$nama.'%'));


     if (!$result)
       return 0;
     else
       return $result[0]['total'];
   }

   function GetUnitKerja($startRec,$itemViewed,$nama,$parent_id) {
      $ret = $this->Open($this->mSqlQueries['get_unit_kerja'],array($parent_id,'%'.$nama.'%',$startRec,$itemViewed));

      return $ret;
   }

   function GetKomponen($detail_id,$action)
   {
      $result = $this->Open($this->mSqlQueries['get_data_komponen'],array($detail_id,$detail_id));
      return $result;
   }

   function GetJenisKegiatan($detailkegiatan_id) {
      $ret = $this->Open($this->mSqlQueries['get_jenis_kegiatan'], array($id));
      if($ret){
         return $ret[0]['jenis_kegiatan'];
      }else{
         return $ret;
      }
   }

   function GetDataSatuanKomponen() {
     return $this->Open($this->mSqlQueries['get_data_satuan_komponen'],array());
   }

   function GetPosisiPagu($bas,$thanggar,$unit_id){
      $result     = $this->Open($this->mSqlQueries['get_posisi_rencana_pengeluaran_sekarang'],array(
         $bas,
         $thanggar,
         $unit_id
      ));
      return $result;
   }

   function GetBasPengeluaran($detail_id){
      $result = $this->Open($this->mSqlQueries['get_bas_pengeluaran'],array($detail_id));
      return $result;
   }

   function GetJumlahPengeluaranPerBas($detail_id){
      $result = $this->Open($this->mSqlQueries['get_jumlah_pengeluaran_perkegiatan_per_bas'],array($detail_id));
      return $result;
   }

   function GetPagu($bas,$unit_id,$thanggar){
      $result = $this->Open($this->mSqlQueries['get_pagu'],array($bas,$unit_id,$thanggar));
      return $result;
   }

 function DoAddRutin($data,&$msg) {
//$this->SetDebugOn();
      $msg      = '';
     $status   = true;
     if(isset($data['ceklis'])) {
        foreach($data['ceklis'] as $key => $val ) {
          $data['komponen'][$key]['sumber_dana_id'] = ($data['komponen'][$key]['sumber_dana_id'] == NULL) ? NULL : $data['komponen'][$key]['sumber_dana_id'];
         $ret = $this->Execute($this->mSqlQueries['do_add'],
                array($data['kegiatan']['kegiatandetail_id'],
                     $data['komponen'][$key]['kode'],
                     $data['komponen'][$key]['nama'],
                     $data['komponen'][$key]['jumlah'],
                     $data['komponen'][$key]['satuan'],
                     $data['komponen'][$key]['biaya'],
                     $data['komponen'][$key]['total'],
                     $data['komponen'][$key]['deskripsi'],
                     $data['komponen'][$key]['formula'],
                     $data['komponen'][$key]['biaya'],
                     $data['komponen'][$key]['jumlah'],
                     $data['komponen'][$key]['total'],
                        $data['komponen'][$key]['mak_id'],
                        $data['komponen'][$key]['sumber_dana_id'],
                        $this->mUserLogedId));
               /**
             * untuk cek saja ,jikalau terjadi gagal proses query.
                */
               $s = sprintf($this->mSqlQueries['do_add'],
                     $data['kegiatan']['kegiatandetail_id'],
                     $data['komponen'][$key]['kode'],
                     $data['komponen'][$key]['nama'],
                     $data['komponen'][$key]['jumlah'],
                     $data['komponen'][$key]['satuan'],
                     $data['komponen'][$key]['biaya'],
                     $data['komponen'][$key]['total'],
                     $data['komponen'][$key]['deskripsi'],
                     $data['komponen'][$key]['formula'],
                     $data['komponen'][$key]['biaya'],
                     $data['komponen'][$key]['jumlah'],
                     $data['komponen'][$key]['total'],
                        $data['komponen'][$key]['mak_id'],
                        $data['komponen'][$key]['sumber_dana_id'],
                        $this->mUserLogedId);
               /**/
            //$msg .= 'Query : '.$s;
         if(!$ret){
               $msg .= $data['komponen'][$key]['kode'].',';
              // $msg .= 'Query : '.$s;
           $status = false;

         }


       }
     } else $status = false;

      return $status;
   }

   function DoAddNonRutin($data) {

      $ret = $this->Execute($this->mSqlQueries['do_add'],
                              array(
                                 $data['kegiatan']['kegiatandetail_id'],
                                    $data['tambah']['kode'],
                                 $data['tambah']['nama'],
                                 $data['tambah']['jumlah'],
                                 $data['tambah']['satuan'],
                                 $data['tambah']['biaya'],
                                 ($data['tambah']['jumlah']*$data['tambah']['biaya']),
                                 $data['tambah']['deskripsi'],
                                 '',0,'',0,NULL));
    return $ret;
   }

   function DoUpdateRutin($data)
   {

      $msg='';
      $this->StartTrans();
      $status = true;
      $ret=true;
      if(empty($data['ceklis'])) $data['ceklis'] = array();
      if(isset($data['komponen'])){
      foreach($data['komponen'] as $val ) {
        $val['sumber_dana_id'] = ($val['sumber_dana_id'] == NULL) ? NULL : $val['sumber_dana_id'];
      //print_r ($data['komponen']);exit();
      //print_r ($val);exit();
         if ($val['rencanapengeluaran_id'] && in_array($val['id'],$data['ceklis'])) {
            $ret = $this->Execute($this->mSqlQueries['do_update_rutin'],
                  array(
                        $data['kegiatan']['kegiatandetail_id'],
                                $val['kode'],
                                $val['nama'],
                                $val['jumlah'],
                                $val['satuan'],
                                $val['biaya'],
                                $val['total'],
                                $val['deskripsi'],
                                $val['formula'],
                                $val['biaya'],
                                $val['jumlah'],
                                $val['total'],
                                $val['mak_id'],
                                $val['sumber_dana_id'],
                                $this->mUserLogedId,
                        $val['rencanapengeluaran_id']));

       } elseif ($val['rencanapengeluaran_id'] && !in_array($val['id'],$data['ceklis'])) {
            $ret = $this->Execute($this->mSqlQueries['do_delete'], array($val['rencanapengeluaran_id']));
         } elseif (!$val['rencanapengeluaran_id'] && in_array($val['id'],$data['ceklis'])) {
            $ret = $this->Execute($this->mSqlQueries['do_add'], array(
                                                                     $data['kegiatan']['kegiatandetail_id'],
                                                                     $val['kode'],
                                                                     $val['nama'],
                                                                     $val['jumlah'],
                                                                     $val['satuan'],
                                                                     $val['biaya'],
                                                                     $val['total'],
                                                                     $val['deskripsi'],
                                                                     $val['formula'],
                                                                     $val['biaya'],
                                                                     $val['jumlah'],
                                                                     $val['total'],
                                                                     $val['mak_id'],
                                                                     $val['sumber_dana_id'],
                                                                     $this->mUserLogedId
                                                    ));
         }

         if(!$ret){
            $msg .= $data['komponen'][$key]['kode'].',';
            $status = false;
         }
      }
   }
      $this->EndTrans($status);
      return $status.$msg.$x;
    }

   function DoUpdateNonRutin($data) {
     $ret = $this->Execute($this->mSqlQueries['do_update_non_rutin'], array(
                                                    $data['tambah']['kode'],
                                                    $data['tambah']['nama'],
                                                    $data['tambah']['jumlah'],
                                                    $data['tambah']['satuan'],
                                                    $data['tambah']['biaya'],
                                                    $data['tambah']['deskripsi'],
                                                    $data['tambah']['id']
                                                     ));
      //$this->mdebug();

      return $ret;
    }

   function DoDelete($id) {
      $ret = $this->Execute($this->mSqlQueries['do_delete'], array($id));
       return $ret;
   }

   function GetComboJenisKegiatan(){
      $return  = $this->Open($this->mSqlQueries['get_jenis_kegiatan'], array());

      return $return;
   }

   /**
    * untuk mendapatkan total sub unit
    * @since 3 Januari 2012
    */
   public function GetTotalSubUnitKerja($parentId)
   {
      $result = $this->Open($this->mSqlQueries['get_total_sub_unit_kerja'], array($parentId));
      return $result[0]['total'];
   }

   /**
    * untuk mendapatkan komponen dari kegiatan non rutin / pengembangan
    * @since 7 Januari 2012
    */
   public function GetKomponenNonRutin($detail_id){
      $result = $this->Open($this->mSqlQueries['get_data_komponen_non_rutin'],array($detail_id));
      return $result;
   }

      /**
    * untuk mendapatkan jumal rencana penerimaan yang telah di approved
    * @since 8 Maret 2012
    */
      public function GetMaxRencanaPenerimaanApproved($unit_id,$thanggar)
   {
      $result = $this->Open($this->mSqlQueries['get_max_rencana_penerimaan_approved'],array($unit_id,$thanggar));
      if (!$result)
      return 0;
     else
         return $result[0]['max_nominal'];
      }

      public function GetMaxRencanaPengeluaran($unit_id,$thanggar)
   {
      $result = $this->Open($this->mSqlQueries['get_max_rencana_pengeluaran'],array($unit_id,$thanggar));
      if (!$result)
      return 0;
     else
         return $result[0]['max_nominal'];
      }

      public function GetTotalPengeluaranKomponenEdit($kegId)
   {
      $result = $this->Open($this->mSqlQueries['get_total_pengeluaran_komponen_edit'],array($kegId));
      //$msg = sprintf($this->mSqlQueries['get_total_pengeluaran_komponen_edit'],$kegId);
      if (!$result)
      return 0;
     else
         return $result[0]['max_nominal'];
      }

    public function GetTipeUnitKerja($unit_kerja_id)
    {
        $result = $this->Open($this->mSqlQueries['get_tipe_unit'],array($unit_kerja_id));
        return $result[0]['tipe'];
    }

    /**
     * untuk mendapatkan jumlah detail belanja di rencana pengeluaran
     *
     */
    public function GetCountDetaiBelanja($kegiatanDetailId)
    {
      $result = $this->Open($this->mSqlQueries['get_count_detai_belanja'],array($kegiatanDetailId));
      if($result){
         return $result[0]['total'];
      } else {
         return 0;
      }

   }

   function parsingUrl($file)
   {
      $msg = Messenger::Instance()->Receive($file);

      if (!empty($msg))
      {
         $tmp['data'] = $msg[0][0];
         $tmp['msg']['action'] = $msg[0][1];
         $tmp['msg']['message'] = $msg[0][2];

         return $tmp;
      }
      else
      {

         return false;
      }
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
      if(!is_array($input)){
         return $input;
      }

      foreach ($input as $key => $value) {
         if(is_array($value)){
            foreach ($value as $k => $v) {
               $array[$key][self::humanize($k, $case)] = $v;
            }
         }
         else{
            $array[self::humanize($key, $case)]  = $value;
         }
      }

      return (array)$array;
   }

   /**
    * @param string  path_info url to be parsed, default null
    * @return string parsed_url based on Dispatcher::Instance()->getQueryString();
    */
   public function _getQueryString($pathInfo = null)
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
}
?>