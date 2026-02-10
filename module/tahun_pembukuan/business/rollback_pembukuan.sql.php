<?php
$sql['get_tahun_pembukuan_aktif'] = "
SELECT
    *
FROM tahun_pembukuan_periode
WHERE tppIsBukaBuku = 'Y'
";

$sql['get_tahun_pembukuan_sebelumnya'] = "
SELECT
    *
FROM tahun_pembukuan_periode
WHERE tppId < %s
ORDER BY tppId DESC
LIMIT 0, 1
";

$sql['delete_buku_besar'] = "
DELETE
    bb
FROM `buku_besar` AS bb
INNER JOIN (
    SELECT
        tppId AS id
    FROM `tahun_pembukuan_periode`
    WHERE tppIsBukaBuku = 'Y' #status tahun pembukuan aktif pada table `tahun_pembukuan_periode`
   LIMIT 1
) AS tp
    ON tp.`id` = bb.`bbTppId`
WHERE
    bb.`bbTppId` = tp.`id`
";

$sql['delete_buku_besar_his'] = "
DELETE bbh
FROM `buku_besar_his` AS bbh
WHERE
    bbh.`bbTppId` = (
        SELECT
            tppId AS id
        FROM 
            `tahun_pembukuan_periode`
        WHERE tppIsBukaBuku = 'Y'
        LIMIT 1
    )
    AND bbPembukuanRefId IS NOT NULL
";

$sql['set_saldo_awal_pembukuan_aktif'] = "
INSERT INTO `buku_besar`
(
    `bbTppId`,
    `bbTanggal`,
    `bbCoaId`,
    `bbSubaccPertamaKode`,
    `bbSubaccKeduaKode`,
    `bbSubaccKetigaKode`,
    `bbSubaccKeempatKode`,
    `bbSubaccKelimaKode`,
    `bbSubaccKeenamKode`,
    `bbSubaccKetujuhKode`,
    `bbSaldoAwal`,
    `bbDebet`,
    `bbKredit`,
    `bbSaldo`,
    `bbSaldoAkhir`,
    `bbUserId`
)
SELECT
    (
        SELECT tpp.`id` FROM(SELECT tppId AS id FROM `tahun_pembukuan_periode` WHERE tppIsBukaBuku = 'Y' LIMIT 1) AS tpp
    ),
    MAX(bbTanggal),
    bbCoaId,
    IFNULL(bbhisSubaccPertamaKode, '00'),
    IFNULL(bbhisSubaccKeduaKode, '00'),
    IFNULL(bbhisSubaccKetigaKode, '00'),
    IFNULL(bbhisSubaccKeempatKode, '00'),
    IFNULL(bbhisSubaccKelimaKode, '00'),
    IFNULL(bbhisSubaccKeenamKode, '00'),
    IFNULL(bbhisSubaccKetujuhKode, '00'),
    '0.00',
    SUM(bbDebet),
    SUM(bbKredit),
    SUM(bbSaldo),
    SUM(bbSaldo),
    bbUserId
FROM `buku_besar_his`
WHERE bbIsJurnalBalik = 'T'
GROUP BY bbCoaId, bbhisSubaccPertamaKode, bbhisSubaccKeduaKode, bbhisSubaccKetigaKode,
bbhisSubaccKeempatKode, bbhisSubaccKelimaKode, bbhisSubaccKeenamKode, bbhisSubaccKetujuhKode
";

