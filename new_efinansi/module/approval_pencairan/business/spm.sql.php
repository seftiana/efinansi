<?php
    # code
    $sql['get_cara_bayar']  = "
    SELECT
      `caraBayarId` AS id,
      CONCAT(`caraBayarKode`,' - ', `caraBayarNama`) AS `name` 
    FROM `finansi_pa_ref_cara_bayar` 
    ORDER BY id ASC
    ";
    
    $sql['get_jenis_pembayaran'] = "
    SELECT
      `jenisPembayaranId` AS id,
      CONCAT(`jenisPembayaranKode`,' - ',`jenisPembayaranNama`) AS `name`
    FROM `finansi_pa_ref_jenis_pembayaran` 
    ORDER BY id ASC
    ";
    
    $sql['get_sifat_pembayaran'] = "
    SELECT
      `sifatPembayaranId` AS id,
      CONCAT(`sifatPembayaranKode`,' - ',`sifatPembayaranNama`) AS `name` 
    FROM `finansi_pa_ref_sifat_pembayaran`
    ORDER BY sifatPembayaranKode ASC
    ";
    
    $sql['insert_into_spm'] = "
    INSERT INTO `finansi_pa_spm`
                (`spmNomor`,
                 `spmCaraBayarId`,
                 `spmJenisBayarId`,
                 `spmSifatBayarId`,
                 `spmNama`,
                 `spmNpwp`,
                 `spmRekening`,
                 `spmBank`,
                 `spmKeterangan`,
                 `spmNominal`,
                 `spmPaguBasId`,
                 `spmNominalPotongan`,
                 `spmUserId`,
                 `spmTanggal`)
    VALUES ('%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            NOW())
    ";
    
    $sql['get_max_id_spm']  = "
        SELECT @last_id := LAST_INSERT_ID() AS last_id
    ";
    
    $sql['generate_number_spm'] = "
    SELECT CONCAT(SUBSTR('000000',1,(CHAR_LENGTH('000000')-CHAR_LENGTH(COUNT(spmId)+1))),COUNT(spmId)+1,'/',YEAR(NOW())) AS number 
    FROM finansi_pa_spm WHERE 1=1
    ";
    
    $sql['get_transaksi_by_approval_id']    = "
    SELECT 
        rd.pengrealdetId AS id,
        rd.pengrealdetRncnpengeluaranId AS rncnpengeluaran_id,
        rd.pengrealdetNominalPencairan AS spp_ini,
        (SELECT 
            SUM(pengrealdetNominalPencairan) 
        FROM
            pengajuan_realisasi_detil AS tmp_rd 
            LEFT JOIN rencana_pengeluaran AS tmp_rp 
                ON tmp_rp.rncnpengeluaranId = tmp_rd.pengrealdetRncnpengeluaranId 
        WHERE tmp_rp.rncnpengeluaranMakId = rp.rncnpengeluaranMakId 
            AND tmp_rd.pengrealdetPengRealId IN 
            (SELECT 
                `spmDetRealDetId` 
            FROM
                `finansi_pa_spm_det`)) AS spp_lalu,
        (SELECT 
            SUM(
                rp.rncnpengeluaranSatuanAprove * rp.rncnpengeluaranKomponenNominalAprove
            ) AS pagu 
        FROM
            rencana_pengeluaran AS rp 
        WHERE rp.rncnpengeluaranId = rd.pengrealdetRncnpengeluaranId) AS pagu_dipa,
        rm.paguBasKode AS mak_kode,
        rm.paguBasKeterangan AS mak_nama,
        prog.`rkaklProgramKode` AS program_kode,
        prog.`rkaklProgramNama` AS program_nama,
        keg_ref.`kegrefNomor` AS keg_nomor,
        keg_ref.`kegrefNama` AS keg_nama,
        keg_ref.`kegrefLabelKode` AS keg_label_kode,
        output.`rkaklOutputKode` AS output_kode,
        output.`rkaklOutputNama` AS output_nama, 
        IFNULL(coa.coaNamaAkun,'-') AS coa_nama, 
        coa.`coaKodeAkun` AS coa_kode, 
        coa.`coaKodeSistem` AS coa_kode_sistem 
    FROM
        pengajuan_realisasi AS pengreal 
        LEFT JOIN pengajuan_realisasi_detil AS rd 
            ON pengreal.`pengrealId` = rd.`pengrealdetPengRealId` 
        JOIN rencana_pengeluaran AS rp 
            ON rp.rncnpengeluaranId = rd.pengrealdetRncnpengeluaranId 
        LEFT JOIN finansi_ref_pagu_bas AS rm 
            ON rm.paguBasId = rp.rncnpengeluaranMakId 
        LEFT JOIN `kegiatan_detail` AS kd 
            ON pengreal.`pengrealKegdetId` = kd.`kegdetId` 
        LEFT JOIN kegiatan_ref AS keg_ref 
            ON kd.`kegdetKegrefId` = keg_ref.`kegrefId` 
        LEFT JOIN finansi_ref_rkakl_output AS output 
            ON output.`rkaklOutputId` = kd.`kegdetRkaklOutputId` 
        LEFT JOIN finansi_ref_rkakl_prog AS prog 
            ON kd.`kegdetProgramRkaklId` = prog.`rkaklProgramId` 
        LEFT JOIN finansi_coa_mak AS cm 
            ON cm.`paguBasId` = rm.`paguBasId` 
        LEFT JOIN coa 
            ON coa.`coaId` = cm.`coaId`
    WHERE pengreal.`pengrealId` = '%s'
    ";
    
    $sql['insert_into_spm_det'] = "
    INSERT INTO `finansi_pa_spm_det`
                (`spmDetSpmId`,
                 `spmDetRealDetId`,
                 `spmDetNominal`,
                 `spmDetUserId`)
    VALUES ('%s',
            '%s',
            '%s',
            '%s')
    ";
    
    $sql['delete_spm']  = "
    DELETE
    FROM `finansi_pa_spm`
    WHERE `spmId` = '%s'
    ";
    
    $sql['get_spm_by_spm_id']   = "
    SELECT
      `spmId` AS spm_id,
      `spmNomor` AS spm_nomor,
      `spmCaraBayarId` AS cara_bayar_id,
      `spmJenisBayarId` AS jenis_bayar_id,
      `spmSifatBayarId` AS sifat_bayar_id,
      `spmNama` AS spm_nama,
      `spmNpwp` AS spm_npwp,
      `spmRekening` AS spm_rekening,
      `spmBank` AS spm_bank,
      `spmKeterangan` AS spm_keterangan,
      `spmNominal` AS spm_nominal,
      `spmUserId` AS user_id,
      `spmTanggal` AS spm_tanggal,
      `spmTglUbah` AS spm_tgl_ubah, 
      cb.`caraBayarKode` AS cara_bayar_kode, 
      cb.`caraBayarNama` AS cara_bayar_nama, 
      jp.`jenisPembayaranKode` AS jenis_bayar_kode, 
      jp.`jenisPembayaranNama` AS jenis_bayar_nama, 
      sp.`sifatPembayaranKode` AS sifat_bayar_kode, 
      sp.`sifatPembayaranNama` AS sifat_bayar_nama, 
      bas.`paguBasId` AS pajakId, 
      bas.`paguBasKode` AS pajak_kode, 
      bas.`paguBasKeterangan` AS pajak_nama, 
      spm.`spmNominalPotongan` AS nominal_potongan 
    FROM `finansi_pa_spm` AS spm 
    LEFT JOIN finansi_pa_ref_cara_bayar AS cb 
    ON cb.`caraBayarId` = spm.`spmCaraBayarId` 
    LEFT JOIN finansi_pa_ref_jenis_pembayaran AS jp 
    ON jp.`jenisPembayaranId` = spm.`spmJenisBayarId` 
    LEFT JOIN finansi_pa_ref_sifat_pembayaran AS sp 
    ON sp.`sifatPembayaranId` = spm.`spmSifatBayarId` 
    LEFT JOIN finansi_ref_pagu_bas AS bas 
    ON spm.`spmPaguBasId` = bas.`paguBasId`
    WHERE spmId = '%s' 
    ";
    
    $sql['update_spm']  = "
    UPDATE `finansi_pa_spm`
    SET 
      `spmCaraBayarId` = '%s',
      `spmJenisBayarId` = '%s',
      `spmSifatBayarId` = '%s',
      `spmNama` = '%s',
      `spmNpwp` = '%s',
      `spmRekening` = '%s',
      `spmBank` = '%s',
      `spmKeterangan` = '%s',
      `spmNominal` = '%s',
      `spmPaguBasId` = '%s', 
      `spmNominalPotongan` = '%s', 
      `spmUserId` = '%s'
    WHERE `spmId` = '%s'
    ";
    
    $sql['delete_spm_det_by_spm_id']    = "
    DELETE
    FROM `finansi_pa_spm_det`
    WHERE `spmDetSpmId` = '%s'
    ";
    
    $sql['get_dipa']    = "
    SELECT
      `dipaId` AS id,
      `dipaNomor` AS dipa_nama,
      `dipaTanggal` AS dipa_tanggal,
      `dipaNominal` AS dipa_nominal 
    FROM `finansi_pa_dipa` 
    WHERE dipaIsAktif = 'Y'  
    LIMIT 0,1
    ";
    
    $sql['get_tipe_pajak'] = "
        SELECT 
            pb.paguBasId AS id, 
            CONCAT(pb.paguBasKode,' - ',pb.paguBasKeterangan) AS `name` 
        FROM 
            finansi_ref_pagu_bas AS pb
        LEFT JOIN finansi_ref_pagu_bas_tipe_bas AS tb
            ON pb.paguBasId = tb.`paguBasId` 
        LEFT JOIN finansi_ref_pagu_bas_tipe AS bt 
            ON bt.`paguBasTipeId` = tb.`paguBasTipeId` 
        WHERE 
            pb.`paguBasStatusAktif` = 'Y' 
            AND bt.`paguBasTipeNama` LIKE '%pajak%'
        ORDER BY pb.paguBasKode ASC
    ";
    
    $sql['get_pajak_spm'] = "
    SELECT 
        spm.spmId AS spm_id,
        spm.`spmPaguBasId` AS pajak_id, 
        spm.`spmNominalPotongan` AS nominal_pajak, 
        bas.`paguBasKode` AS kode_pajak, 
        bas.`paguBasKeterangan` AS nama_pajak 
    FROM finansi_pa_spm AS spm 
    LEFT JOIN `finansi_ref_pagu_bas` AS bas 
        ON bas.`paguBasId` = spm.`spmPaguBasId`
    WHERE spm.`spmId` = '%s'
    ";
?>
