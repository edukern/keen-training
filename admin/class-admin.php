<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KT_Admin {

	public function __construct() {
		add_action( 'admin_menu',            [ $this, 'register_menus' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'admin_notices',         [ $this, 'setup_notice' ] );

		// Handlers de formulário
		$actions = [
			'kt_save_color',
			'kt_save_location', 'kt_delete_location',
			'kt_save_position', 'kt_delete_position',
			'kt_save_member',   'kt_delete_member',
			'kt_save_course',   'kt_delete_course',
			'kt_save_module',   'kt_delete_module',
			'kt_save_quiz',     'kt_save_quiz_questions', 'kt_delete_quiz',
			'kt_save_enrollment', 'kt_delete_enrollment',
			'kt_export_progress', 'kt_dismiss_setup',
			'kt_import_members', 'kt_import_quiz_questions',
			'kt_reset_quiz_attempts',
		];
		foreach ( $actions as $action ) {
			add_action( 'admin_post_' . $action, [ $this, 'handle_' . $action ] );
		}
	}

	/* -----------------------------------------------------------------------
	 * Menus
	 * -------------------------------------------------------------------- */

	public function register_menus() {
		if ( ! KT_Roles::is_super_admin() && ! KT_Roles::is_location_manager() ) return;

		$icon_file = KT_PLUGIN_DIR . 'assets/icon.svg';
		$icon      = file_exists( $icon_file )
			? 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( $icon_file ) )
			: 'dashicons-welcome-learn-more';

		add_menu_page(
			'Keen Training',
			'Keen Training',
			'read',
			'kt-dashboard',
			[ $this, 'page_dashboard' ],
			$icon,
			30
		);

		if ( KT_Roles::is_super_admin() ) {
			add_submenu_page( 'kt-dashboard', 'Unidades',  'Unidades',  'read', 'kt-locations', [ $this, 'page_locations' ] );
			add_submenu_page( 'kt-dashboard', 'Funções',   'Funções',   'read', 'kt-positions', [ $this, 'page_positions' ] );
		}

		add_submenu_page( 'kt-dashboard', 'Colaboradores', 'Colaboradores', 'read', 'kt-members', [ $this, 'page_members' ] );

		if ( KT_Roles::is_super_admin() ) {
			add_submenu_page( 'kt-dashboard', 'Cursos',      'Cursos',       'read', 'kt-courses',     [ $this, 'page_courses' ] );
			add_submenu_page( 'kt-dashboard', 'Avaliações',  'Avaliações',   'read', 'kt-quizzes',     [ $this, 'page_quizzes' ] );
		}

		add_submenu_page( 'kt-dashboard', 'Matrículas',   'Matrículas',    'read', 'kt-enrollments',  [ $this, 'page_enrollments' ] );
		add_submenu_page( 'kt-dashboard', 'Progresso',    'Progresso',     'read', 'kt-progress',     [ $this, 'page_progress' ] );
		add_submenu_page( 'kt-dashboard', 'Certificados', 'Certificados',  'read', 'kt-certificates', [ $this, 'page_certificates' ] );
	}

	public function enqueue_assets( $hook ) {
		if ( strpos( $hook, 'kt-' ) === false && strpos( $hook, 'toplevel_page_kt' ) === false ) return;
		wp_enqueue_style( 'kt-admin', KT_PLUGIN_URL . 'assets/admin.css', [], KT_VERSION );
		wp_enqueue_script( 'kt-admin', KT_PLUGIN_URL . 'assets/admin.js', [ 'jquery' ], KT_VERSION, true );
		wp_localize_script( 'kt-admin', 'ktAdmin', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'kt_admin' ),
		] );
	}

	/* Aviso de configuração inicial */
	public function setup_notice() {
		if ( ! KT_Roles::is_super_admin() ) return;
		if ( ! get_option( 'kt_show_setup_wizard' ) ) return;
		$screen = get_current_screen();
		if ( $screen && strpos( $screen->id, 'kt-' ) !== false ) return;
		?>
		<div class="notice notice-info kt-setup-notice" style="padding:16px;display:flex;align-items:center;gap:20px">
			<span style="font-size:2em">🚀</span>
			<div>
				<strong>Keen Training foi instalado!</strong>
				Siga o assistente de configuração para começar em minutos.
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-dashboard' ) ); ?>" style="margin-left:12px" class="button button-primary">Ver guia de início →</a>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;margin-left:8px">
					<?php wp_nonce_field( 'kt_dismiss_setup' ); ?>
					<input type="hidden" name="action" value="kt_dismiss_setup">
					<button type="submit" class="button">Dispensar</button>
				</form>
			</div>
		</div>
		<?php
	}

	/* -----------------------------------------------------------------------
	 * Páginas do admin
	 * -------------------------------------------------------------------- */

	public function page_dashboard() {
		$location_id  = KT_Roles::is_super_admin() ? 0 : KT_Roles::current_user_location_id();
		$show_wizard  = (bool) get_option( 'kt_show_setup_wizard' );
		$stats = [
			'members'   => count( KT_Member::get_all( $location_id ) ),
			'courses'   => count( KT_Course::get_all() ),
			'locations' => count( KT_Location::get_all() ),
		];
		include KT_PLUGIN_DIR . 'admin/views/dashboard.php';
	}

	public function page_positions() {
		if ( ! KT_Roles::is_super_admin() ) wp_die( 'Acesso negado.' );
		$action   = sanitize_key( $_GET['action'] ?? 'list' );
		$position = ( $action !== 'list' && isset( $_GET['id'] ) ) ? KT_Position::get( absint( $_GET['id'] ) ) : null;
		include KT_PLUGIN_DIR . 'admin/views/positions.php';
	}

	public function page_locations() {
		if ( ! KT_Roles::is_super_admin() ) wp_die( 'Acesso negado.' );
		$action   = sanitize_key( $_GET['action'] ?? 'list' );
		$location = ( $action !== 'list' && isset( $_GET['id'] ) ) ? KT_Location::get( absint( $_GET['id'] ) ) : null;
		$managers = get_users( [ 'orderby' => 'display_name' ] );
		include KT_PLUGIN_DIR . 'admin/views/locations.php';
	}

	public function page_members() {
		$action      = sanitize_key( $_GET['action'] ?? 'list' );
		$member      = ( $action !== 'list' && isset( $_GET['id'] ) ) ? KT_Member::get( absint( $_GET['id'] ) ) : null;
		$locations   = KT_Location::get_all();
		$current_loc = KT_Roles::is_super_admin() ? 0 : KT_Roles::current_user_location_id();
		include KT_PLUGIN_DIR . 'admin/views/members.php';
	}

	public function page_courses() {
		if ( ! KT_Roles::is_super_admin() ) wp_die( 'Acesso negado.' );
		$action  = sanitize_key( $_GET['action'] ?? 'list' );
		$course  = null;
		$modules = [];
		if ( $action !== 'list' && isset( $_GET['id'] ) ) {
			$course  = KT_Course::get( absint( $_GET['id'] ) );
			$modules = $course ? KT_Course::get_modules( $course->id ) : [];
		}
		$locations = KT_Location::get_all();
		include KT_PLUGIN_DIR . 'admin/views/courses.php';
	}

	public function page_quizzes() {
		if ( ! KT_Roles::is_super_admin() ) wp_die( 'Acesso negado.' );
		$action    = sanitize_key( $_GET['action'] ?? 'list' );
		$quiz      = null;
		$questions = [];
		if ( $action !== 'list' && isset( $_GET['id'] ) ) {
			$quiz      = KT_Quiz::get( absint( $_GET['id'] ) );
			$questions = $quiz ? KT_Quiz::get_questions( $quiz->id ) : [];
		}
		$courses = KT_Course::get_all();
		include KT_PLUGIN_DIR . 'admin/views/quizzes.php';
	}

	public function page_enrollments() {
		$location_id = KT_Roles::is_super_admin() ? 0 : KT_Roles::current_user_location_id();
		$members     = KT_Member::get_all( $location_id );
		$courses     = KT_Course::get_all();
		$locations   = KT_Location::get_all();
		include KT_PLUGIN_DIR . 'admin/views/enrollments.php';
	}

	public function page_progress() {
		$location_id = KT_Roles::is_super_admin() ? absint( $_GET['location_id'] ?? 0 ) : KT_Roles::current_user_location_id();
		$course_id   = absint( $_GET['course_id'] ?? 0 );
		$locations   = KT_Location::get_all();
		$courses     = KT_Course::get_all();
		$rows        = [];
		if ( $course_id ) {
			$rows = KT_Progress::course_stats( $course_id );
		} elseif ( $location_id ) {
			$rows = KT_Progress::location_stats( $location_id );
		}
		include KT_PLUGIN_DIR . 'admin/views/progress.php';
	}

	public function page_certificates() {
		global $wpdb;
		$location_id = KT_Roles::is_super_admin() ? 0 : KT_Roles::current_user_location_id();
		$where       = $location_id ? $wpdb->prepare( 'AND m.location_id = %d', $location_id ) : '';
		$certs       = $wpdb->get_results(
			"SELECT cert.*, u.display_name, c.title AS course_title, l.name AS location_name
			 FROM {$wpdb->prefix}kt_certificates cert
			 JOIN {$wpdb->prefix}kt_members m ON m.id = cert.member_id
			 JOIN {$wpdb->users} u ON u.ID = m.user_id
			 JOIN {$wpdb->prefix}kt_courses c ON c.id = cert.course_id
			 LEFT JOIN {$wpdb->prefix}kt_locations l ON l.id = m.location_id
			 WHERE 1=1 $where
			 ORDER BY cert.issued_at DESC"
		);
		include KT_PLUGIN_DIR . 'admin/views/certificates.php';
	}

	/* -----------------------------------------------------------------------
	 * Handlers
	 * -------------------------------------------------------------------- */

	private function verify( $action ) { check_admin_referer( $action ); }

	public function handle_kt_dismiss_setup() {
		check_admin_referer( 'kt_dismiss_setup' );
		delete_option( 'kt_show_setup_wizard' );
		wp_redirect( wp_get_referer() ?: admin_url() );
		exit;
	}

	public function handle_kt_save_position() {
		check_admin_referer( 'kt_position' );
		if ( ! KT_Roles::is_super_admin() ) wp_die( 'Acesso negado.' );
		$id = absint( $_POST['position_id'] ?? 0 );
		if ( $id ) {
			KT_Position::update( $id, $_POST );
		} else {
			$result = KT_Position::create( $_POST );
			if ( is_wp_error( $result ) ) {
				wp_redirect( admin_url( 'admin.php?page=kt-positions&error=' . urlencode( $result->get_error_message() ) ) );
				exit;
			}
		}
		wp_redirect( admin_url( 'admin.php?page=kt-positions&saved=1' ) ); exit;
	}

	public function handle_kt_delete_position() {
		check_admin_referer( 'kt_delete_position' );
		if ( ! KT_Roles::is_super_admin() ) wp_die( 'Acesso negado.' );
		KT_Position::delete( absint( $_POST['position_id'] ) );
		wp_redirect( admin_url( 'admin.php?page=kt-positions&deleted=1' ) ); exit;
	}

	public function handle_kt_save_location() {
		$this->verify( 'kt_location' );
		if ( ! KT_Roles::is_super_admin() ) wp_die( 'Acesso negado.' );
		$id = absint( $_POST['location_id'] ?? 0 );
		$id ? KT_Location::update( $id, $_POST ) : KT_Location::create( $_POST );
		wp_redirect( admin_url( 'admin.php?page=kt-locations&saved=1' ) ); exit;
	}

	public function handle_kt_delete_location() {
		$this->verify( 'kt_delete_location' );
		if ( ! KT_Roles::is_super_admin() ) wp_die( 'Acesso negado.' );
		KT_Location::delete( absint( $_POST['location_id'] ) );
		wp_redirect( admin_url( 'admin.php?page=kt-locations&deleted=1' ) ); exit;
	}

	public function handle_kt_save_member() {
		$this->verify( 'kt_member' );
		$id = absint( $_POST['member_id'] ?? 0 );
		if ( $id ) {
			$m = KT_Member::get( $id );
			if ( ! $m || ! KT_Roles::can_manage_location( $m->location_id ) ) wp_die( 'Acesso negado.' );
			KT_Member::update( $id, $_POST );
		} else {
			if ( ! KT_Roles::can_manage_location( absint( $_POST['location_id'] ?? 0 ) ) ) wp_die( 'Acesso negado.' );
			$result = KT_Member::create( $_POST );
			if ( is_wp_error( $result ) ) {
				wp_redirect( admin_url( 'admin.php?page=kt-members&error=' . urlencode( $result->get_error_message() ) ) ); exit;
			}
		}
		wp_redirect( admin_url( 'admin.php?page=kt-members&saved=1' ) ); exit;
	}

	public function handle_kt_delete_member() {
		$this->verify( 'kt_delete_member' );
		$id = absint( $_POST['member_id'] );
		$m  = KT_Member::get( $id );
		if ( ! $m || ! KT_Roles::can_manage_location( $m->location_id ) ) wp_die( 'Acesso negado.' );
		KT_Member::delete( $id );
		wp_redirect( admin_url( 'admin.php?page=kt-members&deleted=1' ) ); exit;
	}

	public function handle_kt_save_course() {
		$this->verify( 'kt_course' );
		if ( ! KT_Roles::is_super_admin() ) wp_die( 'Acesso negado.' );
		$id = absint( $_POST['course_id'] ?? 0 );
		$id ? KT_Course::update( $id, $_POST ) : ( $id = KT_Course::create( $_POST ) );

		// Salva restrições de acesso
		$loc_ids  = array_map( 'absint',       (array) ( $_POST['restrict_locations'] ?? [] ) );
		$roles    = array_map( 'sanitize_key', (array) ( $_POST['restrict_roles']     ?? [] ) );
		$pos_ids  = array_map( 'absint',       (array) ( $_POST['restrict_positions'] ?? [] ) );
		KT_Restriction::save( $id, $loc_ids, $roles, $pos_ids );

		wp_redirect( admin_url( 'admin.php?page=kt-courses&action=edit&id=' . $id . '&saved=1' ) ); exit;
	}

	public function handle_kt_delete_course() {
		$this->verify( 'kt_delete_course' );
		if ( ! KT_Roles::is_super_admin() ) wp_die( 'Acesso negado.' );
		KT_Course::delete( absint( $_POST['course_id'] ) );
		wp_redirect( admin_url( 'admin.php?page=kt-courses&deleted=1' ) ); exit;
	}

	public function handle_kt_save_module() {
		$this->verify( 'kt_module' );
		if ( ! KT_Roles::is_super_admin() ) wp_die( 'Acesso negado.' );
		$id        = absint( $_POST['module_id'] ?? 0 );
		$course_id = absint( $_POST['course_id'] );
		$id ? KT_Course::update_module( $id, $_POST ) : KT_Course::add_module( $_POST );
		wp_redirect( admin_url( 'admin.php?page=kt-courses&action=edit&id=' . $course_id . '&saved=1' ) ); exit;
	}

	public function handle_kt_delete_module() {
		$this->verify( 'kt_delete_module' );
		if ( ! KT_Roles::is_super_admin() ) wp_die( 'Acesso negado.' );
		$mod       = KT_Course::get_module( absint( $_POST['module_id'] ) );
		$course_id = $mod ? $mod->course_id : 0;
		KT_Course::delete_module( absint( $_POST['module_id'] ) );
		wp_redirect( admin_url( 'admin.php?page=kt-courses&action=edit&id=' . $course_id . '&deleted=1' ) ); exit;
	}

	public function handle_kt_save_quiz() {
		$this->verify( 'kt_quiz' );
		if ( ! KT_Roles::is_super_admin() ) wp_die( 'Acesso negado.' );
		$id = absint( $_POST['quiz_id'] ?? 0 );
		$id ? KT_Quiz::update( $id, $_POST ) : ( $id = KT_Quiz::create( $_POST ) );
		wp_redirect( admin_url( 'admin.php?page=kt-quizzes&action=questions&id=' . $id . '&saved=1' ) ); exit;
	}

	public function handle_kt_save_quiz_questions() {
		global $wpdb;
		$this->verify( 'kt_quiz_questions' );
		if ( ! KT_Roles::is_super_admin() ) wp_die( 'Acesso negado.' );
		$quiz_id   = absint( $_POST['quiz_id'] );
		$questions = $_POST['questions'] ?? [];

		// Apaga perguntas antigas e reinsere
		foreach ( KT_Quiz::get_questions( $quiz_id ) as $q ) {
			$wpdb->delete( $wpdb->prefix . 'kt_quiz_answers',   [ 'question_id' => $q->id ] );
			$wpdb->delete( $wpdb->prefix . 'kt_quiz_questions', [ 'id' => $q->id ] );
		}

		foreach ( $questions as $order => $q_data ) {
			if ( empty( $q_data['question_text'] ) ) continue;
			$q_id = KT_Quiz::add_question( [
				'quiz_id'       => $quiz_id,
				'question_text' => $q_data['question_text'],
				'question_type' => $q_data['question_type'] ?? 'multiple_choice',
				'sort_order'    => (int) $order,
			] );
			$answers = [];
			foreach ( $q_data['answers'] ?? [] as $a ) {
				if ( empty( $a['text'] ) ) continue;
				$answers[] = [ 'text' => $a['text'], 'is_correct' => ! empty( $a['is_correct'] ) ];
			}
			if ( $answers ) KT_Quiz::save_answers( $q_id, $answers );
		}

		wp_redirect( admin_url( 'admin.php?page=kt-quizzes&action=questions&id=' . $quiz_id . '&saved=1' ) ); exit;
	}

	public function handle_kt_delete_quiz() {
		$this->verify( 'kt_delete_quiz' );
		if ( ! KT_Roles::is_super_admin() ) wp_die( 'Acesso negado.' );
		KT_Quiz::delete( absint( $_POST['quiz_id'] ) );
		wp_redirect( admin_url( 'admin.php?page=kt-quizzes&deleted=1' ) ); exit;
	}

	public function handle_kt_save_enrollment() {
		$this->verify( 'kt_enrollment' );
		$course_id   = absint( $_POST['course_id'] );
		$due_date    = sanitize_text_field( $_POST['due_date'] ?? '' ) ?: null;
		$target_type = sanitize_key( $_POST['target_type'] ?? 'member' );

		if ( $target_type === 'location' ) {
			$loc_id = absint( $_POST['location_id'] );
			if ( ! KT_Roles::can_manage_location( $loc_id ) ) wp_die( 'Acesso negado.' );
			KT_Progress::enroll_location( $loc_id, $course_id, $due_date );
		} else {
			$member_ids = array_map( 'absint', (array) ( $_POST['member_ids'] ?? [] ) );
			foreach ( $member_ids as $mid ) {
				$m = KT_Member::get( $mid );
				if ( ! $m || ! KT_Roles::can_manage_location( $m->location_id ) ) continue;
				KT_Progress::enroll( [ $mid ], $course_id, $due_date );
			}
		}
		wp_redirect( admin_url( 'admin.php?page=kt-enrollments&saved=1' ) ); exit;
	}

	public function handle_kt_delete_enrollment() {
		$this->verify( 'kt_delete_enrollment' );
		$mid = absint( $_POST['member_id'] );
		$m   = KT_Member::get( $mid );
		if ( ! $m || ! KT_Roles::can_manage_location( $m->location_id ) ) wp_die( 'Acesso negado.' );
		KT_Progress::unenroll( $mid, absint( $_POST['course_id'] ) );
		wp_redirect( admin_url( 'admin.php?page=kt-enrollments&deleted=1' ) ); exit;
	}

	public function handle_kt_import_members() {
		check_admin_referer( 'kt_import_members' );

		if ( empty( $_FILES['csv_file']['tmp_name'] ) ) {
			wp_redirect( admin_url( 'admin.php?page=kt-members&import_error=' . urlencode( 'Nenhum arquivo enviado.' ) ) );
			exit;
		}

		$file        = $_FILES['csv_file']['tmp_name'];
		$default_loc = absint( $_POST['default_location_id'] ?? 0 );

		// Restrição: manager só pode importar para sua unidade
		if ( ! KT_Roles::is_super_admin() ) {
			$default_loc = KT_Roles::current_user_location_id();
		}

		$send_email       = ! empty( $_POST['send_welcome_email'] );
		$default_password = sanitize_text_field( $_POST['default_password'] ?? '' );
		$result           = KT_Member::import_csv( $file, $default_loc, $send_email, $default_password );

		$msg = "Importação concluída: {$result['created']} colaborador(es) criado(s), {$result['skipped']} já existia(m).";
		if ( $result['errors'] ) {
			$msg .= ' Avisos: ' . implode( ' | ', $result['errors'] );
		}

		wp_redirect( admin_url( 'admin.php?page=kt-members&import_done=' . urlencode( $msg ) ) );
		exit;
	}

	public function handle_kt_import_quiz_questions() {
		check_admin_referer( 'kt_import_quiz_questions' );
		if ( ! KT_Roles::is_super_admin() ) wp_die( 'Acesso negado.' );

		$quiz_id = absint( $_POST['quiz_id'] ?? 0 );
		if ( ! $quiz_id || empty( $_FILES['csv_file']['tmp_name'] ) ) {
			wp_redirect( admin_url( 'admin.php?page=kt-quizzes&action=questions&id=' . $quiz_id . '&import_error=1' ) );
			exit;
		}

		$result = KT_Quiz::import_questions_csv( $_FILES['csv_file']['tmp_name'], $quiz_id );

		$msg = "{$result['created']} pergunta(s) importada(s).";
		if ( $result['errors'] ) {
			$msg .= ' Avisos: ' . implode( ' | ', $result['errors'] );
		}

		wp_redirect( admin_url( 'admin.php?page=kt-quizzes&action=questions&id=' . $quiz_id . '&import_done=' . urlencode( $msg ) ) );
		exit;
	}

	public function handle_kt_reset_quiz_attempts() {
		$this->verify( 'kt_reset_quiz_attempts' );
		if ( ! KT_Roles::is_super_admin() && ! KT_Roles::is_location_manager() ) wp_die( 'Acesso negado.' );

		$member_id = absint( $_POST['member_id'] ?? 0 );
		$quiz_id   = absint( $_POST['quiz_id']   ?? 0 );
		$back_url  = wp_validate_redirect( $_POST['back_url'] ?? '', admin_url( 'admin.php?page=kt-progress' ) );

		if ( $member_id && $quiz_id ) {
			KT_Quiz::reset_attempts( $member_id, $quiz_id );
		}

		wp_redirect( add_query_arg( 'reset_done', 1, $back_url ) );
		exit;
	}

	public function handle_kt_save_color() {
		check_admin_referer( 'kt_save_color' );
		if ( ! KT_Roles::is_super_admin() ) wp_die( 'Acesso negado.' );
		$color = sanitize_hex_color( $_POST['kt_primary_color'] ?? '' );
		if ( $color ) update_option( 'kt_primary_color', $color );
		update_option( 'kt_font_heading', sanitize_text_field( $_POST['kt_font_heading'] ?? '' ) );
		update_option( 'kt_font_body',    sanitize_text_field( $_POST['kt_font_body']    ?? '' ) );
		wp_redirect( admin_url( 'admin.php?page=kt-dashboard&color_saved=1' ) );
		exit;
	}

	public function handle_kt_export_progress() {
		$this->verify( 'kt_export_progress' );
		$location_id = KT_Roles::is_super_admin() ? absint( $_POST['location_id'] ?? 0 ) : KT_Roles::current_user_location_id();
		$rows        = KT_Progress::export_csv_rows( $location_id );

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="progresso-treinamentos-' . date( 'Y-m-d' ) . '.csv"' );
		// BOM para Excel abrir corretamente em PT-BR
		echo "\xEF\xBB\xBF";
		$out = fopen( 'php://output', 'w' );
		if ( $rows ) fputcsv( $out, array_keys( $rows[0] ), ';' );
		foreach ( $rows as $row ) fputcsv( $out, array_values( $row ), ';' );
		fclose( $out );
		exit;
	}
}
