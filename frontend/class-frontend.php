<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KT_Frontend {

	public function __construct() {
		add_shortcode( 'kt_portal',  [ $this, 'shortcode_portal' ] );
		add_shortcode( 'kt_modulo',  [ $this, 'shortcode_module_actions' ] );
		add_shortcode( 'kt_quiz',    [ $this, 'shortcode_quiz_embed' ] );
		add_action( 'wp_enqueue_scripts',         [ $this, 'enqueue_assets' ] );
		add_action( 'wp_ajax_kt_complete_module', [ $this, 'ajax_complete_module' ] );
		add_action( 'wp_ajax_kt_submit_quiz',     [ $this, 'ajax_submit_quiz' ] );
		add_action( 'template_redirect',          [ $this, 'maybe_render_certificate' ] );
		add_action( 'template_redirect',          [ $this, 'enforce_module_page_access' ] );
		add_filter( 'login_redirect',             [ $this, 'login_redirect' ], 10, 3 );
	}

	/**
	 * Redireciona colaboradores para o portal após o login.
	 * Admins e gestores seguem o fluxo padrão do WordPress.
	 */
	public function login_redirect( $redirect_to, $requested_redirect_to, $user ) {
		if ( is_wp_error( $user ) ) return $redirect_to;

		// Super admin ou gestor → painel WordPress normalmente
		$roles = (array) $user->roles;
		if ( in_array( 'kt_super_admin', $roles, true ) ||
		     in_array( 'kt_location_manager', $roles, true ) ||
		     in_array( 'administrator', $roles, true ) ) {
			return $redirect_to ?: admin_url();
		}

		// Colaborador com portal configurado → redireciona para o portal
		$portal_url = get_option( 'kt_portal_page_url' );
		if ( $portal_url ) {
			return $portal_url;
		}

		return $redirect_to;
	}

	public function enqueue_assets() {
		wp_enqueue_style( 'kt-frontend', KT_PLUGIN_URL . 'assets/frontend.css', [], KT_VERSION );
		wp_enqueue_script( 'kt-frontend', KT_PLUGIN_URL . 'assets/frontend.js', [ 'jquery' ], KT_VERSION, true );
		wp_localize_script( 'kt-frontend', 'ktFrontend', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'kt_frontend' ),
			'i18n'    => [
				'salvando'   => 'Salvando…',
				'concluido'  => 'Concluído ✓',
				'enviando'   => 'Enviando…',
				'erro_rede'  => 'Erro de conexão. Tente novamente.',
				'responda'   => 'Responda todas as perguntas antes de enviar.',
				'tentar_nov' => 'Tentar Novamente',
			],
		] );
	}

	/* -----------------------------------------------------------------------
	 * Shortcode principal [kt_portal]
	 * -------------------------------------------------------------------- */

	public function shortcode_portal( $atts ) {
		// Guarda a URL desta página para o shortcode [kt_modulo] poder linkar de volta
		update_option( 'kt_portal_page_url', get_permalink(), false );
		if ( ! is_user_logged_in() ) {
			return '<div class="kt-portal kt-login-prompt"><p>' .
				sprintf(
					'Por favor, <a href="%s">faça login</a> para acessar o portal de treinamentos.',
					esc_url( wp_login_url( get_permalink() ) )
				) . '</p></div>';
		}

		$member = KT_Member::get_by_user_id( get_current_user_id() );

		if ( ! $member ) {
			// Se for admin/super_admin, mostra link para o painel
			if ( KT_Roles::is_super_admin() || KT_Roles::is_location_manager() ) {
				return '<div class="kt-portal"><p>Você tem acesso ao <a href="' . esc_url( admin_url( 'admin.php?page=kt-dashboard' ) ) . '">painel administrativo</a> do Keen Training.</p></div>';
			}
			return '<div class="kt-portal"><p>Você não está cadastrado como colaborador. Entre em contato com seu gerente.</p></div>';
		}

		$view = sanitize_key( $_GET['kt_view'] ?? 'dashboard' );

		ob_start();
		switch ( $view ) {
			case 'course': $this->render_course( $member ); break;
			case 'quiz':   $this->render_quiz( $member );   break;
			default:       $this->render_dashboard( $member );
		}
		return ob_get_clean();
	}

	/* -----------------------------------------------------------------------
	 * Tela: Dashboard do colaborador
	 * -------------------------------------------------------------------- */

	private function render_dashboard( $member ) {
		$enrollments  = KT_Progress::get_enrollments_for_member( $member->id );
		$certificates = KT_Certificate::get_all_for_member( $member->id );
		include KT_PLUGIN_DIR . 'frontend/views/dashboard.php';
	}

	/* -----------------------------------------------------------------------
	 * Tela: Detalhe do curso
	 * -------------------------------------------------------------------- */

	private function render_course( $member ) {
		$course_id  = absint( $_GET['course_id'] ?? 0 );
		$enrollment = KT_Progress::get_enrollment( $member->id, $course_id );

		if ( ! $enrollment ) {
			echo '<div class="kt-portal"><p>Você não está matriculado neste curso.</p></div>';
			return;
		}

		// Verifica restrição de acesso
		if ( ! KT_Restriction::member_can_access( $member->id, $course_id ) ) {
			echo '<div class="kt-portal"><p>Você não tem permissão para acessar este curso.</p></div>';
			return;
		}

		$course  = KT_Course::get( $course_id );
		$modules = KT_Course::get_modules( $course_id );
		include KT_PLUGIN_DIR . 'frontend/views/course.php';
	}

	/* -----------------------------------------------------------------------
	 * Tela: Avaliação
	 * -------------------------------------------------------------------- */

	private function render_quiz( $member ) {
		$quiz_id   = absint( $_GET['quiz_id'] ?? 0 );
		$module_id = absint( $_GET['module_id'] ?? 0 );
		$quiz      = KT_Quiz::get( $quiz_id );

		if ( ! $quiz ) {
			echo '<div class="kt-portal"><p>Avaliação não encontrada.</p></div>';
			return;
		}

		$attempt_count = KT_Quiz::attempt_count( $member->id, $quiz_id );
		$best          = KT_Quiz::best_result( $member->id, $quiz_id );
		$questions = KT_Quiz::get_questions( $quiz_id );
		if ( $quiz->shuffle_questions ) {
			shuffle( $questions );
		}
		include KT_PLUGIN_DIR . 'frontend/views/quiz.php';
	}

	/* -----------------------------------------------------------------------
	 * AJAX: Marcar módulo como concluído
	 * -------------------------------------------------------------------- */

	public function ajax_complete_module() {
		check_ajax_referer( 'kt_frontend', 'nonce' );
		if ( ! is_user_logged_in() ) wp_send_json_error( [ 'message' => 'Não autenticado.' ] );

		$module_id = absint( $_POST['module_id'] ?? 0 );
		$member    = KT_Member::get_by_user_id( get_current_user_id() );
		if ( ! $member ) wp_send_json_error( [ 'message' => 'Colaborador não encontrado.' ] );

		// Se o módulo tem avaliação, exige aprovação antes
		$quiz = KT_Quiz::get_for_module( $module_id );
		if ( $quiz && ! KT_Quiz::best_result( $member->id, $quiz->id ) ) {
			wp_send_json_error( [
				'message'       => 'Você precisa ser aprovado na avaliação antes de concluir este módulo.',
				'quiz_required' => true,
			] );
		}

		KT_Progress::mark_module_complete( $member->id, $module_id );
		$module = KT_Course::get_module( $module_id );
		$pct    = $module ? KT_Progress::course_progress_pct( $member->id, $module->course_id ) : 0;

		// Verifica se o curso foi concluído (para exibir certificado sem reload)
		$course_done = false;
		if ( $module ) {
			$enrollment = KT_Progress::get_enrollment( $member->id, $module->course_id );
			$course_done = $enrollment && $enrollment->status === 'concluido';
		}

		wp_send_json_success( [ 'progress' => $pct, 'course_done' => $course_done ] );
	}

	/* -----------------------------------------------------------------------
	 * AJAX: Enviar avaliação
	 * -------------------------------------------------------------------- */

	public function ajax_submit_quiz() {
		check_ajax_referer( 'kt_frontend', 'nonce' );
		if ( ! is_user_logged_in() ) wp_send_json_error( [ 'message' => 'Não autenticado.' ] );

		$quiz_id   = absint( $_POST['quiz_id'] ?? 0 );
		$module_id = absint( $_POST['module_id'] ?? 0 );
		$member    = KT_Member::get_by_user_id( get_current_user_id() );
		if ( ! $member ) wp_send_json_error( [ 'message' => 'Colaborador não encontrado.' ] );

		$quiz = KT_Quiz::get( $quiz_id );
		if ( ! $quiz ) wp_send_json_error( [ 'message' => 'Avaliação não encontrada.' ] );

		$attempts = KT_Quiz::attempt_count( $member->id, $quiz_id );
		if ( ! KT_Quiz::can_attempt( $member->id, $quiz_id ) ) {
			wp_send_json_error( [ 'message' => 'Você atingiu o número máximo de tentativas.' ] );
		}

		$responses = array_map( 'absint', (array) ( $_POST['responses'] ?? [] ) );
		$result    = KT_Quiz::grade( $member->id, $quiz_id, $responses );

		if ( $result['passed'] && $module_id ) {
			KT_Progress::mark_module_complete( $member->id, $module_id );
		}

		$tentativas_restantes = ( (int) $quiz->max_attempts === 0 ) ? -1 : ( (int) $quiz->max_attempts - $attempts - 1 ); // -1 = ilimitado

		wp_send_json_success( [
			'score'   => $result['score'],
			'passed'  => $result['passed'],
			'correct' => $result['correct'],
			'total'   => $result['total'],
			'message' => $result['passed']
				? sprintf( 'Parabéns! Você foi aprovado(a) com %d%%! 🎉', $result['score'] )
				: sprintf( 'Você obteve %d%%. A nota mínima é %d%%. Tente novamente.', $result['score'], $quiz->pass_threshold ),
			'tentativas_restantes' => max( 0, $tentativas_restantes ),
		] );
	}

	/* -----------------------------------------------------------------------
	 * Shortcode [kt_modulo] — usado em páginas Elementor
	 * -------------------------------------------------------------------- */

	public function shortcode_module_actions( $atts ) {
		if ( ! is_user_logged_in() ) {
			return '<div class="kt-portal"><p>Por favor, <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">faça login</a> para acessar este conteúdo.</p></div>';
		}

		$member = KT_Member::get_by_user_id( get_current_user_id() );
		if ( ! $member ) {
			return '<div class="kt-portal"><p>Você não está cadastrado como colaborador. Entre em contato com seu gerente.</p></div>';
		}

		// Detecta o módulo vinculado a esta página
		$page_id = get_the_ID();
		$module  = KT_Course::get_module_by_page( $page_id );
		if ( ! $module ) {
			return ''; // Página não vinculada a nenhum módulo — silencioso
		}

		// Verifica matrícula no curso
		$enrollment = KT_Progress::get_enrollment( $member->id, $module->course_id );
		if ( ! $enrollment ) {
			return '<div class="kt-portal"><p>Você não está matriculado no curso deste módulo. <a href="' . esc_url( home_url( '/' ) ) . '">Voltar ao início</a></p></div>';
		}

		$is_complete  = KT_Progress::is_module_complete( $member->id, $module->id );
		$quiz         = KT_Quiz::get_for_module( $module->id );
		$quiz_passed  = $quiz ? (bool) KT_Quiz::best_result( $member->id, $quiz->id ) : false;
		$attempts     = $quiz ? KT_Quiz::attempt_count( $member->id, $quiz->id ) : 0;
		$max_attempts = $quiz ? (int) $quiz->max_attempts : 0;
		$unlimited    = $max_attempts === 0;
		$quiz_blocked = $quiz && ! $quiz_passed && ! $unlimited && $attempts >= $max_attempts;
		$course_url   = add_query_arg( [ 'kt_view' => 'course', 'course_id' => $module->course_id ], get_option( 'kt_portal_page_url', home_url( '/' ) ) );

		ob_start();
		include KT_PLUGIN_DIR . 'frontend/views/module-actions.php';
		return ob_get_clean();
	}

	/* -----------------------------------------------------------------------
	 * Shortcode [kt_quiz id="X" modulo="Y"]
	 * Embeds a quiz directly on any Elementor page.
	 * id     — quiz ID (obrigatório)
	 * modulo — module ID (opcional; se passado, aprovação conclui o módulo)
	 * -------------------------------------------------------------------- */

	public function shortcode_quiz_embed( $atts ) {
		$atts = shortcode_atts( [ 'id' => 0, 'modulo' => 0 ], $atts, 'kt_quiz' );
		$quiz_id   = absint( $atts['id'] );
		$module_id = absint( $atts['modulo'] );

		if ( ! $quiz_id ) {
			return '<p style="color:#b91c1c">⚠ Shortcode [kt_quiz]: informe o atributo <code>id</code> com o ID da avaliação.</p>';
		}

		$quiz = KT_Quiz::get( $quiz_id );
		if ( ! $quiz ) {
			return '<p style="color:#b91c1c">⚠ Avaliação não encontrada (id=' . $quiz_id . ').</p>';
		}

		if ( ! is_user_logged_in() ) {
			return '<div class="kt-portal kt-login-prompt"><p>Por favor, <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">faça login</a> para responder esta avaliação.</p></div>';
		}

		$member = KT_Member::get_by_user_id( get_current_user_id() );
		if ( ! $member ) {
			return '<div class="kt-portal"><p>Você não está cadastrado como colaborador. Entre em contato com seu gerente.</p></div>';
		}

		$attempt_count = KT_Quiz::attempt_count( $member->id, $quiz_id );
		$best          = KT_Quiz::best_result( $member->id, $quiz_id );
		$unlimited     = (int) $quiz->max_attempts === 0;
		$exhausted     = ! $unlimited && $attempt_count >= (int) $quiz->max_attempts;
		$attempts_str  = $unlimited
			? ( $attempt_count > 0 ? $attempt_count . ' realizada(s) · Ilimitadas' : 'Ilimitadas' )
			: $attempt_count . ' de ' . $quiz->max_attempts;

		$questions = KT_Quiz::get_questions( $quiz_id );
		if ( $quiz->shuffle_questions ) shuffle( $questions );

		ob_start();
		include KT_PLUGIN_DIR . 'frontend/views/quiz-embed.php';
		return ob_get_clean();
	}

	/* -----------------------------------------------------------------------
	 * Controle de acesso a páginas vinculadas a módulos
	 * -------------------------------------------------------------------- */

	public function enforce_module_page_access() {
		if ( ! is_singular() ) return;

		$page_id = get_the_ID();
		$module  = KT_Course::get_module_by_page( $page_id );
		if ( ! $module ) return; // Página normal, sem vínculo

		// Não logado → login
		if ( ! is_user_logged_in() ) {
			wp_redirect( wp_login_url( get_permalink( $page_id ) ) );
			exit;
		}

		// Admins e gerentes passam sempre
		if ( KT_Roles::is_super_admin() || KT_Roles::is_location_manager() ) return;

		$member = KT_Member::get_by_user_id( get_current_user_id() );

		// Usuário logado mas sem perfil de colaborador
		if ( ! $member ) {
			wp_die( 'Você não tem permissão para acessar este conteúdo.', 'Acesso restrito', [ 'response' => 403 ] );
		}

		// Colaborador não matriculado no curso
		$enrollment = KT_Progress::get_enrollment( $member->id, $module->course_id );
		if ( ! $enrollment ) {
			// Redireciona para o portal com aviso
			$portal = get_option( 'kt_portal_page_url', home_url( '/' ) );
			wp_redirect( add_query_arg( 'kt_acesso_negado', '1', $portal ) );
			exit;
		}
	}

	/* -----------------------------------------------------------------------
	 * Certificado
	 * -------------------------------------------------------------------- */

	public function maybe_render_certificate() {
		$uid = sanitize_text_field( $_GET['kt_cert'] ?? '' );
		if ( ! $uid ) return;
		// Renderiza a página completa do certificado
		echo KT_Certificate::render_html( $uid );
		exit;
	}
}
