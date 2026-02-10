<?php
/**
* ================= doc ====================
* FILENAME     : DoDeleteFile.json.class.php
* @package     : DoDeleteFile
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-27
* @Modified    : 2015-04-27
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/finansi_transaksi_spj_history/business/HistoryTransaksiSpj.class.php';

class DoDeleteFile extends JsonResponse
{
   public function ProcessRequest()
   {
      $mObj          = new HistoryTransaksiSpj();
      $dataDir       = GTFW_APP_DIR . DIRECTORY_SEPARATOR . 'file';
      $tmpDirUpload  = realpath($dataDir) . DIRECTORY_SEPARATOR . 'tmp';
      $dirUpload     = realpath($dataDir) . DIRECTORY_SEPARATOR . 'spj';
      $file          = $mObj->_POST['file'];
      $tmpFile       = realpath($tmpDirUpload . DIRECTORY_SEPARATOR . $file);
      $uloadedFile   = realpath($tmpDirUpload . DIRECTORY_SEPARATOR . $file);

      if(file_exists($tmpFile)){
         if(unlink($tmpFile)){
            $mObj->doDeleteFile($file);
            return true;
         }else{
            return false;
         }
      }elseif(file_exists($uloadedFile)){
         if(unlink($uloadedFile)){
            $mObj->doDeleteFile($file);
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