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
 * Upgrade steps for the RADIUS authentication plugin.
 *
 * @package auth_radius
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Migrate configuration from the deprecated config form component name.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_auth_radius_upgrade($oldversion) {
    if ($oldversion < 2026092900) {
        $legacyconfig = get_config('auth/radius');
        foreach (['host', 'nasport', 'radiustype', 'secret', 'changepasswordurl'] as $name) {
            if (isset($legacyconfig->{$name})) {
                if (get_config('auth_radius', $name) === false) {
                    set_config($name, $legacyconfig->{$name}, 'auth_radius');
                }
                unset_config($name, 'auth/radius');
            }
        }

        upgrade_plugin_savepoint(true, 2026092900, 'auth', 'radius');
    }

    return true;
}