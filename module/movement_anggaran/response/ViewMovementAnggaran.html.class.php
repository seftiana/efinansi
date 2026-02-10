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
    'module/movement_anggaran/business/MovementAnggaran.class.php';
    require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
    'module/user_unit_kerja/business/UserUnitKerja.class.php';

    class ViewMovementAnggaran extends HtmlResponse {
        public $data;
        public $unitKerja;
        public $obj;
        public $userUnitObj;
        public $movementObj;
        protected $user;
        
        function __construct()
        {
            $this->movementObj  = new MovementAnggaran;
            $this->userUnitObj  = new UserUnitKerja();
            $this->user         = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
        }
        
        function TemplateModule() 
        {
            $this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot').
            'module/movement_anggaran/template');
            $this->SetTemplateFile('view_movement_anggaran.html');
        }

        function ProcessRequest() 
        {
            $dataBas                = $this->movementObj->GetComboBas(); // data bas
            $tahunPeriodeArray      = $this->movementObj->GetTahunAnggaranAktif(); // data tahun periode
            // get unit kerja user login
            $userUnitKerja = $this->userUnitObj->GetUnitKerjaUser($this->user);
			$getTotalSubUnitKerja = $this->userUnitObj->GetTotalSubUnitKerja($userUnitKerja['unit_kerja_id']);
            //
            $msg			= Messenger::Instance()->Receive(__FILE__);
            $return['msg']	= $msg[0][1];
            $return['data']	= $msg[0][0];
            $return['css']	= $msg[0][2];
            $komponen_asal  = $return['data']['KOMP'];
            $komponen_tujuan  = $return['data']['KOMPTUJUAN'];
            /*
            $angka  = 0;
            if(isset($return['data']['KOMP'])){
                foreach($komponen_asal AS $key => $val){
                    $komp_asal[$angka]['id']            = $val['rp_id'];
                    $komp_asal[$angka]['nama_komponen'] = $val['namaKomponen'];
                    $kom_asal[$angka]['kode_komponen']  = $val['kodeKomponen'];
                    $kom_asal[$angka]['nominal']        = $val['nominal'];
                    
                   $angka+=1;
                }
            }
            
             
            
            $angka  = 0;
            if(isset($return['data']['KOMPTUJUAN'])){
                foreach($komponen_asal AS $key => $val){
                    $komp_tujuan[$angka]['id']            = $val['rp_id'];
                    $komp_tujuan[$angka]['nama_komponen'] = $val['namaKomponen'];
                    $kom_tujuan[$angka]['kode_komponen']  = $val['kodeKomponen'];
                    $kom_tujuan[$angka]['nominal']        = $val['nominal'];
                    
                   $angka+=1;
                }
            }*/
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
            $this->mrTemplate->AddVar('content', 'TAHUN_PERIODE', $data['tahun_anggaran']['name']);
            $this->mrTemplate->AddVar('content', 'TAHUN_PERIODE_ID', $data['tahun_anggaran']['id']);
            

            if($msg):
                $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
                $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $msg);
                $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $css_box);
            endif;
            $popup_mak      = Dispatcher::Instance()->GetUrl('movement_anggaran','PopupMak','view','html');
            $url_action     = Dispatcher::Instance()->GetUrl('movement_anggaran','Movement','do','html');
            $this->mrTemplate->AddVar('content','URL_ACTION',$url_action);
            $this->mrTemplate->AddVar('content','POPUP_MAK',$popup_mak);
            
            $url_kegiatan   = Dispatcher::Instance()->GetUrl('movement_anggaran','ListKegiatanAnggaran','view','html').'&unit_id='.$data['user_unit_kerja']['unit_kerja_id'];
            $url_kegiatan_tujuan   = Dispatcher::Instance()->GetUrl('movement_anggaran','ListKegiatanAnggaranTujuan','view','html').'&unit_id='.$data['user_unit_kerja']['unit_kerja_id'];
            $this->mrTemplate->AddVar('content','URL_KEGIATAN',$url_kegiatan);
            $this->mrTemplate->AddVar('content','URL_KEGIATAN_TUJUAN',$url_kegiatan_tujuan);
            
            //$unitKerja      = $this->unitKerja;
            //$this->mrTemplate->AddVar('content','UNIT_KERJA_ID',$unitKerja['unit_kerja_id']);
            //$this->mrTemplate->AddVar('content','UNIT_KERJA_NAMA',$unitKerja['unit_kerja_nama']);
            
            //popup unit kerja
            $popup_unitKerja    = Dispatcher::Instance()->GetUrl('movement_anggaran','PopupUnitKerja','view','html');
            $this->mrTemplate->AddVar('content','URL_POPUP_UNIT',$popup_unitKerja);
            /*
            if (empty($data['data'])) 
            {
                $this->mrTemplate->AddVar('data_grid_asal', 'DATA_EMPTY', 'YES');
            } 
            else 
            {
                $this->mrTemplate->AddVar('data_grid_asal', 'DATA_EMPTY', 'NO');

                $dataList   = $data['data'];

                for ($i=0; $i<sizeof($dataList); $i++) {
                    $no                     = $i+$data['start'];
                    $dataList[$i]['nomor']  = $no;

                    if ($no % 2 != 0)
                    {
                        $dataList[$i]['class_name'] = 'table-common-even';
                    }
                    else
                    {
                        $dataList[$i]['class_name'] = '';
                    }

                    $this->mrTemplate->AddVars('data_item_asal', $dataList[$i], '');
                    $this->mrTemplate->parseTemplate('data_item_asal', 'a');
                }
            }

            if (empty($data['data'])) 
            {
                $this->mrTemplate->AddVar('data_grid_tujuan', 'DATA_EMPTY', 'YES');
            } 
            else 
            {
                $this->mrTemplate->AddVar('data_grid_tujuan', 'DATA_EMPTY', 'NO');

                $dataList   = $data['data'];

                for ($i=0; $i<sizeof($dataList); $i++) {
                    $no                     = $i+$data['start'];
                    $dataList[$i]['nomor']  = $no;

                    if ($no % 2 != 0)
                    {
                        $dataList[$i]['class_name'] = 'table-common-even';
                    }
                    else
                    {
                        $dataList[$i]['class_name'] = '';
                    }

                    $this->mrTemplate->AddVars('data_item_tujuan', $dataList[$i], '');
                    $this->mrTemplate->parseTemplate('data_item_tujuan', 'a');
                }
            }
            */
        }
    }
?>