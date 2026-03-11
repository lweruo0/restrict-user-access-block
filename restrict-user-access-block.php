<?php

/**
 * Plugin Name:       restrict-user-access-block
 * Description:       Extends the restricted-user-access plugin to include the ability to show or hide blocks. [update_rua_levels]
 * Version:           0.6.0
 * Author:            Oliver Ruoss
 * Author URI:        https://gitlab.com/oruoss/restrict-user-access-block
 * Plugin URI:        https://gitlab.com/oruoss/restrict-user-access-block
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       crafted-style-helpers
 *
 * @package           Crafted
 */

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/block-editor/how-to-guides/block-tutorial/writing-your-first-block-type/
 */
function restrict_user_access_block_init()
{
    register_block_type(__DIR__ . '/build');
}

add_action('init', 'restrict_user_access_block_init');

/*
 * update Restrict User Access levels
 *
 * @return array (Vorname, Nachname, Level1, ...)
 */
function update_rua_levels(): array
{
    $returnvalue = [];
    if (class_exists('RUA_App')) {
        $rua_app = \RUA_App::instance();
        $all_levels = $rua_app->get_levels();
        $active_levels_by_name = [];
        foreach ($all_levels as $id => $level) {
            if ($level->post_status == \RUA_App::STATUS_ACTIVE) {
                $active_levels_by_name[$level->post_name] = $id;
            }
        }

        // [logged_in_level] => 4117
        // [gast_level] => 4155
        // [tagesgast_level] => 4155
        // [mitglied_level] => 4119
        // [haegele] => 5853
        // [aktiv_level] => 7601
        // [passiv_level] => 7674
        // [foerder_level] => 7603
        // [jugend_level] => 7603
        // [vorstandschaft_level] => 4126
        // [admin_level] => 4120

        $logged_in_level = $active_levels_by_name['logged_in_level'];
        $gast_level = $active_levels_by_name['gast_level'];
        $tagesgast_level = $active_levels_by_name['tagesgast_level'];
        $mitglied_level = $active_levels_by_name['mitglied_level'];
        $aktiv_level = $active_levels_by_name['aktiv_level'];
        $passiv_level = $active_levels_by_name['passiv_level'];
        $jugend_level = $active_levels_by_name['jugend_level'];
        $jugend_leiter_level = $active_levels_by_name['jugend_leiter_level'];
        $vorstandschaft_level = $active_levels_by_name['vorstandschaft_level'];
        $admin_level = $active_levels_by_name['admin_level'];
        $foerder_level = $active_levels_by_name['foerder_level'];

        /*
         * Administrator levels
         */
        $user_query = new WP_User_Query([
            'role' => 'administrator',
        ]);
        $user_query_results = $user_query->get_results();
        error_log(print_r(count($user_query_results), true));
        if (! empty($user_query_results)) {
            if (function_exists('rua_get_user')) {
                foreach ($user_query_results as $user) {
                    rua_get_user($user)->add_level($logged_in_level);
                    rua_get_user($user)->add_level($gast_level);
                    rua_get_user($user)->add_level($tagesgast_level);
                    rua_get_user($user)->add_level($mitglied_level);
                    rua_get_user($user)->add_level($aktiv_level);
                    rua_get_user($user)->add_level($passiv_level);
                    rua_get_user($user)->add_level($jugend_level);
                    rua_get_user($user)->add_level($jugend_leiter_level);
                    rua_get_user($user)->add_level($foerder_level);
                    rua_get_user($user)->add_level($vorstandschaft_level);
                    rua_get_user($user)->add_level($admin_level);
                }
            }
        }

        /*
         * Vorstandschaft levels
         */
        $user_query = new WP_User_Query([
            'role' => 'editor',
        ]);
        $user_query_results = $user_query->get_results();
        error_log(print_r(count($user_query_results), true));
        if (! empty($user_query_results)) {
            if (function_exists('rua_get_user')) {
                foreach ($user_query_results as $user) {
                    rua_get_user($user)->add_level($logged_in_level);
                    rua_get_user($user)->remove_level($gast_level); /* remove */
                    rua_get_user($user)->remove_level($tagesgast_level); /* remove */
                    rua_get_user($user)->add_level($mitglied_level);
                    rua_get_user($user)->add_level($aktiv_level);
                    rua_get_user($user)->remove_level($passiv_level); /* remove */
                    rua_get_user($user)->remove_level($jugend_level); /* remove */
                    rua_get_user($user)->remove_level($foerder_level); /* remove */
                    rua_get_user($user)->add_level($vorstandschaft_level);
                    rua_get_user($user)->remove_level($admin_level); /* remove */
                }
            }
        }

        /*
         * Mitglied levels
         */
        $user_query = new WP_User_Query([
            'role' => 'subscriber',
        ]);
        $user_query_results = $user_query->get_results();
        error_log(print_r(count($user_query_results), true));
        if (! empty($user_query_results)) {
            foreach ($user_query_results as $user) {
                if (function_exists('rua_get_user')) {
                    rua_get_user($user)->add_level($logged_in_level);
                    rua_get_user($user)->remove_level($gast_level); /* remove */
                    rua_get_user($user)->remove_level($tagesgast_level); /* remove */
                    rua_get_user($user)->add_level($mitglied_level);
                
                    $mitgliedschaft = strtolower(get_user_meta($user->ID, $key = 'Mitgliedschaft', true));
                    switch ($mitgliedschaft) {
                        case "aktiv":
                        case "ehrenmitglied":
                            rua_get_user($user)->add_level($aktiv_level);
                            rua_get_user($user)->remove_level($passiv_level); /* remove */
                            rua_get_user($user)->remove_level($jugend_level); /* remove */
                            rua_get_user($user)->remove_level($foerder_level); /* remove */
                            break;
                        case "passiv":
                            rua_get_user($user)->remove_level($aktiv_level); /* remove */
                            rua_get_user($user)->add_level($passiv_level);
                            rua_get_user($user)->remove_level($jugend_level); /* remove */
                            rua_get_user($user)->remove_level($foerder_level); /* remove */
                            break;
                        case "jugend":
                            rua_get_user($user)->remove_level($aktiv_level); /* remove */
                            rua_get_user($user)->remove_level($passiv_level); /* remove */
                            rua_get_user($user)->add_level($jugend_level);
                            rua_get_user($user)->remove_level($foerder_level); /* remove */
                            break;
                        case "förder":
                            rua_get_user($user)->remove_level($aktiv_level); /* remove */
                            rua_get_user($user)->remove_level($passiv_level); /* remove */
                            rua_get_user($user)->remove_level($jugend_level); /* remove */
                            rua_get_user($user)->add_level($foerder_level);
                            break;
                        default:
                            rua_get_user($user)->remove_level($aktiv_level); /* remove */
                            rua_get_user($user)->remove_level($passiv_level); /* remove */
                            rua_get_user($user)->remove_level($jugend_level); /* remove */
                            rua_get_user($user)->remove_level($foerder_level); /* remove */
                    }
                    rua_get_user($user)->remove_level($vorstandschaft_level); /* remove */
                    rua_get_user($user)->remove_level($admin_level); /* remove */
                }
            }
        }

        /*
         * Gast levels
         */
        $user_query = new WP_User_Query([
            'role' => 'gast',
        ]);
        $user_query_results = $user_query->get_results();
        error_log(print_r(count($user_query_results), true));
        if (! empty($user_query_results)) {
            if (function_exists('rua_get_user')) {
                foreach ($user_query_results as $user) {
                    rua_get_user($user)->add_level($logged_in_level);
                    rua_get_user($user)->add_level($gast_level);
                    rua_get_user($user)->remove_level($tagesgast_level); /* remove */
                    rua_get_user($user)->remove_level($mitglied_level); /* remove */
                    rua_get_user($user)->remove_level($aktiv_level); /* remove */
                    rua_get_user($user)->remove_level($passiv_level); /* remove */
                    rua_get_user($user)->remove_level($jugend_level); /* remove */
                    rua_get_user($user)->remove_level($vorstandschaft_level); /* remove */
                    rua_get_user($user)->remove_level($admin_level); /* remove */
                }
            }
        }

        /*
         * Tagesgast levels
         */
        $user_query = new WP_User_Query([
            'role' => 'tagesgast',
        ]);
        $user_query_results = $user_query->get_results();
        error_log(print_r(count($user_query_results), true));
        if (! empty($user_query_results)) {
            if (function_exists('rua_get_user')) {
                foreach ($user_query_results as $user) {
                    rua_get_user($user)->add_level($logged_in_level);
                    rua_get_user($user)->remove_level($gast_level); /* remove */
                    rua_get_user($user)->add_level($tagesgast_level);
                    rua_get_user($user)->remove_level($mitglied_level); /* remove */
                    rua_get_user($user)->remove_level($aktiv_level); /* remove */
                    rua_get_user($user)->remove_level($passiv_level); /* remove */
                    rua_get_user($user)->remove_level($jugend_level); /* remove */
                    rua_get_user($user)->remove_level($vorstandschaft_level); /* remove */
                    rua_get_user($user)->remove_level($admin_level); /* remove */
                }
            }
        }
    }
    return $returnvalue;
}

