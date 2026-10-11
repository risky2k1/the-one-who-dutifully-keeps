{!! SeoHelper::render() !!}

@include('plugins/member::themes.dashboard.layouts.header-meta')

<link
    href="{{ asset('vendor/core/plugins/member/css/dashboard/style.css') }}"
    rel="stylesheet"
>

@if (BaseHelper::isRtlEnabled())
    <link
        href="{{ asset('vendor/core/core/base/css/core.rtl.css') }}"
        rel="stylesheet"
    >
    <link
        href="{{ asset('vendor/core/plugins/member/css/dashboard/style-rtl.css') }}"
        rel="stylesheet"
    >
@endif

@if (File::exists($styleIntegration = Theme::getStyleIntegrationPath()))
    {{-- Link the same filename getStyleIntegrationPath() resolves to: a multi-tenant install suffixes it per store. basename() is style.integration.css by default. --}}
    {!! Html::style(Theme::asset()->url('css/' . basename($styleIntegration) . '?v=' . filectime($styleIntegration))) !!}
@endif
