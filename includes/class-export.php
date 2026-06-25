<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Exportador de dados do Keen Training.
 *
 * Gera um ÚNICO arquivo JSON com todas as entidades do LMS preservando os
 * IDs de origem, para que outro sistema (ex.: Next.js/Supabase) reconstrua os
 * relacionamentos. Operação 100% somente-leitura sobre as tabelas kt_.
 *
 * NÃO exporta o layout das páginas (Elementor) — apenas dados.
 *
 * @see docs/EXPORT.md
 */
class KT_Export {

	/** Versão do formato do envelope JSON. Incremente se a estrutura mudar. */
	const SCHEMA_VERSION = 1;

	/** Tamanho do lote ao paginar tabelas grandes (progress, quiz_results). */
	const CHUNK = 2000;

	/**
	 * Monta o array completo do export (envelope + todas as entidades).
	 *
	 * @return array
	 */
	public static function build() {
		global $wpdb;
		$p = $wpdb->prefix;

		return [
			'schema_version' => self::SCHEMA_VERSION,
			'exported_at'    => gmdate( 'c' ), // ISO 8601 UTC
			'source'         => 'keen-training ' . ( defined( 'KT_VERSION' ) ? KT_VERSION : '?' ),
			'identity_note'  => 'O elo de identidade do colaborador é o bloco "members". '
				. 'Este plugin não armazena CPF nativamente: faça o match no destino por '
				. 'email (preferencial) e, como reforço, por login/nome. O campo '
				. 'cpf_ou_documento só vem preenchido se algum sistema externo gravou '
				. 'CPF/documento em wp_usermeta.',

			'locations'    => self::locations(),
			'positions'    => self::positions(),
			'members'      => self::members(),
			'courses'      => self::courses(),
			'modules'      => self::modules(),
			'quizzes'      => self::quizzes(),
			'enrollments'  => self::enrollments(),
			'progress'     => self::progress(),
			'quiz_results' => self::quiz_results(),
			'certificates' => self::certificates(),
			'restrictions' => self::restrictions(),
		];
	}

	/* -----------------------------------------------------------------------
	 * Entidades
	 * -------------------------------------------------------------------- */

	private static function locations() {
		global $wpdb;
		$rows = $wpdb->get_results( "SELECT id, name, manager_id FROM {$wpdb->prefix}kt_locations ORDER BY id", ARRAY_A );
		return array_map( function ( $r ) {
			return [
				'id'              => (int) $r['id'],
				'name'            => $r['name'],
				'manager_user_id' => (int) $r['manager_id'],
			];
		}, $rows ?: [] );
	}

	private static function positions() {
		global $wpdb;
		$rows = $wpdb->get_results( "SELECT id, name, slug, color FROM {$wpdb->prefix}kt_positions ORDER BY id", ARRAY_A );
		return array_map( function ( $r ) {
			return [
				'id'    => (int) $r['id'],
				'name'  => $r['name'],
				'slug'  => $r['slug'],
				'color' => $r['color'],
			];
		}, $rows ?: [] );
	}

