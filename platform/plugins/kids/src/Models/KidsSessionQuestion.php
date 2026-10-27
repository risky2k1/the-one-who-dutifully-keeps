<?php

namespace Botble\Kids\Models;

use Botble\Base\Models\BaseModel;
use Botble\Kids\Engine\Answer;
use Botble\Kids\Engine\Question;
use Botble\Kids\Enums\QuestionStateEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KidsSessionQuestion extends BaseModel
{
    protected $table = 'kids_session_questions';

    protected $fillable = [
        'session_id',
        'position',
        'topic_id',
        'review_item_id',
        'type',
        'level',
        'prompt',
        'answer',
        'hints',
        'solution',
        'params',
        'attempts',
        'state',
        'first_try_correct',
        'given_answers',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'level' => 'integer',
            'prompt' => 'array',
            'answer' => 'array',
            'hints' => 'array',
            'solution' => 'array',
            'params' => 'array',
            'attempts' => 'integer',
            'state' => QuestionStateEnum::class,
            'first_try_correct' => 'boolean',
            'given_answers' => 'array',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(KidsSession::class, 'session_id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(KidsTopic::class, 'topic_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(KidsReport::class, 'question_id');
    }

    public function toQuestion(): Question
    {
        $answerData = $this->answer ?? [];
        $answer = new Answer(
            $answerData['kind'] ?? 'integer',
            (string) ($answerData['value'] ?? ''),
            (bool) ($answerData['requireSimplest'] ?? false)
        );

        return new Question(
            $this->type,
            $this->prompt ?? [],
            $answer,
            $this->hints ?? [],
            $this->solution ?? [],
            $this->params ?? []
        );
    }

    public function fillFromQuestion(Question $question): static
    {
        $this->type = $question->type;
        $this->prompt = $question->prompt;
        $this->answer = [
            'kind' => $question->answer->kind,
            'value' => $question->answer->value,
            'requireSimplest' => $question->answer->requireSimplest,
        ];
        $this->hints = $question->hints;
        $this->solution = $question->solution;
        $this->params = $question->params;

        return $this;
    }
}
