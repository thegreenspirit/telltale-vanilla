<?php
/**
 * Forum accounts are created in two ways, both through provisionForumUser():
 *  - immediately, when the website API calls /utility/telltaleprovision after an
 *    account chooses its community username (signed request, see forum-bridge.php);
 *  - lazily, the first time a signed-in Telltale user loads a forum page.
 *
 * The Telltale token is validated against the website's shared SQLite token
 * table. The token itself is never trusted as a source of user identity.
 */
class TelltaleAccountSSOPlugin extends Gdn_Plugin {
    const PROVIDER_KEY = 'telltale';
    const SESSION_PREFERENCE = 'TelltaleSSOUserID';

    const ERROR_CONFLICT = 409;
    const ERROR_INVALID = 422;

    public function gdn_dispatcher_appStartup_handler($sender) {
        $token = $this->getTelltaleToken();

        if ($token === '') {
            $this->endManagedSessionWithoutTelltaleToken();
            return;
        }

        try {
            $this->signInFromTelltaleToken($token);
        } catch (Throwable $exception) {
            error_log('TelltaleAccountSSO: ' . $exception->getMessage());
        }
    }

    /**
     * makes the signout button USELESS SHIT (intentional)
     */
    public function entryController_signOut_handler($sender) {
        $token = $this->getTelltaleToken();
        if ($token === '') {
            return;
        }

        try {
            $this->signInFromTelltaleToken($token);
        } catch (Throwable $exception) {
            error_log('Telltale SSO sign-out restoration: ' . $exception->getMessage());
        }
    }

    /**
     * Body: {"user_id": <Telltale user id>}. The request must carry a valid
     * X-Telltale-Timestamp / X-Telltale-Signature pair. The account details are
     * read from the website database, never from the request.
     */
    public function utilityController_telltaleProvision_create($sender) {
        if (strtoupper((string)Gdn::request()->requestMethod()) !== 'POST') {
            $this->respondJson(405, ['ok' => false, 'message' => 'POST required.']);
        }

        try {
            $this->loadWebsiteIncludes();

            $body = (string)file_get_contents('php://input');
            $timestamp = isset($_SERVER['HTTP_X_TELLTALE_TIMESTAMP']) ? (string)$_SERVER['HTTP_X_TELLTALE_TIMESTAMP'] : '';
            $signature = isset($_SERVER['HTTP_X_TELLTALE_SIGNATURE']) ? (string)$_SERVER['HTTP_X_TELLTALE_SIGNATURE'] : '';
            if (!tt_forum_bridge_verify($timestamp, $body, $signature)) {
                $this->respondJson(403, ['ok' => false, 'message' => 'Invalid signature.']);
            }

            $payload = json_decode($body, true);
            $telltaleUserID = is_array($payload) ? (int)(isset($payload['user_id']) ? $payload['user_id'] : 0) : 0;
            $telltaleUser = $telltaleUserID > 0 ? tt_db_user_by_id($telltaleUserID) : null;
            if (!is_array($telltaleUser)) {
                $this->respondJson(404, ['ok' => false, 'message' => 'The Telltale account was not found.']);
            }
            if (trim((string)(isset($telltaleUser['username']) ? $telltaleUser['username'] : '')) === '') {
                $this->respondJson(422, ['ok' => false, 'message' => 'The account has no community username yet.']);
            }

            $result = $this->provisionForumUser($telltaleUser);
            $this->respondJson(200, [
                'ok' => true,
                'forum_id' => $result['UserID'],
                'created' => $result['Created'],
            ]);
        } catch (Throwable $exception) {
            $code = (int)$exception->getCode();
            if ($code === self::ERROR_CONFLICT || $code === self::ERROR_INVALID) {
                $this->respondJson($code, ['ok' => false, 'message' => $exception->getMessage()]);
            }
            error_log('Telltale SSO provisioning: ' . $exception->getMessage());
            $this->respondJson(500, ['ok' => false, 'message' => 'The forum account could not be created.']);
        }
    }

    private function respondJson($status, array $data) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        http_response_code((int)$status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, private');
        echo json_encode($data);
        exit;
    }

    private function getTelltaleToken() {
        if (!isset($_COOKIE['telltale-token']) || !is_string($_COOKIE['telltale-token'])) {
            return '';
        }
        return trim($_COOKIE['telltale-token']);
    }

