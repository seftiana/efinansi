<?php

/**
 * class ViewLapRpd
 * @package lap_rpd_per_kegiatan
 * @subpackage response
 * @todo untuk menampilkan tampilan daftar data laporan RDP
 * @since mei 2012
 * @copyright 2012 Gamatechno Indonesia
 * @author noor hadi <noor.hadi@gamatechno.com>
 */

require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
    'module/lap_rpd_per_kegiatan/business/AppLapRpdPerKegiatan.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
    'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewLapRpd extends HtmlResponse
{

	var $Pesan;

	function TemplateModule()
    {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
                'module/lap_rpd_per_kegiatan/template');
		$this->SetTemplateFile('view_lap_rpd_per_kegiatan.html');
	}

	function ProcessRequest()
    {
		$_POST = $_POST->AsArray();
		$rincianObj = new AppLapRpd();
		$userUnitKerjaObj = new UserUnitKerja();

		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		//$role = $userUnitKerjaObj->GetRoleUser($userId);
		//print_r($role);
        $unit = $userUnitKerjaObj->GetUnitKerjaUser($userId);

			if($_POST['btncari']) {
				$this->Data['tahun_anggaran'] = $_POST['tahun_anggaran'];
				$this->Data['unitkerja'] = $_POST['unitkerja'];
				$this->Data['unitkerja_label'] = $_POST['unitkerja_label'];
			} elseif($_GET['cari'] != "") {
				$get = $_GET->AsArray();
				$this->Data['tahun_anggaran'] = Dispatcher::Instance()->Decrypt($get['tahun_anggaran']);
				$this->Data['unitkerja'] = Dispatcher::Instance()->Decrypt($get['unitkerja']);
				$this->Data['unitkerja_label'] = Dispatcher::Instance()->Decrypt($get['unitkerja_label']);
			} else {
				$tahun_anggaran = $rincianObj->GetTahunAnggaranAktif();
				$this->Data = $_POST;
				$this->Data['tahun_anggaran'] = $tahun_anggaran['id'];
                $this->Data['unitkerja'] = $unit['unit_kerja_id'];
                $this->Data['unitkerja_label'] = $unit['unit_kerja_nama'];
			}

			$arr_tahun_anggaran = $rincianObj->GetComboTahunAnggaran();
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

 	//view
		$totalData = $rincianObj->GetCountDataRpd(
                                                $this->Data['tahun_anggaran'],
                                                $this->Data['unitkerja']);
		//print_r($operator_role);
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataRincian = $rincianObj->GetDataRpd($startRec,
                                                $itemViewed,
                                                $this->Data['tahun_anggaran'],
                                                $this->Data['unitkerja']);

        $dataMak = $rincianObj->GetMak($this->Data['tahun_anggaran'],$this->Data['unitkerja']);
		//print_r($dataRincian);
		$url = Dispatcher::Instance()->GetUrl(
                                    Dispatcher::Instance()->mModule,
                                    Dispatcher::Instance()->mSubModule,
                                    Dispatcher::Instance()->mAction,
                                    Dispatcher::Instance()->mType .
                                    '&tahun_anggaran=' .
                                    Dispatcher::Instance()->Encrypt($this->Data['tahun_anggaran'])   .
                                    '&tahun_anggaran_label=' .
                                    Dispatcher::Instance()->Encrypt($this->Data['tahun_anggaran_label'])  .
                                    '&unitkerja=' .
                                    Dispatcher::Instance()->Encrypt($this->Data['unitkerja'])  .
                                    '&unitkerja_label=' .
                                    Dispatcher::Instance()->Encrypt($this->Data['unitkerja_label']) .
                                    '&cari=' . Dispatcher::Instance()->Encrypt(1));


		Messenger::Instance()->SendToComponent(
                                    'paging',
                                    'Paging',
                                    'view',
                                    'html',
                                    'paging_top',
                                    array(
                                            $itemViewed,
                                            $totalData,
                                            $url,
                                             $currPage),
                                    Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['role_name'] = $role['role_name'];
		$return['operator_role'] = $operator_role;
		$return['data'] = $dataRincian;
		$return['startRec'] = $startRec;
		$return['itemViewed'] = $itemViewed;
		$return['start'] = $startRec+1;
        $return['total_sub_unit'] = $userUnitKerjaObj->GetTotalSubUnitKerja($unit['unit_kerja_id']);
        $return['data_mak'] =$dataMak;
		return $return;
	}

	function tambahNol($str="0", $jml_char=2)
    {
		while(strlen($str) < $jml_char) {
			$str = "0" . $str;
		}
		return $str;
	}

	function ParseTemplate($data = NULL)
    {
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'URL_SEARCH',
                                    Dispatcher::Instance()->GetUrl(
                                                'lap_rpd_per_kegiatan',
                                                'lapRpd',
                                                'view',
                                                'html'));

		$this->mrTemplate->AddVar('content', 'URL_CETAK',
                                    Dispatcher::Instance()->GetUrl(
                                                'lap_rpd_per_kegiatan',
                                                'cetakLapRpd',
                                                'view',
                                                'html') .
                                                "&tgl=".Dispatcher::Instance()->mType .
                                                '&tahun_anggaran=' .
                                                Dispatcher::Instance()->Encrypt($this->Data['tahun_anggaran']).
                                                '&unitkerja='.
                                                Dispatcher::Instance()->Encrypt($this->Data['unitkerja']));
                                                /**
                                                '&startRec='.
                                                Dispatcher::Instance()->Encrypt($data['startRec']).
                                                '&itemViewed='.
                                                Dispatcher::Instance()->Encrypt($data['itemViewed']));
                                                */

		$this->mrTemplate->AddVar('content', 'URL_EXCEL',
                                Dispatcher::Instance()->GetUrl(
                                            'lap_rpd_per_kegiatan',
                                            'excelLapRpd',
                                            'view',
                                            'xlsx') .
                                            "&tgl=".Dispatcher::Instance()->mType .
                                            '&tahun_anggaran=' .
                                            Dispatcher::Instance()->Encrypt($this->Data['tahun_anggaran']) .
                                            '&unitkerja=' .
                                            Dispatcher::Instance()->Encrypt($this->Data['unitkerja']));
                                            /**
                                            '&startRec='. Dispatcher::Instance()->Encrypt($data['startRec']) .
                                            '&itemViewed='.
                                            Dispatcher::Instance()->Encrypt($data['itemViewed']));
                                            */

      $this->mrTemplate->AddVar('content', 'UNITKERJA', $this->Data['unitkerja']);
	  $this->mrTemplate->AddVar('role', 'UNITKERJA_LABEL', $this->Data['unitkerja_label']);

      if($data['total_sub_unit'] > 0){
   			$this->mrTemplate->AddVar('role', 'IS_PARENT_UNIT', 'YES');
			$this->mrTemplate->AddVar('role', 'URL_POPUP_UNITKERJA',
                            Dispatcher::Instance()->GetUrl(
                                        'lap_rpd_per_kegiatan',
                                        'popupUnitkerja',
                                        'view',
                                        'html'));
      } else {
        	$this->mrTemplate->AddVar('role', 'IS_PARENT_UNIT', 'NO');
      }

		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

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
		 $mak = '';
         $no=1;
         $cari = Dispatcher::Instance()->Encrypt(1);

         for ($i=0; $i<sizeof($dataGrid);) {
         //=========strat setting tampilan=======================
            $view_program_nomor = $dataGrid[$i]['program_nomor'];
            $view_kegiatan_nomor = $dataGrid[$i]['kegiatan_nomor'];

            //komponen
            if(($program_nomor == $dataGrid[$i]['program_id']) &&
                    ($kegiatan_nomor == $dataGrid[$i]['subprogram_id']) &&
                        ($sub_keg_nomor == $dataGrid[$i]['subkegiatan_id']) &&
                            ($mak == $dataGrid[$i]['mak_id'])) {
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

            }elseif ($mak != $dataGrid[$i]['mak_id']) {
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
			}/**
            elseif ($unit_mak != ($dataGrid[$i]['unit_id'].$dataGrid[$i]['mak_id']) ) {
				$unit_mak = $dataGrid[$i]['unit_id'].$dataGrid[$i]['mak_id'];
				$unit_nama = $dataGrid[$i]['unit_subunit'];
				if(($dataGrid[$i]['unit_id'] == "") && ($dataGrid[$i]['unit_subunit'] == "")) {
					$send[$x]['nama'] = "NULL";
					$send[$x]['jumlah_setuju'] = "NULL";
				} else {
					$send[$x]['nama'] = "<i>".$unit_nama."</i>";
					$send[$x]['jumlah_setuju'] = "NULL";
				}
			}*/
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
                                $send[$j]['jumlah_setuju'] = '<b>' .
                                                number_format($send[$j]['jumlah_setuju'], 0, ',', '.').'</b>';
                            }elseif($send[$j]['jenis'] == 'subkegiatan'){
                                $send[$j]['jumlah_setuju'] = '<i>' .
                                                number_format($send[$j]['jumlah_setuju'], 0, ',', '.').'</i>';
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
