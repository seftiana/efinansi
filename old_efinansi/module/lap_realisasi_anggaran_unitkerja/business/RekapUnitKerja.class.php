<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class RekapUnitKerja extends Database {

   protected $mSqlFile;
   public $_POST;
   public $_GET;
   public $indonesianMonth    = array(
      0 => array(
         'id' => 0,
         'name' => 'N/A'
      ), array(
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
      $this->mSqlFile   = 'module/lap_realisasi_anggaran_unitkerja/business/rekap_unit_kerja.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      parent::__construct($connectionNumber);
   }

   public function GetPeriodeTahun($param = array())
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

   function GetComboJenisKegiatan() {
      $result = $this->Open($this->mSqlQueries['get_combo_jenis_kegiatan'], array());
      return $result;
   }

   function GetUnitIdentity($id){
      return $this->Open($this->mSqlQueries['get_unit_kerja_id'],array($id));
   }

   function GetData($offset,$limit,$param = array())
   {
       #$this->SetDebugOn();
      $return     = $this->Open($this->mSqlQueries['get_data'], array(
         $param['ta_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['program_id'],
         (int)($param['program_id'] == '' OR $param['program_id'] === NULL),
         $param['jenis_kegiatan'],
         (int)($param['jenis_kegiatan'] == '' OR strtolower($param['jenis_kegiatan']) == 'all'),
         $param['bulan'],
         (int)($param['bulan'] == '' OR strtolower($param['bulan']) == 'all' OR $param['bulan'] == '0'),         
         $offset,
         $limit
      ));

      return $return;
   }

   function GetCount()
   {
      $return     = $this->Open($this->mSqlQueries['get_count'], array());

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
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['program_id'],
         (int)($param['program_id'] == '' OR $param['program_id'] === NULL),
         $param['jenis_kegiatan'],
         (int)($param['jenis_kegiatan'] == '' OR strtolower($param['jenis_kegiatan']) == 'all'),
         $param['bulan'],
         (int)($param['bulan'] == '' OR strtolower($param['bulan']) == 'all' OR $param['bulan'] == '0'),
      ));

      return $return;
   }

   public function GetDataDetail($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_detail'], array(
         $id
      ));

      return $return[0];
   }

   public function GetDataPengajuanRealisasi($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_pengajuan_realisasi'], array(
         $id
      ));

      return $return;
   }

   //== untuk combo box
   function GetDataTahunAnggaran(&$idaktif,&$namaaktif) {
      if(trim($idaktif)=='') {
       $id = $this->Open($this->mSqlQueries['get_ta_aktif'],array());
      if($id) {
         $idaktif = $id[0]['id'];
         $namaaktif = $id[0]['nama'];
      }
      }
      $result = $this->Open($this->mSqlQueries['get_data_ta'],array());
     return $result;
   }


   function GetResumeUnitKerja($data){

      $objUserUnitKerja = new UserUnitKerja;
     $userid=Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
     $unitkerja= $objUserUnitKerja->GetUnitKerjaUser($userid);
     $userrole = $objUserUnitKerja->GetRoleUser($userid);

      if(trim($data['unit_id'])=='' || trim($data['unit_id'])=='all'){
         if(($userrole['role_id']==1) || ($userrole['role_id'] ==4)) { //role_id  =1 (administrator) , role_id =2 (pusat) kalo administor ato pusat ditampilin semua unitkerjanya
            $data['unit_id'] = '%%';
           $data['parent_id']='%%';
         } elseif($unitkerja['is_unit_kerja']) {
            $data['unit_id'] = $unitkerja['unit_kerja_id'];
           $data['parent_id'] = $data['unit_id'];
        } else {
            $data['unit_id'] = $unitkerja['unit_kerja_id'];
           $data['parent_id'] = '';
        }
      } else $data['parent_id']='';

      if(trim($data['program_id']) == '')
        $data['program_id']='%%';

      if(trim($data['jenis_kegiatan']) == 'all' || trim($data['jenis_kegiatan'])=='')
        $data['jenis_kegiatan']='%%';

      if(empty($data['bulan'])){
        $data['bulan'] = 'all';
      }

       $ret = $this->Open(
                            $this->mSqlQueries['get_resume_unit_kerja'],
                            array(
                                    $data['bulan'],
                                    $data['bulan'],
                                    $data['ta_id'],
                                    $data['unit_id'],'%',
                                    $data['unit_id'],
                                    $data['program_id'],
                                    $data['jenis_kegiatan']));
      //$this->mdebug();
      return $ret;
   }

   function GetResumeProgram($data)
   {
      $objUserUnitKerja = new UserUnitKerja;
     $userid=Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
     $unitkerja= $objUserUnitKerja->GetUnitKerjaUser($userid);
     $userrole = $objUserUnitKerja->GetRoleUser($userid);

      if(trim($data['unit_id'])=='' || trim($data['unit_id'])=='all'){
         if(($userrole['role_id']==1) || ($userrole['role_id'] ==4)) { //role_id  =1 (administrator) , role_id =2 (pusat) kalo administor ato pusat ditampilin semua unitkerjanya
            $data['unit_id'] = '%%';
           $data['parent_id']='%%';
         } elseif($unitkerja['is_unit_kerja']) {
            $data['unit_id'] = $unitkerja['unit_kerja_id'];
           $data['parent_id'] = $data['unit_id'];
        } else {
            $data['unit_id'] = $unitkerja['unit_kerja_id'];
           $data['parent_id'] = '';
        }
      } else $data['parent_id']='';

      if(trim($data['program_id']) == '')
        $data['program_id']='%%';

      if(trim($data['jenis_kegiatan']) == 'all' || trim($data['jenis_kegiatan'])=='')
        $data['jenis_kegiatan']='%%';

      if(empty($data['bulan'])){
        $data['bulan'] = 'all';
      }

       $ret = $this->Open(
                            $this->mSqlQueries['get_resume_program'],
                            array(
                                    $data['ta_id'],
                                    $data['bulan'],
                                    $data['bulan'],
                                    $data['unit_id'],'%',
                                    $data['unit_id'],
                                    $data['program_id'],
                                    $data['jenis_kegiatan']));
      return $ret;
   }

   function GetResumeKegiatan($data)
   {
      $objUserUnitKerja = new UserUnitKerja;
     $userid=Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
     $unitkerja= $objUserUnitKerja->GetUnitKerjaUser($userid);
     $userrole = $objUserUnitKerja->GetRoleUser($userid);

      if(trim($data['unit_id'])=='' || trim($data['unit_id'])=='all'){
         if(($userrole['role_id']==1) || ($userrole['role_id'] ==4)) {
            $data['unit_id'] = '%%';
           $data['parent_id']='%%';
         } elseif($unitkerja['is_unit_kerja']) {
            $data['unit_id'] = $unitkerja['unit_kerja_id'];
           $data['parent_id'] = $data['unit_id'];
        } else {
            $data['unit_id'] = $unitkerja['unit_kerja_id'];
           $data['parent_id'] = '';
        }
      } else $data['parent_id']='';

      if(trim($data['program_id']) == '')
        $data['program_id']='%%';

      if(trim($data['jenis_kegiatan']) == 'all' || trim($data['jenis_kegiatan'])=='')
        $data['jenis_kegiatan']='%%';

      if(empty($data['bulan'])){
        $data['bulan'] = 'all';
      }

       $ret = $this->Open(
                            $this->mSqlQueries['get_resume_kegiatan'],
                            array(
                                    $data['ta_id'],
                                    $data['bulan'],
                                    $data['bulan'],
                                    $data['unit_id'],'%',
                                    $data['unit_id'],
                                    $data['program_id'],
                                    $data['jenis_kegiatan']));
      return $ret;
   }

  function GetCountUnitKerja ($nama) {
      $objUserUnitKerja = new UserUnitKerja;

      $userid=Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
     $unitkerja= $objUserUnitKerja->GetUnitKerjaUser($userid);
     $userrole = $objUserUnitKerja->GetRoleUser($userid);
     $parent_id = $unitkerja['unit_kerja_id'];

     if(isset($userrole['role_id']))
       if(($userrole['role_id']==1) || ($userrole['role_id'] ==4))
         $parent_id = '%%';

     $result = $this->Open($this->mSqlQueries['get_count_unit_kerja'], array($parent_id,'%'.$nama.'%'));

     if (!$result)
       return 0;
     else
       return $result[0]['total'];
   }

   function GetUnitKerja($startRec,$itemViewed,$nama) {
      $objUserUnitKerja = new UserUnitKerja;

      $userid=Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
     $unitkerja= $objUserUnitKerja->GetUnitKerjaUser($userid);
     $userrole = $objUserUnitKerja->GetRoleUser($userid);
     $parent_id = $unitkerja['unit_kerja_id'];


     if(isset($userrole['role_id']))
       if(($userrole['role_id']==1) || ($userrole['role_id'] ==4)) //role_id  =1 (administrator) , role_id =2 (pusat) kalo administor ato pusat ditampilin semua unitkerjanya
         $parent_id = '%%';

      $ret = $this->Open($this->mSqlQueries['get_unit_kerja'],array($parent_id,'%'.$nama.'%',$startRec,$itemViewed));


     return $ret;
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
    * @required indonesianMonths
    * @param String $date date format YYYY-mm-dd H:i:s, YYYY-mm-dd
    * @param String $format long, short
    * @return String  Indonesian Date
    */
   public function indonesianDate($date, $format = 'long')
   {
      $timeFormat          = '%02d:%02d:%02d';
      $patern              = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/';
      $patern1             = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/';
      switch ($format) {
         case 'long':
            $dateFormat    = '%02d %s %04d';
            break;
         case 'short':
            $dateFormat    = '%02d-%s-%04d';
            break;
         default:
            $dateFormat    = '%02d %s %04d';
            break;
      }

      if(preg_match($patern, $date, $matches)){
         $year    = (int)$matches[1];
         $month   = (int)$matches[2];
         $day     = (int)$matches[3];
         $hour    = (int)$matches[4];
         $minute  = (int)$matches[5];
         $second  = (int)$matches[6];
         $mon     = $this->indonesianMonth[$month];

         $date    = sprintf($dateFormat, $day, $mon, $year);
         $time    = sprintf($timeFormat, $hour, $minute, $second);
         $result  = $date.' '.$time;

      }elseif(preg_match($patern1, $date, $matches)){
         $year    = (int)$matches[1];
         $month   = (int)$matches[2];
         $day     = (int)$matches[3];
         $mon     = $this->indonesianMonth[$month]['name'];

         $date    = sprintf($dateFormat, $day, $mon, $year);

         $result  = $date;
      }else{
         $date    = getdate();
         $year    = (int)$date['year'];
         $month   = (int)$date['mon'];
         $day     = (int)$date['mday'];
         $hour    = (int)$date['hours'];
         $minute  = (int)$date['minutes'];
         $second  = (int)$date['seconds'];
         $mon     = $this->indonesianMonth[$month]['name'];

         $date    = sprintf($dateFormat, $day, $mon, $year);
         $time    = sprintf($timeFormat, $hour, $minute, $second);
         $result  = $date.' '.$time;
      }

      return $result;
   }
}
?>