<?php
#doc
#    classname:    AppPopupUnitKerja
#    scope:        PUBLIC
# extends extends Database
# construct: $connectionNumber = 0
#/doc
require_once GTFWConfiguration::GetValue('application','docroot').
'module/user_unit_kerja/business/UserUnitKerja.class.php';
class AppPopupUnitKerja extends Database
{
   #    internal variables
   protected $mSqlFile;
   protected $userId;
   public $userUnitObj;

   #    Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->userId        = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $this->userUnitObj   = new UserUnitKerja();
      $this->mSqlFile      = 'module/pagu_anggaran_unit_per_mak/business/app_popup_unit_kerja.sql.php';
      parent::__construct($connectionNumber);
   }

   function GetQueryKeren($sql,$params) {
      foreach ($params as $k => $v) {
         if (is_array($v)) {
            $params[$k] = '~~' . join("~~,~~", $v) . '~~';
            $params[$k] = str_replace('~~', '\'', addslashes($params[$k]));
         } else {
            $params[$k] = addslashes($params[$k]);
         }
      }
      $param_serialized = '~~' . join("~~,~~", $params) . '~~';
      $param_serialized = str_replace('~~', '\'', addslashes($param_serialized));
      eval('$sql_parsed = sprintf("' . $sql . '", ' . $param_serialized . ');');
      //echo $sql_parsed;
      return $sql_parsed;
   }

   function GetDataUnitkerja ($offset, $limit, $param = array()) {
      $unitKerja     = $this->userUnitObj->GetUnitKerjaUser($this->userId);
      $kode          = trim($param['kode']);
      $nama          = trim($param['nama']);
      $tipe          = $param['tipe'];
      $unitId        = $unitKerja['unit_kerja_id'];

      $return        = $this->Open($this->mSqlQueries['get_data'], array(
         '%'.$kode.'%',
         '%'.$nama.'%',
         $tipe,
         (int)($tipe == '' OR $tipe == null OR strtolower($tipe) == 'all'),
         $this->userId,
         $unitId,
         $unitId,
         $unitId,
         $unitId,
         $unitId,
         $unitId,
         $unitId,
         $offset,
         $limit
      ));

      return $return;
      /*if($tipeunit != "")
         $str_tipeunit = " AND unitkerjaTipeUnitId = " . $tipeunit;
      else
         $str_tipeunit = "";

      if($role['role_name'] == "OperatorUnit")
         $str_unit = " AND (unitkerjaParentId=" .
                  $unitkerjaUser['unit_kerja_id'] . " OR unitkerjaId=" .
                  $unitkerjaUser['unit_kerja_id'] . ")";
      else
         $str_unit="";

      $sql = $this->GetQueryKeren($this->mSqlQueries['get_data_unitkerja'],
            array('%'.$kode.'%', '%'.$kode.'%', '%'.$unitkerja.'%',
             '%'.$unitkerja.'%', $str_tipeunit, $str_unit, $offset, $limit));
      //echo "<pre>" . $sql . "</pre>";

      return $this->Open($sql, array());*/
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
   function GetCountDataUnitkerja ($kode='', $unitkerja='', $tipeunit='', $role=array(), $unitkerjaUser=array()) {
      if($tipeunit != "")
         $str_tipeunit = " AND unitkerjaTipeUnitId = " . $tipeunit;
      else
         $str_tipeunit = "";

      if($role['role_name'] == "OperatorUnit")
         $str_unit = " AND (unitkerjaParentId=" . $unitkerjaUser['unit_kerja_id'] .
                  " OR unitkerjaId=" . $unitkerjaUser['unit_kerja_id'] . ")";
      else
         $str_unit="";
      $sql    = $this->GetQueryKeren($this->mSqlQueries['get_count_data_unitkerja'],
            array('%'.$kode.'%',
                 '%'.$kode.'%',
                 '%'.$unitkerja.'%',
                 '%'.$unitkerja.'%',
                 $str_tipeunit,
                 $str_unit));
      $result = $this->Open($sql, array());

      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }

   function GetDataTipeUnit($unitkerjaId = NULL) {
      $result = $this->Open($this->mSqlQueries['get_data_tipe_unit'], array());
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
}
?>