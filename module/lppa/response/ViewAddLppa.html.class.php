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
        $periodeTahun = $this->mObj->GetPeriodeTahun(array('active' => true));
        $minYear = date('Y', strtotime($periodeTahun[0]['start']));
        $maxYear = date('Y', strtotime($periodeTahun[0]['end'])); 
        if($_REQUEST['dataId'] == '') {

                $this->Data = $_POST;
                //tahun anggaran dari $_POST dan $_GET, unitkerja dan sub-unit dari $_POST dan $_GET
                $tahun_anggaran = $this->mObj->GetTahunAnggaranAktif(); 
                $this->Data['lppa_id'] = '';
                $this->Data['tahun_anggaran_id'] = $tahun_anggaran['id'];
                $this->Data['tahun_anggaran_nama'] = $tahun_anggaran['name'];
                $this->Data['unit_kerja_id'] = $unit['unit_kerja_id'];
                $this->Data['unit_kerja_nama'] = $unit['unit_kerja_nama'];
                $this->Data['realisasi_id_exist'] = '';
                $this->Data['realisasi_id'] = $unit['realisasi_id'];
                $this->Data['realisasi_no'] = $unit['realisasi_no'];
                $this->Data['penanggung_jawab'] = $unit['penanggung_jawab'];
                $this->Data['mengetahui'] = $unit['mengetahui'];
                $this->Data['lppa_file'] = $unit['lppa_file'];
                $this->Data['lppa_file_exist'] ='';
                $this->Data['tanggal'] =  date('Y-m-d');
                $this->Data['nomor_lppa'] = '';
        } else {
            //edit data            
            $dataLPPA = $this->mObj->GetLppaById($idDec);
            $this->Data['lppa_id'] = $dataLPPA['lppa_id'];
            $this->Data['tahun_anggaran_id_old'] = $dataLPPA['tahun_anggaran_id_old'];
            $this->Data['tahun_anggaran_id'] = $dataLPPA['tahun_anggaran_id'];
            $this->Data['unit_kerja_id'] = $dataLPPA['unit_kerja_id'];
            $this->Data['unit_kerja_id_old'] = $dataLPPA['unit_kerja_id_old'];
            $this->Data['unit_kerja_nama'] = $dataLPPA['unit_kerja_nama'];
            $this->Data['realisasi_id_exist'] = $dataLPPA['realisasi_id'];
            $this->Data['realisasi_id'] = $dataLPPA['realisasi_id'];
            $this->Data['realisasi_no'] = $dataLPPA['realisasi_no'];
            $this->Data['penanggung_jawab'] = $dataLPPA['penanggung_jawab'];
            $this->Data['mengetahui'] = $dataLPPA['mengetahui'];
            $this->Data['uraian'] = $dataLPPA['uraian'];
            $this->Data['lppa_file_exist'] = $dataLPPA['lppa_file'];
            $this->Data['tanggal'] =  date('Y-m-d', strtotime($dataLPPA['tgl_lppa'])); 
            $this->Data['nomor_lppa'] = $dataLPPA['no_lppa'];
        }
        

        $this->Data['KOMP'] = $this->mObjRef->GetDetailBelanjaFpaById($this->Data['realisasi_id'],$this->Data['lppa_id']); 

        if(isset($msg[0][0])) {
          $this->Data = $msg[0][0];
        }


                $arr_tahun_anggaran = $this->mObj->GetComboTahunAnggaran();
                
                Messenger::Instance()->SendToComponent(
                                        'combobox', 
                                        'Combobox', 
                                        'view', 
                                        'html', 
                                        'tahun_anggaran', 
                                        array(
                                                'tahun_anggaran_id', 
                                                $arr_tahun_anggaran, 
                                                $this->Data['tahun_anggaran_id'], '-', 
                                                ' style="width:200px;" id="tahun_anggaran" onchange="changeTa()"'), 
                                        Messenger::CurrentRequest);


        // combobox tanggal
        Messenger::Instance()->SendToComponent(
            'tanggal', 'Tanggal', 'view', 'html', 'tanggal', 
            array( 
                $this->Data['tanggal'],
                $minYear,
                $maxYear
            ), Messenger::CurrentRequest
        );

        $return['decDataId'] = $idDec;
        $return['total_sub_unit'] = $userUnitKerja->GetTotalSubUnitKerja($unit['unit_kerja_id']);      
        //$this->mObjRef->SetDebugOn();
        // echo '<pre>';
        // print_r($dataDetailBelanjaFpa);
        // print_r($this->Data['KOMP']);
        // echo '</pre>';
        $return['detail_belanja_fpa']['data']  = json_encode($this->Data['KOMP']);
        //print_r($return['detail_belanja_fpa']);
        //echo '</pre>';
        return $return;
   }

   function ParseTemplate($data = null)
   {
      
        $protocol            = (int)$_SERVER['HTTP_PORT'] === 443 ? 'https://' : 'http://';
        $serverRoot          = realpath($_SERVER['DOCUMENT_ROOT']);
        $baseAddress         = $protocol.$_SERVER['HTTP_HOST'];
        $documentRoot        = GTFWConfiguration::GetValue('application', 'docroot');
        $documentLppaPath    = realpath($documentRoot.'/document/lppa/');

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
        $this->mrTemplate->AddVar('content', 'UNIT_KERJA_ID_OLD', $this->Data['unit_kerja_id_old']);
        $this->mrTemplate->AddVar('content', 'UNIT_KERJA_NAMA', $this->Data['unit_kerja_nama']);
        $this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN_ID', $this->Data['tahun_anggaran_id']);
        $this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN_ID_OLD', $this->Data['tahun_anggaran_id_old']);
        $this->mrTemplate->AddVar('content', 'REALISASI_ID_EXIST', $this->Data['realisasi_id_exist']);
        $this->mrTemplate->AddVar('content', 'REALISASI_ID', $this->Data['realisasi_id']);
        $this->mrTemplate->AddVar('content', 'REALISASI_NO', $this->Data['realisasi_no']);
        $this->mrTemplate->AddVar('content', 'PENANGGUNG_JAWAB', $this->Data['penanggung_jawab']);
        $this->mrTemplate->AddVar('content', 'MENGETAHUI', $this->Data['mengetahui']);        
        $this->mrTemplate->AddVar('content', 'URAIAN', $this->Data['uraian']);        
        $this->mrTemplate->AddVar('content', 'LPPA_EXIST', $this->Data['lppa_file_exist']);
        $this->mrTemplate->AddVar('cek_unitkerja_parent', 'UNIT_KERJA_NAMA',$this->Data['unit_kerja_nama']);
        $this->mrTemplate->AddVar('cek_unitkerja_parent','URL_POPUP_UNIT_KERJA', $urlPopupUnitKerja);
                                                                            
        $this->mrTemplate->AddVar('content', 'URL_POPUP_REALISASI', $urlPopupRealisasi);
        
        if(!empty($this->Data['lppa_file_exist'])) {
            $lppaFile            = $documentLppaPath.'/'.$this->Data['lppa_file_exist'];
            $documentDownload    = str_replace($serverRoot, $baseAddress, $lppaFile);
            $this->mrTemplate->AddVar('content', 'DATA_FILE', $this->Data['lppa_file_exist']);
            $this->mrTemplate->AddVar('content', 'DATA_FILE_DOWNLOAD', $documentDownload);
        }
        
        // no lppa
        if($this->Data['lppa_id'] =='') {
            $this->mrTemplate->SetAttribute('no_lppa_label', 'visibility', 'hidden');
            $this->mrTemplate->SetAttribute('no_lppa_label_auto', 'visibility', 'visible');
        } else {
            $this->mrTemplate->SetAttribute('no_lppa_label', 'visibility', 'visible');
            $this->mrTemplate->SetAttribute('no_lppa_label_auto', 'visibility', 'hidden');
            $this->mrTemplate->AddVar('no_lppa_label', 'NOMOR_LPPA',$this->Data['nomor_lppa']);
        }
        if($message){
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
            $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
        }
   }
}

?>