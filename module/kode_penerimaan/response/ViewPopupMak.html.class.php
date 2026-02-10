<?php
#doc
# package:     ViewPopupMak
# scope:       PUBLIC
# @created     ucil.619
# @Author      Eko Susilo
# @Created     2012-09-12
# @Modified    2012-09-12
# @Analysts    Nanang Ruswianto
# @copyright   Copyright (c) 2012 Gamatechno
#/doc
require_once GTFWConfiguration::GetValue('application','docroot').
'module/kode_penerimaan/business/AppPopupMak.class.php';

class ViewPopupMak extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/kode_penerimaan/template/');
      $this->SetTemplateFile('view_popup_mak.html');
   }

   function ProcessRequest(){
      $objMak     = new AppPopupMak();
      $_POST      = $_POST->AsArray();
      $_GET       = $_GET->AsArray();

      if(isset($_POST['btnSearch'])){
         $kode    = trim($_POST['kode']);
      }elseif($_GET['search']){
         $kode    = Dispatcher::Instance()->Decrypt($_GET['kode']);
      }else{
         $kode    = '';
      }

      $offset         = 0;
      $limit          = 20;
      $page           = 0;

      if(isset($_GET['page'])){
         $page    = (string) $_GET['page'];
         $offset  = ($page - 1) * $limit;
      }
      #paging url
      $url    = Dispatcher::Instance()->GetUrl(
                Dispatcher::Instance()->mModule,Dispatcher::Instance()->mSubModule,
                Dispatcher::Instance()->mAction,
                Dispatcher::Instance()->mType).
                '&kode='.Dispatcher::Instance()->Encrypt($kode).
                '&search='.Dispatcher::Instance()->Encrypt(1);

      $destination_id   = "popup-subcontent";

      $data             = $objMak->GetData($kode, $offset, $limit);
      $total_data       = $objMak->Count();
      #send data to pagging component
      Messenger::Instance()->SendToComponent(
         'paging',
         'Paging',
         'view',
         'html',
         'paging_top',
         array(
            $limit,
            $total_data,
            $url,
            $page,
            $destination_id
         ),
         Messenger::CurrentRequest
      );

      $return['dataList']     = $data;
      $return['start']        = $offset+1;
      return $return;
   }

   function ParseTemplate($data = null){
      $url_search    = Dispatcher::Instance()->GetUrl(
         'kode_penerimaan',
         'PopupMak',
         'view',
         'html'
      );

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $url_search);

      $lists         = $data['dataList'];
      if(empty($lists)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');

         # inisialisasi
         $index         = 0;
         $dataLists     = array();
         $bas           = '';

         for ($i = 0; $i < count($lists);)
         {

            if($bas == $lists[$i]['bas_id'])
            {
               $dataLists[$index]['id']      = $lists[$i]['paguBasId'];
               $dataLists[$index]['kode']    = $lists[$i]['paguBasKode'];
               $dataLists[$index]['nama']    = $lists[$i]['paguBasKeterangan'];
               $dataLists[$index]['type']    = 'child';
               $dataLists[$index]['nomor']   = $data['start']+$i;
               $i++;
            }
            else
            {
               $bas                          = $lists[$i]['bas_id'];
               $dataLists[$index]['id']      = $lists[$i]['paguBasParentId'];
               $dataLists[$index]['kode']    = $lists[$i]['bas_kode'];
               $dataLists[$index]['nama']    = $lists[$i]['bas_nama'];
               $dataLists[$index]['type']    = 'parent';
            }
            $index++;
         }

         unset($i);
         for ($i = 0; $i < count($dataLists); $i++)
         {
            if($dataLists[$i]['type'] == 'parent'){
               $dataLists[$i]['class_name']     = 'table-common-even1';
               $dataLists[$i]['row_style']      = 'font-weight: bold;';
               $dataLists[$i]['link']           = 'display: none;';
            }else{
               if($dataLists[$i]['nomor'] % 2 != 0){
                  $dataLists[$i]['class_name']  = 'table-common-even2';
               }else{
                  $dataLists[$i]['class_name']  = '';
               }
            }
            $this->mrTemplate->AddVars('data_list', $dataLists[$i], 'DATA_');
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }

      }

   }
}
?>