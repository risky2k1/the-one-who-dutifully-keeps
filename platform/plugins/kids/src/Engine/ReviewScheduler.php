<?php

namespace Botble\Kids\Engine;

use DateTimeImmutable;

final class ReviewScheduler
{
    /**
     * @return array{stage: ?int, due_at: ?DateTimeImmutable}
     */
    public static function afterAnswer(?int $stage, bool $firstTryCorrect, DateTimeImmutable $now): array
    {
        if ($stage === null) {
            if ($firstTryCorrect) {
                return ['stage' => null, 'due_at' => null];
            }

            return [
                'stage' => 1,
                'due_at' => $now->modify('+1 day'),
            ];
        }

        if ($firstTryCorrect) {
            if ($stage === 1) {
                return [
                    'stage' => 2,
                    'due_at' => $now->modify('+3 days'),
                ];
            }

            // stage 2 or higher -> done
            return ['stage' => null, 'due_at' => null];
        }

        // Wrong in review -> reset to stage 1, +1 day
        return [
            'stage' => 1,
            'due_at' => $now->modify('+1 day'),
        ];
    }
}
