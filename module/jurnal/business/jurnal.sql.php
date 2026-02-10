<?php

$sql['get_data_cetak']="
SELECT *,
   t.prId          	AS id,
   t.tanggal     	AS tanggal,
   pd.pdKeterangan  AS keterangan,
   pd.pdNilai       AS nilai,
   pd.pdStatus      AS tipeakun,
   t.prIsPosting   AS is_posting,
   t.prDelIsLocked AS is_locked,
   t.RealName      AS petugas_entri,
   c.coaKodeAkun    AS rekening_kode,
   c.coaNamaAkun    AS rekening_nama,
   t.prKeterangan AS catatan,
   CONCAT_WS(
      '-',
      TRIM(BOTH FROM pdSubaccPertamaKode),
      TRIM(BOTH FROM pdSubaccKeduaKode),
      TRIM(BOTH FROM pdSubaccKetigaKode),
      TRIM(BOTH FROM pdSubaccKeempatKode),
      TRIM(BOTH FROM pdSubaccKelimaKode),
      TRIM(BOTH FROM pdSubaccKeenamKode),
      TRIM(BOTH FROM pdSubaccKetujuhKode)
   ) AS subAccount
FROM (SELECT
   		transId,
   		transReferensi AS referensi,
   		transIsJurnal  AS is_jurnal,
   		IF(ttNamaJurnal IN ('BKK','BKM'),transDueDate,transTanggalEntri) AS tanggal,
   		prId,
   		prIsPosting,
   		prDelIsLocked,
   		RealName,
   		transCatatan,
        prKeterangan
	  FROM transaksi
	  JOIN pembukuan_referensi ON transId = prTransId
	  JOIN transaksi_tipe_ref ON ttId = transTtId
   	  JOIN gtfw_user ON UserId = prUserId
        JOIN (SELECT
            pdPrId AS id,
            CONCAT_WS(
               '-',
               TRIM(BOTH FROM pdSubaccPertamaKode),
               TRIM(BOTH FROM pdSubaccKeduaKode),
               TRIM(BOTH FROM pdSubaccKetigaKode),
               TRIM(BOTH FROM pdSubaccKeempatKode),
               TRIM(BOTH FROM pdSubaccKelimaKode),
               TRIM(BOTH FROM pdSubaccKeenamKode),
               TRIM(BOTH FROM pdSubaccKetujuhKode)
            ) AS pdSubAccount
         FROM pembukuan_detail
         JOIN coa
            ON coaId = pdCoaId
         GROUP BY pdPrId
         ) AS detailPembukuan ON detailPembukuan.id = prId
   	  WHERE 
   	  	transReferensi LIKE '%s'
   	  	AND (IF(ttNamaJurnal IN ('BKK','BKM'),transDueDate,transTanggalEntri) BETWEEN '%s' AND '%s')
         AND (pdSubAccount LIKE '%s' OR 1 = %s)
	  ORDER BY transReferensi ASC
) t 
JOIN pembukuan_detail pd ON t.prId = pd.pdPrId
JOIN coa c ON pd.pdCoaId = c.coaId
# WHERE pr.prIsJurnalBalik <> 1
ORDER BY t.referensi ASC
";

//===GET===
$sql['get_combo_coa']="
SELECT
 coaId AS id,
 coaNamaAkun AS name
FROM
 coa
WHERE
 coaIsDebetPositif LIKE %s AND
 coaId NOT IN (SELECT DISTINCT(coaParentAkun) FROM coa)
";

$sql['get_referensi_transaksi']="
SELECT
   t.transId AS transaksi_id,
   t.transDueDate AS transaksi_tanggal,
   t.transReferensi AS transaksi_referensi,
   t.transCatatan AS transaksi_catatan,
   t.transNilai AS transaksi_nilai

FROM
 transaksi t
 JOIN tahun_anggaran ta ON t.transThanggarId = ta.thanggarId

WHERE
 ta.thanggarIsAktif ='Y' AND
 t.transIsJurnal ='T'
";

