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
	 * Renderiza o certificado como HTML para impressão/PDF.
	 *
	 * @param string $cert_uid  UUID do certificado. Se vazio, renderiza um preview de demonstração.
	 */
	public static function render_html( $cert_uid = '', $is_preview = false ) {
		$is_preview = $is_preview || empty( $cert_uid );

		if ( $is_preview ) {
			$name         = 'Nome do Colaborador';
			$course_title = 'Nome do Treinamento';
			$date         = date_i18n( 'd \d\e F \d\e Y' );
			$uid_display  = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx';
		} else {
			$cert = self::get_by_uid( sanitize_text_field( $cert_uid ) );
			if ( ! $cert ) {
				return '<p style="font-family:sans-serif;text-align:center;margin:60px">Certificado não encontrado.</p>';
			}
			$member       = KT_Member::get( $cert->member_id );
			$course       = KT_Course::get( $cert->course_id );
			$name         = $member ? esc_html( $member->display_name ) : 'Colaborador';
			$course_title = $course  ? esc_html( $course->title )       : 'Treinamento';
			$date         = date_i18n( 'd \d\e F \d\e Y', strtotime( $cert->issued_at ) );
			$uid_display  = esc_html( $cert->cert_uid );
		}

		// Configurações salvas no admin
		$primary      = sanitize_hex_color( get_option( 'kt_primary_color', '#3b82f6' ) ) ?: '#3b82f6';
		$accent       = sanitize_hex_color( get_option( 'kt_cert_accent_color', '' ) ) ?: $primary;
		$company_name = esc_html( get_option( 'kt_cert_company_name', get_bloginfo( 'name' ) ) );
		$logo_url     = esc_url( get_option( 'kt_cert_logo_url', '' ) );
		$show_id      = get_option( 'kt_cert_show_id', '1' ) === '1';
		$font_body    = sanitize_text_field( get_option( 'kt_font_body', 'Inter' ) ) ?: 'Inter';
		$font_heading = sanitize_text_field( get_option( 'kt_font_heading', $font_body ) ) ?: $font_body;

		// Google Fonts — carrega ambas as famílias necessárias
		$gfonts_families = array_unique( array_filter( [ $font_body, $font_heading ] ) );
		$gfonts_url = '';
		if ( $gfonts_families ) {
			$gfonts_url = 'https://fonts.googleapis.com/css2?'
				. implode( '&', array_map( fn($f) => 'family=' . urlencode( $f ) . ':wght@300;400;500;600;700;800', $gfonts_families ) )
				. '&display=swap';
		}

		// Deriva cor de fundo levemente escura para a barra lateral
		// Gera versão semi-transparente do accent para o corner e bg-block
		ob_start(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Certificado de Conclusão</title>
<?php if ( $gfonts_url ): ?>
<link href="<?php echo esc_url( $gfonts_url ); ?>" rel="stylesheet">
<?php endif; ?>
<style>
  :root {
    --accent:     <?php echo $accent; ?>;
    --black:      #000000;
    --complement: #F0EBE6;
    --white:      #FFFFFF;
    --font-body:  '<?php echo esc_attr( $font_body ); ?>', system-ui, sans-serif;
    --font-head:  '<?php echo esc_attr( $font_heading ); ?>', system-ui, sans-serif;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    background: #e8e3de;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    font-family: var(--font-body);
    padding: 20px;
  }

  .certificate {
    width: 1100px;
    max-width: 100%;
    aspect-ratio: 1.414;
    background: var(--white);
    position: relative;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
  }

  .top-bar {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 6px;
    background: var(--accent);
  }

  .side-accent {
    position: absolute;
    top: 0; left: 0;
    width: 80px;
    height: 100%;
    background: var(--black);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    padding-bottom: 40px;
  }

  .side-accent .vertical-text {
    writing-mode: vertical-rl;
    text-orientation: mixed;
    transform: rotate(180deg);
    color: rgba(255,255,255,0.25);
    font-size: 10px;
    font-weight: 500;
    letter-spacing: 4px;
    text-transform: uppercase;
    font-family: var(--font-body);
  }

  .side-accent .dot-accent {
    position: absolute;
    top: 28px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: var(--accent);
  }

  .content {
    margin-left: 80px;
    padding: 50px 70px 36px 60px;
    height: 100%;
    display: flex;
    flex-direction: column;
    position: relative;
  }

  .logo-area {
    margin-bottom: 36px;
    height: 44px;
    display: flex;
    align-items: center;
  }
  .logo-area img   { max-height: 44px; max-width: 200px; object-fit: contain; }
  .logo-text {
    font-size: 18px;
    font-weight: 700;
    color: rgba(0,0,0,0.3);
    letter-spacing: 2px;
    text-transform: uppercase;
    font-family: var(--font-head);
  }

  .label-certificado {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: rgba(0,0,0,0.4);
    margin-bottom: 6px;
    font-family: var(--font-body);
  }

  .title {
    font-size: 50px;
    font-weight: 800;
    color: var(--black);
    letter-spacing: -1.5px;
    line-height: 1.05;
    font-family: var(--font-head);
  }

  .title-underline {
    width: 56px;
    height: 4px;
    background: var(--accent);
    margin-top: 14px;
    margin-bottom: 32px;
  }

  .subtitle {
    font-size: 12px;
    font-weight: 400;
    color: rgba(0,0,0,0.45);
    letter-spacing: 2.5px;
    text-transform: uppercase;
    margin-bottom: 8px;
    font-family: var(--font-body);
  }

  .participant-name {
    font-size: 38px;
    font-weight: 300;
    color: var(--black);
    letter-spacing: -0.5px;
    padding-bottom: 8px;
    border-bottom: 2px solid var(--accent);
    display: inline-block;
    margin-bottom: 26px;
    font-family: var(--font-head);
  }

  .description {
    font-size: 14px;
    font-weight: 400;
    color: rgba(0,0,0,0.5);
    line-height: 1.6;
    margin-bottom: 6px;
    font-family: var(--font-body);
  }

  .training-name {
    font-size: 22px;
    font-weight: 700;
    color: var(--black);
    letter-spacing: -0.3px;
    position: relative;
    display: inline-block;
    padding-bottom: 6px;
    margin-bottom: 0;
    font-family: var(--font-head);
  }

  .training-name::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0;
    width: 100%;
    height: 3px;
    background: var(--accent);
    opacity: 0.5;
  }

  .footer {
    margin-top: auto;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    padding-top: 16px;
    border-top: 1px solid rgba(0,0,0,0.08);
  }

  .date-block {
    font-size: 12px;
    color: rgba(0,0,0,0.45);
    font-family: var(--font-body);
  }
  .date-block .date-value {
    font-weight: 600;
    color: var(--black);
    font-size: 13px;
    margin-top: 2px;
  }

  .cert-id {
    font-size: 9px;
    font-weight: 500;
    color: rgba(0,0,0,0.25);
    letter-spacing: 1px;
    font-family: monospace;
  }

  .corner-accent {
    position: absolute;
    bottom: 0; right: 0;
    width: 140px;
    height: 140px;
    overflow: hidden;
    pointer-events: none;
  }
  .corner-accent::before {
    content: '';
    position: absolute;
    bottom: -70px; right: -70px;
    width: 140px; height: 140px;
    background: var(--accent);
    border-radius: 50%;
    opacity: 0.12;
  }

  .bg-block {
    position: absolute;
    top: 50%; right: 0;
    width: 180px; height: 200px;
    background: var(--complement);
    opacity: 0.35;
    transform: translateY(-50%);
    pointer-events: none;
  }

  /* Botões de ação (escondidos na impressão) */
  .kt-cert-actions {
    margin-top: 24px;
    display: flex;
    gap: 12px;
  }
  .kt-cert-btn {
    padding: 10px 28px;
    font-size: .95em;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-family: var(--font-body);
    text-decoration: none;
    display: inline-block;
  }
  .kt-cert-btn-print { background: var(--accent); color: #fff; }
  .kt-cert-btn-close { background: #eee; color: #333; }

  @media print {
    body { background: #fff; padding: 0; }
    .certificate { box-shadow: none; width: 100%; }
    .kt-cert-actions { display: none; }
  }
</style>
</head>
<body>
<div class="certificate">
  <div class="top-bar"></div>

  <div class="side-accent">
    <div class="dot-accent"></div>
    <span class="vertical-text"><?php echo $company_name; ?> — Certificado</span>
  </div>

  <div class="content">
    <div class="logo-area">
      <?php if ( $logo_url ): ?>
        <img src="<?php echo $logo_url; ?>" alt="<?php echo $company_name; ?>">
      <?php else: ?>
        <span class="logo-text"><?php echo $company_name; ?></span>
      <?php endif; ?>
    </div>

    <div class="label-certificado">Certificado de Conclusão</div>
    <div class="title">Certificado de<br>Conclusão</div>
    <div class="title-underline"></div>

    <div class="subtitle">Certificamos que</div>
    <div class="participant-name"><?php echo $name; ?></div>

    <div class="description">concluiu com êxito o treinamento</div>
    <div class="training-name"><?php echo $course_title; ?></div>

    <div class="footer">
      <div class="date-block">
        Concluído em
        <div class="date-value"><?php echo $date; ?></div>
      </div>
      <?php if ( $show_id ): ?>
        <div class="cert-id">ID <?php echo $uid_display; ?></div>
      <?php endif; ?>
    </div>
  </div>

  <div class="corner-accent"></div>
  <div class="bg-block"></div>
</div>

<div class="kt-cert-actions">
  <button class="kt-cert-btn kt-cert-btn-print" onclick="window.print()">Imprimir / Salvar PDF</button>
  <button class="kt-cert-btn kt-cert-btn-close" onclick="window.close()">Fechar</button>
</div>
</body>
</html>
		<?php
		return ob_get_clean();
	}
}
