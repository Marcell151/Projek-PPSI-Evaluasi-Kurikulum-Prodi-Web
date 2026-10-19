<?php
/**
 * Generator Diagram Bab III (format draw.io / diagrams.net)
 * ---------------------------------------------------------
 * Menghasilkan:
 *   - docs/diagram/diagram_bab3.drawio  (multi-halaman: Use Case, Activity, ERD)
 *   - viewer_arsitektur.html            (pratinjau di browser)
 *
 * ERD dibangun OTOMATIS dari database/data_kosong.sql sehingga selalu
 * sesuai dengan struktur tabel aktual.
 *
 * Jalankan:  C:\xampp\php\php.exe docs\diagram\generate_diagram.php
 */

$ROOT = dirname(__DIR__, 2);
$OUT_DRAWIO = __DIR__ . '/diagram_bab3.drawio';
$OUT_VIEWER = $ROOT . '/viewer_arsitektur.html';

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_XML1, 'UTF-8'); }

class Page
{
    public $name; public $cells = []; public $w = 1600; public $h = 1200;
    private $n = 0; private $p;
    function __construct($name, $prefix) { $this->name = $name; $this->p = $prefix; }
    function nid() { return $this->p . '-' . (++$this->n); }
    function vertex($label, $x, $y, $w, $h, $style, $parent = '1')
    {
        $id = $this->nid();
        $this->cells[] = sprintf(
            '<mxCell id="%s" value="%s" style="%s" vertex="1" parent="%s"><mxGeometry x="%s" y="%s" width="%s" height="%s" as="geometry"/></mxCell>',
            esc($id), esc($label), esc($style), esc($parent), $x, $y, $w, $h
        );
        return $id;
    }
    function edge($s, $t, $label = '', $style = '', $points = [])
    {
        $id = $this->nid();
        $pts = '';
        if ($points) {
            $pts = '<Array as="points">';
            foreach ($points as $p) $pts .= sprintf('<mxPoint x="%s" y="%s"/>', $p[0], $p[1]);
            $pts .= '</Array>';
        }
        $this->cells[] = sprintf(
            '<mxCell id="%s" value="%s" style="%s" edge="1" parent="1" source="%s" target="%s"><mxGeometry relative="1" as="geometry">%s</mxGeometry></mxCell>',
            esc($id), esc($label), esc($style), esc($s), esc($t), $pts
        );
        return $id;
    }
    function model()
    {
        return '<mxGraphModel dx="1400" dy="900" grid="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="1" pageScale="1" pageWidth="' . $this->w . '" pageHeight="' . $this->h . '" math="0" shadow="0"><root><mxCell id="0"/><mxCell id="1" parent="0"/>' . implode('', $this->cells) . '</root></mxGraphModel>';
    }
    function diagram($i) { return '<diagram id="page-' . $i . '" name="' . esc($this->name) . '">' . $this->model() . '</diagram>'; }
}

/* ===================== Konstanta gaya (mirip draw.io default) ===================== */
const FLOW = 'edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;endArrow=classic;endFill=1;labelBackgroundColor=#ffffff;fontSize=11;';
const oR = 'exitX=1;exitY=0.5;exitDx=0;exitDy=0;';
const oL = 'exitX=0;exitY=0.5;exitDx=0;exitDy=0;';
const oB = 'exitX=0.5;exitY=1;exitDx=0;exitDy=0;';
const oT = 'exitX=0.5;exitY=0;exitDx=0;exitDy=0;';
const iT = 'entryX=0.5;entryY=0;entryDx=0;entryDy=0;';
const iB = 'entryX=0.5;entryY=1;entryDx=0;entryDy=0;';
const iL = 'entryX=0;entryY=0.5;entryDx=0;entryDy=0;';
const iR = 'entryX=1;entryY=0.5;entryDx=0;entryDy=0;';

/* ===================== Helper Activity Diagram (swimlane) ===================== */
class Act
{
    public $pg; public $lx = []; public $top; public $rowH;
    function __construct(Page $pg, $title, $lanes, $rows, $rowH = 85)
    {
        $this->pg = $pg; $this->rowH = $rowH;
        $x0 = 20; $y0 = 20; $titleH = 24; $laneHead = 30;
        $this->top = $y0 + $titleH + $laneHead + 40;
        $totalW = array_sum(array_column($lanes, 1));
        $totalH = $titleH + $laneHead + 40 + ($rows - 1) * $rowH + 60;
        $pg->vertex($title, $x0, $y0, $totalW, $totalH, 'swimlane;html=1;startSize=24;fontStyle=1;collapsible=0;');
        $x = $x0;
        foreach ($lanes as $l) {
            $pg->vertex($l[0], $x, $y0 + $titleH, $l[1], $totalH - $titleH, 'swimlane;html=1;startSize=30;fontStyle=1;collapsible=0;');
            $this->lx[] = [$x, $l[1]];
            $x += $l[1];
        }
        $pg->w = $totalW + 40; $pg->h = $totalH + 40;
    }
    function cx($lane, $dx = 0) { return $this->lx[$lane][0] + $this->lx[$lane][1] / 2 + $dx; }
    function cy($row) { return $this->top + $row * $this->rowH; }
    function n($type, $lane, $row, $label = '', $dx = 0, $w = null, $h = null)
    {
        switch ($type) {
            case 'start': $w = 30; $h = 30; $st = 'ellipse;html=1;shape=startState;fillColor=#000000;strokeColor=#000000;'; break;
            case 'end':   $w = 30; $h = 30; $st = 'ellipse;html=1;shape=endState;fillColor=#000000;strokeColor=#000000;'; break;
            case 'dec':   $w = 40; $h = 40; $st = 'rhombus;html=1;labelPosition=left;verticalLabelPosition=top;align=right;verticalAlign=bottom;fontSize=11;'; break;
            default:      $w = $w ?: 160; $h = $h ?: 50; $st = 'rounded=1;whiteSpace=wrap;html=1;arcSize=20;fontSize=11;';
        }
        return $this->pg->vertex($label, $this->cx($lane, $dx) - $w / 2, $this->cy($row) - $h / 2, $w, $h, $st);
    }
    function f($a, $b, $label = '', $c = '', $pts = []) { return $this->pg->edge($a, $b, $label, FLOW . $c, $pts); }
}

