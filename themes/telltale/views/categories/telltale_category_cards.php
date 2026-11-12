<?php if (!defined('APPLICATION')) exit();

/**
 * Identify a Telltale "header discussion" category.
 *
 * These are overview/header categories with exactly one visible child, and
 * that child is a regular Discussions category. The parent is the homepage
 * card; the child is the real discussion destination and is not listed twice.
 *
 * @param array|object $category
 * @return array|false The sole child, or false when this is not a match.
 */
function telltaleHeaderDiscussionChild($category) {
    $displayAs = val('DisplayAs', $category);
    if (!in_array($displayAs, ['Categories', 'Flat', 'Heading'], true)) {
        return false;
    }

    $children = val('Children', $category, []);
    if (!is_array($children) || count($children) !== 1) {
        return false;
    }

    $child = reset($children);
    if (val('DisplayAs', $child) !== 'Discussions') {
        return false;
    }

    return $child;
}

/**
 * Only normal web URLs and relative paths are accepted.
 *
 * @param string $photoUrl
 * @return string
 */
function telltaleCategoryBackgroundStyle($photoUrl) {
    $photoUrl = trim((string)$photoUrl);
    if ($photoUrl === '') {
        return '';
    }

    $scheme = parse_url($photoUrl, PHP_URL_SCHEME);
    if ($scheme && !in_array(strtolower($scheme), ['http', 'https'], true)) {
        return '';
    }

    $cssUrl = str_replace( ["\\", "'", "\r", "\n"], ["\\\\", "\\27 ", '', ''], $photoUrl );
    return "background-image: url('".$cssUrl."');";
}

/**
 * @param array $categories
 * @return array
 */
function telltaleFilterArchivedCategories($categories) {
    if (empty($categories) || !is_array($categories)) {
        return [];
    }

    $result = [];
    foreach ($categories as $category) {
        if (val('Archived', $category)) {
            continue;
        }

        $children = val('Children', $category);
        if (is_array($children) && !empty($children)) {
            $children = telltaleFilterArchivedCategories($children);
            if (is_array($category)) {
                $category['Children'] = $children;
            } else {
                $category->Children = $children;
            }
        }

        $result[] = $category;
    }

    return $result;
}

/**
 * @param array $categories
 * @param int $depth
 * @return void
 */
function telltaleWriteCategoryList($categories, $depth = 1) {
    if (empty($categories)) {
        echo '<div class="Empty">'.t('No categories were found.').'</div>';
        return;
    }
    ?>
    <div class="DataListWrap">
        <h2 class="sr-only"><?php echo t('Category List'); ?></h2>
        <ul class="DataList CategoryList TelltaleCategoryList">
            <?php telltaleWriteCategoryCards($categories, $depth); ?>
        </ul>
    </div>
    <?php
}

/**
 * @param array $categories
 * @param int $depth
 * @return void
 */
function telltaleWriteCategoryCards($categories, $depth = 1) {
    if (empty($categories) || !is_array($categories)) {
        return;
    }

    foreach ($categories as $category) {
        $children = val('Children', $category, []);
        $categoryID = val('CategoryID', $category);
        $categoryClass = cssClass($category, true);
        $writeChildren = getWriteChildrenMethod($category, $depth);
        $headingLevel = min(6, $depth + 2);
        $displayAs = val('DisplayAs', $category);
        $headerDiscussionChild = telltaleHeaderDiscussionChild($category);
        $isHeaderDiscussion = ($headerDiscussionChild !== false);

        if ($displayAs === 'Heading' && !$isHeaderDiscussion) {
            ?>
            <li id="Category_<?php echo (int)$categoryID; ?>" class="CategoryHeading <?php echo htmlspecialchars($categoryClass, ENT_QUOTES, 'UTF-8'); ?>">
                <div role="heading" aria-level="<?php echo (int)$headingLevel; ?>" class="ItemContent Category TelltaleCategoryHeading">
                    <?php echo Gdn_Format::text(val('Name', $category)); ?>
                </div>
            </li>
            <?php
        } else {
            Gdn::controller()->EventArguments['ChildCategories'] = &$children;
            Gdn::controller()->EventArguments['Category'] = &$category;
            Gdn::controller()->fireEvent('BeforeCategoryItem');

            $photoUrl = val('PhotoUrl', $category, '');
            if ($photoUrl === '' && $isHeaderDiscussion) {
                $photoUrl = val('PhotoUrl', $headerDiscussionChild, '');
            }
            $backgroundStyle = telltaleCategoryBackgroundStyle($photoUrl);
            $categoryUrl = categoryUrl($category, '', '//');
            $discussionCount = (int)val(
                'CountAllDiscussions',
                $category,
                $isHeaderDiscussion ? val('CountAllDiscussions', $headerDiscussionChild, 0) : 0
            );
            $discussionLabel = sprintf(
                pluralTranslate(
                    $discussionCount,
                    '%s discussion html',
                    '%s discussions html',
                    t('%s discussion'),
                    t('%s discussions')
                ),
                bigPlural($discussionCount, '%s discussion')
            );
            $extraClass = $isHeaderDiscussion ? ' TelltaleHeaderDiscussion' : '';
            ?>
            <li id="Category_<?php echo (int)$categoryID; ?>" class="<?php echo htmlspecialchars(trim($categoryClass.$extraClass), ENT_QUOTES, 'UTF-8'); ?>">
                <a href="<?php echo htmlspecialchars($categoryUrl, ENT_QUOTES, 'UTF-8'); ?>"
                   class="block relative cover ratio-16x9 TelltaleCategoryCard"<?php
                   if ($backgroundStyle !== '') {
                       echo ' style="'.htmlspecialchars($backgroundStyle, ENT_QUOTES, 'UTF-8').'"';
                   }
                   ?>>
                    <div class="absolute b0 l0 w100 bg-transparent-black p1 TelltaleCategoryCardCaption">
                        <h4 class="m0 white"><?php echo Gdn_Format::text(val('Name', $category)); ?></h4>
                        <p class="m0 white text-smaller uppercase"><?php echo $discussionLabel; ?></p>
                    </div>
                </a>
                <?php if (!$isHeaderDiscussion && $writeChildren === 'list' && !empty($children)): ?>
                    <div class="ChildCategories TelltaleCategoryChildren">
                        <?php echo categoryString($children, $depth + 1); ?>
                    </div>
                <?php endif; ?>
            </li>
            <?php
        }

        if (!$isHeaderDiscussion && $writeChildren === 'items') {
            telltaleWriteCategoryCards($children, $depth + 1);
        }
    }
}