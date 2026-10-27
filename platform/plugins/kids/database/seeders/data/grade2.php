<?php

return [
    'categories' => [
        [
            'code' => 'g2',
            'name' => 'Toán lớp 2',
            'grade' => 2,
            'parent_code' => null,
            'generator' => null,
            'generator_config' => null,
            'status' => 'approved',
        ],
        [
            'code' => 'g2.numbers',
            'name' => 'Số và phép tính',
            'grade' => 2,
            'parent_code' => 'g2',
            'generator' => null,
            'generator_config' => null,
            'status' => 'approved',
        ],
        [
            'code' => 'g2.geometry',
            'name' => 'Hình học và đo lường',
            'grade' => 2,
            'parent_code' => 'g2',
            'generator' => null,
            'generator_config' => null,
            'status' => 'approved',
        ],
    ],
    'topics' => [
        [
            'code' => 'g2.add_sub_100_nocarry',
            'name' => 'Cộng trừ không nhớ phạm vi 100',
            'grade' => 2,
            'parent_code' => 'g2.numbers',
            'generator' => 'arithmetic',
            'generator_config' => [
                'levels' => [
                    '1' => ['ops' => ['+'], 'a' => [1, 10], 'b' => [1, 9], 'carry' => false, 'result_max' => 20],
                    '2' => ['ops' => ['+', '-'], 'a' => [10, 25], 'b' => [1, 14], 'carry' => false, 'result_max' => 40],
                    '3' => ['ops' => ['+', '-'], 'a' => [20, 39], 'b' => [10, 21], 'carry' => false, 'result_max' => 60],
                    '4' => ['ops' => ['+', '-'], 'a' => [30, 49], 'b' => [10, 31], 'carry' => false, 'result_max' => 80],
                    '5' => ['ops' => ['+', '-'], 'a' => [30, 69], 'b' => [10, 30], 'carry' => false, 'result_max' => 100],
                ],
            ],
            'status' => 'approved',
        ],
        [
            'code' => 'g2.add_sub_100_carry',
            'name' => 'Cộng trừ có nhớ phạm vi 100',
            'grade' => 2,
            'parent_code' => 'g2.numbers',
            'generator' => 'arithmetic',
            'generator_config' => [
                'levels' => [
                    '1' => ['ops' => ['+', '-'], 'a' => [11, 29], 'b' => [2, 9], 'carry' => true, 'result_max' => 40],
                    '2' => ['ops' => ['+', '-'], 'a' => [21, 49], 'b' => [2, 9], 'carry' => true, 'result_max' => 60],
                    '3' => ['ops' => ['+', '-'], 'a' => [21, 39], 'b' => [11, 21], 'carry' => true, 'result_max' => 60],
                    '4' => ['ops' => ['+', '-'], 'a' => [25, 49], 'b' => [15, 31], 'carry' => true, 'result_max' => 80],
                    '5' => ['ops' => ['+', '-'], 'a' => [25, 65], 'b' => [15, 35], 'carry' => true, 'result_max' => 100],
                ],
            ],
            'status' => 'approved',
        ],
        [
            'code' => 'g2.add_sub_1000',
            'name' => 'Cộng trừ phạm vi 1000',
            'grade' => 2,
            'parent_code' => 'g2.numbers',
            'generator' => 'arithmetic',
            'generator_config' => [
                'levels' => [
                    '1' => ['ops' => ['+', '-'], 'a' => [100, 500], 'b' => [100, 400], 'carry' => false, 'result_max' => 1000],
                    '2' => ['ops' => ['+', '-'], 'a' => [101, 800], 'b' => [1, 9], 'carry' => false, 'result_max' => 1000],
                    '3' => ['ops' => ['+', '-'], 'a' => [110, 700], 'b' => [10, 99], 'carry' => false, 'result_max' => 1000],
                    '4' => ['ops' => ['+', '-'], 'a' => [100, 500], 'b' => [100, 499], 'carry' => false, 'result_max' => 1000],
                    '5' => ['ops' => ['+', '-'], 'a' => [150, 550], 'b' => [150, 450], 'carry' => true, 'result_max' => 1000],
                ],
            ],
            'status' => 'approved',
        ],
        [
            'code' => 'g2.mul_div_2_5',
            'name' => 'Bảng nhân, chia 2 và 5',
            'grade' => 2,
            'parent_code' => 'g2.numbers',
            'generator' => 'arithmetic',
            'generator_config' => [
                'levels' => [
                    '1' => ['ops' => ['×'], 'a' => [2, 2], 'b' => [1, 10], 'result_max' => 20],
                    '2' => ['ops' => ['×'], 'a' => [5, 5], 'b' => [1, 10], 'result_max' => 50],
                    '3' => ['ops' => ['×'], 'a' => [2, 5], 'b' => [1, 10], 'result_max' => 50],
                    '4' => ['ops' => ['÷'], 'a' => [2, 5], 'b' => [1, 10], 'result_max' => 50],
                    '5' => ['ops' => ['×', '÷'], 'a' => [2, 5], 'b' => [1, 10], 'missing' => true, 'result_max' => 50],
                ],
            ],
            'status' => 'approved',
        ],
        [
            'code' => 'g2.compare_1000',
            'name' => 'So sánh số',
            'grade' => 2,
            'parent_code' => 'g2.numbers',
            'generator' => 'comparison',
            'generator_config' => [
                'levels' => [
                    '1' => ['mode' => 'number', 'max' => 20],
                    '2' => ['mode' => 'number', 'max' => 100],
                    '3' => ['mode' => 'number', 'max' => 1000],
                    '4' => ['mode' => 'expr_vs_number', 'max' => 100],
                    '5' => ['mode' => 'expr_vs_expr', 'max' => 100],
                ],
            ],
            'status' => 'approved',
        ],
        [
            'code' => 'g2.word_one_step',
            'name' => 'Toán có lời văn (một bước)',
            'grade' => 2,
            'parent_code' => 'g2.numbers',
            'generator' => 'word_problem',
            'generator_config' => [
                'levels' => [
                    '1' => ['ops' => ['+'], 'a' => [1, 10], 'b' => [1, 9], 'carry' => false, 'result_max' => 20],
                    '2' => ['ops' => ['-'], 'a' => [10, 20], 'b' => [1, 9], 'carry' => false, 'result_max' => 20],
                    '3' => ['ops' => ['+', '-'], 'a' => [10, 50], 'b' => [10, 40], 'carry' => false, 'result_max' => 100],
                    '4' => ['ops' => ['+', '-'], 'a' => [15, 65], 'b' => [15, 35], 'carry' => true, 'result_max' => 100],
                    '5' => ['ops' => ['×'], 'a' => [2, 5], 'b' => [1, 10], 'result_max' => 50],
                ],
                'templates' => [
                    ['op' => '+', 'body' => '{name} có {a} {item}, mẹ cho thêm {b} {item}. Hỏi {name} có tất cả bao nhiêu {item}?', 'unit' => 'cái'],
                    ['op' => '-', 'body' => '{name} có {a} {item}, đã cho bạn {b} {item}. Hỏi {name} còn lại bao nhiêu {item}?', 'unit' => 'cái'],
                    ['op' => '×', 'body' => 'Mỗi hộp có {a} {item}. Có tất cả {b} hộp như thế. Hỏi có bao nhiêu {item}?', 'unit' => 'cái'],
                    ['op' => '÷', 'body' => 'Có {a} {item} chia đều cho {b} bạn. Hỏi mỗi bạn được mấy {item}?', 'unit' => 'cái'],
                ],
            ],
            'status' => 'approved',
        ],
        [
            'code' => 'g2.cube_count',
            'name' => 'Đếm khối lập phương',
            'grade' => 2,
            'parent_code' => 'g2.geometry',
            'generator' => 'cube_count',
            'generator_config' => [
                'levels' => [
                    '1' => ['rows' => 2, 'cols' => 2, 'max_height' => 1, 'allow_gaps' => false],
                    '2' => ['rows' => 3, 'cols' => 2, 'max_height' => 2, 'allow_gaps' => false],
                    '3' => ['rows' => 3, 'cols' => 3, 'max_height' => 2, 'allow_gaps' => false],
                    '4' => ['rows' => 3, 'cols' => 3, 'max_height' => 3, 'allow_gaps' => false],
                    '5' => ['rows' => 4, 'cols' => 3, 'max_height' => 3, 'allow_gaps' => true],
                ],
            ],
            'status' => 'approved',
        ],
    ],
    'word_templates' => [
        // Addition (3)
        [
            'op' => '+',
            'body' => '{name} có {a} quả cam, mẹ cho thêm {b} quả cam. Hỏi {name} có tất cả bao nhiêu quả cam?',
            'unit' => 'quả cam',
            'status' => 'approved',
        ],
        [
            'op' => '+',
            'body' => 'Đội Một trồng được {a} cây xanh, đội Hai trồng được {b} cây xanh. Hỏi cả hai đội trồng được bao nhiêu cây xanh?',
            'unit' => 'cây xanh',
            'status' => 'approved',
        ],
        [
            'op' => '+',
            'body' => 'Bình có {a} viên bi, An có nhiều hơn Bình {b} viên bi. Hỏi An có bao nhiêu viên bi?',
            'unit' => 'viên bi',
            'status' => 'approved',
        ],
        // Subtraction (3)
        [
            'op' => '-',
            'body' => 'Trên cành cây có {a} con chim, một lát sau có {b} con bay đi. Hỏi trên cành cây còn lại bao nhiêu con chim?',
            'unit' => 'con chim',
            'status' => 'approved',
        ],
        [
            'op' => '-',
            'body' => 'Cửa hàng có {a} quyển vở, buổi sáng bán được {b} quyển vở. Hỏi cửa hàng còn lại bao nhiêu quyển vở?',
            'unit' => 'quyển vở',
            'status' => 'approved',
        ],
        [
            'op' => '-',
            'body' => 'Lan có {a} bông hoa, Lan tặng Mai {b} bông hoa. Hỏi Lan còn lại bao nhiêu bông hoa?',
            'unit' => 'bông hoa',
            'status' => 'approved',
        ],
        // Multiplication (3)
        [
            'op' => '×',
            'body' => 'Mỗi bàn học có {a} bạn ngồi. Hỏi {b} bàn học như thế có tất cả bao nhiêu bạn?',
            'unit' => 'bạn',
            'status' => 'approved',
        ],
        [
            'op' => '×',
            'body' => 'Một gói kẹo có {a} cái kẹo. Mẹ mua {b} gói kẹo như thế. Hỏi mẹ mua tất cả bao nhiêu cái kẹo?',
            'unit' => 'cái kẹo',
            'status' => 'approved',
        ],
        [
            'op' => '×',
            'body' => 'Mỗi túi có {a} quả táo. Có tất cả {b} túi táo như vậy. Hỏi có bao nhiêu quả táo?',
            'unit' => 'quả táo',
            'status' => 'approved',
        ],
        // Division (3)
        [
            'op' => '÷',
            'body' => 'Có {a} cái kẹo chia đều cho {b} bạn. Hỏi mỗi bạn được mấy cái kẹo?',
            'unit' => 'cái kẹo',
            'status' => 'approved',
        ],
        [
            'op' => '÷',
            'body' => 'Có {a} bông hoa cắm đều vào {b} lọ hoa. Hỏi mỗi lọ hoa có bao nhiêu bông hoa?',
            'unit' => 'bông hoa',
            'status' => 'approved',
        ],
        [
            'op' => '÷',
            'body' => 'Cô giáo có {a} quyển vở thưởng đều cho {b} học sinh xuất sắc. Hỏi mỗi học sinh nhận được bao nhiêu quyển vở?',
            'unit' => 'quyển vở',
            'status' => 'approved',
        ],
    ],
];