    /**
     * Clear only sessions previously established by this SSO plugin when the
     * website token has disappeared (for example, after account-page logout).
     */
    private function endManagedSessionWithoutTelltaleToken() {
        $session = Gdn::session();
        if ($session->isValid()
            && (string)$session->getPreference(self::SESSION_PREFERENCE, '') !== '') {
            $session->end();
        }
    }

    /**
     * Validate the bearer token against the website database, resolve or
     * provision its Vanilla account, and start the Vanilla session.
     */
    private function signInFromTelltaleToken($token) {
        $this->loadWebsiteIncludes();

        // This performs a SHA-256 token-hash lookup and checks the server-side
        // expiry. It does not trust the JWT claims supplied by the browser.
        $telltaleUser = tt_db_user_for_token($token);
        if (!is_array($telltaleUser)) {
            $this->endManagedSessionWithoutTelltaleToken();
            return;
        }

        $telltaleUserID = (int)(isset($telltaleUser['user_id']) ? $telltaleUser['user_id'] : 0);
        if ($telltaleUserID < 1) {
            throw new RuntimeException('The Telltale account has an invalid user ID.');
        }

        // The account page's community-username setting is the opt-in/provisioning
        // step. Do not invent a forum username from an email address.
        if (trim((string)(isset($telltaleUser['username']) ? $telltaleUser['username'] : '')) === '') {
            $this->endManagedSessionWithoutTelltaleToken();
            return;
        }

        $session = Gdn::session();

        // Fast path: this forum session already belongs to this Telltale account.
        $knownForumID = (int)(isset($telltaleUser['forum_id']) ? $telltaleUser['forum_id'] : 0);
        if ($knownForumID > 0
            && $session->isValid()
            && (int)$session->UserID === $knownForumID
            && (string)$session->getPreference(self::SESSION_PREFERENCE, '') === (string)$telltaleUserID) {
            return;
        }

        $forumUserID = $this->provisionForumUser($telltaleUser)['UserID'];

        if (!$session->isValid() || (int)$session->UserID !== (int)$forumUserID) {
            $session->start((int)$forumUserID, true, true);
            Gdn::userModel()->fireEvent('AfterSignIn');
        }
        if ($session->isValid()
            && (string)$session->getPreference(self::SESSION_PREFERENCE, '') !== (string)$telltaleUserID) {
            $session->setPreference(self::SESSION_PREFERENCE, (string)$telltaleUserID);
        }
    }

    /**
     * Find, adopt or create the Vanilla account for a Telltale account and make
     * sure the Telltale-ID -> Vanilla-ID link exists on both sides. Idempotent.
     *
     * @param array $telltaleUser The account record from the website database.
     * @return array ['UserID' => int, 'Created' => bool]
     * @throws RuntimeException With code ERROR_CONFLICT / ERROR_INVALID when the forum
     *                          can never accept this username or email.
     */
    private function provisionForumUser(array $telltaleUser) {
        $telltaleUserID = (int)(isset($telltaleUser['user_id']) ? $telltaleUser['user_id'] : 0);
        $username = trim((string)(isset($telltaleUser['username']) ? $telltaleUser['username'] : ''));
        $email = trim((string)(isset($telltaleUser['email']) ? $telltaleUser['email'] : ''));

        if ($telltaleUserID < 1 || $username === '') {
            throw new RuntimeException('The Telltale account is missing its ID or community username.', self::ERROR_INVALID);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('The Telltale account does not have a valid email address.', self::ERROR_INVALID);
        }

        // Serialize provisioning for one account (API call and a page load can race).
        $pdo = Gdn::database()->connection();
        $lockName = 'telltale_forum_provision_' . $telltaleUserID;
        $locked = false;
        try {
            $lock = $pdo->prepare('SELECT GET_LOCK(:name, 15)');
            $lock->execute([':name' => $lockName]);
            $locked = (int)$lock->fetchColumn() === 1;
        } catch (Throwable $exception) {
            // Locking is best-effort; the uniqueness checks below still protect us.
        }

        try {
            $result = $this->findOrCreateForumUser($telltaleUserID, $username, $email, $telltaleUser);
        } finally {
            if ($locked) {
                try {
                    $unlock = $pdo->prepare('SELECT RELEASE_LOCK(:name)');
                    $unlock->execute([':name' => $lockName]);
                } catch (Throwable $exception) {
                    // Nothing useful to do; the lock is released when the connection closes.
                }
            }
        }

        // Keep the website's account record pointing at the real Vanilla UserID.
        // Failing to write this must never block the forum account or sign-in.
        if ((int)(isset($telltaleUser['forum_id']) ? $telltaleUser['forum_id'] : 0) !== $result['UserID']) {
            try {
                tt_db_link_forum_account($telltaleUserID, $result['UserID']);
            } catch (Throwable $exception) {
                error_log('Telltale SSO could not store the forum ID on the website account: ' . $exception->getMessage());
            }
        }

        return $result;
    }

