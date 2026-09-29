<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Authentication Plugin: RADIUS Authentication
 *
 * Authenticates against a RADIUS server.
 * Contributed by Clive Gould <clive@ce.bromley.ac.uk>
 * CHAP support contributed by Stanislav Tsymbalov http://www.tsymbalov.net/
 *
 * @package auth_radius
 * @author Martin Dougiamas
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');

/**
 * RADIUS authentication plugin.
 */
class auth_plugin_radius extends auth_plugin_base {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'radius';
        $this->config = get_config('auth_radius');
        if (!is_object($this->config)) {
            $this->config = new stdClass();
        }
        if (empty($this->config->host)) {
            $this->config->host = '127.0.0.1';
        }
        if (empty($this->config->nasport)) {
            $this->config->nasport = '1812';
        }
        if (empty($this->config->radiustype)) {
            $this->config->radiustype = 'PAP';
        }
        if (!isset($this->config->secret)) {
            $this->config->secret = '';
        }
        if (!isset($this->config->changepasswordurl)) {
            $this->config->changepasswordurl = '';
        }
    }

    /**
     * Old syntax of class constructor. Deprecated in PHP7.
     *
     * @deprecated since Moodle 3.1
     */
    public function auth_plugin_radius() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist.
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool Authentication success or failure.
     */
    public function user_login($username, $password) {
        require_once(__DIR__ . '/lib/Auth/RADIUS.php');
        require_once(__DIR__ . '/lib/Crypt/CHAP.php');

        if ($username === '' || $password === '') {
            return false;
        }

        $type = $this->config->radiustype ?? 'PAP';
        $supportedtypes = ['PAP', 'CHAP_MD5', 'MSCHAPv1', 'MSCHAPv2'];
        if (!in_array($type, $supportedtypes, true)) {
            return false;
        }

        try {
            $classname = 'Auth_RADIUS_' . $type;
            $rauth = new $classname($username, $password);
            $rauth->addServer($this->config->host, $this->config->nasport, $this->config->secret);

            $rauth->username = $username;

            switch ($type) {
                case 'CHAP_MD5':
                case 'MSCHAPv1':
                    $classname = $type == 'MSCHAPv1' ? 'Crypt_CHAP_MSv1' : 'Crypt_CHAP_MD5';
                    $crypt = new $classname();
                    $crypt->password = $password;
                    $rauth->challenge = $crypt->challenge;
                    $rauth->chapid = $crypt->chapid;
                    $rauth->response = $crypt->challengeResponse();
                    $rauth->flags = 1;
                    break;

                case 'MSCHAPv2':
                    $crypt = new Crypt_CHAP_MSv2();
                    $crypt->username = $username;
                    $crypt->password = $password;
                    $rauth->challenge = $crypt->authChallenge;
                    $rauth->peerChallenge = $crypt->peerChallenge;
                    $rauth->chapid = $crypt->chapid;
                    $rauth->response = $crypt->challengeResponse();
                    break;

                default:
                    $rauth->password = $password;
                    break;
            }

            return $rauth->start() && $rauth->send() === true;
        } catch (Throwable $exception) {
            return false;
        } finally {
            if (isset($rauth)) {
                $rauth->close();
            }
        }
    }

    public function prevent_local_passwords() {
        return true;
    }

    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    public function is_internal() {
        return false;
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    public function can_change_password() {
        return !empty($this->config->changepasswordurl);
    }

    /**
     * Returns the URL for changing the user's password.
     */
    public function change_password_url() {
        if (empty($this->config->changepasswordurl)) {
            return null;
        }

        return new moodle_url($this->config->changepasswordurl);
    }

}


