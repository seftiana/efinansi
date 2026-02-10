<?php
    require_once GTFWConfiguration::GetValue('application','docroot').
    'module/approval_pencairan/business/Spm.class.php';

    #doc
    #    classname:    ProcessSpm
    #    scope:        PUBLIC
    # extends
    # construct:
    #/doc

    class ProcessSpm
    {
        public $obj;
        public $_POST;
        public $pageView;
        public $pageInput;

        public $cssDone = "notebox-done";
        public $cssFail = "notebox-warning";

        #    Constructor
        function __construct ()
        {
            # code...
            $this->obj      = new Spm();
            $this->_POST    = $_POST->AsArray();
            $this->pageView = Dispatcher::Instance()->GetUrl('approval_pencairan','ApprovalPencairan','view','html');
            $this->pageInput    = Dispatcher::Instance()->GetUrl('approval_pencairan','InputSpm','view','html');
        }
        ###

        function check()
        {
            if (isset($this->_POST['btnSubmit']))
            {
                # code...
                // jika ada proses penyimpanan check data yang di kirimkan
                if (isset($this->_POST['btnSubmit']))
                {
                    $cara_bayar     = $this->_POST['cara_bayar'];
                    $jenis_bayar    = $this->_POST['jenis_bayar'];
                    $sifat_bayar    = $this->_POST['sifat_bayar'];
                    $nama           = trim($this->_POST['nama']);
                    if (empty($cara_bayar) OR empty($jenis_bayar) OR empty($sifat_bayar) OR empty($nama))
                    {
                        # code...
                        return "dataEmpty";
                    }
                    else
                    {
                        # code...
                        return true;
                    }
                }
            }
        }

        function Add()
        {
            $check          = $this->check();

            $cara_bayar     = $this->_POST['cara_bayar'];
            $jenis_bayar    = $this->_POST['jenis_bayar'];
            $sifat_bayar    = $this->_POST['sifat_bayar'];
            $nama           = trim($this->_POST['nama']);
            $npwp           = trim($this->_POST['npwp']);
            $rekening       = trim($this->_POST['rekening']);
            $bank           = trim($this->_POST['bank']);
            $uraian         = trim($this->_POST['uraian']);
            $nominal_spm    = $this->_POST['nominal'];
            $detil_id       = $this->_POST['id_detil'];
            $nominal_detil  = $this->_POST['nominal_detil'];
            $basId          = $this->_POST['kode_penerimaan'];
            $nominal_pajak  = $this->_POST['nominal_pajak'];
            $userId 		= trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());

            if ($check === true)
            {
                # code...
                // jika semua data yang mandatory sudah terpenuhi
                $add_spm    = $this->obj->DoInsertSpm(
                                                    $cara_bayar,
                                                    $jenis_bayar,
                                                    $sifat_bayar,
                                                    $nama,
                                                    $npwp,
                                                    $rekening,
                                                    $bank,
                                                    $uraian,
                                                    $nominal_spm,
                                                    $basId,
                                                    $nominal_pajak,
                                                    $userId
                                                    );
                if ($add_spm)
                {
                    $spm_id     = $this->obj->LastSpmId();
                    for ($i = 0; $i < count($detil_id); $i++)
                    {
                        # code...
                        $insert_spm_det = $this->obj->InsertSpmDet($spm_id,$detil_id[$i],$nominal_detil[$i],$userId);
                        //echo 'id = '.$detil_id[$i].'<br />';
                        //echo 'nominal = '.$nominal_detil[$i].'<br />';
                    }

                    if ($insert_spm_det)
                    {
                        # code...
                        Messenger::Instance()->Send('approval_pencairan', 'ApprovalPencairan', 'view', 'html',
                        array($this->_POST,'Proses Berhasil', $this->cssDone)
                        ,Messenger::NextRequest);
                        return $this->pageView;
                    }
                    else
                    {
                        # code...
                        $this->obj->DeleteSpm($spm_id);
                        Messenger::Instance()->Send('approval_pencairan', 'InputSpm', 'view', 'html',
                        array($this->_POST,'Proses gagal'.$spm_id, $this->cssFail)
                        ,Messenger::NextRequest);
                        return $this->pageInput.'&dataId='.$this->_POST['dataId'];
                    }

                }
                else
                {
                    Messenger::Instance()->Send('approval_pencairan', 'InputSpm', 'view', 'html',
                    array($this->_POST,'Proses Gagal', $this->cssFail)
                    ,Messenger::NextRequest);
                    return $this->pageInput.'&dataId='.$this->_POST['dataId'];
                }

            }
            elseif ($check == 'dataEmpty')
            {
                # code...
                // jika data yang di perlukan tidak ada
                Messenger::Instance()->Send('approval_pencairan', 'InputSpm', 'view', 'html',
                array($this->_POST,'Isi data pada field yang mempunyai tanda bintang (*)', $this->cssFail)
                ,Messenger::NextRequest);
                return $this->pageInput.'&dataId='.$this->_POST['dataId'];
            }
            else
            {
                # code...
                return $this->pageView;
            }

            return $this->pageView;
        }

        function Update()
        {
            $check  = $this->check();
            $cara_bayar     = $this->_POST['cara_bayar'];
            $jenis_bayar    = $this->_POST['jenis_bayar'];
            $sifat_bayar    = $this->_POST['sifat_bayar'];
            $nama           = trim($this->_POST['nama']);
            $npwp           = trim($this->_POST['npwp']);
            $rekening       = trim($this->_POST['rekening']);
            $bank           = trim($this->_POST['bank']);
            $uraian         = trim($this->_POST['uraian']);
            $nominal_spm    = $this->_POST['nominal'];
            $detil_id       = $this->_POST['id_detil'];
            $nominal_detil  = $this->_POST['nominal_detil'];
            $basId          = $this->_POST['kode_penerimaan'];
            $nominal_pajak  = $this->_POST['nominal_pajak'];
            $userId 		= trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
            $spmId          = $this->_POST['spmId'];

            if ($check === true)
            {
                # code...
                // jika semua field yang di butuhkan sudah terpenuhi, update data spm
                $update     = $this->obj->UpdateSpm($cara_bayar,$jenis_bayar,$sifat_bayar,$nama,
                                                $npwp,$rekening,$bank,$uraian,$nominal_spm,$basId,$nominal_pajak,$userId,$spmId);
                if ($update)
                {
                    # code...
                    // delete data spm detail dan masukkan data yang baru
                    if ($this->obj->DeleteSpmDetBySpmId($spmId))
                    {
                        # code...
                        for ($i = 0; $i < count($detil_id); $i++)
                        {
                            # code...
                            $insert_spm_det = $this->obj->InsertSpmDet($spmId,$detil_id[$i],$nominal_detil[$i],$userId);
                            //echo 'id = '.$detil_id[$i].'<br />';
                            //echo 'nominal = '.$nominal_detil[$i].'<br />';
                        }
                    }
                    if ($insert_spm_det)
                    {
                        # code...
                        Messenger::Instance()->Send('approval_pencairan', 'ApprovalPencairan', 'view', 'html',
                        array($this->_POST,'Proses berhasil', $this->cssDone)
                        ,Messenger::NextRequest);
                        return $this->pageView;
                    }
                    else
                    {
                        # code...
                        Messenger::Instance()->Send('approval_pencairan', 'InputSpm', 'view', 'html',
                        array($this->_POST,'Proses gagal'.$spm_id, $this->cssFail)
                        ,Messenger::NextRequest);
                        return $this->pageInput.'&dataId='.$this->_POST['dataId'].'&spmId='.$spmId;
                    }


                }
                else
                {
                    # code...
                    Messenger::Instance()->Send('approval_pencairan', 'InputSpm', 'view', 'html',
                    array($this->_POST,'Proses Gagal'.$spm_id, $this->cssFail)
                    ,Messenger::NextRequest);
                    return $this->pageInput.'&dataId='.$this->_POST['dataId'].'&spmId='.$spmId;
                }
            }
            elseif ($check == 'dataEmpty')
            {
                # code...
                // jika data yang di perlukan tidak ada
                Messenger::Instance()->Send('approval_pencairan', 'InputSpm', 'view', 'html',
                array($this->_POST,'Isi data pada field yang mempunyai tanda bintang (*)', $this->cssFail)
                ,Messenger::NextRequest);
                return $this->pageInput.'&dataId='.$this->_POST['dataId'].'&spmId='.$spmId;
            }
            else
            {
                # code...
                return $this->pageView;
            }

            return $this->pageView;
        }
    }
    ###
?>
