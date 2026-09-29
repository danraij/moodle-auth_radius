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
 * Admin settings and defaults for the RADIUS authentication plugin.
 *
 * @package auth_radius
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    if (!extension_loaded('radius')) {
        $settings->add(new admin_setting_heading('auth_radius/extensionwarning', '',
                get_string('auth_radius_noextension', 'auth_radius')));
    }

    $settings->add(new admin_setting_configtext('auth_radius/host',
            get_string('auth_radiushost_key', 'auth_radius'),
            get_string('auth_radiushost', 'auth_radius'), '127.0.0.1', PARAM_RAW_TRIMMED));

    $settings->add(new admin_setting_configtext('auth_radius/nasport',
            get_string('auth_radiusnasport_key', 'auth_radius'),
            get_string('auth_radiusnasport', 'auth_radius'), '1812', PARAM_INT));

    $radiustypes = [
        'PAP' => get_string('auth_radiustypepap', 'auth_radius'),
        'CHAP_MD5' => get_string('auth_radiustypechapmd5', 'auth_radius'),
        'MSCHAPv1' => get_string('auth_radiustypemschapv1', 'auth_radius'),
        'MSCHAPv2' => get_string('auth_radiustypemschapv2', 'auth_radius'),
    ];
    $settings->add(new admin_setting_configselect('auth_radius/radiustype',
            get_string('auth_radiustype_key', 'auth_radius'),
            get_string('auth_radiustype', 'auth_radius'), 'PAP', $radiustypes));

    $settings->add(new admin_setting_configpasswordunmask('auth_radius/secret',
            get_string('auth_radiussecret_key', 'auth_radius'),
            get_string('auth_radiussecret', 'auth_radius'), ''));

    $settings->add(new admin_setting_configtext('auth_radius/changepasswordurl',
            get_string('auth_radiuschangepasswordurl_key', 'auth_radius'),
            get_string('changepasswordhelp', 'auth'), '', PARAM_URL));
}