<?php

/**
* @package ViewMovementAnggaran
* @copyright Copyright (c) PT Gamatechno Indonesia
* @Analyzed By Dyan Galih <galih@gamatechno.com>
* @author Dyan Galih <galih@gamatechno.com>
* @version 0.1
* @startDate 2011-01-01
* @lastUpdate 2011-01-01
* @description View Movement Anggaran
*/

    require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/history_apbnp/business/HistoryApbnp.class.php';
    
    require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
    'module/user_unit_kerja/business/UserUnitKerja.class.php';

    class ViewUpdateHistoryApbnp extends HtmlResponse 
    {
        public $data;
        public $unitKerja;
        public $obj;
        public $userUnitObj;
        public $movementObj;
        protected $user;
        
        function __construct()
        {
            $this->movementObj  = new HistoryApbnp;
            $this->userUnitObj  = new UserUnitKerja();
            $this->user         = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
        }
        
        function TemplateModule() 
        {
            $this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot').
            'module/history_apbnp/template');
            $this->SetTemplateFile('view_update_history_apbnp.html');
        }

        function ProcessRequest() 
        {
            $dataBas                = $this->movementObj->GetComboBas(); // data bas
            $tahunPeriodeArray      = $this->movementObj->GetTahunAnggaranAktif(); // data tahun periode
            if(is_object($_GET)){
                $_GET = $_GET->AsArray();
            }
            
            $historyId = Dispatcher::Instance()->Decrypt($_GET['id']);
            
            // get unit kerja user login
            $userUnitKerja = $this->userUnitObj->GetUnitKerjaUser($this->user);
			$getTotalSubUnitKerja = $this->userUnitObj->GetTotalSubUnitKerja($userUnitKerja['unit_kerja_id']);
            $dataMv =  $this->movementObj->DetailApbnp($historyId);
            
            $komp_asal      = array();
            $komp_tujuan    = array();
            if(!empty($dataMv)) {
                $iAsal = 0;
                $iTujuan = 0;
                foreach($dataMv as $key => $value){
                    if($value['type_movement'] == 'asal'){
                        $komp_asal[$iAsal]['rp_id'] = $value['rp_id'];
                        $komp_asal[$iAsal]['kegiatan_detail_id'] = $value['kegiatan_detail_id'];
                        $komp_asal[$iAsal]['kodeKomponen'] = $value['kode_komponen'];
                        $komp_asal[$iAsal]['namaKomponen'] = $value['nama_komponen'];
                        $komp_asal[$iAsal]['nominal'] = $value['nilai_komponen_movement'];
                        $komp_asal[$iAsal]['nominal_hid'] = $value['nilai_komponen_semula'];
                        $iAsal++;
                    } else {
                        $komp_tujuan[$iTujuan]['rp_id'] = $value['rp_id'];
                        $komp_tujuan[$iTujuan]['kegiatan_detail_id'] = $value['kegiatan_detail_id'];
                        $komp_tujuan[$iTujuan]['kodeKomponen'] = $value['kode_komponen'];
                        $komp_tujuan[$iTujuan]['namaKomponen'] = $value['nama_komponen'];
                        $komp_tujuan[$iTujuan]['nominal'] = $value['nilai_komponen_movement'];
                        $komp_tujuan[$iTujuan]['nominal_hid'] = $value['nilai_komponen_semula'];                        
                        $iTujuan++;
                    }
                }
            }
            //
            $msg			= Messenger::Instance()->Receive(__FILE__);
            $return['msg']  = $msg[0][1];            
            $return['css']  = $msg[0][2];
            if(isset($msg[0][0])){
                $return['data']     = $msg[0][0];
                $komponen_asal      = $msg[0][0]['KOMP'];
                $komponen_tujuan    = $msg[0][0]['KOMPTUJUAN'];      
            } else {
                
                $dataHistory['historyId']           = $dataMv[0]['id'];
                $dataHistory['tahunAnggaranId']     = $dataMv[0]['tahun_anggaran_id'];
                $dataHistory['tahunAnggaranNama']   = $dataMv[0]['tahun_anggaran_nama'];
                //asal                            
                $dataHistory['unitKerjaId']     = $dataMv[0]['unit_kerja_id_asal'];
                $dataHistory['unitKerjaKode']   = $dataMv[0]['unit_kerja_kode_asal'];
                $dataHistory['unitKerjaNama']   = $dataMv[0]['unit_kerja_nama_asal'];
                $dataHistory['kegrefId']        = $dataMv[0]['keg_asal'];
                $dataHistory['kegrefNomor']     = $dataMv[0]['nomor_kegiatan_asal'];
                $dataHistory['kegrefNama']      = $dataMv[0]['kegiatan_asal'];
                $dataHistory['kegDetId']        = $komp_asal[0]['kegiatan_detail_id'];
                //tujuan
                $dataHistory['unitKerjaIdTujuan']   = $dataMv[0]['unit_kerja_id_tujuan'];
                $dataHistory['unitKerjaKodeTujuan'] = $dataMv[0]['unit_kerja_kode_tujuan'];
                $dataHistory['unitKerjaNamaTujuan'] = $dataMv[0]['unit_kerja_nama_tujuan'];
                $dataHistory['kegrefIdTujuan']      = $dataMv[0]['keg_tujuan'];
                $dataHistory['kegrefNomorTujuan']   = $dataMv[0]['nomor_kegiatan_tujuan'];
                $dataHistory['kegrefNamaTujuan']    = $dataMv[0]['kegiatan_tujuan'];
                $dataHistory['kegDetIdTujuan']      = $komp_tujuan[0]['kegiatan_detail_id'];
                $return['data']     = $dataHistory;
                $komponen_asal      = $komp_asal;
                $komponen_tujuan    = $komp_tujuan;
            }
            
            
            $return['komponen_asal']   = json_encode($komponen_asal);
            $return['komponen_tujuan'] = json_encode($komponen_tujuan);
            $return['tahun_anggaran']  = $tahunPeriodeArray;
			$return['user_unit_kerja']   = $userUnitKerja; 
			$return['total_sub_unit_kerja'] = $getTotalSubUnitKerja;
            return $return;
        }

        function ParseTemplate($data = NULL) {
            $msg        = $data['msg'];
            $css_box    = $data['css'];
            $post       = $data['data'];
            
			
			if((int) $data['total_sub_unit_kerja'] > 0){
				$this->mrTemplate->AddVar('content','DISPLAY_UNIT','');
			} else {
				$this->mrTemplate->AddVar('content','DISPLAY_UNIT','style="display:none;"');
			}
			
          	$this->mrTemplate->AddVar('content','USER_UNIT_KERJA_ID',$data['user_unit_kerja']['unit_kerja_id']);
			$this->mrTemplate->AddVar('content','USER_UNIT_KERJA_KODE',$data['user_unit_kerja']['unit_kerja_kode']);
			$this->mrTemplate->AddVar('content','USER_UNIT_KERJA_NAMA',$data['user_unit_kerja']['unit_kerja_nama']);
            //$this->mrTemplate->AddVar('content','MAK_ID',$post['mak_id']);
            //$this->mrTemplate->AddVar('content','MAK_NAMA',$post['mak_nama']);
            //print_r($post);
            $this->mrTemplate->AddVar('content','HISTORY_ID',$post['historyId']);
            
            $this->mrTemplate->AddVar('content','UNIT_KERJA_ID',$post['unitKerjaId']);
			$this->mrTemplate->AddVar('content','UNIT_KERJA_NAMA',$post['unitKerjaNama']);
			$this->mrTemplate->AddVar('content','KEG_DET_ID',$post['kegDetId']);
            $this->mrTemplate->AddVar('content','KEG_REF_ID',$post['kegrefId']);
            $this->mrTemplate->AddVar('content','KEG_REF_NOMOR',$post['kegrefNomor']);
            $this->mrTemplate->AddVar('content','KEG_REF_NAMA',$post['kegrefNama']);
			
            $this->mrTemplate->AddVar('content','UNIT_KERJA_ID_TUJUAN',$post['unitKerjaIdTujuan']);
			$this->mrTemplate->AddVar('content','UNIT_KERJA_NAMA_TUJUAN',$post['unitKerjaNamaTujuan']);
			$this->mrTemplate->AddVar('content','KEG_DET_ID_TUJUAN',$post['kegDetIdTujuan']);
            $this->mrTemplate->AddVar('content','KEG_REF_ID_TUJUAN',$post['kegrefIdTujuan']);
            $this->mrTemplate->AddVar('content','KEG_REF_NOMOR_TUJUAN',$post['kegrefNomorTujuan']);
            $this->mrTemplate->AddVar('content','KEG_REF_NAMA_TUJUAN',$post['kegrefNamaTujuan']);

            $this->mrTemplate->addVar('content', 'KOMPONEN_ASAL', $data['komponen_asal']);
            $this->mrTemplate->addVar('content', 'KOMPONEN_TUJUAN', $data['komponen_tujuan']);
                                                                            
            # send tahun periode aktif to template
            $this->mrTemplate->AddVar('content', 'TAHUN_PERIODE', $post['tahunAnggaranNama']);
            $this->mrTemplate->AddVar('content', 'TAHUN_PERIODE_ID',$post['tahunAnggaranId']);
            

            if($msg):
                $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
                $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $msg);
                $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $css_box);
            endif;
            $popup_mak      = Dispatcher::Instance()->GetUrl('history_apbnp','PopupMak','view','html');
            $url_action     = Dispatcher::Instance()->GetUrl('history_apbnp','UpdateHistoryApbnp','do','json');
            
            $this->mrTemplate->AddVar('content','URL_ACTION',$url_action);
            $this->mrTemplate->AddVar('content','POPUP_MAK',$popup_mak);
            
            $url_kegiatan   = Dispatcher::Instance()->GetUrl('history_apbnp','ListKegiatanAnggaran','view','html').'&unit_id='.$data['user_unit_kerja']['unit_kerja_id'];
            $url_kegiatan_tujuan   = Dispatcher::Instance()->GetUrl('history_apbnp','ListKegiatanAnggaranTujuan','view','html').'&unit_id='.$data['user_unit_kerja']['unit_kerja_id'];
            $this->mrTemplate->AddVar('content','URL_KEGIATAN',$url_kegiatan);
            $this->mrTemplate->AddVar('content','URL_KEGIATAN_TUJUAN',$url_kegiatan_tujuan);
        }
    }
?>