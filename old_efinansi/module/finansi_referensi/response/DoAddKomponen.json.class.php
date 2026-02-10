<?php
/**
* @package DoAddKomponen
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/response/Komponen.proc.class.php';

class DoAddKomponen extends JsonResponse
{
   private $mObj;
   protected $_POST;
   protected $_GET;
   private $queryString;
   function __construct()
   {
      $this->mObj          = new Komponen();
      if(is_object($_POST)){
         $this->_POST      = $_POST->AsArray();
      }else{
         $this->_POST      = $_POST;
      }
      if(is_object($_GET)){
         $this->_GET       = $_GET->AsArray();
      }else{
         $this->_GET       = $_GET;
      }
      $this->queryString   = self::__getQueryString();
   }

   public function ProcessRequest()
   {
      $queryString         = $this->queryString.'&search='.Dispatcher::Instance()->Encrypt(1);
      $urLRedirect         = $this->mObj->Add().'&'.$queryString;
      return array( 
         'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urLRedirect.'&ascomponent=1")'
      );
   }

   /**
    * @param string  path_info url to be parsed, default null
    * @return string parsed_url based on Dispatcher::Instance()->getQueryString();
    */
   public function __getQueryString($pathInfo = null)
   {
      $parseUrl            = is_null($pathInfo) ? parse_url($_SERVER['QUERY_STRING']) : parse_url($pathInfo);
      $explodedUrl         = explode('&', $parseUrl['path']);
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