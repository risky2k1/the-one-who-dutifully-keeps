// dashboard/activity-logs.js imports ActivityLogComponent.vue — Vue required.
// Hand-written LTR + RTL SCSS variants (not rtlcss-generated), nested output dirs.
export default {
    vue: true,
    js: [
        'member-admin',
        { src: 'resources/js/dashboard/script.js',        out: 'dashboard/script.js' },
        { src: 'resources/js/dashboard/activity-logs.js', out: 'dashboard/activity-logs.js' },
    ],
    sass: [
        { src: 'resources/sass/dashboard/style.scss',     out: 'dashboard/style.css' },
        { src: 'resources/sass/dashboard/style-rtl.scss', out: 'dashboard/style-rtl.css' },
        { src: 'resources/sass/front-auth.scss',          out: 'front-auth.css' },
    ],
}
