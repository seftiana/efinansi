<?php
#doc
#    classname:    RencanaKinerjaTahunan
#    scope:        PUBLIC
#
#/doc

class RencanaKinerjaTahunan extends Database
{
   #    internal variables
   public $_POST;
   public $_GET;
   public $method;
   private $mUserId    = null;
   protected $mSqlFile;
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
   #    Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->mSqlFile   = 'module/rencana_kinerja_tahunan_kegiatan/business/rencana_kinerja_tahunan.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->method     = strtolower($_SERVER['REQUEST_METHOD']);
      if(class_exists('Security')){
         $this->mUserId  = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      }
      parent::__construct($connectionNumber);
   }

   public function getCountDetailBelanja($kegiatanDetailId)
   {
      $return     = $this->Open($this->mSqlQueries['get_count_detail_belanja'], array($kegiatanDetailId));
      if($return){
         return $return[0]['total'];
      }else{
         return 0;
      }       
   }
   public function getPeriodeTahun($param = array())
   {
      $default    = array(
         'active' => false,
         'open' => false
      );
      $options    = array_merge($default, (array)$param);
      $return     = $this->Open($this->mSqlQueries['get_periode_tahun'], array(
         (int)($options['active'] === false),
         (int)($options['open'] === false)
      ));

      return $return;
   }

   public function getUserId()
   {
      return (int)$this->mUserId;
   }

   public function getRangeYear()
   {
      $data       = $this->Open($this->mSqlQueries['set_date'], array());
      $getdate    = getdate();
      $currMon    = (int)$getdate['mon'];
      $currYear   = (int)$getdate['year'];

      if(!empty($data)){
         $start_date    = date('Y-m-d', strtotime($data[0]['startDate']));
         $end_date      = date('Y-m-d', strtotime($data[0]['endDate']));
      }else{
         $start_date    = date('Y-m-d', mktime(0,0,0, $currMon, 1, $currYear));
         $end_date      = date('Y-m-t', strtotime($start_date));
      }

      return compact('start_date', 'end_date');
   }

   public function GetComboJenisKegiatan()
   {
      $return     = $this->Open(
         $this->mSqlQueries['get_combo_jenis_kegiatan'],
         array()
      );

      return $return;
   }

   # get combo prioritas
   function GetComboPrioritas() {
      $result = $this->Open($this->mSqlQueries['get_combo_prioritas'], array());
      return $result;
   }

   public function GetTahunAnggaranInput()
   {
      $result = $this->Open(
         $this->mSqlQueries['get_combo_tahun_anggaran_input'],
         array()
      );

      return $result;
   }

   
   public function GetTahunAnggaranById($tahunAnggaranId)
   {
      $result = $this->Open($this->mSqlQueries['get_tahun_anggaran_by_id'],array(
          $tahunAnggaranId
      ));

      return $result[0];
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

   # get data rencana kinerja tahunan kegiatan
   public function GetData($offset, $limit, $param = array())
   {
       if(empty($param['bulan']) || ($param['bulan']=='all')) {
           $flag = 1;
       } else {
           $flag = 0;
       }
       
      $return     = $this->Open($this->mSqlQueries['get_data'], array(
         '%'.$param['kode'].'%',
         '%'.$param['nama'].'%',
         $param['ta_id'],
         $param['program_id'],
         (int)(($param['program_id'] == '' OR $param['program_id'] === NULL) OR strtolower($param['program_id']) == 'all'),
         $param['jenis'],
         (int)(($param['jenis'] == '' OR $param['jenis'] === NULL) OR strtolower($param['jenis']) == 'all'),
         $param['prioritas'],
         (int)(($param['prioritas'] == '' OR $param['prioritas'] === NULL) OR strtolower($param['prioritas']) == 'all'),
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['bulan'],$flag,
         $offset,
         $limit
      ));

      return $return;
   }

   public function getDataDetail($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_kegiatan_detail'], array(
         $id
      ));

      return $return[0];
   }

   public function doSaveKegiatan($param = array())
   {
      $result     = true;
      $this->StartTrans();
      if(!is_array($param)){
         $result  &= false;
      }
      // insert into table kegiatan
      $result     &= $this->Execute($this->mSqlQueries['do_insert_kegiatan'], array(
         $param['unit_id'],
         $param['program_id'],
         $param['latar_belakang'],
         $param['indikator'],
         $param['baseline'],
         $param['final'],
         $param['ta_id'],
         $param['nama_pic'],
         $this->mUserId
      ));
      // get last insert id
      $kegiatanId       = $this->LastInsertId();
      // insert into table kegiatan_detail
      $result     &= $this->Execute($this->mSqlQueries['do_insert_kegiatan_detail'], array(
         $kegiatanId,
         $param['sub_kegiatan_id'],
         $param['deskripsi'],
         $param['catatan'],
         $param['kegiatan_nama'],
         $param['tupoksi_id'],
         $param['tupoksi_id'],
         $param['start_date'],
         $param['end_date'],
         $param['prioritas'],
         $param['mastuk'],
         $param['mastk'],
         $param['keltuk'],
         $param['keltk'],
         $param['ikk_id'],
         $param['ikk_id'],
         $param['iku_id'],
         $param['iku_id'],
         $this->mUserId
      ));
      return $this->EndTrans($result);
   }

   public function doUpdateKegiatan($param = array())
   {
      $result     = true;
      $this->StartTrans();
      if(!is_array($param)){
         $result  &= false;
      }
      $kegiatanId       = $param['keg_id'];
      // insert into table kegiatan
      $result     &= $this->Execute($this->mSqlQueries['do_update_kegiatan'], array(
         $param['unit_id'],
         $param['program_id'],
         $param['latar_belakang'],
         $param['indikator'],
         $param['baseline'],
         $param['final'],
         $param['ta_id'],
         $param['nama_pic'],
         $this->mUserId,
         $kegiatanId
      ));
      // get last insert id
      // insert into table kegiatan_detail
      $result     &= $this->Execute($this->mSqlQueries['do_update_kegiatan_detail'], array(
         $kegiatanId,
         $param['sub_kegiatan_id'],
         $param['deskripsi'],
         $param['catatan'],
         $param['kegiatan_nama'],
         $param['tupoksi_id'],
         $param['tupoksi_id'],
         $param['start_date'],
         $param['end_date'],
         $param['prioritas'],
         $param['mastuk'],
         $param['mastk'],
         $param['keltuk'],
         $param['keltk'],
         $param['ikk_id'],
         $param['ikk_id'],
         $param['iku_id'],
         $param['iku_id'],
         $this->mUserId,
         $param['id'],
         $kegiatanId
      ));
      return $this->EndTrans($result);
   }

   public function doDeleteKegiatan($id, $kegiatan_id)
   {
      $result     = true;
      $this->StartTrans();
      // delete kegiatan detail
      $result     &= $this->Execute($this->mSqlQueries['delete_kegiatan_detil'], array(
         $id,
         $kegiatan_id
      ));
      // delete data kegiatan
      $result     &= $this->Execute($this->mSqlQueries['delete_kegiatan'], array(
         $kegiatan_id
      ));
      return $this->EndTrans($result);
   }

   // ------------------------------------------------------------------------------------- //
   function GetUnit($user_id)
   {
      $return     = $this->Open($this->mSqlQueries['get_unit'], array($user_id));

      return $return[0];
   }

   # mendapatkan semua tahun periode
   public function GetComboTahunAnggaranAll()
   {
      $result = $this->Open($this->mSqlQueries['get_combo_tahun_anggaran_all'], array());

      return $result;
   }

   public function GetTahunAnggaranAktif()
   {
      $result = $this->Open($this->mSqlQueries['get_tahun_anggaran_aktif'], array());
      return $result[0];
   }

   public function CheckTahunAnggaran($id)
    {
       $result    = $this->Open($this->mSqlQueries['check_tahun_anggaran'], array($id));

       return $result[0];
    }



   // insert into kegiatan
   function CekKegiatan($unitKerjaId = 0,$programId = 0,$tahunAnggaran = 0)
   {
      $result = $this->Open($this->mSqlQueries['cek_kegiatan'],
                              array(
                                    $unitKerjaId,
                                    $programId,
                                    $tahunAnggaran));
      return $result[0]['total'];
   }
   function InsertIntoKegiatan($data)
   {
      $data_id            = $data['data_id'];
      $tahun_anggaran     = $data['tahun_anggaran'];
      $kode_sistem        = $data['kode_sistem'];
      $id_unit            = $data['id_unit'];
      $nama_unit          = $data['nama_unit'];
      $program            = $data['program'];
      $kegiatan           = $data['kegiatan'];
      $sub_kegiatan       = $data['sub_kegiatan'];
      $sub_kegiatan_nama  = $data['sub_kegiatan_nama'];
      $ikk_id             = $data['ikk_id'];
      $ikk                = $data['ikk'];
      $iku_id             = $data['iku_id'];
      $iku                = $data['iku'];
      $tupoksi_id         = $data['tupoksi_id'];
      $tupoksi            = $data['tupoksi'];
      $latar_belakang     = trim($data['latar_belakang']);
      $indikator          = trim($data['indikator']);
      $baseline           = trim($data['baseline']);
      $final              = trim($data['final']);
      $deskripsi          = trim($data['deskripsi']);
      $catatan            = trim($data['catatan']);
      $waktu_pelaksanaan_mulai    = $data['waktu_pelaksanaan_mulai_year'].'-'.$data['waktu_pelaksanaan_mulai_mon'].'-01';
      $waktu_pelaksanaan_akhir    = $data['waktu_pelaksanaan_selesai_year'].'-'.$data['waktu_pelaksanaan_selesai_mon'].'-01';
      $output             = trim($data['output']);
      $prioritas          = $data['prioritas'];
      $mastuk             = $data['mastuk'];
      $mastk              = $data['mastk'];
      $keltuk             = $data['keltuk'];
      $keltk              = $data['keltk'];
      $satker_pimpinan        = $data['satker_pimpinan'];
      $satker_pimpinan_label  = $data['satker_pimpinan_label'];
      $pimpinan_unit          = $data['unitkerja_pimpinan'];
      $nama_pic               = $data['nama_pic'];
      $user_id            = $data['user_id'];

      if($this->CekKegiatan($id_unit,$program,$tahun_anggaran) > 0){
         $result = true;
         $status = 1;
      } else {
         $result     = $this->Execute(
            $this->mSqlQueries['insert_into_kegiatan'],
            array(
               $id_unit,
               $program,
               $latar_belakang,
               $indikator,
               $baseline,
               $final,
               $tahun_anggaran,
               $satker_pimpinan,
               $pimpinan_unit,
               $nama_pic,
               $user_id
               )
            );
         $status = 0;
      }

      if($result){
         // get last insert id kegiatan
         if($status == 0){
            $last_id    = $this->Open($this->mSqlQueries['get_last_id'], array());
            $last_id    = $last_id[0]['last_id'];
         } else {
            $last_id    = $this->Open($this->mSqlQueries['get_kegiatan_id'],
                           array($id_unit,$program,$tahun_anggaran));
            $last_id    = $last_id[0]['keg_id'];
         }
         // insert data kegiatan detil

         $res        = $this->Execute(
            $this->mSqlQueries['do_add_detil_usulan_kegiatan'],
            array(
               $last_id,
               $sub_kegiatan,
               $deskripsi,
               $catatan,
               $output,
               $waktu_pelaksanaan_mulai,
               $waktu_pelaksanaan_akhir,
               $prioritas,
               $mastuk,
               $mastk,
               $keltuk,
               $keltk,
               empty($ikk_id)?NULL:$ikk_id,
               empty($iku_id)?NULL:$iku_id,
               empty($tupoksi_id) ? NULL :$tupoksi_id,
               $user_id
            )
         );

         if($res){
            return true;

         }else{
            $this->Execute(
               $this->mSqlQueries['delete_kegiatan'],
               array(
                  $last_id
               )
            );
            return $this->GetLastError();
         }
      }else{
         return $this->GetLastError();
      }
   }


   # update data kegiatan
   function UpdateDataKegiatan($data)
   {
      //$this->setDebugOn();
      $data_id            = $data['data_id'];
      $kegiatan_id        = $data['kegiatan_id'];
      $tahun_anggaran     = $data['tahun_anggaran'];
      $kode_sistem        = $data['kode_sistem'];
      $id_unit            = $data['id_unit'];
      $nama_unit          = $data['nama_unit'];
      $program            = $data['program'];
      $kegiatan           = $data['kegiatan'];
      $sub_kegiatan       = $data['sub_kegiatan'];
      $sub_kegiatan_nama  = $data['sub_kegiatan_nama'];
      $ikk_id             = $data['ikk_id'];
      $ikk                = $data['ikk'];
      $iku_id             = $data['iku_id'];
      $iku                = $data['iku'];
      $tupoksi_id         = $data['tupoksi_id'];
      $tupoksi            = $data['tupoksi'];
      $latar_belakang     = trim($data['latar_belakang']);
      $indikator          = trim($data['indikator']);
      $baseline           = trim($data['baseline']);
      $final              = trim($data['final']);
      $deskripsi          = trim($data['deskripsi']);
      $catatan            = trim($data['catatan']);
      $waktu_pelaksanaan_mulai    = $data['waktu_pelaksanaan_mulai_year'].'-'.$data['waktu_pelaksanaan_mulai_mon'].'-01';
      $waktu_pelaksanaan_akhir    = $data['waktu_pelaksanaan_selesai_year'].'-'.$data['waktu_pelaksanaan_selesai_mon'].'-01';
      $output             = trim($data['output']);
      $prioritas          = $data['prioritas'];
      $mastuk             = $data['mastuk'];
      $mastk              = $data['mastk'];
      $keltuk             = $data['keltuk'];
      $keltk              = $data['keltk'];
      $satker_pimpinan        = $data['satker_pimpinan'];
      $satker_pimpinan_label  = $data['satker_pimpinan_label'];
      $pimpinan_unit          = $data['unitkerja_pimpinan'];
      $nama_pic               = $data['nama_pic'];
      $user_id            = $data['user_id'];

      if($this->CekKegiatan($id_unit,$program,$tahun_anggaran) > 0){
         $result = true;
         $status = 1;
      } else {        /**
         $result     = $this->Execute(
                     $this->mSqlQueries['update_kegiatan'],
                           array(
                              $id_unit,
                              $program,
                              $latar_belakang,
                              $indikator,
                              $baseline,
                              $final,
                              $tahun_anggaran,
                              $satker_pimpinan,
                              $pimpinan_unit,
                              $nama_pic,
                              $user_id,
                              $kegiatan_id
                           ));
         $status = 0;
         **/
         $result     = $this->Execute(
            $this->mSqlQueries['insert_into_kegiatan'],
            array(
               $id_unit,
               $program,
               $latar_belakang,
               $indikator,
               $baseline,
               $final,
               $tahun_anggaran,
               $satker_pimpinan,
               $pimpinan_unit,
               $nama_pic,
               $user_id
               )
            );
         $status = 0;
      }

      if($result){
         // get last insert id kegiatan
         if($status == 0){
            $kegiatan_id   = $this->Open($this->mSqlQueries['get_last_id'], array());
            $kegiatan_id   = $kegiatan_id[0]['last_id'];
         } else {
            $kegiatan_id   = $this->Open($this->mSqlQueries['get_kegiatan_id'],
                           array($id_unit,$program,$tahun_anggaran));
            $kegiatan_id   = $kegiatan_id[0]['keg_id'];
         }
         // insert data kegiatan detil

         $res        = $this->Execute(
            $this->mSqlQueries['update_kegiatan_detil'],
            array(
               $kegiatan_id,
               $sub_kegiatan,
               $deskripsi,
               $catatan,
               $output,
               $waktu_pelaksanaan_mulai,
               $waktu_pelaksanaan_akhir,
               $prioritas,
               $mastuk,
               $mastk,
               $keltuk,
               $keltk,
               empty($ikk_id)?NULL:$ikk_id,
               empty($iku_id)?NULL:$iku_id,
               empty($tupoksi_id) ? NULL :$tupoksi_id,
               $user_id,
               $data_id
            )
         );

         if($res){
            return true;

         }else{
            /**
            $this->Execute(
               $this->mSqlQueries['delete_kegiatan'],
               array(
                  $last_id
               )
            );
            */
            return $this->GetLastError();
         }
      }else{
         return $this->GetLastError();
      }

      exit;
   }

   function CountData($kode_sistem, $kode,$nama,$tahun_anggaran,$program,$jenis,$prioritas)
   {
      //$this->setDebugOn();
      if($jenis == ''){
         $jenis = 'all';
      }

      if($prioritas =='all' || $prioritas ==''){
         $flag = 1;
      } else {
         $flag = 0;
      }

      $return     = $this->Open(
         $this->mSqlQueries['count_data'],
         array(
            '%'.$kode.'%',
            '%'.$nama.'%',
            $tahun_anggaran,
            //$program,
            //(int)($program == ''),
            //$jenis,
            //(int)($jenis == 'all'),
            $kode_sistem,
            $kode_sistem,
            $prioritas,
            $flag
         )
      );

      if(!$return){
         return 0;
      }else{
         return $return[0]['total'];
      }
   }

   function GetDataById($id_detil, $id_kegiatan){
      $return     = $this->Open(
         $this->mSqlQueries['get_data_by_id'],
         array($id_detil, $id_kegiatan)
      );

      return $return[0];
   }

   function DeleteKegiatan($id, $id_kegiatan){
      $result     = $this->Execute(
         $this->mSqlQueries['delete_kegiatan_detil'],
         array(
            $id,
            $id_kegiatan
         )
      );
      return $result;
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

   function _dateToIndo($date) {
        $indonesian_months = array(
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
            'Nopember',
            'Desember'
        );

        if (preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $date, $patch)) {
            $year = (int) $patch[1];
            $month = (int) $patch[2];
            $month = $indonesian_months[(int) $patch[2]];
            $day = (int) $patch[3];
            $hour = (int) $patch[4];
            $min = (int) $patch[5];
            $sec = (int) $patch[6];

            $return = $day . ' ' . $month . ' ' . $year;
        } elseif (preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $date, $patch)) {
            $year = (int) $patch[1];
            $month = (int) $patch[2];
            $month = $indonesian_months[$month];
            $day = (int) $patch[3];

            $return = $day . ' ' . $month . ' ' . $year;
        } else {
            $return = (int) $date;
        }
        return $return;
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

   public function getModule($pathInfo = null)
   {
      $module              = NULL;
      if(is_null($pathInfo)){
         $parseUrl         = parse_url($_SERVER['QUERY_STRING']);
         $explodedUrl      = explode('&', $parseUrl['path']);
      }else{
         $parseUrl         = parse_url($pathInfo);
         $explodedUrl      = explode('&', $parseUrl['query']);
      }

      foreach ($explodedUrl as $path) {
         if(preg_match('/^mod=[a-zA-Z0-9_-]+/', $path, $matches)){
            list($key, $value)   = explode('=', $matches[0]);
            $module        = $value;
         }
      }

      return $module;
   }

   public function getSubModule($pathInfo = null)
   {
      $subModule           = NULL;
      if(is_null($pathInfo)){
         $parseUrl         = parse_url($_SERVER['QUERY_STRING']);
         $explodedUrl      = explode('&', $parseUrl['path']);
      }else{
         $parseUrl         = parse_url($pathInfo);
         $explodedUrl      = explode('&', $parseUrl['query']);
      }

      foreach ($explodedUrl as $path) {
         if(preg_match('/^sub=[a-zA-Z0-9_-]+/', $path, $matches)){
            list($key, $value)   = explode('=', $matches[0]);
            $subModule     = $value;
         }
      }

      return $subModule;
   }

   public function getAction($pathInfo = null)
   {
      $action           = NULL;
      if(is_null($pathInfo)){
         $parseUrl         = parse_url($_SERVER['QUERY_STRING']);
         $explodedUrl      = explode('&', $parseUrl['path']);
      }else{
         $parseUrl         = parse_url($pathInfo);
         $explodedUrl      = explode('&', $parseUrl['query']);
      }

      foreach ($explodedUrl as $path) {
         if(preg_match('/^act=[a-zA-Z0-9_-]+/', $path, $matches)){
            list($key, $value)   = explode('=', $matches[0]);
            $action        = $value;
         }
      }

      return $action;
   }

   public function getType($pathInfo = null)
   {
      $type                = NULL;
      if(is_null($pathInfo)){
         $parseUrl         = parse_url($_SERVER['QUERY_STRING']);
         $explodedUrl      = explode('&', $parseUrl['path']);
      }else{
         $parseUrl         = parse_url($pathInfo);
         $explodedUrl      = explode('&', $parseUrl['query']);
      }

      foreach ($explodedUrl as $path) {
         if(preg_match('/^typ=[a-zA-Z0-9_-]+/', $path, $matches)){
            list($key, $value)   = explode('=', $matches[0]);
            $type          = $value;
         }
      }

      return $type;
   }
}
?>