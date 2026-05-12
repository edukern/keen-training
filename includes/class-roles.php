<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KT_Roles {

	public static function register() {
		// kt_super_admin foi unificado com kt_admin na v2.4.2.
		// O bloco add_role foi removido; a migração converte usuários existentes.

		// Administrador do plugin: acesso total ao KT + gestão de usuários WP
		// (o WP exibe o menu Usuários automaticamente com essas caps)
		add_role( 'kt_admin', 'Keen Training — Administrador', [
			'read'                   => true,
			// Capacidades Keen Training
			'kt_manage_locations'    => true,
			'kt_manage_members'      => true,
			'kt_manage_courses'      => true,
			'kt_manage_quizzes'      => true,
			'kt_manage_enrollments'  => true,
			'kt_view_all_progress'   => true,
			'kt_manage_certificates' => true,
			'kt_manage_restrictions' => true,
			// Gestão de usuários WordPress
			'list_users'             => true,
			'create_users'           => true,
			'edit_users'             => true,
			'promote_users'          => true,
		] );

		add_role( 'kt_location_manager', 'Keen Training — Gerente de Unidade', [
			'read'                   => true,
			'kt_manage_members'      => true,
			'kt_manage_enrollments'  => true,
			'kt_view_own_progress'   => true,
		] );

		add_role( 'kt_staff', 'Keen Training — Colaborador', [
			'read'                  => true,
			'kt_view_own_training'  => true,
		] );
	}

	public static function remove() {
		remove_role( 'kt_super_admin' ); // legado — pode não existir
		remove_role( 'kt_admin' );
		remove_role( 'kt_location_manager' );
		remove_role( 'kt_staff' );
	}

	/** Retorna true se o usuário atual pode gerenciar determinada unidade */
	public static function can_manage_location( $location_id ) {
		$user = wp_get_current_user();
		if ( self::is_super_admin() ) return true;
		if ( in_array( 'kt_location_manager', $user->roles, true ) ) {
			return (int) get_user_meta( $user->ID, 'kt_location_id', true ) === (int) $location_id;
		}
		return false;
	}

	/** ID da unidade vinculada ao usuário atual */
	public static function current_user_location_id() {
		return (int) get_user_meta( get_current_user_id(), 'kt_location_id', true );
	}

	/**
	 * Acesso total ao Keen Training.
	 * Inclui: kt_admin, kt_super_admin (legado), administrator WP.
	 */
	public static function is_super_admin() {
		$user = wp_get_current_user();
		return in_array( 'kt_admin', $user->roles, true )
			|| in_array( 'kt_super_admin', $user->roles, true ) // legado
			|| current_user_can( 'administrator' );
	}

	/** Administrador do plugin: super admin + pode criar/editar usuários WP */
	public static function is_kt_admin() {
		$user = wp_get_current_user();
		return in_array( 'kt_admin', $user->roles, true )
			|| current_user_can( 'administrator' );
	}

	public static function is_location_manager() {
		return in_array( 'kt_location_manager', wp_get_current_user()->roles, true );
	}

	public static function is_staff() {
		return in_array( 'kt_staff', wp_get_current_user()->roles, true );
	}

	/** Retorna o nome amigável de um cargo/papel */
	public static function role_label( $role ) {
		$labels = [
			'kt_admin'             => 'Administrador',
			'kt_super_admin'       => 'Administrador (legado)', // migrado para kt_admin
			'kt_location_manager'  => 'Gerente de Unidade',
			'kt_staff'             => 'Colaborador',
			'administrator'        => 'Administrador WordPress',
		];
		return $labels[ $role ] ?? $role;
	}
}
