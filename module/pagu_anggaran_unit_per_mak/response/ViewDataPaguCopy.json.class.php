<?php 
#doc
# package:     ViewDataPaguCopy
# scope:       PUBLIC
# @created     ucil.619
# @Author      Eko Susilo
# @Created     2013-06-20
# @Modified    2013-06-20
# @Analysts    Nanang Ruswianto
# @copyright   Copyright (c) 2012 Gamatechno
#/doc
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/pagu_anggaran_unit_per_mak/business/PaguAnggaranUnitPerMak.class.php';

class ViewDataPaguCopy extends JsonResponse
{
   #   internal variables
   public $mObj;
   protected $_GET;
   protected $_POST;
   #   Constructor
   function __construct ()
   {
      if(is_object($_GET)){
         $this->_GET       = $_GET->AsArray();
      }else{
         $this->_GET       = $_GET;
      }
      if(is_object($_POST)){
         $this->_POST      = $_POST->AsArray();
      }else{
         $this->_POST      = $_POST;
      }
      $this->mObj          = new PaguAnggaranUnitPerMak();
   }
   
   public function ProcessRequest()
   {
      $post          = array();
      $post['srcTaId']     = $this->_GET['srcTaId'];
      $post['destTaId']    = $this->_GET['destTaId'];
      $post['unitId']      = $this->_GET['unitId'];
      $post['unitNama']    = $this->_GET['unitNama'];

      $dataObject          = $this->mObj->GetListPaguAnggaran((array)$post);
      // ubah data array menjadi data json
      $dataArray           = json_encode($dataObject);

      return array('exec' => 'loadData('.$dataArray.');');
   }
}
?>