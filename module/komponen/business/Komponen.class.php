<?php

class Komponen extends Database
{
   protected $mSqlFile;
   public $_POST;
   public $_GET;
   public $method;

   function __construct($connectionNumber=0) {
      $this->mSqlFile   = 'module/komponen/business/komponen.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->method     = strtoupper($_SERVER['REQUEST_METHOD']);
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
      $return     = $this->Open($this->mSqlQueries['get_data'], array(
         '%'.$param['nama'].'%',
         '%'.$param['nama'].'%',
         $offset,
         $limit
      ));

      return $return;
   }

   function GetDataKodeMAK($offset, $limit, $nama) {
      $result = $this->Open($this->mSqlQueries['get_data_mak'],
                array('%'.$nama.'%','%'.$nama.'%', $offset, $limit));
      //echo $this->getLastError();
      return $result;
   }

   function GetCountKodeMAK ($nama) {
     $result = $this->Open($this->mSqlQueries['get_count_mak'], array('%'.$nama.'%','%'.$nama.'%'));
     if (!$result)
       return 0;
     else
       return $result[0]['total'];
   }

   function GetSatuanKomponen(){
      return $this->Open($this->mSqlQueries['get_jenis_komponen'], array());
   }

   function GetLimitKomponen($params) {
     return $this->Open($this->mSqlQueries['get_limit_komponen'],$params);
   }

   function GetExcelKomponen() {
     return $this->Open($this->mSqlQueries['get_excel_komponen'],array());
   }

   function JumlahListKomponen($params) {
     $rs = $this->Open($this->mSqlQueries['jumlah_list_komponen'],$params);
     return $rs[0]['jumlah'];
   }

   function GetKomponenFromId($params) {
     return $this->Open($this->mSqlQueries['get_komponen_from_id'], array($params));
   }

   function InsertKomponen($nama,$satuan, $deskripsi,$formula, $idCoa, $hargaSatuan,
                $kompIsSBU, $kompMakId, $kompSumberDanaId, $kompIsLangsung,
                $kompIsTetap, $formulahasil,$kompKodeAset,$kompIsPengadaan)
   {
      $this->StartTrans();
     $result = $this->Execute($this->mSqlQueries['insert_komponen'],
                                    array(
                                            $nama,
                                            $satuan,
                                            $deskripsi,
                                            $formula,
                                            $idCoa,
                                            $hargaSatuan,
                                            $kompIsSBU,
                                            $kompMakId,
                                            $kompSumberDanaId,
                                            $kompIsLangsung,
                                            $kompIsTetap,
                                            $formulahasil,
                                            $kompKodeAset,
                                            $kompIsPengadaan
                                            ));

      $this->EndTrans($result);
     return $result;
   }

   function UpdateKomponen($nama,$satuan, $deskripsi,$formula, $idCoa,
                    $hargaSatuan, $kompIsSBU, $kompMakId, $kompSumberDanaId,
                    $kompIsLangsung, $kompIsTetap, $formulahasil,$kompKodeAset,$kompIsPengadaan,$kompId)
   {
     $this->StartTrans();
     $result =  $this->Execute($this->mSqlQueries['update_komponen'],
                                    array(
                                            $nama,
                                            $satuan,
                                            $deskripsi,
                                            $formula,
                                            $idCoa,
                                            $hargaSatuan,
                                            $kompIsSBU,
                                            $kompMakId,
                                            $kompSumberDanaId,
                                            $kompIsLangsung,
                                            $kompIsTetap,
                                            $formulahasil,
                                            $kompKodeAset,
                                            $kompIsPengadaan,
                                            $kompId));
        $this->EndTrans($result);
       return $result;
   }

   function DeleteKomponen($params) {
     return $this->Execute($this->mSqlQueries['delete_komponen'], array($params));
   }

   //untuk popup coa----
   function GetDataKodePenerimaan ($offset, $limit, $kode, $nama) {
     $result = $this->Open($this->mSqlQueries['get_data_kode_penerimaan'],
            array('%'.$kode.'%','%'.$nama.'%',$offset,$limit));
    return $result;
   }

   function GetCountKodePenerimaan ($kode, $nama) {
     $result = $this->Open($this->mSqlQueries['get_count_kode_penerimaan'], array('%'.$kode.'%','%'.$nama.'%'));
     if (!$result)
       return 0;
     else
       return $result[0]['total'];
   }
   //end untuk popup coa----

   //popup sumber dana
      function GetDataKodeSumberDana($offset, $limit, $nama) {
      $result = $this->Open($this->mSqlQueries['get_data_sumber_dana'], array('%'.$nama.'%', $offset, $limit));
      //echo $this->getLastError();
      return $result;
   }

   function GetCountKodeSumberDana ($nama) {
     $result = $this->Open($this->mSqlQueries['get_count_sumber_dana'], array('%'.$nama.'%'));
     if (!$result)
       return 0;
     else
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
}
?>