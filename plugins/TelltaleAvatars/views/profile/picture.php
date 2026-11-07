<?php if (!defined('APPLICATION')) exit();

$userID = (int) val('UserID', $this->User, Gdn::session()->UserID);
$transientKey = Gdn::session()->transientKey();
$selectedCategory = (string) Gdn::request()->get('CategoryID', '');
$action = url('/profile/picture?userid='.rawurlencode((string) $userID)
    .($selectedCategory !== '' ? '&CategoryID='.rawurlencode($selectedCategory) : ''));
$avatars = (array) $this->data('telltaleAvatars', []);

$categories = [
    '65' => 'The Wolf Among Us',
    '137' => 'Batman - The Telltale Series',
    '42' => 'Tales of Monkey Island',
    '67' => 'Hector: Badge of Carnage',
    '64' => 'The Walking Dead',
    '140' => 'Guardians of the Galaxy',
    '110' => 'Game of Thrones',
    '99' => 'Tales from the Borderlands',
    '53' => 'At The Inventory',
    '56' => 'Jurassic Park',
    '73' => 'Law & Order: Legacies',
    '54' => 'Back to the Future',
    '51' => 'Nelson Tethers: Puzzle Agent',
    '11' => 'Sam & Max',
    '8' => 'Bone',
    '31' => 'Strong Bad',
    '131' => 'Minecraft: Story Mode',
    '-1' => 'Custom Avatar'
];
?>
<div class="js-change-picture">
    <h1 class="H"><?php echo t('Choose an Avatar'); ?></h1>
    <p><?php echo t('Select an avatar from one of our games or use a gravatar or other linked source. Simply select an avatar from below.'); ?></p>

    <form method="post"
          action="<?php echo htmlspecialchars($action, ENT_QUOTES, 'UTF-8'); ?>"
          enctype="multipart/form-data"
          class="js-change-picture-form">
        <div>
            <input type="hidden" id="Form_TransientKey" name="TransientKey"
                   value="<?php echo htmlspecialchars($transientKey, ENT_QUOTES, 'UTF-8'); ?>" />
            <input type="hidden" id="Form_hpt" name="hpt" value="" style="display:none;" />
            <input type="hidden" id="Form_Avatar" name="Avatar" value=""
                   class="js-new-avatar-url hidden" />

            <div class="TextBoxWrapper">
                <select id="Form_AvatarCategory" name="AvatarCategory"
                        class="js-change-avatar-category padded-bottom">
                    <option value=""><?php echo t('Select a category...'); ?></option>
                    <?php foreach ($categories as $id => $name): ?>
                        <option value="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>"
                            <?php echo ($selectedCategory === (string) $id) ? 'selected="selected"' : ''; ?>>
                            <?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="avatar-options"><?php foreach ($avatars as $avatar): ?><img src="<?php echo htmlspecialchars($avatar['url'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($avatar['alt'], ENT_QUOTES, 'UTF-8'); ?>" class="pointer padded-left padded-right padded-bottom js-select-avatar" style="width: 200px;" /><?php endforeach; ?></div>
        </div>
    </form>
</div>
