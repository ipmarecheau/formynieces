<?php
namespace App\Services\PastPapers;
use App\Models\PaperSitting;
use App\Services\SchoolJournal\OcrService;
use Illuminate\Support\Facades\Storage;
class PaperDigitiser
{
    public function __construct(private OcrService $ocr) {}
    public function digitise(PaperSitting $sitting): string
    {
        $submission=$sitting->submissions()->latest()->first(); if(!$submission)return 'failed';
        $questions=$sitting->paper?->questions()->whereIn('id',$sitting->question_ids)->get()->keyBy('number')??collect(); $needsReview=false; $found=false;
        $imageAttempted=false; $ocrFailed=false;
        foreach((array)$submission->image_paths as $path){$mime=Storage::disk('local')->mimeType($path)?:'application/octet-stream';if(in_array($mime,['image/jpeg','image/png','image/webp'],true)){$imageAttempted=true;$result=$this->ocr->digitize(Storage::disk('local')->path($path),$mime);if(!$result){$ocrFailed=true;continue;}}else{continue;}foreach((array)($result['questions']??[]) as $read){$question=$questions->get((int)($read['number']??0));if(!$question){$needsReview=true;continue;}$found=true;$confidence=(float)($read['confidence']??$read['topic_confidence']??0.5);$answer=trim((string)($read['student_answer']??''));$correct=mb_strtolower($answer)===mb_strtolower(trim((string)$question->correct_answer));$sitting->answers()->updateOrCreate(['past_paper_question_id'=>$question->id],['read_answer'=>$answer,'is_correct'=>$correct,'marks_awarded'=>$correct?$question->marks:0,'confidence'=>$confidence]);if($confidence<.70)$needsReview=true;}}
        $status=($imageAttempted && ($ocrFailed || !$found))?'rewrite_required':(!$imageAttempted?'needs_review':($needsReview?'needs_review':'digitised'));$submission->update(['digitisation_status'=>$status]);return $status;
    }
}
