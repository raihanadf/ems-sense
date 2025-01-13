<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class TreatmentController extends Controller
{
    public function index(): View
    {
        return view('livewire.pages.treatment.index');
    }
}