$pages = [];

/* =========================================================================
 * 1. USE CASE DIAGRAM
 * ========================================================================= */
$uc = new Page('Use Case Diagram', 'uc');
$uc->w = 1700; $uc->h = 1360;
$ACTOR = 'shape=umlActor;verticalLabelPosition=bottom;verticalAlign=top;html=1;outlineConnect=0;';
$UC    = 'ellipse;whiteSpace=wrap;html=1;fontSize=11;';
$ASSOC = 'endArrow=classic;html=1;rounded=0;endFill=1;';
$DASH  = 'edgeStyle=orthogonalEdgeStyle;dashed=1;endArrow=open;endFill=0;html=1;rounded=0;fontSize=10;labelBackgroundColor=#ffffff;';
$GEN   = 'endArrow=block;endFill=0;endSize=12;html=1;rounded=0;';

$admin = $uc->vertex('Admin', 60, 500, 30, 60, $ACTOR);
$resp  = $uc->vertex('Responden', 1400, 600, 30, 60, $ACTOR);

// Spesialisasi aktor responden (generalisasi)
foreach ([['Mahasiswa', 380], ['Dosen', 520], ['Alumni', 660], ['Perusahaan', 800]] as $a) {
    $id = $uc->vertex($a[0], 1580, $a[1], 30, 60, $ACTOR);
    $uc->edge($id, $resp, '', $GEN);
}

$AX = 330; $AW = 170; $AH = 70;   // kolom use case Admin
$SX = 600; $SW = 150; $SH = 50;   // kolom include/extend Admin
$RX = 1120; $RW = 170;            // kolom use case Responden
$TX = 880;                        // kolom include/extend Responden

$A1 = $uc->vertex('Kelola Master Data Pengguna', $AX, 105, $AW, $AH, $UC);
$A2 = $uc->vertex('Kelola Periode Evaluasi', $AX, 330, $AW, $AH, $UC);
$A3 = $uc->vertex('Kelola Bank Kuesioner', $AX, 520, $AW, $AH, $UC);
$A4 = $uc->vertex('Melihat Dashboard Analitik', $AX, 715, $AW, $AH, $UC);
$A5 = $uc->vertex('Kelola Laporan Evaluasi', $AX, 830, $AW, $AH, $UC);
$A6 = $uc->vertex('Kelola Tiket Bantuan (Balas &amp; Ubah Status)', $AX, 950, $AW, $AH, $UC);
foreach ([$A1, $A2, $A3, $A4, $A5, $A6] as $u) $uc->edge($admin, $u, '', $ASSOC);

// Include/extend A1
foreach ([['Data Mahasiswa', 0], ['Data Dosen', 60], ['Data Alumni', 120], ['Data Perusahaan', 180]] as $s) {
    $id = $uc->vertex($s[0], $SX, $s[1], $SW, $SH, $UC);
    $uc->edge($A1, $id, 'Include', $DASH . oR . iL);
}
$imp = $uc->vertex('Impor Massal Pengguna (Excel / CSV)', $SX, 240, $SW, $SH, $UC);
$uc->edge($imp, $A1, 'Extend', $DASH . oL . iR);

// Include/extend A3
foreach ([['Kategori Kompetensi', 400], ['Bahan Kajian', 460], ['Mata Kuliah', 520], ['Pertanyaan &amp; Opsi Jawaban', 580]] as $s) {
    $id = $uc->vertex($s[0], $SX, $s[1], $SW, $SH, $UC);
    $uc->edge($A3, $id, 'Include', $DASH . oR . iL);
}
$soft = $uc->vertex('Aktif / Nonaktifkan Data (Soft Delete)', $SX, 645, $SW, 55, $UC);
$uc->edge($soft, $A3, 'Extend', $DASH . oL . iR);

// Extend A4 & A5
$flt = $uc->vertex('Filter Periode Evaluasi', $SX, 722, $SW, 45, $UC);
$uc->edge($flt, $A4, 'Extend', $DASH . oL . iR);
$pdf = $uc->vertex('Cetak Laporan PDF', $SX, 790, $SW, $SH, $UC);
$xls = $uc->vertex('Ekspor Laporan Excel', $SX, 850, $SW, $SH, $UC);
$uc->edge($pdf, $A5, 'Extend', $DASH . oL . iR);
$uc->edge($xls, $A5, 'Extend', $DASH . oL . iR);

// Use case Responden
$R1 = $uc->vertex('Melihat Beranda &amp; Status Evaluasi', $RX, 330, $RW, $AH, $UC);
$R2 = $uc->vertex('Mengisi Kuesioner Evaluasi', $RX, 530, $RW, $AH, $UC);
$R3 = $uc->vertex('Submit Final Evaluasi', $RX, 720, $RW, $AH, $UC);
$R4 = $uc->vertex('Melihat Riwayat Evaluasi', $RX, 830, $RW, $AH, $UC);
$R5 = $uc->vertex('Mengirim Tiket Bantuan', $RX, 950, $RW, $AH, $UC);
foreach ([$R1, $R2, $R3, $R4, $R5] as $u) $uc->edge($resp, $u, '', $ASSOC);