$sql['set_saldo_awal_tahun_pembukuan_aktif'] = "
UPDATE `tahun_pembukuan` AS tp
INNER JOIN (
    SELECT
        tphCoaId AS coa_id,
        tphUnitkerjaId AS unit_id,
        tphSaldoAwal AS saldo_awal,
        tphDebet AS debet,
        tphKredit AS kredit,
        tphSaldo AS saldo,
        tphSaldoAkhir AS saldo_akhir,
        IFNULL(tphSubaccPertamaKode,'00') AS sub_pertama,
        IFNULL(tphSubaccKeduaKode,'00') AS sub_kedua,
        IFNULL(tphSubaccKetigaKode,'00') AS sub_ketiga,
        IFNULL(tphSubaccKeempatKode,'00') AS sub_keempat,
        IFNULL(tphSubaccKelimaKode,'00') AS sub_kelima,
        IFNULL(tphSubaccKeenamKode,'00') AS sub_keenam,
        IFNULL(tphSubaccKetujuhKode,'00') AS sub_ketujuh,
        CONCAT_WS('-',
            IFNULL(`tphSubaccPertamaKode`,'00'),
            IFNULL(`tphSubaccKeduaKode`,'00'),
            IFNULL(`tphSubaccKetigaKode`,'00'),
            IFNULL(`tphSubaccKeempatKode`,'00'),
            IFNULL(`tphSubaccKelimaKode`,'00'),
            IFNULL(`tphSubaccKeenamKode`,'00'),
            IFNULL(`tphSubaccKetujuhKode`,'00')
        ) AS subAcc
    FROM `tahun_pembukuan_hist`
    WHERE
        tphTppId = (SELECT tp_lalu.`id` FROM(SELECT MAX(tphTppId) AS id FROM `tahun_pembukuan_hist` LIMIT 1) AS tp_lalu)
    GROUP BY tphCoaId, subAcc
) AS saldo_tahun_lalu ON saldo_tahun_lalu.`coa_id` = tp.`tpCoaId`
SET
    tp.`tpUnitkerjaId` = saldo_tahun_lalu.`unit_id`,
    tp.`tpSaldoAwal` = saldo_tahun_lalu.`saldo_awal`,
    tp.`tpDebet` = saldo_tahun_lalu.`debet`,
    tp.`tpKredit` = saldo_tahun_lalu.`kredit`,
    tp.`tpSaldo` = saldo_tahun_lalu.`saldo`,
    tp.`tpSaldoAkhir` = saldo_tahun_lalu.`saldo_akhir`
WHERE
    tp.`tpCoaId` = saldo_tahun_lalu.`coa_id`
    AND tp.`tpSubaccPertamaKode` = saldo_tahun_lalu.`sub_pertama`
    AND tp.`tpSubaccKeduaKode` = saldo_tahun_lalu.`sub_kedua`
    AND tp.`tpSubaccKetigaKode` = saldo_tahun_lalu.`sub_ketiga`
    AND tp.`tpSubaccKeempatKode` = saldo_tahun_lalu.`sub_keempat`
    AND tp.`tpSubaccKelimaKode` = saldo_tahun_lalu.`sub_kelima`
    AND tp.`tpSubaccKeenamKode` = saldo_tahun_lalu.`sub_keenam`
    AND tp.`tpSubaccKetujuhKode` = saldo_tahun_lalu.`sub_ketujuh`
";

$sql['rollback_status_posting'] = "
UPDATE `pembukuan_referensi` AS pr
INNER JOIN (
    SELECT
        transId AS trans_id
    FROM transaksi AS tr
    JOIN `tahun_pembukuan_periode` AS tpp
        ON tpp.`tppId` = tr.`transTppId`
    WHERE
        tppIsBukaBuku = 'Y'
    GROUP BY tr.`transId`
) AS trans
    ON trans.`trans_id` = pr.`prTransId`
SET pr.`prIsPosting` = 'T' #ubah status posting menjadi BELUM POSTING
WHERE
    pr.`prTransId` = trans.`trans_id`
";

$sql['set_non_aktif_tpp'] ="
UPDATE
    `tahun_pembukuan_periode`
SET
    tppIsBukaBuku = 'T'
";

$sql['set_aktif_tpp_sebelumnya'] = "
UPDATE
    `tahun_pembukuan_periode`
SET
    tppIsBukaBuku = 'Y'
WHERE
    tppId = '%s'
";

$sql['update_tpp_buku_besar'] = "
UPDATE
    buku_besar
SET
    bbTppId = '%s'
";

$sql['update_tpp_tahun_pembukuan'] = "
UPDATE
    tahun_pembukuan
SET tpTppId = '%s'
";

$sql['delete_history_tpp_sebelumnya'] = "
DELETE FROM
    `tahun_pembukuan_hist`
