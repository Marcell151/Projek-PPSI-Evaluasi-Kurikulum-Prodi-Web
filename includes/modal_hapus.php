<?php
// includes/modal_hapus.php
// Modal reusable untuk pilihan Soft Delete (Nonaktifkan) vs Hard Delete (Konfirmasi 2 Tahap)
?>
<div id="modal-pilihan-hapus" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 hidden flex items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <i class="ph ph-trash text-red-500 text-xl"></i> Pilihan Penghapusan Data
            </h3>
            <button type="button" onclick="tutupModalHapus()" class="text-gray-400 hover:text-gray-700 transition-colors">
                <i class="ph ph-x text-xl"></i>
            </button>
        </div>

        <div class="p-6 space-y-4">
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm text-gray-700">
                Target data: <strong id="hapus-target-label" class="text-gray-900">-</strong>
            </div>

            <!-- Opsi 1: Soft Delete -->
            <div id="box-soft-delete" class="border border-blue-200 bg-blue-50 rounded-xl p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-blue-900 text-sm flex items-center gap-1.5">
                            <i class="ph ph-archive-tray text-blue-600 text-base"></i> Nonaktifkan (Soft Delete)
                            <span class="text-[10px] bg-blue-600 text-white px-2 py-0.5 rounded-full uppercase">Disarankan</span>
                        </p>
                        <p class="text-xs text-blue-800 mt-1">
                            Data dinonaktifkan (tidak muncul di formulir baru / tidak dapat login), namun riwayat evaluasi dan laporan tetap utuh. Dapat diaktifkan kembali kapan saja.
                        </p>
                    </div>
                </div>
                <form action="" method="POST" class="mt-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="soft_delete">
                    <input type="hidden" name="id" id="soft-delete-id">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-1.5">
                        <i class="ph ph-power"></i> Nonaktifkan Data Ini
                    </button>
                </form>
            </div>

            <div id="info-sudah-nonaktif" class="hidden border border-gray-200 bg-gray-50 rounded-xl p-3 text-xs text-gray-600 flex items-center gap-2">
                <i class="ph ph-info text-gray-500 text-base"></i>
                <span>Data ini sudah berstatus <strong>Nonaktif</strong> (Soft Deleted). Anda dapat mengaktifkannya kembali dari tabel atau menghapusnya permanen di bawah ini.</span>
            </div>

            <!-- Opsi 2: Hard Delete dengan Konfirmasi Tahap 2 -->
            <div class="border border-red-200 bg-red-50 rounded-xl p-4">
                <p class="font-semibold text-red-900 text-sm flex items-center gap-1.5">
                    <i class="ph ph-warning-octagon text-red-600 text-base"></i> Hapus Permanen (Hard Delete)
                </p>
                <p class="text-xs text-red-800 mt-1">
                    Menghapus data dari database secara permanen dan <strong>tidak dapat dibatalkan</strong>. Hanya berhasil jika data belum memiliki riwayat transaksi/relasi.
                </p>

                <!-- Tombol buka tahap 2 -->
                <div id="hard-delete-step1" class="mt-3">
                    <button type="button" onclick="bukaKonfirmasiHardDelete()" class="w-full bg-white hover:bg-red-100 text-red-600 border border-red-300 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Lanjut ke Hapus Permanen...
                    </button>
                </div>

                <!-- Panel Konfirmasi Tahap 2 -->
                <div id="hard-delete-step2" class="hidden mt-3 pt-3 border-t border-red-200 space-y-3">
                    <label class="flex items-start gap-2 cursor-pointer text-xs text-red-900 font-medium">
                        <input type="checkbox" id="cb-konfirmasi-hard" class="mt-0.5 rounded border-red-300 text-red-600 focus:ring-red-500">
                        <span>Saya paham data ini akan dihapus permanen dari database dan tidak dapat dipulihkan kembali.</span>
                    </label>
                    <form action="" method="POST" onsubmit="return confirm('KONFIRMASI TERAKHIR: Yakin ingin menghapus data ini secara permanen?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="hapus">
                        <input type="hidden" name="id" id="hard-delete-id">
                        <div class="flex gap-2">
                            <button type="button" onclick="batalKonfirmasiHardDelete()" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-3 py-2 rounded-lg text-xs font-medium">
                                Kembali
                            </button>
                            <button type="submit" id="btn-submit-hard-delete" disabled class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors opacity-50 cursor-not-allowed flex items-center justify-center gap-1.5">
                                <i class="ph ph-trash"></i> Ya, Hapus Permanen
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end">
            <button type="button" onclick="tutupModalHapus()" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-100 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                Batal
            </button>
        </div>
    </div>
</div>

<script>
function bukaModalHapus(id, label, sudahNonaktif) {
    document.getElementById('hapus-target-label').textContent = label || ('ID #' + id);
    document.getElementById('soft-delete-id').value = id;
    document.getElementById('hard-delete-id').value = id;

    // Toggle tampilan opsi soft delete jika sudah nonaktif
    if (sudahNonaktif) {
        document.getElementById('box-soft-delete').classList.add('hidden');
        document.getElementById('info-sudah-nonaktif').classList.remove('hidden');
    } else {
        document.getElementById('box-soft-delete').classList.remove('hidden');
        document.getElementById('info-sudah-nonaktif').classList.add('hidden');
    }

    batalKonfirmasiHardDelete();
    document.getElementById('modal-pilihan-hapus').classList.remove('hidden');
}

function tutupModalHapus() {
    document.getElementById('modal-pilihan-hapus').classList.add('hidden');
}

function bukaKonfirmasiHardDelete() {
    document.getElementById('hard-delete-step1').classList.add('hidden');
    document.getElementById('hard-delete-step2').classList.remove('hidden');
    const cb = document.getElementById('cb-konfirmasi-hard');
    cb.checked = false;
    toggleHardDeleteBtn();
}

function batalKonfirmasiHardDelete() {
    document.getElementById('hard-delete-step1').classList.remove('hidden');
    document.getElementById('hard-delete-step2').classList.add('hidden');
    const cb = document.getElementById('cb-konfirmasi-hard');
    if (cb) cb.checked = false;
    toggleHardDeleteBtn();
}

function toggleHardDeleteBtn() {
    const cb = document.getElementById('cb-konfirmasi-hard');
    const btn = document.getElementById('btn-submit-hard-delete');
    if (!cb || !btn) return;
    if (cb.checked) {
        btn.disabled = false;
        btn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
    }
}

document.getElementById('cb-konfirmasi-hard').addEventListener('change', toggleHardDeleteBtn);
</script>
