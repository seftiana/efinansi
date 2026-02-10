<?php
/**
* ================= doc ====================
* FILENAME     : Module.class.php
* @package     : Module
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-11-12
* @Modified    : 2014-11-12
* @Analysts    : Nobody
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/

class Module extends Database
{
   # internal variables
   protected $mSqlFile;
   private $mPath;
   private $mIgnore  = array();
   private $mAction  = array();
   private $mTypes   = array();
   public $_POST;
   public $_GET;
   # Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->mSqlFile   = 'module/modules/business/module.sql.php';
      $this->mPath      = realpath(GTFW_APP_DIR.'module/');
      $this->mIgnore    = array('.', '..', '.svn');
      $this->mAction    = array('Do','View','Popup','Process','Input','Combo','Destroy', 'Print', 'Rest');
      $this->mTypes     = array(
         'html',
         'rest',
         'cli',
         'dbf',
         'img',
         'json',
         'jsonrpc',
         'nusoap',
         'pdf',
         'pdfx',
         'rtf',
         'shtml',
         'smarty',
         'soap',
         'xls',
         'xlsx'
      );

      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      parent::__construct($connectionNumber);
   }

   public function GetQueryModule($module = null)
   {
      $return     = $this->Open($this->mSqlQueries['get_menu_query'], array(
         $module,
         (int)($module == '' OR $module === NULL)
      ));
      $query      = array();
      if($return){
         $index   = 0;
         foreach ($return as $ret) {
            $query[$index]    = $ret['module'];
            $index++;
         }
      }

      return $query;
   }

   public function GetGtfwApplication()
   {
      $return     = $this->Open($this->mSqlQueries['get_gtfw_application'], array());
      $gtfwApp    = array();
      $index      = 0;
      foreach ($return as $app) {
         $gtfwApp[$index]['id']     = $app['id'];
         $gtfwApp[$index]['name']   = strtoupper(preg_replace('/(?<=\w)([A-Z])/', '_\1', $app['name']));
         $index++;
      }
      return $gtfwApp;
   }

   public function GetDir($appId = NULL)
   {
      $applicationId = is_null($appId) ? GTFWConfiguration::GetValue('application', 'application_id') : (int)$appId;

      $module        = self::GetListDirectory();
      sort($module);

      $return        = array();
      $index         = 0;
      foreach ($module as $mod) {
         $return[$index]['name']       = $mod['name'];
         $return[$index]['conflict']   = 0;
         $return[$index]['registered'] = 0;
         $return[$index]['unregister'] = 0;
         $return[$index]['sub_module'] = 0;
         $registeredModule             = self::ChangekeyName(self::GetRegisteredModule($applicationId, $mod['name']));
         $subModule                    = self::GetSubModule($mod['name'], $applicationId);
         if(count($subModule) === 0){
            continue;
         }
         $rModule       = array();
         $mModule       = array();
         for ($i=0; $i < count($subModule); $i++) {
            $mModule[$i]   = lcfirst($subModule[$i]['name']);
            $return[$index]['sub_module']       += 1;
            if((bool)$subModule[$i]['status'] === true){
               $return[$index]['registered']    += 1;
            }

            if((bool)$subModule[$i]['status'] === false){
               $return[$index]['unregister']    += 1;
            }
         }
         sort($mModule);

         for ($i=0; $i < count($registeredModule); $i++) {
            $rModule[$i]   = $registeredModule[$i]['sub_module'];
            if(!in_array(lcfirst($registeredModule[$i]['sub_module']), $mModule)){
               // $return[$index]['conflict'][] = $registeredModule[$i]['sub_module'];
               $return[$index]['conflict']   += 1;
            }
         }

         // sort($rModule);
         // $subMerge         = array_merge($mModule, $rModule);
         // $return[$index]['register']      = $rModule;
         // $return[$index]['sub_module']    = $mModule;
         $index++;
      }
      return $return;
   }

   public function GetConflictedModule($appId = NULL, $module = NULL)
   {
      $applicationId = is_null($appId) ? GTFWConfiguration::GetValue('application', 'application_id') : (int)$appId;
      $module        = array();
      $dataModule          = self::GetListDirectory();
      sort($dataModule);
      $registeredModule    = self::ChangekeyName(self::GetRegisteredModule($applicationId, $mod['name']));
      $return        = array();
      $index         = 0;
      foreach ($dataModule as $mod) {
         $module[]   = $mod['name'];
      }

      foreach ($registeredModule as $reg) {
         if(in_array($reg['module'], $module)){
            continue;
         }
         $return[$index]   = $reg;
         $index++;
      }

      return $return;
   }

   public function GetModuleDetail($appId = NULL, $module_name = NULL)
   {
      $applicationId = is_null($appId) ? GTFWConfiguration::GetValue('application', 'application_id') : (int)$appId;
      if($module_name === NULL){
         return null;
      }

      $module        = self::GetListDirectory();
      sort($module);

      $return        = array();
      $data_detail   = array();
      $data_detail['sub_module']['register']    = array();
      $data_detail['sub_module']['unregister']  = array();
      $data_detail['sub_module']['conflict']    = array();
      $index         = 0;
      foreach ($module as $mod) {
         if(strtolower($mod['name']) != strtolower($module_name)){
            continue;
         }
         $return[$index]               = (array)$mod;
         $return[$index]['conflict']   = 0;
         $return[$index]['registered'] = 0;
         $return[$index]['unregister'] = 0;
         $return[$index]['sub_module'] = 0;
         $registeredModule             = self::ChangekeyName(self::GetRegisteredModule($applicationId, $mod['name']));
         $subModule                    = self::GetSubModule($mod['name'], $applicationId);
         if(count($subModule) === 0){
            continue;
         }
         $rModule       = array();
         $mModule       = array();
         for ($i=0; $i < count($subModule); $i++) {
            $mModule[$i]   = lcfirst($subModule[$i]['name']);
            $return[$index]['sub_module']       += 1;
            if((bool)$subModule[$i]['status'] === true){
               $return[$index]['registered']    += 1;
            }

            if((bool)$subModule[$i]['status'] === false){
               $return[$index]['unregister']    += 1;
               $data_detail['sub_module']['unregister'][]   = $subModule[$i];
            }
         }
         sort($mModule);

         for ($i=0; $i < count($registeredModule); $i++) {
            $rModule[$i]   = $registeredModule[$i]['sub_module'];
            if(!in_array(lcfirst($registeredModule[$i]['sub_module']), $mModule)){
               // $return[$index]['conflict'][] = $registeredModule[$i]['sub_module'];
               $return[$index]['conflict']   += 1;
               $data_detail['sub_module']['conflict'][]     = $registeredModule[$i];
            }else{
               $data_detail['sub_module']['register'][]     = $registeredModule[$i];
            }
         }
         $index++;
      }

      $data_module      = $return[0];
      return compact('data_module', 'data_detail');
   }

   public function DoCleanConflictedModule($appId = NULL, $module = NULL)
   {
      $dataModule       = self::GetConflictedModule($appId, $module);
      $module           = array();
      $index            = 0;
      $result           = true;
      $this->StartTrans();
      foreach ($dataModule as $mod) {
         $module[$index]   = $mod['module_id'];
         $result        &= $this->Execute($this->mSqlQueries['do_delete_gtfw_module'], array(
            $mod['module_id']
         ));
      }

      return $this->EndTrans($result);
   }

   public function DoFixSubModuleConflict($subModule = array())
   {
      $result        = true;
      $this->StartTrans();
      if(!is_array($subModule)){
         $result     &= false;
      }

      foreach ($subModule as $sub) {
         $result        &= $this->Execute($this->mSqlQueries['do_delete_gtfw_module'], array(
            $sub['id']
         ));
      }
      return $this->EndTrans($result);
   }

   public function GetSubModule($module, $appId)
   {
      $modulePath    = realpath($this->mPath.'/'.$module.'/response');
      // $subModule     = self::GetListDirectory($modulePath);
      $dataModule    = array();
      $index         = 0;
      if(is_dir($modulePath) && file_exists($modulePath)){
         if($hd = opendir($modulePath)){
            while ($sub = readdir($hd)) {
               if(in_array($sub, $this->mIgnore)){
                  continue;
               }
               list($name, $type, $class, $ext)    = explode('.', $sub);
               $humanize   = preg_replace('/(?<=\w)([A-Z])/', '_\1', $name);
               $split      = preg_split('/[\s_]+/', $humanize);
               $act        = ucfirst($split[0]);
               if(!in_array($act, $this->mAction)){
                  continue;
               }
               if(!in_array($type, $this->mTypes)){
                  continue;
               }

               $subModuleName                      = preg_replace('/'.$split[0].'/', '', $name);
               $checkStatusRegister                = self::GetRegisteredModule(
                  $appId,
                  $module,
                  $subModuleName,
                  $act,
                  $type
               );
               $status                             = self::Count();
               $dataModule[$index]['sub_module']   = $sub;
               $dataModule[$index]['name']         = lcfirst($subModuleName);
               $dataModule[$index]['action']       = strtolower($act);
               $dataModule[$index]['type']         = strtolower($type);
               $dataModule[$index]['class']        = $class;
               $dataModule[$index]['ext']          = $ext;
               $dataModule[$index]['status']       = (empty($checkStatusRegister)) ? FALSE : TRUE;
               $index++;
            }
            closedir($hd);
         }
      }
      return $dataModule;
   }

   private function GetListDirectory($path = null)
   {
      $path       = is_null($path) ? $this->mPath : $path;
      $search     = array('index.html', 'index.php');
      $index      = 0;
      $dataList   = array();
      if(!is_dir($path)){
         return null;
      }
      if(!is_readable($path)){
         return null;
      }
      if(!$handle = opendir($path)){
         return null;
      }

      if(is_dir($path)){
         if($handle = opendir($path)){
            while ($dh = readdir($handle)) {
               if(in_array($handle, $this->mIgnore)){
                  continue;
               }
               if(strpos($dh, '.') === 0){
                  continue;
               }
               if(strpos(strrev($dh), '~') === 0){
                  continue;
               }
               if(in_array($dh, $search) && dirname($path.$dh) == $_SERVER['DOCUMENT_ROOT']){
                  continue;
               }
               $dataList[$index]['id']    = $dh;
               $dataList[$index]['name']  = $dh;
               $dataList[$index]['path']  = realpath($path.'/'.$dh);
               $index++;
            }

            closedir($handle);
         }
      }

      return (array)$dataList;
   }

   public function Count()
   {
      $return     = $this->Open($this->mSqlQueries['count'], array());

      if($return){
         return $return[0];
      }else{
         return 0;
      }
   }

   public function GetRegisteredModule($appId, $module = null, $subModule = NULL, $action = NULL, $type = NULL)
   {
      $return     = $this->Open($this->mSqlQueries['get_registered_module'], array(
         $appId,
         strtolower($module),
         (int)($module == '' OR $module === NULL),
         strtolower($subModule),
         (int)($subModule == '' OR $subModule === NULL),
         $action,
         (int)($action == '' OR $action === NULL),
         $type,
         (int)($type == '' OR $type === NULL)
      ));

      return $return;
   }

   public function DoRegisterModule($dataModule = array())
   {
      $result     = true;
      $this->StartTrans();
      if(!is_array($dataModule)){
         $result  &= false;
      }

      foreach ($dataModule as $module) {
         $result  &= $this->Execute($this->mSqlQueries['do_insert_gtfw_module'], array(
            $module['module'],
            strtolower($module['label']),
            $module['sub_module'],
            $module['action'],
            $module['type'],
            $module['description'],
            $module['access'],
            $module['app_id']
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
}
?>