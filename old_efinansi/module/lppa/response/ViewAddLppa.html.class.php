<?php

/**
* ================= doc ====================
* FILENAME     : ViewAddTransaksiSpj.html.class.php
* @package     : ViewAddTransaksiSpj
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-17
* @Modified    : 2015-03-17
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/

require_once GTFWConfiguration::GetValue('application','docroot').
'module/lppa/business/Lppa.class.php';

require_once GTFWConfiguration::GetValue('application','docroot').
'module/lppa/business/AppReferensi.class.php';

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewAddLppa extends HtmlResponse
{
   function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/lppa/template/');
      $this->SetTemplateFile('view_add_lppa.html');
   }

   function ProcessRequest()
   {
            
        $_POST = $_POST->AsArray();
        
        $idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);

        $this->mObj = new Lppa;
        $this->mObjRef = new AppReferensi;
        $msg = Messenger::Instance()->Receive(__FILE__);
        $this->Pesan = $msg[0][1];
        
        
        $userUnitKerja = new UserUnitKerja();
        $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
        $this->Role = $userUnitKerja->GetRoleUser($userId);
        $unit = $userUnitKerja->GetUnitKerjaUser($userId);
        $unit_parent = $userUnitKerja->GetUnitKerja($unit['unit_kerja_parent_id']);

        if($_REQUEST['dataId'] == '') {

                $this->Data = $_POST;
                //tahun anggaran dari $_POST dan $_GET, unitkerja dan sub-unit dari $_POST dan $_GET
                $tahun_anggaran = $this->mObj->GetTahunAnggaranAktif();
                $this->Data['lppa_id'] = '';
                $this->Data['tahun_anggaran_id'] = $tahun_anggaran['id'];
                $this->Data['tahun_anggaran_nama'] = $tahun_anggaran['name'];
                $this->Data['unit_kerja_id'] = $unit['unit_kerja_id'];
                $this->Data['unit_kerja_nama'] = $unit['unit_kerja_nama'];
                $this->Data['realisasi_id'] = $unit['realisasi_id'];
                $this->Data['realisasi_no'] = $unit['realisasi_no'];
                $this->Data['penanggung_jawab'] = $unit['penanggung_jawab'];
                $this->Data['mengetahui'] = $unit['mengetahui'];
                
           
        } else {
            //edit data            
            $dataLPPA = $this->mObj->GetLppaById($idDec);
            $this->Data['lppa_id'] = $dataLPPA['lppa_id'];
            $this->Data['tahun_anggaran_old'] = $dataLPPA['tahun_anggaran_id_old'];
            $this->Data['tahun_anggaran_id'] = $dataLPPA['tahun_anggaran_id'];
            $this->Data['unit_kerja_id'] = $dataLPPA['unit_kerja_id'];
            $this->Data['unit_kerja_old'] = $dataLPPA['unit_kerja_id_old'];
            $this->Data['unit_kerja_nama'] = $dataLPPA['unit_kerja_nama'];
            $this->Data['realisasi_id'] = $dataLPPA['realisasi_id'];
            $this->Data['realisasi_no'] = $dataLPPA['realisasi_no'];
            $this->Data['penanggung_jawab'] = $dataLPPA['penanggung_jawab'];
            $this->Data['mengetahui'] = $dataLPPA['mengetahui'];
            $this->Data['uraian'] = $dataLPPA['uraian'];
        }
        
        if(isset($msg[0][0])):
          $this->Data = $msg[0][0];
        endif;


                $arr_tahun_anggaran = $this->mObj->GetComboTahunAnggaran();
                
                Messenger::Instance()->SendToComponent(
                                        'combobox', 
                                        'Combobox', 
                                        'view', 
                                        'html', 
                                        'tahun_anggaran', 
                                        array(
                                                'tahun_anggaran', 
                                                $arr_tahun_anggaran, 
                                                $this->Data['tahun_anggaran'], '-', 
                                                ' style="width:200px;" id="tahun_anggaran"'), 
                                        Messenger::CurrentRequest);
                                        
        $return['decDataId'] = $idDec;
        $return['total_sub_unit'] = $userUnitKerja->GetTotalSubUnitKerja($unit['unit_kerja_id']);      
//$this->mObjRef->SetDebugOn();
        $dataDetailBelanjaFpa     = $this->mObjRef->GetDetailBelanjaFpaById($this->Data['realisasi_id'],$this->Data['lppa_id']);
        //echo '<pre>';
        //print_r($dataDetailBelanjaFpa);
        $return['detail_belanja_fpa']['data']  = json_encode($dataDetailBelanjaFpa);
        //print_r($return['detail_belanja_fpa']);
        //echo '</pre>';
        return $return;
   }

   function ParseTemplate($data = null)
   {
        $dataDetailBelanjaFpa = $data['detail_belanja_fpa'];
        $this->mrTemplate->AddVars('content', $dataDetailBelanjaFpa, 'KOMP_');

        if($data['total_sub_unit'] > 0){
                $this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT','YES');
        } else {
                $this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT', 'NO');
        }   

        if ($this->Pesan) {
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
        }

    
      $urlPopupUnitKerja   = Dispatcher::Instance()->GetUrl(
         'lppa',
         'PopupUnitKerja',
         'view',
         'html'
      );

      $urlPopupRealisasi   = Dispatcher::Instance()->GetUrl(
         'lppa',
         'PopupRealisasi',
         'view',
         'html'
      );

      $urlAction           = Dispatcher::Instance()->GetUrl(
         'lppa',
         'AddLppa',
         'do',
         'json'
      );

    
        if ( $this->Data['lppa_id'] == '') {
            $url="addLppa";
            $tambah="Tambah";
        } else {
            $url="updateLppa";
            $tambah="Ubah";
            $this->mrTemplate->AddVar('content', 'LPPA_ID', $data['decDataId']);
        }


        $this->mrTemplate->AddVar('content', 'URL_ACTION', 
                                    Dispatcher::Instance()->GetUrl(
                                                                'lppa', 
                                                                $url, 
                                                                'do', 
                                                                'html') . 
                                                                "&dataId=" . 
                                    Dispatcher::Instance()->Encrypt($data['decDataId']));
                                          
        $this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
        $this->mrTemplate->AddVar('content', 'LPPA_ID',$this->Data['lppa_id']);
        $this->mrTemplate->AddVar('content', 'JUMLAH_KELAS',$this->Data['jumlah_kelas']);
        $this->mrTemplate->AddVar('content', 'UNIT_KERJA_ID', $this->Data['unit_kerja_id']);
        $this->mrTemplate->AddVar('content', 'UNIT_KERJA_ID_OLD', $this->Data['unit_kerja_old']);
        $this->mrTemplate->AddVar('content', 'UNIT_KERJA_NAMA', $this->Data['unit_kerja_nama']);
        $this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN_ID', $this->Data['tahun_anggaran_id']);
        $this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN_ID_OLD', $this->Data['tahun_anggaran_old']);
        $this->mrTemplate->AddVar('content', 'REALISASI_ID', $this->Data['realisasi_id']);
        $this->mrTemplate->AddVar('content', 'REALISASI_NO', $this->Data['realisasi_no']);
        $this->mrTemplate->AddVar('content', 'PENANGGUNG_JAWAB', $this->Data['penanggung_jawab']);
        $this->mrTemplate->AddVar('content', 'MENGETAHUI', $this->Data['mengetahui']);        
        $this->mrTemplate->AddVar('content', 'URAIAN', $this->Data['uraian']);        
        $this->mrTemplate->AddVar('cek_unitkerja_parent', 'UNIT_KERJA_NAMA',$this->Data['unit_kerja_nama']);
        $this->mrTemplate->AddVar('cek_unitkerja_parent','URL_POPUP_UNIT_KERJA', $urlPopupUnitKerja);
                                                                            
        $this->mrTemplate->AddVar('content', 'URL_POPUP_REALISASI', $urlPopupRealisasi);
        
        if($message){
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
            $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
        }
   }
}

?>