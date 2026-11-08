<?php
// scripts/generate_dummy_excel.php
// Script untuk menghasilkan file dummy CSV dan XLSX untuk pengujian impor master data

function buat_xlsx_sederhana($filepath, $rows) {
    $zip = new ZipArchive();
    if ($zip->open($filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new Exception("Gagal membuat zip $filepath");
    }

    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>');

    $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

    $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="Sheet1" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>');

    $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
</Relationships>');

    $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
    $sheet .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n";
    $sheet .= '  <sheetData>' . "\n";

    $row_num = 1;
    foreach ($rows as $row) {
        $sheet .= '    <row r="' . $row_num . '">' . "\n";
        $col_idx = 0;
        foreach ($row as $val) {
            $col_letter = chr(ord('A') + $col_idx);
            $cell_ref = $col_letter . $row_num;
            $safe_val = htmlspecialchars((string)$val, ENT_XML1, 'UTF-8');
            $sheet .= '      <c r="' . $cell_ref . '" t="inlineStr"><is><t>' . $safe_val . '</t></is></c>' . "\n";
            $col_idx++;
        }
        $sheet .= '    </row>' . "\n";
        $row_num++;
    }

    $sheet .= '  </sheetData>' . "\n";
    $sheet .= '</worksheet>';

    $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
    $zip->close();
}

function buat_csv_sederhana($filepath, $rows) {
    $f = fopen($filepath, 'w');
    // Tulis BOM agar Excel Windows membacanya dengan encoding UTF-8 yang benar
    fprintf($f, chr(0xEF).chr(0xBB).chr(0xBF));
    foreach ($rows as $r) {
        fputcsv($f, $r);
    }
    fclose($f);
}

// 1. Data Dummy Mahasiswa
$data_mhs = [
    ['NIM', 'Nama Lengkap (Wajib)', 'Email (Wajib)'],
    ['322310050', 'Budi Wicaksono (Uji Coba)', 'budi.dummy@student.machung.ac.id'],
    ['322310051', 'Siti Rahmawati (Uji Coba)', 'siti.dummy@student.machung.ac.id'],
    ['322310052', 'Ahmad Fadillah (Uji Coba)', 'ahmad.dummy@student.machung.ac.id']
];

// 2. Data Dummy Dosen
$data_dosen = [
    ['NIDN', 'Nama Lengkap (Wajib)', 'Email (Wajib)'],
    ['0712039901', 'Dr. Ir. Hendra Gunawan, M.T. (Uji Coba)', 'hendra.dummy@machung.ac.id'],
    ['0712039902', 'Dewi Lestari, S.Kom., M.Cs. (Uji Coba)', 'dewi.dummy@machung.ac.id']
];

// 3. Data Dummy Alumni
$data_alumni = [
    ['Nama Lengkap (Wajib)', 'Email (Wajib)', 'Tahun Lulus'],
    ['Fajar Pratama (Uji Coba)', 'fajar.dummy@gmail.com', '2023'],
    ['Nadia Safitri (Uji Coba)', 'nadia.dummy@gmail.com', '2024']
];

// 4. Data Dummy Perusahaan
$data_perusahaan = [
    ['Nama Perwakilan (Wajib)', 'Email (Wajib)', 'Nama Instansi/Perusahaan'],
    ['Joko Santoso (HR Manager)', 'joko.hr@dummycorp.co.id', 'PT Dummy Corp Mandiri'],
    ['Lestari Putri (Talent Lead)', 'lestari@startupdigital.id', 'Startup Digital Nusantara']
];

$dir = __DIR__ . '/../template';
if (!is_dir($dir)) mkdir($dir, 0777, true);

buat_xlsx_sederhana($dir . '/dummy_impor_mahasiswa.xlsx', $data_mhs);
buat_csv_sederhana($dir . '/dummy_impor_mahasiswa.csv', $data_mhs);

buat_xlsx_sederhana($dir . '/dummy_impor_dosen.xlsx', $data_dosen);
buat_csv_sederhana($dir . '/dummy_impor_dosen.csv', $data_dosen);

buat_xlsx_sederhana($dir . '/dummy_impor_alumni.xlsx', $data_alumni);
buat_csv_sederhana($dir . '/dummy_impor_alumni.csv', $data_alumni);

buat_xlsx_sederhana($dir . '/dummy_impor_perusahaan.xlsx', $data_perusahaan);
buat_csv_sederhana($dir . '/dummy_impor_perusahaan.csv', $data_perusahaan);

echo "Semua berkas dummy Excel (.xlsx) dan CSV berhasil dibuat di folder template/\n";

