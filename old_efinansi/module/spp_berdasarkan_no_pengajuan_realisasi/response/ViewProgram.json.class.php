<?php
/**
* @package ViewProgram
* @copyright Copyright (c) PT Gamatechno Indonesia
* @author Dyan Galih <galih@gamatechno.com>
* @version 0.1
* @startDate 2010-10-10
* @lastUpdate 2010-10-10
* @description View Program Json
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/realisasi_pencairan_2/business/RealisasiPencairan.class.php';

class ViewProgram extends JsonResponse {

   function TemplateModule() {

   }

   function ProcessRequest() {
      $obj = new RealisasiPencairan;
      $idTa = $_GET['id_ta'];
      $opt = (isset($_GET['opt']) ? $_GET['opt'] : 'all');
	  	
      $arrProgram = $obj->GetDataProgram($idTa);

      $arrProgram = json_encode($arrProgram);
      return array( 'exec' => 'replaceProgram('.$arrProgram.',"'.$opt.'")');
   }

   function ParseTemplate($data = NULL) {
   }
}
?>