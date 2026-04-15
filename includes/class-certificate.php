<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KT_Certificate {

	public static function issue( $member_id, $course_id ) {
		global $wpdb;
		$member_id = absint( $member_id );
		$course_id = absint( $course_id );

		$exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM {$wpdb->prefix}kt_certificates WHERE member_id = %d AND course_id = %d",
			$member_id, $course_id
		) );
		if ( $exists ) return (int) $exists;

		$uid = wp_generate_uuid4();
		$wpdb->insert( $wpdb->prefix . 'kt_certificates', [
			'member_id' => $member_id,
			'course_id' => $course_id,
			'cert_uid'  => $uid,
		] );
		return $wpdb->insert_id;
	}

	public static function get( $member_id, $course_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}kt_certificates WHERE member_id = %d AND course_id = %d",
			$member_id, $course_id
		) );
	}

	public static function get_by_uid( $uid ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}kt_certificates WHERE cert_uid = %s", $uid
		) );
	}

	public static function get_all_for_member( $member_id ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT cert.*, c.title AS course_title
			 FROM {$wpdb->prefix}kt_certificates cert
			 JOIN {$wpdb->prefix}kt_courses c ON c.id = cert.course_id
			 WHERE cert.member_id = %d
			 ORDER BY cert.issued_at DESC",
			$member_id
		) );
	}

	/**
	 * Renderiza o certificado como HTML para impressão.
	 */
	public static function render_html( $cert_uid ) {
		$cert = self::get_by_uid( sanitize_text_field( $cert_uid ) );
		if ( ! $cert ) {
			return '<p style="font-family:sans-serif;text-align:center;margin:60px">Certificado não encontrado.</p>';
		}

		$member       = KT_Member::get( $cert->member_id );
		$course       = KT_Course::get( $cert->course_id );
		$name         = $member ? esc_html( $member->display_name ) : 'Colaborador';
		$course_title = $course  ? esc_html( $course->title ) : 'Treinamento';
		$date         = date_i18n( 'd \d\e F \d\e Y', strtotime( $cert->issued_at ) );
		$company      = esc_html( get_bloginfo( 'name' ) );

		ob_start(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Certificado de Conclusão</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Georgia, 'Times New Roman', serif; background: #f5f0e8; display: flex; flex-direction: column; align-items: center; padding: 40px 20px; min-height: 100vh; }
  .cert {
    width: 100%; max-width: 820px;
    background: #fff;
    border: 10px double #b8860b;
    padding: 60px 70px;
    text-align: center;
    box-shadow: 0 8px 40px rgba(0,0,0,.15);
    position: relative;
  }
  .cert::before, .cert::after {
    content: '';
    position: absolute;
    inset: 8px;
    border: 2px solid #daa520;
    pointer-events: none;
  }
  .cert-company { font-size: .95em; color: #8b6914; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 28px; }
  .cert-seal { font-size: 3.5em; margin-bottom: 12px; }
  .cert h1 { font-size: 2.2em; color: #2c2c2c; letter-spacing: 1px; margin-bottom: 6px; }
  .cert .sub { font-size: 1em; color: #888; margin-bottom: 28px; font-style: italic; }
  .cert .recipient { font-size: 2em; color: #b8860b; border-bottom: 2px solid #b8860b; display: inline-block; padding: 0 20px 6px; margin: 4px 0 24px; }
  .cert .action { color: #555; margin-bottom: 10px; font-size: 1em; }
  .cert .course-name { font-size: 1.5em; font-style: italic; color: #2c2c2c; margin-bottom: 28px; }
  .cert .date { color: #777; font-size: .95em; margin-top: 28px; }
  .cert .uid { font-size: .72em; color: #bbb; margin-top: 16px; font-family: monospace; letter-spacing: 1px; }
  .actions { margin-top: 28px; display: flex; gap: 12px; }
  .btn { padding: 10px 28px; font-size: 1em; border-radius: 6px; border: none; cursor: pointer; font-family: sans-serif; }
  .btn-print { background: #b8860b; color: #fff; }
  .btn-close { background: #eee; color: #333; }
  @media print {
    body { background: #fff; padding: 0; }
    .cert { box-shadow: none; }
    .actions { display: none; }
  }
</style>
</head>
<body>
<div class="cert">
  <div class="cert-company"><?php echo $company; ?></div>
  <div class="cert-seal">🏆</div>
  <h1>Certificado de Conclusão</h1>
  <p class="sub">Certificamos que</p>
  <div class="recipient"><?php echo $name; ?></div>
  <p class="action">concluiu com êxito o treinamento</p>
  <div class="course-name"><?php echo $course_title; ?></div>
  <p class="date">Concluído em <?php echo $date; ?></p>
  <p class="uid">ID do Certificado: <?php echo esc_html( $cert->cert_uid ); ?></p>
</div>
<div class="actions">
  <button class="btn btn-print" onclick="window.print()">🖨️ Imprimir / Salvar PDF</button>
  <button class="btn btn-close" onclick="window.close()">Fechar</button>
</div>
</body>
</html>
		<?php
		return ob_get_clean();
	}
}
