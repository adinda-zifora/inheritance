<?php
declare(strict_types=1);
require_once __DIR__ . '/Student.php';

final class ScholarshipStudent extends Student {
    public function __construct(
        string $nim, 
        string $nama, 
        string $fakultas, 
        string $prodi,
        string $asalSekolah, 
        int $semester, 
        string $hobi, 
        string $agama,
        string $alamat, 
        string $golonganDarah, 
        private int $ukt, 
        private int $potonganPersen
    ) {
        parent::__construct($nim, $nama, $fakultas, $prodi, $asalSekolah, $semester, $hobi, $agama, $alamat, $golonganDarah);
        if ($ukt < 0 || $potonganPersen < 0 || $potonganPersen > 100) {
            throw new InvalidArgumentException('UKT atau potongan persen tidak valid.');
        }
    }

    public function getUkt(): int { return $this->ukt; }
    public function getPotonganPersen(): int { return $this->potonganPersen; }
    public function getJenis(): string { return 'Mahasiswa Beasiswa'; }

    public function hitungBiaya(): int {
        return intdiv($this->ukt * (100 - $this->potonganPersen), 100);
    }
}