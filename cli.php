<?php

if ( ! defined( 'PLUGINS_PATH_TC_CLI' ) ) 
    define( 'PLUGINS_PATH_TC_CLI', ABSPATH . 'wp-content/plugins/' );

function theme_check_active() {
    return in_array( 'theme-check/theme-check.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
}

function themeforest_check_active() {
    return in_array( 'themeforest-check/themeforest-check.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
}

// TODO: Themeforest flag support
if ( theme_check_active() ) {
    include PLUGINS_PATH_TC_CLI . 'theme-check/main.php';
    include PLUGINS_PATH_TC_CLI . 'theme-check/checkbase.php';
}

/**
 * Implements example command.
 */
class ThemeCheckCli extends WP_CLI_Command {

    function __construct() {
        parent::__construct();
        $this->fetcher = new \WP_CLI\Fetchers\Theme;
    }

    /**
     * Prints a greeting.
     * 
     * ## OPTIONS
     * 
     * <name>
     * : The name of the person to greet.
     * 
     * ## EXAMPLES
     * 
     *     wp theme-check check twentyfifteen
     *
     * @synopsis <name> [--disable-themeforest]
     */
    function check( $args, $assoc_args ) {
        global $checkcount, $themechecks;

        $theme = $this->fetcher->get_check( $args[0] );
        $files = $theme->get_files( null, -1 );
        $css = $php = $other = array();

        if ( themeforest_check_active() && $assoc_args['disable-themeforest'] == true ) {

        }

        if ( $files ) {
            foreach( $files as $key => $filename ) {
                if ( substr( $filename, -4 ) == '.php' ) {
                    $php[$filename] = php_strip_whitespace( $filename );
                }
                else if ( substr( $filename, -4 ) == '.css' ) {
                    $css[$filename] = file_get_contents( $filename );
                }
                else {
                    $other[$filename] = ( ! is_dir($filename) ) ? file_get_contents( $filename ) : '';
                }
            }

            // run the checks
            $success = run_themechecks($php, $css, $other);

            foreach ( $themechecks as $check ) {
                if ( $check instanceof themecheck ) {
                    $errors = $check->getError();

                    if ( ! empty( $error ) ) {
                        $errors = array_merge( $error, $errors );
                    }

                    $errors = array_map( 'strip_tags', $errors );

                    foreach ( $errors as $key => $value ) {
                        list( $type, $message ) = explode( ':', $value, 2 );

                        switch ( trim( $type ) ) {
                            case 'WARNING':
                                WP_CLI::line( WP_CLI::colorize( '%rWARNING:%n ' . trim( $message ) ) );
                            break;

                            case 'INFO':
                                WP_CLI::line( WP_CLI::colorize( '%bINFO:%n ' . trim( $message ) ) );
                            break;

                            case 'RECOMMENDED':
                                WP_CLI::line( WP_CLI::colorize( '%gRECOMMENDED:%n ' . trim( $message ) ) );
                            break;

                            case 'REQUIRED':
                                WP_CLI::line( WP_CLI::colorize( '%rREQUIRED:%n ' . trim( $message ) ) );
                            break;
                        }

                    }
                }
            }
        }
    }
}

WP_CLI::add_command( 'theme test', 'ThemeCheckCli' );