	/**
	 * Colaboradores com a identidade do usuário WP anexada (nome, email, login,
	 * documento). Este é o bloco mais importante para casar pessoas no destino.
	 */
	private static function members() {
		global $wpdb;
		$rows = $wpdb->get_results(
			"SELECT m.id, m.user_id, m.location_id, m.position_id, m.hire_date, m.birth_date,
			        u.user_login, u.user_email, u.display_name,
			        umf.meta_value AS first_name,
			        uml.meta_value AS last_name
			 FROM {$wpdb->prefix}kt_members m
			 JOIN {$wpdb->users} u ON u.ID = m.user_id
			 LEFT JOIN {$wpdb->usermeta} umf ON umf.user_id = u.ID AND umf.meta_key = 'first_name'
			 LEFT JOIN {$wpdb->usermeta} uml ON uml.user_id = u.ID AND uml.meta_key = 'last_name'
			 ORDER BY m.id",
			ARRAY_A
		);

		return array_map( function ( $r ) {
			$first = trim( (string) $r['first_name'] );
			$last  = trim( (string) $r['last_name'] );
			$nome  = trim( $first . ' ' . $last );
			if ( $nome === '' ) $nome = $r['display_name'];

			return [
				'id'               => (int) $r['id'],
				'user_id'          => (int) $r['user_id'],
				'location_id'      => (int) $r['location_id'],
				'position_id'      => $r['position_id'] !== null ? (int) $r['position_id'] : null,
				'hire_date'        => self::iso( $r['hire_date'] ),
				'birth_date'       => self::iso( $r['birth_date'] ),
				// Identidade
				'nome'             => $nome,
				'email'            => $r['user_email'],
				'login'            => $r['user_login'],
				'cpf_ou_documento' => self::find_document( (int) $r['user_id'] ),
				// Reforço de match (não exigidos pelo destino, mas úteis)
				'display_name'     => $r['display_name'],
				'first_name'       => $first ?: null,
				'last_name'        => $last ?: null,
			];
		}, $rows ?: [] );
	}

	private static function courses() {
		global $wpdb;
		$rows = $wpdb->get_results( "SELECT id, title, description, passing_score FROM {$wpdb->prefix}kt_courses ORDER BY id", ARRAY_A );
		return array_map( function ( $r ) {
			return [
				'id'            => (int) $r['id'],
				'title'         => $r['title'],
				'description'   => $r['description'],
				'passing_score' => (int) $r['passing_score'],
			];
		}, $rows ?: [] );
	}

	private static function modules() {
		global $wpdb;
		$rows = $wpdb->get_results(
			"SELECT id, course_id, title, description, content_url, embed_type, sort_order
			 FROM {$wpdb->prefix}kt_modules ORDER BY course_id, sort_order, id",
			ARRAY_A
		);
		return array_map( function ( $r ) {
			return [
				'id'          => (int) $r['id'],
				'course_id'   => (int) $r['course_id'],
				'title'       => $r['title'],
				'description' => $r['description'],
				'content_url' => $r['content_url'],
				'embed_type'  => $r['embed_type'],
				'sort_order'  => (int) $r['sort_order'],
			];
		}, $rows ?: [] );
	}

	/**
	 * Quizzes com perguntas e alternativas ANINHADAS, cada uma mantendo seu id.
	 * Carrega tudo em 3 consultas e agrupa em memória (evita N+1).
	 */
	private static function quizzes() {
		global $wpdb;
		$p = $wpdb->prefix;

		$quizzes = $wpdb->get_results(
			"SELECT id, module_id, course_id, title, pass_threshold, max_attempts,
			        shuffle_questions, shuffle_answers, question_pool_size,
			        pass_message, fail_message
			 FROM {$p}kt_quizzes ORDER BY id",
			ARRAY_A
		) ?: [];

		$questions = $wpdb->get_results(
			"SELECT id, quiz_id, question_text, question_type, explanation, sort_order
			 FROM {$p}kt_quiz_questions ORDER BY quiz_id, sort_order, id",
			ARRAY_A
		) ?: [];

		$answers = $wpdb->get_results(
			"SELECT id, question_id, answer_text, is_correct
			 FROM {$p}kt_quiz_answers ORDER BY question_id, id",
			ARRAY_A
		) ?: [];

		// Agrupa alternativas por pergunta
		$answers_by_q = [];
		foreach ( $answers as $a ) {
			$answers_by_q[ (int) $a['question_id'] ][] = [
				'id'          => (int) $a['id'],
				'answer_text' => $a['answer_text'],
				'is_correct'  => (bool) $a['is_correct'],
			];
		}

		// Agrupa perguntas por quiz, já com suas alternativas
		$questions_by_quiz = [];
		foreach ( $questions as $q ) {
			$qid = (int) $q['id'];
			$questions_by_quiz[ (int) $q['quiz_id'] ][] = [
				'id'            => $qid,
				'question_text' => $q['question_text'],
				'question_type' => $q['question_type'],
				'explanation'   => $q['explanation'],
				'sort_order'    => (int) $q['sort_order'],
				'answers'       => $answers_by_q[ $qid ] ?? [],
			];
		}

		return array_map( function ( $q ) use ( $questions_by_quiz ) {
			return [
				'id'                 => (int) $q['id'],
				'module_id'          => $q['module_id'] !== null ? (int) $q['module_id'] : null,
				'course_id'          => $q['course_id'] !== null ? (int) $q['course_id'] : null,
				'title'              => $q['title'],
				'pass_threshold'     => (int) $q['pass_threshold'],
				'max_attempts'       => (int) $q['max_attempts'],
				'shuffle_questions'  => (bool) $q['shuffle_questions'],
				'shuffle_answers'    => (bool) $q['shuffle_answers'],
				'question_pool_size' => (int) $q['question_pool_size'],
				'pass_message'       => $q['pass_message'],
				'fail_message'       => $q['fail_message'],
				'questions'          => $questions_by_quiz[ (int) $q['id'] ] ?? [],
			];
		}, $quizzes );
	}