$pil = $uc->vertex('Memilih &amp; Memfilter Mata Kuliah (Semester / BK)', $TX, 430, $SW, 55, $UC);
$drf = $uc->vertex('Simpan Draf Otomatis (Auto-Save)', $TX, 532, $SW, $SH, $UC);
$als = $uc->vertex('Mengisi Alasan Nilai Rendah (Skala 1–2)', $TX, 625, $SW, 55, $UC);
$uc->edge($R2, $pil, 'Include', $DASH . oL . iR);
$uc->edge($R2, $drf, 'Include', $DASH . oL . iR);
$uc->edge($als, $R2, 'Extend', $DASH . oR . iL);

// Use case bersama
$S2 = $uc->vertex('Kelola Profil &amp; Ganti Kata Sandi', 650, 1060, 180, $AH, $UC);
$S1 = $uc->vertex('Login &amp; Autentikasi', 650, 1160, 180, $AH, $UC);
$S3 = $uc->vertex('Lupa / Atur Ulang Kata Sandi', 655, 1270, 170, $SH, $UC);
$uc->edge($S3, $S1, 'Extend', $DASH . oT . iB);
$uc->edge($admin, $S2, '', $ASSOC, [[200, 1095]]);
$uc->edge($admin, $S1, '', $ASSOC, [[180, 1195]]);
$uc->edge($resp, $S2, '', $ASSOC, [[1300, 1095]]);
$uc->edge($resp, $S1, '', $ASSOC, [[1320, 1195]]);
$pages[] = $uc;

/* =========================================================================
 * 2. ACTIVITY: AUTENTIKASI (LOGIN)
 * ========================================================================= */
$p = new Page('Activity - Autentikasi (Login)', 'ad1');
$a = new Act($p, 'Autentikasi (Login)', [['Pengguna (Admin / Responden)', 460], ['Sistem', 480]], 13);
$s   = $a->n('start', 0, 0);
$n1  = $a->n('act', 0, 1, 'Mengakses halaman Login');
$n2  = $a->n('act', 0, 2, 'Memasukkan Email dan Kata Sandi');
$n3  = $a->n('act', 0, 3, 'Menekan tombol Masuk');
$n4  = $a->n('act', 1, 3, 'Memverifikasi token CSRF dan kredensial (akun aktif + password_verify)', -60, 170, 55);
$d1  = $a->n('dec', 1, 4, 'Apakah Data Valid?', -60);
$er  = $a->n('act', 1, 4, 'Menampilkan pesan Email / Kata Sandi salah', 140, 150, 50);
$n5  = $a->n('act', 1, 5, 'Membuat sesi baru (session_regenerate_id) dan menyimpan peran', -60, 170, 55);
$d2  = $a->n('dec', 1, 6, 'Wajib ganti kata sandi?', -60);
$pf  = $a->n('act', 1, 6, 'Menampilkan halaman Profil (Wajib Ganti Sandi)', 140, 150, 50);
$gs  = $a->n('act', 0, 7, 'Mengisi dan menyimpan kata sandi baru');
$sv  = $a->n('act', 1, 8, 'Menyimpan hash kata sandi baru', 140, 150, 50);
$d3  = $a->n('dec', 1, 9, 'Pengecekan Role / Hak Akses', -60);
$ad  = $a->n('act', 1, 10, 'Menampilkan Dashboard Admin', -150, 140, 50);
$rs  = $a->n('act', 1, 10, 'Menampilkan Beranda Responden', 120, 140, 50);
$mg  = $a->n('dec', 1, 11, '', -15);
$e   = $a->n('end', 1, 12, '', -15);
$a->f($s, $n1); $a->f($n1, $n2); $a->f($n2, $n3);
$a->f($n3, $n4, '', oR . iL);
$a->f($n4, $d1, '', oB . iT);
$a->f($d1, $er, 'Tidak', oR . iL);
$a->f($er, $n2, '', oT . iR, [[$a->cx(1, 140), $a->cy(2)]]);
$a->f($d1, $n5, 'Ya', oB . iT);
$a->f($n5, $d2, '', oB . iT);
$a->f($d2, $pf, 'Ya', oR . iL);
$a->f($pf, $gs, '', oB . iR);
$a->f($gs, $sv, '', oR . iL);
$a->f($sv, $d3, '', oB . iR);
$a->f($d2, $d3, 'Tidak', oB . iT);
$a->f($d3, $ad, 'Admin', oL . iT);
$a->f($d3, $rs, 'Responden', oR . iT);
$a->f($ad, $mg, '', oB . iL);
$a->f($rs, $mg, '', oB . iR);
$a->f($mg, $e, '', oB . iT);
$pages[] = $p;

/* =========================================================================
 * 3. ACTIVITY: KELOLA MASTER DATA PENGGUNA & IMPOR
 * ========================================================================= */
