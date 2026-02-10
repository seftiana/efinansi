<?php
//== for combo box ==
$sql['get_data_ta'] =
   "SELECT
      thanggarId AS id,
	  thanggarNama AS name
	FROM
	  tahun_anggaran
	ORDER BY
	  thanggarNama ASC
";
// get tahun anggaran aktif
$sql['get_ta_active']	= "
	SELECT
      thanggarId AS id
	FROM
	  tahun_anggaran
	WHERE
	  thanggarIsAktif='Y'
	LIMIT 1
";

// jenis pembayaran
$sql['jenis_pembayaran'] = "
SELECT 
	jp.jenisPembayaranId AS id, 
	CONCAT(jp.jenisPembayaranKode,' - ',jp.jenisPembayaranNama) AS name
FROM finansi_pa_ref_jenis_pembayaran AS jp 
ORDER BY 
	jp.jenisPembayaranNama ASC
";

// sifat pembayaran
$sql['sifat_pembayaran'] = "
SELECT 
	sp.sifatPembayaranId AS id, 
	CONCAT(sp.sifatPembayaranKode,' - ',sp.sifatPembayaranNama) AS name
FROM finansi_pa_ref_sifat_pembayaran AS sp 
ORDER BY 
	sp.sifatPembayaranNama ASC
";

// getdata
$sql['get_data'] = "
SELECT
	rp.rncnpengeluaranId AS id, 
	rp.rncnpengeluaranKegdetId AS keg_id,
	mak.makId AS mak_id, 
	mak.makKode AS mak_kode,
	mak.makNama AS mak_nama, 
	pau.paguAnggUnitNominal AS pagu_dipa, 
	SUM(rp.rncnpengeluaranSatuanAprove*rp.rncnpengeluaranKomponenNominalAprove) AS nominal_approve
FROM 
	rencana_pengeluaran AS rp 
JOIN 
	finansi_ref_mak AS mak 
ON 
	mak.makId = rp.rncnpengeluaranMakId
LEFT JOIN 
	finansi_pagu_anggaran_unit AS pau 
ON 
	pau.paguAnggMakId = rp.rncnpengeluaranMakId 
LEFT JOIN 
	finansi_pa_spp_det AS sd 
ON 
	sd.sppDetRncnpengeluaranId = rp.rncnpengeluaranId 
WHERE 
	rp.rncnpengeluaranIsAprove = 'Ya' 
	AND pau.paguAnggUnitThAnggaranId = '%s'
	AND pau.paguAnggUnitUnitKerjaId = '%s' 
	AND sd.sppDetRncnpengeluaranId IS NULL 
GROUP BY rp.rncnpengeluaranMakId  
LIMIT %s, %s
";
// getdata
$sql['get_data_by_id'] = "
SELECT
	rp.rncnpengeluaranId AS id, 
	rp.rncnpengeluaranKegdetId AS keg_id,
	mak.makId AS mak_id, 
	mak.makKode AS mak_kode,
	mak.makNama AS mak_nama, 
	pau.paguAnggUnitNominal AS pagu_dipa, 
	SUM(rp.rncnpengeluaranSatuanAprove*rp.rncnpengeluaranKomponenNominalAprove) AS nominal_approve
FROM 
	rencana_pengeluaran AS rp 
JOIN 
	finansi_ref_mak AS mak 
ON 
	mak.makId = rp.rncnpengeluaranMakId
LEFT JOIN 
	finansi_pagu_anggaran_unit AS pau 
ON 
	pau.paguAnggMakId = rp.rncnpengeluaranMakId 
WHERE 
	rp.rncnpengeluaranId = '%s' 
	AND rp.rncnpengeluaranIsAprove = 'Ya' 
	AND pau.paguAnggUnitThAnggaranId = '%s'
	AND pau.paguAnggUnitUnitKerjaId = '%s'
GROUP BY rp.rncnpengeluaranMakId 
";

// count data
$sql['count_data'] ="
SELECT
	COUNT(rp.rncnpengeluaranId) AS total_data 
FROM 
	rencana_pengeluaran AS rp 
JOIN 
	finansi_ref_mak AS mak 
ON 
	mak.makId = rp.rncnpengeluaranMakId
LEFT JOIN 
	finansi_pagu_anggaran_unit AS pau 
ON 
	pau.paguAnggMakId = rp.rncnpengeluaranMakId 
LEFT JOIN 
	finansi_pa_spp_det AS sd 
ON 
	sd.sppDetRncnpengeluaranId = rp.rncnpengeluaranId 
WHERE 
	rp.rncnpengeluaranIsAprove = 'Ya' 
	AND pau.paguAnggUnitThAnggaranId = '%s'
	AND pau.paguAnggUnitUnitKerjaId = '%s' 
	AND sd.sppDetRncnpengeluaranId IS NULL 
";

// get unit kerja
$sql['get_unit_kerja_by_user'] = "
SELECT 
	unitkerjaId	AS unit_id,
	unitkerjaNama AS unit_nama, 
	unitkerjaKode AS unit_kode, 
	unitkerjaParentId AS unit_parent 
FROM unit_kerja_ref AS unit_ref 
LEFT JOIN user_unit_kerja AS user_unit 
	ON user_unit.userunitkerjaUnitkerjaId = unit_ref.unitkerjaId 
WHERE 
	user_unit.userunitkerjaUserId = '%s'
";

// do input spp
$sql['input_spp']	= "
INSERT INTO finansi_pa_spp 
	(sppNomor,sppTgl,sppSifatPembayaran,sppJenisPembayaran,sppKeperluan,sppJenisBelanja,sppAtasNama,
	sppAlamat,sppRekening,sppNilaiSpk,sppTotal,sppUserId)
VALUES 
	('%s',NOW(),'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')
";

// get last number
$sql['get_last_number']	= "
SELECT MAX(sppNomor)+1 AS last_nomor FROM finansi_pa_spp
";

// get last insert id
$sql['get_last_id']="SELECT @last_id := LAST_INSERT_ID() AS last_id";

// insert into finansi_pa_spp_det
$sql['insert_spp_det']	= "
INSERT INTO 
	finansi_pa_spp_det (sppDetSppId,sppDetRncnpengeluaranId,sppDetNominal,sppDetUserId,sppSetTglUbah) 
	VALUES 
	('%s','%s','%s','%s',NOW())
";

$sql['delete_spp']	= "
DELETE FROM finansi_pa_spp WHERE sppId = '%s'
";
?>