	private static function enrollments() {
		global $wpdb;
		$out = [];
		self::each_chunk(
			"SELECT id, member_id, course_id, assigned_by, assigned_date, due_date, status, completed_at
			 FROM {$wpdb->prefix}kt_enrollments ORDER BY id",
			function ( $r ) use ( &$out ) {
				$out[] = [
					'id'            => (int) $r['id'],
					'member_id'     => (int) $r['member_id'],
					'course_id'     => (int) $r['course_id'],
					'assigned_by'   => (int) $r['assigned_by'],
					'assigned_date' => self::iso( $r['assigned_date'] ),
					'due_date'      => self::iso( $r['due_date'] ),
					'status'        => $r['status'],
					'completed_at'  => self::iso( $r['completed_at'] ),
				];
			}
		);
		return $out;
	}

	private static function progress() {
		global $wpdb;
		$out = [];
		self::each_chunk(
			"SELECT id, member_id, module_id, completed_at
			 FROM {$wpdb->prefix}kt_progress ORDER BY id",
			function ( $r ) use ( &$out ) {
				$out[] = [
					'id'           => (int) $r['id'],
					'member_id'    => (int) $r['member_id'],
					'module_id'    => (int) $r['module_id'],
					'completed_at' => self::iso( $r['completed_at'] ),
				];
			}
		);
		return $out;
	}

	private static function quiz_results() {
		global $wpdb;
		$out = [];
		self::each_chunk(
			"SELECT id, member_id, quiz_id, score, passed, attempt_date, answers_snapshot
			 FROM {$wpdb->prefix}kt_quiz_results ORDER BY id",
			function ( $r ) use ( &$out ) {
				$out[] = [
					'id'               => (int) $r['id'],
					'member_id'        => (int) $r['member_id'],
					'quiz_id'          => (int) $r['quiz_id'],
					'score'            => (int) $r['score'],
					'passed'           => (bool) $r['passed'],
					'attempt_date'     => self::iso( $r['attempt_date'] ),
					// Exportado como veio (texto/JSON). Pode ser null em registros antigos.
					'answers_snapshot' => $r['answers_snapshot'],
				];
			}
		);
		return $out;
	}

	private static function certificates() {
		global $wpdb;
		$rows = $wpdb->get_results(
			"SELECT id, member_id, course_id, issued_at, cert_uid
			 FROM {$wpdb->prefix}kt_certificates ORDER BY id",
			ARRAY_A
		);
		return array_map( function ( $r ) {
			return [
				'id'        => (int) $r['id'],
				'member_id' => (int) $r['member_id'],
				'course_id' => (int) $r['course_id'],
				'issued_at' => self::iso( $r['issued_at'] ),
				'cert_uid'  => $r['cert_uid'],
			];
		}, $rows ?: [] );
	}