$sql['get_data']="
SELECT *,
	t.prId          	AS id,
	t.tanggal,
   	pd.pdKeterangan  	AS keterangan,
   	pd.pdNilai       	AS nilai,
   	pd.pdStatus      	AS tipeakun,
   	t.prIsPosting   	AS is_posting,
   	t.prDelIsLocked 	AS is_locked,
   	t.RealName      	AS petugas_entri,
   	c.coaKodeAkun    	AS rekening_kode,
   	c.coaNamaAkun    	AS rekening_nama,
   	t.prKeterangan AS catatan,
      CONCAT_WS(
         '-',
         TRIM(BOTH FROM pdSubaccPertamaKode),
         TRIM(BOTH FROM pdSubaccKeduaKode),
         TRIM(BOTH FROM pdSubaccKetigaKode),
         TRIM(BOTH FROM pdSubaccKeempatKode),
         TRIM(BOTH FROM pdSubaccKelimaKode),
         TRIM(BOTH FROM pdSubaccKeenamKode),
         TRIM(BOTH FROM pdSubaccKetujuhKode)
      ) AS subAccount
FROM (SELECT
   		transId,
   		transReferensi AS referensi,
   		transIsJurnal  AS is_jurnal,
   		IF(ttNamaJurnal IN ('BKK','BKM'),transDueDate,transTanggalEntri) AS tanggal,
   		prId,
   		prIsPosting,
   		prDelIsLocked,
   		RealName,
        transCatatan,
        prKeterangan
   	  FROM transaksi
	  JOIN pembukuan_referensi ON transId = prTransId
   	  JOIN transaksi_tipe_ref ON ttId = transTtId
   	  JOIN gtfw_user ON UserId = prUserId
        JOIN (SELECT
            pdPrId AS id,
            CONCAT_WS(
               '-',
               TRIM(BOTH FROM pdSubaccPertamaKode),
               TRIM(BOTH FROM pdSubaccKeduaKode),
               TRIM(BOTH FROM pdSubaccKetigaKode),
               TRIM(BOTH FROM pdSubaccKeempatKode),
               TRIM(BOTH FROM pdSubaccKelimaKode),
               TRIM(BOTH FROM pdSubaccKeenamKode),
               TRIM(BOTH FROM pdSubaccKetujuhKode)
            ) AS pdSubAccount
         FROM pembukuan_detail
         JOIN coa
            ON coaId = pdCoaId
         GROUP BY pdPrId
         ) AS detailPembukuan ON detailPembukuan.id = prId
   	  WHERE transReferensi LIKE '%s'
      	# AND prIsJurnalBalik != 1
       	AND (IF(ttNamaJurnal IN ('BKK','BKM'),transDueDate,transTanggalEntri) BETWEEN '%s' AND '%s')
      AND (pdSubAccount LIKE '%s' OR 1 = %s)
	  ORDER BY transReferensi ASC
	  LIMIT %s, %s
) t 
JOIN pembukuan_detail pd ON t.prId = pd.pdPrId
JOIN coa c ON pd.pdCoaId = c.coaId
ORDER BY t.referensi ASC
";

$sql['get_data_all'] = "
SELECT *,
    t.prId          AS id,
    t.tanggal,
    pd.pdKeterangan  AS keterangan,
    pd.pdNilai       AS nilai,
    pd.pdStatus      AS tipeakun,
    t.prIsPosting   AS is_posting,
    t.prDelIsLocked AS is_locked,
    t.RealName      AS petugas_entri,
    c.coaKodeAkun    AS rekening_kode,
    c.coaNamaAkun    AS rekening_nama,
    t.prKeterangan AS catatan
FROM (SELECT
        transId,
        transReferensi AS referensi,
        transIsJurnal  AS is_jurnal,
        IF(ttNamaJurnal IN ('BKK','BKM'),transDueDate,transTanggalEntri) AS tanggal,
        prId,
        prIsPosting,
        prDelIsLocked,
        RealName,
        transCatatan,
        prKeterangan
      FROM transaksi
      JOIN pembukuan_referensi ON transId = prTransId
      JOIN transaksi_tipe_ref ON ttId = transTtId
      JOIN gtfw_user ON UserId = prUserId
      WHERE
      (IF(ttNamaJurnal IN ('BKK','BKM'),transDueDate,transTanggalEntri) BETWEEN '%s' AND '%s')
      ORDER BY transReferensi ASC
      LIMIT %s, %s
) t 
JOIN pembukuan_detail pd ON t.prId = pd.pdPrId
JOIN coa c ON pd.pdCoaId = c.coaId
# WHERE pr.prIsJurnalBalik <> 1
ORDER BY t.referensi ASC
";

