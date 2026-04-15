<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * KT_Restriction — Controle de acesso a cursos por unidade e/ou cargo.
 *
 * Se um curso não tiver nenhuma restrição cadastrada, fica disponível para todos.
 * Se tiver restrições, apenas as unidades/cargos listados têm acesso.
 */
class KT_Restriction {

	/**
	 * Salva as restrições de um curso (substitui as existentes).
	 *
	 * @param int   $course_id
	 * @param array $location_ids  IDs de unidades permitidas (vazio = sem restrição por unidade)
	 * @param array $roles         Slugs de papéis permitidos  (vazio = sem restrição por cargo)
	 */
	public static function save( $course_id, $location_ids = [], $roles = [], $position_ids = [] ) {
		global $wpdb;
		$course_id = absint( $course_id );

		// Apaga restrições anteriores
		$wpdb->delete( $wpdb->prefix . 'kt_course_restrictions', [ 'course_id' => $course_id ] );

		foreach ( (array) $location_ids as $loc_id ) {
			$loc_id = absint( $loc_id );
			if ( ! $loc_id ) continue;
			$wpdb->insert( $wpdb->prefix . 'kt_course_restrictions', [
				'course_id'         => $course_id,
				'restriction_type'  => 'location',
				'restriction_value' => (string) $loc_id,
			] );
		}

		foreach ( (array) $roles as $role ) {
			$role = sanitize_key( $role );
			if ( ! $role ) continue;
			$wpdb->insert( $wpdb->prefix . 'kt_course_restrictions', [
				'course_id'         => $course_id,
				'restriction_type'  => 'role',
				'restriction_value' => $role,
			] );
		}

		foreach ( (array) $position_ids as $pos_id ) {
			$pos_id = absint( $pos_id );
			if ( ! $pos_id ) continue;
			$wpdb->insert( $wpdb->prefix . 'kt_course_restrictions', [
				'course_id'         => $course_id,
				'restriction_type'  => 'position',
				'restriction_value' => (string) $pos_id,
			] );
		}
	}

	public static function get_position_ids( $course_id ) {
		global $wpdb;
		$rows = $wpdb->get_col( $wpdb->prepare(
			"SELECT restriction_value FROM {$wpdb->prefix}kt_course_restrictions
			 WHERE course_id = %d AND restriction_type = 'position'",
			$course_id
		) );
		return array_map( 'intval', $rows );
	}

	/** Retorna as unidades permitidas para um curso (array de IDs). Vazio = sem restrição. */
	public static function get_location_ids( $course_id ) {
		global $wpdb;
		$rows = $wpdb->get_col( $wpdb->prepare(
			"SELECT restriction_value FROM {$wpdb->prefix}kt_course_restrictions
			 WHERE course_id = %d AND restriction_type = 'location'",
			$course_id
		) );
		return array_map( 'intval', $rows );
	}

	/** Retorna os papéis permitidos para um curso (array de slugs). Vazio = sem restrição. */
	public static function get_roles( $course_id ) {
		global $wpdb;
		return $wpdb->get_col( $wpdb->prepare(
			"SELECT restriction_value FROM {$wpdb->prefix}kt_course_restrictions
			 WHERE course_id = %d AND restriction_type = 'role'",
			$course_id
		) );
	}

	/**
	 * Verifica se um colaborador pode acessar um curso.
	 * Regra: se o curso não tem restrições → pode. Se tem → precisa estar na lista.
	 *
	 * @param int $member_id
	 * @param int $course_id
	 * @return bool
	 */
	public static function member_can_access( $member_id, $course_id ) {
		global $wpdb;

		$total_restrictions = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}kt_course_restrictions WHERE course_id = %d", $course_id
		) );

		// Sem restrições → acesso livre
		if ( $total_restrictions === 0 ) return true;

		$member = KT_Member::get( $member_id );
		if ( ! $member ) return false;

		$allowed_locations = self::get_location_ids( $course_id );
		$allowed_roles     = self::get_roles( $course_id );
		$allowed_positions = self::get_position_ids( $course_id );

		// Verifica unidade
		if ( $allowed_locations && in_array( (int) $member->location_id, $allowed_locations, true ) ) {
			return true;
		}

		// Verifica função personalizada
		if ( $allowed_positions && $member->position_id && in_array( (int) $member->position_id, $allowed_positions, true ) ) {
			return true;
		}

		// Verifica perfil WordPress (compatibilidade)
		if ( $allowed_roles ) {
			$user = get_user_by( 'id', $member->user_id );
			if ( $user ) {
				foreach ( $allowed_roles as $role ) {
					if ( in_array( $role, $user->roles, true ) ) return true;
				}
			}
		}

		return false;
	}

	/**
	 * Filtra um array de IDs de cursos, retornando apenas os que o colaborador pode acessar.
	 */
	public static function filter_courses_for_member( $member_id, $course_ids ) {
		return array_values( array_filter( $course_ids, function( $cid ) use ( $member_id ) {
			return self::member_can_access( $member_id, $cid );
		} ) );
	}

	/**
	 * Retorna texto descritivo das restrições de um curso para exibição no admin.
	 */
	public static function describe( $course_id ) {
		$locs  = self::get_location_ids( $course_id );
		$roles = self::get_roles( $course_id );

		if ( ! $locs && ! $roles ) return 'Livre (sem restrição)';

		$parts = [];

		if ( $locs ) {
			$names = [];
			foreach ( $locs as $lid ) {
				$loc = KT_Location::get( $lid );
				$names[] = $loc ? esc_html( $loc->name ) : "#$lid";
			}
			$parts[] = 'Unidades: ' . implode( ', ', $names );
		}
		$positions = self::get_position_ids( $course_id );
		if ( $positions ) {
			$names = [];
			foreach ( $positions as $pid ) {
				$pos = KT_Position::get( $pid );
				$names[] = $pos ? esc_html( $pos->name ) : "#$pid";
			}
			$parts[] = 'Funções: ' . implode( ', ', $names );
		}
		if ( $roles ) {
			$labels = array_map( [ 'KT_Roles', 'role_label' ], $roles );
			$parts[] = 'Perfis WP: ' . implode( ', ', $labels );
		}
		return implode( ' | ', $parts );
	}
}