    private function findOrCreateForumUser($telltaleUserID, $username, $email, array $telltaleUser) {
        $userModel = Gdn::userModel();
        $uniqueID = (string)$telltaleUserID;

        // is it already linked?
        $authentication = $userModel->getAuthentication($uniqueID, self::PROVIDER_KEY);
        $linkedID = (int)(is_array($authentication) && isset($authentication['UserID']) ? $authentication['UserID'] : 0);
        if ($linkedID > 0 && $userModel->getID($linkedID, DATASET_TYPE_ARRAY)) {
            return ['UserID' => $linkedID, 'Created' => false];
        }

        // Older website revisions wrote a placeholder forum_id. Only reuse it if the Vanilla account exists and both username and email match.
        $adoptID = 0;
        $legacyForumID = (int)(isset($telltaleUser['forum_id']) ? $telltaleUser['forum_id'] : 0);
        if ($legacyForumID > 0) {
            $legacy = $userModel->getID($legacyForumID, DATASET_TYPE_ARRAY);
            if ($legacy
                && strcasecmp((string)$legacy['Name'], $username) === 0
                && strcasecmp((string)$legacy['Email'], $email) === 0) {
                $adoptID = $legacyForumID;
            }
        }

        $created = false;
        if ($adoptID === 0) {
            // Refuse politely instead of failing deep inside the model.
            if ($userModel->getByUsername($username)) {
                throw new RuntimeException('That username is already in use.', self::ERROR_CONFLICT);
            }
            if ($userModel->getByEmail($email)) {
                throw new RuntimeException(
                    'The email address on this account is already used by another community account.',
                    self::ERROR_CONFLICT
                );
            }

            // Create a forum-only account with a random password. Authentication is exclusively through the Telltale account.
            $adoptID = (int)$userModel->save([
                'Name' => $username,
                'Email' => $email,
                'Password' => betterRandomString(48, 'Aa0!'),
                'HashMethod' => 'Random',
            ], [
                'NoConfirmEmail' => true,
                'ValidateName' => false,
                'FixUnique' => false,
            ]);

            if ($adoptID < 1) {
                $parts = [];
                $errors = $userModel->validationResults();
                if (is_array($errors)) {
                    foreach ($errors as $field => $messages) {
                        foreach ((array)$messages as $message) {
                            $parts[] = (string)$message;
                        }
                    }
                }
                error_log('Telltale SSO: Vanilla refused the account: ' . implode('; ', $parts));
                throw new RuntimeException(
                    'That username can\'t be used in the community. Please choose a different one.',
                    self::ERROR_INVALID
                );
            }
            $created = true;
        }

        // Persist the stable Telltale-ID -> Vanilla-ID association in Vanilla.
        $userModel->saveAuthentication([
            'UserID' => $adoptID,
            'Provider' => self::PROVIDER_KEY,
            'UniqueID' => $uniqueID,
        ]);

        return ['UserID' => $adoptID, 'Created' => $created];
    }

    /**
     * Load the website's account database helpers and bridge. The Vanilla
     * installation is /community; the Telltale website root is its parent.
     */
    private function loadWebsiteIncludes() {
        if (function_exists('tt_db_user_for_token') && function_exists('tt_forum_bridge_verify')) {
            return;
        }

        if (defined('PATH_ROOT')) {
            $websiteRoot = dirname(rtrim(PATH_ROOT, '/\\'));
        } else {
            $websiteRoot = dirname(__DIR__, 3);
        }
        $includes = $websiteRoot . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;

        foreach (['database.php', 'forum-bridge.php'] as $file) {
            if (!is_file($includes . $file)) {
                throw new RuntimeException('The website include ' . $file . ' was not found.');
            }
            require_once $includes . $file;
        }
        if (!function_exists('tt_db_user_for_token') || !function_exists('tt_db_user_by_id')) {
            throw new RuntimeException('The website account helpers are unavailable.');
        }
    }
}
