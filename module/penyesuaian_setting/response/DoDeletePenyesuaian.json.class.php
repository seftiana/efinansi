<?php
/**
* @package DoDeletePenyesuaian
* @copyright Copyright (c) PT Gamatechno Indonesia
* @Analyzed By Dyan Galih <galih@gamatechno.com>
* @author Dyan Galih <galih@gamatechno.com>
* @version 0.1
* @startDate 2011-09-11
* @lastUpdate 2011-09-11
* @description Do Delete Penyesuaian
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/penyesuaian_setting/response/PenyesuaianSetting.proc.class.php';

class DoDeletePenyesuaian extends JsonResponse {
   function ProcessRequest() {

      $objPenyesuaianSetting = new PenyesuaianSettingProc();

      $urlRedirect = $objPenyesuaianSetting->Delete();

      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
   }
}
?>