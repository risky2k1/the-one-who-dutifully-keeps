<?php

namespace Botble\Member\Http\Controllers;

use Botble\Blog\Models\Post;
use Botble\LanguageAdvanced\Http\Controllers\LanguageAdvancedController as BaseLanguageAdvancedController;
use Botble\LanguageAdvanced\Http\Requests\LanguageAdvancedRequest;
use Botble\LanguageAdvanced\Supports\LanguageAdvancedManager;
use Botble\Member\Forms\PostForm;
use Botble\Member\Models\Member;

class LanguageAdvancedController extends BaseLanguageAdvancedController
{
    public function save(int|string $id, LanguageAdvancedRequest $request)
    {
        // Members may only translate their own posts. The model, form and slug are
        // taken from the owned post, never from the request.
        $post = Post::query()
            ->where([
                'id' => $id,
                'author_id' => auth('member')->id(),
                'author_type' => Member::class,
            ])
            ->firstOrFail();

        // The member dashboard has no language meta box, so the hidden "language" input
        // is never posted; without it the parent skips saving the translated slug.
        $language = LanguageAdvancedManager::getTranslationLocale();

        if (! $request->filled('language') && LanguageAdvancedManager::isValidLanguageCode($language)) {
            $request->merge(['language' => $language]);
        }

        $request->merge([
            'model' => Post::class,
            'form' => PostForm::class,
            'slug_id' => $post->slugable?->getKey(),
        ]);

        return parent::save($post->getKey(), $request);
    }
}
