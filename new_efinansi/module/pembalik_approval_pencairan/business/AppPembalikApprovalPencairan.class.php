<?php

class AppPembalikApprovalPencairan extends Database {

   protected $mSqlFile;
   protected $mUserId = NULL;
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
   
   function __construct($connectionNumber=0) {
      $this->mSqlFile   = 'module/pembalik_approval_pencairan/business/apppembalikapprovalpencairan.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      parent::__construct($connectionNumber);
   }

   public function setUserId()
   {
      if(class_exists('Security')){
         if(method_exists(Security::Instance(), 'GetUserId')){
            $this->mUserId    = Security::Instance()->GetUserId();
         }else{
            $this->mUserId    = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
         }
      }
   }

   /**
    * [getUserId description]
    * @return Int userid
    */
   public function getUserId()
   {
      $this->setUserId();
      return (int)$this->mUserId;
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

   public function GetComboJenisKegiatan() {
      $result = $this->Open($this->mSqlQueries['get_combo_jenis_kegiatan'], array());
      return $result;
   }

   /**
    * [GetDataProgram description]
    * @param [type]
    */
   function GetDataProgram($idTa = NULL){
      $result     = $this->Open($this->mSqlQueries['get_data_program'],array(
         $idTa,
         (int)($idTa == NULL OR $idTa == '')
      ));
      $return     = array();
      $index      = 0;
      $count      = count($result);
      if(!empty($result)){
         $taId    = '';
         for ($i=0; $i < $count;) {
            if((int)$result[$i]['taId'] === (int)$taId){
               $return[$taId][$index]['id']     = $result[$i]['id'];
               $return[$taId][$index]['name']   = $result[$i]['name'];
               $i++;
               $index+=1;
            }else{
               $index      = 0;
               $taId       = (int)$result[$i]['taId'];
            }
         }

      }
      return $return;
   }

   /**
    * [Count description]
    */
   public function Count()
   {
      $return     = $this->Open($this->mSqlQueries['count'], array());

      if($return){
         return $return[0]['count'];
      }else{
         return 0;
      }
   }

   public function getDataRealisasi($offset, $limit, $param = array())
   {
      $return  = $this->Open($this->mSqlQueries['get_data_realisasi_pencairan'], array(
         $param['ta_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['program_id'],
         (int)(($param['program_id'] === NULL OR $param['program_id'] == '') OR strtolower($param['program_id']) == 'all'),
         '%'.$param['nomor_pengajuan'].'%',
         '%'.$param['kode'].'%',
         '%'.$param['nama'].'%',
         $param['jenis_kegiatan'],
         (int)(($param['jenis_kegiatan'] === NULL OR $param['jenis_kegiatan'] == '') OR strtolower($param['jenis_kegiatan']) == 'all'),
         $param['bulan'],
         (int)(($param['bulan'] === NULL OR $param['bulan'] == '') OR strtolower($param['bulan']) == 'all'),        
         $param['bulan_anggaran'],
         (int)($param['bulan_anggaran'] == '' OR strtolower($param['bulan_anggaran']) == 'all'),         
         $offset,
         $limit
      ));

      return self::ChangeKeyName($return);
   }

   public function getDataDetail($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_detail'], array(
         $id,
         (int)($id === NULL OR $id == ''),
         $id,
         (int)($id === NULL OR $id == ''),
         $id
      ));

      if($return){
         return self::ChangeKeyName($return[0]);
      }else{
         return null;
      }
   }

