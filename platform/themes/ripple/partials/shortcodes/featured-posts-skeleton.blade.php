@once
    <style>
    .post__inside--feature .post__thumbnail {
        position: relative;
        padding-bottom: 66.67%;
        overflow: hidden;
    }

    .post__inside--feature .skeleton-img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .post__inside--feature .post__header {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 20px;
        background: rgba(0, 0, 0, 0.1);
    }

    .post__inside--feature .skeleton-title {
        height: 28px;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 4px;
        margin-bottom: 10px;
        animation: skeleton-loading 1.5s infinite;
    }

    .post__inside--feature .skeleton-meta {
        height: 16px;
        width: 180px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 4px;
        animation: skeleton-loading 1.5s infinite;
    }

    .post__inside--feature-small .post__header {
        padding: 15px;
    }

    .post__inside--feature-small .skeleton-title {
        height: 24px;
        margin-bottom: 0;
    }
    </style>
@endonce

<section class="section pt-50 pb-50 bg-lightgray">
    <div class="container">
        <div class="post-group post-group--hero">
            <div class="post-group__left">
                <article class="post post__inside post__inside--feature">
                    <div class="post__thumbnail">
                        <div class="skeleton-img skeleton-loading-bg"></div>
                    </div>
                    <header class="post__header">
                        <h3 class="post__title text-truncate">
                            <div class="skeleton-title"></div>
                        </h3>
                        <div class="post__meta">
                            <div class="skeleton-meta"></div>
                        </div>
                    </header>
                </article>
            </div>
            <div class="post-group__right">
                @for ($i = 0; $i < 4; $i++)
                    <div class="post-group__item">
                        <article class="post post__inside post__inside--feature post__inside--feature-small">
                            <div class="post__thumbnail">
                                <div class="skeleton-img skeleton-loading-bg"></div>
                            </div>
                            <header class="post__header">
                                <h3 class="post__title text-truncate">
                                    <div class="skeleton-title"></div>
                                </h3>
                            </header>
                        </article>
                    </div>
                @endfor
            </div>
        </div>
    </div>
</section>
