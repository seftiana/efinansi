<?php
/**
* Module : approval_pencairan
* FileInclude : Spm.class.php
* Class : ViewPrintSpm
* Extends : HtmlResponse
*/
    require_once GTFWConfiguration::GetValue('application','docroot').
    'module/approval_pencairan/business/Spm.class.php';
    require_once GTFWConfiguration::GetValue('application','docroot').
    'module/approval_pencairan/business/AppApprovalPencairan.class.php';
    require_once GTFWConfiguration::GetValue('application','docroot').
    'main/function/terbilang.php';

    class ViewPrintSpm extends HtmlResponse{
        function TemplateModule(){
            $this->setTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
            'module/approval_pencairan/template/');
            $this->setTemplateFile('view_print_spm.html');
        }
        function TemplateBase() {
		    $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
		    $this->SetTemplateFile('document-print.html');
		    $this->SetTemplateFile('layout-common-print.html');
	    }
        function ProcessRequest(){
            # code ...
            $spmObj         = new Spm();
            $Obj = new AppApprovalPencairan();
            $msg = Messenger::Instance()->Receive(__FILE__);
            $post_message   = $msg[0][0];
            $message        = $msg[0][1];
            $css            = $msg[0][2];

            $listCarabayar          = $spmObj->ListCaraBayar();
            $listJenisPembayaran    = $spmObj->ListJenisPembayaran();
            $listSifatPembayaran    = $spmObj->ListSifatPembayaran();
            $dataId                 = Dispatcher::Instance()->Decrypt($_GET['dataId']);
            $spmId                  = Dispatcher::Instance()->Decrypt($_GET['spmId']);

            if (isset($spmId) AND $spmId != '')
            {
                # code...
                $spm_data           = $spmObj->GetSpmBySpmId($spmId);
                $dipa               = $spmObj->GetDipa();
            }
            $dataApprovalPencairan = $Obj->GetDataById($dataId);
            $return['dataApprovalPencairan'] = $dataApprovalPencairan;
            $return['detil']        = $spmObj->ListKegiatanByApprovalId($dataId);
            $return['spm_pajak']    = $spmObj->GetPajakSpm($spmId);
            $return['data_spm']     = $spm_data;
            $return['dataId']       = $dataId;
            $return['spmId']        = $spmId;
            $return['dipa']         = $dipa;

            return $return;
        }

        function ParseTemplate($data = null){
            # code ...
            // print_r($data['dipa']);
            $number     = new Number();

            $dataApprovalPencairan = $data['dataApprovalPencairan'];
            $this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN_LABEL', $dataApprovalPencairan['tahun_anggaran_label']);
            $tanggal_spm    = date_format(date_create($data['data_spm']['spm_tanggal']), 'd-m-Y');
            $this->mrTemplate->AddVar('content','SPM_ID',$data['data_spm']['spm_id']);
            $this->mrTemplate->AddVar('content','SPM_NOMOR',$data['data_spm']['spm_nomor']);
            $this->mrTemplate->AddVar('content','CARA_BAYAR_ID',$data['data_spm']['cara_bayar_id']);
            $this->mrTemplate->AddVar('content','JENIS_BAYAR_ID',$data['data_spm']['jenis_bayar_id']);
            $this->mrTemplate->AddVar('content','SIFAT_BAYAR_ID',$data['data_spm']['sifat_bayar_id']);
            $this->mrTemplate->AddVar('content','SPM_NAMA',$data['data_spm']['spm_nama']);
            $this->mrTemplate->AddVar('content','SPM_NPWP',$data['data_spm']['spm_npwp']);
            $this->mrTemplate->AddVar('content','SPM_REKENING',$data['data_spm']['spm_rekening']);
            $this->mrTemplate->AddVar('content','SPM_BANK',$data['data_spm']['spm_bank']);
            $this->mrTemplate->AddVar('content','SPM_KETERANGAN',$data['data_spm']['spm_keterangan']);
            $this->mrTemplate->AddVar('content','SPM_NOMINAL','Rp. '.number_format($data['data_spm']['spm_nominal'],2,',','.'));
            $this->mrTemplate->AddVar('content','USER_ID',$data['data_spm']['user_id']);
            $this->mrTemplate->AddVar('content','SPM_TANGGAL',$tanggal_spm);
            $this->mrTemplate->AddVar('content','SPM_TGL_UBAH',$data['data_spm']['spm_tgl_ubah']);
            $this->mrTemplate->AddVar('content','CARA_BAYAR_KODE',$data['data_spm']['cara_bayar_kode']);
            $this->mrTemplate->AddVar('content','CARA_BAYAR_NAMA',$data['data_spm']['cara_bayar_nama']);
            $this->mrTemplate->AddVar('content','JENIS_BAYAR_KODE',$data['data_spm']['jenis_bayar_kode']);
            $this->mrTemplate->AddVar('content','JENIS_BAYAR_NAMA',$data['data_spm']['jenis_bayar_nama']);
            $this->mrTemplate->AddVar('content','SIFAT_BAYAR_KODE',$data['data_spm']['sifat_bayar_kode']);
            $this->mrTemplate->AddVar('content','SIFAT_BAYAR_NAMA',$data['data_spm']['sifat_bayar_nama']);
            $this->mrTemplate->AddVar('content','TERBILANG',$number->terbilang($data['data_spm']['spm_nominal'],1));
            $this->mrTemplate->AddVar('content','SUB_AKUN', substr($data['detil'][0]['mak_kode'],0,4));
            $this->mrTemplate->AddVar('content','KEGIATAN_NOMOR',$data['detil'][0]['keg_nomor']);
            $this->mrTemplate->AddVar('content','OUTPUT_KODE',$data['detil'][0]['output_kode']);
            $this->mrTemplate->AddVar('content','TANGGAL_SPM',date('Y-m-d', time()));
            $this->mrTemplate->AddVar('content','DIPA_NAMA',wordwrap($data['dipa']['dipa_nama'], 20, "<br />\n"));
            $this->mrTemplate->AddVar('content','DIPA_TANGGAL',$data['dipa']['dipa_tanggal']);
            $this->mrTemplate->AddVar('content','DIPA_NOMINAL',$data['dipa']['dipa_nominal']);
            $this->mrTemplate->AddVar('content','DIPA_TAHUN',date_format(date_create($data['dipa']['dipa_tanggal']), 'Y'));

            $this->mrTemplate->AddVar('content','TIMESTAMP',date('Y/m/d H:i:s', time()));
            $userName = Security::Instance()->mAuthentication->GetCurrentUser()->GetRealName();
            $this->mrTemplate->AddVar('content', 'USERNAME', $userName);
            $list_detil     = $data['detil'];
            $listPajak      = $data['spm_pajak'];

            for ($i = 0; $i < count($list_detil); $i++)
            {
                # code...
                $list_detil[$i]['nominal_text'] = number_format($list_detil[$i]['spp_ini'],2,',','.');
                $nominal[$i]                    = $list_detil[$i]['spp_ini'];

                if($i<1)
                {
                    $nominal_pajak[$i]               = $listPajak['nominal_pajak'];
                    $list_detil[$i]['kode_pajak']    = GTFWConfiguration::GetValue('organization','kementerian_lembaga_no').'/'.GTFWConfiguration::GetValue('organization','unit_org_eselon_no').'/'.GTFWConfiguration::GetValue('organization','nomor_lokasi').'/'.$listPajak['kode_pajak'];
                    $list_detil[$i]['nama_pajak']    = $listPajak['nama_pajak'];
                    $list_detil[$i]['nominal_pajak'] = number_format($listPajak['nominal_pajak'],2,',','.');
                }
                else
                {
                    $list_detil[$i]['kode_pajak']    = '';
                    $list_detil[$i]['nama_pajak']    = '';
                    $list_detil[$i]['nominal_pajak'] = '';
                    $nominal_pajak[$i]               = 0;
                }

                $this->mrTemplate->AddVars('data_item',$list_detil[$i],'');
                $this->mrTemplate->parseTemplate('data_item','a');
            }
            $this->mrTemplate->AddVar('content','JUMLAH_UANG',number_format(array_sum($nominal),2,',','.'));
            $this->mrTemplate->AddVar('content','NOMINAL',array_sum($nominal));
            $this->mrTemplate->AddVar('content','JUMLAH_PAJAK',number_format(array_sum($nominal_pajak),2,',','.'));
        }

        function bilang($x) {
		    $x = abs($x);
		    $angka = array("", "satu", "dua", "tiga", "empat", "lima",
		    "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
		    $result = "";
		    if ($x <12) {
			    $result = " ". $angka[$x];
		    } else if ($x <20) {
			    $result = $this->bilang($x - 10). " belas";
		    } else if ($x <100) {
			    $result = $this->bilang($x/10)." puluh". $this->bilang($x % 10);
		    } else if ($x <200) {
			    $result = " seratus" . bilang($x - 100);
		    } else if ($x <1000) {
			    $result = $this->bilang($x/100) . " ratus" . $this->bilang($x % 100);
		    } else if ($x <2000) {
			    $result = " seribu" . bilang($x - 1000);
		    } else if ($x <1000000) {
			    $result = $this->bilang($x/1000) . " ribu" . $this->bilang($x % 1000);
		    } else if ($x <1000000000) {
			    $result = $this->bilang($x/1000000) . " juta" . $this->bilang($x % 1000000);
		    } else if ($x <1000000000000) {
			    $result = $this->bilang($x/1000000000) . " milyar" . $this->bilang(fmod($x,1000000000));
		    } else if ($x <1000000000000000) {
			    $result = $this->bilang($x/1000000000000) . " trilyun" . $this->bilang(fmod($x,1000000000000));
		    }
			    return $result;
	    }
	    function terbilang($x, $style=4) {
		    if($x<0) {
			    $hasil = "minus ". trim($this->bilang($x));
		    } else {
			    $hasil = trim($this->bilang($x));
		    }
		    switch ($style) {
			    case 1:
				    $hasil = strtoupper($hasil);
				    break;
			    case 2:
				    $hasil = strtolower($hasil);
				    break;
			    case 3:
				    $hasil = ucwords($hasil);
				    break;
			    default:
				    $hasil = ucfirst($hasil);
				    break;
		    }
		    return $hasil.' Rupiah';
	    }
    }
?>