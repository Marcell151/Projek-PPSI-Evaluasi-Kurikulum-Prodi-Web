<?php
// includes/impor_helper.php
// Helper untuk membaca file impor tabular (Excel .xlsx, .csv) secara universal,
// baik menggunakan PhpSpreadsheet (jika ada) maupun parser native PHP yang ringan & cepat.

/**
 * Membaca berkas Excel (.xlsx) atau CSV (.csv) menjadi array baris.
 * 
 * @param string $file_path Path file temporary di server
 * @param string $original_name Nama berkas asli untuk deteksi ekstensi
 * @return array Array 2 dimensi baris data [[col0, col1, ...], ...]
 * @throws Exception Jika format tidak didukung atau berkas rusak
 */
function baca_file_impor($file_path, $original_name) {
    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

    if (!in_array($ext, ['csv', 'xlsx', 'xls'])) {
        throw new Exception("Format berkas .$ext tidak didukung. Harap gunakan berkas .xlsx atau .csv.");
    }

    // 1. Prioritaskan PhpSpreadsheet jika vendor/autoload.php tersedia
    $vendor_path = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($vendor_path)) {
        require_once $vendor_path;
        if (class_exists('\\PhpOffice\\PhpSpreadsheet\\IOFactory')) {
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
                $worksheet = $spreadsheet->getActiveSheet();
                return $worksheet->toArray();
            } catch (Exception $e) {
                // Jika PhpSpreadsheet gagal membaca file tertentu, lanjutkan ke parser native
            }
        }
    }

    // 2. Parser Native untuk berkas CSV
    if ($ext === 'csv') {
        return baca_csv_native($file_path);
    }

    // 3. Parser Native untuk berkas XLSX menggunakan ZipArchive & SimpleXML
    if ($ext === 'xlsx') {
        if (class_exists('ZipArchive')) {
            return baca_xlsx_native($file_path);
        } else {
            throw new Exception("Ekstensi Zip PHP belum aktif di server untuk membaca .xlsx. Silakan simpan berkas sebagai .csv dan unggah kembali.");
        }
    }

    // 4. Berkas XLS versi lama (Excel 97-2003) tanpa PhpSpreadsheet
    if ($ext === 'xls') {
        throw new Exception("Format .xls (Excel lama) memerlukan PhpSpreadsheet. Silakan simpan ulang berkas sebagai .xlsx atau .csv di Microsoft Excel.");
    }

    throw new Exception("Gagal memproses berkas $original_name.");
}

/**
 * Membaca berkas CSV dengan auto-deteksi delimiter (koma, titik koma, tab) dan penanganan BOM UTF-8
 */
function baca_csv_native($file_path) {
    if (!file_exists($file_path)) {
        throw new Exception("Berkas tidak ditemukan: $file_path");
    }

    $handle = fopen($file_path, 'r');
    if (!$handle) {
        throw new Exception("Gagal membuka berkas CSV.");
    }

    // Lewati UTF-8 BOM jika ada
    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") {
        rewind($handle);
    }

    // Deteksi delimiter dari baris pertama
    $first_line = fgets($handle);
    if ($first_line === false) {
        fclose($handle);
        return [];
    }

    $delimiters = [',', ';', "\t"];
    $best_delimiter = ',';
    $max_count = 0;
    foreach ($delimiters as $delim) {
        $count = substr_count($first_line, $delim);
        if ($count > $max_count) {
            $max_count = $count;
            $best_delimiter = $delim;
        }
    }

    // Kembalikan pointer setelah BOM
    rewind($handle);
    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") {
        rewind($handle);
    }

    $rows = [];
    while (($data = fgetcsv($handle, 0, $best_delimiter)) !== false) {
        // Cek apakah seluruh kolom kosong
        $all_empty = true;
        foreach ($data as $cell) {
            if (trim($cell) !== '') {
                $all_empty = false;
                break;
            }
        }
        if (!$all_empty) {
            $rows[] = array_map(function($v) {
                return trim($v ?? '');
            }, $data);
        }
    }

    fclose($handle);
    return $rows;
}

/**
 * Membaca berkas XLSX secara native menggunakan ZipArchive dan SimpleXML
 */
function baca_xlsx_native($file_path) {
    $zip = new ZipArchive();
    $res = $zip->open($file_path);
    if ($res !== true) {
        throw new Exception("Gagal membuka arsip .xlsx (Kode error: $res).");
    }

    // Baca shared strings jika ada (standar XLSX menyimpan teks di sharedStrings.xml)
    $shared_strings = [];
    $ss_xml = $zip->getFromName('xl/sharedStrings.xml');
    if ($ss_xml) {
        $xml = @simplexml_load_string($ss_xml);
        if ($xml) {
            foreach ($xml->si as $si) {
                if (isset($si->t)) {
                    $shared_strings[] = (string)$si->t;
                } elseif (isset($si->r)) {
                    $text = '';
                    foreach ($si->r as $r) {
                        $text .= (string)$r->t;
                    }
                    $shared_strings[] = $text;
                } else {
                    $shared_strings[] = '';
                }
            }
        }
    }

    // Temukan worksheet pertama (biasanya xl/worksheets/sheet1.xml)
    $sheet_xml = $zip->getFromName('xl/worksheets/sheet1.xml');
    if (!$sheet_xml) {
        // Coba cari nama berkas worksheet pertama yang ada
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if (preg_match('#xl/worksheets/sheet\d+\.xml#i', $stat['name'])) {
                $sheet_xml = $zip->getFromIndex($i);
                break;
            }
        }
    }

    if (!$sheet_xml) {
        $zip->close();
        throw new Exception("Worksheet tidak ditemukan di dalam berkas .xlsx.");
    }

    $xml = @simplexml_load_string($sheet_xml);
    $zip->close();

    if (!$xml || !isset($xml->sheetData)) {
        throw new Exception("Format XML worksheet tidak valid.");
    }

    $rows = [];
    foreach ($xml->sheetData->row as $row) {
        $row_data = [];
        $max_col = 0;

        foreach ($row->c as $c) {
            $cell_ref = (string)$c['r']; // Contoh: A1, B1, C2
            preg_match('/([A-Z]+)(\d+)/', $cell_ref, $m);
            $col_letters = $m[1] ?? 'A';
            
            // Ubah huruf kolom menjadi indeks angka (A=0, B=1, ..., Z=25, AA=26, dst)
            $col_idx = 0;
            $len = strlen($col_letters);
            for ($k = 0; $k < $len; $k++) {
                $col_idx = $col_idx * 26 + (ord($col_letters[$k]) - ord('A') + 1);
            }
            $col_idx -= 1; // 0-based

            $val = '';
            $type = (string)$c['t'];

            if ($type === 's') {
                // Shared string index
                $idx = (int)$c->v;
                $val = $shared_strings[$idx] ?? '';
            } elseif ($type === 'inlineStr') {
                $val = (string)$c->is->t;
            } else {
                $val = (string)$c->v;
            }

            $row_data[$col_idx] = trim($val);
            if ($col_idx > $max_col) {
                $max_col = $col_idx;
            }
        }

        // Normalisasikan array agar indeks 0 sampai max_col terisi
        $normalized_row = [];
        for ($c = 0; $c <= $max_col; $c++) {
            $normalized_row[$c] = $row_data[$c] ?? '';
        }

        // Cek jika seluruh baris kosong
        $all_empty = true;
        foreach ($normalized_row as $v) {
            if ($v !== '') {
                $all_empty = false;
                break;
            }
        }
        if (!$all_empty) {
            $rows[] = $normalized_row;
        }
    }

    return $rows;
}

