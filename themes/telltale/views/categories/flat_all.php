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

$categories = $this->data('CategoryTree');
$this->EventArguments['NumRows'] = count($categories);

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
    ?>
    <h2 class="sr-only"><?php echo t('Category List'); ?></h2>
    <ul class="DataList CategoryList">
        <?php foreach ($categories as $category) {
            $this->EventArguments['Category'] = &$category;
            $this->fireEvent('BeforeCategoryItem');
            writeListItem($category, 1);
        } ?>
    </ul>
    <?php
}
?>
<div class="PageControls Bottom">
    <?php PagerModule::write(); ?>
</div>