$p = new Page('Activity - Kelola Master Pengguna', 'ad2');
$a = new Act($p, 'Kelola Master Data Pengguna &amp; Impor Massal', [['Admin', 440], ['Sistem', 620]], 11);
$s   = $a->n('start', 0, 0);
$n1  = $a->n('act', 0, 1, 'Membuka menu Master Data (Mahasiswa / Dosen / Alumni / Perusahaan)', 0, 170, 55);
$l1  = $a->n('act', 1, 1, 'Menampilkan daftar pengguna sesuai peran', 0, 170, 50);
$d1  = $a->n('dec', 0, 2, 'Pilih aksi?');
$t1  = $a->n('act', 0, 3, 'Mengisi form tambah (nama, email, nomor induk)', -140, 125, 60);
$t2  = $a->n('act', 0, 3, 'Mengubah data pada form edit', 0, 125, 60);
$t3  = $a->n('act', 0, 3, 'Mengunduh template lalu mengunggah berkas .xlsx / .xls / .csv', 140, 125, 60);
$sv  = $a->n('act', 1, 4, 'INSERT / UPDATE pengguna &amp; profil_pengguna (transaksi, sandi default ter-hash)', -215, 150, 60);
$df  = $a->n('dec', 1, 4, 'Format berkas valid?', 215);
$de  = $a->n('dec', 1, 5, 'Email unik?', -215);
$fe  = $a->n('act', 1, 5, 'Menampilkan pesan format tidak valid', 70, 125, 50);
$rd  = $a->n('act', 1, 5, 'Membaca baris data (baris header dilewati)', 215, 140, 50);
$ok  = $a->n('act', 1, 6, 'Commit &amp; tampilkan notifikasi sukses', -215, 140, 50);
$rb  = $a->n('act', 1, 6, 'Rollback &amp; tampilkan pesan gagal', -70, 125, 50);
$ins = $a->n('act', 1, 6, 'Menyimpan tiap baris; email duplikat dihitung gagal', 215, 140, 55);
$lg  = $a->n('act', 1, 7, 'Mencatat riwayat ke tabel log_impor', 215, 140, 50);
$sm  = $a->n('act', 1, 8, 'Menampilkan ringkasan impor (berhasil / gagal)', 215, 140, 50);
$mg  = $a->n('dec', 1, 9, '', 0);
$e   = $a->n('end', 1, 10, '', 0);
$a->f($s, $n1); $a->f($n1, $l1, '', oR . iL); $a->f($l1, $d1, '', oB . iR);
$a->f($d1, $t1, 'Tambah', oL . iT); $a->f($d1, $t2, 'Edit', oB . iT); $a->f($d1, $t3, 'Impor', oR . iT);
$a->f($t1, $sv, '', oB . iT); $a->f($t2, $sv, '', oB . iT); $a->f($t3, $df, '', oB . iT);
$a->f($sv, $de, '', oB . iT);
$a->f($de, $ok, 'Ya', oB . iT); $a->f($de, $rb, 'Tidak', oR . iT);
$a->f($df, $fe, 'Tidak', oL . iT); $a->f($df, $rd, 'Ya', oB . iT);
$a->f($rd, $ins); $a->f($ins, $lg); $a->f($lg, $sm);
$a->f($ok, $mg, '', oB . iL); $a->f($rb, $mg, '', oB . iT); $a->f($fe, $mg, '', oB . iT); $a->f($sm, $mg, '', oB . iR);
$a->f($mg, $e, '', oB . iT);
$pages[] = $p;

/* =========================================================================
 * 4. ACTIVITY: KELOLA PERIODE EVALUASI
 * ========================================================================= */
$p = new Page('Activity - Kelola Periode', 'ad3');
$a = new Act($p, 'Kelola Periode Evaluasi', [['Admin', 440], ['Sistem', 540]], 8);
$s  = $a->n('start', 0, 0);
$n1 = $a->n('act', 0, 1, 'Membuka menu Periode Evaluasi');
$l1 = $a->n('act', 1, 1, 'Menampilkan daftar periode beserta status (draft / buka / tutup)', 0, 180, 55);
$d1 = $a->n('dec', 0, 2, 'Pilih aksi?');
$t1 = $a->n('act', 0, 3, 'Mengisi tahun akademik lalu klik Tambah', -140, 125, 55);
$t2 = $a->n('act', 0, 3, 'Klik Buka pada periode', 0, 125, 55);
$t3 = $a->n('act', 0, 3, 'Klik Tutup pada periode', 140, 125, 55);
$x1 = $a->n('act', 1, 4, "INSERT periode (status = 'draft')", -175, 140, 55);
$x2 = $a->n('act', 1, 4, "Transaksi: tutup periode yang sedang buka, lalu set periode terpilih 'buka'", 0, 160, 70);
$x3 = $a->n('act', 1, 4, "UPDATE status = 'tutup'", 175, 140, 55);
$mg = $a->n('dec', 1, 5, '');
$x4 = $a->n('act', 1, 6, 'Menampilkan notifikasi &amp; daftar periode terbaru', 0, 180, 50);
$e  = $a->n('end', 1, 7);
$a->f($s, $n1); $a->f($n1, $l1, '', oR . iL); $a->f($l1, $d1, '', oB . iR);
$a->f($d1, $t1, 'Tambah', oL . iT); $a->f($d1, $t2, 'Buka', oB . iT); $a->f($d1, $t3, 'Tutup', oR . iT);
$a->f($t1, $x1, '', oB . iT); $a->f($t2, $x2, '', oB . iT); $a->f($t3, $x3, '', oB . iT);
$a->f($x1, $mg, '', oB . iL); $a->f($x2, $mg, '', oB . iT); $a->f($x3, $mg, '', oB . iR);
$a->f($mg, $x4); $a->f($x4, $e);
$pages[] = $p;

/* =========================================================================
 * 5. ACTIVITY: KELOLA BANK KUESIONER (CRUD + SOFT DELETE)
 * ========================================================================= */