$sql['get_data_all_cetak'] = "
SELECT *,
	t.prId          AS id,
	t.tanggal,
   	pd.pdKeterangan  AS keterangan,
   	pd.pdNilai       AS nilai,
   	pd.pdStatus      AS tipeakun,
   	t.prIsPosting   AS is_posting,
   	t.prDelIsLocked AS is_locked,
   	t.RealName      AS petugas_entri,
   	c.coaKodeAkun    AS rekening_kode,
   	c.coaNamaAkun    AS rekening_nama,
   	t.prKeterangan AS catatan
FROM (SELECT
   		transId,
   		transReferensi AS referensi,
   		transIsJurnal  AS is_jurnal,
   		IF(ttNamaJurnal IN ('BKK','BKM'),transDueDate,transTanggalEntri) AS tanggal,
   		prId,
   		prIsPosting,
   		prDelIsLocked,
   		RealName,
        transCatatan,
        prKeterangan
	  FROM transaksi
	  JOIN pembukuan_referensi ON transId = prTransId
   	  JOIN transaksi_tipe_ref ON ttId = transTtId
   	  JOIN gtfw_user ON UserId = prUserId
   	  WHERE
   	  (IF(ttNamaJurnal IN ('BKK','BKM'),transDueDate,transTanggalEntri) BETWEEN '%s' AND '%s')
	  ORDER BY transReferensi ASC
) t 
JOIN pembukuan_detail pd ON t.prId = pd.pdPrId
JOIN coa c ON pd.pdCoaId = c.coaId
# WHERE pr.prIsJurnalBalik <> 1
ORDER BY t.referensi ASC
";

$sql['get_count']="
SELECT
   COUNT(transId) as total
FROM
  transaksi t
  JOIN pembukuan_referensi pr ON t.transId=pr.prTransId
  JOIN transaksi_tipe_ref ON ttId = transTtId
  JOIN (SELECT
      pdPrId AS id,
      CONCAT_WS(
         '-',
         TRIM(BOTH FROM pdSubaccPertamaKode),
         TRIM(BOTH FROM pdSubaccKeduaKode),
         TRIM(BOTH FROM pdSubaccKetigaKode),
         TRIM(BOTH FROM pdSubaccKeempatKode),
         TRIM(BOTH FROM pdSubaccKelimaKode),
         TRIM(BOTH FROM pdSubaccKeenamKode),
         TRIM(BOTH FROM pdSubaccKetujuhKode)
      ) AS subAccount
   FROM pembukuan_detail
   JOIN coa
      ON coaId = pdCoaId
   GROUP BY pdPrId
   ) AS detailPembukuan ON detailPembukuan.id = prId
WHERE
   transReferensi LIKE %s
   # AND pr.prIsJurnalBalik <> 1
   AND (IF(ttNamaJurnal IN ('BKK','BKM'),transDueDate,transTanggalEntri) BETWEEN '%s' AND '%s')
   AND (subAccount LIKE '%s' OR 1 = %s)
";

$sql['get_count_all'] = "
SELECT
    COUNT(DISTINCT(prId)) as total
FROM
  transaksi t
  JOIN pembukuan_referensi pr ON t.transId=pr.prTransId
  JOIN pembukuan_detail pd ON pr.prId = pd.pdPrId
# WHERE pr.prIsJurnalBalik <> 1
GROUP BY prId
";

$sql['get_min_max_tahun_pencatatan'] = "
SELECT
 YEAR(MIN(prTanggal)) - 5 AS minTahun,
 YEAR(MAX(prTanggal)) + 5 AS maxTahun
FROM
 pembukuan_referensi
";


$sql['get_data_by_id']="
SELECT
 t.transId AS referensi_id,
 t.transReferensi AS referensi_nama,
 t.transNilai AS referensi_nilai,
 t.transDueDate AS referensi_tanggal,
 pr.prId AS pembukuan_referensi_id,
 pd.pdId AS detail_id,
 pd.pdKeterangan AS detail_keterangan,
 pd.pdNilai AS detail_nilai,
 pd.pdStatus AS detail_status,
 c.coaId AS coa_id,
 c.coaKodeAkun AS coa_kode,
 c.coaNamaAkun AS coa_nama,
 c.coaIsDebetPositif AS coa_status_debet

