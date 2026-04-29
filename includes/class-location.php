<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KT_Location {

	public static function get_all() {
		global $wpdb;
		return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}kt_locations ORDER BY name ASC" );
	}

	public static function get( $id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}kt_locations WHERE id = %d", $id
		) );
	}

	public static function create( $data ) {
		global $wpdb;
		$wpdb->insert( $wpdb->prefix . 'kt_locations', [
			'name'       => sanitize_text_field( $data['name'] ),
			'address'    => sanitize_textarea_field( $data['address'] ?? '' ),
			'manager_id' => absint( $data['manager_id'] ?? 0 ),
		] );
		$location_id = $wpdb->insert_id;
		if ( ! empty( $data['manager_id'] ) ) {
			$manager_id = absint( $data['manager_id'] );
			update_user_meta( $manager_id, 'kt_location_id', $location_id );
			$user = new WP_User( $manager_id );
			if ( ! in_array( 'administrator', $user->roles, true ) && ! in_array( 'kt_location_manager', $user->roles, true ) && ! in_array( 'kt_super_admin', $user->roles, true ) ) {
				$user->add_role( 'kt_location_manager' );
			}
		}
		return $location_id;
	}

	public static function update( $id, $data ) {
		global $wpdb;
		$id          = absint( $id );
		$manager_id  = absint( $data['manager_id'] ?? 0 );

		$wpdb->update(
			$wpdb->prefix . 'kt_locations',
			[
				'name'       => sanitize_text_field( $data['name'] ),
				'address'    => sanitize_textarea_field( $data['address'] ?? '' ),
				'manager_id' => $manager_id,
			],
			[ 'id' => $id ]
		);

		if ( $manager_id ) {
			// Atualiza meta do gerente com a unidade
			update_user_meta( $manager_id, 'kt_location_id', $id );
			$user = new WP_User( $manager_id );
			// Garante que tem o papel de gerente se não for admin
			if ( ! in_array( 'administrator', $user->roles, true ) && ! in_array( 'kt_location_manager', $user->roles, true ) && ! in_array( 'kt_super_admin', $user->roles, true ) ) {
				$user->add_role( 'kt_location_manager' );
			}
		}
	}

	/**
	 * Percorre todas as unidades e garante que os gerentes têm a role correta.
	 * Útil para corrigir registros criados antes do fix.
	 */
	public static function sync_manager_roles() {
		$fixed = 0;
		foreach ( self::get_all() as $loc ) {
			if ( ! $loc->manager_id ) continue;
			$user = new WP_User( $loc->manager_id );
			if ( ! $user->ID ) continue;
			if ( ! in_array( 'administrator', $user->roles, true ) && ! in_array( 'kt_location_manager', $user->roles, true ) && ! in_array( 'kt_super_admin', $user->roles, true ) ) {
				$user->add_role( 'kt_location_manager' );
				$fixed++;
			}
		}
		return $fixed;
	}

	public static function delete( $id ) {
		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'kt_locations', [ 'id' => absint( $id ) ] );
	}

	public static function get_member_count( $location_id ) {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}kt_members WHERE location_id = %d", $location_id
		) );
	}
}
