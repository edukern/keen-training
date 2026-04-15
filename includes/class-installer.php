<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KT_Installer {

	public static function activate() {
		self::create_tables();
		KT_Roles::register();
		// Sinaliza que o assistente de configuração deve ser exibido
		add_option( 'kt_show_setup_wizard', '1' );
		flush_rewrite_rules();
	}

	public static function deactivate() {
		flush_rewrite_rules();
	}

	private static function create_tables() {
		global $wpdb;
		$charset = $wpdb->get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Unidades (lojas)
		dbDelta( "CREATE TABLE {$wpdb->prefix}kt_locations (
			id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name       VARCHAR(200)    NOT NULL,
			address    TEXT            NOT NULL DEFAULT '',
			manager_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) $charset;" );

		// Funções de colaboradores (taxonomia própria)
		dbDelta( "CREATE TABLE {$wpdb->prefix}kt_positions (
			id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name        VARCHAR(200)    NOT NULL,
			slug        VARCHAR(200)    NOT NULL,
			description TEXT            NOT NULL DEFAULT '',
			color       VARCHAR(7)      NOT NULL DEFAULT '#64748b',
			created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY slug (slug)
		) $charset;" );

		// Colaboradores
		dbDelta( "CREATE TABLE {$wpdb->prefix}kt_members (
			id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id     BIGINT UNSIGNED NOT NULL,
			location_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			position_id BIGINT UNSIGNED          DEFAULT NULL,
			hire_date   DATE                     DEFAULT NULL,
			created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY user_id (user_id),
			KEY position_id (position_id)
		) $charset;" );

		// Cursos
		dbDelta( "CREATE TABLE {$wpdb->prefix}kt_courses (
			id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			title         VARCHAR(300)    NOT NULL,
			description   TEXT            NOT NULL DEFAULT '',
			passing_score TINYINT UNSIGNED NOT NULL DEFAULT 70,
			created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) $charset;" );

		// Módulos
		dbDelta( "CREATE TABLE {$wpdb->prefix}kt_modules (
			id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			course_id    BIGINT UNSIGNED NOT NULL,
			title        VARCHAR(300)    NOT NULL,
			description  TEXT            NOT NULL DEFAULT '',
			content_url  TEXT            NOT NULL DEFAULT '',
			embed_type   VARCHAR(30)     NOT NULL DEFAULT 'link',
			page_id      BIGINT UNSIGNED          DEFAULT NULL,
			sort_order   SMALLINT        NOT NULL DEFAULT 0,
			created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY course_id (course_id),
			KEY page_id (page_id)
		) $charset;" );

		// Avaliações (quizzes)
		dbDelta( "CREATE TABLE {$wpdb->prefix}kt_quizzes (
			id                BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
			module_id         BIGINT UNSIGNED           DEFAULT NULL,
			course_id         BIGINT UNSIGNED           DEFAULT NULL,
			title             VARCHAR(300)     NOT NULL,
			pass_threshold    TINYINT UNSIGNED NOT NULL DEFAULT 70,
			max_attempts      TINYINT UNSIGNED NOT NULL DEFAULT 3,
			shuffle_questions TINYINT(1)       NOT NULL DEFAULT 0,
			shuffle_answers   TINYINT(1)       NOT NULL DEFAULT 0,
			created_at        DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) $charset;" );

		// Perguntas das avaliações
		dbDelta( "CREATE TABLE {$wpdb->prefix}kt_quiz_questions (
			id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			quiz_id       BIGINT UNSIGNED NOT NULL,
			question_text TEXT            NOT NULL,
			question_type VARCHAR(20)     NOT NULL DEFAULT 'multiple_choice',
			sort_order    SMALLINT        NOT NULL DEFAULT 0,
			PRIMARY KEY (id),
			KEY quiz_id (quiz_id)
		) $charset;" );

		// Alternativas
		dbDelta( "CREATE TABLE {$wpdb->prefix}kt_quiz_answers (
			id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			question_id BIGINT UNSIGNED NOT NULL,
			answer_text TEXT            NOT NULL,
			is_correct  TINYINT(1)      NOT NULL DEFAULT 0,
			PRIMARY KEY (id),
			KEY question_id (question_id)
		) $charset;" );

		// Matrículas
		dbDelta( "CREATE TABLE {$wpdb->prefix}kt_enrollments (
			id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			member_id     BIGINT UNSIGNED NOT NULL,
			course_id     BIGINT UNSIGNED NOT NULL,
			assigned_by   BIGINT UNSIGNED NOT NULL DEFAULT 0,
			assigned_date DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			due_date      DATE                     DEFAULT NULL,
			status        VARCHAR(20)     NOT NULL DEFAULT 'nao_iniciado',
			completed_at  DATETIME                 DEFAULT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY member_course (member_id, course_id)
		) $charset;" );

		// Progresso por módulo
		dbDelta( "CREATE TABLE {$wpdb->prefix}kt_progress (
			id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			member_id    BIGINT UNSIGNED NOT NULL,
			module_id    BIGINT UNSIGNED NOT NULL,
			completed_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY member_module (member_id, module_id)
		) $charset;" );

		// Resultados de avaliações
		dbDelta( "CREATE TABLE {$wpdb->prefix}kt_quiz_results (
			id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			member_id    BIGINT UNSIGNED NOT NULL,
			quiz_id      BIGINT UNSIGNED NOT NULL,
			score        TINYINT UNSIGNED NOT NULL DEFAULT 0,
			passed       TINYINT(1)       NOT NULL DEFAULT 0,
			attempt_date DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY member_quiz (member_id, quiz_id)
		) $charset;" );

		// Certificados
		dbDelta( "CREATE TABLE {$wpdb->prefix}kt_certificates (
			id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			member_id  BIGINT UNSIGNED NOT NULL,
			course_id  BIGINT UNSIGNED NOT NULL,
			issued_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			cert_uid   VARCHAR(64)     NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY member_course (member_id, course_id),
			UNIQUE KEY cert_uid (cert_uid)
		) $charset;" );

		// Restrições de acesso por curso
		dbDelta( "CREATE TABLE {$wpdb->prefix}kt_course_restrictions (
			id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			course_id        BIGINT UNSIGNED NOT NULL,
			restriction_type VARCHAR(20)     NOT NULL DEFAULT 'location',
			restriction_value VARCHAR(100)   NOT NULL,
			PRIMARY KEY (id),
			KEY course_id (course_id)
		) $charset;" );

		update_option( 'kt_db_version', KT_VERSION );
	}

	/**
	 * Chamado no plugins_loaded. Só executa migrações quando a versão
	 * armazenada no banco é diferente da versão atual do plugin.
	 *
	 * Como atualizar o plugin sem perder dados dos clientes:
	 *  1. Incremente KT_VERSION em keen-training.php.
	 *  2. Adicione um bloco "if ( version_compare( $installed, 'X.Y.Z', '<' ) )"
	 *     abaixo para cada conjunto de mudanças de schema.
	 *  3. O dbDelta e os ALTER TABLE apenas acrescentam — nunca apagam dados.
	 *  4. Para distribuir: compacte a pasta do plugin em .zip e envie ao cliente.
	 *     Ele faz upload via Plugins > Adicionar Novo > Fazer Upload. Os dados
	 *     no banco (colaboradores, cursos, progresso, certificados) são
	 *     preservados integralmente.
	 */
	public static function maybe_upgrade() {
		$installed = get_option( 'kt_db_version', '0' );

		// Nenhuma mudança desde a última vez — sai sem tocar no banco
		if ( version_compare( $installed, KT_VERSION, '>=' ) ) {
			return;
		}

		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset = $wpdb->get_charset_collate();

		// -----------------------------------------------------------------
		// Migrações introduzidas na v1.0.0
		// (instalações anteriores ao sistema de versões chegam aqui com '0')
		// -----------------------------------------------------------------
		if ( version_compare( $installed, '1.0.0', '<' ) ) {

			// Colunas de embaralhamento no quiz
			$quiz_cols = $wpdb->get_col( "SHOW COLUMNS FROM {$wpdb->prefix}kt_quizzes" );
			if ( ! in_array( 'shuffle_questions', $quiz_cols, true ) ) {
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}kt_quizzes ADD COLUMN shuffle_questions TINYINT(1) NOT NULL DEFAULT 0" );
			}
			if ( ! in_array( 'shuffle_answers', $quiz_cols, true ) ) {
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}kt_quizzes ADD COLUMN shuffle_answers TINYINT(1) NOT NULL DEFAULT 0" );
			}

			// Tabela de funções (pode não existir em instalações antigas)
			dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}kt_positions (
				id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				name        VARCHAR(200)    NOT NULL,
				slug        VARCHAR(200)    NOT NULL,
				description TEXT            NOT NULL DEFAULT '',
				color       VARCHAR(7)      NOT NULL DEFAULT '#64748b',
				created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				UNIQUE KEY slug (slug)
			) $charset;" );

			// Coluna position_id em membros
			$mem_cols = $wpdb->get_col( "SHOW COLUMNS FROM {$wpdb->prefix}kt_members" );
			if ( ! in_array( 'position_id', $mem_cols, true ) ) {
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}kt_members ADD COLUMN position_id BIGINT UNSIGNED DEFAULT NULL" );
			}

			// Coluna page_id nos módulos (vincula módulo a página Elementor)
			$mod_cols = $wpdb->get_col( "SHOW COLUMNS FROM {$wpdb->prefix}kt_modules" );
			if ( ! in_array( 'page_id', $mod_cols, true ) ) {
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}kt_modules ADD COLUMN page_id BIGINT UNSIGNED DEFAULT NULL" );
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}kt_modules ADD KEY page_id (page_id)" );
			}
		}

		// -----------------------------------------------------------------
		// Migrações introduzidas na v2.0.0
		// -----------------------------------------------------------------
		if ( version_compare( $installed, '2.0.0', '<' ) ) {

			// Novos campos na tabela de quizzes
			$quiz_cols = $wpdb->get_col( "SHOW COLUMNS FROM {$wpdb->prefix}kt_quizzes" );
			if ( ! in_array( 'pass_message', $quiz_cols, true ) ) {
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}kt_quizzes ADD COLUMN pass_message TEXT NOT NULL DEFAULT ''" );
			}
			if ( ! in_array( 'fail_message', $quiz_cols, true ) ) {
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}kt_quizzes ADD COLUMN fail_message TEXT NOT NULL DEFAULT ''" );
			}
			if ( ! in_array( 'question_pool_size', $quiz_cols, true ) ) {
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}kt_quizzes ADD COLUMN question_pool_size INT NOT NULL DEFAULT 0" );
			}

			// Explicação por pergunta
			$q_cols = $wpdb->get_col( "SHOW COLUMNS FROM {$wpdb->prefix}kt_quiz_questions" );
			if ( ! in_array( 'explanation', $q_cols, true ) ) {
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}kt_quiz_questions ADD COLUMN explanation TEXT NOT NULL DEFAULT ''" );
			}

			// Snapshot de respostas nos resultados
			$r_cols = $wpdb->get_col( "SHOW COLUMNS FROM {$wpdb->prefix}kt_quiz_results" );
			if ( ! in_array( 'answers_snapshot', $r_cols, true ) ) {
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}kt_quiz_results ADD COLUMN answers_snapshot LONGTEXT DEFAULT NULL" );
			}
		}

		// -----------------------------------------------------------------
		// Adicione blocos futuros aqui, ex:
		// if ( version_compare( $installed, '2.1.0', '<' ) ) { ... }
		// -----------------------------------------------------------------

		update_option( 'kt_db_version', KT_VERSION );
	}
}
