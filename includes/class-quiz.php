<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KT_Quiz {

	/* -----------------------------------------------------------------------
	 * CRUD de avaliações
	 * -------------------------------------------------------------------- */

	public static function get_all() {
		global $wpdb;
		return $wpdb->get_results(
			"SELECT q.*, c.title AS course_title, m.title AS module_title
			 FROM {$wpdb->prefix}kt_quizzes q
			 LEFT JOIN {$wpdb->prefix}kt_courses c ON c.id = q.course_id
			 LEFT JOIN {$wpdb->prefix}kt_modules m ON m.id = q.module_id
			 ORDER BY q.title ASC"
		);
	}

	public static function get( $id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}kt_quizzes WHERE id = %d", $id
		) );
	}

	public static function get_for_module( $module_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}kt_quizzes WHERE module_id = %d LIMIT 1", $module_id
		) );
	}

	public static function create( $data ) {
		global $wpdb;
		$wpdb->insert( $wpdb->prefix . 'kt_quizzes', [
			'module_id'          => ! empty( $data['module_id'] ) ? absint( $data['module_id'] ) : null,
			'course_id'          => ! empty( $data['course_id'] ) ? absint( $data['course_id'] ) : null,
			'title'              => sanitize_text_field( $data['title'] ),
			'pass_threshold'     => absint( $data['pass_threshold'] ?? 80 ),
			'max_attempts'       => absint( $data['max_attempts'] ?? 0 ), // 0 = ilimitado
			'shuffle_questions'  => empty( $data['shuffle_questions'] ) ? 0 : 1,
			'shuffle_answers'    => empty( $data['shuffle_answers'] )   ? 0 : 1,
			'pass_message'       => sanitize_textarea_field( $data['pass_message'] ?? '' ),
			'fail_message'       => sanitize_textarea_field( $data['fail_message'] ?? '' ),
			'question_pool_size' => absint( $data['question_pool_size'] ?? 0 ),
		] );
		return $wpdb->insert_id;
	}

	public static function update( $id, $data ) {
		global $wpdb;
		$wpdb->update(
			$wpdb->prefix . 'kt_quizzes',
			[
				'module_id'          => ! empty( $data['module_id'] ) ? absint( $data['module_id'] ) : null,
				'course_id'          => ! empty( $data['course_id'] ) ? absint( $data['course_id'] ) : null,
				'title'              => sanitize_text_field( $data['title'] ),
				'pass_threshold'     => absint( $data['pass_threshold'] ?? 80 ),
				'max_attempts'       => absint( $data['max_attempts'] ?? 0 ), // 0 = ilimitado
				'shuffle_questions'  => empty( $data['shuffle_questions'] ) ? 0 : 1,
				'shuffle_answers'    => empty( $data['shuffle_answers'] )   ? 0 : 1,
				'pass_message'       => sanitize_textarea_field( $data['pass_message'] ?? '' ),
				'fail_message'       => sanitize_textarea_field( $data['fail_message'] ?? '' ),
				'question_pool_size' => absint( $data['question_pool_size'] ?? 0 ),
			],
			[ 'id' => absint( $id ) ]
		);
	}

	/** Retorna true se o colaborador ainda pode tentar (0 = ilimitado). */
	public static function can_attempt( $member_id, $quiz_id ) {
		$quiz = self::get( $quiz_id );
		if ( ! $quiz ) return false;
		if ( (int) $quiz->max_attempts === 0 ) return true; // ilimitado
		return self::attempt_count( $member_id, $quiz_id ) < (int) $quiz->max_attempts;
	}

	public static function delete( $id ) {
		global $wpdb;
		$id = absint( $id );
		foreach ( self::get_questions( $id ) as $q ) {
			$wpdb->delete( $wpdb->prefix . 'kt_quiz_answers', [ 'question_id' => $q->id ] );
		}
		$wpdb->delete( $wpdb->prefix . 'kt_quiz_questions', [ 'quiz_id' => $id ] );
		$wpdb->delete( $wpdb->prefix . 'kt_quizzes', [ 'id' => $id ] );
	}

	/* -----------------------------------------------------------------------
	 * Perguntas e alternativas
	 * -------------------------------------------------------------------- */

	/**
	 * Retorna perguntas de uma avaliação.
	 *
	 * @param int  $quiz_id
	 * @param bool $for_attempt  Se true, aplica pool e embaralhamento conforme configuração do quiz.
	 * @return array
	 */
	public static function get_questions( $quiz_id, $for_attempt = false ) {
		global $wpdb;
		$questions = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}kt_quiz_questions
			 WHERE quiz_id = %d ORDER BY sort_order ASC, id ASC",
			$quiz_id
		) );

		if ( $for_attempt ) {
			$quiz = self::get( $quiz_id );
			if ( $quiz ) {
				if ( $quiz->shuffle_questions ) {
					shuffle( $questions );
				}
				$pool_size = (int) ( $quiz->question_pool_size ?? 0 );
				if ( $pool_size > 0 && $pool_size < count( $questions ) ) {
					$questions = array_slice( $questions, 0, $pool_size );
				}
			}
		}

		return $questions;
	}

	public static function get_answers( $question_id ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}kt_quiz_answers WHERE question_id = %d", $question_id
		) );
	}

	public static function add_question( $data ) {
		global $wpdb;
		$wpdb->insert( $wpdb->prefix . 'kt_quiz_questions', [
			'quiz_id'       => absint( $data['quiz_id'] ),
			'question_text' => sanitize_textarea_field( $data['question_text'] ),
			'question_type' => sanitize_key( $data['question_type'] ?? 'multiple_choice' ),
			'sort_order'    => absint( $data['sort_order'] ?? 0 ),
			'explanation'   => sanitize_textarea_field( $data['explanation'] ?? '' ),
		] );
		return $wpdb->insert_id;
	}

	public static function save_answers( $question_id, $answers ) {
		global $wpdb;
		$question_id = absint( $question_id );
		$wpdb->delete( $wpdb->prefix . 'kt_quiz_answers', [ 'question_id' => $question_id ] );
		foreach ( $answers as $ans ) {
			if ( empty( $ans['text'] ) ) continue;
			$wpdb->insert( $wpdb->prefix . 'kt_quiz_answers', [
				'question_id' => $question_id,
				'answer_text' => sanitize_text_field( $ans['text'] ),
				'is_correct'  => ! empty( $ans['is_correct'] ) ? 1 : 0,
			] );
		}
	}

	/* -----------------------------------------------------------------------
	 * Correção de avaliação
	 * -------------------------------------------------------------------- */

	/**
	 * Corrige a avaliação e salva o resultado.
	 *
	 * @param int   $member_id
	 * @param int   $quiz_id
	 * @param array $responses     [ question_id => answer_id ] OR [ question_id => [ answer_id, ... ] ] for multiple_select
	 * @param array $question_ids  Optional list of question IDs to restrict grading (for pool support).
	 * @return array [ score, passed, correct, total, snapshot ]
	 */
	public static function grade( $member_id, $quiz_id, $responses, $question_ids = [] ) {
		global $wpdb;

		$quiz      = self::get( $quiz_id );
		$questions = self::get_questions( $quiz_id );

		// Restrict to the pooled set if question_ids provided
		if ( ! empty( $question_ids ) ) {
			$question_ids = array_map( 'absint', $question_ids );
			$questions    = array_filter( $questions, function( $q ) use ( $question_ids ) {
				return in_array( (int) $q->id, $question_ids, true );
			} );
			$questions = array_values( $questions );
		}

		$total    = count( $questions );
		$correct  = 0;
		$snapshot = [];

		foreach ( $questions as $q ) {
			$all_answers = self::get_answers( $q->id );

			// Collect correct answer IDs
			$correct_ids = [];
			foreach ( $all_answers as $ans ) {
				if ( $ans->is_correct ) {
					$correct_ids[] = (int) $ans->id;
				}
			}

			// Normalise user response to array of ints
			$raw = $responses[ $q->id ] ?? [];
			if ( ! is_array( $raw ) ) {
				$raw = $raw ? [ $raw ] : [];
			}
			$user_ids = array_map( 'absint', array_filter( $raw ) );

			// Validate that provided answer IDs actually belong to this question
			$valid_ids = array_map( function( $a ) { return (int) $a->id; }, $all_answers );
			$user_ids  = array_values( array_filter( $user_ids, function( $id ) use ( $valid_ids ) {
				return in_array( $id, $valid_ids, true );
			} ) );

			// A question is correct if the selected set exactly matches the correct set
			$is_correct = ! empty( $user_ids )
				&& count( $user_ids ) === count( $correct_ids )
				&& empty( array_diff( $user_ids, $correct_ids ) )
				&& empty( array_diff( $correct_ids, $user_ids ) );

			if ( $is_correct ) $correct++;

			$snapshot[] = [
				'question_id'      => (int) $q->id,
				'question_text'    => $q->question_text,
				'type'             => $q->question_type,
				'user_answer_ids'  => $user_ids,
				'correct_answer_ids' => $correct_ids,
				'answers'          => array_map( function( $a ) {
					return [
						'id'         => (int) $a->id,
						'text'       => $a->answer_text,
						'is_correct' => (bool) $a->is_correct,
					];
				}, $all_answers ),
				'explanation'      => $q->explanation ?? '',
				'is_correct'       => $is_correct,
			];
		}

		$score  = $total > 0 ? (int) round( ( $correct / $total ) * 100 ) : 0;
		$passed = $score >= (int) $quiz->pass_threshold;

		$wpdb->insert( $wpdb->prefix . 'kt_quiz_results', [
			'member_id'       => absint( $member_id ),
			'quiz_id'         => absint( $quiz_id ),
			'score'           => $score,
			'passed'          => $passed ? 1 : 0,
			'answers_snapshot' => wp_json_encode( $snapshot ),
		] );

		return compact( 'score', 'passed', 'correct', 'total', 'snapshot' );
	}

	/**
	 * Retorna o último resultado de um colaborador em uma avaliação, incluindo answers_snapshot.
	 *
	 * @param int $member_id
	 * @param int $quiz_id
	 * @return object|null
	 */
	public static function get_last_result( $member_id, $quiz_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}kt_quiz_results
			 WHERE member_id = %d AND quiz_id = %d
			 ORDER BY attempt_date DESC LIMIT 1",
			$member_id, $quiz_id
		) );
	}

	public static function attempt_count( $member_id, $quiz_id ) {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}kt_quiz_results WHERE member_id = %d AND quiz_id = %d",
			$member_id, $quiz_id
		) );
	}

	public static function best_result( $member_id, $quiz_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}kt_quiz_results
			 WHERE member_id = %d AND quiz_id = %d AND passed = 1
			 ORDER BY score DESC LIMIT 1",
			$member_id, $quiz_id
		) );
	}

	/**
	 * Apaga todas as tentativas de um colaborador em uma avaliação,
	 * permitindo que ele tente novamente do zero.
	 */
	public static function reset_attempts( $member_id, $quiz_id ) {
		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'kt_quiz_results', [
			'member_id' => absint( $member_id ),
			'quiz_id'   => absint( $quiz_id ),
		] );
	}

	/**
	 * Importa perguntas a partir de um arquivo CSV e as adiciona à avaliação.
	 *
	 * Formato esperado (separador ; ou ,):
	 * PERGUNTA ; TIPO ; ALTERNATIVA_A ; ALTERNATIVA_B ; ALTERNATIVA_C ; ALTERNATIVA_D ; CORRETA
	 *
	 * TIPO  : MC (Múltipla Escolha) ou VF (Verdadeiro/Falso)
	 * CORRETA: A, B, C ou D
	 *
	 * Para perguntas V/F basta preencher ALTERNATIVA_A (Verdadeiro) e ALTERNATIVA_B (Falso).
	 *
	 * @return array { created, errors[] }
	 */
	public static function import_questions_csv( $file_path, $quiz_id ) {
		$quiz_id = absint( $quiz_id );
		$result  = [ 'created' => 0, 'errors' => [] ];

		$handle = fopen( $file_path, 'r' );
		if ( ! $handle ) {
			$result['errors'][] = 'Não foi possível abrir o arquivo.';
			return $result;
		}

		// Detecta separador
		$first = fgets( $handle );
		rewind( $handle );
		$sep = ( substr_count( $first, ';' ) >= substr_count( $first, ',' ) ) ? ';' : ',';

		// Lê e normaliza cabeçalho
		$bom    = "\xEF\xBB\xBF";
		$header = fgetcsv( $handle, 0, $sep );
		if ( $header && strpos( $header[0], $bom ) === 0 ) {
			$header[0] = substr( $header[0], 3 );
		}
		$header = array_map( function( $h ) {
			return mb_strtoupper( trim( $h ), 'UTF-8' );
		}, $header );

		// Índices de colunas
		$col_pergunta  = array_search( 'PERGUNTA',      $header );
		$col_tipo      = array_search( 'TIPO',          $header );
		$col_a         = array_search( 'ALTERNATIVA_A', $header );
		$col_b         = array_search( 'ALTERNATIVA_B', $header );
		$col_c         = array_search( 'ALTERNATIVA_C', $header );
		$col_d         = array_search( 'ALTERNATIVA_D', $header );
		$col_correta   = array_search( 'CORRETA',       $header );
		$col_explicacao = array_search( 'EXPLICACAO',   $header ); // optional

		if ( $col_pergunta === false || $col_correta === false ) {
			$result['errors'][] = 'O arquivo precisa ter as colunas PERGUNTA e CORRETA no mínimo.';
			fclose( $handle );
			return $result;
		}

		// Próxima ordem disponível para as perguntas
		$sort_base = count( self::get_questions( $quiz_id ) );
		$line = 1;

		while ( ( $row = fgetcsv( $handle, 0, $sep ) ) !== false ) {
			$line++;
			$pergunta = trim( $row[ $col_pergunta ] ?? '' );
			if ( empty( $pergunta ) ) continue;

			$tipo_raw = mb_strtoupper( trim( $row[ $col_tipo ] ?? 'MC' ), 'UTF-8' );
			if ( $tipo_raw === 'VF' || $tipo_raw === 'VERDADEIRO/FALSO' ) {
				$tipo = 'true_false';
			} elseif ( $tipo_raw === 'MS' || $tipo_raw === 'MULTIPLA_SELECAO' ) {
				$tipo = 'multiple_select';
			} else {
				$tipo = 'multiple_choice';
			}

			$alts = [
				'A' => trim( $row[ $col_a ] ?? '' ),
				'B' => trim( $row[ $col_b ] ?? '' ),
				'C' => trim( $row[ $col_c ] ?? '' ),
				'D' => trim( $row[ $col_d ] ?? '' ),
			];
			$correta = mb_strtoupper( trim( $row[ $col_correta ] ?? '' ), 'UTF-8' );

			// Para V/F, preenche padrão se não fornecido
			if ( $tipo === 'true_false' ) {
				if ( empty( $alts['A'] ) ) $alts['A'] = 'Verdadeiro';
				if ( empty( $alts['B'] ) ) $alts['B'] = 'Falso';
				$alts['C'] = '';
				$alts['D'] = '';
			}

			// CORRETA pode ter múltiplos valores separados por | para multiple_select (ex: "A|C")
			$corretas_raw = mb_strtoupper( trim( $row[ $col_correta ] ?? '' ), 'UTF-8' );
			$corretas     = array_filter( array_map( 'trim', explode( '|', $corretas_raw ) ) );

			if ( empty( $corretas ) ) {
				$result['errors'][] = "Linha $line: CORRETA deve ser A, B, C, D ou combinação com | — pulada.";
				continue;
			}
			foreach ( $corretas as $c ) {
				if ( ! in_array( $c, [ 'A', 'B', 'C', 'D' ], true ) ) {
					$result['errors'][] = "Linha $line: CORRETA contém valor inválido '$c' — pulada.";
					continue 2;
				}
				if ( empty( $alts[ $c ] ) ) {
					$result['errors'][] = "Linha $line: alternativa correta ($c) está vazia — pulada.";
					continue 2;
				}
			}

			// Se múltiplas corretas e tipo não explicitado, força multiple_select
			if ( count( $corretas ) > 1 && $tipo === 'multiple_choice' ) {
				$tipo = 'multiple_select';
			}

			$explicacao = ( $col_explicacao !== false ) ? trim( $row[ $col_explicacao ] ?? '' ) : '';

			$q_id = self::add_question( [
				'quiz_id'       => $quiz_id,
				'question_text' => $pergunta,
				'question_type' => $tipo,
				'sort_order'    => $sort_base + $result['created'],
				'explanation'   => $explicacao,
			] );

			$answers = [];
			foreach ( [ 'A', 'B', 'C', 'D' ] as $letra ) {
				if ( empty( $alts[ $letra ] ) ) continue;
				$answers[] = [
					'text'       => $alts[ $letra ],
					'is_correct' => in_array( $letra, $corretas, true ) ? 1 : 0,
				];
			}
			self::save_answers( $q_id, $answers );
			$result['created']++;
		}

		fclose( $handle );
		return $result;
	}
}
