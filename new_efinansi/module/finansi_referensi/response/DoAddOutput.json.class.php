<?php
/**
* @package DoAddOutput
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/response/Output.proc.class.php';
class DoAddOutput extends JsonResponse
{
   protected $_POST;
   protected $_GET;
   private $mObj;
   function __construct()
   {
      $this->mObj          = new Output();
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
   }

   public function ProcessRequest()
   {
      $getQueryString      = self::__getQueryString();
      $urlRedirect         = $this->mObj->Add().'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$getQueryString;
      return array( 
         'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")'
      );
   }

   /**
    * @param string $pathInfo path_info url to be parsed, default null
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