/**
 * shortcode for updating Restrict User Access levels.
 *
 * @since 0.1.0
 *
 * @return string The content of the shortcode
 */
function update_rua_levels_shortcode(): string
{
    $result = update_rua_levels();
    $returnvalue = "<h3> Update Restrict User Access Levels of all Users <h3>";
    $returnvalue .= "<p>Hier wäre eine datatable mit allen benutzern noch cool (TODO)</p>";
    return $returnvalue;
}

// register shortcode
add_shortcode('update_rua_levels', 'update_rua_levels_shortcode');


/**
 * traegt in die Block settings die verfuegbaren aktiven rua levels ein.
 *
 */
function restrict_user_access_block_settings($editor_settings, $editor_context)
{
    if (! empty($editor_context->post)) {
        if (class_exists('RUA_App')) {
            $rua_app = \RUA_App::instance();
            $all_levels = $rua_app->get_levels();
            $active_levels_by_name = [];
            foreach ($all_levels as $id => $level) {
                if ($level->post_status == RUA_App::STATUS_ACTIVE) {
                    $active_levels_by_name[$level->post_name] = $id;
                }
            }
            $editor_settings['active_rua_levels'] = $active_levels_by_name;
        }
    }
    return $editor_settings;
}
add_filter('block_editor_settings_all', 'restrict_user_access_block_settings', 10, 2);




    /**
     * @var bool $has_access
     * @var \RUA_User_Interface $user
     * @var array $a
     */
    $has_access = apply_filters('rua/shortcode/restrict', $has_access, $user, $a);

    if (!$has_access) {
        $content = '';

        // Only apply the page content if it exists
        $page = $a['page'] ? get_post($a['page']) : null;
        if ($page) {
            setup_postdata($page);
            $content = get_the_content();
            wp_reset_postdata();
        }
    }

    return do_shortcode($content);
}


