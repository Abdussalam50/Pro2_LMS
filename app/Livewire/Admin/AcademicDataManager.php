<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MataKuliah;
use App\Models\Kelas;
use App\Models\AcademicPeriod;
use App\Models\DosenData;
use Illuminate\Support\Str;

class AcademicDataManager extends Component
{
    use WithPagination;

    public $currentTab = 'courses';
    public $search = '';
    public $selectedPeriod = 'all';

    // Course Form
    public $showCourseModal = false;
    public $editingCourseId = null;
    public $courseName;
    public $courseCode;
    public $courseDosenId;

    // Class Form
    public $showClassModal = false;
    public $editingClassId = null;
    public $className;
    public $classCode;
    public $classMataKuliahId;
    public $classPeriodId;

    protected $queryString = ['search', 'currentTab', 'selectedPeriod'];

    public function updatedSearch() { $this->resetPage(); }
    public function updatedSelectedPeriod() { $this->resetPage(); }
    
    public function setTab($tab) {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    // --- Course Methods ---

    public function openCourseModal($courseId = null)
    {
        $this->resetValidation();
        $this->resetCourseForm();

        if ($courseId) {
            $course = MataKuliah::find($courseId);
            if ($course) {
                $this->editingCourseId = $course->mata_kuliah_id;
                $this->courseName = $course->mata_kuliah;
                $this->courseCode = $course->kode;
                $this->courseDosenId = $course->dosen_id;
            }
        }
        $this->showCourseModal = true;
    }

    public function resetCourseForm()
    {
        $this->editingCourseId = null;
        $this->courseName = '';
        $this->courseCode = '';
        $this->courseDosenId = '';
    }

    public function saveCourse()
    {
        $this->validate([
            'courseName' => 'required|string|max:255',
            'courseCode' => 'required|string|max:50',
            'courseDosenId' => 'required|exists:dosens,dosen_id',
        ]);

        if ($this->editingCourseId) {
            MataKuliah::find($this->editingCourseId)->update([
                'mata_kuliah' => $this->courseName,
                'kode' => $this->courseCode,
                'dosen_id' => $this->courseDosenId,
            ]);
        } else {
            MataKuliah::create([
                'mata_kuliah_id' => (string) Str::uuid(),
                'mata_kuliah' => $this->courseName,
                'kode' => $this->courseCode,
                'dosen_id' => $this->courseDosenId,
            ]);
        }

        $this->showCourseModal = false;
        $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'Mata Kuliah berhasil disimpan.', 'icon' => 'success']);
    }

    public function deleteCourse($id)
    {
        MataKuliah::destroy($id);
        $this->dispatch('swal', ['title' => 'Dihapus!', 'text' => 'Mata Kuliah berhasil dihapus.', 'icon' => 'success']);
    }

    // --- Class Methods ---

    public function openClassModal($classId = null)
    {
        $this->resetValidation();
        $this->resetClassForm();

        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        $this->classPeriodId = $activePeriod?->id;

        if ($classId) {
            $kelas = Kelas::find($classId);
            if ($kelas) {
                $this->editingClassId = $kelas->kelas_id;
                $this->className = $kelas->kelas;
                $this->classCode = $kelas->kode;
                $this->classMataKuliahId = $kelas->mata_kuliah_id;
                $this->classPeriodId = $kelas->academic_period_id;
            }
        }
        $this->showClassModal = true;
    }

    public function resetClassForm()
    {
        $this->editingClassId = null;
        $this->className = '';
        $this->classCode = '';
        $this->classMataKuliahId = '';
        $this->classPeriodId = '';
    }

    public function saveClass()
    {
        $this->validate([
            'className' => 'required|string|max:255',
            'classCode' => 'required|string|max:50',
            'classMataKuliahId' => 'required|exists:mata_kuliah,mata_kuliah_id',
            'classPeriodId' => 'nullable|exists:academic_periods,id',
        ]);

        if ($this->editingClassId) {
            Kelas::find($this->editingClassId)->update([
                'kelas' => $this->className,
                'kode' => $this->classCode,
                'mata_kuliah_id' => $this->classMataKuliahId,
                'academic_period_id' => $this->classPeriodId,
            ]);
        } else {
            Kelas::create([
                'kelas_id' => (string) Str::uuid(),
                'kelas' => $this->className,
                'kode' => $this->classCode,
                'mata_kuliah_id' => $this->classMataKuliahId,
                'academic_period_id' => $this->classPeriodId,
            ]);
        }

        $this->showClassModal = false;
        $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'Kelas berhasil disimpan.', 'icon' => 'success']);
    }

    public function deleteClass($id)
    {
        Kelas::destroy($id);
        $this->dispatch('swal', ['title' => 'Dihapus!', 'text' => 'Kelas berhasil dihapus.', 'icon' => 'success']);
    }

    public function render()
    {
        $periods = AcademicPeriod::orderBy('name')->get();
        $dosens = DosenData::orderBy('nama')->get();
        $coursesList = MataKuliah::orderBy('mata_kuliah')->get();

        $courses = MataKuliah::query()
            ->when($this->search, function ($q) {
                $q->where('mata_kuliah', 'like', '%' . $this->search . '%')
                  ->orWhere('kode', 'like', '%' . $this->search . '%');
            })
            ->with('dosen', 'kelas')
            ->paginate(10);

        $classes = Kelas::query()
            ->when($this->search, function ($q) {
                $q->where('kelas', 'like', '%' . $this->search . '%')
                  ->orWhere('kode', 'like', '%' . $this->search . '%');
            })
            ->when($this->selectedPeriod !== 'all', function ($q) {
                $q->where('academic_period_id', $this->selectedPeriod);
            })
            ->with('mataKuliah', 'academicPeriod')
            ->paginate(10);

        return view('livewire.admin.academic-data-manager', [
            'courses' => $courses,
            'classes' => $classes,
            'periods' => $periods,
            'dosens' => $dosens,
            'coursesList' => $coursesList,
        ])->layout('components.layout');
    }
}
