<?php

/**
 * A WP CLI Plugin for Theme Check and Themeforest Check for running tests from cli
 *
 * @package  Theme_Check_Cli
 * @author   johnakos <johnniewolker@hotmail.com>
 * @license  MIT
 *
 * Plugin Name: Theme Check CLI
 * Version: 1.0
 * Description: A CLI interface for the Theme Check and Themeforest Check plugin
 * Author: Johnakos
 * Plugin URI: https://github.com/johnakos/theme-check-cli
 * License: MIT
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

function theme_check_cli_init() {
    if ( ! class_exists( 'ThemeCheckMain' ) )
        return;

    if ( defined( 'WP_CLI' ) && WP_CLI ) {
        include __DIR__ . '/cli.php';
    }
}

add_action( 'plugins_loaded', 'theme_check_cli_init' );