WHERE tphTppId = '%s'
";

$sql['set_saldo_tahun_pembukuan_kosong'] = "
UPDATE `tahun_pembukuan` 
SET
   `tpSaldoAwal` = '0.00',
   `tpDebet` = '0.00',
   `tpKredit` = '0.00',
   `tpSaldo` = '0.00',
   `tpSaldoAkhir` = '0.00'
";

$sql['update_saldo_tahun_pembukuan_sebelumnya'] = "
UPDATE `tahun_pembukuan` AS tp
INNER JOIN (
   SELECT
      tphCoaId AS coa_id,
      tphUnitkerjaId AS unit_id,
      tphSaldoAwal AS saldo_awal,
      tphDebet AS debet,
      tphKredit AS kredit,
      tphSaldo AS saldo,
      tphSaldoAkhir AS saldo_akhir,
      IFNULL(tphSubaccPertamaKode,'00') AS sub_pertama,
      IFNULL(tphSubaccKeduaKode,'00') AS sub_kedua,
      IFNULL(tphSubaccKetigaKode,'00') AS sub_ketiga,
      IFNULL(tphSubaccKeempatKode,'00') AS sub_keempat,
      IFNULL(tphSubaccKelimaKode,'00') AS sub_kelima,
      IFNULL(tphSubaccKeenamKode,'00') AS sub_keenam,
      IFNULL(tphSubaccKetujuhKode,'00') AS sub_ketujuh,
      CONCAT_WS('-',
          IFNULL(`tphSubaccPertamaKode`,'00'),
          IFNULL(`tphSubaccKeduaKode`,'00'),
          IFNULL(`tphSubaccKetigaKode`,'00'),
          IFNULL(`tphSubaccKeempatKode`,'00'),
          IFNULL(`tphSubaccKelimaKode`,'00'),
          IFNULL(`tphSubaccKeenamKode`,'00'),
          IFNULL(`tphSubaccKetujuhKode`,'00')
      ) AS subAcc
   FROM `tahun_pembukuan_hist`
   WHERE
      tphTppId = '%s'
   GROUP BY tphCoaId,subAcc
) AS saldo_tahun_lalu ON saldo_tahun_lalu.`coa_id` = tp.`tpCoaId`
SET
   tp.`tpUnitkerjaId` = saldo_tahun_lalu.`unit_id`,
   tp.`tpSaldoAwal` = saldo_tahun_lalu.`saldo_awal`,
   tp.`tpDebet` = saldo_tahun_lalu.`debet`,
   tp.`tpKredit` = saldo_tahun_lalu.`kredit`,
   tp.`tpSaldo` = saldo_tahun_lalu.`saldo`,
   tp.`tpSaldoAkhir` = saldo_tahun_lalu.`saldo_akhir`
 WHERE
    tp.`tpCoaId` = saldo_tahun_lalu.`coa_id`
    AND tp.`tpSubaccPertamaKode` = saldo_tahun_lalu.`sub_pertama`
    AND tp.`tpSubaccKeduaKode` = saldo_tahun_lalu.`sub_kedua`
    AND tp.`tpSubaccKetigaKode` = saldo_tahun_lalu.`sub_ketiga`
    AND tp.`tpSubaccKeempatKode` = saldo_tahun_lalu.`sub_keempat`
    AND tp.`tpSubaccKelimaKode` = saldo_tahun_lalu.`sub_kelima`
    AND tp.`tpSubaccKeenamKode` = saldo_tahun_lalu.`sub_keenam`
    AND tp.`tpSubaccKetujuhKode` = saldo_tahun_lalu.`sub_ketujuh`
";

$sql['set_rolledback_tpp_aktif'] = "
UPDATE
    tahun_pembukuan_periode
SET
    tppIsRolledBack = 'Y'
WHERE tppId = '%s'
";

$sql['insert_log_rollback'] = "
INSERT INTO log_rollback_tahun_pembukuan
SET
    logRollbackUserId ='%s',
    logRollbackTanggal = NOW(),
    logRollbackKeterangan = '%s'
";
