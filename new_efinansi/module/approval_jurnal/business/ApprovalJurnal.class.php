<?php
class ApprovalJurnal extends Database
{
   protected $mSqlFile;
   public $_POST;
   public $_GET;
   public $method;
   protected $mUserId = null;
   # subaccount
   public $subAccName;
   public $subAccJml;
   public $defaultSubacc;

   function __construct($connectionNumber=0) {
      $this->mSqlFile   = 'module/approval_jurnal/business/approvaljurnal.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->method     = strtolower($_SERVER['REQUEST_METHOD']);
      parent::__construct($connectionNumber);
      $this->subAccName       = array('Pertama','Kedua','Ketiga','Keempat','Kelima','Keenam','Ketujuh');
      $this->subAccJml        = GTFWConfiguration::GetValue('application','subAccJml');
      $this->defaultSubacc    = str_replace('9','0',GTFWConfiguration::GetValue('application','subAccFormat'));
   }

   private function setUserId()
   {
      if(class_exists('Security')){
         $this->mUserId       = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      }
   }

   public function getUserId()
   {
      $this->setUserId();

      return (int)$this->mUserId;
   }

   /**
    * [getPeriodeTahunPembukuan description]
    * @param  array
    * @return [type]
    */
   public function getTahunPembukuanPeriode($param = array())
   {
      $default    = array(
         'open' => false
      );
      $options    = array_merge($default, (array)$param);
      $return     = $this->Open($this->mSqlQueries['get_tahun_pembukuan_periode'], array(
         (int)($options['open'] === false)
      ));

      return $return;
   }

   public function getRangeYears()
   {
      $getdate    = getdate();
      $crrYear    = (int)$getdate['year'];
      $return     = $this->Open($this->mSqlQueries['get_min_max_tahun_pencatatan'], array());

      if($return && !empty($return)){
         $result['min_year']     = $return[0]['minTahun'];
         $result['max_year']     = $return[0]['maxTahun'];
      }else{
         $result['min_year']     = $crrYear-5;
         $result['max_year']     = $crrYear+5;
      }

      return $result;
   }

   public function GetComboTipeTransaksi() {
      return $this->open($this->mSqlQueries['get_combo_tipe_transaksi'],array());
   }

   public function GetBentukTransaksi(){
      $return     = $this->open($this->mSqlQueries['get_bentuk_transaksi'],array());
      return $return;
   }

