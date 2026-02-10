<?php

$sql['get_total_realisasi_penerimaan'] ="
SELECT
	SUM(IF (transaksiBankPenerimaanNominal IS NULL,0,transaksiBankPenerimaanNominal)) 	AS target_pnbp,
	SUM(transaksiBankNominal) AS total_realisasi
FROM finansi_pa_transaksi_bank AS tb
JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = tb.`transaksiBankUnitId`
JOIN `finansi_pa_transaksi_bank_detil` tbd  ON tbd.`transaksiBankDetilTransaksiBankId` = tb.`transaksiBankId`
LEFT JOIN `finansi_pa_transaksi_pembayaran` tpb ON tpb.`pembTBankId` = tb.`transaksiBankId`
LEFT JOIN `transaksi_detail_penerimaan_bank` tr_pb ON tr_pb.`transdtPenerimaanBankTBankId`= tb.`transaksiBankId`
LEFT JOIN transaksi tr ON tr.`transId` = tr_pb.`transdtPenerimaanBankTransId`
WHERE
    transaksiBankTipe = 'penerimaan' 
    AND transaksiBankTipeTransaksi NOT IN ('piutang','pengakuan_depmasuk')
    AND transaksiBankTanggal BETWEEN '%s' AND '%s'
    AND (uk.unitkerjaKodeSistem LIKE 
        CONCAT((SELECT unitkerjaKodeSistem FROM unit_kerja_ref WHERE unit_kerja_ref.unitkerjaId='%s'),'.','%s') 
        OR 
        uk.unitkerjaKodeSistem = (SELECT unitkerjaKodeSistem FROM unit_kerja_ref WHERE unit_kerja_ref.unitkerjaId='%s')
    )
";


$sql['get_nominal_perbulan'] ="
SELECT
    uk.unitkerjaId AS unit_id,
    IFNULL(tpb.`pembJenisBiayaId`,0) AS jb_id,
    MONTH(transaksiBankTanggal) AS bulan,
    YEAR(transaksiBankTanggal) AS tahun,
    CONCAT( YEAR(transaksiBankTanggal),'-',MONTH(transaksiBankTanggal)) AS kode,
    SUM(transaksiBankNominal) AS nominal
FROM 
   finansi_pa_transaksi_bank AS tb
   JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = tb.`transaksiBankUnitId`
   JOIN `finansi_pa_transaksi_bank_detil` tbd  ON tbd.`transaksiBankDetilTransaksiBankId` = tb.`transaksiBankId`
   LEFT JOIN `finansi_pa_transaksi_pembayaran` tpb ON tpb.`pembTBankId` = tb.`transaksiBankId`
   LEFT JOIN `transaksi_detail_penerimaan_bank` tr_pb ON tr_pb.`transdtPenerimaanBankTBankId`= tb.`transaksiBankId`
   LEFT JOIN transaksi tr ON tr.`transId` = tr_pb.`transdtPenerimaanBankTransId`
WHERE
    transaksiBankTipe = 'penerimaan' 
    AND transaksiBankTipeTransaksi NOT IN ('piutang','pengakuan_depmasuk')
    AND transaksiBankTanggal BETWEEN '%s' AND '%s'
    AND (uk.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') 
	OR 
	uk.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
    )
GROUP BY unit_id,tahun,bulan
";

//===GET===
$sql['get_count_data'] = "
	SELECT FOUND_ROWS() AS total
";

$sql['get_data_realisasi_pnbp'] = "
SELECT
    SQL_CALC_FOUND_ROWS
	unitkerjaId AS idunit,
    IFNULL(tpb.`pembJenisBiayaId`,0) AS jb_id,
	unitkerjaKode AS kode_unit,
	unitkerjaNama AS nama_unit,
	transaksiBankId AS id,
	transaksiBankNomor AS bankNomor,
	transaksiBankTanggal AS tanggal,
	transaksiBankPenerima AS penerima,
	transaksiBankNominal AS nominal,
	transaksiBankTipe AS bankTipe,
	transaksiBankTipeTransaksi AS transTipe,
	SUM(IF (transaksiBankPenerimaanNominal IS NULL,0,transaksiBankPenerimaanNominal)) 	AS target_pnbp,
	SUM(transaksiBankNominal) AS total_realisasi,
	IFNULL(tpb.`pembJenisBiayaNama`,
        CONCAT(UCASE(LEFT(transaksiBankTipeTransaksi,1)),
        MID(transaksiBankTipeTransaksi,2,LENGTH(transaksiBankTipeTransaksi) -1))
    ) AS jenisBiayaNama,
	tr.`transCatatan` AS keterangan
