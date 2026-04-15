<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\SiteSetting;

class LayoutCustomization extends Component
{
    public $settings = [
        'dosen' => [
            'primary_color' => '#4f46e5',
            'sidebar_bg' => '#1e1b4b',
            'sidebar_border' => '#312e81',
            'sidebar_accent' => '#4f46e5',
            'app_icon' => 'book-open',
        ],
        'mahasiswa' => [
            'primary_color' => '#4f46e5',
            'sidebar_bg' => '#1e1b4b',
            'sidebar_border' => '#312e81',
            'sidebar_accent' => '#4f46e5',
            'app_icon' => 'book-open',
        ]
    ];

    public function mount()
    {
        foreach (['dosen', 'mahasiswa'] as $role) {
            foreach ($this->settings[$role] as $key => $default) {
                $this->settings[$role][$key] = SiteSetting::get("{$role}_layout_{$key}", $default);
            }
        }
    }

    public function save()
    {
        foreach ($this->settings as $role => $fields) {
            foreach ($fields as $key => $value) {
                SiteSetting::updateOrCreate(
                    ['key' => "{$role}_layout_{$key}"],
                    ['value' => $value]
                );
            }
        }

        session()->flash('message', 'Pengaturan layout berhasil disimpan.');
        $this->dispatch('swal', [
            'title' => 'Tersimpan!',
            'text' => 'Pengaturan layout berhasil disimpan.',
            'icon' => 'success'
        ]);
    }

    public function render()
    {
        return view('livewire.admin.layout-customization')->layout('components.layout');
    }
}