	private static function restrictions() {
		global $wpdb;
		$rows = $wpdb->get_results(
			"SELECT id, course_id, restriction_type, restriction_value
			 FROM {$wpdb->prefix}kt_course_restrictions ORDER BY id",
			ARRAY_A
		);
		return array_map( function ( $r ) {
			return [
				'id'                => (int) $r['id'],
				'course_id'         => (int) $r['course_id'],
				'restriction_type'  => $r['restriction_type'],
				'restriction_value' => $r['restriction_value'],
			];
		}, $rows ?: [] );
	}

	/* -----------------------------------------------------------------------
	 * Helpers
	 * -------------------------------------------------------------------- */

	/**
	 * Percorre uma consulta em lotes (LIMIT/OFFSET) chamando $cb para cada linha.
	 * Mantém o uso de memória sob controle em tabelas grandes.
	 *
	 * @param string   $sql Consulta SEM cláusula LIMIT.
	 * @param callable $cb  Recebe cada linha como array associativo.
	 */
	private static function each_chunk( $sql, callable $cb ) {
		global $wpdb;
		$offset = 0;
		do {
			$rows = $wpdb->get_results(
				$wpdb->prepare( $sql . ' LIMIT %d OFFSET %d', self::CHUNK, $offset ),
				ARRAY_A
			);
			if ( ! $rows ) break;
			foreach ( $rows as $r ) $cb( $r );
			$offset += self::CHUNK;
		} while ( count( $rows ) === self::CHUNK );
	}

	/**
	 * Normaliza datas/datetimes para string ISO 8601 SEM converter fuso.
	 * - 'YYYY-MM-DD'           → inalterado
	 * - 'YYYY-MM-DD HH:MM:SS'  → 'YYYY-MM-DDTHH:MM:SS' (mesma hora de relógio)
	 * - null / '' / '0000-..'  → null
	 */
	private static function iso( $value ) {
		if ( $value === null ) return null;
		$value = trim( (string) $value );
		if ( $value === '' || strpos( $value, '0000-00-00' ) === 0 ) return null;
		if ( preg_match( '/^(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2}:\d{2})$/', $value, $m ) ) {
			return $m[1] . 'T' . $m[2];
		}
		return $value;
	}

	/**
	 * Procura um CPF/documento em wp_usermeta para o usuário.
	 * O plugin não grava esse dado, mas integrações externas costumam usar
	 * chaves como 'cpf', 'documento', 'rg' ou 'matricula'. Retorna o primeiro
	 * valor não-vazio encontrado, ou null.
	 */
	private static function find_document( $user_id ) {
		global $wpdb;
		$value = $wpdb->get_var( $wpdb->prepare(
			"SELECT meta_value FROM {$wpdb->usermeta}
			 WHERE user_id = %d
			   AND LOWER(meta_key) IN ('cpf','documento','doc','rg','matricula','matrícula','cpf_cnpj')
			   AND meta_value <> ''
			 ORDER BY FIELD(LOWER(meta_key),'cpf','cpf_cnpj','documento','doc','matricula','rg')
			 LIMIT 1",
			$user_id
		) );
		return $value !== null && $value !== '' ? $value : null;
	}

	/* -----------------------------------------------------------------------
	 * Saída
	 * -------------------------------------------------------------------- */

	/** Nome de arquivo sugerido para o download. */
	public static function filename() {
		return 'keen-training-export-' . gmdate( 'Y-m-d-His' ) . '.json';
	}

	/** Codifica o export como JSON (UTF-8, legível). */
	public static function to_json( $pretty = true ) {
		$flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
		if ( $pretty ) $flags |= JSON_PRETTY_PRINT;
		return wp_json_encode( self::build(), $flags );
	}

	/**
	 * Envia o JSON como download e encerra a execução.
	 * Usado pelo handler admin (admin_post).
	 */
	public static function stream_download() {
		// Bases grandes podem demandar mais tempo/memória
		if ( function_exists( 'set_time_limit' ) ) @set_time_limit( 0 );
		@ini_set( 'memory_limit', '512M' );

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . self::filename() . '"' );
		echo self::to_json( true );
		exit;
	}
}
