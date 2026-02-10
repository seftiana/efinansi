<?php
$sql['get_count']	="
select 
	COUNT(DISTINCT spp.sppId) As total_data   
from 
	finansi_pa_spp AS spp 
join 
	finansi_pa_spp_det AS sd
ON sd.sppDetSppId = spp.sppId 
left join 
	finansi_pa_ref_jenis_pembayaran AS jp 
ON spp.sppJenisPembayaran = jp.jenisPembayaranId 
left join 
	finansi_pa_ref_sifat_pembayaran AS sp 
ON spp.sppSifatPembayaran = sp.sifatPembayaranId 
JOIN 
	rencana_pengeluaran AS rp 
ON 
	sd.sppDetRncnpengeluaranId = rp.rncnpengeluaranId 
LEFT JOIN 
	finansi_pagu_anggaran_unit pa 
ON 
	pa.paguAnggMakId = rp.rncnpengeluaranMakId 
WHERE pa.paguAnggUnitThAnggaranId = '%s'
AND pa.paguAnggUnitUnitKerjaId = '%s' 
";
$sql['get_data'] = "
select 
	@spp_id := spp.sppId As id, 
	spp.sppNomor AS nomor_spp,
	spp.sppKeperluan AS keperluan,
	spp.sppJenisBelanja AS jenis_belanja,
	spp.sppAtasNama AS spp_nama,
	spp.sppAlamat AS spp_alamat, 
	spp.sppRekening AS spp_rekening, 
	spp.sppNilaiSpk AS nilai_spk, 
	spp.sppTotal AS spp_total, 
	spp.sppTgl AS spp_tanggal,
	sp.sifatPembayaranKode AS kode_sifat_pembayaran,
	sp.sifatPembayaranNama As nama_sifat_pembayaran, 
	jp.jenisPembayaranKode AS kode_jenis_pembayaran,
	jp.jenisPembayaranNama AS nama_jenis_pembayaran, 
	sd.sppDetSppId AS id_spp_det, 
	pa.paguAnggUnitNominal AS nominal_dipa, 
	(SELECT SUM(rncnpengeluaranSatuanAprove*rncnpengeluaranKomponenNominalAprove) 
	FROM rencana_pengeluaran AS rp 
	WHERE rp.rncnpengeluaranId = sd.sppDetRncnpengeluaranId 
	GROUP BY rp.rncnpengeluaranMakId) AS jml_nominal_spp  
from 
	finansi_pa_spp AS spp 
join 
	finansi_pa_spp_det AS sd
ON sd.sppDetSppId = spp.sppId 
left join 
	finansi_pa_ref_jenis_pembayaran AS jp 
ON spp.sppJenisPembayaran = jp.jenisPembayaranId 
left join 
	finansi_pa_ref_sifat_pembayaran AS sp 
ON spp.sppSifatPembayaran = sp.sifatPembayaranId 
JOIN 
	rencana_pengeluaran AS rp 
ON 
	sd.sppDetRncnpengeluaranId = rp.rncnpengeluaranId 
LEFT JOIN 
	finansi_pagu_anggaran_unit pa 
ON 
	pa.paguAnggMakId = rp.rncnpengeluaranMakId 
WHERE pa.paguAnggUnitThAnggaranId = '%s'
AND pa.paguAnggUnitUnitKerjaId = '%s' 

order BY 
	spp.sppNomor ASC 
LIMIT %s, %s
";

$sql['get_data_by_id'] ="
SELECT 
	@spp_id := spp.sppId AS id, 
	spp.sppNomor AS nomor_spp,
	sp.sifatPembayaranKode AS kode_sifat_pembayaran,
	sp.sifatPembayaranNama AS nama_sifat_pembayaran, 
	jp.jenisPembayaranKode AS kode_jenis_pembayaran,
	jp.jenisPembayaranNama AS nama_jenis_pembayaran, 
	sd.sppDetSppId AS id_spp_det, 
	(SELECT SUM(rncnpengeluaranSatuanAprove*rncnpengeluaranKomponenNominalAprove) AS approve
	FROM rencana_pengeluaran 
	WHERE rncnpengeluaranId = sd.sppDetRncnpengeluaranId) AS nilai_pengeluaran_approve,
	spp.sppKeperluan AS keperluan, 
	spp.sppJenisBelanja AS jenis_belanja, 
	spp.sppAtasNama AS spp_nama,
	spp.sppAlamat AS alamat, 
	spp.sppRekening AS rekening, 
	spp.sppNilaiSpk AS nilai_spk,
	spp.sppTotal AS spp_total, 
	sd.sppDetNominal AS nominal_detail, 
	rm.makKode AS mak_kode,
	rm.makNama AS mak_nama, 
	pa.paguAnggUnitNominal AS nominal_dipa, 
	(SELECT SUM(rncnpengeluaranSatuanAprove*rncnpengeluaranKomponenNominalAprove) 
	FROM rencana_pengeluaran AS rp 
	WHERE rp.rncnpengeluaranId = sd.sppDetRncnpengeluaranId 
	GROUP BY rp.rncnpengeluaranMakId) AS jml_nominal_spp  
FROM 
	finansi_pa_spp AS spp 
JOIN 
	finansi_pa_spp_det AS sd
ON sd.sppDetSppId = spp.sppId 
LEFT JOIN 
	finansi_pa_ref_jenis_pembayaran AS jp 
ON spp.sppJenisPembayaran = jp.jenisPembayaranId 
LEFT JOIN 
	finansi_pa_ref_sifat_pembayaran AS sp 
ON spp.sppSifatPembayaran = sp.sifatPembayaranId 
JOIN
	rencana_pengeluaran AS rp 
ON rp.rncnpengeluaranId = sd.sppDetRncnpengeluaranId 
JOIN 
	finansi_ref_mak AS rm 
ON rp.rncnpengeluaranMakId = rm.makId 
LEFT JOIN 
	finansi_pagu_anggaran_unit pa 
ON 
	pa.paguAnggMakId = rp.rncnpengeluaranMakId 
WHERE spp.sppId = '%s' 
AND pa.paguAnggUnitThAnggaranId = '%s'
AND pa.paguAnggUnitUnitKerjaId = '%s' 
";

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
?>