<?php

//===GET===
$sql['get_count'] =
   "SELECT
	COUNT(prog.programId) AS total

FROM
   pengajuan_realisasi pr
   JOIN kegiatan_detail kd ON (pr.pengrealKegdetId= kd.kegdetId)
   JOIN kegiatan_ref kr ON (kd.kegdetKegrefId = kr.kegrefId)
   JOIN sub_program sp ON (kr.kegrefSubprogId = sp.subprogId)
   JOIN program_ref prog ON (sp.subprogProgramId = prog.programId)
   LEFT JOIN jenis_kegiatan_ref jk ON ( sp.subprogJeniskegId = jk.jeniskegId)
   JOIN kegiatan k ON (kd.kegdetKegId=k.kegId)
   JOIN unit_kerja_ref uk ON (k.kegUnitkerjaId=uk.unitkerjaId)

WHERE prog.programThanggarId = %s AND
      (uk.unitkerjaId = %s OR uk.unitkerjaParentId = %s) AND
      prog.programId LIKE %s AND
      jk.jeniskegId LIKE %s
LIMIT 1
   ";


$sql['get_data']="
SELECT
   pr.pengrealId AS id,
   pr.pengrealTanggal AS pr_tanggal,
   prog.programId AS program_id,
CONCAT(CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
				WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.00.00') AS program_kode,
   prog.programNama AS program_nama,


   sp.subprogId AS kegiatan_id,
	ifnull(CONCAT(CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
						WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
						WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.00'),'') AS kegiatan_kode,


kr.kegrefId AS subkegiatan_id,
CONCAT(ifnull(subprogNama,''),' (',jeniskegNama,')') AS kegiatan_nama,
	ifnull(CONCAT(CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
						WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
						WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.',CASE WHEN LENGTH(kegrefNomor) = 1 THEN CONCAT('0',kegrefNomor)
						WHEN LENGTH(kegrefNomor) = 2 THEN kegrefNomor END),'') AS subkegiatan_kode,

kr.kegrefNama AS subkegiatan_nama,

pr.pengrealNominal AS pr_nominal_usulan,
pr.pengrealNominalAprove AS pr_nominal_setuju,
pr.pengrealIsApprove AS pr_is_approve

FROM
   pengajuan_realisasi pr
   JOIN kegiatan_detail kd ON (pr.pengrealKegdetId= kd.kegdetId)
   JOIN kegiatan_ref kr ON (kd.kegdetKegrefId = kr.kegrefId)
   JOIN sub_program sp ON (kr.kegrefSubprogId = sp.subprogId)
   JOIN program_ref prog ON (sp.subprogProgramId = prog.programId)
   LEFT JOIN jenis_kegiatan_ref jk ON ( sp.subprogJeniskegId = jk.jeniskegId)
   JOIN kegiatan k ON (kd.kegdetKegId=k.kegId)
   JOIN unit_kerja_ref uk ON (k.kegUnitkerjaId=uk.unitkerjaId)

WHERE prog.programThanggarId = %s AND
      (uk.unitkerjaId = %s OR uk.unitkerjaParentId = %s) AND
      prog.programId LIKE %s AND
      jk.jeniskegId LIKE %s

ORDER BY program_kode , kegiatan_kode, subkegiatan_kode

LIMIT %s, %s

";

$sql['get_data_by_id']="
SELECT
   pr.pengrealId AS id,
   pr.pengrealTanggal AS tanggal,
   pr.pengrealNomorPengajuan AS nomor_pengajuan,
   pr.pengrealNominal AS nominal,
   pr.pengrealKeterangan AS keterangan,
   kd.kegdetId AS kegiatandetail_id,
   k.kegId AS kegiatanunit_id,


   IF (uk.unitkerjaParentId = '0' , uk.unitkerjaId , (SELECT unitkerjaId FROM unit_kerja_ref WHERE unitkerjaId=uk.unitkerjaParentId)) unit_id,

   IF (uk.unitkerjaParentId = '0' , uk.unitkerjaNama , (SELECT unitkerjaNama FROM unit_kerja_ref WHERE unitkerjaId=uk.unitkerjaParentId)) unit_nama,

   IF (uk.unitkerjaParentId != '0' , uk.unitkerjaId,'') subunit_id,
   IF (uk.unitkerjaParentId != '0' , uk.unitkerjaNama,'') subunit_nama,


   prog.programId AS program_id,
   prog.programNama AS program_nama,

   sp.subprogId AS kegiatan_id,
   sp.subprogNama AS kegiatan_nama,

   kr.kegrefId AS subkegiatan_id,
   kr.kegrefNama AS subkegiatan_nama



FROM
   pengajuan_realisasi pr
   JOIN kegiatan_detail kd ON (pr.pengrealKegdetId= kd.kegdetId)
   JOIN kegiatan_ref kr ON (kd.kegdetKegrefId = kr.kegrefId)
   JOIN sub_program sp ON (kr.kegrefSubprogId = sp.subprogId)
   JOIN program_ref prog ON (sp.subprogProgramId = prog.programId)
   LEFT JOIN jenis_kegiatan_ref jk ON ( sp.subprogJeniskegId = jk.jeniskegId)
   JOIN kegiatan k ON (kd.kegdetKegId=k.kegId)
   JOIN unit_kerja_ref uk ON (k.kegUnitkerjaId=uk.unitkerjaId)


WHERE
   pr.pengrealId=%s
LIMIT 1


";

$sql['get_min_tahun']="
SELECT
   IFNULL(MIN(pengrealTanggal), DATE(NOW())) as min
FROM
   pengajuan_realisasi
";

$sql['get_max_tahun']="
SELECT
   IFNULL(MIN(pengrealTanggal), DATE_ADD(DATE(NOW()), INTERVAL 1 YEAR)) as max
FROM
   pengajuan_realisasi
";

$sql['get_jenis_kegiatan']="
   SELECT
      sp.subprogJeniskegId AS jenis_kegiatan
   FROM
      kegiatan_detail kd
      JOIN kegiatan k ON k.kegId=kd.kegdetKegId
      JOIN program_ref pr ON pr.programId = k.kegProgramId
      JOIN sub_program sp ON sp.subprogId = pr.programId
   WHERE kd.kegdetId=%s
   LIMIT 1
";

$sql['get_rencana_nominal']="
SELECT
   SUM(rp.rncnpengeluaranKomponenNominalAprove*rp.rncnpengeluaranSatuanAprove) AS nominal_approve
FROM
   rencana_pengeluaran rp
WHERE
   rp.rncnpengeluaranKegdetId=%s
";
$sql['get_realisasi_nominal']="
SELECT
   SUM(pengrealNominal) AS nominal,
   SUM(pengrealNominalAprove) AS nominal_approve
FROM
   pengajuan_realisasi
WHERE
   pengrealKegdetId=%s
";

$sql['get_realisasi_nominal_edit']="
SELECT
   'all' AS tipe,
   SUM(pengrealNominal) AS nominal,
   SUM(pengrealNominalAprove) AS nominal_approve
FROM
   pengajuan_realisasi
WHERE
   pengrealKegdetId=%s

UNION

SELECT
   'single' AS tipe,
   pengrealNominal AS nominal,
   pengrealNominalAprove AS nominal_approve
FROM
   pengajuan_realisasi
WHERE
   pengrealId=%s
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

$sql['get_data_program'] =
   "SELECT
      programId AS id,
	  programNama AS name
	FROM
	  program_ref
	ORDER BY
	  programNama ASC
   ";

$sql['get_data_jenis_kegiatan'] =
   "SELECT
      jeniskegId AS id,
	  jeniskegNama AS name
	FROM
	  jenis_kegiatan_ref
   WHERE jeniskegId < 3
	ORDER BY
	  jeniskegNama ASC
";

$sql['get_data_satuan_komponen'] =
   "SELECT
      satkompNama AS id,
	  satkompNama AS name
	FROM
	  satuan_komponen
	ORDER BY
	  satkompNama ASC
   ";

$sql['get_ta_aktif']=
   "SELECT
      thanggarId AS id,
	  thanggarNama AS nama
	FROM
	  tahun_anggaran
	WHERE
	  thanggarIsAktif='Y'
	LIMIT 1
   ";
$sql['get_unit_kerja']=
   "SELECT
      unitkerjaId AS unitkerja_id,
      unitkerjaKode AS unitkerja_kode,
      unitkerjaNama AS unitkerja_nama
	FROM
	  unit_kerja_ref
	WHERE
	  unitkerjaParentId LIKE %s AND
	  unitkerjaNama LIKE %s
	ORDER BY
	  unitkerjaKode, UnitkerjaNama ASC
	LIMIT %s, %s
   ";

$sql['get_count_unit_kerja']=
   "SELECT
      COUNT(unitkerjaId) AS total
	FROM
	  unit_kerja_ref
	WHERE
	  unitkerjaParentId LIKE %s AND
	  unitkerjaNama LIKE %s
	ORDER BY
	  unitkerjaKode, UnitkerjaNama ASC
	LIMIT 1
   ";



//===DO===

$sql['do_add'] =
   "INSERT INTO `pengajuan_realisasi`
    (`pengrealKegdetId`, `pengrealNomorPengajuan`, `pengrealNominal`, `pengrealKeterangan`, `pengrealUserId`, `pengrealTanggal`, `pengrealIsApprove`, `pengrealNominalAprove` )
   VALUES
    (%s,  %s,  %s,  %s,  %s,  %s,  NULL,  '0.00' )";

$sql['do_delete']="
   DELETE FROM `pengajuan_realisasi`
   WHERE
    `pengrealId`=%s
";




$sql['do_update'] =
   "UPDATE `pengajuan_realisasi` SET
     `pengrealKegdetId`=%s,
     `pengrealNomorPengajuan`=%s,
     `pengrealNominal`=%s,
     `pengrealKeterangan`=%s,
     `pengrealUserId`=%s,
     `pengrealTanggal`=%s
    WHERE
	  `pengrealId`=%s
";


?>