   public function getDataJurnal($offset, $limit, $param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_data_jurnal'], array(
         $param['tipe'],
         (int)($param['tipe'] == '' OR strtolower($param['tipe']) == 'all'),
         '%'.$param['kode'].'%',
         $param['status'],
         (int)($param['status'] == '' OR strtolower($param['status']) == 'all'),
         date('Y-m-d', strtotime($param['start_date'])),
         date('Y-m-d', strtotime($param['end_date'])),
         $offset,
         $limit
      ));

      return $return;
   }

   public function getCountDataJurnal($param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_count_data_jurnal'], array(
         $param['tipe'],
         (int)($param['tipe'] == '' OR strtolower($param['tipe']) == 'all'),
         '%'.$param['kode'].'%',
         $param['status'],
         (int)($param['status'] == '' OR strtolower($param['status']) == 'all'),
         date('Y-m-d', strtotime($param['start_date'])),
         date('Y-m-d', strtotime($param['end_date']))
      ));

      if($return){
         return $return[0]['count'];
      }else{
         return 0;
      }
   }

   public function doApproveJurnal($param = array())
   {
      $result     = true;
      $queryLog   = array();
      $ipAddress  = $this->GetRealIP();
      $userId     = $this->getUserId();
      $this->StartTrans();
      if(!is_array($param)){
         $result  &= false;
      }

      foreach ($param as $data) {
         $result  &= $this->Execute($this->mSqlQueries['do_add'], array(
            $data['status_kas'],
            $data['bentuk_transaksi'],
            $data['id']
         ));

         $queryLog[] = sprintf($this->mSqlQueries['do_add'], $data['status_kas'], $data['bentuk_transaksi'], $data['id']);
      }

      // add query log
      $result     &= $this->Execute($this->mSqlQueries['do_add_log'], array(
         $userId,
         $ipAddress,
         'Approval Jurnal'
      ));
      $loggerId   = $this->LastInsertId();
      foreach ($queryLog as $query) {
         $result  &= $this->Execute($this->mSqlQueries['do_add_log_detil'], array(
            $loggerId,
            addslashes($query)
         ));
      }
      return $this->EndTrans($result);
   }

   public function doUnApproveJurnal($param = array())
   {
      $result     = true;
      $queryLog   = array();
      $ipAddress  = $this->GetRealIP();
      $userId     = $this->getUserId();
      $this->StartTrans();
      if(!is_array($param)){
         $result  &= false;
      }

      foreach ($param as $data) {
         $result  &= $this->Execute($this->mSqlQueries['do_unapprove'], array(
            $data['id']
         ));

         $queryLog[] = sprintf($this->mSqlQueries['do_unapprove'], $data['id']);
      }

      // add query log
      $result     &= $this->Execute($this->mSqlQueries['do_add_log'], array(
         $userId,
         $ipAddress,
         'Approval Jurnal'
      ));
      $loggerId   = $this->LastInsertId();
      foreach ($queryLog as $query) {
         $result  &= $this->Execute($this->mSqlQueries['do_add_log_detil'], array(
            $loggerId,
            addslashes($query)
         ));
      }
      return $this->EndTrans($result);
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

   public function GetRealIP(){
      if ($_ENV["HTTP_CLIENT_IP"]) :
         $ip_address    = $_ENV["HTTP_CLIENT_IP"];
      elseif ($_ENV["HTTP_X_FORWARDED_FOR"]) :
         $ip_address    = $_ENV["HTTP_X_FORWARDED_FOR"];
      elseif ($_ENV["HTTP_X_FORWARDED"]) :
         $ip_address    = $_ENV["HTTP_X_FORWARDED"];
      elseif ($_ENV["HTTP_FORWARDED_FOR"]) :
         $ip_address    = $_ENV["HTTP_FORWARDED_FOR"];
      elseif ($_ENV["HTTP_FORWARDED"]) :
         $ip_address    = $_ENV["HTTP_FORWARDED"];
      elseif ($_SERVER['REMOTE_ADDR']) :
         $ip_address    = $_SERVER['REMOTE_ADDR'];
      endif;

      return $ip_address;
   }

   //==GET==
   function GetMinMaxTahunPencatatan(){
     $ret = $this->open($this->mSqlQueries['get_min_max_tahun_pencatatan'],array($start , $count));
    if($ret)
       return $ret[0];
    else {
      $now_thn = date('Y');
      $thn['minTahun'] = $now_thn - 5 ;
      $thn['maxTahun'] = $now_thn + 5 ;
      return $thn;
     }
   }

   function GetDataKodeAkun(){
      $result = $this->open($this->mSqlQueries['get_data_kode_akun'],array());
      return $result;
   }

   function GetData($start , $count, $tipe='', $is_approve='', $tgl_awal, $tgl_akhir){
      if($tipe == 'all') $tipe='';

      $pembukuan_referensi = $this->open($this->mSqlQueries['get_pembukuan_referensi'], array('%'.$tipe.'%', '%'.$is_approve.'%', $tgl_awal, $tgl_akhir, $start , $count));


      for($i=0;$i<sizeof($pembukuan_referensi);$i++) {
         $arr_id[] = $pembukuan_referensi[$i]['id'];
      }
      $str_id = @implode("','", $arr_id);

      $sql = $this->mSqlQueries['get_data'];
      # generate dynamic subaccount
      if($this->subAccJml > 0){
         $defaultSubAcc = explode('-',$this->defaultSubacc);
         for($i=0;$i<=($this->subAccJml-1);$i++){
            $arrView[$i] = 'IFNULL(pd.pdSubacc'.$this->subAccName[$i].'Kode,"'.$defaultSubAcc[$i].'")';
         }
         $addSqlView = ',CONCAT('.implode(",'-',",$arrView).') AS sub_account';
         $sql = str_replace('[SUBACC_VIEW]', $addSqlView, $sql);
      }else $sql = str_replace('[SUBACC_VIEW]', '', $sql);

      $result = $this->open($sql, array($str_id));

      return $result;
   }


   function GetCount($tipe='', $is_approve='', $tgl_awal, $tgl_akhir) {
      //echo sprintf($this->mSqlQueries['get_count'],'%'.$tipe.'%', '%'.$is_approve.'%', $this->str_pr);
     if($tipe == 'all') $tipe='';
     $tot = $this->open($this->mSqlQueries['get_count'], array('%'.$tipe.'%', '%'.$is_approve.'%', $tgl_awal, $tgl_akhir));
     if(!empty($tot)) {
       return sizeof($tot);
     } else {
       return false;
     }
   }

   function GetDataById($id){
      return $this->open($this->mSqlQueries['get_data_by_id'],array($id));
   }

   function GetComboCoa($type='all'){

      switch($type) {
        case 'kredit':
          $type ='0';
         break;

       case 'debet':
          $type ='1';
         break;

       case 'all':
          $type ='%%';
         break;
      }
      $ret=$this->open($this->mSqlQueries['get_combo_coa'],array($type));
      return $ret;
   }

//===DO==
   function DoAdd() {
      $post = $_POST->AsArray(); #print_r($post);
      for($i=0; $i<sizeof($post['id']); $i++) {
         $exec = $this->Execute($this->mSqlQueries['do_add'], array($post['is_kas'][$i], $post['bentuk_transaksi'][$i], $post['id'][$i]));
      if($exec) {
         $sql = sprintf($this->mSqlQueries['do_add'], $post['is_kas'][$i], $post['bentuk_transaksi'][$i], $post['id'][$i]);
         $this->DoAddLog("Approve Jurnal", $sql);
      }
      }
      return $exec;
   }

   function DoUnapprove() {
      $post = $_POST->AsArray();
      for($i=0; $i<sizeof($post['id']); $i++) {
         $exec = $this->Execute($this->mSqlQueries['do_unapprove'], array($post['id'][$i]));
         if($exec) {
            $sql = sprintf($this->mSqlQueries['do_unapprove'], $post['id'][$i]);
            $this->DoAddLog("Unapprove Jurnal", $sql);
         }
      }
      return $exec;
   }


   function date2string($date) {
      $bln = array(
                   1  => 'Januari',
               2  => 'Februari',
               3  => 'Maret',
               4  => 'April',
               5  => 'Mei',
               6  => 'Juni',
               7  => 'Juli',
               8  => 'Agustus',
               9  => 'September',
               10 => 'Oktober',
               11 => 'November',
               12 => 'Desember'
                  );
      $arrtgl = explode('-',$date);
      return $arrtgl[2].' '.$bln[(int) $arrtgl[1]].' '.$arrtgl[0];

   }

   //LOGGER LOGGER LOGGER

   function DoAddLog($keterangan, $query) {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $ip = $this->GetRealIP();
      $result = $this->Execute($this->mSqlQueries['do_add_log'], array($userId, $ip, $keterangan));

     $id_logger = $this->LastInsertId();

      if(is_array($query)) {
       foreach($query as $val) {
        $this->DoAddLogDetil($id_logger, $val);
      }

     } else
       $this->DoAddLogDetil($id_logger, $query);
      return $result;
   }

   function DoAddLogDetil($id, $query) {
      $result = $this->Execute($this->mSqlQueries['do_add_log_detil'], array($id, addslashes($query)));
      return $result;
   }
}
?>
