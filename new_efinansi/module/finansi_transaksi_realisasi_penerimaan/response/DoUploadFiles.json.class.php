<?php
/**
* ================= doc ====================
* FILENAME     : DoUploadFiles.json.class.php
* @package     : DoUploadFiles
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-01
* @Modified    : 2015-03-01
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/

class DoUploadFiles extends JsonResponse
{
   public function ProcessRequest()
   {
      if (!defined('DS')) {
         define('DS', DIRECTORY_SEPARATOR);
      }
      $dataDir       = GTFW_APP_DIR . DS . 'file';
      $tmpDirUpload  = realpath($dataDir) . DS . 'tmp';
      $uploadDir     = realpath($dataDir) . DS . 'rencana_penerimaan';
      $file          = $_FILES['attachment'];
      $return        = array();

      if(!file_exists($dataDir)){
         $err[]      = 'Folder tujuan upload tidak ditemukan';
      }
      if(!is_writable($dataDir)){
         $err[]      = 'Pastikan folder tujuan upload writable';
      }

      if(isset($err)){
         $return['message']   = $err[0];
         $return['result']    = false;
      }else{
         if(!file_exists($tmpDirUpload)){
            mkdir($tmpDirUpload);
            chmod($tmpDirUpload, 0777);
         }

         if(!file_exists($uploadDir)){
            mkdir($uploadDir);
            chmod($uploadDir, 0777);
         }
         $fileTmp    = $file['tmp_name'];
         $fileName   = $file['name'];
         $fileSize   = $file['size'];
         $name       = pathinfo($fileName, PATHINFO_FILENAME);
         $ext        = pathinfo($fileName, PATHINFO_EXTENSION);
         $prefix     = 'p'.date("YmdHis", time());
         $fileName   = preg_replace('/\s[\s]+/','-',$fileName);
         $fileName   = preg_replace('/^[\-]+/','',$fileName);
         $fileName   = preg_replace('/[\-]+$/','',$fileName);
         $fileName   = preg_replace('/\s+/', '_', $fileName);
         $newName    = $prefix.'_'.$fileName;
         if(move_uploaded_file($fileTmp, realpath($tmpDirUpload). DS . $newName)){
            chmod(realpath($tmpDirUpload). DS . $newName, 0777);
            $return['file_name'] = $fileName;
            $return['fake_path'] = $newName;
            $return['size']      = $fileSize;
            $return['result']    = true;
            $return['message']   = NULL;
         }else{
            $return['result']    = false;
            $return['message']   = 'Upload file gagal';
         }
      }

      return $return;
   }
}
?>