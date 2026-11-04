<?php
declare(strict_types=1);
require_once __DIR__ . '/Student.php';

final class RegularStudent extends Student {
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
        private int $ukt
    ) {
        parent::__construct($nim, $nama, $fakultas, $prodi, $asalSekolah, $semester, $hobi, $agama, $alamat, $golonganDarah);
        if ($ukt < 0) {
            throw new InvalidArgumentException('UKT tidak boleh negatif.');
        }
    }

    public function getUkt(): int { return $this->ukt; }
    public function getPotonganPersen(): int { return 0; }
    public function getJenis(): string { return 'Mahasiswa Reguler'; }

    public function hitungBiaya(): int { 
        return $this->ukt; 
    }
}