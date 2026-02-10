<?php
class AppPopupKegiatanRef extends Database
{

   protected $mSqlFile;
   public $_POST;
   public $_GET;
   public $indonesianMonth    = array(
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
   );

   function __construct($connectionNumber=0)
   {
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->mSqlFile   = 'module/realisasi_pencairan_2/business/apppopupsubkegiatan.sql.php';
      parent::__construct($connectionNumber);
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

   public function GetData($offset, $limit, $param = array())
   {
      //$this->SetDebugOn();
      $realisasi     = array('data_id' => null);
      $option        = array_merge($realisasi, (array)$param);
      $return     = $this->Open($this->mSqlQueries['get_data'], array(
         $option['data_id'],
         (int)($option['data_id'] === NULL OR $option['data_id'] == ''),
         $option['data_id'],
         (int)($option['data_id'] === NULL OR $option['data_id'] == ''),
         '%'.$option['kode'].'%',
         '%'.$option['kode'].'%',
         $option['unit_id'],
         $option['ta_id'],
         $offset,
         $limit
      ));

      return $return;
   }

   function GetKomponenAnggaran($offset, $limit, $param = array())
   {//$this->SetDebugOn();
      $realisasi     = array('data_id' => null);
      $option        = array_merge($realisasi, (array)$param);
      $dataResult    = $this->Open($this->mSqlQueries['get_komponen_anggaran'], array(
         '%'.$option['kode'].'%',
         '%'.$option['kode'].'%',
         $option['unit_id'],
         $option['ta_id'],
         $offset,
         $limit,
         $option['data_id'],
         (int)($option['data_id'] === NULL OR $option['data_id'] == '')
      ));

      if(empty($dataResult)){
         return null;
      }else{
         $data_komponen = self::ChangeKeyName($dataResult);
         $kegId         = '';
         $index         = 0;
         $data_grid     = array();

         for ($i=0; $i < count($data_komponen);) {
            if((int)$kegId === (int)$data_komponen[$i]['kegdet_id']){
               $data_grid[$kegId][$index]    = $data_komponen[$i];
               $i++;
               $index++;
            }else{
               $kegId      = (int)$data_komponen[$i]['kegdet_id'];
               unset($index);
               $index      = 0;
            }
         }

         return compact('data_komponen', 'data_grid');
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