<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumentasi 
         Sistem Evaluasi Kurikulum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script type="module">
        import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';
        mermaid.initialize({ startOnLoad: true, theme: 'default' });
    </script>
    <style>
        .diagram-container {
            display: flex;
            justify-content: center;
            overflow-x: auto;
            background-color: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans p-8">

    <div class="max-w-6xl mx-auto">
        <header class="mb-10 text-center border-b pb-6 border-gray-200">
            <h1 class="text-3xl font-bold text-gray-900">Arsitektur & Pemodelan Visual Sistem</h1>
            <p class="text-gray-500 mt-2">Sistem Informasi Evaluasi Kurikulum Program Studi</p>
            <p class="text-sm text-blue-600 mt-4">Halaman ini otomatis merender diagram dari sintaks Mermaid.js. Anda dapat melakukan *Screenshot* gambar diagram di bawah ini untuk dimasukkan ke laporan SRS Anda.</p>
        </header>

        <!-- 1. Use Case Diagram -->
        <section class="mb-12">
            <h2 class="text-xl font-bold mb-4 text-gray-800 border-l-4 border-blue-500 pl-3">1. Use Case Diagram: Interaksi Aktor & Modul</h2>
            <div class="diagram-container">
                <div class="mermaid">
                    flowchart LR
                        %% Styling untuk Actor agar berbeda
                        classDef actorStyle fill:#fff,stroke:#000,stroke-width:2px,shape:circle
                        classDef usecaseStyle fill:#f9f9f9,stroke:#333,stroke-width:1px,shape:ellipse

                        %% Actors (menggunakan bentuk lingkaran untuk membedakan dari use case)
                        Admin((Admin)):::actorStyle
                        Mhs((Mahasiswa)):::actorStyle
                        Dsn((Dosen)):::actorStyle
                        Alm((Alumni)):::actorStyle
                        Prs((Perusahaan)):::actorStyle

                        %% Boundary Sistem
                        subgraph "Sistem Informasi Evaluasi Kurikulum"
                            UC1([Login & Autentikasi RBAC]):::usecaseStyle
                            UC2([Kelola Master Data / Impor]):::usecaseStyle
                            UC3([Kelola Periode & Instrumen]):::usecaseStyle
                            UC4([Pilih Filter Mata Kuliah]):::usecaseStyle
                            UC5([Isi Kuesioner & Auto-Save]):::usecaseStyle
                            UC6([Submit Final Terkunci]):::usecaseStyle
                            UC7([Dasbor Analitik & Ekspor]):::usecaseStyle
                        end

                        %% Admin Relations
                        Admin --- UC1
                        Admin --- UC2
                        Admin --- UC3
                        Admin --- UC7

                        %% Responden Relations
                        Mhs --- UC1
                        Dsn --- UC1
                        Alm --- UC1
                        Prs --- UC1

                        Mhs --- UC4
                        Dsn --- UC4
                        Alm --- UC4

                        Mhs --- UC5
                        Dsn --- UC5
                        Alm --- UC5
                        Prs --- UC5

                        Mhs --- UC6
                        Dsn --- UC6
                        Alm --- UC6
                        Prs --- UC6
                </div>
            </div>
        </section>

        <!-- 2. Activity Diagram: RBAC -->
        <section class="mb-12">
            <h2 class="text-xl font-bold mb-4 text-gray-800 border-l-4 border-blue-500 pl-3">2. Activity Diagram: Autentikasi dan Perutean Dinamis (RBAC)</h2>
            <div class="diagram-container">
                <div class="mermaid">
                    flowchart TD
                        %% Meniru bentuk Swimlane Draw.io
                        subgraph Pengguna ["Pengguna (Admin / Responden)"]
                            direction TB
                            start((Mulai)) --> input[Mengakses Halaman Login]
                            input --> submit[Memasukkan Email & Password]
                            submit --> click[Menekan Tombol Login]
                        end

                        subgraph Sistem ["Sistem (Backend)"]
                            direction TB
                            verify[Memverifikasi Kredensial ke Database]
                            cek{Kredensial Valid?}
                            error[Menampilkan Pesan Galat]
                            cekRole{Pengecekan Role}
                            initAdmin[Inisialisasi Session Admin]
                            initResp[Inisialisasi Session Responden]
                        end

                        subgraph Tampilan ["Antarmuka (Frontend)"]
                            direction TB
                            dash[Menampilkan Dasbor Kendali]
                            portal[Menampilkan Portal Kuesioner]
                            selesai((Selesai))
                        end

                        click --> verify
                        verify --> cek
                        cek -- Tidak --> error
                        error --> input
                        
                        cek -- Ya --> cekRole
                        cekRole -- Admin --> initAdmin
                        initAdmin --> dash
                        dash --> selesai
                        
                        cekRole -- Responden --> initResp
                        initResp --> portal
                        portal --> selesai
                </div>
            </div>
        </section>

        <!-- 3. Activity Diagram: Kuesioner -->
        <section class="mb-12">
            <h2 class="text-xl font-bold mb-4 text-gray-800 border-l-4 border-blue-500 pl-3">3. Activity Diagram: Pengisian Kuesioner (Save & Recovery)</h2>
            <div class="diagram-container">
                <div class="mermaid">
                    flowchart TD
                        subgraph Responden ["Responden"]
                            direction TB
                            mulai((Mulai)) --> login[Login ke Portal]
                            pilih[Memilih Mata Kuliah via Filter]
                            isi[Mulai Mengisi Skala/Teks Kuesioner]
                            submitFinal[Menekan Tombol 'Submit Final']
                        end

                        subgraph Sistem ["Sistem Database"]
                            direction TB
                            cekSesi{Pemeriksaan Status Sesi}
                            tolak[Sistem Menolak Akses Formulir]
                            tarik[Menarik Data Draft Terakhir]
                            cekSkala{Apakah Nilai Skala <= 2?}
                            munculAlasan[Muncul Form Alasan Wajib]
                            autosave[(Simpan Otomatis via AJAX)]
                            cekSelesai{Formulir Selesai?}
                            kunci[(Ubah Status DB Menjadi Final)]
                            endFlow((Selesai))
                        end

                        login --> cekSesi
                        cekSesi -- Final --> tolak
                        cekSesi -- Draft --> tarik
                        tarik --> pilih
                        pilih --> isi
                        
                        isi --> cekSkala
                        cekSkala -- Ya --> munculAlasan
                        cekSkala -- Tidak --> autosave
                        munculAlasan --> autosave
                        
                        autosave --> cekSelesai
                        cekSelesai -- Belum --> isi
                        cekSelesai -- Sudah --> submitFinal
                        
                        submitFinal --> kunci
                        kunci --> endFlow
                </div>
            </div>
        </section>

        <!-- 4. Activity Diagram: Manajemen Instrumen -->
        <section class="mb-12">
            <h2 class="text-xl font-bold mb-4 text-gray-800 border-l-4 border-blue-500 pl-3">4. Activity Diagram: Manajemen Instrumen (Soft-Delete)</h2>
            <div class="diagram-container">
                <div class="mermaid">
                    flowchart TD
                        A([Admin Akses Master Instrumen]) --> B{Pilih Aksi?}
                        
                        B -- Tambah Data Baru --> C[Input Detail Matkul / Pertanyaan]
                        C --> D[Pilih Status Bawaan Aktif]
                        D --> E[Sistem Melakukan INSERT ke Database]
                        E --> J([Selesai])
                        
                        B -- Non-Aktifkan Data Lama --> F[Pilih Baris Data]
                        F --> G[Klik Toggle Status menjadi Tidak Aktif]
                        G --> H[Sistem Memblokir Perintah DELETE]
                        H --> I[Sistem Melakukan UPDATE kolom aktif = FALSE]
                        I --> J
                </div>
            </div>
        </section>

        <!-- 5. Activity Diagram: Dasbor -->
        <section class="mb-12">
            <h2 class="text-xl font-bold mb-4 text-gray-800 border-l-4 border-blue-500 pl-3">5. Activity Diagram: Dasbor Analitik & Rekapitulasi Data</h2>
            <div class="diagram-container">
                <div class="mermaid">
                    flowchart TD
                        A([Akses Dasbor Admin]) --> B[Sistem Mengambil Data Periode Aktif]
                        
                        B --> C{Admin Mengubah Filter Periode?}
                        C -- Ya --> D[Sistem Mengirim Permintaan AJAX]
                        D --> E[Query Agregasi Pangkalan Data]
                        C -- Tidak --> E
                        
                        E --> F{Proses Render Visualisasi}
                        
                        F -->|Kalkulasi Opsi Kompetensi| G[Render Radar Chart: Garis Alumni vs Perusahaan]
                        F -->|Kalkulasi Rata-Rata Skala| H[Render Peringkat: Top 5 & Bottom 5 Mata Kuliah]
                        F -->|Ekstraksi Jawaban Teks| I[Render Tabel Ulasan dengan Pajinasi JS]
                        
                        G --> J[Tampilan Dasbor Diperbarui]
                        H --> J
                        I --> J
                        
                        J --> K{Tindakan Ekspor}
                        K -- Ekspor PDF --> L[Sistem Menyusun Layout Print]
                        K -- Ekspor Excel --> M[Sistem Menarik Data CSV/XLSX]
                        
                        L --> N([Selesai])
                        M --> N
                </div>
            </div>
        </section>

        <!-- 6. Entity Relationship Diagram (ERD) -->
        <section class="mb-12">
            <h2 class="text-xl font-bold mb-4 text-gray-800 border-l-4 border-blue-500 pl-3">6. Entity Relationship Diagram (ERD)</h2>
            <div class="diagram-container">
                <div class="mermaid">
                    erDiagram
                        PENGGUNA {
                            int id PK
                            varchar nama
                            varchar email
                            enum peran "Admin, Mahasiswa, Dosen, Alumni, Perusahaan"
                        }
                        
                        MATA_KULIAH {
                            int id PK
                            varchar kode
                            varchar nama
                            int sks
                            boolean aktif
                        }
                        
                        PERTANYAAN {
                            int id PK
                            varchar teks
                            enum tipe "Skala, Teks, Checkbox"
                            enum peran_sasaran
                        }
                        
                        PENGISIAN {
                            int id PK
                            int pengguna_id FK
                            enum status "draft, final"
                        }

                        JAWABAN {
                            int id PK
                            int pengisian_id FK
                            int pertanyaan_id FK
                            int mata_kuliah_id FK
                            int nilai_skala 
                            text teks_alasan 
                        }
                        
                        JAWABAN_MULTI {
                            int jawaban_id FK
                            int opsi_id FK
                        }

                        PENGGUNA ||--o{ PENGISIAN : "melakukan"
                        MATA_KULIAH ||--o{ JAWABAN : "dinilai_pada"
                        PENGISIAN ||--o{ JAWABAN : "menghasilkan"
                        JAWABAN ||--o{ JAWABAN_MULTI : "menyimpan_opsi_ganda"
                </div>
            </div>
        </section>

        <footer class="text-center text-sm text-gray-400 pb-12 border-t pt-6 border-gray-200">
            Dicetak secara otomatis oleh Sistem Informasi Evaluasi Kurikulum
        </footer>
    </div>

</body>
</html>
