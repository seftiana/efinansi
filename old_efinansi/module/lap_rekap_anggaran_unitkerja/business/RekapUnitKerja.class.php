<?php
class RekapUnitKerja extends Database
{
   protected $mSqlFile;
   public $_POST;
   public $_GET;
   public function __construct($connectionNumber=0)
   {
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->mSqlFile   = 'module/lap_rekap_anggaran_unitkerja/business/rekap_unit_kerja.sql.php';
      parent::__construct($connectionNumber);
   }

   public function GetPeriodeTahun($param = array())
   {
      $default       = array(
         'active' => false,
         'open' => false
      );
      $options       = array_merge($default, (array)$param);
      $return        = $this->Open($this->mSqlQueries['get_periode_tahun'], array(
         (int)($options['active'] === false),
         (int)($options['open'] === false)
      ));

      return $return;
   }

   public function GetComboJenisKegiatan() {
      $result = $this->Open($this->mSqlQueries['get_combo_jenis_kegiatan'], array());
      return $result;
   }

   public function GetUnitIdentity($id)
   {
      return $this->Open($this->mSqlQueries['get_unit_kerja_id'],array($id));
   }

   function GetData($offset, $limit, $param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_data'], array(
         $param['ta_id'],
         $param['program_id'],
         (int)($param['program_id'] === NULL OR $param['program_id'] == ''),
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['jenis_kegiatan'],
         (int)($param['jenis_kegiatan'] == '' OR strtolower($param['jenis_kegiatan']) == 'all'),
         $offset,
         $limit
      ));

      return $return;
   }

   function GetCountData() {
      $return     = $this->Open($this->mSqlQueries['get_count_data'], array());

      if($return){
         return $return[0]['count'];
      }else{
         return 0;
      }
   }

   public function GetDataResume($param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_data_resume'], array(
         $param['ta_id'],
         $param['program_id'],
         (int)($param['program_id'] === NULL OR $param['program_id'] == ''),
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['jenis_kegiatan'],
         (int)($param['jenis_kegiatan'] == '' OR strtolower($param['jenis_kegiatan']) == 'all')
      ));

      return $return;
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