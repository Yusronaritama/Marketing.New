<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;

class FormElementsController extends Controller
{
public function index()
{
    $questions = Question::latest()->get();

    return view('admin.form-elements.create', compact('questions'));
}

}
