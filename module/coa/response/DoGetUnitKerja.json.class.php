<?php

require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/Coa.class.php';

class DoGetUnitKerja extends JsonResponse
{
    public function ProcessRequest()
    {
        $mObj          = new Coa();
        $dataList      = $mObj->GetUnitKerjaRef();
        $dataGrid      = array();

        if(!empty($dataList)){
            $items      = array();
            foreach ($dataList as $list) {
                $items[$list['parentId']][]  = array(
                'id' => $list['id'],
                'text' => $list['kode'].' &mdash; '.$list['nama'],
                'kode_sistem' => $list['kodeSistem']
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
