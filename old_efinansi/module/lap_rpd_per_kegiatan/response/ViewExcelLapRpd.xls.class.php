<?php

/**
 * class ViewExcelLapRpd
 * @package lap_rpd_per_kegiatan
 * @subpackage response
 * @todo untuk menampilkan tampilan cetak data format excel
 * @since mei 2012
 * @copyright 2012 Gamatechno Indonesia
 * @author noor hadi <noor.hadi@gamatechno.com>
 */

require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
        'module/lap_rpd_per_kegiatan/business/AppLapRpdPerKegiatan.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
        'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewExcelLapRpd extends XlsResponse
{

    var $mWorksheets = array('Data');

    function GetFileName()
    {

      return 'LapRpdPerKegiatan.xls';
    }

    function L($indexLangName = '')
    {
   		$lang = GTFWConfiguration::GetValue('language',$indexLangName);
   		if(!empty($lang)){
   			return $lang;
   		}
   		return '';
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
		//$operator_rule = Dispatcher::Instance()->Decrypt($v['operator_rule']);
		//$startRec = Dispatcher::Instance()->Decrypt($v['startRec']);
		//$itemViewed = Dispatcher::Instance()->Decrypt($v['itemViewed']);
		//$data = $Obj->GetDataCetak($tahun_anggaran, $unitkerjaId, $unitkerjaId);
		$data = $Obj->GetDataRpdCetak($tahun_anggaran,$unitkerjaId);
        $dataMak = $Obj->GetMak($tahun_anggaran,$unitkerjaId);
		$unitkerja = $Obj->GetUnitKerja($unitkerjaId);
		$tahunanggaran = $Obj->GetTahunAnggaranCetak($tahun_anggaran);

		$unitkerja_nama = $unitkerja['unit_kerja_nama'];
		$tahunanggaran_nama = $tahunanggaran['name'];

		if (empty($data)) {
			$this->mWorksheets['Data']->write(0, 0, $this->L('data_kosong'));
		} else {
            $fTitle = $this->mrWorkbook->add_format();
	        $fTitle->set_bold();
            $fTitle->set_size(12);
            $fTitle->set_align('vcenter');

		    $formatHeader = $this->mrWorkbook->add_format();
            $formatHeader->set_border(1);
            $formatHeader->set_bold();
            $formatHeader->set_size(11);
            $formatHeader->set_align('center');
            $formatHeader->set_align('vcenter');
            $formatHeader->set_text_wrap();

            $formatProgram = $this->mrWorkbook->add_format();
            $formatProgram->set_border(1);
            $formatProgram->set_bold();
            $formatProgram->set_size(11);
            $formatProgram->set_align('left');
            $formatProgram->set_align('vcenter');
            $formatProgram->set_text_wrap();

            $formatCurrencyProgram = $this->mrWorkbook->add_format();
            $formatCurrencyProgram->set_border(1);
            $formatCurrencyProgram->set_bold();
            $formatCurrencyProgram->set_size(11);
            $formatCurrencyProgram->set_align('right');
            $formatCurrencyProgram->set_align('vcenter');

            $formatKegiatan = $this->mrWorkbook->add_format();
            $formatKegiatan->set_border(1);
            $formatKegiatan->set_bold();
            $formatKegiatan->set_size(10);
            $formatKegiatan->set_align('left');
            $formatKegiatan->set_align('vcenter');
            $formatKegiatan->set_text_wrap();

            $formatCurrencyKegiatan = $this->mrWorkbook->add_format();
            $formatCurrencyKegiatan->set_border(1);
            $formatCurrencyKegiatan->set_bold();
            $formatCurrencyKegiatan->set_italic();
            $formatCurrencyKegiatan->set_size(10);
            $formatCurrencyKegiatan->set_align('right');
            $formatCurrencyKegiatan->set_align('vcenter');

            $formatSubKegiatan = $this->mrWorkbook->add_format();
            $formatSubKegiatan->set_border(1);
            $formatSubKegiatan->set_italic();
            $formatSubKegiatan->set_size(10);
            $formatSubKegiatan->set_align('left');
            $formatSubKegiatan->set_align('vcenter');
            $formatSubKegiatan->set_text_wrap();

            $formatCurrencySubKegiatan = $this->mrWorkbook->add_format();
            $formatCurrencySubKegiatan->set_border(1);
            $formatCurrencySubKegiatan->set_italic();
            $formatCurrencySubKegiatan->set_size(10);
            $formatCurrencySubKegiatan->set_align('right');
            $formatCurrencySubKegiatan->set_align('vcenter');

            $formatSubKegiatan2 = $this->mrWorkbook->add_format();
            $formatSubKegiatan2->set_border(1);
            $formatSubKegiatan2->set_italic();
            $formatSubKegiatan2->set_underline(1);
            $formatSubKegiatan2->set_size(10);
            $formatSubKegiatan2->set_align('left');
            $formatSubKegiatan2->set_align('vcenter');
            $formatSubKegiatan2->set_text_wrap();

            $format = $this->mrWorkbook->add_format();
            $format->set_border(1);
            $format->set_align('left');
            $format->set_align('vcenter');
            $format->set_text_wrap();


            $formatCurrency = $this->mrWorkbook->add_format();
            $formatCurrency->set_border(1);
            $formatCurrency->set_align('right');
            $formatCurrency->set_align('vcenter');


		   $this->mWorksheets['Data']->write(0, 0, $this->L('lap_rincian_penggunaan_dana_per_kegiatan'), $fTitle);
		   $this->mWorksheets['Data']->write(2, 0,  $this->L('tahun_periode').' : '.$tahunanggaran_nama);
		   $this->mWorksheets['Data']->write(3, 0, $this->L('unit').' / '.$this->L('sub_unit').' : '.
                                                        $unitkerja_nama);

        $dataGrid = $data;
		//print_r($dataGrid); exit();
         $i=0;
         $x=0;

         $program_nomor=''; //inisialisasi program
         $kegiatan_nomor=''; //inisialisasi kegiatan
         $sub_keg_nomor=''; //inisialisasi subkegiatan
		 $mak='';
         $no=1;
		//print_r($dataGrid); exit();
         for ($i=0; $i<sizeof($dataGrid);) {
         //=========strat setting tampilan=======================

            $view_program_nomor = $dataGrid[$i]['program_nomor'];
            $view_kegiatan_nomor = $dataGrid[$i]['kegiatan_nomor'];

            //komponen
            if(($program_nomor == $dataGrid[$i]['program_id']) &&
                    ($kegiatan_nomor == $dataGrid[$i]['subprogram_id']) &&
                        ($sub_keg_nomor == $dataGrid[$i]['subkegiatan_id']) &&
                            ($mak == $dataGrid[$i]['mak_id'])) {

               $send[$x]['kode'] = '';
    		   $send[$x]['nama'] = " - ".$dataGrid[$i]['komponen_nama'];
               $send[$x]['satuan_setuju'] = $dataGrid[$i]['satuan_setuju'].' '.$dataGrid[$i]['nama_satuan'];
               $send[$x]['nominal_setuju'] = $dataGrid[$i]['nominal_setuju'];
               $send[$x]['jumlah_setuju'] = $dataGrid[$i]['jumlah_setuju'];
               $send[$x]['nomor'] = $dataGrid[$i]['nomor'];
               $send[$x]['jenis'] = "komponen";
               $send[$x]['mak_id'] = $dataGrid[$i]['mak_id'];
               $send[$x]['format'] =$format;
               $send[$x]['format_curr'] = $formatCurrency;
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

               $send[$x]['kode']=$view_program_nomor;
               $dataGrid[$i]['program_nama_rkakl']=
                    empty($dataGrid[$i]['program_nama_rkakl'])?'-':$dataGrid[$i]['program_nama_rkakl'];
               $send[$x]['nama']=$dataGrid[$i]['program_nama'].'[ '.$dataGrid[$i]['program_nama_rkakl'].' ]';
               $send[$x]['nomor']=$no;
               $send[$x]['jenis'] = "program";
               $send[$x]['format'] =$formatProgram;
               $send[$x]['format_curr'] = $formatCurrencyProgram;

               $no++;

            //kegiatan
            } elseif($kegiatan_nomor != $dataGrid[$i]['subprogram_id']) {
               $kegiatan_nomor = $dataGrid[$i]['subprogram_id'];

               $jenis_keg_id=$dataGrid[$i]['jenis_keg_id'];
               $send[$x]['kode']=$view_kegiatan_nomor;
               $dataGrid[$i]['kegiatan_nama_rkakl']=
                    empty($dataGrid[$i]['kegiatan_nama_rkakl'])?'-':$dataGrid[$i]['kegiatan_nama_rkakl'];
               $send[$x]['nama']=$dataGrid[$i]['kegiatan_nama'].'[ '.$dataGrid[$i]['kegiatan_nama_rkakl'].' ]';
               $send[$x]['jenis'] = "kegiatan";

               $send[$x]['format'] =$formatKegiatan;
               $send[$x]['format_curr'] = $formatCurrencyKegiatan;

            //subkegiatan
            } elseif($sub_keg_nomor != $dataGrid[$i]['subkegiatan_id']) {

               //===========start pengaturan tampilan kode;=======================
               $jenisKegId=$dataGrid[$i]['jenis_keg_id'];

               $dataGrid[$i]['subkegiatan_nomor'] = $dataGrid[$i]['subkegiatan_nomor'];

               //===========end pengaturan tampilan kode;=======================

               $sub_keg_nomor = $dataGrid[$i]['subkegiatan_id'];
               $jenis_keg_id=$dataGrid[$i]['jenis_keg_id'];
               $send[$x]['kode']=$dataGrid[$i]['subkegiatan_nomor'];
               $dataGrid[$i]['subkegiatan_nama_rkakl']=
                    empty($dataGrid[$i]['subkegiatan_nama_rkakl'])?'-':$dataGrid[$i]['subkegiatan_nama_rkakl'];
               $send[$x]['nama']=$dataGrid[$i]['subkegiatan_nama'].
                                '[ '.$dataGrid[$i]['subkegiatan_nama_rkakl'].' ]';
               $send[$x]['jenis'] = "subkegiatan";
               $send[$x]['format'] =$formatSubKegiatan;
               $send[$x]['format_curr'] = $formatCurrencySubKegiatan;

            }elseif ($mak != $dataGrid[$i]['mak_id']) {
				$mak = $dataGrid[$i]['mak_id'];
				$send[$x]['sts'] = 'mak';
				/*
				$makkode = $dataGrid[$i]['makKode'];
                $makNama = $dataGrid[$i]['makNama'];

                $send[$x]['jenis'] = 'header_mak';
                $send[$x]['mak_id'] =  $dataGrid[$i]['mak_id'];
				if(($dataGrid[$i]['makKode'] == "") && ($dataGrid[$i]['makNama'] == "")) {
				    $send[$x]['kode'] = "NULL";
					$send[$x]['nama'] = "NULL";
					$send[$x]['jumlah_setuju'] = "NULL";
				} else {
				    $send[$x]['kode'] = $makkode;
					$send[$x]['nama'] = $makNama;
					$send[$x]['jumlah_setuju'] = "NULL";
				}
                $send[$x]['format'] =$formatSubKegiatan2;
                $send[$x]['format_curr'] = $formatCurrencySubKegiatan;
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

		   $num=5;
		   $this->mWorksheets['Data']->merge_cells(5,0,7,0);
		   $this->mWorksheets['Data']->merge_cells(5,1,7,1);
		   $this->mWorksheets['Data']->merge_cells(5,2,7,2);
		   $this->mWorksheets['Data']->merge_cells(5,3,7,3);
           $this->mWorksheets['Data']->merge_cells(5,4,7,4);
		   $this->mWorksheets['Data']->merge_cells(5,5,7,5);
           $this->mWorksheets['Data']->set_column(0,0,7);
           $this->mWorksheets['Data']->write($num, 0, $this->L('no'), $formatHeader);
           $this->mWorksheets['Data']->set_column(1,1,15);
           $this->mWorksheets['Data']->write($num, 1,  $this->L('kode'), $formatHeader);
           $this->mWorksheets['Data']->set_column(2,2,55);
           $this->mWorksheets['Data']->write($num, 2, $this->L('label_rincian'), $formatHeader);
           $this->mWorksheets['Data']->set_column(3,3,30);
		   $this->mWorksheets['Data']->write($num, 3, $this->L('unit').' / '.$this->L('sub_unit'),
                                                        $formatHeader);
           $this->mWorksheets['Data']->set_column(4,4,10);
           $this->mWorksheets['Data']->write($num, 4, $this->L('volume'), $formatHeader);
           $this->mWorksheets['Data']->set_column(5,5,20);
           $this->mWorksheets['Data']->write($num, 5, $this->L('harga_satuan'), $formatHeader);

           $col = 6;

            $header = $dataMak;
            $max_header = sizeof($header);
            /**
             * membuat header
             */
            if($max_header > 0){
            $this->mWorksheets['Data']->merge_cells(5,$col,5,($col + $max_header)-1);
             $this->mWorksheets['Data']->write($num,$col ,$this->L('perhitungan'), $formatHeader);
               for($n=0;$n < $max_header;$n++) {

                 $this->mWorksheets['Data']->merge_cells(5,$col,5,($col + $max_header)-1);
                 $this->mWorksheets['Data']->write(5, $col + $n+1,'', $formatHeader);
                 $this->mWorksheets['Data']->set_column($col + $n,$col + $n,20);
                 $this->mWorksheets['Data']->write(6, $col + $n, $header[$n]['makNama'], $formatHeader);
                 $this->mWorksheets['Data']->write(7, $col + $n, $header[$n]['makKode'], $formatHeader);
               }
            }
            /**
             * end
            */
           $this->mWorksheets['Data']->set_column(($col + $max_header),($col + $max_header),20);
           $this->mWorksheets['Data']->merge_cells(5,($col + $max_header),7,($col + $max_header));
           $this->mWorksheets['Data']->write($num, ($col + $max_header),$this->L('jumlah_biaya'),
                $formatHeader);
		   $num=6;
		   $this->mWorksheets['Data']->merge_cells(5,0,7,0);
		   $this->mWorksheets['Data']->merge_cells(5,1,7,1);
		   $this->mWorksheets['Data']->merge_cells(5,2,7,2);
		   $this->mWorksheets['Data']->merge_cells(5,3,7,3);
           $this->mWorksheets['Data']->merge_cells(5,4,7,4);
		   $this->mWorksheets['Data']->merge_cells(5,5,7,5);
		   $this->mWorksheets['Data']->write($num, 0, '', $formatHeader);
           $this->mWorksheets['Data']->write($num, 1, '', $formatHeader);
           $this->mWorksheets['Data']->write($num, 2, '', $formatHeader);
           $this->mWorksheets['Data']->write($num, 3, '', $formatHeader);
		   $this->mWorksheets['Data']->write($num, 4, '', $formatHeader);
           $this->mWorksheets['Data']->write($num, 5, '', $formatHeader);
           $this->mWorksheets['Data']->merge_cells(5,($col + $max_header),7,($col + $max_header));
           $this->mWorksheets['Data']->write($num, ($col + $max_header), '', $formatHeader);


		   $num=7;
		   $this->mWorksheets['Data']->merge_cells(5,0,7,0);
		   $this->mWorksheets['Data']->merge_cells(5,1,7,1);
		   $this->mWorksheets['Data']->merge_cells(5,2,7,2);
		   $this->mWorksheets['Data']->merge_cells(5,3,7,3);
           $this->mWorksheets['Data']->merge_cells(5,4,7,4);
		   $this->mWorksheets['Data']->merge_cells(5,5,7,5);
		   $this->mWorksheets['Data']->write($num, 0, '', $formatHeader);
           $this->mWorksheets['Data']->write($num, 1, '', $formatHeader);
           $this->mWorksheets['Data']->write($num, 2, '', $formatHeader);
           $this->mWorksheets['Data']->write($num, 3, '', $formatHeader);
		   $this->mWorksheets['Data']->write($num, 4, '', $formatHeader);
           $this->mWorksheets['Data']->write($num, 5, '', $formatHeader);
           $this->mWorksheets['Data']->merge_cells(5,($col + $max_header),7,($col + $max_header));
           $this->mWorksheets['Data']->write($num, ($col + $max_header), '', $formatHeader);

            $num=8;
			for($j=0;$j<sizeof($send);$j++) {
				if($send[$j]['sts'] == 'mak'){
					continue;
				}
			   if($send[$j]['jumlah_setuju'] == "NULL") {
					$send[$j]['jumlah_setuju'] = '';
			   } else {
			   		$send[$j]['jumlah_setuju'] = $send[$j]['jumlah_setuju'];
			   }
			   $this->mWorksheets['Data']->write($num, 0, $send[$j]['nomor'], $send[$j]['format']);
			   $this->mWorksheets['Data']->write_string($num, 1, $send[$j]['kode'], $send[$j]['format']);
			   $this->mWorksheets['Data']->write_string($num, 2, $send[$j]['nama'], $send[$j]['format']);
               $this->mWorksheets['Data']->write_string($num, 3, $send[$j]['unit_subunit'], $send[$j]['format']);
			   $this->mWorksheets['Data']->write($num, 4, $send[$j]['satuan_setuju'], $send[$j]['format']);
			   $this->mWorksheets['Data']->write($num, 5, $send[$j]['nominal_setuju'],$send[$j]['format_curr']);

                if($max_header > 0){
                    for($f=0;$f <sizeof($header);$f++) {

                        if( $send[$j]['jenis'] == 'komponen'){
                            if($send[$j]['mak_id'] == $header[$f]['mak_id']){
                                $this->mWorksheets['Data']->write($num, $col + $f,
                                            $send[$j]['jumlah_setuju'],$send[$j]['format_curr']);
                            } else {
                                $this->mWorksheets['Data']->write($num, $col + $f,
                                            '',$send[$j]['format_curr']);
                            }
                        }  else {
                            $this->mWorksheets['Data']->write($num, $col + $f,
                                            '',$send[$j]['format_curr']);
                        }

                    }

                }

			   $this->mWorksheets['Data']->write($num, ($col + $max_header),
                            $send[$j]['jumlah_setuju'], $send[$j]['format_curr']);
			   $num++;
			}


            /**
             * total per mak
             */
               $this->mWorksheets['Data']->merge_cells($num,0,$num,5);
			   $this->mWorksheets['Data']->write($num, 0,'TOTAL',$formatHeader);
               $this->mWorksheets['Data']->merge_cells($num,0,$num,5);
               $this->mWorksheets['Data']->write($num, 1, '',$formatHeader);
               $this->mWorksheets['Data']->merge_cells($num,0,$num,5);
			   $this->mWorksheets['Data']->write($num, 2, '',$formatHeader);
               $this->mWorksheets['Data']->merge_cells($num,0,$num,5);
               $this->mWorksheets['Data']->write($num, 3, '',$formatHeader);
               $this->mWorksheets['Data']->merge_cells($num,0,$num,5);
			   $this->mWorksheets['Data']->write($num, 4, '',$formatHeader);
               $this->mWorksheets['Data']->merge_cells($num,0,$num,5);
			   $this->mWorksheets['Data']->write($num, 5, '',$formatHeader);

                if($max_header > 0){
                    for($f=0;$f <sizeof($header);$f++) {
                        $this->mWorksheets['Data']->write($num, $col + $f,
                                            $header[$f]['jumlah_per_mak'],$formatCurrencyProgram);
                }
			   $this->mWorksheets['Data']->write($num, ($col + $max_header),'', $formatCurrencyProgram);
			   $num++;
            }
            /**
             * end
             */
		}
	}
}