$p = new Page('Activity - Kelola Bank Kuesioner', 'ad4');
$a = new Act($p, 'Kelola Bank Kuesioner (CRUD &amp; Soft Delete)', [['Admin', 440], ['Sistem', 540]], 11);
$s  = $a->n('start', 0, 0);
$n1 = $a->n('act', 0, 1, 'Membuka menu Bank Kuesioner');
$n2 = $a->n('act', 0, 2, 'Memilih submenu: Kategori Kompetensi / Bahan Kajian / Mata Kuliah / Pertanyaan', 0, 180, 60);
$l1 = $a->n('act', 1, 2, 'Menampilkan daftar data beserta status aktif', 0, 170, 50);
$d1 = $a->n('dec', 0, 3, 'Pilih aksi?');
$t1 = $a->n('act', 0, 4, 'Mengisi form Tambah data', -140, 125, 60);
$t2 = $a->n('act', 0, 4, 'Mengubah data (kode, nama, semester, SKS, kategori, BK, opsi)', 0, 125, 70);
$t3 = $a->n('act', 0, 4, 'Menekan toggle Aktif / Nonaktif', 140, 125, 60);
$v1 = $a->n('act', 1, 5, 'Memvalidasi token CSRF &amp; input wajib', -130, 150, 50);
$sd = $a->n('act', 1, 5, 'UPDATE kolom aktif (Soft Delete) — data historis tetap utuh', 165, 150, 60);
$d2 = $a->n('dec', 1, 6, 'Valid? (kode unik)', -130);
$er = $a->n('act', 1, 6, 'Menampilkan pesan galat', 40, 110, 45);
$in = $a->n('act', 1, 7, 'INSERT / UPDATE data + relasi (mata_kuliah_bk / opsi_pertanyaan)', -130, 160, 60);
$mg = $a->n('dec', 1, 8, '', 40);
$sh = $a->n('act', 1, 9, 'Menampilkan notifikasi &amp; daftar terbaru', 40, 170, 50);
$e  = $a->n('end', 1, 10, '', 40);
$a->f($s, $n1); $a->f($n1, $n2); $a->f($n2, $l1, '', oR . iL); $a->f($l1, $d1, '', oB . iR);
$a->f($d1, $t1, 'Tambah', oL . iT); $a->f($d1, $t2, 'Edit', oB . iT); $a->f($d1, $t3, 'Nonaktifkan', oR . iT);
$a->f($t1, $v1, '', oB . iT); $a->f($t2, $v1, '', oB . iT); $a->f($t3, $sd, '', oB . iT);
$a->f($v1, $d2, '', oB . iT);
$a->f($d2, $er, 'Tidak', oR . iL); $a->f($d2, $in, 'Ya', oB . iT);
$a->f($in, $mg, '', oB . iL); $a->f($er, $mg, '', oB . iT); $a->f($sd, $mg, '', oB . iR);
$a->f($mg, $sh); $a->f($sh, $e);
$pages[] = $p;

/* =========================================================================
 * 6. ACTIVITY: PENGISIAN KUESIONER (AUTO-SAVE & DUAL-LAYER VALIDATION)
 * ========================================================================= */
$p = new Page('Activity - Pengisian Kuesioner', 'ad5');
$a = new Act($p, 'Pengisian Kuesioner Evaluasi', [['Responden (Mahasiswa / Alumni / Dosen / Perusahaan)', 460], ['Sistem', 540]], 15);
$s   = $a->n('start', 0, 0);
$n1  = $a->n('act', 0, 1, 'Membuka halaman Beranda');
$c1  = $a->n('act', 1, 1, "Memeriksa periode berstatus 'buka' dan status pengisian pengguna", -100, 180, 55);
$d1  = $a->n('dec', 1, 2, 'Periode buka &amp; belum final?', -100);
$inf = $a->n('act', 1, 2, 'Menampilkan info (tidak ada periode aktif / evaluasi sudah selesai)', 150, 160, 60);
$e1  = $a->n('end', 1, 3, '', 150);
$ld  = $a->n('act', 1, 3, 'Memuat formulir &amp; draf terakhir (tab_terakhir, jawaban tersimpan)', -100, 180, 55);
$pm  = $a->n('act', 0, 4, 'Memfilter mata kuliah (Semester / Bahan Kajian) dan memilih min. 1 mata kuliah', -60, 190, 60);
$is  = $a->n('act', 0, 5, 'Mengisi penilaian (skala / pilihan ganda / teks)', -60, 180, 50);
$d2  = $a->n('dec', 0, 6, 'Nilai skala 1 atau 2?', -60);
$al  = $a->n('act', 0, 7, 'Mengisi alasan wajib (min. 10 karakter) — validasi klien', 120, 160, 55);
$m1  = $a->n('dec', 0, 8, '', -60);
$ds  = $a->n('act', 1, 8, 'Menyimpan draf otomatis via AJAX (simpan-draf.php)', -100, 180, 50);
$d3  = $a->n('dec', 0, 9, 'Semua pertanyaan terisi?', -60);
$sb  = $a->n('act', 0, 10, 'Menekan tombol Submit Final', -60);
$vs  = $a->n('act', 1, 10, 'Validasi server: CSRF, periode buka, min. 1 mata kuliah, pertanyaan wajib, alasan nilai 1–2 (≥ 10 karakter)', -100, 200, 75);
$d4  = $a->n('dec', 1, 11, 'Lolos validasi?', -100);
$ge  = $a->n('act', 1, 11, 'Menampilkan pesan galat &amp; kembali ke formulir', 150, 150, 55);
$fn  = $a->n('act', 1, 12, "UPDATE pengisian: status = 'final', waktu_submit = NOW()", -100, 190, 55);
$ok  = $a->n('act', 1, 13, 'Menampilkan notifikasi sukses di Beranda', -100, 180, 50);
$e   = $a->n('end', 1, 14, '', -100);
$a->f($s, $n1); $a->f($n1, $c1, '', oR . iL); $a->f($c1, $d1, '', oB . iT);
$a->f($d1, $inf, 'Tidak', oR . iL); $a->f($inf, $e1, '', oB . iT);
$a->f($d1, $ld, 'Ya', oB . iT);
$a->f($ld, $pm, '', oB . iR);
$a->f($pm, $is); $a->f($is, $d2, '', oB . iT);
$a->f($d2, $al, 'Ya', oR . iT); $a->f($d2, $m1, 'Tidak', oB . iT); $a->f($al, $m1, '', oB . iR);
$a->f($m1, $ds, '', oR . iL);
$a->f($ds, $d3, '', oB . iR);
$a->f($d3, $is, 'Belum', oL . iL, [[$a->cx(0, -205), $a->cy(9)], [$a->cx(0, -205), $a->cy(5)]]);
$a->f($d3, $sb, 'Sudah', oB . iT);
$a->f($sb, $vs, '', oR . iL);
$a->f($vs, $d4, '', oB . iT);
$a->f($d4, $ge, 'Tidak', oR . iL);
$a->f($ge, $is, '', oT . iR, [[$a->cx(1, 150), $a->cy(5)]]);
$a->f($d4, $fn, 'Ya', oB . iT); $a->f($fn, $ok); $a->f($ok, $e);
$pages[] = $p;

