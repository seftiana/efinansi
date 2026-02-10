<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/rencana_penerimaan_adjust_history/business/RencanaPenerimaanAdjustHistory.class.php';

#doc
#    classname:    ViewFormRencanaPenerimaanAdjustHistory
#    scope:        PUBLIC
#
#/doc

class ViewFormRencanaPenerimaanAdjust extends HtmlResponse
{
    #    internal variables
    public $obj;
    public $data;
    public $message;
    public $style;
    public $decId;
    #    Constructor
    function __construct ()
    {
        # code...
        $this->obj      = new RencanaPenerimaanAdjustHistory();
        $this->decId    = Dispatcher::Instance()->Decrypt($_GET['dataId']);
    }
    
    function TemplateModule()
    {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
        'module/rencana_penerimaan_adjust_history/template/');
        $this->SetTemplateFile('view_form_rencana_penerimaan_adjust.html');
    }
    
    function ProcessRequest()
    {
        $this->data     = $this->obj->GetDataById($this->decId);
        $this->data['januari']   = $this->data['januari_l'];
        $this->data['februari']  = $this->data['februari_l'];
        $this->data['maret']     = $this->data['maret_l'];
        $this->data['april']     = $this->data['april_l'];
        $this->data['mei']       = $this->data['mei_l'];
        $this->data['juni']      = $this->data['juni_l'];
        $this->data['juli']      = $this->data['juli_l'];
        $this->data['agustus']   = $this->data['agustus_l'];
        $this->data['september'] = $this->data['september_l'];
        $this->data['oktober']   = $this->data['oktober_l'];
        $this->data['november']  = $this->data['nopember_l'];
        $this->data['desember']  = $this->data['desember_l'];
        $this->data['tnominal']  = $this->data['total'];
        
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
            $this->data['januariadjust']   = $this->data['januari_b'];
            $this->data['februariadjust']  = $this->data['februari_b'];
            $this->data['maretadjust']     = $this->data['maret_b'];
            $this->data['apriladjust']     = $this->data['april_b'];
            $this->data['meiadjust']       = $this->data['mei_b'];
            $this->data['juniadjust']      = $this->data['juni_b'];
            $this->data['juliadjust']      = $this->data['juli_b'];
            $this->data['agustusadjust']   = $this->data['agustus_b'];
            $this->data['septemberadjust'] = $this->data['september_b'];
            $this->data['oktoberadjust']   = $this->data['oktober_b'];
            $this->data['novemberadjust']  = $this->data['nopember_b'];
            $this->data['desemberadjust']  = $this->data['desember_b'];
            
            # persen
            $this->data['pjanuariadjust']   = ($this->data['januari_b']/intval($this->data['totalterima']))*100;
            $this->data['pfebruariadjust'] = ($this->data['februari_b']/intval($this->data['totalterima']))*100;
            $this->data['pmaretadjust']     = ($this->data['maret_b']/intval($this->data['totalterima']))*100;
            $this->data['papriladjust']     = ($this->data['april_b']/intval($this->data['totalterima']))*100;
            $this->data['pmeiadjust']       = ($this->data['mei_b']/intval($this->data['totalterima']))*100;
            $this->data['pjuniadjust']      = ($this->data['juni_b']/intval($this->data['totalterima']))*100;
            $this->data['pjuliadjust']      = ($this->data['juli_b']/intval($this->data['totalterima']))*100;
            $this->data['pagustusadjust']   = ($this->data['agustus_b']/intval($this->data['totalterima']))*100;
            $this->data['pseptemberadjust'] = ($this->data['september_b']/intval($this->data['totalterima']))*100;
            $this->data['poktoberadjust']   = ($this->data['oktober_b']/intval($this->data['totalterima']))*100;
            $this->data['pnovemberadjust']  = ($this->data['nopember_b']/intval($this->data['totalterima']))*100;
            $this->data['pdesemveradjust']  = ($this->data['desember_b']/intval($this->data['totalterima']))*100;
            
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
            'rencana_penerimaan_adjust_history',
            'EditAdjustment',
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

}
?>
