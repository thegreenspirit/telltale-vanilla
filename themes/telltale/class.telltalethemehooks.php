<?php
class TelltaleThemeHooks extends Gdn_Plugin {
    /**
     * @param CategoriesController $sender
     * @return void
     */
    public function categoriesController_render_before($sender) {
        $category = $sender->data('Category');
        if (empty($category)) {
            return;
        }

        $displayAs = val('DisplayAs', $category);
        if (!in_array($displayAs, ['Categories', 'Flat', 'Heading'], true)) {
            return;
        }

        $categoryID = (int)val('CategoryID', $category, 0);
        if ($categoryID <= 0) {
            return;
        }

        // Get visible descendants and select only immediate children.
        $descendants = CategoryModel::getSubtree($categoryID, false);
        $directChildren = [];
        foreach ($descendants as $candidate) {
            if ((int)val('ParentCategoryID', $candidate, 0) === $categoryID) {
                $directChildren[] = $candidate;
            }
        }

        if (count($directChildren) !== 1 || val('DisplayAs', $directChildren[0]) !== 'Discussions') {
            return;
        }

        $target = CategoryModel::categoryUrl($directChildren[0], '', true);
        if ($target) {
            redirectTo($target);
        }
    }
}