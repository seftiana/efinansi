<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/rencana_penerimaan_adjust/business/RencanaPenerimaanAdjust.class.php';

#doc
#    classname:    ViewInputRencanaPenerimaanAdjust
#    scope:        PUBLIC
#
#/doc

class ViewInputRencanaPenerimaanAdjust extends HtmlResponse
{
    #    internal variables
    public $obj;
    public $data;
    public $message;
    public $style;
    public $idDec;
    #    Constructor
    function __construct ()
    {
        # code...
        $this->obj      = new RencanaPenerimaanAdjust();
        $this->idDec    = Dispatcher::Instance()->Decrypt($_GET['dataId']);
    }
    
    
    function TemplateModule()
    {
        $this->SetTemplateBasedir(GTFwConfiguration::GetValue('application','docroot').
        'module/rencana_penerimaan_adjust/template/');
        $this->SetTemplateFile('view_input_rencana_penerimaan_adjust.html');
    }
    
    function ProcessRequest()
    {
        #echo $this->obj->GenerateNomorAdjustment();
        #echo $this->idDec;
        $this->data     = $this->obj->GetDataRencanaPenerimaanById($this->idDec);
        $msg			= Messenger::Instance()->Receive(__FILE__);
        if(isset($msg) AND !empty($msg[0][0])){
            $return['msg']	= $msg[0][1];
		    $return['data']	= $msg[0][0];
		    $return['css']	= $msg[0][2];
		    
            $this->data['januariadjust']   = $return['data']['adjust_januari'];
            $this->data['februariadjust']  = $return['data']['adjust_februari'];
            $this->data['maretadjust']     = $return['data']['adjust_maret'];
            $this->data['apriladjust']     = $return['data']['adjust_april'];
            $this->data['meiadjust']       = $return['data']['adjust_mei'];
            $this->data['juniadjust']      = $return['data']['adjust_juni'];
            $this->data['juliadjust']      = $return['data']['adjust_juli'];
            $this->data['agustusadjust']   = $return['data']['adjust_agustus'];
            $this->data['septemberadjust'] = $return['data']['adjust_september'];
            $this->data['oktoberadjust']   = $return['data']['adjust_oktober'];
            $this->data['novemberadjust']  = $return['data']['adjust_nopember'];
            $this->data['desemberadjust']  = $return['data']['adjust_desember'];
            
            # persen
            $this->data['pjanuariadjust']   = $return['data']['pjanuariadjust'];
            $this->data['pfebbruariadjust'] = $return['data']['pfebruariadjust'];
            $this->data['pmaretadjust']     = $return['data']['pmaretadjust'];
            $this->data['papriladjust']     = $return['data']['papriladjust'];
            $this->data['pmeiadjust']       = $return['data']['pmeiadjust'];
            $this->data['pjuniadjust']      = $return['data']['pjuniadjust'];
            $this->data['pjuliadjust']      = $return['data']['pjuliadjust'];
            $this->data['pagustusadjust']   = $return['data']['pagustusadjust'];
            $this->data['psemtemberadjust'] = $return['data']['psemtemberadjust'];
            $this->data['poktoberadjust']   = $return['data']['poktoberadjust'];
            $this->data['pnovemberadjust']  = $return['data']['pnovemberadjust'];
            $this->data['pdesemveradjust']  = $return['data']['pdesemberadjust'];
            
            $this->data['tpersen']          = $return['data']['tpersen'];
            $this->data['tnominal']         = $return['data']['total'];
        }else{
            $this->data['januariadjust']   = $this->data['januari'];
            $this->data['februariadjust']  = $this->data['februari'];
            $this->data['maretadjust']     = $this->data['maret'];
            $this->data['apriladjust']     = $this->data['april'];
            $this->data['meiadjust']       = $this->data['mei'];
            $this->data['juniadjust']      = $this->data['juni'];
            $this->data['juliadjust']      = $this->data['juli'];
            $this->data['agustusadjust']   = $this->data['agustus'];
            $this->data['septemberadjust'] = $this->data['september'];
            $this->data['oktoberadjust']   = $this->data['oktober'];
            $this->data['novemberadjust']  = $this->data['november'];
            $this->data['desemberadjust']  = $this->data['desember'];
            
            # persen
            $this->data['pjanuariadjust']   = $this->data['pjanuari'];
            $this->data['pfebruariadjust'] = $this->data['pfebruari'];
            $this->data['pmaretadjust']     = $this->data['pmaret'];
            $this->data['papriladjust']     = $this->data['papril'];
            $this->data['pmeiadjust']       = $this->data['pmei'];
            $this->data['pjuniadjust']      = $this->data['pjuni'];
            $this->data['pjuliadjust']      = $this->data['pjuli'];
            $this->data['pagustusadjust']   = $this->data['pagustus'];
            $this->data['psemtemberadjust'] = $this->data['psemtember'];
            $this->data['poktoberadjust']   = $this->data['poktober'];
            $this->data['pnovemberadjust']  = $this->data['pnovember'];
            $this->data['pdesemveradjust']  = $this->data['pdesember'];
            
        }
        return $return;
    }
    
    function ParseTemplate($data = null)
    {
        $msg        = $data['msg'];
        $style      = $data['css'];
        $this->mrTemplate->AddVar('content','BUT_VALUE','hide');
        if($msg):
		    $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
		    $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $msg);
		    $this->mrTemplate->AddVar('warning_box', 'STYLE', $style);
		    $this->mrTemplate->AddVar('content','BUT_VALUE','show');
	    endif;
        $url_action = Dispatcher::Instance()->GetUrl(
            'rencana_penerimaan_adjust',
            'Adjust',
            'do',
            'html'
        );
        
        
        $this->mrTemplate->AddVar('content','URL_ACTION',$url_action);
        
        $dataList   = $this->data;
        $dataList['pagu']           = intval($dataList['pagu']);
        $dataList['totalpagu']      = number_format(floatval($dataList['totalpagu']), 2, ',','.');
        $dataList['totalterima']    = number_format(floatval($dataList['totalterima']), 2, ',', '.');
        $dataList['tarif']          = number_format(floatval($dataList['tarif']), 2, ',', '.');
        $dataList['adjustment']     = floatval(20000);
        $dataList['dataid']         = $this->idDec;
        foreach($dataList as $key => $val){
            $this->mrTemplate->AddVar('content',strtoupper($key),$val);
        }
    }

#TAHUN_ANGGARAN_ID
#TAHUN_ANGGARAN_LABEL
#UNITKERJA_ID
#UNITKERJA_LABEL
#PENERIMAAN_ID
#KODE_PENERIMAAN
#NAMA_PENERIMAAN
#TOTAL
#JANUARI
#FEBRUARI
#MARET
#APRIL
#MEI
#JUNI
#JULI
#AGUSTUS
#SEPTEMBER
#OKTOBER
#NOVEMBER
#DESEMBER
#VOLUME
#TARIF
#TOTALTERIMA
#PAGU
#TOTALPAGU
#KETERANGAN
#PJANUARI
#PFEBRUARI
#PMARET
#PAPRIL
#PMEI
#PJUNI
#PJULI
#PAGUSTUS
#PSEPTEMBER
#POKTOBER
#PNOVEMBER
#PDESEMBER
#TNOMINAL
#TPERSEN
#ADJUSTMENT
}
?>
