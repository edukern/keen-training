<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KT_Course {

	/* -----------------------------------------------------------------------
	 * Cursos
	 * -------------------------------------------------------------------- */

	public static function get_all() {
		global $wpdb;
		return $wpdb->get_results(
			"SELECT c.*, COUNT(m.id) AS module_count
			 FROM {$wpdb->prefix}kt_courses c
			 LEFT JOIN {$wpdb->prefix}kt_modules m ON m.course_id = c.id
			 GROUP BY c.id
			 ORDER BY c.title ASC"
		);
	}

	public static function get( $id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}kt_courses WHERE id = %d", $id
		) );
	}

	public static function create( $data ) {
		global $wpdb;
		$wpdb->insert( $wpdb->prefix . 'kt_courses', [
			'title'         => sanitize_text_field( $data['title'] ),
			'description'   => sanitize_textarea_field( $data['description'] ?? '' ),
			'passing_score' => absint( $data['passing_score'] ?? 70 ),
		] );
		return $wpdb->insert_id;
	}

	public static function update( $id, $data ) {
		global $wpdb;
		$wpdb->update(
			$wpdb->prefix . 'kt_courses',
			[
				'title'         => sanitize_text_field( $data['title'] ),
				'description'   => sanitize_textarea_field( $data['description'] ?? '' ),
				'passing_score' => absint( $data['passing_score'] ?? 70 ),
			],
			[ 'id' => absint( $id ) ]
		);
	}

	public static function delete( $id ) {
		global $wpdb;
		$id = absint( $id );
		foreach ( self::get_modules( $id ) as $m ) {
			self::delete_module( $m->id );
		}
		KT_Restriction::save( $id, [], [] ); // remove restrições
		$wpdb->delete( $wpdb->prefix . 'kt_courses', [ 'id' => $id ] );
	}

	/* -----------------------------------------------------------------------
	 * Módulos
	 * -------------------------------------------------------------------- */

	public static function get_modules( $course_id ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}kt_modules
			 WHERE course_id = %d ORDER BY sort_order ASC, id ASC",
			$course_id
		) );
	}

	public static function get_module( $id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}kt_modules WHERE id = %d", $id
		) );
	}

	public static function add_module( $data ) {
		global $wpdb;
		$page_id    = ! empty( $data['page_id'] ) ? absint( $data['page_id'] ) : null;
		$url        = $page_id ? '' : esc_url_raw( $data['content_url'] ?? '' );
		$embed_type = $page_id ? 'page' : ( empty( $data['embed_type'] ) ? self::detect_embed_type( $url ) : sanitize_key( $data['embed_type'] ) );

		$wpdb->insert( $wpdb->prefix . 'kt_modules', [
			'course_id'   => absint( $data['course_id'] ),
			'title'       => sanitize_text_field( $data['title'] ),
			'description' => sanitize_textarea_field( $data['description'] ?? '' ),
			'content_url' => $url,
			'embed_type'  => $embed_type,
			'page_id'     => $page_id,
			'sort_order'  => absint( $data['sort_order'] ?? 0 ),
		] );
		return $wpdb->insert_id;
	}

	public static function update_module( $id, $data ) {
		global $wpdb;
		$page_id    = ! empty( $data['page_id'] ) ? absint( $data['page_id'] ) : null;
		$url        = $page_id ? '' : esc_url_raw( $data['content_url'] ?? '' );
		$embed_type = $page_id ? 'page' : ( empty( $data['embed_type'] ) ? self::detect_embed_type( $url ) : sanitize_key( $data['embed_type'] ) );

		$wpdb->update(
			$wpdb->prefix . 'kt_modules',
			[
				'title'       => sanitize_text_field( $data['title'] ),
				'description' => sanitize_textarea_field( $data['description'] ?? '' ),
				'content_url' => $url,
				'embed_type'  => $embed_type,
				'page_id'     => $page_id,
				'sort_order'  => absint( $data['sort_order'] ?? 0 ),
			],
			[ 'id' => absint( $id ) ]
		);
	}

	/** Retorna o módulo vinculado a uma página WP específica, ou null. */
	public static function get_module_by_page( $page_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}kt_modules WHERE page_id = %d LIMIT 1",
			absint( $page_id )
		) );
	}

	public static function delete_module( $id ) {
		global $wpdb;
		$id = absint( $id );
		$wpdb->update( $wpdb->prefix . 'kt_quizzes', [ 'module_id' => null ], [ 'module_id' => $id ] );
		$wpdb->delete( $wpdb->prefix . 'kt_modules', [ 'id' => $id ] );
	}

	/* -----------------------------------------------------------------------
	 * Detecção e renderização de embeds
	 * -------------------------------------------------------------------- */

	public static function detect_embed_type( $url ) {
		if ( preg_match( '/youtube\.com|youtu\.be/', $url ) )  return 'youtube';
		if ( strpos( $url, 'vimeo.com' ) !== false )           return 'vimeo';
		if ( strpos( $url, 'drive.google.com' ) !== false )    return 'google_drive';
		if ( preg_match( '/\.(mp4|webm|ogg)$/i', $url ) )      return 'video';
		if ( preg_match( '/\.pdf$/i', $url ) )                  return 'pdf';
		return 'link';
	}

	public static function render_embed( $module ) {
		$url  = esc_url( $module->content_url );
		$type = $module->embed_type;

		if ( $type === 'youtube' ) {
			preg_match( '/(?:v=|youtu\.be\/)([A-Za-z0-9_-]{11})/', $module->content_url, $m );
			$vid = $m[1] ?? '';
			if ( $vid ) {
				return '<div class="kt-embed-wrap"><iframe src="https://www.youtube.com/embed/' . esc_attr( $vid ) . '?rel=0" frameborder="0" allowfullscreen></iframe></div>';
			}
		}

		if ( $type === 'vimeo' ) {
			preg_match( '/vimeo\.com\/(\d+)/', $module->content_url, $m );
			$vid = $m[1] ?? '';
			if ( $vid ) {
				return '<div class="kt-embed-wrap"><iframe src="https://player.vimeo.com/video/' . esc_attr( $vid ) . '" frameborder="0" allowfullscreen></iframe></div>';
			}
		}

		if ( $type === 'google_drive' ) {
			$embed_url = preg_replace( '/\/view(\?.*)?$/', '/preview', $module->content_url );
			return '<div class="kt-embed-wrap"><iframe src="' . esc_url( $embed_url ) . '" frameborder="0" allowfullscreen></iframe></div>';
		}

		if ( $type === 'video' ) {
			return '<div class="kt-embed-wrap"><video controls><source src="' . $url . '"></video></div>';
		}

		if ( $type === 'pdf' ) {
			return '<div class="kt-embed-wrap"><iframe src="' . $url . '#toolbar=0" type="application/pdf"></iframe></div>';
		}

		return '<p class="kt-content-link"><a href="' . $url . '" target="_blank" rel="noopener noreferrer" class="kt-btn kt-btn-primary">📄 Abrir Material de Treinamento</a></p>';
	}
}
