<?php

namespace App\Http\Controllers;

use App\Services\ExamAgentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamAgentController extends Controller
{
    public function __construct(private ExamAgentService $examAgent) {}

    public function index(Request $request): View
    {
        $user      = $request->user();
        $examAgent = $this->examAgent->analyse($user);

        return view('exam-agent', compact('user', 'examAgent'));
    }
}