FROM
 transaksi t
 JOIN pembukuan_referensi pr ON t.transId =pr.prTransId
 JOIN pembukuan_detail pd ON pr.prId = pdPrId
 JOIN coa c ON pd.pdCoaId = c.coaId

WHERE
 pr.prId = %s

ORDER BY pr.prId,pd.pdStatus
";

$sql['get_transaksi_by_id'] = "
   SELECT
      transId AS id,
		(IF(tempUnitId IS NULL,unitkerjaId,unitkerjaId)) AS unitkerja,
		(IF(tempUnitNama IS NULL,unitkerjaNama,CONCAT_WS('/ ',tempUnitNama, unitkerjaNama))) AS unitkerja_label,
      transTransjenId AS jenis,
      transTtId AS tipe,
      transReferensi AS no_referensi,
      transTanggalEntri AS tanggal,
      transDueDate AS due_date,
      GROUP_CONCAT(transinvoiceNomor SEPARATOR ', ') AS referensi,
      transCatatan AS catatan_transaksi,
      transNilai AS nominal,
      transPenanggungJawabNama AS penanggung_jawab,
      transIsJurnal AS is_jurnal
   FROM
      transaksi
      JOIN unit_kerja_ref ON (unitkerjaId = transUnitkerjaId)
      LEFT JOIN transaksi_invoice ON transinvoiceTransId = transId
		LEFT JOIN
			(SELECT
				unitkerjaId AS tempUnitId,
				unitkerjaKode AS tempUnitKode,
				unitkerjaNama AS tempUnitNama,
				unitkerjaParentId AS tempParentId
			FROM unit_kerja_ref WHERE unitkerjaParentId = 0) tmpUnitKerja ON(unitkerjaParentId=tempUnitId)
   WHERE
      transId='%s'
   GROUP BY transId
";

$sql['get_journal']="
SELECT
   prId,
   prTransId,
   prUserId,
   prTanggal,
   prKeterangan,
   prIsPosting,
   prDelIsLocked,
   prIsApproved,
   prIsKas,
   prBentukTransaksi,
   prIsJurnalBalik,
   pdNilai,
   IF(pdStatus='D',pdNilai,0) AS debet,
   IF(pdStatus='K',pdNilai,0) AS kredit,
   pdKeterangan AS deskripsi,
   pdStatus,
   coaKodeAkun AS kode_akun,
   coaNamaAkun AS nama_akun
FROM pembukuan_referensi
LEFT JOIN pembukuan_detail ON pdPrId = prId
LEFT JOIN coa ON pdCoaId = coaId
WHERE prId = '%s'
ORDER BY pdStatus,kode_akun
";


//===DO===
$sql['do_add_pembukuan_referensi']="
INSERT INTO `pembukuan_referensi`
   (`prTransId`, `prUserId`, `prTanggal`, `prKeterangan`, `prIsPosting`, `prDelIsLocked`, `prIsApproved` )
VALUES
   (%s,  %s,  '%s',  '%s',  'T',  'T',  'T' )
";

$sql['do_add_pembukuan_detail']="
INSERT INTO `pembukuan_detail`
   (`pdPrId`, `pdCoaId`, `pdNilai`, `pdKeterangan`, `pdStatus` )
VALUES
   (%s,  %s,  %s,  %s,  %s )
";

$sql['do_update_pembukuan_referensi']="
UPDATE `pembukuan_referensi`
SET
 `prTransId`=%s,
 `prUserId`=%s,
 `prKeterangan`=%s,
 `prIsApproved`='T'
WHERE `prId`=%s
";

$sql['do_update_pembukuan_detail']="
UPDATE
  `pembukuan_detail`
SET
  `pdCoaId`=%s,
  `pdNilai`=%s,
  `pdKeterangan`=%s
where
  `pdId`=%s
";


$sql['do_delete_pembukuan_detail']="
DELETE FROM pembukuan_detail WHERE pdPrId = %s;
";

$sql['do_delete_pembukuan_detail_single']="
DELETE FROM pembukuan_detail WHERE pdId = %s;
";