function check_access($atts)
{
    if (function_exists('rua_get_user')) {
        $user = rua_get_user();
        if ($user->has_global_access()) {
            return true;
        }
    }
    $a = shortcode_atts([
        'role'      => '',
        'level'     => '',
        'page'      => 0,
        'drip_days' => 0,
    ], $atts, 'restrict');

    $has_access = false;
    if (!class_exists('RUA_App')) {
        return true;
    }
    $legacy_app = \RUA_App::instance();

    if ($a['level'] !== '') {
        $has_negation = strpos($a['level'], '!') !== false;
        $user_levels = array_flip($user->get_level_ids());
        if (!empty($user_levels) || $has_negation) {
            $level_names = explode(',', str_replace(' ', '', $a['level']));
            $not_found = 0;
            foreach ($level_names as $level_name) {
                $level = $legacy_app->level_manager->get_level_by_name(ltrim($level_name, '!'));
                if (!$level) {
                    $not_found++;
                    continue;
                }
                //if level param is negated, give access only if user does not have it
                if ($level->post_name != $level_name) {
                    $has_access = !isset($user_levels[$level->ID]);
                } elseif (isset($user_levels[$level->ID])) {
                    $drip = (int) $a['drip_days'];
                    if ($drip > 0 && $user->has_level($level->ID)) {
                        //@todo if extended level drips content, use start date
                        //of level user is member of
                        $start = $user->level_memberships()->get($level->ID)->get_start();
                        if ($start > 0) {
                            $drip_time = strtotime('+' . $drip . ' days 00:00', $start);
                            $should_drip = apply_filters(
                                'rua/auth/content-drip',
                                time() <= $drip_time,
                                $user,
                                $level->ID,
                            );
                            if ($should_drip) {
                                continue;
                            }
                        }
                    }
                    $has_access = true;
                }
                if ($has_access) {
                    break;
                }
            }
            //if levels do not exist, make content visible
            if (!$has_access && $not_found && $not_found === count($level_names)) {
                $has_access = true;
            }
        }
    } elseif ($a['role'] !== '') {
        $user_roles = array_flip(wp_get_current_user()->roles);
        if (!empty($user_roles)) {
            $roles = explode(',', str_replace(' ', '', $a['role']));
            foreach ($roles as $role_name) {
                $role = ltrim($role_name, '!');
                $not = $role != $role_name;
                //when role is negated, give access if user does not have it
                //otherwise give access only if user has it
                if ($not xor isset($user_roles[$role])) {
                    $has_access = true;
                    break;
                }
            }
        }
    }

    /**
     * @var bool $has_access
     * @var \RUA_User_Interface $user
     * @var array $a
     */
    $has_access = apply_filters('rua/shortcode/restrict', $has_access, $user, $a);

    return $has_access;
}


/**
 * Filters the content of a single block.
 *
 * @param string $block_content
 *            The block content about to be appended.
 * @param array $block
 *            The full block, including name and attributes.
 *
 * @since 0.1.0
 *
 * @return string The block content about to be appended.
 */
function restrict_user_access_block_render_block_filter($block_content, $block)
{

    // If we are in the admin interface, bail.
    if (is_admin()) {
        return $block_content;
    }
    // make array of the levels:
    if (isset($block['attrs']) && isset($block['attrs']['ruaLevelsBlock2'])) {
        $levelArray = $block['attrs']['ruaLevelsBlock2'];
    } else {
        $levelArray = [];
    }
    // only check if levels are given
    if (class_exists('RUA_App')) {
        if (! empty($levelArray)) {
            $rua_app = \RUA_App::instance();
            foreach ($levelArray as $level) {
                $atts = [
                    "level" => $level,
                ];
                // we call the given shortcodefunction to check
                $has_access = check_access($atts);
                if ($has_access) {
                    return $block_content;
                }
            }
            return "";
        }
    }
    return $block_content;
}

if (! class_exists('RUA_App')) {
    add_filter('render_block', 'restrict_user_access_block_render_block_filter', 0, 2);
}
