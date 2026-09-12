<?php
namespace App\Services\PastPapers;
use App\Models\PastPaper;
use App\Models\PastPaperQuestion;
use Illuminate\Support\Facades\Storage;
class PastPaperBankService
{
    public function export(): array
    {
        return ['version'=>1,'exported_at'=>now()->toIso8601String(),'papers'=>PastPaper::with('questions')->get()->map(fn($p)=>[
            'title'=>$p->title,'subject'=>$p->subject,'provenance'=>$p->provenance,'source_ref'=>$p->source_ref,'is_published'=>$p->is_published,
            'questions'=>$p->questions->map(fn($q)=>$q->only(['syllabus_module_id','seed_question_id','number','item_type','prompt','options','correct_answer','mark_scheme','marks','objective','difficulty','provenance','qc_status','qc_reason','is_withdrawn']))->values()->all(),
        ])->values()->all()];
    }
    public function backup(): string { $path='backups/past-paper-bank-'.now()->format('Ymd-His').'.json'; Storage::disk('local')->put($path,json_encode($this->export(),JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)); return $path; }
    public function import(array $payload): int
    {
        $count=0; foreach ((array)($payload['papers']??[]) as $row) { $paper=PastPaper::updateOrCreate(['source_ref'=>$row['source_ref']??null],collect($row)->except('questions')->all()); foreach ((array)($row['questions']??[]) as $question) { PastPaperQuestion::updateOrCreate(['past_paper_id'=>$paper->id,'number'=>$question['number']],$question); $count++; } } return $count;
    }
}
