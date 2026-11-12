<?php if (!defined('APPLICATION')) exit();
include_once $this->fetchViewLocation('helper_functions', 'categories');
include_once __DIR__.'/telltale_category_cards.php';

$categoryID = is_object($this->Category) ? val('CategoryID', $this->Category) : null;
$title = $this->data('Title');
if (!is_null($this->Category)) {
    $title .= followButton($categoryID);
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
$this->EventArguments['NumRows'] = count($categories);

telltaleWriteCategoryList($categories, 1);
?>
<div class="PageControls Bottom">
    <?php PagerModule::write(); ?>
</div>
