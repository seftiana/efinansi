<?php
/**
* @package DoDeleteKegiatan
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/response/Kegiatan.proc.class.php';

class DoDeleteKegiatan extends JsonResponse
{
   protected $_POST;
   protected $_GET;
   private $mObj;
   function __construct()
   {
      $this->mObj          = new Kegiatan();
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
      $queryData['tahun_anggaran']  = Dispatcher::Instance()->Decrypt($this->_GET['tahun_anggaran']);
      $queryData['kegiatan_id']     = Dispatcher::Instance()->Decrypt($this->_GET['kegiatan_id']);
      $queryData['kegiatan']        = Dispatcher::Instance()->Decrypt($this->_GET['kegiatan']);
      $queryData['output_id']       = Dispatcher::Instance()->Decrypt($this->_GET['output_id']);
      $queryData['output']          = Dispatcher::Instance()->Decrypt($this->_GET['output']);
      $queryData['kode']            = Dispatcher::Instance()->Decrypt($this->_GET['kode']);
      $queryData['nama']            = Dispatcher::Instance()->Decrypt($this->_GET['nama']);
      $queryData['ta_label']        = Dispatcher::Instance()->Decrypt($this->_GET['ta_label']);
      $queryData['search']          = 1;

      foreach ($queryData as $key => $value) {
         $queryBuild[$key]          = Dispatcher::Instance()->Encrypt($value);
      }
      $queryString               = urldecode(http_build_query($queryBuild));

      $urlRedirect      = $this->mObj->Delete().'&'.$queryString;

      return array( 
         'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")'
      );
      
   }
}
?>