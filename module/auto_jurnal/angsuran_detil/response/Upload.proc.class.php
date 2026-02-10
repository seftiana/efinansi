<?php
/**
* ================= doc ====================
* FILENAME     : Upload.proc.class.php
* @package     : Upload
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-07-17
* @Modified    : 2014-07-17
* @Analysts    : Nanang Ruswianto <nanang@gamatechno.com>
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/'.Dispatcher::Instance()->mModule.'/business/Upload.class.php';

class UploadRAB
{

   private $pageView;
   private $cssDone;
   private $cssFail;

   public function __construct($html=true)
   {
      if($html){
         $this->pageView   = Dispatcher::Instance()->GetUrl(
            'angsuran_detil',
            'rencanaPengeluaran',
            'view',
            'html'
         );
      }else{
         $this->pageView   = Dispatcher::Instance()->GetUrl(
            'angsuran_detil',
            'rencanaPengeluaran',
            'view',
            'html',
            true
         );
      }
      $this->cssFail = 'notebox-warning';
      $this->cssDone = 'notebox-done';
   }

   public function Upload()
   {
      $mObj                = new Upload();
      $queryString         = $mObj->_getQueryString();
      $dataId              = $mObj->_POST['data_id'];
      $uploadFile          = $this->UploadFile($_FILES['rab_file'], $dataId);

      if((bool)$uploadFile['error'] === true){
          Messenger::Instance()->Send(
            'angsuran_detil',
            'rencanaPengeluaran',
            'view',
            'html',
            array(
               NULL,
               $uploadFile['message'],
               'notebox-warning'
            ),Messenger::NextRequest);
      }else{
         $mObj->UpdateFileRAB($uploadFile['file'],$dataId);
         Messenger::Instance()->Send(
            'angsuran_detil',
            'rencanaPengeluaran',
            'view',
            'html',
            array(
               NULL,
               'Upload File RAB sukses',
               'notebox-done'
            ),Messenger::NextRequest);
      }

      return $this->pageView.'&search=1&'.$queryString;
   }

   private function UploadFile($files, $id)
   {
      $return              = array();
      $return['error']     = true;
      $return['message']   = null;
      $return['data']      = null;
      $MAXIMUM_FILESIZE    = 8 * 1024 * 1024;
      $fileTypeAllowed     = array(
         "application/x-gzip",
         "application/x-rar",
         "application/zip",
         "application/rtf",
         "application/msword",
         "application/wps-office.doc",
         "application/wps-office.docx",
         "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
         "application/vnd.ms-excel",
         "application/wps-office.xls",
         "application/wps-office.xlsx",
         "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
         "application/pdf"
      );
      $typeAccepted        = array('doc', 'docx', 'xls', 'xlsx', 'pdf', 'rar', 'zip', 'rtf');
      $basedir             = realpath(GTFWConfiguration::GetValue('application', 'docroot') . "document/rab/");
      $file_name           = $files['name'];
      $file_tmp            = $files['tmp_name'];
      $file_size           = $files['size'];
      $file_type           = $files['type'];


      if(empty($files['tmp_name'])){
         $err[]         = 'Tidak ada file yang akan di upload';
      }

      if($file_size > 0 && $file_size > $MAXIMUM_FILESIZE){
         $err[]         = 'Ukuran file yang Anda upload terlalu besar. Maximal file upload 10MB';
      }

      // jika file yang di upload tidak sesuai dengan file yang di definisikan
      if(!in_array($file_type, $fileTypeAllowed)){
         $err[]         = 'File yang bisa di upload adalah : '. implode(',', $typeAccepted);
      }

      if(!is_writable($basedir)){
         $err[]         = 'Cek directory tujuan upload. Pastikan directory tujuan upload writable dan readable.';
      }

      if(isset($err)){
         $return['error']     = true;
         $return['message']   = $err[0];
         $return['file']      = null;
      }else{
         foreach (glob($basedir.'/RAB_'.$id.'_*') as $filename) {
            unlink($filename);
         }
         $timestamp           = date('YmdHis', time());
         $ext                 = pathinfo($file_name, PATHINFO_EXTENSION);
         $newFile             = 'RAB_' . $id . '_' . $timestamp .'.' . $ext;

         if(move_uploaded_file($file_tmp, $basedir.'/'.$newFile)){
            $return['error']     = false;
            $return['message']   = 'Upload File RAB sukses - ';
            $return['file']      = $newFile;
         }else{
            $return['error']     = true;
            $return['message']   = 'Upload File RAB gagal';
            $return['file']      = null;
         }
      }

      return (array)$return;
   }

}
?>