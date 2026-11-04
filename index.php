<?php
declare(strict_types=1);
require_once __DIR__ . '/StudentRepository.php';
session_start();

function aman(mixed $teks): string {
    return htmlspecialchars((string)$teks, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$repo = new StudentRepository($_SESSION['mahasiswa'] ?? []);
$pesan = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$error = '';
$agamaList = ['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'];
$keys = [
    'nim'=>'NIM','nama'=>'Nama Lengkap','fakultas'=>'Fakultas',
    'prodi'=>'Program Studi','asalSekolah'=>'Asal Sekolah',
    'semester'=>'Semester','hobi'=>'Hobi','agama'=>'Agama',
    'alamat'=>'Alamat','golonganDarah'=>'Golongan Darah'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) {
            throw new InvalidArgumentException('Token formulir tidak valid. Muat ulang halaman.');
        }
        $aksi = (string)($_POST['aksi'] ?? '');
        $nimLama = (string)($_POST['nim_lama'] ?? '');

        if ($aksi === 'hapus') {
            $nim = trim((string)($_POST['nim'] ?? ''));
            if (!$repo->hapus($nim)) throw new InvalidArgumentException('NIM tidak ditemukan.');
        } elseif (in_array($aksi, ['tambah','ubah'], true)) {
            $data = [];
            foreach ($keys as $k => $label) {
                $data[$k] = trim((string)($_POST[$k] ?? ''));
            }
            foreach (['nim','nama','fakultas','prodi','asalSekolah','agama','alamat','golonganDarah'] as $k) {
                if ($data[$k] === '') throw new InvalidArgumentException($keys[$k] . ' wajib diisi.');
            }
            $semester = filter_var($data['semester'], FILTER_VALIDATE_INT);
            if ($semester === false || $semester < 1 || $semester > 14) {
                throw new InvalidArgumentException('Semester harus 1–14.');
            }

            $tipe = (string)($_POST['tipe'] ?? 'reguler');
            $ukt = filter_var($_POST['ukt'] ?? 0, FILTER_VALIDATE_INT);
            if ($ukt === false || $ukt < 0) {
                throw new InvalidArgumentException('UKT harus berupa angka valid.');
            }

            if ($tipe === 'reguler') {
                $m = new RegularStudent(
                    $data['nim'], $data['nama'], $data['fakultas'], $data['prodi'],
                    $data['asalSekolah'], $semester, $data['hobi'], $data['agama'],
                    $data['alamat'], $data['golonganDarah'], $ukt
                );
            } else {
                $potongan = filter_var($_POST['potongan'] ?? 0, FILTER_VALIDATE_INT);
                if ($potongan === false || $potongan < 0 || $potongan > 100) {
                    throw new InvalidArgumentException('Potongan beasiswa harus antara 0-100%.');
                }
                $m = new ScholarshipStudent(
                    $data['nim'], $data['nama'], $data['fakultas'], $data['prodi'],
                    $data['asalSekolah'], $semester, $data['hobi'], $data['agama'],
                    $data['alamat'], $data['golonganDarah'], $ukt, $potongan
                );
            }

            $berhasil = $aksi === 'tambah' ? $repo->tambah($m) : $repo->ubah($nimLama, $m);
            if (!$berhasil) throw new InvalidArgumentException('NIM duplikat atau data lama tidak ditemukan.');
        }

        $_SESSION['mahasiswa'] = $repo->semua();
        $_SESSION['flash'] = 'Data berhasil ' . ($aksi === 'hapus' ? 'dihapus' : ($aksi === 'ubah' ? 'diubah' : 'ditambahkan')) . '.';
        header('Location: index.php');
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

if (!isset($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
$edit = $repo->cari((string)($_GET['edit'] ?? ''));
?>
<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>CRUD Mahasiswa - Inheritance (Pertemuan 6)</title>
<style>
    *{box-sizing:border-box}body{font-family:system-ui,Arial,sans-serif;background:#f1f5f9;color:#15324d;margin:0}
    main{max-width:1120px;margin:36px auto;padding:0 18px}h1{margin-bottom:4px}
    small,.muted{color:#60758a}.panel{background:white;border-radius:13px;padding:24px;margin:20px 0;box-shadow:0 5px 18px #16324d14}
    .grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}
    label{font-weight:650;font-size:13px;display:block}input,select,textarea{display:block;width:100%;padding:11px;margin-top:6px;border:1px solid #c8d5e3;border-radius:7px;background:white;font:inherit}
    button,.btn{border:0;border-radius:7px;background:#176397;color:white;padding:9px 15px;cursor:pointer;text-decoration:none;display:inline-block;font-size:13px}
    .danger{background:#b9414a}.actions{display:flex;gap:7px;align-items:center}.actions form{margin:0}
    table{border-collapse:collapse;width:100%;font-size:13px}th,td{padding:12px 9px;text-align:left;border-bottom:1px solid #e1e8f0}th{background:#e8f1f8}
    .scroll{overflow-x:auto}.notice{padding:12px;border-radius:8px;background:#e3f4e9;margin-bottom:15px}.err{background:#ffe8e8}
</style>
</head>
<body>
<main>
<h1>Data Mahasiswa — Inheritance</h1><p class="muted">Praktikum PBO Pertemuan 6: Pewarisan, Overriding, & Polimorfisme</p>

<?php if ($pesan): ?><div class="notice"><?= aman($pesan) ?></div><?php endif; ?>
<?php if ($error): ?><div class="notice err"><?= aman($error) ?></div><?php endif; ?>

<section class="panel"><h2><?= $edit ? 'Ubah Data' : 'Tambah' ?> Mahasiswa</h2>
<form method="post">
<input type="hidden" name="csrf" value="<?= aman($_SESSION['csrf']) ?>">
<input type="hidden" name="aksi" value="<?= $edit ? 'ubah' : 'tambah' ?>">
<input type="hidden" name="nim_lama" value="<?= aman($edit?->getNim() ?? '') ?>">
<div class="grid">

<?php foreach ($keys as $key => $label): 
    $value = $edit ? match($key) {
        'nim' => $edit->getNim(),
        'nama' => $edit->getNama(),
        'fakultas' => $edit->getFakultas(),
        'prodi' => $edit->getProdi(),
        'asalSekolah' => $edit->getAsalSekolah(),
        'semester' => $edit->getSemester(),
        'hobi' => $edit->getHobi(),
        'agama' => $edit->getAgama(),
        'alamat' => $edit->getAlamat(),
        'golonganDarah' => $edit->getGolonganDarah(),
        default => ''
    } : '';
?>
<label><?= aman($label) ?>
<?php if ($key === 'agama'): ?>
<select name="agama" required><option value="">-- Pilih Agama --</option>
<?php foreach ($agamaList as $agama): ?>
<option value="<?= aman($agama) ?>" <?= $value === $agama ? 'selected' : '' ?>><?= aman($agama) ?></option>
<?php endforeach; ?></select>
<?php elseif ($key === 'golonganDarah'): ?>
<select name="golonganDarah" required><option value="">-- Pilih Gol. Darah --</option>
<?php foreach (['A','B','AB','O'] as $gd): ?>
<option value="<?= aman($gd) ?>" <?= $value === $gd ? 'selected' : '' ?>><?= aman($gd) ?></option>
<?php endforeach; ?></select>
<?php elseif ($key === 'alamat'): ?>
<textarea name="alamat" rows="2" required><?= aman($value) ?></textarea>
<?php else: ?>
<input name="<?= aman($key) ?>" value="<?= aman($value) ?>" <?= $key === 'semester' ? 'type="number" min="1" max="14"' : 'type="text"' ?> required>
<?php endif; ?></label>
<?php endforeach; ?>

<label>Jenis Mahasiswa
<select name="tipe" id="tipeSelect" onchange="toggleBeasiswa()" required>
    <option value="reguler" <?= ($edit && $edit instanceof RegularStudent) ? 'selected' : '' ?>>Mahasiswa Reguler</option>
    <option value="beasiswa" <?= ($edit && $edit instanceof ScholarshipStudent) ? 'selected' : '' ?>>Mahasiswa Beasiswa</option>
</select></label>

<label>UKT Dasar (Rp)
<input type="number" name="ukt" min="0" value="<?= aman($edit?->getUkt() ?? '') ?>" required></label>

<label id="labelPotongan" style="display:none;">Potongan Beasiswa (%)
<input type="number" name="potongan" min="0" max="100" value="<?= aman($edit?->getPotonganPersen() ?? 0) ?>"></label>

</div>
<p style="margin-top:20px;"><button><?= $edit ? 'Simpan Perubahan' : 'Tambah Mahasiswa' ?></button>
<?php if ($edit): ?><a class="btn" href="index.php" style="background:#60758a;">Batal</a><?php endif; ?></p>
</form></section>

<section class="panel"><h2>Daftar Mahasiswa (<?= count($repo->semua()) ?>)</h2><div class="scroll"><table>
<thead>
    <tr>
        <th>NIM</th><th>Nama</th><th>Fakultas</th><th>Prodi</th><th>Semester</th><th>Jenis</th><th>UKT Dasar</th><th>Potongan</th><th>Biaya Akhir</th><th>Aksi</th>
    </tr>
</thead>
<tbody>
<?php foreach ($repo->semua() as $m): ?>
<tr>
    <td><?= aman($m->getNim()) ?></td>
    <td><?= aman($m->getNama()) ?></td>
    <td><?= aman($m->getFakultas()) ?></td>
    <td><?= aman($m->getProdi()) ?></td>
    <td><?= aman($m->getSemester()) ?></td>
    <td><?= aman($m->getJenis()) ?></td>
    <td>Rp <?= number_format($m->getUkt(), 0, ',', '.') ?></td>
    <td><?= $m->getPotonganPersen() ?>%</td>
    <td><strong>Rp <?= number_format($m->hitungBiaya(), 0, ',', '.') ?></strong></td>
    <td>
        <div class="actions">
            <a class="btn" href="?edit=<?= urlencode($m->getNim()) ?>">Edit</a>
            <form method="post" onsubmit="return confirm('Hapus data ini?')">
                <input type="hidden" name="csrf" value="<?= aman($_SESSION['csrf']) ?>">
                <input type="hidden" name="aksi" value="hapus">
                <input type="hidden" name="nim" value="<?= aman($m->getNim()) ?>">
                <button class="danger btn">Hapus</button>
            </form>
        </div>
    </td>
</tr>
<?php endforeach; ?>
<?php if (!$repo->semua()): ?>
    <tr><td colspan="10" style="text-align:center;">Belum ada data mahasiswa.</td></tr>
<?php endif; ?>
</tbody></table></div></section>
</main>
<script>
function toggleBeasiswa() {
    var tipe = document.getElementById('tipeSelect').value;
    var labelP = document.getElementById('labelPotongan');
    labelP.style.display = (tipe === 'beasiswa') ? 'block' : 'none';
}
toggleBeasiswa();
</script>
</body>
</html>