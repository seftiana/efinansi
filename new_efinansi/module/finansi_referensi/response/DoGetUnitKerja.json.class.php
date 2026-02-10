<?php
/**
* ================= doc ====================
* FILENAME     : DoGetUnitKerja.json.class.php
* @package     : DoGetUnitKerja
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-11-11
* @Modified    : 2014-11-11
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/FinansiReferensi.class.php';

class DoGetUnitKerja extends JsonResponse
{
   public function ProcessRequest()
   {
      $mObj          = new FinansiReferensi();
      $dataList      = $mObj->ChangeKeyName($mObj->GetUnitKerjaRef());
      $dataGrid      = array();

      if(!empty($dataList)){
         $items      = array();
         foreach ($dataList as $list) {
            $items[$list['parent_id']][]  = array(
               'id' => $list['id'],
               'text' => $list['kode'].' &mdash; '.$list['nama'],
               'kode_sistem' => $list['kode_sistem']
            );
         }

         $parent     = $items[0];
         $dataGrid   = $mObj->createTree($items, $parent);
      }

      $dataTree['id']   = 0;
      $dataTree['item'] = $dataGrid;
      return $dataTree;


   }
}
?>