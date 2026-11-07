<?php
/**
 * Telltale Avatars for Vanilla Forums 3.0.x.
 *
 * Replaces the profile picture page's view while leaving ProfileController's
 * permission checks, request routing, and regular upload handling intact.
 */
class TelltaleAvatarsPlugin extends Gdn_Plugin {

    /**
     * Intercept ProfileController's render() magic method.
     *
     * @param Gdn_Controller $sender
     * @param array $requestArgs
     * @return mixed
     */
    public function profileController_render_create($sender, $requestArgs = []) {
        $renderArgs = is_array($sender->EventArguments) ? $sender->EventArguments : [];

        // Match on the controller method, not the URL: the side-menu link is
        // /profile/picture/{id}/{name}, the photo popup is /profile/picture?userid=...
        $isPicturePage = isset($sender->RequestMethod)
            && strtolower($sender->RequestMethod) === 'picture';

        if ($isPicturePage) {
            // ProfileController::picture() has already run its permission checks
            // and loaded $sender->User by the time it calls render(), so this is
            // the safe place to persist a chosen avatar URL.
            $this->saveSelectedAvatar($sender);

            // Avatars for the selected category, rendered server-side by the view.
            $sender->setData('telltaleAvatars', $this->getAvatars(Gdn::request()->get('CategoryID', ''), $sender));

            // Vanilla passed: picture, profile, dashboard, [asset name].
            // Keep the view and controller name; only change its application.
            $renderArgs[0] = 'picture';
            $renderArgs[1] = 'profile';
            $renderArgs[2] = 'plugins/TelltaleAvatars';
        }

        // Call the real implementation directly to avoid re-entering this override.
        return call_user_func_array([$sender, 'xRender'], $renderArgs);
    }

    /**
     * Get the avatar image URLs for a category from avatars.php.
     *
     * Category -1 ("Custom Avatar") returns the user's Gravatar.
     *
     * @param string|int $categoryID
     * @param ProfileController $sender
     * @return array List of ['url' => string, 'alt' => string].
     */
    private function getAvatars($categoryID, $sender) {
        $categoryID = (string) $categoryID;
        if ($categoryID === '') {
            return [];
        }

        if ($categoryID === '-1') {
            $email = trim((string) val('Email', $sender->User, ''));
            return $email === '' ? [] : [['url' => $this->gravatarUrl($email), 'alt' => 'Gravatar']];
        }

        $config = require __DIR__.'/avatars.php';
        $files = val($categoryID, $config['categories'], []);
        $base = rtrim($config['baseUrl'], '/').'/';

        $avatars = [];
        foreach ($files as $file) {
            $avatars[] = ['url' => $base.$file, 'alt' => pathinfo($file, PATHINFO_FILENAME)];
        }
        return $avatars;
    }

    /**
     * Build a 200px Gravatar URL, using the Gravatar plugin when it is enabled so the
     * fallback image matches the rest of the site.
     *
     * @param string $email
     * @return string
     */
    private function gravatarUrl($email) {
        if (class_exists('GravatarPlugin')) {
            return GravatarPlugin::generateUrl($email, 200);
        }
        $scheme = Gdn::request()->scheme() === 'https' ? 'https://secure.gravatar.com' : 'http://www.gravatar.com';
        return $scheme.'/avatar/'.md5(strtolower($email)).'/?'.http_build_query(['size' => 200, 'rating' => 'g']);
    }

    /**
     * Store a selected remote avatar URL.
     *
     * Vanilla 3.0.2's ProfileController::picture() never fires a BeforePicture
     * event (so a profileController_beforePicture_handler is dead code), and its
     * stock upload handling ignores a plain URL in the Avatar field. The chooser
     * submits an http(s) URL, so it is saved to User.Photo here, called from the
     * render override after picture() has already enforced login, photo-edit
     * permissions and the edit-other-users restriction via getUserInfo().
     *
     * @param ProfileController $sender
     */
    private function saveSelectedAvatar($sender) {
        if (strtoupper((string) Gdn::request()->requestMethod()) !== 'POST') {
            return;
        }

        $avatarURL = trim((string) Gdn::request()->post('Avatar', ''));
        if ($avatarURL === '') {
            return; // Ordinary file upload, already handled by Vanilla.
        }

        $parts = @parse_url($avatarURL);
        if (!$parts || empty($parts['host']) || empty($parts['scheme'])
            || !in_array(strtolower($parts['scheme']), ['http', 'https'], true)
            || filter_var($avatarURL, FILTER_VALIDATE_URL) === false
        ) {
            return;
        }

        if (!Gdn::session()->isValid() || !isset($sender->Form) || !$sender->Form->authenticatedPostBack()) {
            return; // Requires a valid transient key.
        }

        $userID = (int) val('UserID', $sender->User, 0);
        if (!$userID) {
            return;
        }

        Gdn::userModel()->setField($userID, 'Photo', $avatarURL);
        $sender->informMessage(t('Your settings have been saved.'));

        if ($sender->deliveryType() === DELIVERY_TYPE_VIEW) {
            // Chooser was opened in the profile photo popup: refresh like the stock action does.
            $sender->jsonTarget('', '', 'Refresh');
            $sender->setRedirectTo(userUrl($sender->User));
        } else {
            redirectTo(userUrl($sender->User));
        }
    }

    /**
     * @param ProfileController $sender
     */
    public function profileController_render_before($sender) {
        if ($sender->deliveryType() === DELIVERY_TYPE_ALL) {
            $sender->addJsFile('avatars.js', 'plugins/TelltaleAvatars');
        }
    }

    /**
     * Add the "Choose your Avatar" entry to the profile navigation.
     *
     * @param ProfileController $sender
     */
    public function profileController_addProfileTabs_handler($sender) {
        $session = Gdn::session();
        $user = $sender->User;
        if (!$user || !$session->isValid() || !$sender->CanEditPhotos || val('Banned', $user)) {
            return;
        }
        if ($user->UserID != $session->UserID && !$session->checkPermission('Garden.Users.Edit')) {
            return;
        }

        $sender->addProfileTab(
            'ChooseAvatar',
            '/profile/picture?userid='.(int) $user->UserID,
            'ChooseAvatarLink',
            t('Choose your Avatar')
        );
    }
}
