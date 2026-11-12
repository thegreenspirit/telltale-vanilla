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

$categories = telltaleFilterArchivedCategories($this->data('CategoryTree'));

telltaleWriteCategoryList($categories, 1);