FROM finansi_pa_transaksi_bank AS tb
JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = tb.`transaksiBankUnitId`
JOIN `finansi_pa_transaksi_bank_detil` tbd  ON tbd.`transaksiBankDetilTransaksiBankId` = tb.`transaksiBankId`
LEFT JOIN `finansi_pa_transaksi_pembayaran` tpb ON tpb.`pembTBankId` = tb.`transaksiBankId`
LEFT JOIN `transaksi_detail_penerimaan_bank` tr_pb ON tr_pb.`transdtPenerimaanBankTBankId`= tb.`transaksiBankId`
LEFT JOIN transaksi tr ON tr.`transId` = tr_pb.`transdtPenerimaanBankTransId`
WHERE
transaksiBankTipe = 'penerimaan' 
AND transaksiBankTipeTransaksi NOT IN ('piutang','pengakuan_depmasuk')
AND transaksiBankTanggal BETWEEN %s AND %s
AND (uk.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') 
	OR 
	uk.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'))
GROUP BY idunit,jb_id
ORDER BY kode_unit asc
LIMIT %s,%s
";

/*
	Update query get_data
	since 28 Juni 2016
*/

$sql['get_data_realisasi_pnbp_old'] = "
SELECT 
	rp.id AS id,
	rp.bankNomor AS bankNomor,
	rp.tanggal AS tanggal,
	rp.penerima AS penerima,
	rp.nominal AS nominal,
	rp.bankTipe AS bankTipe,
	rp.transTipe AS transTipe,
	rp.idunit AS idunit,
	rp.kode_satker AS kode_satker,
	rp.nama_satker AS nama_satker,
	rp.kode_unit AS kode_unit,
	rp.nama_unit AS nama_unit,
	rp.parentId AS parentId,
	rp.idrencana AS idrencana,
	rp.idkode AS idkode,
	rp.kode AS kode,
	rp.nama AS nama,
	rp.keterangan AS keterangan,
	rp.target_pnbp AS target_pnbp,
	SUM(rp.realJan) AS realJan,
	SUM(rp.realFeb) AS realFeb,
	SUM(rp.realMar) AS realMar,
	SUM(rp.realApr) AS realApr,
	SUM(rp.realMei) AS realMei,
	SUM(rp.realJun) AS realJun,
	SUM(rp.realJul) AS realJul,
	SUM(rp.realAgs) AS realAgs,
	SUM(rp.realSep) AS realSep,
	SUM(rp.realOkt) AS realOkt,
	SUM(rp.realNov) AS realNov,
	SUM(rp.realDes) AS realDes,
	SUM(rp.total_realisasi) AS total_realisasi
FROM
(	
SELECT	
	transaksiBankId AS id,
	transaksiBankNomor AS bankNomor,
	transaksiBankTanggal AS tanggal,
	transaksiBankPenerima AS penerima,
	transaksiBankNominal AS nominal,
	transaksiBankTipe AS bankTipe,
	transaksiBankTipeTransaksi AS transTipe,
	unitkerjaId AS idunit,
	unitkerjaKode AS kode_satker,
	unitkerjaNama AS nama_satker,
	unitkerjaKode AS kode_unit,
	unitkerjaNama AS nama_unit,
	unitkerjaParentId AS parentId,
	renterimaId AS idrencana,
	kodeterimaId AS idkode,
	kodeterimaKode AS kode,
	kodeterimaNama AS nama,
	IF(rp.renterimaKeterangan IS NULL OR rp.renterimaKeterangan = '', '-', rp.renterimaKeterangan) AS keterangan,
	(IF (transaksiBankPenerimaanNominal IS NULL,0,transaksiBankPenerimaanNominal)) 	AS target_pnbp,
	IF(MONTH(transaksiBankTanggal) = '01', SUM(transaksiBankNominal), 0 ) AS realJan,
	IF(MONTH(transaksiBankTanggal) = '02', SUM(transaksiBankNominal), 0 ) AS realFeb,
	IF(MONTH(transaksiBankTanggal) = '03', SUM(transaksiBankNominal), 0 ) AS realMar,
	IF(MONTH(transaksiBankTanggal) = '04', SUM(transaksiBankNominal), 0 ) AS realApr,
	IF(MONTH(transaksiBankTanggal) = '05', SUM(transaksiBankNominal), 0 ) AS realMei,
	IF(MONTH(transaksiBankTanggal) = '06', SUM(transaksiBankNominal), 0 ) AS realJun,
	IF(MONTH(transaksiBankTanggal) = '07', SUM(transaksiBankNominal), 0 ) AS realJul,
	IF(MONTH(transaksiBankTanggal) = '08', SUM(transaksiBankNominal), 0 ) AS realAgs,
	IF(MONTH(transaksiBankTanggal) = '09', SUM(transaksiBankNominal), 0 ) AS realSep,
	IF(MONTH(transaksiBankTanggal) = '10', SUM(transaksiBankNominal), 0 ) AS realOkt,
	IF(MONTH(transaksiBankTanggal) = '11', SUM(transaksiBankNominal), 0 ) AS realNov,
	IF(MONTH(transaksiBankTanggal) = '12', SUM(transaksiBankNominal), 0 ) AS realDes,
	SUM(transaksiBankNominal) AS total_realisasi
FROM finansi_pa_transaksi_bank AS tb
LEFT JOIN rencana_penerimaan AS rp ON rp.`renterimaId` = tb.`transaksiBankPenerimaanId`
LEFT JOIN unit_kerja_ref ON tb.`transaksiBankUnitId` = unitkerjaId  AND renterimaThanggarId ='%s'
LEFT JOIN kode_penerimaan_ref AS kp ON kp.`kodeterimaId` = rp.`renterimaKodeterimaId`
LEFT JOIN (
            SELECT 
                    renterimaUnitkerjaId AS totalUnitkerjaId, 
                    SUM(renterimaTotalTerima) AS totalTotalTerima, 
                    SUM(renterimaJumlah) AS totalterima 
            FROM 
                    rencana_penerimaan 
            WHERE 
                    renterimaThanggarId ='%s' 
            GROUP BY totalUnitkerjaId
            ) AS total ON totalUnitkerjaId=unitkerjaId
WHERE	(transaksiBankTipe = 'penerimaan' AND transaksiBankTipeTransaksi = 'pembayaran')
	AND (unit_kerja_ref.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') 
	OR 
	unit_kerja_ref.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'))
				
	GROUP BY transaksiBankId, kodeterimaId
	) rp
	
	GROUP BY rp.idunit, rp.idkode, rp.idrencana
   LIMIT %s,%s
";

$sql['get_data_realisasi_pnbp_cetak'] ="
SELECT
	unitkerjaId AS idunit,
	unitkerjaKode AS kode_satker,
	unitkerjaNama AS nama_satker,
	unitkerjaKode AS kode_unit,
	unitkerjaNama AS nama_unit,
	transaksiBankId AS id,
	transaksiBankNomor AS bankNomor,
	transaksiBankTanggal AS tanggal,
	transaksiBankPenerima AS penerima,
	transaksiBankNominal AS nominal,
	transaksiBankTipe AS bankTipe,
	transaksiBankTipeTransaksi AS transTipe,
	(IF (transaksiBankPenerimaanNominal IS NULL,0,transaksiBankPenerimaanNominal)) 	AS target_pnbp,
	IF(MONTH(transaksiBankTanggal) = '01', (transaksiBankNominal), 0 ) AS realJan,
	IF(MONTH(transaksiBankTanggal) = '02', (transaksiBankNominal), 0 ) AS realFeb,
	IF(MONTH(transaksiBankTanggal) = '03', (transaksiBankNominal), 0 ) AS realMar,
	IF(MONTH(transaksiBankTanggal) = '04', (transaksiBankNominal), 0 ) AS realApr,
	IF(MONTH(transaksiBankTanggal) = '05', (transaksiBankNominal), 0 ) AS realMei,
	IF(MONTH(transaksiBankTanggal) = '06', (transaksiBankNominal), 0 ) AS realJun,
	IF(MONTH(transaksiBankTanggal) = '07', (transaksiBankNominal), 0 ) AS realJul,
	IF(MONTH(transaksiBankTanggal) = '08', (transaksiBankNominal), 0 ) AS realAgs,
	IF(MONTH(transaksiBankTanggal) = '09', (transaksiBankNominal), 0 ) AS realSep,
	IF(MONTH(transaksiBankTanggal) = '10', (transaksiBankNominal), 0 ) AS realOkt,
	IF(MONTH(transaksiBankTanggal) = '11', (transaksiBankNominal), 0 ) AS realNov,
	IF(MONTH(transaksiBankTanggal) = '12', (transaksiBankNominal), 0 ) AS realDes,
	(transaksiBankNominal) AS total_realisasi,
	IFNULL(tpb.`pembJenisBiayaNama`,
        CONCAT(UCASE(LEFT(transaksiBankTipeTransaksi,1)),MID(transaksiBankTipeTransaksi,2,LENGTH(transaksiBankTipeTransaksi) -1))
    ) AS jenisBiayaNama,
	tr.`transCatatan` AS keterangan
FROM finansi_pa_transaksi_bank AS tb
JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = tb.`transaksiBankUnitId`
JOIN `finansi_pa_transaksi_bank_detil` tbd  ON tbd.`transaksiBankDetilTransaksiBankId` = tb.`transaksiBankId`
LEFT JOIN `finansi_pa_transaksi_pembayaran` tpb ON tpb.`pembTBankId` = tb.`transaksiBankId`
LEFT JOIN `transaksi_detail_penerimaan_bank` tr_pb ON tr_pb.`transdtPenerimaanBankTBankId`= tb.`transaksiBankId`
LEFT JOIN transaksi tr ON tr.`transId` = tr_pb.`transdtPenerimaanBankTransId`
WHERE
transaksiBankTipe = 'penerimaan' 
AND transaksiBankTipeTransaksi NOT IN ('piutang','pengakuan_depmasuk')
AND transaksiBankTanggal BETWEEN %s AND %s
AND (uk.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') 
	OR 
	uk.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'))
ORDER BY kode_satker asc
";

$sql['get_data_realisasi_pnbp_cetak_old'] = "
SELECT 
	rp.id AS id,
	rp.bankNomor AS bankNomor,
	rp.tanggal AS tanggal,
	rp.penerima AS penerima,
	rp.nominal AS nominal,
	rp.bankTipe AS bankTipe,
	rp.transTipe AS transTipe,
	rp.idunit AS idunit,
	rp.kode_satker AS kode_satker,
	rp.nama_satker AS nama_satker,
	rp.kode_unit AS kode_unit,
	rp.nama_unit AS nama_unit,
	rp.parentId AS parentId,
	rp.idrencana AS idrencana,
	rp.idkode AS idkode,
	rp.kode AS kode,
	rp.nama AS nama,
	rp.keterangan AS keterangan,
	rp.target_pnbp AS target_pnbp,
	SUM(rp.realJan) AS realJan,
	SUM(rp.realFeb) AS realFeb,
	SUM(rp.realMar) AS realMar,
	SUM(rp.realApr) AS realApr,
	SUM(rp.realMei) AS realMei,
	SUM(rp.realJun) AS realJun,
	SUM(rp.realJul) AS realJul,
	SUM(rp.realAgs) AS realAgs,
	SUM(rp.realSep) AS realSep,
	SUM(rp.realOkt) AS realOkt,
	SUM(rp.realNov) AS realNov,
	SUM(rp.realDes) AS realDes,
	SUM(rp.total_realisasi) AS total_realisasi
FROM
(	
SELECT	
	transaksiBankId AS id,
	transaksiBankNomor AS bankNomor,
	transaksiBankTanggal AS tanggal,
	transaksiBankPenerima AS penerima,
	transaksiBankNominal AS nominal,
	transaksiBankTipe AS bankTipe,
	transaksiBankTipeTransaksi AS transTipe,
	unitkerjaId AS idunit,
	unitkerjaKode AS kode_satker,
	unitkerjaNama AS nama_satker,
	unitkerjaKode AS kode_unit,
	unitkerjaNama AS nama_unit,
	unitkerjaParentId AS parentId,
	renterimaId AS idrencana,
	kodeterimaId AS idkode,
	kodeterimaKode AS kode,
	kodeterimaNama AS nama,
	IF(rp.renterimaKeterangan IS NULL OR rp.renterimaKeterangan = '', '-', rp.renterimaKeterangan) AS keterangan,
	(IF (transaksiBankPenerimaanNominal IS NULL,0,transaksiBankPenerimaanNominal)) 	AS target_pnbp,
	IF(MONTH(transaksiBankTanggal) = '01', SUM(transaksiBankNominal), 0 ) AS realJan,
	IF(MONTH(transaksiBankTanggal) = '02', SUM(transaksiBankNominal), 0 ) AS realFeb,
	IF(MONTH(transaksiBankTanggal) = '03', SUM(transaksiBankNominal), 0 ) AS realMar,
	IF(MONTH(transaksiBankTanggal) = '04', SUM(transaksiBankNominal), 0 ) AS realApr,
	IF(MONTH(transaksiBankTanggal) = '05', SUM(transaksiBankNominal), 0 ) AS realMei,
	IF(MONTH(transaksiBankTanggal) = '06', SUM(transaksiBankNominal), 0 ) AS realJun,
	IF(MONTH(transaksiBankTanggal) = '07', SUM(transaksiBankNominal), 0 ) AS realJul,
	IF(MONTH(transaksiBankTanggal) = '08', SUM(transaksiBankNominal), 0 ) AS realAgs,
	IF(MONTH(transaksiBankTanggal) = '09', SUM(transaksiBankNominal), 0 ) AS realSep,
	IF(MONTH(transaksiBankTanggal) = '10', SUM(transaksiBankNominal), 0 ) AS realOkt,
	IF(MONTH(transaksiBankTanggal) = '11', SUM(transaksiBankNominal), 0 ) AS realNov,
	IF(MONTH(transaksiBankTanggal) = '12', SUM(transaksiBankNominal), 0 ) AS realDes,
	SUM(transaksiBankNominal) AS total_realisasi
FROM finansi_pa_transaksi_bank AS tb
LEFT JOIN rencana_penerimaan AS rp ON rp.`renterimaId` = tb.`transaksiBankPenerimaanId`
LEFT JOIN unit_kerja_ref ON tb.`transaksiBankUnitId` = unitkerjaId  AND renterimaThanggarId ='%s'
LEFT JOIN kode_penerimaan_ref AS kp ON kp.`kodeterimaId` = rp.`renterimaKodeterimaId`
LEFT JOIN (
            SELECT 
                    renterimaUnitkerjaId AS totalUnitkerjaId, 
                    SUM(renterimaTotalTerima) AS totalTotalTerima, 
                    SUM(renterimaJumlah) AS totalterima 
            FROM 
                    rencana_penerimaan 
            WHERE 
                    renterimaThanggarId ='%s' 
            GROUP BY totalUnitkerjaId
            ) AS total ON totalUnitkerjaId=unitkerjaId
WHERE	(transaksiBankTipe = 'penerimaan' AND transaksiBankTipeTransaksi = 'pembayaran')
	AND (unit_kerja_ref.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') 
	OR 
	unit_kerja_ref.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'))
				
	GROUP BY transaksiBankId, kodeterimaId
	) rp
	
	GROUP BY rp.idunit, rp.idkode, rp.idrencana
";

$sql['get_data_rencana_penerimaan_by_id']="
	SELECT
		thanggarId 				AS tahun_anggaran_id,
		thanggarNama 			AS tahun_anggaran_label,
		unitkerjaId 			AS unitkerja_id,
		unitkerjaNama 			AS unitkerja_label,
		kodeterimaId 			AS penerimaan_id,
		kodeterimaKode 			AS kode_penerimaan,
		kodeterimaNama 			AS nama_penerimaan,
		renterimaTotalTerima 	AS total,
		renterimaJmlJan			AS januari,
		renterimaJmlFeb			AS februari,
		renterimaJmlMar			AS maret,
		renterimaJmlApr			AS april,
		renterimaJmlMei			AS mei,
		renterimaJmlJun 		AS juni,
		renterimaJmlJul			AS juli,
		renterimaJmlAgs			AS agustus,
		renterimaJmlSep			AS september,
		renterimaJmlOkt			AS oktober,
		renterimaJmlNov			AS november,
		renterimaJmlDes			AS desember,
		renterimaVolume			AS volume,
		renterimaTarif				AS tarif,
		renterimaJumlah			AS totalterima,
		renterimaPersenPagu		AS pagu,
		renterimaPagu				AS totalpagu,
		renterimaKeterangan		AS keterangan
	FROM
		rencana_penerimaan
		JOIN kode_penerimaan_ref ON (kodeterimaId = renterimaKodeterimaId)
		JOIN tahun_anggaran ON (thanggarId = renterimaThanggarId)
		JOIN unit_kerja_ref ON (unitkerjaId = renterimaUnitkerjaId)
	WHERE
		renterimaId=%s
";

$sql['get_total_data_realisasi_pnbp_perbulan'] = "
SELECT
	SUM(transaksiBankPenerimaanNominal) 	AS t_target_pnbp,
	IF(MONTH(transaksiBankTanggal) = '01', SUM(transaksiBankNominal), 0 ) AS t_realJan,
	IF(MONTH(transaksiBankTanggal) = '02', SUM(transaksiBankNominal), 0 ) AS t_realFeb,
	IF(MONTH(transaksiBankTanggal) = '03', SUM(transaksiBankNominal), 0 ) AS t_realMar,
	IF(MONTH(transaksiBankTanggal) = '04', SUM(transaksiBankNominal), 0 ) AS t_realApr,
	IF(MONTH(transaksiBankTanggal) = '05', SUM(transaksiBankNominal), 0 ) AS t_realMei,
	IF(MONTH(transaksiBankTanggal) = '06', SUM(transaksiBankNominal), 0 ) AS t_realJun,
	IF(MONTH(transaksiBankTanggal) = '07', SUM(transaksiBankNominal), 0 ) AS t_realJul,
	IF(MONTH(transaksiBankTanggal) = '08', SUM(transaksiBankNominal), 0 ) AS t_realAgs,
	IF(MONTH(transaksiBankTanggal) = '09', SUM(transaksiBankNominal), 0 ) AS t_realSep,
	IF(MONTH(transaksiBankTanggal) = '10', SUM(transaksiBankNominal), 0 ) AS t_realOkt,
	IF(MONTH(transaksiBankTanggal) = '11', SUM(transaksiBankNominal), 0 ) AS t_realNov,
	IF(MONTH(transaksiBankTanggal) = '12', SUM(transaksiBankNominal), 0 ) AS t_realDes,
	SUM(transaksiBankNominal) AS t_total_realisasi
FROM finansi_pa_transaksi_bank AS tb
JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = tb.`transaksiBankUnitId`
JOIN `finansi_pa_transaksi_bank_detil` tbd  ON tbd.`transaksiBankDetilTransaksiBankId` = tb.`transaksiBankId`
LEFT JOIN `finansi_pa_transaksi_pembayaran` tpb ON tpb.`pembTBankId` = tb.`transaksiBankId`
LEFT JOIN `transaksi_detail_penerimaan_bank` tr_pb ON tr_pb.`transdtPenerimaanBankTBankId`= tb.`transaksiBankId`
LEFT JOIN transaksi tr ON tr.`transId` = tr_pb.`transdtPenerimaanBankTransId`
WHERE
transaksiBankTipe = 'penerimaan' 
AND transaksiBankTipeTransaksi NOT IN ('piutang','pengakuan_depmasuk')
AND transaksiBankTanggal BETWEEN %s AND %s
AND (uk.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') 
	OR 
	uk.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'))

";

//COMBO
$sql['get_combo_tahun_anggaran']="
	SELECT
		thanggarId 		AS id,
		thanggarNama 	AS name
	FROM
		tahun_anggaran
	ORDER BY thanggarNama
";
//aktif
$sql['get_tahun_anggaran_aktif']="
	SELECT
		thanggarId 		AS id,
		thanggarNama 	AS name
	FROM
		tahun_anggaran
	WHERE
		thanggarIsAktif='Y'
";
//aktif
$sql['get_tahun_anggaran']="
SELECT
  ta.thanggarId AS id,
  ta.thanggarNama AS `name`,
  ta.`thanggarBuka` AS tanggal_awal,
  ta.`thanggarTutup` AS tanggal_akhir
FROM
  tahun_anggaran ta
WHERE thanggarId = '%s'
";

$sql['set_date']           = "
SELECT
   MIN(thanggarBuka) AS startDate,
   MAX(thanggarTutup) AS endDate
FROM tahun_anggaran
";


$sql['set_date_aktif']           = "
SELECT
   MIN(thanggarBuka) AS startDate,
   MAX(thanggarTutup) AS endDate
FROM tahun_anggaran
WHERE
thanggarIsAktif = 'Y'
";

?>
