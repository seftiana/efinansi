<?php

class UserUnitKerja extends Database {

   protected $mSqlFile= 'module/user_unit_kerja/business/userunitkerja.sql.php';

   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);
   }

   function InsertUserUnitKerja($params){
      return $this->Execute($this->mSqlQueries['insert_user_unit_kerja'], $params);
   }


   function GetRoleUser($userId) {
      $ret = $this->Open($this->mSqlQueries['get_role_user'], array($userId));
      return $ret[0];
   }

   /**
    * fungsi GetUnitKerjaUser
    * untuk mengetahui unit kerja dari user yang sedang login / aktif
    * @param $userId  userId yang sedang aktif / login
    * @access public
    * @return array()
    */
   function GetUnitKerjaUser($userId) {
      $ret = $this->Open($this->mSqlQueries['get_unit_kerja_user'], array($userId));
      return $ret[0];
   }

   function GetSatkerUnitKerjaUser($userId) {
      $ret = $this->Open($this->mSqlQueries['get_satker_unit_kerja_user'], array($userId));
     if($ret[0]['is_unit_kerja'] == '0')
        $ret[0]['is_unit_kerja'] = true;
     else
        $ret[0]['is_unit_kerja'] = false;

     return $ret[0];
   }
   function GetSatkerUnitKerjaUserDua($userId) {
      $ret = $this->Open($this->mSqlQueries['get_satker_unit_kerja_user_dua'], array($userId));
     if($ret[0]['is_unit_kerja'] == '0')
        $ret[0]['is_unit_kerja'] = true;
     else
        $ret[0]['is_unit_kerja'] = false;

     return $ret[0];
   }

   function GetSatkerUnitKerja($unitkerjaId) {
      $ret = $this->Open($this->mSqlQueries['get_satker_unit_kerja'], array($unitkerjaId));

     if($ret[0]['is_unit_kerja'] == '0')
        $ret[0]['is_unit_kerja'] = true;
     else
        $ret[0]['is_unit_kerja'] = false;

     return $ret[0];
   }

   function UpdateUserUnitKerjaByUserId($params){
      return $this->Execute($this->mSqlQueries['update_user_unit_kerja_by_user_id'], $params);
   }

   /**
    * added
    * @since 9 Januari 2012
    * untuk mendapatkan unitkerja
    */
   function GetUnitKerja($unitkerjaId) {
      $ret = $this->Open($this->mSqlQueries['get_unit_kerja'], array($unitkerjaId));
     return $ret[0];
   }

   /**
    * added
    * @since 29 Desember 2011
    * Untuk mendapatkan total unit kerja
    */

    public function GetTotalSubUnitKerja($parentId)
    {
      $result = $this->Open($this->mSqlQueries['get_total_sub_unit_kerja'], array($parentId));
      return $result[0]['total'];
    }
    /**
     * end
     */

   /**
    * @description Get Data unit kerja by user id, based: parent-child
    * @param Int $userId, user id
    * @return Array $return, data unit kerja
    */
   public function GetUnitKerjaRefUser($userId)
   {
      $return        = $this->Open($this->mSqlQueries['get_unit_kerja_ref_user'], array(
         $userId
      ));

      if($return){
         return $return[0];
      }else{
         return null;
      }
   }
}
?>