/* =========================================================================
 * 7. ACTIVITY: DASHBOARD ANALITIK & LAPORAN
 * ========================================================================= */
$p = new Page('Activity - Dashboard & Laporan', 'ad6');
$a = new Act($p, 'Dashboard Analitik &amp; Laporan', [['Admin', 460], ['Sistem', 540]], 13);
$s   = $a->n('start', 0, 0);
$n1  = $a->n('act', 0, 1, 'Membuka halaman Dashboard');
$ap  = $a->n('act', 1, 1, 'Membaca periode terpilih &amp; memanggil API data-dashboard.php (AJAX)', -90, 190, 55);
$ag  = $a->n('act', 1, 2, 'Mengagregasi data: kompetensi, peringkat mata kuliah, sebaran alumni, ulasan', -90, 190, 60);
$d1  = $a->n('dec', 1, 3, 'Data tersedia?', -90);
$rd  = $a->n('act', 1, 4, "Merender Radar Chart, Tabel Kompetensi, Top / Bottom 5 (label 'Data Terbatas' jika &lt; 3 penilai), Doughnut &amp; Ulasan", -90, 200, 80);
$em  = $a->n('act', 1, 4, 'Merender empty state (kerangka grafik kosong)', 150, 150, 55);
$m1  = $a->n('dec', 1, 5, '', -90);
$d2  = $a->n('dec', 0, 6, 'Tindakan selanjutnya?');
$fp  = $a->n('act', 0, 7, 'Memilih filter periode', -145, 120, 50);
$lp  = $a->n('act', 0, 7, 'Membuka Pusat Laporan &amp; memilih format', 0, 130, 55);
$e1  = $a->n('end', 0, 7, '', 150);
$d3  = $a->n('dec', 0, 8, 'Format laporan?');
$pd  = $a->n('act', 1, 9, 'Menyusun tampilan cetak PDF (cetak-pdf.php)', -130, 150, 55);
$ex  = $a->n('act', 1, 9, 'Membuat berkas Excel (export-excel.php)', 120, 150, 55);
$m2  = $a->n('dec', 1, 10, '');
$dl  = $a->n('act', 0, 11, 'Mengunduh / mencetak laporan');
$e   = $a->n('end', 0, 12);
$a->f($s, $n1); $a->f($n1, $ap, '', oR . iL); $a->f($ap, $ag); $a->f($ag, $d1, '', oB . iT);
$a->f($d1, $rd, 'Ya', oB . iT); $a->f($d1, $em, 'Tidak', oR . iT);
$a->f($rd, $m1, '', oB . iT); $a->f($em, $m1, '', oB . iR);
$a->f($m1, $d2, '', oL . iR);
$a->f($d2, $fp, 'Ganti periode', oL . iT);
$a->f($d2, $lp, 'Cetak laporan', oB . iT);
$a->f($d2, $e1, 'Selesai', oR . iT);
$a->f($fp, $ap, '', oT . iT, [[$a->cx(0, -145), $a->cy(0) + 45], [$a->cx(1, -90), $a->cy(0) + 45]]);
$a->f($lp, $d3, '', oB . iT);
$a->f($d3, $pd, 'PDF', oB . iT); $a->f($d3, $ex, 'Excel', oR . iT);
$a->f($pd, $m2, '', oB . iL); $a->f($ex, $m2, '', oB . iR);
$a->f($m2, $dl, '', oB . iR); $a->f($dl, $e);
$pages[] = $p;

/* =========================================================================
 * 8. ACTIVITY: TIKET BANTUAN
 * ========================================================================= */
$p = new Page('Activity - Tiket Bantuan', 'ad7');
$a = new Act($p, 'Tiket Bantuan (Helpdesk)', [['Responden', 360], ['Sistem', 380], ['Admin', 360]], 10);
$s  = $a->n('start', 0, 0);
$r1 = $a->n('act', 0, 1, 'Membuka menu Tiket Bantuan');
$r2 = $a->n('act', 0, 2, 'Mengisi subjek dan uraian kendala');
$r3 = $a->n('act', 0, 3, 'Menekan tombol Kirim');
$s1 = $a->n('act', 1, 3, "Validasi CSRF &amp; menyimpan tiket (status = 'baru')", 0, 170, 55);
$m1 = $a->n('act', 2, 4, 'Membuka menu Tiket');
$s2 = $a->n('act', 1, 5, "Menampilkan daftar tiket (prioritas 'baru' → 'diproses' → 'selesai')", 0, 180, 60);
$m2 = $a->n('act', 2, 6, 'Menulis balasan &amp; mengubah status (diproses / selesai)', 0, 170, 55);
$s3 = $a->n('act', 1, 7, 'UPDATE status dan balasan_admin', 0, 170, 50);
$r4 = $a->n('act', 0, 8, 'Melihat status &amp; balasan tiket');
$e  = $a->n('end', 0, 9);
$a->f($s, $r1); $a->f($r1, $r2); $a->f($r2, $r3);
$a->f($r3, $s1, '', oR . iL); $a->f($s1, $m1, '', oR . iT);
$a->f($m1, $s2, '', oB . iR); $a->f($s2, $m2, '', oR . iT);
$a->f($m2, $s3, '', oB . iR); $a->f($s3, $r4, '', oB . iR); $a->f($r4, $e);
$pages[] = $p;

