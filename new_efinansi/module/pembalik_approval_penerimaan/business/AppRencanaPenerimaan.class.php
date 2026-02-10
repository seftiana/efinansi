<?php
class AppRencanaPenerimaan extends Database
{
   protected $mSqlFile;
   public $_POST;
   public $_GET;

   function __construct($connectionNumber=0)
   {
      $this->mSqlFile   = 'module/pembalik_approval_penerimaan/business/apprencanapenerimaan.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
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
      $return        = $this->Open($this->mSqlQueries['get_data'], array(
         $param['ta_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id'],
         '%'.$param['kode'].'%',
         '%'.$param['kode'].'%',
         $param['status'],
         (int)($param['status'] == '' OR strtolower($param['status']) == 'all'),
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

      return $sql_parsed;
   }

   function GetCountData($kodenama,$tahunAnggaran, $userId, $approval) {

      if($approval != 'all' && $approval != '')
         $approval_str=" AND renterimaRpstatusId=$approval ";
      else
         $approval_str = "";

      $sql = $this->GetQueryKeren(
                     $this->mSqlQueries['get_count_data'],
                      array(
                           $tahunAnggaran,
                            $tahunAnggaran,
                            $kodenama,
                            "%".$kodenama."%",
                            $userId,
                            $approval_str));

      $data = $this->Open($sql, array());

      if (!$data) {
         return 0;
      } else {
         return $data[0]['total'];
      }
   }

   //yg dipake ini----
   function GetDataUnitkerja($kodenama,$tahunAnggaran, $userId, $approval) {
      if($approval != 'all' && $approval != '')
         $approval_str=" AND renterimaRpstatusId=$approval ";
      else
         $approval_str = "";

      $sql = $this->GetQueryKeren(
                     $this->mSqlQueries['get_data_unitkerja'],
                      array(
                           $tahunAnggaran,
                            $tahunAnggaran,
                            $kodenama,
                            "%".$kodenama."%",
                            $userId,
                            $approval_str));

      $result = $this->Open($sql, array());
      //file_put_contents('C:/test.txt', print_r($this->getLastError(),1));
      return $result;
   }
   //-------

   function GetDataRencanaPenerimaanById($id) {
      $result = $this->Open($this->mSqlQueries['get_data_rencana_penerimaan_by_id'], array($id));
      return $result;
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
   function GetTahunAnggaran($id) {
      $result = $this->Open($this->mSqlQueries['get_tahun_anggaran'], array($id));
      return $result[0];
   }
//===DO==

   function DoUpdateRencanaPenerimaan($approval, $id) {
      $result = $this->Execute($this->mSqlQueries['do_update_rencana_penerimaan'], array( $approval,$id));
      //file_put_contents('C:/test.txt', print_r($this->getLastError(),1));
      return $result;
   }

   /*function DoDeleteRencanaPenerimaanById($id) {
      $result=$this->Execute($this->mSqlQueries['do_delete_rencana_penerimaan_by_id'], array($id));
      return $result;
   }

   function DoDeleteRencanaPenerimaanByArrayId($arrId) {
      $id = implode("', '", $arrId);
      $result = $this->Execute($this->mSqlQueries['do_delete_rencana_penerimaan_by_array_id'], array($id));
      return $result;
   }*/

   function GetStatusApproval() {
      $result = $this->Open($this->mSqlQueries['status_approval'], array());
      return $result;
   }

   /**
    * untuk mendapatkan total sub unit
    * @since 3 Januari 2012
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
}
?>