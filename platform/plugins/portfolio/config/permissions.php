<?php

return [
    [
        'name' => 'Portfolio',
        'flag' => 'portfolio.index',
        'parent_flag' => 'core.cms',
    ],
    // Projects
    [
        'name' => 'Projects',
        'flag' => 'portfolio.projects.index',
        'parent_flag' => 'portfolio.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'portfolio.projects.create',
        'parent_flag' => 'portfolio.projects.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'portfolio.projects.edit',
        'parent_flag' => 'portfolio.projects.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'portfolio.projects.destroy',
        'parent_flag' => 'portfolio.projects.index',
    ],
    // Categories
    [
        'name' => 'Categories',
        'flag' => 'portfolio.categories.index',
        'parent_flag' => 'portfolio.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'portfolio.categories.create',
        'parent_flag' => 'portfolio.categories.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'portfolio.categories.edit',
        'parent_flag' => 'portfolio.categories.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'portfolio.categories.destroy',
        'parent_flag' => 'portfolio.categories.index',
    ],
    // Skills
    [
        'name' => 'Skills & Domains',
        'flag' => 'portfolio.skills.index',
        'parent_flag' => 'portfolio.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'portfolio.skills.create',
        'parent_flag' => 'portfolio.skills.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'portfolio.skills.edit',
        'parent_flag' => 'portfolio.skills.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'portfolio.skills.destroy',
        'parent_flag' => 'portfolio.skills.index',
    ],
    // Experiences
    [
        'name' => 'Experiences',
        'flag' => 'portfolio.experiences.index',
        'parent_flag' => 'portfolio.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'portfolio.experiences.create',
        'parent_flag' => 'portfolio.experiences.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'portfolio.experiences.edit',
        'parent_flag' => 'portfolio.experiences.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'portfolio.experiences.destroy',
        'parent_flag' => 'portfolio.experiences.index',
    ],
    // Settings
    [
        'name' => 'Settings & Profile',
        'flag' => 'portfolio.settings',
        'parent_flag' => 'portfolio.index',
    ],
];
