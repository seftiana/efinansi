<?php
/**
* Module : approval_pencairan
* FileInclude : Spm.class.php
* Class : ViewInputSpm
* Extends : HtmlResponse
*/
    require_once GTFWConfiguration::GetValue('application','docroot').
    'module/approval_pencairan/business/Spm.class.php';

    class ViewInputSpm extends HtmlResponse{
        function TemplateModule(){
            $this->setTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
            'module/approval_pencairan/template/');
            $this->setTemplateFile('view_input_spm.html');
        }
        
        function ProcessRequest(){
            $spmObj         = new Spm();
            $msg = Messenger::Instance()->Receive(__FILE__);
            $post_message   = $msg[0][0];
            $message        = $msg[0][1];
            $css            = $msg[0][2];
            
            $listCarabayar          = $spmObj->ListCaraBayar();
            $listJenisPembayaran    = $spmObj->ListJenisPembayaran();
            $listSifatPembayaran    = $spmObj->ListSifatPembayaran();
            $dataId                 = Dispatcher::Instance()->Decrypt($_GET['dataId']);
            $spmId                  = Dispatcher::Instance()->Decrypt($_GET['spmId']);
            $pajakArr               = $spmObj->GetComboPajak();
            
            if (isset($spmId) AND $spmId != '')
            {
                # code...
                $spm_data           = $spmObj->GetSpmBySpmId($spmId);
            }
            
            if(isset($post_message))
            {
                $caraBayarSelected      = $post_message['cara_bayar'];
                $jenisBayarSelected     = $post_message['jenis_bayar'];
                $sifatBayarSelected     = $post_message['sifat_bayar'];
                $kdPenerimaanSelected   = $post_message['kode_penerimaan'];
            }
            elseif (isset($spmId) AND $spmId != '')
            {
                # code...
                $caraBayarSelected      = $spm_data['cara_bayar_id'];
                $jenisBayarSelected     = $spm_data['jenis_bayar_id'];
                $sifatBayarSelected     = $spm_data['sifat_bayar_id'];
                $kdPenerimaanSelected   = $spm_data['pajakId'];
            }
            else
            {
                # code...
                $caraBayarSelected  = '';
                $jenisBayarSelected = '';
                $sifatBayarSelected = '';
            }
            Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'cara_bayar', 
            array(
                'cara_bayar',
                $listCarabayar,
                $caraBayarSelected,
                'all',
                ' style="width:175px;" '
            ) ,Messenger::CurrentRequest);
            
            Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'jenis_bayar', 
            array(
                'jenis_bayar',
                $listJenisPembayaran,
                $jenisBayarSelected,
                'all',
                ' style="width:185px;" ') ,
            Messenger::CurrentRequest);
            
            Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'sifat_bayar', 
            array(
                'sifat_bayar',
                $listSifatPembayaran,
                $sifatBayarSelected,
                'all',
                ' style="width:185px;" ') ,
            Messenger::CurrentRequest);
            
            Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'kode_penerimaan', 
            array(
                'kode_penerimaan',
                $pajakArr,
                $kdPenerimaanSelected,
                'all',
                ' style="width:275px;" '
            ) ,Messenger::CurrentRequest);
            
            $return['detil']    = $spmObj->ListKegiatanByApprovalId($dataId);
            $return['post']     = $post_message;
            $return['pesan']    = $message;
            $return['css']      = $css;
            $return['data_spm'] = $spm_data;
            $return['dataId']   = $dataId;
            $return['spmId']    = $spmId;
            
            return $return;
            
        }
        
        function ParseTemplate($data = null){
            $this->mrTemplate->AddVar('content','DATA_ID',$data['dataId']);
            $this->mrTemplate->AddVar('content','SPM_ID',$data['spmId']);
            if (isset($data['pesan']))
            {
                # code...
                $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
		        $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['pesan']);
		        $this->mrTemplate->AddVar('warning_box', 'CSS', $data['css']);
		        $this->mrTemplate->AddVar('content','NAMA',$data['post']['nama']);
		        $this->mrTemplate->AddVar('content','NPWP',$data['post']['npwp']);
		        $this->mrTemplate->AddVar('content','REKENING',$data['post']['rekening']);
		        $this->mrTemplate->AddVar('content','BANK',$data['post']['bank']);
		        $this->mrTemplate->AddVar('content','URAIAN',$data['post']['uraian']);
                $this->mrTemplate->AddVar('content','NOMINAL_PAJAK',$data['post']['nominal_pajak']);
            }
            elseif (isset($data['data_spm']))
            {
                # code...
                $this->mrTemplate->AddVar('content','NAMA',$data['data_spm']['spm_nama']);
		        $this->mrTemplate->AddVar('content','NPWP',$data['data_spm']['spm_npwp']);
		        $this->mrTemplate->AddVar('content','REKENING',$data['data_spm']['spm_rekening']);
		        $this->mrTemplate->AddVar('content','BANK',$data['data_spm']['spm_bank']);
		        $this->mrTemplate->AddVar('content','URAIAN',$data['data_spm']['spm_keterangan']);
                $this->mrTemplate->AddVar('content','NOMINAL_PAJAK',$data['data_spm']['nominal_potongan']);
            }
            if (isset($data['spmId']) AND $data['spmId'] != '')
            {
                # code...
                $url_action     = Dispatcher::Instance()->GetUrl('approval_pencairan','UpdateSpm','do','html');
                $btn_val        = 'UpdateSpm';
                $judul          = 'Edit';
            }
            else
            {
                # code...
                $url_action     = Dispatcher::Instance()->GetUrl('approval_pencairan','AddSpm','do','html');
                $btn_val        = 'Simpan';
                $judul          = 'Tambah';
            }
            
            
            $this->mrTemplate->AddVar('content','URL_ACTION',$url_action);
            $this->mrTemplate->AddVar('content','JUDUL',$judul);
            $this->mrTemplate->AddVar('content','BTN_VAL',$btn_val);
            
            $list_detil     = $data['detil'];
            for ($i = 0; $i < count($list_detil); $i++)
            {
                # code...
                $list_detil[$i]['nominal_text'] = number_format($list_detil[$i]['spp_ini'],2,',','.');
                $nominal[$i]                    = $list_detil[$i]['spp_ini'];
                $this->mrTemplate->AddVars('data_item',$list_detil[$i],'');
                $this->mrTemplate->parseTemplate('data_item','a');
            }
            $this->mrTemplate->AddVar('content','JUMLAH_UANG',number_format(array_sum($nominal),2,',','.'));
            $this->mrTemplate->AddVar('content','NOMINAL',array_sum($nominal)); 
        }
    }
?>
