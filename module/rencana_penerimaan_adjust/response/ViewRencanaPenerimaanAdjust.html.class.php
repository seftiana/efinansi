<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/rencana_penerimaan_adjust/business/RencanaPenerimaanAdjust.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/user_unit_kerja/business/UserUnitKerja.class.php';

#doc
#    classname:    ViewRencanaPenerimaanAdjust
#    scope:        PUBLIC
#
#/doc

class ViewRencanaPenerimaanAdjust extends HtmlResponse
{
    #    internal variables
    public $obj;
    public $data;
    public $dataList;
    public $message;
    public $style;
    public $uid;
    public $unitKerjaObj;
    public $post;
    #    Constructor
    function __construct ()
    {
        # code...
        $this->obj          = new RencanaPenerimaanAdjust();
        $this->unitKerjaObj = new UserUnitKerja();
        $this->uid          = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
        $this->post         = $_POST->AsArray();
    }
    
    function TemplateModule()
    {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
        'module/rencana_penerimaan_adjust/template/');
        $this->SetTemplateFile('view_rencana_penerimaan_adjust.html');
    }
    
    function ProcessRequest()
    {
        $role   = $this->unitKerjaObj->GetRoleUser($this->uid);
        $unit   = $this->unitKerjaObj->GetUnitKerjaUser($this->uid);
        $tahun_anggaran     = $this->obj->GetTahunAnggaranAktif();
        $arr_tahun_anggaran = $this->obj->GetComboTahunAnggaran();
        $msg			= Messenger::Instance()->Receive(__FILE__);
        $return['msg']	= $msg[0][1];
		$return['data']	= $msg[0][0];
		$return['css']	= $msg[0][2];
		    
        if(isset($this->post['btncari'])){
            $this->data['nama']             = trim($this->post['nama']);
            $this->data['tahun_anggaran']   = $this->post['tahun_anggaran'];
            $this->data['unit_id']          = $this->post['unit_id'];
            $this->data['unit']             = $this->post['unit'];
        }elseif(isset($_GET) AND $_GET['cari'] != ''){
            $this->data['nama']             = Dispatcher::Instance()->Decrypt($_GET['nama']);
            $this->data['tahun_anggaran']   = Dispatcher::Instance()->Decrypt($_GET['tahun_anggaran']);
            $this->data['unit_id']          = Dispatcher::Instance()->Decrypt($_GET['unit_id']);
            $this->data['unit']             = Dispatcher::Instance()->Decrypt($_GET['unit']);
        }else{
            $this->data['tahun_anggaran']   = $tahun_anggaran['id'];
            $this->data['unit_id']          = $unit['unit_kerja_id'];
            $this->data['unit']             = $unit['unit_kerja_nama'];
            $this->data['nama']             = '';
        }
        
        Messenger::Instance()->SendToComponent(
            'combobox', 
            'Combobox', 
            'view', 
            'html', 
            'tahun_anggaran', 
            array(
                'tahun_anggaran', 
                $arr_tahun_anggaran, 
                $this->data['tahun_anggaran'], '-', 
                ' style="width:200px;" id="tahun_anggaran"'
            ), 
        Messenger::CurrentRequest);
        
        // paging
        $totalData = $this->obj->GetCountData(
                    $this->data['nama'],
                    $this->data['tahun_anggaran'], 
                    $this->data['unit_id']);
        $itemViewed = 20;
        $currPage = 1;
        $startRec = 0 ;
        if(isset($_GET['page'])) {
            $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
            $startRec =($currPage-1) * $itemViewed;
        }
        
        $data_unit = $this->obj->GetDataUnitkerja(
                    $this->data['nama'],
                    $this->data['tahun_anggaran'],
                    $this->data['unit_id'],$startRec,$itemViewed);
        
        $url = Dispatcher::Instance()->GetUrl(
            Dispatcher::Instance()->mModule, 
            Dispatcher::Instance()->mSubModule, 
            Dispatcher::Instance()->mAction, 
            Dispatcher::Instance()->mType 
            . '&nama=' . Dispatcher::Instance()->Encrypt($this->data['nama']) 
            . '&tahun_anggaran=' . Dispatcher::Instance()->Encrypt(
            $this->data['tahun_anggaran']) 
            . '&unit_id=' . Dispatcher::Instance()->Encrypt($this->data['unit_id']) 
            . '&unit=' . Dispatcher::Instance()->Encrypt(
            $this->data['unit']) 
            . '&cari=' . Dispatcher::Instance()->Encrypt(1));

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
        $this->dataList     = $data_unit;     
        $this->data['role'] = $role;
        return $return;
    }
    
    function ParseTemplate($data = null)
    {
        $msg        = $data['msg'];
        $style      = $data['css'];
        if($msg):
		    $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
		    $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $msg);
		    $this->mrTemplate->AddVar('warning_box', 'STYLE', $style);
		    
	    endif;
        $role       = $this->data['role'];
        $url_search = Dispatcher::Instance()->GetUrl(
            'rencana_penerimaan_adjust',
            'RencanaPenerimaanAdjust',
            'view',
            'html'
        );
        $this->mrTemplate->AddVar('content','URL_SEARCH',$url_search);
        $this->mrTemplate->AddVar('content','URL_RESET',$url_search);
        $this->mrTemplate->AddVar('role','WHOAMI',strtolower($role['role_name']));
        
        $popup_unit_kerja   = Dispatcher::Instance()->GetUrl(
            'rencana_penerimaan_adjust',
            'PopupUnitkerja',
            'view',
            'html'
        );
        
        $this->mrTemplate->AddVar('role','URL_POPUP_UNIT_KERJA',$popup_unit_kerja);
        
        // data search
        $this->mrTemplate->AddVar('content','SEARCH_NAMA',$this->data['nama']);
        $this->mrTemplate->AddVar('role','UNIT_ID',$this->data['unit_id']);
        $this->mrTemplate->AddVar('role','UNIT',$this->data['unit']);
        
        $data_list  = $this->dataList;
        if(empty($data_list)){
            # jika data rencana penerimaan tidak ada alias kosong
            $this->mrTemplate->AddVar('data_list','DATA_EMPTY','YES');
        }else{
            # jika data rencana penerimaan ada datanya
            $this->mrTemplate->AddVar('data_list','DATA_EMPTY','NO');
            
            $total='';
            $jumlah_total='';
            $idrencana='';
            $idkode='';
            $kode='';
            $nama='';

            $kode_satker = '';
            $kode_unit = '';
            $nama_satker='';
            $nama_unit='';
            
            for ($i=0; $i<sizeof($data_list);) {
                 if(($data_list[$i]['kode_satker'] == $kode_satker) && 
                        ($data_list[$i]['kode_unit'] == $kode_unit)) {
                        
                    if($data_list[$i]['idrencana'] == "") {
                        $i++; 
                        continue;
                    }
                    
                    $send                       = $data_list[$i];
                    $send['total_penerimaan']   = number_format($data_list[$i]['total'], 0, ',', '.');
                    $send['class_name']         = "";
                    $send['nomor']              = $no;
                    $send['class_button']       = "links";
                    
                    $url_add_edit               = Dispatcher::Instance()->GetUrl(
                        'rencana_penerimaan', 
                        'InputRencanaPenerimaan', 
                        'view', 
                        'html&dataId='. Dispatcher::Instance()->Encrypt(
                            $data_list[$i]['idrencana']
                        )
                    );

                    if ($send['approval']==1){
                        $send['url_add_edit']   = '<a class="xhr dest_subcontent-element" 
                                                href="'.$url_add_edit.'" 
                                                title="Aadjust Rencana penerimaan">".
                                                "<img src="images/button-edit.gif" ".
                                                "alt="Ubah Rencana Penerimaan"/></a>';
                        
                        $url_adjust             = Dispatcher::Instance()->GetUrl(
                            'rencana_penerimaan_adjust',
                            'InputRencanaPenerimaanAdjust',
                            'view',
                            'html&dataId='. Dispatcher::Instance()->Encrypt(
                                $data_list[$i]['idrencana']
                            )
                        );
                        $send['url_adjust']     = '<a class="xhr dest_subcontent-element" 
                                                href="'.$url_adjust.'" 
                                                Title="Adjustment rencana penerimaan">
                                                <img src="images/button-tindaklanjuti.gif" 
                                                alt="Adjust" /></a>';
                    }else{
                        $send['url_add_edit'] = '';
                    }
                    //$send['url_cetak'] = '<a  href="'.$url_cetak.'" title="Cetak SSBP">
                    //<img src="images/button-print.gif" alt="Cetak SSBP"/></a>';
                    
                    $this->mrTemplate->AddVar('cekbox', 'data_number', $number);
                    $this->mrTemplate->AddVar('cekbox', 'data_idrencana', $data_list[$i]['idrencana']);
                    $this->mrTemplate->AddVar('cekbox', 'data_nama', $data_list[$i]['nama']);
                    $this->mrTemplate->AddVar('cekbox', 'IS_SHOW', 'YES');
                    $i++;
                    $no++;
                    $number++;

                } elseif($data_list[$i]['kode_satker'] != $kode_satker && 
                        $data_list[$i]['nama_satker'] == $data_list[$i]['nama_unit']) {
                    $send['url_adjust'] = '';
                    $kode_satker        = $data_list[$i]['kode_satker'];
                    $kode_unit          = $data_list[$i]['kode_unit'];
                    $nama_satker        = $data_list[$i]['nama_satker'];
                    $nama_unit          = $data_list[$i]['nama_unit'];
                    $send['kode']       = "<b>".$kode_unit."</b>";
                    $send['nama']       = "<b>".$data_list[$i]['nama_unit']."</b>";
                    $send['total_penerimaan'] = "<b>".number_format(
                        $data_list[$i]['jumlah_total'], 
                        0, 
                        ',',
                        '.'
                    )."</b>";
                    $send['class_name']     = "table-common-even1";
                    $send['nomor']          = "";
                    $send['class_button']   = "toolbar";
                    $url_add_edit = Dispatcher::Instance()->GetUrl(
                        'rencana_penerimaan', 
                        'InputRencanaPenerimaan', 
                        'view', 
                        'html&tahun_anggaran=' . 
                        Dispatcher::Instance()->Encrypt(
                            $this->data['tahun_anggaran']
                        ) .'&unitkerja=' . 
                        Dispatcher::Instance()->Encrypt(
                            $data_list[$i]['idunit']
                        ) .'&cari=' . Dispatcher::Instance()->Encrypt(1)
                    );
 
                    $send['url_add_edit']   = '<a class="xhr dest_subcontent-element" href="'.
                    $url_add_edit.'" title="Tambah Rencana Penerimaan">
                    <img src="images/button-add.gif" 
                    alt="Tambah Rencana Penerimaan"/></a>';
 
                    $send['url_add_delete'] = "";
                    $send['url_cetak']      = "";
                    $no=1;
                }elseif($data_list[$i]['kode_unit'] != $kode_unit) {
                    
                    $kode_satker    = $data_list[$i]['kode_satker'];
                    $kode_unit      = $data_list[$i]['kode_unit'];
                    $nama_satker    = $data_list[$i]['nama_satker'];
                    $nama_unit      = $data_list[$i]['nama_unit'];
                    $send['kode']   = "<b>".$kode_unit."</b>";
                    $send['nama']   = "<b>".$data_list[$i]['nama_unit']."</b>";
                    $send['total_penerimaan'] = "<b>".number_format(
                        $data_list[$i]['jumlah_total'], 
                        0, 
                        ',', 
                        '.'
                    )."</b>";
                    $send['class_name']     = "table-common-even";
                    $send['nomor']          = "";
                    $send['class_button']   = "toolbar";
                    $url_add_edit           = Dispatcher::Instance()->GetUrl(
                        'rencana_penerimaan', 
                        'InputRencanaPenerimaan', 
                        'view', 
                        'html&tahun_anggaran=' . 
                        Dispatcher::Instance()->Encrypt(
                        $this->data['tahun_anggaran']) . 
                        '&unitkerja=' . 
                        Dispatcher::Instance()->Encrypt($data_list[$i]['idunit']) . 
                        '&cari=' . Dispatcher::Instance()->Encrypt(1)
                    );
 
                    $send['url_add_edit'] = '<a class="xhr dest_subcontent-element" href="'.
                    $url_add_edit.
                     '" title="Tambah Rencana Penerimaan">
                    <img src="images/button-add.gif" 
                    alt="Tambah Rencana Penerimaan"/></a>';
 
                    $send['url_add_delete'] = "";
                    $send['url_cetak'] ="";
                    $no=1;
                }
 
            if($checkTA['is_aktif'] == 'T'){
                $send['disable_check']  = "disable='disabled'";
                $send['hidden_link']    = "style='display:none;'";
                $send['url_add_edit']   = "";
                $send['url_add_delete'] = "";    
            }
            
            $this->mrTemplate->AddVars('data_grid', $send, 'DATA_');
            $this->mrTemplate->parseTemplate('data_grid', 'a');
            
            }
        }
    }
}
?>
