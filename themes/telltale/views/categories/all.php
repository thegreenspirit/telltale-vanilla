<?php if (!defined('APPLICATION')) exit();
include_once $this->fetchViewLocation('helper_functions', 'categories');
include_once __DIR__.'/telltale_category_cards.php';

$title = $this->data('Title');
if (!is_null($this->Category)) {
    $title .= followButton($this->Category->CategoryID);
}
echo '<h1 class="H HomepageTitle">'.$title.'</h1>';
if ($description = $this->description()) {
    echo wrap($description, 'div', ['class' => 'P PageDescription']);
}
$this->fireEvent('AfterPageTitle');
if ($this->data('EnableFollowingFilter')) {
    echo '<div class="PageControls Top">'.categoryFilters().'</div>';
}

$categories = $this->data('CategoryTree');

if (is_null($this->Category)) {
    if (empty($categories)) {
        echo '<div class="Empty">'.t('No categories were found.').'</div>';
    } else {
        ?>
        <div class="DataListWrap">
            <h2 class="sr-only"><?php echo t('Category List'); ?></h2>
            <ul class="DataList CategoryList TelltaleCategoryList">
                <?php telltaleWriteCategoryCards($categories, 1); ?>
            </ul>
        </div>
        <?php
    }
} else {
    writeCategoryList($categories, 1);
}