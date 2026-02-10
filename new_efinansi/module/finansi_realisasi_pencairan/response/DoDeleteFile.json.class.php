<?php
/**
* ================= doc ====================
* FILENAME     : DoDeleteFile.json.class.php
* @package     : DoDeleteFile
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-04
* @Modified    : 2015-03-04
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/finansi_realisasi_pencairan/business/RealisasiPencairan.class.php';

class DoDeleteFile extends JsonResponse
{
   public function ProcessRequest()
   {
      $mObj          = new RealisasiPencairan();
      $dataDir       = GTFW_APP_DIR . DIRECTORY_SEPARATOR . 'file';
      $tmpDirUpload  = realpath($dataDir) . DIRECTORY_SEPARATOR . 'tmp';
      $file          = $mObj->_POST['file'];
      $file          = realpath($tmpDirUpload . DIRECTORY_SEPARATOR . $file);
      if(file_exists($file)){
         if(unlink($file)){
            return true;
         }else{
            return false;
         }
      }else{
         return false;
      }
   }
}
?>