/* =========================================================================
 * 9. ERD — diparsing dari database/data_kosong.sql
 * ========================================================================= */
$sql = file_get_contents($ROOT . '/database/data_kosong.sql');
if (substr($sql, 0, 2) === "\xFF\xFE") $sql = mb_convert_encoding(substr($sql, 2), 'UTF-8', 'UTF-16LE');
$sql = str_replace("\r\n", "\n", $sql);
preg_match_all('/CREATE TABLE `(\w+)` \((.*?)\n\) ENGINE/s', $sql, $mm, PREG_SET_ORDER);
$tables = [];
foreach ($mm as $t) {
    $cols = []; $pk = []; $uk = []; $fk = [];
    foreach (explode("\n", $t[2]) as $line) {
        $line = rtrim(trim($line), ',');
        if (preg_match('/^`(\w+)` (.+)$/', $line, $c)) {
            $type = preg_split('/\s+(NOT NULL|DEFAULT|GENERATED|CHARACTER|AUTO_INCREMENT|CHECK|COLLATE)/', $c[2])[0];
            $cols[$c[1]] = ['type' => $type, 'null' => stripos($c[2], 'NOT NULL') === false, 'gen' => stripos($c[2], 'GENERATED') !== false];
        } elseif (preg_match('/^PRIMARY KEY \((.+)\)/', $line, $q)) {
            $pk = array_map(fn($x) => trim($x, '` '), explode(',', $q[1]));
        } elseif (preg_match('/^UNIQUE KEY `\w+` \(`(\w+)`\)$/', $line, $q)) {
            $uk[] = $q[1];
        } elseif (preg_match('/FOREIGN KEY \(`(\w+)`\) REFERENCES `(\w+)` \(`(\w+)`\)/', $line, $q)) {
            $fk[$q[1]] = [$q[2], $q[3]];
        }
    }
    $tables[$t[1]] = compact('cols', 'pk', 'uk', 'fk');
}

$erd = new Page('ERD', 'erd');
$W1 = 36; $W2 = 150; $W3 = 104; $TW = $W1 + $W2 + $W3; $RH = 22; $GAPX = 110;
$layout = [ // tabel => [kolom grid, y]
    'profil_pengguna'     => [0, 40],  'log_impor'        => [0, 300], 'tiket'          => [0, 540],
    'pengguna'            => [1, 40],  'periode_evaluasi' => [1, 340],
    'pengisian'           => [2, 40],  'pengisian_matkul' => [2, 330],
    'jawaban'             => [3, 40],  'jawaban_multi'    => [3, 330], 'mata_kuliah'    => [3, 500],
    'pertanyaan'          => [4, 40],  'opsi_pertanyaan'  => [4, 560], 'mata_kuliah_bk' => [4, 790],
    'kategori_kompetensi' => [5, 40],  'bahan_kajian'     => [5, 560],
];
$TABLE = 'shape=table;startSize=30;container=1;collapsible=1;childLayout=tableLayout;fixedRows=1;rowLines=0;fontStyle=1;align=center;resizeLast=1;html=1;fontSize=12;';
$ROW   = 'shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;top=0;left=0;right=0;html=1;';
$CELL  = 'shape=partialRectangle;connectable=0;fillColor=none;top=0;left=0;bottom=0;overflow=hidden;whiteSpace=wrap;html=1;fontSize=11;';
$rowIds = [];
$maxX = 0; $maxY = 0;
foreach ($tables as $name => $t) {
    [$col, $y] = $layout[$name] ?? [6, 40];
    $x = 40 + $col * ($TW + $GAPX);
    $H = 30 + count($t['cols']) * $RH;
    $tid = $erd->vertex($name, $x, $y, $TW, $H, $TABLE);
    $i = 0; $lastPk = end($t['pk']);
    foreach ($t['cols'] as $cn => $cd) {
        $keys = [];
        if (in_array($cn, $t['pk'])) $keys[] = 'PK';
        if (isset($t['fk'][$cn])) $keys[] = 'FK';
        if (in_array($cn, $t['uk'])) $keys[] = 'UK';
        $type = strtoupper($cd['type']);
        if (str_starts_with($type, 'ENUM') && strlen($type) > 24) $type = 'ENUM(…)';
        if ($cd['gen']) $type .= ' (GEN)';
        $isPk = in_array($cn, $t['pk']);
        $rid = $erd->vertex('', 0, 30 + $i * $RH, $TW, $RH, $ROW . 'bottom=' . ($cn === $lastPk ? 1 : 0) . ';', $tid);
        $erd->vertex(implode(',', $keys), 0, 0, $W1, $RH, $CELL . 'right=1;fontStyle=1;fontSize=10;', $rid);
        $erd->vertex($cn, $W1, 0, $W2, $RH, $CELL . 'right=0;align=left;spacingLeft=4;' . ($isPk ? 'fontStyle=5;' : (isset($t['fk'][$cn]) ? 'fontStyle=2;' : '')), $rid);
        $erd->vertex($type, $W1 + $W2, 0, $W3, $RH, $CELL . 'right=0;align=left;spacingLeft=4;fontColor=#555555;fontSize=10;', $rid);
        $rowIds[$name][$cn] = $rid;
        $i++;
    }
    $maxX = max($maxX, $x + $TW); $maxY = max($maxY, $y + $H);
}
$relCount = 0;
foreach ($tables as $name => $t) {
    foreach ($t['fk'] as $cn => [$refT, $refC]) {
        $nullable = $t['cols'][$cn]['null'];
        $oneToOne = ($t['pk'] === [$cn]); // PK sekaligus FK => relasi 1:1
        $style = 'edgeStyle=entityRelationEdgeStyle;fontSize=12;html=1;rounded=0;endFill=0;startFill=0;endSize=10;startSize=10;'
            . 'startArrow=' . ($nullable ? 'ERzeroToOne' : 'ERmandOne') . ';'
            . 'endArrow=' . ($oneToOne ? 'ERzeroToOne' : 'ERzeroToMany') . ';';
        $erd->edge($rowIds[$refT][$refC], $rowIds[$name][$cn], '', $style);
        $relCount++;
    }
}
// Legenda
$erd->vertex('<b>Keterangan</b><br>PK = Primary Key (garis bawah)<br>FK = Foreign Key (miring)<br>UK = Unique Key<br>GEN = Generated Column<br>Notasi relasi: Crow\'s Foot', 40, $maxY + 40, 260, 100, 'text;html=1;align=left;verticalAlign=top;spacingLeft=8;spacingTop=4;strokeColor=#666666;fillColor=#ffffff;fontSize=11;');
$erd->w = $maxX + 60; $erd->h = $maxY + 180;
$pages[] = $erd;

