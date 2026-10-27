<?php

namespace Botble\Kids\Services;

use Botble\Kids\Engine\AnswerMatcher;
use Botble\Kids\Enums\QuestionStateEnum;
use Botble\Kids\Enums\SessionStatusEnum;
use Botble\Kids\Models\KidsSessionQuestion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AnsweringService
{
    /**
     * @return array{correct: bool, attempts: int, state: string, hint: string[]|null, revealed: array{answer: string, solution: string[]}|null}
     */
    public function answer(KidsSessionQuestion $question, string $value): array
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            throw ValidationException::withMessages(['value' => ['Đáp án không được để trống.']]);
        }

        return DB::transaction(function () use ($question, $trimmed): array {
            /** @var KidsSessionQuestion $lockedQuestion */
            $lockedQuestion = KidsSessionQuestion::query()
                ->where('id', $question->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $session = $lockedQuestion->session;
            $sessionStatus = (string) ($session->status?->getValue() ?? $session->status);

            if ($sessionStatus !== SessionStatusEnum::IN_PROGRESS) {
                abort(409, 'Phiên làm bài đã kết thúc hoặc không còn khả dụng.');
            }

            if ($session->expires_at && $session->expires_at->isPast()) {
                $session->update(['status' => SessionStatusEnum::EXPIRED]);
                abort(409, 'Phiên làm bài đã quá thời gian quy định.');
            }

            $questionState = (string) ($lockedQuestion->state?->getValue() ?? $lockedQuestion->state);
            if ($questionState === QuestionStateEnum::CORRECT || $questionState === QuestionStateEnum::REVEALED || $lockedQuestion->attempts >= 2) {
                abort(409, 'Câu hỏi này đã hoàn thành lượt trả lời.');
            }

            $engineQ = $lockedQuestion->toQuestion();
            $isCorrect = AnswerMatcher::matches($engineQ->answer, $trimmed);

            $attempts = $lockedQuestion->attempts + 1;
            $given = $lockedQuestion->given_answers ?? [];
            $given[] = $trimmed;

            $lockedQuestion->attempts = $attempts;
            $lockedQuestion->given_answers = $given;

            if ($attempts === 1) {
                if ($isCorrect) {
                    $lockedQuestion->first_try_correct = true;
                    $lockedQuestion->state = QuestionStateEnum::CORRECT;
                    $lockedQuestion->save();

                    return [
                        'correct' => true,
                        'attempts' => 1,
                        'state' => 'correct',
                        'hint' => null,
                        'revealed' => null,
                    ];
                }

                $lockedQuestion->first_try_correct = false;
                $lockedQuestion->state = QuestionStateEnum::OPEN;
                $lockedQuestion->save();

                return [
                    'correct' => false,
                    'attempts' => 1,
                    'state' => 'open',
                    'hint' => $engineQ->hints,
                    'revealed' => null,
                ];
            }

            // Attempt 2
            if ($isCorrect) {
                $lockedQuestion->state = QuestionStateEnum::CORRECT;
                $lockedQuestion->save();

                return [
                    'correct' => true,
                    'attempts' => 2,
                    'state' => 'correct',
                    'hint' => null,
                    'revealed' => null,
                ];
            }

            $lockedQuestion->state = QuestionStateEnum::REVEALED;
            $lockedQuestion->save();

            return [
                'correct' => false,
                'attempts' => 2,
                'state' => 'revealed',
                'hint' => null,
                'revealed' => [
                    'answer' => $engineQ->answer->display(),
                    'solution' => $engineQ->solution,
                ],
            ];
        });
    }
}
