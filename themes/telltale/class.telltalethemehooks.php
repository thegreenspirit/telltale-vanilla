<?php
class TelltaleThemeHooks extends Gdn_Plugin {
    public function entryController_signIn_handler($sender, $args = []) {
        $request = Gdn::request();
        $scheme = $request->scheme() ?: 'http';
        $hostAndPort = $request->hostAndPort();

        if ($hostAndPort) {
            redirectTo($scheme.'://'.$hostAndPort.'/account/login?rd=community');
        }

        redirectTo('/account/login?rd=community');
    }

    public function discussionController_render_before($sender) {
        $this->removeTelltaleSidebarModules($sender);
    }

    public function discussionsController_render_before($sender) {
        $this->removeTelltaleSidebarModules($sender);
    }

    public function activityController_render_before($sender) {
        $this->removeTelltaleSidebarModules($sender);
    }

    public function tagsController_render_before($sender) {
        $this->removeTelltaleSidebarModules($sender);
    }

    /**
     * @param Gdn_Controller $sender
     * @return void
     */
    private function removeTelltaleSidebarModules($sender) {
        if (!isset($sender->Assets['Panel']) || !is_array($sender->Assets['Panel'])) {
            return;
        }

        foreach ($sender->Assets['Panel'] as $assetName => $module) {
            $assetKey = strtolower((string)$assetName);
            $moduleClass = is_object($module) ? strtolower(get_class($module)) : '';

            if (
                $assetKey === 'guestmodule'
                || $assetKey === 'categoriesmodule'
                || $moduleClass === 'guestmodule'
                || $moduleClass === 'categoriesmodule'
            ) {
                unset($sender->Assets['Panel'][$assetName]);
            }
        }
    }

    /**
     * @param CategoriesController $sender
     * @return void
     */
    public function categoriesController_render_before($sender) {
        $this->removeTelltaleSidebarModules($sender);

        $category = $sender->data('Category');
        if (empty($category)) {
            return;
        }

        $displayAs = val('DisplayAs', $category);
        if (in_array($displayAs, 'Discussions', true)) {
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