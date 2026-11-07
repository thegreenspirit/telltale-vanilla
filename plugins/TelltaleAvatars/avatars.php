<?php if (!defined('APPLICATION')) exit();
/**
 * Avatar presets. Keys are the category IDs used in views/profile/picture.php,
 * values are file names under baseUrl. Category -1 (Custom Avatar) is handled
 * in the plugin: it shows the user's Gravatar.
 */
$range = function ($prefix, $to) {
    $files = [];
    for ($i = 1; $i <= $to; $i++) {
        $files[] = sprintf('%s_avatar_%04d.png', $prefix, $i);
    }
    return $files;
};

return [
    'baseUrl' => 'https://cdn.telltalegames.com/avatars/v1/200x250/',
    'categories' => [
        '65' => $range('TWAU', 47),   // The Wolf Among Us
        '137' => [                    // Batman - The Telltale Series
            'BAT_avatar_002.png',
            'BAT_avatar_005.png',
            'BAT_avatar_003.png',
            'BAT_avatar_007.png',
            'BAT_avatar_006.png',
            'BAT_avatar_001.png',
            'BAT_avatar_004.png',
            'BATMAN_avatar_0008.png',
            'BAT_avatar_008.png',
        ],
        '42' => $range('TOMI', 18),   // Tales of Monkey Island
        '67' => $range('HCTR', 23),   // Hector: Badge of Carnage
        '64' => $range('DEAD', 96),   // The Walking Dead
        '140' => $range('GOTG', 5),   // Guardians of the Galaxy
        '110' => $range('XGOT', 13),  // Game of Thrones
        '99' => $range('TFTB', 51),   // Tales from the Borderlands
        '53' => $range('PKRN', 11),   // At The Inventory
        '56' => $range('XXJP', 11),   // Jurassic Park
        '73' => $range('LNDO', 8),    // Law & Order: Legacies
        '54' => $range('BTTF', 11),   // Back to the Future
        '51' => $range('PUZZ', 11),   // Nelson Tethers: Puzzle Agent
        '11' => $range('SMMX', 23),   // Sam & Max
        '8' => $range('BONE', 8),     // Bone
        '31' => $range('SBAD', 15),   // Strong Bad
        '131' => $range('MINE', 23),  // Minecraft: Story Mode
    ],
];
