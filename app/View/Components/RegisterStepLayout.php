<?php

namespace App\View\Components;

use Illuminate\View\Component;

class RegisterStepLayout extends Component
{
    public string $title; // Properti ini akan bisa diakses di view

    public function __construct(string $title = 'Pendaftaran Mahasiswa')
    {
        $this->title = $title;
    }

    public function render()
    {
        return view('components.register-step-layout');
    }
}