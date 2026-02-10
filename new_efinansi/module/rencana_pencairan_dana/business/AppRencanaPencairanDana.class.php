<?php
class AppRencanaPencairanDana extends Database
{
   protected $mSqlFile;
   public $_POST;
   public $_GET;
   function __construct($connectionNumber=0){
      $this->mSqlFile      = 'module/rencana_pencairan_dana/business/apprencanapencairandana.sql.php';
      $this->_POST         = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET          = is_object($_GET) ? $_GET->AsArray() : $_GET;
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

   public function GetData($offset, $limit, $param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_data'], array(
         $param['ta_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id'],
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

   function GetDataRencanaPencairan($offset, $limit, $tahun_anggaran, $unitkerja)
    {

      $sql = sprintf($this->mSqlQueries['get_rencana_pencairan_dana'],
                                $tahun_anggaran,
                                $unitkerja,'%',
                                $unitkerja,
                                $offset,
                                $limit);

      $data = $this->Open($sql, array());
      return $data;
   }

   function GetDataCetak($tahun_anggaran, $unitkerjaId, $unitkerjaId) {
      $result = $this->Open($this->mSqlQueries['get_rencana_pencairan_dana'], array());
      //echo $this->GetLastError();exit;
      return $result;
   }

   function GetCountDataRencanaPencairan($tahun_anggaran, $unitkerja='')
    {

      $sql = sprintf($this->mSqlQueries['get_count_rencana_pencairan_dana'],
                            $tahun_anggaran,
                            $unitkerja,'%',
                            $unitkerja);

      $result = $this->Open($sql, array());
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
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

   function GetTahunAnggaranCetak($thId) {
      $result = $this->Open($this->mSqlQueries['get_tahun_anggaran_cetak'], array($thId));
      return $result[0];
   }

   function GetUnitKerja($unirkerjaId) {
      $result = $this->Open($this->mSqlQueries['get_unit_kerja'], array($unirkerjaId));
      return $result[0];
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