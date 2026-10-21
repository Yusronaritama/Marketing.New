<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class QuizResultsController extends Controller
{
    /**
     * Display the quiz results management page
     */
    public function index()
    {
        return view('admin.quiz-results');
    }
}