$sql['do_delete_pembukuan_referensi']="
DELETE FROM pembukuan_referensi WHERE prId = %s;
";

//LOGGER LOGGER LOGGER

$sql['do_add_log'] = "
   INSERT INTO logger(logUserId, logAlamatIp, logUpdateTerakhir, logKeterangan)
   VALUES ('%s', '%s', NOW(), '%s')
";

$sql['do_add_log_detil'] = "
   INSERT INTO logger_detail(logId, logAksiQuery)
   VALUES ('%s', '%s')
";

// untuk proses balik jurnal
$sql['get_max_pembukuan_referensi_id'] = "
SELECT
   MAX(prId) AS max_id
FROM
   pembukuan_referensi
";

$sql['update_status_jurnal'] = "
UPDATE
   transaksi
SET
   transIsJurnal = 'Y'
WHERE
   transId = %s
";

$sql['update_status_posting_saat_jurnal_balik'] = "
UPDATE
   pembukuan_referensi
SET
   prIsPosting = 'T',
   prIsApproved = 'T'
WHERE
   prId = '%s'
";

$sql['update_status_is_jurnal'] = "
UPDATE
   transaksi
SET
   transIsJurnal = 'Y'
WHERE
   transId = %s
";

//posting setelah jurnal balik
$sql['get_data_jurnal_balik'] = "
   SELECT
      prId AS pembukuan_ref_id,
      transID AS transaksi_id,
      transTanggalEntri AS transaksi_tanggal,
      transReferensi AS transaksi_referensi,
      transCatatan AS transaksi_catatan,
      pdCoaId AS coa_id,
      coaKodeAkun AS akun_kode,
      coaNamaAkun AS akun_nama,
      pdNilai AS nilai,
      pdStatus AS status_pembukuan,
      coaIsDebetPositif AS coa_status_debet,
      coaCoaKelompokId AS coa_kelompok
   FROM
      transaksi
      JOIN pembukuan_referensi ON transId = prTransId
      JOIN pembukuan_detail ON prId = pdPrId
      JOIN coa ON pdCoaId = coaId
      JOIN coa_kelompok ON coaCoaKelompokId = coaKelompokId
   WHERE
      prId = '%s'
";

$sql['cek_akun_buku_besar'] = "
   SELECT
      bbId AS bb_id,
      bbSaldoAwal AS saldo_awal,
      bbSaldoAkhir AS saldo_akhir
   FROM
      buku_besar
   WHERE
      bbCoaId = '%s'
";

//hanya mengakomodir 1 coa laba rigi saja. kalo lebih dari 1 ??
$sql['cek_akun_laba_rugi_buku_besar'] = "
   SELECT
      bbId AS bb_id,
      bbSaldoAwal AS saldo_awal,
      bbSaldoAkhir AS saldo_akhir
   FROM
      buku_besar
   WHERE
      bbCoaId = (SELECT coaId FROM coa WHERE `coaIsLabaRugiThJln` = '1')
";

$sql['get_coa_laba_rugi'] = "
   SELECT
      coaKelompokId
   FROM
      coa_kelompok
   WHERE
      coaKelompokNama = 'Pendapatan' OR coaKelompokNama = 'Biaya';
";

