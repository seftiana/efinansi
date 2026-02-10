<?php

/**
 * Query untuk Class LapKertasKerjaKonversiRupiahMurni
 * @package lap_kertas_kerja_konversi_rupiah_murni
 * @category 2011 Gamatechno
 */

$sql['get_periode_tahun']    = "
SELECT
   thanggarId AS id,
   thanggarNama AS `name`,
   renstraTanggalAwal AS `start`,
   renstraTanggalAkhir AS `end`
FROM tahun_anggaran
JOIN renstra
   ON renstraId = thanggarRenstraId
WHERE 1 = 1
AND (thanggarIsAktif = 'Y' OR 1 = %s)
AND (thanggarIsOpen = 'Y' OR 1 = %s)
ORDER BY thanggarNama
";

   $sql['get_combo_tahun_anggaran'] ="
      SELECT
         `thanggarId` AS `id`,
         `thanggarNama` AS `name`,
         `thanggarIsAktif`
      FROM `tahun_anggaran`
 	 WHERE 	`thanggarIsAktif` = 'Y'
   ";

   $sql['get_tahun_anggaran_by_id']="
      SELECT
         `thanggarNama`,
         `thanggarBuka`
      FROM `tahun_anggaran`
      WHERE thanggarId = '%s' AND 	`thanggarIsAktif` = 'Y'
   ";

   $sql['get_data']="
		SELECT
			`coa`.`coaKodeAkun` AS sak_akun,
			`coa`.`coaNamaAkun` AS sak_uraian,
			(IF(`coa`.`coaKodeAkun`IS NULL,NULL,`pengajuan_realisasi_detil`.`pengrealdetNominalApprove`))
				AS sak_jumlah,
			`finansi_ref_mak`.`makKode` AS sap_akun,
			`finansi_ref_mak`.`makNama` AS sap_uraian,
			`pengajuan_realisasi_detil`.`pengrealdetNominalApprove`  AS sap_jumlah
		FROM `pengajuan_realisasi_detil`
			LEFT JOIN `pengajuan_realisasi` ON `pengajuan_realisasi`.`pengrealId` =
				`pengajuan_realisasi_detil`.`pengrealdetPengRealId`
			LEFT JOIN `rencana_pengeluaran`  ON `pengajuan_realisasi_detil`.`pengrealdetRncnpengeluaranId` =
				`rencana_pengeluaran`.`rncnpengeluaranId`
			LEFT JOIN `finansi_ref_mak` ON `finansi_ref_mak`.`makId` =
				`rencana_pengeluaran`.`rncnpengeluaranMakId`
			LEFT JOIN `finansi_coa_mak` ON `finansi_coa_mak`.`makId` = `finansi_ref_mak`.`makId`
			LEFT JOIN `coa` ON `coa`.`coaId` = `finansi_coa_mak`.`coaId`
			LEFT JOIN `kegiatan_detail` ON `kegiatan_detail`.`kegdetId` = `pengajuan_realisasi`.`pengrealKegdetId`
			LEFT JOIN `kegiatan` ON `kegiatan`.`kegId` = `kegiatan_detail`.`kegdetKegId`
		WHERE
			`kegiatan`.`kegThanggarId` ='%s'
				AND
			`pengajuan_realisasi_detil`.`pengrealdetTanggal` < '%s'
		LIMIT %s,%s
   ";
   $sql['get_count_data']="
   	SELECT
			COUNT(*)  AS total
		FROM `pengajuan_realisasi_detil`
			LEFT JOIN `pengajuan_realisasi` ON `pengajuan_realisasi`.`pengrealId` =
				`pengajuan_realisasi_detil`.`pengrealdetPengRealId`
			LEFT JOIN `rencana_pengeluaran`
				ON `pengajuan_realisasi_detil`.`pengrealdetRncnpengeluaranId` =
				`rencana_pengeluaran`.`rncnpengeluaranId`
			LEFT JOIN `finansi_ref_mak` ON `finansi_ref_mak`.`makId` =
				`rencana_pengeluaran`.`rncnpengeluaranMakId`
			LEFT JOIN `finansi_coa_mak` ON `finansi_coa_mak`.`makId` = `finansi_ref_mak`.`makId`
			LEFT JOIN `coa` ON `coa`.`coaId` = `finansi_coa_mak`.`coaId`
			LEFT JOIN `kegiatan_detail` ON `kegiatan_detail`.`kegdetId` =
				`pengajuan_realisasi`.`pengrealKegdetId`
			LEFT JOIN `kegiatan` ON `kegiatan`.`kegId` = `kegiatan_detail`.`kegdetKegId`
		WHERE
			`kegiatan`.`kegThanggarId` ='%s'
				AND
			`pengajuan_realisasi_detil`.`pengrealdetTanggal` < '%s'
   " ;