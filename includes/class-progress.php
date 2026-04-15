<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KT_Progress {

	/* -----------------------------------------------------------------------
	 * Matrículas
	 * -------------------------------------------------------------------- */

	/** Matricula membros em um curso. Verifica restrição de acesso. */
	public static function enroll( $member_ids, $course_id, $due_date = null, $assigned_by = 0 ) {
		global $wpdb;
		$course_id   = absint( $course_id );
		$assigned_by = $assigned_by ?: get_current_user_id();

		foreach ( (array) $member_ids as $member_id ) {
			$member_id = absint( $member_id );
			if ( ! $member_id ) continue;

			// Verifica acesso pela política de restrição
			if ( ! KT_Restriction::member_can_access( $member_id, $course_id ) ) continue;

			// Ignora se já matriculado
			$exists = $wpdb->get_var( $wpdb->prepare(
				"SELECT id FROM {$wpdb->prefix}kt_enrollments WHERE member_id = %d AND course_id = %d",
				$member_id, $course_id
			) );
			if ( $exists ) continue;

			$wpdb->insert( $wpdb->prefix . 'kt_enrollments', [
				'member_id'   => $member_id,
				'course_id'   => $course_id,
				'assigned_by' => $assigned_by,
				'due_date'    => $due_date ?: null,
				'status'      => 'nao_iniciado',
			] );
		}
	}

	/** Matricula todos os colaboradores de uma unidade. */
	public static function enroll_location( $location_id, $course_id, $due_date = null ) {
		global $wpdb;
		$members = $wpdb->get_col( $wpdb->prepare(
			"SELECT id FROM {$wpdb->prefix}kt_members WHERE location_id = %d", $location_id
		) );
		self::enroll( $members, $course_id, $due_date );
	}

	public static function unenroll( $member_id, $course_id ) {
		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'kt_enrollments', [
			'member_id' => absint( $member_id ),
			'course_id' => absint( $course_id ),
		] );
	}

	public static function get_enrollments_for_member( $member_id ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT e.*, c.title AS course_title, c.passing_score
			 FROM {$wpdb->prefix}kt_enrollments e
			 JOIN {$wpdb->prefix}kt_courses c ON c.id = e.course_id
			 WHERE e.member_id = %d
			 ORDER BY e.assigned_date DESC",
			$member_id
		) );
	}

	public static function get_enrollment( $member_id, $course_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}kt_enrollments WHERE member_id = %d AND course_id = %d",
			$member_id, $course_id
		) );
	}

	/* -----------------------------------------------------------------------
	 * Conclusão de módulos
	 * -------------------------------------------------------------------- */

	public static function is_module_complete( $member_id, $module_id ) {
		global $wpdb;
		return (bool) $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM {$wpdb->prefix}kt_progress WHERE member_id = %d AND module_id = %d",
			$member_id, $module_id
		) );
	}

	public static function mark_module_complete( $member_id, $module_id ) {
		global $wpdb;
		$member_id = absint( $member_id );
		$module_id = absint( $module_id );

		if ( self::is_module_complete( $member_id, $module_id ) ) return;

		$wpdb->insert( $wpdb->prefix . 'kt_progress', [
			'member_id' => $member_id,
			'module_id' => $module_id,
		] );

		$module = KT_Course::get_module( $module_id );
		if ( $module ) {
			self::maybe_complete_course( $member_id, (int) $module->course_id );
		}
	}

	/* -----------------------------------------------------------------------
	 * Conclusão de cursos
	 * -------------------------------------------------------------------- */

	private static function maybe_complete_course( $member_id, $course_id ) {
		global $wpdb;

		$modules = KT_Course::get_modules( $course_id );
		$total   = count( $modules );
		if ( ! $total ) return;

		$done = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}kt_progress p
			 JOIN {$wpdb->prefix}kt_modules m ON m.id = p.module_id
			 WHERE p.member_id = %d AND m.course_id = %d",
			$member_id, $course_id
		) );

		if ( $done >= $total ) {
			$wpdb->update(
				$wpdb->prefix . 'kt_enrollments',
				[ 'status' => 'concluido', 'completed_at' => current_time( 'mysql' ) ],
				[ 'member_id' => $member_id, 'course_id' => $course_id ]
			);
			KT_Certificate::issue( $member_id, $course_id );
		} else {
			$wpdb->query( $wpdb->prepare(
				"UPDATE {$wpdb->prefix}kt_enrollments
				 SET status = 'em_andamento'
				 WHERE member_id = %d AND course_id = %d AND status = 'nao_iniciado'",
				$member_id, $course_id
			) );
		}
	}

	/* -----------------------------------------------------------------------
	 * Estatísticas
	 * -------------------------------------------------------------------- */

	public static function course_progress_pct( $member_id, $course_id ) {
		global $wpdb;
		$total = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}kt_modules WHERE course_id = %d", $course_id
		) );
		if ( ! $total ) return 0;
		$done = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}kt_progress p
			 JOIN {$wpdb->prefix}kt_modules m ON m.id = p.module_id
			 WHERE p.member_id = %d AND m.course_id = %d",
			$member_id, $course_id
		) );
		return (int) round( ( $done / $total ) * 100 );
	}

	public static function location_stats( $location_id ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT m.id AS member_id, u.display_name, e.course_id, c.title AS course_title,
			        e.status, e.due_date, e.completed_at, p.name AS position_name
			 FROM {$wpdb->prefix}kt_members m
			 JOIN {$wpdb->users} u ON u.ID = m.user_id
			 JOIN {$wpdb->prefix}kt_enrollments e ON e.member_id = m.id
			 JOIN {$wpdb->prefix}kt_courses c ON c.id = e.course_id
			 LEFT JOIN {$wpdb->prefix}kt_positions p ON p.id = m.position_id
			 WHERE m.location_id = %d
			 ORDER BY u.display_name ASC, c.title ASC",
			$location_id
		) );
	}

	public static function course_stats( $course_id ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT m.id AS member_id, u.display_name, l.name AS location_name,
			        p.name AS position_name, e.status, e.due_date, e.completed_at,
			        (SELECT MAX(qr.score) FROM {$wpdb->prefix}kt_quiz_results qr
			         JOIN {$wpdb->prefix}kt_quizzes q ON q.id = qr.quiz_id
			         WHERE qr.member_id = m.id
			           AND (q.course_id = e.course_id
			                OR q.module_id IN (SELECT id FROM {$wpdb->prefix}kt_modules WHERE course_id = e.course_id))
			        ) AS best_quiz_score
			 FROM {$wpdb->prefix}kt_enrollments e
			 JOIN {$wpdb->prefix}kt_members m ON m.id = e.member_id
			 JOIN {$wpdb->users} u ON u.ID = m.user_id
			 LEFT JOIN {$wpdb->prefix}kt_locations l ON l.id = m.location_id
			 LEFT JOIN {$wpdb->prefix}kt_positions p ON p.id = m.position_id
			 WHERE e.course_id = %d
			 ORDER BY u.display_name ASC",
			$course_id
		) );
	}

	public static function export_csv_rows( $location_id = 0 ) {
		global $wpdb;
		$where = $location_id ? $wpdb->prepare( 'WHERE m.location_id = %d', $location_id ) : '';
		return $wpdb->get_results(
			"SELECT u.display_name AS 'Nome', u.user_email AS 'E-mail',
			        l.name AS 'Unidade', p.name AS 'Função',
			        c.title AS 'Curso', e.status AS 'Status',
			        e.due_date AS 'Prazo', e.completed_at AS 'Concluído em'
			 FROM {$wpdb->prefix}kt_enrollments e
			 JOIN {$wpdb->prefix}kt_members m ON m.id = e.member_id
			 JOIN {$wpdb->users} u ON u.ID = m.user_id
			 LEFT JOIN {$wpdb->prefix}kt_locations l ON l.id = m.location_id
			 LEFT JOIN {$wpdb->prefix}kt_positions p ON p.id = m.position_id
			 JOIN {$wpdb->prefix}kt_courses c ON c.id = e.course_id
			 $where
			 ORDER BY l.name ASC, u.display_name ASC, c.title ASC",
			ARRAY_A
		);
	}

	/* Status labels em PT-BR */
	public static function status_label( $status ) {
		$labels = [
			'nao_iniciado' => 'Não Iniciado',
			'em_andamento' => 'Em Andamento',
			'concluido'    => 'Concluído',
		];
		return $labels[ $status ] ?? ucfirst( str_replace( '_', ' ', $status ) );
	}
}
