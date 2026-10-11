<?php

namespace Database\Seeders;

use Botble\Base\Facades\Html;
use Botble\Blog\Models\Category;
use Botble\Blog\Models\Post;
use Botble\Blog\Models\Tag;
use Botble\Gallery\Models\Gallery;
use Botble\Gallery\Models\GalleryMeta;
use Botble\LanguageAdvanced\Database\Seeders\BaseTranslationSeeder;
use Botble\LanguageAdvanced\Database\Seeders\Traits\HasLanguageSeeder;
use Botble\LanguageAdvanced\Database\Seeders\Traits\HasMenuTranslationSeeder;
use Botble\LanguageAdvanced\Database\Seeders\Traits\HasPageTranslation;
use Botble\LanguageAdvanced\Database\Seeders\Traits\HasThemeOptionSeeder;
use Botble\LanguageAdvanced\Database\Seeders\Traits\HasWidgetSeeder;
use Botble\Menu\Database\Traits\HasMenuSeeder;
use Botble\Menu\Models\Menu;
use Botble\Menu\Models\MenuLocation;
use Botble\Page\Models\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TranslationSeeder extends BaseTranslationSeeder
{
    use HasLanguageSeeder;
    use HasMenuSeeder;
    use HasMenuTranslationSeeder;
    use HasPageTranslation;
    use HasThemeOptionSeeder;
    use HasWidgetSeeder;

    public function run(): void
    {
        if (! is_plugin_active('language')) {
            return;
        }

        $this->createLanguages();

        $locales = $this->locales();

        $this->seedThemeOptions($locales);
        $this->seedMenus($locales);
        $this->seedWidgets($locales);

        if (is_plugin_active('language-advanced')) {
            $this->seedPageTranslations($locales);
            $this->seedCategoryTranslations($locales);
            $this->seedTagTranslations($locales);
            $this->seedPostTranslations($locales);
            $this->seedGalleryTranslations($locales);
            $this->seedGalleryMetaTranslations($locales);

            $this->seedSlugTranslations($this->getSlugTranslatableModels(), $locales);
        }
    }

    protected function locales(): array
    {
        return ['ar', 'vi', 'fr', 'id', 'tr'];
    }

    protected function getDefaultMenuSlugs(): array
    {
        return ['main-menu', 'social'];
    }

    protected function seedMenus(array $locales): void
    {
        $defaultMenuSlugs = $this->getDefaultMenuSlugs();
        $menus = Menu::query()
            ->whereIn('slug', $defaultMenuSlugs)
            ->get()
            ->keyBy('slug');

        if ($menus->isEmpty()) {
            return;
        }

        $hasMainMenu = $menus->has('main-menu');
        $hasSocialMenu = $menus->has('social');

        $menuOrigins = [];
        foreach ($menus as $slug => $menu) {
            $menuOrigins[$slug] = $this->getLanguageMetaOrigin($menu);
        }

        $mainMenuLocation = MenuLocation::query()
            ->where('menu_id', $menus->get('main-menu')?->getKey())
            ->where('location', 'main-menu')
            ->first();

        $locationOrigin = $mainMenuLocation ? $this->getLanguageMetaOrigin($mainMenuLocation) : null;

        $pageIds = Page::query()->pluck('id', 'name')->all();

        foreach ($locales as $locale) {
            $translations = $this->loadMenuTranslations($locale);
            if (empty($translations)) {
                continue;
            }

            if ($hasMainMenu && isset($translations['main-menu'])) {
                $this->createMenuTranslation(
                    $locale,
                    'main-menu',
                    $translations['main-menu']['name'],
                    $this->buildMainMenuItems($translations['main-menu'], $pageIds),
                    $menuOrigins['main-menu'] ?? null,
                    $locationOrigin
                );
            }

            if ($hasSocialMenu && isset($translations['social'])) {
                $this->createMenuTranslation(
                    $locale,
                    'social',
                    $translations['social']['name'],
                    $this->buildSocialMenuItems(),
                    $menuOrigins['social'] ?? null,
                    null
                );
            }
        }

        \Botble\Menu\Facades\Menu::clearCacheMenuItems();
    }

    protected function buildMainMenuItems(array $labels, array $pageIds): array
    {
        return [
            [
                'title' => $labels['home'],
                'url' => '/',
            ],
            [
                'title' => $labels['purchase'],
                'url' => 'https://botble.com/go/download-cms',
                'target' => '_blank',
            ],
            [
                'title' => $labels['blog'],
                'reference_id' => $pageIds['Blog'] ?? null,
                'reference_type' => Page::class,
            ],
            [
                'title' => $labels['galleries'],
                'reference_id' => $pageIds['Galleries'] ?? null,
                'reference_type' => Page::class,
            ],
            [
                'title' => $labels['contact'],
                'reference_id' => $pageIds['Contact'] ?? null,
                'reference_type' => Page::class,
            ],
        ];
    }

    protected function buildSocialMenuItems(): array
    {
        return [
            [
                'title' => 'Facebook',
                'url' => 'https://facebook.com',
                'icon_font' => 'ti ti-brand-facebook',
                'target' => '_blank',
            ],
            [
                'title' => 'Twitter',
                'url' => 'https://twitter.com',
                'icon_font' => 'ti ti-brand-x',
                'target' => '_blank',
            ],
            [
                'title' => 'GitHub',
                'url' => 'https://github.com',
                'icon_font' => 'ti ti-brand-github',
                'target' => '_blank',
            ],
            [
                'title' => 'Linkedin',
                'url' => 'https://linkedin.com',
                'icon_font' => 'ti ti-brand-linkedin',
                'target' => '_blank',
            ],
        ];
    }

    protected function pageTranslations(): array
    {
        $categoryId = null;

        if (Schema::hasTable('categories')) {
            $categoryId = Category::query()->skip(1)->value('id');
            $categoryId = $categoryId ? (int) $categoryId : null;
        }

        $locales = $this->locales();
        $translations = [];

        foreach ($locales as $locale) {
            $translations[$locale] = [
                'Homepage' => [
                    'name' => $this->loadPageTranslationsFromJson($locale)['Homepage']['name'] ?? 'Homepage',
                    'content' => $this->homepageContent($locale, $categoryId),
                ],
            ];
        }

        return $translations;
    }

    protected function homepageContent(string $locale, ?int $categoryId): string
    {
        $titles = [
            'en' => [
                'whats_new' => 'What\'s new?',
                'best_for_you' => 'Best for you',
                'galleries' => 'Galleries',
            ],
            'ar' => [
                'whats_new' => 'ما الجديد؟',
                'best_for_you' => 'الأفضل لك',
                'galleries' => 'المعارض',
            ],
            'vi' => [
                'whats_new' => 'Có gì mới?',
                'best_for_you' => 'Tốt nhất cho bạn',
                'galleries' => 'Bộ sưu tập',
            ],
            'fr' => [
                'whats_new' => 'Quoi de neuf ?',
                'best_for_you' => 'Meilleur pour vous',
                'galleries' => 'Galeries',
            ],
            'id' => [
                'whats_new' => 'Apa yang baru?',
                'best_for_you' => 'Terbaik untuk Anda',
                'galleries' => 'Galeri',
            ],
            'tr' => [
                'whats_new' => 'Neler yeni?',
                'best_for_you' => 'Sizin için en iyisi',
                'galleries' => 'Galeriler',
            ],
        ];

        $localeTitles = $titles[$locale] ?? $titles['en'];

        return Html::tag('div', '[featured-posts enable_lazy_loading="yes"][/featured-posts]') .
            Html::tag(
                'div',
                '[recent-posts title="' . $localeTitles['whats_new'] . '" enable_lazy_loading="yes"][/recent-posts]'
            ) .
            Html::tag(
                'div',
                '[featured-categories-posts title="' . $localeTitles['best_for_you'] . '" category_id="' . $categoryId . '" enable_lazy_loading="yes"][/featured-categories-posts]'
            ) .
            Html::tag(
                'div',
                '[all-galleries limit="6" title="' . $localeTitles['galleries'] . '" enable_lazy_loading="yes"][/all-galleries]'
            );
    }

    protected function getSlugTranslatableModels(): array
    {
        $models = [
            Page::class,
        ];

        if (is_plugin_active('blog')) {
            $models[] = Category::class;
            $models[] = Tag::class;
            $models[] = Post::class;
        }

        if (is_plugin_active('gallery')) {
            $models[] = Gallery::class;
        }

        return $models;
    }

    protected function seedCategoryTranslations(array $locales): void
    {
        if (! Schema::hasTable('categories_translations') || ! Schema::hasTable('categories')) {
            return;
        }

        $this->seedEntityFromJson(
            entity: 'categories',
            tableName: 'categories_translations',
            modelClass: Category::class,
            locales: $locales,
            columns: ['name']
        );
    }

    protected function seedTagTranslations(array $locales): void
    {
        if (! is_plugin_active('blog')) {
            return;
        }

        if (! Schema::hasTable('tags_translations') || ! Schema::hasTable('tags')) {
            return;
        }

        $this->seedEntityFromJson(
            entity: 'tags',
            tableName: 'tags_translations',
            modelClass: Tag::class,
            locales: $locales,
            columns: ['name']
        );
    }

    protected function seedPostTranslations(array $locales): void
    {
        if (! is_plugin_active('blog')) {
            return;
        }

        if (! Schema::hasTable('posts_translations')) {
            return;
        }

        $postIds = Post::query()->pluck('id', 'name')->all();

        if (empty($postIds)) {
            return;
        }

        $postOrder = $this->postOrder();

        foreach ($locales as $locale) {
            $postTranslations = $this->loadTranslations('posts', $locale);
            $paragraphs = $this->loadTranslations('posts-paragraphs', $locale);
            $shortParagraphs = $this->loadTranslations('posts-short-paragraphs', $locale);

            if (empty($postTranslations) || empty($paragraphs) || empty($shortParagraphs)) {
                continue;
            }

            foreach ($postOrder as $index => $englishName) {
                $postId = $postIds[$englishName] ?? null;

                if (! $postId) {
                    continue;
                }

                $postTranslation = $postTranslations[$index] ?? null;

                if (! $postTranslation) {
                    continue;
                }

                $content = $this->buildTranslatedPostContent(
                    $index,
                    $paragraphs,
                    $shortParagraphs
                );

                DB::table('posts_translations')->updateOrInsert(
                    [
                        'lang_code' => $locale,
                        'posts_id' => $postId,
                    ],
                    [
                        'lang_code' => $locale,
                        'posts_id' => $postId,
                        'name' => $postTranslation['name'],
                        'description' => $postTranslation['description'],
                        'content' => $content,
                    ]
                );
            }
        }
    }

    protected function buildTranslatedPostContent(
        int $index,
        array $paragraphs,
        array $shortParagraphs
    ): string {
        $paragraphCount = count($paragraphs);
        $shortCount = count($shortParagraphs);

        if ($paragraphCount === 0 || $shortCount === 0) {
            return '';
        }

        $content = '';

        if ($index % 3 === 0) {
            $content .= Html::tag(
                'p',
                '[youtube-video]https://www.youtube.com/watch?v=SlPhMPnQ58k[/youtube-video]'
            );
        }

        $content .= Html::tag('p', $paragraphs[$index % $paragraphCount]);
        $content .= Html::tag(
            'p',
            Html::image(
                $this->fileUrl('news/' . rand(1, 5) . '.webp', size: 'medium'),
                'image',
                ['style' => 'width: 100%', 'class' => 'image_resized']
            )
                ->toHtml(),
            ['class' => 'text-center']
        );
        $content .= Html::tag('p', $shortParagraphs[$index % $shortCount]);
        $content .= Html::tag(
            'p',
            Html::image(
                $this->fileUrl('news/' . rand(6, 10) . '.webp', size: 'medium'),
                'image',
                ['style' => 'width: 100%', 'class' => 'image_resized']
            )
                ->toHtml(),
            ['class' => 'text-center']
        );
        $content .= Html::tag('p', $paragraphs[($index + 3) % $paragraphCount]);
        $content .= Html::tag(
            'p',
            Html::image(
                $this->fileUrl('news/' . rand(11, 14) . '.webp', size: 'medium'),
                'image',
                ['style' => 'width: 100%', 'class' => 'image_resized']
            )
                ->toHtml(),
            ['class' => 'text-center']
        );
        $content .= Html::tag('p', $paragraphs[($index + 5) % $paragraphCount]);

        return $content;
    }

    protected function postOrder(): array
    {
        return [
            'The Rise of Quantum Computing: IBM Unveils 1000-Qubit Processor',
            'Apple Vision Pro 2: The Future of Spatial Computing Has Arrived',
            'ChatGPT-5 Released: New AI Model Shows Human-Level Reasoning',
            'Tesla\'s Full Self-Driving Finally Approved for Highway Use in California',
            'Major Cybersecurity Breach: 500 Million Records Exposed in Cloud Storage Misconfiguration',
            'Microsoft Introduces AI-Powered Code Review: 40% Reduction in Production Bugs',
            'Boston Dynamics Robots Now Working in Amazon Warehouses',
            'Meta\'s New VR Gloves Let You Feel Virtual Objects',
            'Neuralink Begins Human Trials: First Patient Controls Computer with Thoughts',
            'Google\'s Project Starline: 3D Video Calls Without Headsets',
            'NVIDIA H200 GPU Breaks AI Training Records',
            'Ethereum 3.0 Launches: 100,000 Transactions Per Second Achieved',
            'SpaceX Starship Successfully Lands on Moon with NASA Astronauts',
            'Amazon\'s Drone Delivery Expands to 100 Cities Across the US',
            'Revolutionary Cancer Treatment: AI Discovers Personalized Drug Combinations',
            'Samsung\'s Transparent OLED Displays Transform Retail Shopping',
            'Waymo Robotaxis Now Operating in 25 Major Cities',
            'Solar Paint Achieves 30% Efficiency: Every Building Can Generate Power',
            'Blue Origin\'s Space Hotel Welcomes First Tourists',
            'AI Teachers in South Korea: Personalized Education for Every Student',
        ];
    }

    protected function seedGalleryTranslations(array $locales): void
    {
        if (! is_plugin_active('gallery')) {
            return;
        }

        if (! Schema::hasTable('galleries_translations') || ! Schema::hasTable('galleries')) {
            return;
        }

        $galleryIds = Gallery::query()->pluck('id', 'name')->all();

        foreach ($locales as $locale) {
            $translations = $this->loadTranslations('galleries', $locale);

            if (empty($translations)) {
                continue;
            }

            foreach ($translations as $name => $translation) {
                $galleryId = $galleryIds[$name] ?? null;

                if (! $galleryId) {
                    continue;
                }

                DB::table('galleries_translations')->updateOrInsert(
                    [
                        'lang_code' => $locale,
                        'galleries_id' => $galleryId,
                    ],
                    [
                        'lang_code' => $locale,
                        'galleries_id' => $galleryId,
                        'name' => $translation['name'],
                        'description' => $translation['description'],
                    ]
                );
            }
        }
    }

    protected function seedGalleryMetaTranslations(array $locales): void
    {
        if (! is_plugin_active('gallery')) {
            return;
        }

        if (! Schema::hasTable('gallery_meta_translations') || ! Schema::hasTable('gallery_meta')) {
            return;
        }

        $metaItems = GalleryMeta::query()->select(['id', 'images'])->get();

        if ($metaItems->isEmpty()) {
            return;
        }

        foreach ($locales as $locale) {
            $descriptions = $this->loadTranslations('gallery-meta', $locale);

            if (empty($descriptions)) {
                continue;
            }

            foreach ($metaItems as $meta) {
                $images = $meta->images;

                if (is_string($images)) {
                    $images = json_decode($images, true);
                }

                if (! is_array($images)) {
                    continue;
                }

                $translatedImages = $this->translateGalleryImages($images, $descriptions);

                DB::table('gallery_meta_translations')->updateOrInsert(
                    [
                        'lang_code' => $locale,
                        'gallery_meta_id' => $meta->getKey(),
                    ],
                    [
                        'lang_code' => $locale,
                        'gallery_meta_id' => $meta->getKey(),
                        'images' => json_encode(
                            $translatedImages,
                            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                        ),
                    ]
                );
            }
        }
    }

    protected function translateGalleryImages(array $images, array $descriptions): array
    {
        $translated = [];

        foreach ($images as $index => $image) {
            $translatedImage = $image;

            if (isset($descriptions[$index])) {
                $translatedImage['description'] = $descriptions[$index];
            }

            $translated[] = $translatedImage;
        }

        return $translated;
    }
}
