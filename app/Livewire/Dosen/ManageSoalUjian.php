<?php

namespace App\Livewire\Dosen;

use Livewire\Component;
use App\Models\Ujian;
use App\Models\SoalUjian;
use App\Models\MateriUjian;
use App\Models\BankSoal;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

class ManageSoalUjian extends Component
{
    use WithFileUploads;

    public $ujian;
    public $soalUjians;
    public $materiUjians;
    public $activeTab = 'soals'; // soals, materi
    public $showModal = false;
    public $showMateriModal = false;
    public $showBankModal = false;
    public $editMode = false;
    public $soalId;
    public $bankSoals = [];

    public $materiForm = [
        'nama_materi' => '',
        'deskripsi' => '',
        'file_materi' => null
    ];

    public $form = [
        'type' => 'pilihan_ganda', // pilihan_ganda, esai
        'soal' => '',
        'pilihan' => [
            'a' => '',
            'b' => '',
            'c' => '',
            'd' => '',
            'e' => '',
        ],
        'jawaban_benar' => '',
        'jawaban_esai' => '',
        'bobot' => 10,
    ];

    public function mount($ujianId)
    {
        $this->ujian = Ujian::findOrFail($ujianId);
        $this->loadSoals();
    }

    public function loadSoals()
    {
        $this->soalUjians = SoalUjian::where('ujian_id', $this->ujian->ujian_id)->get();
        $this->materiUjians = MateriUjian::where('ujian_id', $this->ujian->ujian_id)->get();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function openMateriModal()
    {
        $this->resetMateriForm();
        $this->showMateriModal = true;
    }

    public function resetMateriForm()
    {
        $this->materiForm = [
            'nama_materi' => '',
            'deskripsi' => '',
            'file_materi' => null
        ];
    }

    public function saveMateri()
    {
        $this->validate([
            'materiForm.nama_materi' => 'required|string|max:255',
            'materiForm.file_materi' => 'required|file|max:20480', // 20MB
        ], [
            'materiForm.nama_materi.required' => 'Nama materi wajib diisi.',
            'materiForm.file_materi.required' => 'File materi harus diunggah.',
            'materiForm.file_materi.max' => 'Ukuran file maksimal 20MB.',
        ]);

        $filePath = $this->materiForm['file_materi']->store('materi_ujians', 'public');

        MateriUjian::create([
            'ujian_id' => $this->ujian->ujian_id,
            'kelas_id' => $this->ujian->kelas_id,
            'mata_kuliah_id' => $this->ujian->mata_kuliah_id,
            'dosen_id' => $this->ujian->dosen_id,
            'nama_materi' => $this->materiForm['nama_materi'],
            'deskripsi' => $this->materiForm['deskripsi'],
            'file_materi' => $filePath,
        ]);

        $this->showMateriModal = false;
        $this->loadSoals();
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Berhasil',
            'message' => 'Materi referensi berhasil ditambahkan.',
            'timer' => 3000
        ]);
    }

    public function deleteMateri($id)
    {
        MateriUjian::destroy($id);
        $this->loadSoals();
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Terhapus',
            'message' => 'Materi berhasil dihapus.',
            'timer' => 2000
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetForm();
        $soal = SoalUjian::findOrFail($id);
        $this->soalId = $id;
        $this->form['type'] = $soal->pilihan_ganda ? 'pilihan_ganda' : 'esai';
        $this->form['soal'] = $soal->soal;
        $this->form['bobot'] = $soal->bobot;
        
        if ($this->form['type'] === 'pilihan_ganda') {
            $this->form['pilihan'] = $soal->pilihan_ganda;
            $this->form['jawaban_benar'] = trim((string) $soal->jawaban_benar);
        } else {
            $this->form['jawaban_esai'] = $soal->jawaban_esai;
        }
        
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $rules = [
            'form.soal' => 'required',
            'form.bobot' => 'required|integer|min:1',
        ];

        if ($this->form['type'] === 'pilihan_ganda') {
            $rules['form.pilihan.a'] = 'required';
            $rules['form.pilihan.b'] = 'required';
            $rules['form.jawaban_benar'] = 'required';
        }

        $this->validate($rules);

        $data = [
            'ujian_id' => $this->ujian->ujian_id,
            'soal' => $this->form['soal'],
            'bobot' => $this->form['bobot'],
            'pilihan_ganda' => $this->form['type'] === 'pilihan_ganda' ? $this->form['pilihan'] : null,
            'jawaban_benar' => $this->form['type'] === 'pilihan_ganda' ? trim((string) $this->form['jawaban_benar']) : null,
            'jawaban_esai' => $this->form['type'] === 'esai' ? $this->form['jawaban_esai'] : null,
        ];

        $bankData = [
            'dosen_id' => $this->ujian->dosen_id,
            'jenis' => 'ujian',
            'tipe_soal' => $this->form['type'],
            'judul_soal' => Str::limit(strip_tags($this->form['soal']), 50),
            'konten_soal' => $this->form['soal'],
            'opsi_jawaban' => $this->form['type'] === 'pilihan_ganda' ? $this->form['pilihan'] : null,
            'kunci_jawaban' => $this->form['type'] === 'pilihan_ganda' ? $this->form['jawaban_benar'] : $this->form['jawaban_esai'],
            'bobot_referensi' => $this->form['bobot'],
        ];

        if ($this->editMode) {
            $soal = SoalUjian::findOrFail($this->soalId);
            $soal->update($data);

            // Sync ke Bank Soal
            if ($soal->bank_soal_id) {
                $bank = BankSoal::find($soal->bank_soal_id);
                if ($bank) {
                    $bank->update($bankData);
                } else {
                    $newBank = BankSoal::create($bankData);
                    $soal->update(['bank_soal_id' => $newBank->bank_soal_id]);
                }
            } else {
                $newBank = BankSoal::create($bankData);
                $soal->update(['bank_soal_id' => $newBank->bank_soal_id]);
            }
        } else {
            // New Entry
            $bank = BankSoal::create($bankData);
            $data['bank_soal_id'] = $bank->bank_soal_id;
            SoalUjian::create($data);
        }

        $this->showModal = false;
        $this->loadSoals();
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Berhasil',
            'message' => 'Soal berhasil disimpan.',
            'timer' => 3000
        ]);
    }

    public function openBankModal()
    {
        $this->bankSoals = BankSoal::where('dosen_id', $this->ujian->dosen_id)
            ->where('jenis', 'ujian')
            ->latest()
            ->get();
        $this->showBankModal = true;
    }

    public function importFromBank($bankSoalId)
    {
        $bank = BankSoal::find($bankSoalId);
        if ($bank) {
            SoalUjian::create([
                'ujian_id' => $this->ujian->ujian_id,
                'bank_soal_id' => $bank->bank_soal_id, // Store reference
                'soal' => $bank->konten_soal,
                'bobot' => $bank->bobot_referensi,
                'pilihan_ganda' => $bank->tipe_soal === 'pilihan_ganda' ? $bank->opsi_jawaban : null,
                'jawaban_benar' => $bank->tipe_soal === 'pilihan_ganda' ? $bank->kunci_jawaban : null,
                'jawaban_esai' => $bank->tipe_soal === 'esai' ? $bank->kunci_jawaban : null,
            ]);
            
            $this->showBankModal = false;
            $this->loadSoals();
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Impor Berhasil',
                'message' => 'Soal berhasil diimpor dari bank soal.',
                'timer' => 3000
            ]);
        }
    }

    public function delete($id)
    {
        SoalUjian::destroy($id);
        $this->loadSoals();
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Terhapus',
            'message' => 'Soal berhasil dihapus.',
            'timer' => 2000
        ]);
    }

    protected function resetForm()
    {
        $this->form = [
            'type' => 'pilihan_ganda',
            'soal' => '',
            'pilihan' => [
                'a' => '',
                'b' => '',
                'c' => '',
                'd' => '',
                'e' => '',
            ],
            'jawaban_benar' => '',
            'jawaban_esai' => '',
            'bobot' => 10,
        ];
    }

    public function render()
    {
        return view('livewire.dosen.manage-soal-ujian')
            ->layout('components.layout');
    }
}
