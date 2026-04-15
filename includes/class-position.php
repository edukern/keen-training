<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * KT_Position — Funções personalizadas de colaboradores.
 *
 * Funciona como uma taxonomia própria do plugin: Vendas, Marketing,
 * Administrativo, Caixa, etc. Usada para vincular colaboradores e
 * para restrição de acesso a cursos.
 */
class KT_Position {

	public static function get_all() {
		global $wpdb;
		return $wpdb->get_results(
			"SELECT p.*, COUNT(m.id) AS member_count
			 FROM {$wpdb->prefix}kt_positions p
			 LEFT JOIN {$wpdb->prefix}kt_members m ON m.position_id = p.id
			 GROUP BY p.id
			 ORDER BY p.name ASC"
		);
	}

	public static function get( $id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}kt_positions WHERE id = %d", absint( $id )
		) );
	}

	public static function get_by_slug( $slug ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}kt_positions WHERE slug = %s", sanitize_key( $slug )
		) );
	}

	/** @return int|WP_Error */
	public static function create( $data ) {
		global $wpdb;

		$name = sanitize_text_field( $data['name'] ?? '' );
		if ( ! $name ) return new WP_Error( 'missing_name', 'O nome da função é obrigatório.' );

		$slug = sanitize_key( $data['slug'] ?? $name );
		if ( ! $slug ) $slug = sanitize_key( remove_accents( $name ) );

		// Garante slug único
		$base = $slug;
		$i    = 1;
		while ( self::get_by_slug( $slug ) ) {
			$slug = $base . '-' . $i++;
		}

		$wpdb->insert( $wpdb->prefix . 'kt_positions', [
			'name'        => $name,
			'slug'        => $slug,
			'description' => sanitize_textarea_field( $data['description'] ?? '' ),
			'color'       => sanitize_hex_color( $data['color'] ?? '' ) ?: '#64748b',
		] );
		return $wpdb->insert_id;
	}

	public static function update( $id, $data ) {
		global $wpdb;
		$name = sanitize_text_field( $data['name'] ?? '' );
		if ( ! $name ) return false;

		$wpdb->update(
			$wpdb->prefix . 'kt_positions',
			[
				'name'        => $name,
				'description' => sanitize_textarea_field( $data['description'] ?? '' ),
				'color'       => sanitize_hex_color( $data['color'] ?? '' ) ?: '#64748b',
			],
			[ 'id' => absint( $id ) ]
		);
		return true;
	}

	/** Remove a função. Colaboradores vinculados ficam sem função (position_id = NULL). */
	public static function delete( $id ) {
		global $wpdb;
		$id = absint( $id );
		$wpdb->update( $wpdb->prefix . 'kt_members', [ 'position_id' => null ], [ 'position_id' => $id ] );
		$wpdb->delete( $wpdb->prefix . 'kt_positions', [ 'id' => $id ] );
	}

	/** Retorna nome da função ou '—' se não vinculada. */
	public static function label( $position_id ) {
		if ( ! $position_id ) return '—';
		$pos = self::get( $position_id );
		return $pos ? esc_html( $pos->name ) : '—';
	}
}
