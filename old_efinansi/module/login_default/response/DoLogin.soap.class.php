<?php
require_once GTFWConfiguration::GetValue( 'application', 'gtfw_base').'main/lib/sobb/SoapGatewayBase.class.php';

require_once GTFWConfiguration::GetValue( 'application', 'docroot').'module/login_default/business/login.service.class.php';

/**
* Default Login Gateway
* @package Login
* @author Akhmad Fathonih <toni@gamatechno.com>
* @version 1.0
* @copyright 2006&copy;Gamatechno
*/

class DoLogin extends SoapGatewayBase {
   function __construct() {
      parent::__construct();
   }

   /**
   Overide this method to set WSDL
   */
   function configureWsdlEvent() {

      $this->configureWsdl('LoginService', false, GTFWConfiguration::GetValue( 'application', 'baseaddress').Dispatcher::Instance()->GetWsdlUrl('login_default', 'Login', 'Do', 'soap'));
   }

   /**
   * register service object here
   */
   function collectServiceObjects() {
      $objDefault = new LoginService();
      $this->importServices($objDefault);
   }
}

?>
