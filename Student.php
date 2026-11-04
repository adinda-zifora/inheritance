<?php
declare(strict_types=1);

abstract class Student {
    public function __construct(
        private string $nim,
        private string $nama,
        private string $fakultas,
        private string $prodi,
        private string $asalSekolah,
        private int $semester,
        private string $hobi,
        private string $agama,
        private string $alamat,
        private string $golonganDarah
    ) {
        if (trim($nim) === '' || trim($nama) === '' || $semester < 1 || $semester > 14) {
            throw new InvalidArgumentException('NIM, nama, dan semester harus valid.');
        }
    }

    public function getNim(): string { return $this->nim; }
    public function getNama(): string { return $this->nama; }
    public function getFakultas(): string { return $this->fakultas; }
    public function getProdi(): string { return $this->prodi; }
    public function getAsalSekolah(): string { return $this->asalSekolah; }
    public function getSemester(): int { return $this->semester; }
    public function getHobi(): string { return $this->hobi; }
    public function getAgama(): string { return $this->agama; }
    public function getAlamat(): string { return $this->alamat; }
    public function getGolonganDarah(): string { return $this->golonganDarah; }

    abstract public function hitungBiaya(): int;
    abstract public function getJenis(): string;
    abstract public function getUkt(): int;
    abstract public function getPotonganPersen(): int;
}