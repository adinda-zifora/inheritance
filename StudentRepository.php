<?php
declare(strict_types=1);

require_once __DIR__ . '/RegularStudent.php';
require_once __DIR__ . '/ScholarshipStudent.php';

class StudentRepository {
    /** @var array<string, Student> */
    private array $data = [];

    public function __construct(array $dataAwal = []) {
        foreach ($dataAwal as $item) {
            if ($item instanceof Student) {
                $this->data[$item->getNim()] = $item;
            }
        }
    }

    public function tambah(Student $m): bool {
        if (isset($this->data[$m->getNim()])) return false;
        $this->data[$m->getNim()] = $m;
        return true;
    }

    public function semua(): array { 
        return $this->data; 
    }

    public function all(): array { 
        return $this->semua(); 
    }

    public function cari(string $nim): ?Student {
        return $this->data[$nim] ?? null;
    }

    public function ubah(string $nimLama, Student $m): bool {
        if (!isset($this->data[$nimLama])) return false;
        if ($m->getNim() !== $nimLama && isset($this->data[$m->getNim()])) return false;
        unset($this->data[$nimLama]);
        $this->data[$m->getNim()] = $m;
        return true;
    }

    public function hapus(string $nim): bool {
        if (!isset($this->data[$nim])) return false;
        unset($this->data[$nim]);
        return true;
    }
}