$sql['do_insert_buku_besar'] = "
   INSERT INTO buku_besar
      (bbTppId, bbTanggal, bbCoaId, bbSaldoAwal, bbDebet, bbKredit, bbSaldo, bbSaldoAkhir, bbUserId)
   VALUES ((SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'), now(), '%s', '%s', '%s', '%s', '%s', '%s', '%s');
";

$sql['do_insert_laba_rugi_buku_besar'] = "
   INSERT INTO buku_besar
      (bbTppId, bbTanggal, bbCoaId, bbSaldoAwal, bbDebet, bbKredit, bbSaldo, bbSaldoAkhir, bbUserId)
   VALUES ((SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'), now(), (SELECT coaId FROM coa WHERE `coaIsLabaRugiThJln` = '1' AND coaUnitkerjaId = (SELECT userunitkerjaUnitkerjaId FROM user_unit_kerja WHERE userunitkerjaUserId = %s)), '%s', '%s', '%s', '%s', '%s', '%s');
";

$sql['do_update_buku_besar'] = "
   UPDATE
      buku_besar
   SET
      bbTppId = (SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'),
      bbTanggal = now(),
      bbCoaId = '%s',
      bbSaldoAwal = '%s',
      bbDebet = '%s',
      bbKredit = '%s',
      bbSaldo = '%s',
      bbSaldoAkhir = '%s',
      bbUserId = '%s'
   WHERE
      bbId = '%s'
";

$sql['do_update_laba_rugi_buku_besar'] = "
   UPDATE
      buku_besar
   SET
      bbTppId = (SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'),
      bbTanggal = now(),
      bbCoaId = (SELECT coaId FROM coa WHERE `coaIsLabaRugiThJln` = '1' AND coaUnitkerjaId = (SELECT userunitkerjaUnitkerjaId FROM user_unit_kerja WHERE userunitkerjaUserId = %s)),
      bbSaldoAwal = '%s',
      bbDebet = '%s',
      bbKredit = '%s',
      bbSaldo = '%s',
      bbSaldoAkhir = '%s',
      bbUserId = '%s'
   WHERE
      bbId = '%s'
";

$sql['do_insert_buku_besar_his'] = "
   INSERT INTO buku_besar_his
      (bbTppId, bbPembukuanRefId, bbTanggal, bbCoaId, bbSaldoAwal, bbDebet, bbKredit, bbSaldo, bbSaldoAkhir, bbUserId)
   VALUES ((SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'), '%s', now(), '%s', '%s', '%s', '%s', '%s', '%s', '%s');
";

$sql['do_insert_laba_rugi_buku_besar_his'] = "
   INSERT INTO buku_besar_his
      (bbTppId, bbPembukuanRefId, bbTanggal, bbCoaId, bbSaldoAwal, bbDebet, bbKredit, bbSaldo, bbSaldoAkhir, bbUserId)
   VALUES ((SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'), '%s', now(), (SELECT coaId FROM coa WHERE `coaIsLabaRugiThJln` = '1' AND coaUnitkerjaId = (SELECT userunitkerjaUnitkerjaId FROM user_unit_kerja WHERE userunitkerjaUserId = %s)), '%s', '%s', '%s', '%s', '%s', '%s');
";

$sql['update_status_posting_balik_pembukuan_ref'] = "
   UPDATE
      pembukuan_referensi
   SET
      prIsPosting = 'Y',
      prIsJurnalBalik = 1
   WHERE
      prId = '%s'
";

$sql['update_status_is_jurnal_ketika_delete'] = "
   UPDATE
      transaksi
   SET
      transIsJurnal = '%s'
   WHERE
      transId = (SELECT prTransId FROM pembukuan_referensi WHERE prId = %s)
";

$sql['get_periode_pembukuan_aktif'] = "
SELECT
  `tppTanggalAwal` AS tanggal_awal,
  `tppTanggalAkhir` AS tanggal_akhir
FROM  `tahun_pembukuan_periode` tbp
WHERE tbp.`tppIsBukaBuku` = 'Y'
";

$sql['get_sub_account_combobox'] = "
SELECT
   CONCAT_WS('-', TRIM(BOTH FROM subaccPertamaKode),
   TRIM(BOTH FROM subaccKeduaKode),
   TRIM(BOTH FROM subaccKetigaKode),
   TRIM(BOTH FROM subaccKeempatKode),
   TRIM(BOTH FROM subaccKelimaKode),
   TRIM(BOTH FROM subaccKeenamKode),
   TRIM(BOTH FROM subaccKetujuhKode)) AS id,
   CONCAT(TRIM(BOTH FROM subaccPertamaKode), ' - ',subaccPertamaNama) AS name
FROM finansi_keu_ref_subacc_1
LEFT JOIN finansi_keu_ref_subacc_2
   ON subaccKeduaKode = 00
LEFT JOIN finansi_keu_ref_subacc_3
   ON subaccKetigaKode = 00
LEFT JOIN finansi_keu_ref_subacc_4
  ON subaccKeempatKode = 00
LEFT JOIN finansi_keu_ref_subacc_5
   ON subaccKelimaKode =  00
LEFT JOIN finansi_keu_ref_subacc_6
   ON subaccKeenamKode = 00
LEFT JOIN finansi_keu_ref_subacc_7
   ON subaccKetujuhKode = 00
";

?>