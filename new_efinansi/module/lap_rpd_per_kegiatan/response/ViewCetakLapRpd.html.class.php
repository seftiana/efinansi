<?php

/**
 * class ViewCetakLapRpd
 * @package lap_rpd_per_kegiatan
 * @subpackage response
 * @todo untuk menampilkan tampilan cetak data
 * @since mei 2012
 * @copyright 2012 Gamatechno Indonesia
 * @author noor hadi <noor.hadi@gamatechno.com>
 */
 
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/lap_rpd_per_kegiatan/business/AppLapRpdPerKegiatan.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewCetakLapRpd extends HtmlResponse {

	function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
            'module/lap_rpd_per_kegiatan/template');
      $this->SetTemplateFile('cetak_lap_rpd_per_kegiatan.html');
   }
   
   function TemplateBase() 
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
	  $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }
   
	function ProcessRequest() 
    {
  	     if(isset($_GET)) { //pasti dari form pencarian :p	  
	     if(is_object($_GET))
		    $v = $_GET->AsArray();
		 else
		    $v = $_GET;        
        }        
		$Obj = new AppLapRpd();
		$tahun_anggaran = Dispatcher::Instance()->Decrypt($v['tahun_anggaran']);
		$unitkerjaId = Dispatcher::Instance()->Decrypt($v['unitkerja']);
		//$operator_rule = Dispatcher::Instance()->Decrypt($_GET['operator_rule']);
		//$startRec = Dispatcher::Instance()->Decrypt($v['startRec']);
		//$itemViewed = Dispatcher::Instance()->Decrypt($v['itemViewed']);
		//$dataCetak = $Obj->GetDataCetak($tahun_anggaran, $unitkerjaId, $unitkerjaId);
		$dataCetak = $Obj->GetDataRpdCetak($tahun_anggaran,$unitkerjaId);
        $dataMak = $Obj->GetMak($tahun_anggaran,$unitkerjaId);
		$unitkerja = $Obj->GetUnitKerja($unitkerjaId);
		$tahunanggaran = $Obj->GetTahunAnggaranCetak($tahun_anggaran);

		$return['data'] = $dataCetak;
		$return['unitkerja'] = $unitkerja;
		$return['unitkerja_nama'] = $unitkerja['unit_kerja_nama'];
		$return['tahunanggaran'] = $tahunanggaran;
		$return['tahunanggaran_nama'] = $tahunanggaran['name'];
        $return['data_mak'] =$dataMak;
		return $return;
	}

	function ParseTemplate($data = NULL) {

		$this->mrTemplate->AddVar('content', 'TAHUN_PERIODE', $data['tahunanggaran_nama']);
		$this->mrTemplate->AddVar('content', 'UNIT_KERJA_LABEL', $data['unitkerja_nama']);
		
		if (empty($data['data'])) {
			$this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
		} else {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
         $dataGrid = $data['data'];

         $i=0;
         $x=0;
         
         
         $program_nomor=''; //inisialisasi program
         $kegiatan_nomor=''; //inisialisasi kegiatan
         $sub_keg_nomor=''; //inisialisasi subkegiatan
		 $mak='';
         $no=1;

         for ($i=0; $i<sizeof($dataGrid);) {
         //=========strat setting tampilan=======================
            
              $view_program_nomor = $dataGrid[$i]['program_nomor'];
              $view_kegiatan_nomor = $dataGrid[$i]['kegiatan_nomor'];

            //komponen
            if(($program_nomor == $dataGrid[$i]['program_id']) &&   
                    ($kegiatan_nomor == $dataGrid[$i]['subprogram_id']) && 
                        ($sub_keg_nomor == $dataGrid[$i]['subkegiatan_id'])&& ($mak == $dataGrid[$i]['mak_id'])) {
               $dataGrid[$i]['class_name']='';
               $send[$x]['kode'] = '';
               $send[$x]['nama'] = "&nbsp;-&nbsp;".$dataGrid[$i]['komponen_nama'];
               $send[$x]['satuan_setuju'] = $dataGrid[$i]['satuan_setuju'].' '.$dataGrid[$i]['nama_satuan'];
               $send[$x]['nominal_setuju'] = number_format($dataGrid[$i]['nominal_setuju'], 0, ',', '.');
               $send[$x]['jumlah_setuju'] = $dataGrid[$i]['jumlah_setuju'];
               $send[$x]['nomor'] = $dataGrid[$i]['nomor'];
               $send[$x]['jenis'] = "komponen";
               $send[$x]['mak_id'] = $dataGrid[$i]['mak_id'];
               
               $send[$x]['unit_subunit'] = $dataGrid[$i]['unit_subunit'];
               /**
               if($i == 0){
						$send[$x]['unit_subunit'] = $dataGrid[$i]['unit_subunit'];	
					} else{
						$send[$x]['unit_subunit'] =  
							((($dataGrid[$i - 1]['unit_id'].$dataGrid[$i - 1]['mak_id']) != 
                                    ($dataGrid[$i]['unit_id'].$dataGrid[$i]['mak_id'])) ? 
										$dataGrid[$i]['unit_subunit']: ''); 
						
					}
                */
               $i++;

            //program
            } elseif($program_nomor != $dataGrid[$i]['program_id']) {

               $program_nomor = $dataGrid[$i]['program_id'];

               $send[$x]['class_name']='table-common-even1';
               $send[$x]['kode']='<b>'.$view_program_nomor.'</b>';
               $dataGrid[$i]['program_nama_rkakl']=
                        empty($dataGrid[$i]['program_nama_rkakl'])?'-':$dataGrid[$i]['program_nama_rkakl'];
               $send[$x]['nama']='<b>'.$dataGrid[$i]['program_nama'].'<br />[ '.
                            $dataGrid[$i]['program_nama_rkakl'].' ]</b>';
               $send[$x]['nomor']='<b>'.$no.'</b>';
               $send[$x]['jenis'] = "program";

               $no++;

            //kegiatan
            } elseif($kegiatan_nomor != $dataGrid[$i]['subprogram_id']) {
               $kegiatan_nomor = $dataGrid[$i]['subprogram_id'];
               $jenis_keg_id=$dataGrid[$i]['jenis_keg_id'];
               $send[$x]['class_name']='table-common-even2';
               $send[$x]['kode']='<b>'.$view_kegiatan_nomor.'</b>';
               $dataGrid[$i]['kegiatan_nama_rkakl']=
                            empty($dataGrid[$i]['kegiatan_nama_rkakl'])?'-':$dataGrid[$i]['kegiatan_nama_rkakl'];
               $send[$x]['nama']='<b>'.$dataGrid[$i]['kegiatan_nama'].'<br />[ '.
                        $dataGrid[$i]['kegiatan_nama_rkakl'].' ]</b>';
               $send[$x]['jenis'] = "kegiatan";

            //subkegiatan
            } elseif($sub_keg_nomor != $dataGrid[$i]['subkegiatan_id']) {

               //===========start pengaturan tampilan kode;=======================
               $jenisKegId=$dataGrid[$i]['jenis_keg_id'];
               $dataGrid[$i]['subkegiatan_nomor'] = $dataGrid[$i]['subkegiatan_nomor'];
               //===========end pengaturan tampilan kode;=======================

               $sub_keg_nomor = $dataGrid[$i]['subkegiatan_id'];
               $jenis_keg_id=$dataGrid[$i]['jenis_keg_id'];
               $send[$x]['class_name']='table-common-even2';
               $send[$x]['kode']='<i>'.$dataGrid[$i]['subkegiatan_nomor'].'</i>';
               $dataGrid[$i]['subkegiatan_nama_rkakl']=
                    empty($dataGrid[$i]['subkegiatan_nama_rkakl'])?'-':$dataGrid[$i]['subkegiatan_nama_rkakl'];
               $send[$x]['nama']='<i>'.$dataGrid[$i]['subkegiatan_nama'].'<br />[ '.
                    $dataGrid[$i]['subkegiatan_nama_rkakl'].' ]</i>';
               $send[$x]['jenis'] = "subkegiatan";

            } elseif ($mak != $dataGrid[$i]['mak_id']) {
				$mak = $dataGrid[$i]['mak_id'];
				$send[$x]['sts'] = 'mak';	
				/*
				$makkode = $dataGrid[$i]['makKode'];
				$makNama = $dataGrid[$i]['makNama'];
                
				if(($dataGrid[$i]['makKode'] == "") && ($dataGrid[$i]['makNama'] == "")) {
				    $send[$x]['kode'] = "NULL";
					$send[$x]['nama'] = "NULL";
					$send[$x]['jumlah_setuju'] = "NULL";
				} else {
				    $send[$x]['kode'] = "<u><i>".$makkode."</i></u>";
					$send[$x]['nama'] = "<u><i>".$makNama."</i></u>";
					$send[$x]['jumlah_setuju'] = "NULL";
				}
                
                $send[$x]['mak_id'] =  $dataGrid[$i]['mak_id'];                
                */
			}
            $x++;

         }

			$i = sizeof($send)-1;
			$nominal_usulan=0;
			while($i >= 0) {
				if($send[$i]['jenis'] == 'komponen') {
					$jumlah_setuju += $send[$i]['jumlah_setuju'];
					$nominal_setuju += $send[$i]['nominal_setuju'];
				}
				if($send[$i]['jenis'] == 'subkegiatan') {
					$send[$i]['jumlah_setuju'] = $jumlah_setuju;
					$jumlah_setuju_sk += $jumlah_setuju;
					$jumlah_setuju=0;
				}
				if($send[$i]['jenis'] == 'kegiatan') {
					$send[$i]['jumlah_setuju'] = $jumlah_setuju_sk;
					$jumlah_setuju_program += $jumlah_setuju_sk;
					$jumlah_setuju=0;
				}
				if($send[$i]['jenis'] == 'program') {
					$send[$i]['jumlah_setuju'] = $jumlah_setuju_program;
					$jumlah_setuju_program = 0;
				}
				$i--;
			}

            $header = $data['data_mak'];
            $max_header = sizeof($header); 
            /**
             * membuat header
             */           
            if($max_header > 0){
            $this->mrTemplate->AddVar('content', 'MAX_HEADER', ($max_header));
            for($n=0;$n < $max_header;$n++) {
				 $this->mrTemplate->AddVars('data_h_kode_item', $header[$n], 'HK_');
                 $this->mrTemplate->AddVars('data_h_nama_item', $header[$n], 'HN_');
                
                 $this->mrTemplate->parseTemplate('data_h_kode_item', 'a');
				 $this->mrTemplate->parseTemplate('data_h_nama_item', 'a');
			}
            }
            /**
             * end
             */
			for($j=0;$j<sizeof($send);$j++) {
				if($send[$j]['sts'] == 'mak'){
						continue;
				}
               if($send[$j]['jumlah_setuju'] == "NULL") {
					$send[$j]['jumlah_setuju'] = '';
			   } else {
                 if($send[$j]['jenis'] == 'program' || $send[$j]['jenis'] == 'kegiatan'){
                   $send[$j]['jumlah_setuju'] = '<b>'.number_format($send[$j]['jumlah_setuju'], 0, ',', '.').'</b>';
                 }elseif($send[$j]['jenis'] == 'subkegiatan'){
                   $send[$j]['jumlah_setuju'] = '<i>'.number_format($send[$j]['jumlah_setuju'], 0, ',', '.').'</i>';
                 }else{
                   $send[$j]['jumlah_setuju'] = number_format($send[$j]['jumlah_setuju'], 0, ',', '.');
                 }
			   }
                if($max_header > 0){
                    for($f=0;$f <sizeof($header);$f++) {	
                                              
                        if( $send[$j]['jenis'] == 'komponen'){
                            if($send[$j]['mak_id'] == $header[$f]['mak_id']){
                                $send[$j]['colom'].='<td align="right">'.$send[$j]['jumlah_setuju'].'</td>';
                            } else {
                                $send[$j]['colom'].='<td></td>';
                            }
                        } else {
                            $send[$j]['colom'] .= '<td></td>';
                        }
                 
                    }
                    
                }               
				 $this->mrTemplate->AddVars('data_item', $send[$j], 'DATA_');
				 $this->mrTemplate->parseTemplate('data_item', 'a');
			}
            
                /**
                 * total per mak
                 */
                if($max_header > 0){
                    $jml_mak='';
                    for($v=0;$v <sizeof($header);$v++) {
                        $jml_mak .= '<td align="right"><b>'.
                                            number_format($header[$v]['jumlah_per_mak'],0,',','.').'</b></td>';
                    }
                    $this->mrTemplate->AddVar('data_grid', 'JUMLAH_PER_MAK', $jml_mak);
                }            
		}
	}
}