/* =========================================================================
 * OUTPUT
 * ========================================================================= */
$diagrams = '';
foreach ($pages as $i => $pg) $diagrams .= $pg->diagram($i + 1);
$mxfile = '<mxfile host="app.diagrams.net" agent="generate_diagram.php" version="24.0.0">' . $diagrams . '</mxfile>';
file_put_contents($OUT_DRAWIO, $mxfile);

// Viewer HTML
$nav = ''; $sections = '';
foreach ($pages as $i => $pg) {
    $no = $i + 1; $anchor = 'diagram-' . $no;
    $nav .= '<a href="#' . $anchor . '" id="nav-' . $no . '"><span>' . $no . '</span>' . esc($pg->name) . '</a>';
    $cfg = json_encode([
        'highlight' => '#0000ff', 'nav' => true, 'resize' => true, 'lightbox' => true,
        'toolbar' => 'zoom lightbox', 'edit' => '_blank',
        'xml' => '<mxfile>' . $pg->diagram($no) . '</mxfile>',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $sections .= '<section id="' . $anchor . '"><h2>' . $no . '. ' . esc($pg->name) . '</h2><div class="canvas"><div class="mxgraph" style="max-width:100%" data-mxgraph="' . htmlspecialchars($cfg, ENT_QUOTES, 'UTF-8') . '"></div></div></section>';
}
$generated = date('d-m-Y H:i');
$html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Viewer Arsitektur Diagram Bab III – Sistem Evaluasi Kurikulum</title>
<meta name="description" content="Pratinjau Use Case, Activity Diagram, dan ERD Sistem Evaluasi Kurikulum dalam gaya draw.io untuk dokumen SRS Bab III.">
<style>
*{box-sizing:border-box}body{margin:0;font-family:Helvetica,Arial,sans-serif;background:#eef1f5;color:#1f2937;display:flex}
aside{position:sticky;top:0;height:100vh;width:290px;flex-shrink:0;background:#fff;border-right:1px solid #dde2e8;padding:22px 16px;overflow-y:auto}
aside h1{font-size:16px;margin:0 0 4px}aside p{font-size:12px;color:#6b7280;margin:0 0 18px;line-height:1.5}
aside a{display:flex;gap:10px;align-items:center;padding:9px 10px;border-radius:8px;color:#374151;text-decoration:none;font-size:13px;transition:.15s}
aside a span{width:24px;height:24px;border-radius:6px;background:#f1f5f9;display:grid;place-items:center;font-size:11px;font-weight:bold}
aside a:hover{background:#eff6ff;color:#1d4ed8}aside a:hover span{background:#1d4ed8;color:#fff}
.dl{display:block;margin-top:18px;text-align:center;background:#1d4ed8;color:#fff!important;padding:10px;border-radius:8px;font-weight:bold}
.dl:hover{background:#1e40af!important}
main{flex:1;padding:28px;min-width:0}section{margin-bottom:36px}
section h2{font-size:15px;margin:0 0 10px;padding-left:10px;border-left:4px solid #1d4ed8}
.canvas{background:#fff;border:1px solid #dde2e8;border-radius:10px;padding:20px;overflow:auto}
</style>
</head>
<body>
<aside>
<h1>Diagram Bab III</h1>
<p>Sistem Evaluasi Kurikulum. Gaya draw.io. Klik ikon perbesar untuk tampilan penuh / ikon pensil untuk membuka di draw.io.<br>Dibuat: {$generated}</p>
<nav>{$nav}</nav>
<a class="dl" href="docs/diagram/diagram_bab3.drawio" download id="btn-unduh-drawio">Unduh berkas .drawio</a>
</aside>
<main>{$sections}</main>
<!-- Khusus halaman dokumentasi: renderer resmi draw.io (butuh internet). -->
<script src="https://viewer.diagrams.net/js/viewer-static.min.js"></script>
</body>
</html>
HTML;
file_put_contents($OUT_VIEWER, $html);

echo "OK: " . count($pages) . " halaman diagram\n";
echo "Tabel ERD: " . count($tables) . " (" . implode(', ', array_keys($tables)) . ")\n";
echo "Relasi FK: {$relCount}\n";
echo "-> {$OUT_DRAWIO}\n-> {$OUT_VIEWER}\n";
