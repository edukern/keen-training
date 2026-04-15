<?php
/**
 * Plugin Name: Keen Training
 * Description: Plataforma de onboarding e treinamento corporativo. Gerencie colaboradores, cursos, avaliações, progresso e certificados por unidade.
 * Version:     1.0.0
 * Author:      Keenfisher
 * Text Domain: keen-training
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'KT_VERSION',    '1.0.0' );
define( 'KT_PLUGIN_FILE', __FILE__ );
define( 'KT_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'KT_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );

// Atualizações automáticas via GitHub Releases
require_once KT_PLUGIN_DIR . 'includes/plugin-update-checker/plugin-update-checker.php';
$kt_updater = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
	'https://github.com/edukern/keen-training/',
	KT_PLUGIN_FILE,
	'keen-training'
);
$kt_updater->setBranch( 'main' );
$kt_updater->getVcsApi()->enableReleaseAssets();

// Autoload de classes
spl_autoload_register( function ( $class ) {
	$map = [
		'KT_Installer'   => KT_PLUGIN_DIR . 'includes/class-installer.php',
		'KT_Roles'       => KT_PLUGIN_DIR . 'includes/class-roles.php',
		'KT_Location'    => KT_PLUGIN_DIR . 'includes/class-location.php',
		'KT_Position'    => KT_PLUGIN_DIR . 'includes/class-position.php',
		'KT_Member'      => KT_PLUGIN_DIR . 'includes/class-member.php',
		'KT_Course'      => KT_PLUGIN_DIR . 'includes/class-course.php',
		'KT_Restriction' => KT_PLUGIN_DIR . 'includes/class-restriction.php',
		'KT_Quiz'        => KT_PLUGIN_DIR . 'includes/class-quiz.php',
		'KT_Progress'    => KT_PLUGIN_DIR . 'includes/class-progress.php',
		'KT_Certificate' => KT_PLUGIN_DIR . 'includes/class-certificate.php',
		'KT_Admin'       => KT_PLUGIN_DIR . 'admin/class-admin.php',
		'KT_Frontend'    => KT_PLUGIN_DIR . 'frontend/class-frontend.php',
	];
	if ( isset( $map[ $class ] ) ) {
		require_once $map[ $class ];
	}
} );

// Ícone na tela de plugins instalados
add_filter( 'plugin_row_meta', function( $links, $file ) {
	return $links;
}, 10, 2 );

add_action( 'admin_head', function() {
	$icon_url = KT_PLUGIN_URL . 'assets/icon.svg';
	echo '<style>
		#adminmenu #toplevel_page_kt-dashboard .wp-menu-image img { padding-top: 4px; width: 20px; height: 20px; }
	</style>';
} );

register_activation_hook( __FILE__, [ 'KT_Installer', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'KT_Installer', 'deactivate' ] );

function kt_init() {
	KT_Installer::maybe_upgrade();
	if ( is_admin() ) {
		new KT_Admin();
	}
	new KT_Frontend();
}
add_action( 'plugins_loaded', 'kt_init' );