   public function getKomponenPencairan($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_komponen_pencairan'], array(
         $id,
         (int)($id == NULL OR $id == ''),
         $id
      ));

      return self::ChangeKeyName($return);
   }

   public function doUnproveRealisasi($id)
   {
      $result     = true;
      $userId     = $this->getUserId();
      $this->StartTrans();
      if(!$id){
         $result  &= false;
      }
      $result     &= $this->Execute($this->mSqlQueries['do_update_realisasi_detail'], array(
         $userId,
         $id
      ));
      $result     &= $this->Execute($this->mSqlQueries['do_update'], array(
         $userId,
         $id
      ));

      return $this->EndTrans($result);
   }

   function GetDataApproval($kodenama,$offset, $limit, $tahun_anggaran, $unitkerja='', $program='', $jenis='') {
      if($unitkerja != "") {
         //$str_unitkerja = " AND (unitkerjaId=$unitkerja OR unitkerjaParentId=$unitkerja)";
             $str_unitkerja =" AND (unitkerjaKodeSistem  LIKE
                                CONCAT((
                                SELECT unitkerjaKodeSistem FROM unit_kerja_ref WHERE unitkerjaId = '".
                                $unitkerja."'),'.','%')
                                OR
                            unitkerjaKodeSistem LIKE
                                CONCAT((
                                SELECT unitkerjaKodeSistem FROM unit_kerja_ref WHERE unitkerjaId = '".
                                $unitkerja."'))) ";

      } else {
         $str_unitkerja = "";
      }
      if($program != "") {
         $str_program = " AND programId=$program ";
      } else {
         $str_program = "";
      }
      if($jenis != "" && $jenis != "all") {
         $str_jenis = " AND jeniskegId=$jenis ";
      } else {
         $str_jenis = "";
      }
      $sql = sprintf($this->mSqlQueries['get_data_approval'], $kodenama,$kodenama,$kodenama,'%'.$kodenama.'%','%'.$kodenama.'%','%'.$kodenama.'%',$tahun_anggaran, $str_unitkerja, $str_program, $str_jenis, $offset, $limit);
      $data = $this->Open($sql, array());
      //echo $sql;
      return $data;
   }

   function GetCountDataApproval($kodenama,$tahun_anggaran, $unitkerja='', $program='', $jenis='') {
      if($unitkerja != "") {
         //$str_unitkerja = " AND (unitkerjaId=$unitkerja OR unitkerjaParentId=$unitkerja)";
              $str_unitkerja =" AND (unitkerjaKodeSistem  LIKE
                                CONCAT((
                                SELECT unitkerjaKodeSistem FROM unit_kerja_ref WHERE unitkerjaId = '".
                                $unitkerja."'),'.','%')
                                OR
                            unitkerjaKodeSistem LIKE
                                CONCAT((
                                SELECT unitkerjaKodeSistem FROM unit_kerja_ref WHERE unitkerjaId = '".
                                $unitkerja."'))) ";

      } else {
         $str_unitkerja = "";
      }
      if($program != "") {
         $str_program = " AND programId=$program ";
      } else {
         $str_program = "";
      }
      if($jenis != "" && $jenis != "all") {
         $str_jenis = " AND jeniskegId=$jenis ";
      } else {
         $str_jenis = "";
      }
      $sql = sprintf($this->mSqlQueries['get_count_data_approval'], $kodenama,$kodenama,$kodenama,'%'.$kodenama.'%','%'.$kodenama.'%','%'.$kodenama.'%',$tahun_anggaran, $str_unitkerja, $str_program, $str_jenis);
      $result = $this->Open($sql, array());
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }

   function GetDataById($id) {
      $result = $this->Open($this->mSqlQueries['get_data_by_id'], array($id));
      return $result[0];
   }

   function GetNominal($id) {
      $result = $this->Open($this->mSqlQueries['get_data_nominal'], array($id));
      return $result[0];
   }

   //get combo tahun anggaran
   function GetComboTahunAnggaran() {
      $result = $this->Open($this->mSqlQueries['get_combo_tahun_anggaran'], array());
      return $result;
   }
   function GetTahunAnggaranAktif() {
      $result = $this->Open($this->mSqlQueries['get_tahun_anggaran_aktif'], array());
      return $result[0];
   }

//===DO==
   function DoUpdate($userId, $id) {
      $result = $this->Execute($this->mSqlQueries['do_update'], array($userId, $id));
      return $result;
   }

/*
   function GetDataApprovalById($approvalId) {
      $result = $this->Open($this->mSqlQueries['get_data_approval_by_id'], array($approvalId));
      return $result;
   }

   function GetDataApprovalByArrayId($arrApprovalId) {
      $approvalId = implode("', '", $arrApprovalId);
      $result = $this->Open($this->mSqlQueries['get_data_approval_by_array_id'], array($approvalId));
      return $result;
   }

   */

   /**
    * untuk mendapatkan total sub unit
    * @since 11 Januari 2012
    */
   public function GetTotalSubUnitKerja($parentId)
   {
      $result = $this->Open($this->mSqlQueries['get_total_sub_unit_kerja'],
                  array($parentId));
      return $result[0]['total'];
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