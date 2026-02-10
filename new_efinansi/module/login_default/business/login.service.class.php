<?php
require_once GTFWConfiguration::GetValue( 'application', 'gtfw_base').'main/lib/sobb/PublicServiceProvider.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'gtfw_base').'main/lib/gtfw/security/login_method/default/Login.class.php';

class LoginService extends PublicServiceProvider {
   var $mFunctions = array(
      'Login' => array(
            'in' => array("username" => "xsd:string", "password" => "xsd:string"),
            'out' => array("return" => "xsd:boolean"),
            'namespace' => 'soapgateway',
            'soapaction' => 'soapgateway',
            'style' => 'rpc',
            'use' => false,
            'documentation' => 'Login Service',
            'encodingStyle' => 'utf8'),
       'IsLoggedIn' => array(
            'in' => array(),
            'out' => array("return" => "xsd:boolean"),
            'namespace' => 'soapgateway',
            'soapaction' => 'soapgateway',
            'style' => 'rpc',
            'use' => false,
            'documentation' => 'IsLoggedIn Service',
            'encodingStyle' => 'utf8')
         );

   function Login($username, $password) {
      $login = new Login();
      $login->SetUsername($username);
      $login->SetPassword($password);

      $result = $login->DoLogin();
      return new soapval("return", "xsd:boolean", $result);
   }

   function IsLoggedIn() {
      return Security::Instance()->IsLoggedIn();
   }

   function collectServices() {
      //die('collectServices::register service here');
      $this->registerService($this->mFunctions);
   }

   function getServicePrefix() {
      return 'auth';
   }
}

?>
