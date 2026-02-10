<?php 
#doc
# package:     AppReferensi
# scope:       PUBLIC
# @created     ucil.619
# @Author      Eko Susilo
# @Created     2013-03-18
# @Modified    2013-03-18
# @Analysts    Nanang Ruswianto
# @copyright   Copyright (c) 2012 Gamatechno
#/doc

class AppReferensi extends Database
{
   #   internal variables
   protected $mSqlFile;
   #   Constructor
   function __construct ($connectionNumber = 0)
   {
      # code...
      $this->mSqlFile      = 'module/'.Dispatcher::Instance()->mModule.'/business/app_referensi.sql.php';
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

   public function GetDataProgram($offset, $limit, $kode = null)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_program'], array(
         '%'.$kode.'%',
         '%'.$kode.'%', 
         $offset, 
         $limit
      ));

      return $return;
   }

   public function GetDataOutput($offset, $limit, $kode, $kegiatanKode, $program = null)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_output'], array(
         $program, 
         (int)($program == null OR $program == '' OR strtolower($program) == 'all'), 
         '%'.$kegiatanKode.'%', 
         '%'.$kegiatanKode.'%', 
         '%'.$kode.'%', 
         '%'.$kode.'%', 
         $offset, 
         $limit
      ));

      return $return;
   }

   public function GetDataKomponen($offset, $limit, $kode)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_komponen'], array(
         '%'.$kode.'%', 
         '%'.$kode.'%', 
         $offset, 
         $limit
      ));

      return $return;
   }

   public function GetDataMak($offset, $limit, $param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_data_mak'], array(
         '%'.$param['bas_kode'].'%', 
         '%'.$param['bas_kode'].'%', 
         '%'.$param['kode'].'%', 
         '%'.$param['kode'].'%', 
         $offset, 
         $limit
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