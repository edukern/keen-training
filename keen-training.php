<?php
/**
 * Plugin Name: Keen Training
 * Description: Plataforma de onboarding e treinamento corporativo. Gerencie colaboradores, cursos, avaliaÃ§Ãµes, progresso e certificados por unidade.
 * Version:     2.9.11
 * Author:      Keenfisher
 * Text Domain: keen-training
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'KT_VERSION',    '2.9.11' );
define( 'KT_PLUGIN_FILE', __FILE__ );
define( 'KT_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'KT_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );

// AtualizaÃ§Ãµes automÃ¡ticas via GitHub Releases
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
		'KT_Export'      => KT_PLUGIN_DIR . 'includes/class-export.php',
		'KT_Admin'         => KT_PLUGIN_DIR . 'admin/class-admin.php',
		'KT_Frontend'      => KT_PLUGIN_DIR . 'frontend/class-frontend.php',
		'KT_Notifications' => KT_PLUGIN_DIR . 'includes/class-notifications.php',
	];
	if ( isset( $map[ $class ] ) ) {
		require_once $map[ $class ];
	}
} );

// Ãcone na tela de plugins instalados
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
register_deactivation_hook( __FILE__, function() {
	KT_Installer::deactivate();
	// Remove o cron diÃ¡rio de aniversÃ¡rios ao desativar o plugin
	$ts = wp_next_scheduled( 'kt_birthday_digest_check' );
	if ( $ts ) wp_unschedule_event( $ts, 'kt_birthday_digest_check' );
} );

// Callback do cron (registrado antes do plugins_loaded para garantir disponibilidade)
add_action( 'kt_birthday_digest_check', [ 'KT_Notifications', 'maybe_send' ] );

function kt_init() {
	KT_Installer::maybe_upgrade();
	KT_Notifications::maybe_schedule();
	if ( is_admin() ) {
		new KT_Admin();
	}
	new KT_Frontend();
}
add_action( 'plugins_loaded', 'kt_init' );

// Comando WP-CLI: exporta todos os dados do LMS para um arquivo JSON.
// Uso: wp keen-training export [--file=<caminho>]
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'keen-training export', function ( $args, $assoc_args ) {
		$json = KT_Export::to_json( true );
		$file = $assoc_args['file'] ?? '';
		if ( $file ) {
			if ( false === file_put_contents( $file, $json ) ) {
				WP_CLI::error( "Não foi possível gravar em: $file" );
			}
			WP_CLI::success( 'Export gravado em ' . $file . ' (' . size_format( strlen( $json ) ) . ')' );
		} else {
			// Sem --file: imprime o JSON no stdout (pode redirecionar para arquivo)
			WP_CLI::line( $json );
		}
	} );
}
