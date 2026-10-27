<?php

return [
    [
        'name' => 'Kids',
        'flag' => 'kids.index',
        'parent_flag' => 'core.cms',
    ],
    // Topics
    [
        'name' => 'Topics',
        'flag' => 'kids.topics.index',
        'parent_flag' => 'kids.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'kids.topics.create',
        'parent_flag' => 'kids.topics.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'kids.topics.edit',
        'parent_flag' => 'kids.topics.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'kids.topics.destroy',
        'parent_flag' => 'kids.topics.index',
    ],
    // Word Templates
    [
        'name' => 'Word Templates',
        'flag' => 'kids.word-templates.index',
        'parent_flag' => 'kids.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'kids.word-templates.create',
        'parent_flag' => 'kids.word-templates.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'kids.word-templates.edit',
        'parent_flag' => 'kids.word-templates.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'kids.word-templates.destroy',
        'parent_flag' => 'kids.word-templates.index',
    ],
    // Parents
    [
        'name' => 'Parents',
        'flag' => 'kids.parents.index',
        'parent_flag' => 'kids.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'kids.parents.create',
        'parent_flag' => 'kids.parents.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'kids.parents.edit',
        'parent_flag' => 'kids.parents.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'kids.parents.destroy',
        'parent_flag' => 'kids.parents.index',
    ],
    // Reports
    [
        'name' => 'Reports',
        'flag' => 'kids.reports.index',
        'parent_flag' => 'kids.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'kids.reports.edit',
        'parent_flag' => 'kids.reports.index',
    ],
];
