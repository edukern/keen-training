<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * KT_Notifications — Agendamento e envio de digest de aniversários e admissões.
 *
 * Fluxo:
 *  1. Um cron diário (kt_birthday_digest_check) é registrado via maybe_schedule().
 *  2. Em cada execução, maybe_send() verifica se hoje é o dia configurado.
 *  3. Se for, send_digest() busca os eventos dos próximos N dias e envia o e-mail.
 *
 * Configurações salvas no wp_options:
 *  - kt_notif_email       string   E-mail destino
 *  - kt_notif_frequency   string   'weekly' | 'monthly'
 *  - kt_notif_day         int      Dia da semana (0=Dom…6=Sáb) OU dia do mês (1-28)
 *  - kt_notif_days_ahead  int      Janela de dias a consultar (padrão: 7 ou 30)
 */
class KT_Notifications {

	const CRON_HOOK = 'kt_birthday_digest_check';

	/* -----------------------------------------------------------------------
	 * Agendamento
	 * -------------------------------------------------------------------- */

	public static function maybe_schedule() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			// Agenda para rodar diariamente a partir de meia-noite de hoje
			wp_schedule_event( strtotime( 'today midnight' ), 'daily', self::CRON_HOOK );
		}
	}

	public static function unschedule() {
		$ts = wp_next_scheduled( self::CRON_HOOK );
		if ( $ts ) {
			wp_unschedule_event( $ts, self::CRON_HOOK );
		}
	}

	/* -----------------------------------------------------------------------
	 * Callback do cron — verifica se hoje é o dia de envio
	 * -------------------------------------------------------------------- */

	public static function maybe_send() {
		$email     = sanitize_email( get_option( 'kt_notif_email', '' ) );
		$frequency = get_option( 'kt_notif_frequency', '' );
		$day       = absint( get_option( 'kt_notif_day', 1 ) );

		// Não configurado — silencioso
		if ( ! $email || ! $frequency ) {
			return;
		}

		$today_ts = current_time( 'timestamp' );

		if ( $frequency === 'weekly' ) {
			// $day: 0=Domingo … 6=Sábado  (PHP date('w'))
			if ( (int) date( 'w', $today_ts ) !== $day ) {
				return;
			}
			$days_ahead = absint( get_option( 'kt_notif_days_ahead', 7 ) );
		} elseif ( $frequency === 'monthly' ) {
			// $day: dia do mês 1-28
			if ( (int) date( 'j', $today_ts ) !== $day ) {
				return;
			}
			$days_ahead = absint( get_option( 'kt_notif_days_ahead', 30 ) );
		} else {
			return;
		}

		self::send_digest( $email, $days_ahead );
	}

	/* -----------------------------------------------------------------------
	 * Envio do digest
	 * -------------------------------------------------------------------- */

	public static function send_digest( $email, $days_ahead = 7 ) {
		$events = self::get_upcoming_events( $days_ahead );

		if ( empty( $events['birthdays'] ) && empty( $events['anniversaries'] ) ) {
			return false; // nada a enviar
		}

		$site_name = get_bloginfo( 'name' );
		$label     = $days_ahead === 1 ? 'amanhã' : "nos próximos {$days_ahead} dias";
		$subject   = "[{$site_name}] 🎂 Datas Especiais dos Colaboradores — {$label}";
		$body      = self::build_email_html( $events, $days_ahead, $site_name );

		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];
		return wp_mail( $email, $subject, $body, $headers );
	}

	/* -----------------------------------------------------------------------
	 * Consulta de eventos futuros
	 * -------------------------------------------------------------------- */

	/**
	 * Retorna colaboradores com aniversário (birth_date) ou aniversário de empresa
	 * (hire_date) que caem nos próximos $days_ahead dias (comparação mês+dia,
	 * independente do ano).
	 *
	 * @param int $days_ahead Número de dias à frente a considerar (inclusive hoje).
	 * @return array { birthdays: array[], anniversaries: array[] }
	 */
	public static function get_upcoming_events( $days_ahead = 7 ) {
		global $wpdb;

		$birthdays     = [];
		$anniversaries = [];

		$members = $wpdb->get_results(
			"SELECT m.id, m.birth_date, m.hire_date,
			        NULLIF(TRIM(CONCAT(
			            COALESCE(umf.meta_value,''), ' ',
			            COALESCE(uml.meta_value,'')
			        )), '') AS full_name,
			        u.display_name,
			        l.name AS location_name
			 FROM {$wpdb->prefix}kt_members m
			 JOIN {$wpdb->users} u ON u.ID = m.user_id
			 LEFT JOIN {$wpdb->prefix}kt_locations l ON l.id = m.location_id
			 LEFT JOIN {$wpdb->usermeta} umf ON umf.user_id = u.ID AND umf.meta_key = 'first_name'
			 LEFT JOIN {$wpdb->usermeta} uml ON uml.user_id = u.ID AND uml.meta_key = 'last_name'
			 WHERE m.birth_date IS NOT NULL OR m.hire_date IS NOT NULL
			 ORDER BY l.name ASC, u.display_name ASC"
		);

		$today_str = date( 'Y-m-d', current_time( 'timestamp' ) );
		$today_ts  = strtotime( $today_str );
		$today_y   = (int) date( 'Y', $today_ts );

		foreach ( $members as $m ) {
			$name = $m->full_name ?: $m->display_name;

			// ── Aniversário de nascimento ──────────────────────────────────
			if ( $m->birth_date ) {
				$diff = self::days_until_annual( $m->birth_date, $today_ts, $today_y );
				if ( $diff !== false && $diff >= 0 && $diff < $days_ahead ) {
					$year_of_event = ( $diff === 0 && (int) date( 'md', strtotime( $m->birth_date ) ) === (int) date( 'md', $today_ts ) )
						? $today_y
						: ( strtotime( date( 'Y', $today_ts ) . substr( $m->birth_date, 4 ) ) >= $today_ts ? $today_y : $today_y + 1 );
					$age = $year_of_event - (int) substr( $m->birth_date, 0, 4 );

					$birthdays[] = [
						'name'       => $name,
						'location'   => $m->location_name ?? '—',
						'date'       => self::format_md( $m->birth_date ),
						'days_until' => $diff,
						'extra'      => $age . ' ' . ( $age === 1 ? 'ano' : 'anos' ),
					];
				}
			}

			// ── Aniversário de empresa ─────────────────────────────────────
			if ( $m->hire_date ) {
				$diff = self::days_until_annual( $m->hire_date, $today_ts, $today_y );
				if ( $diff !== false && $diff >= 0 && $diff < $days_ahead ) {
					$hire_y        = (int) substr( $m->hire_date, 0, 4 );
					$year_of_event = strtotime( $today_y . substr( $m->hire_date, 4 ) ) >= $today_ts
						? $today_y
						: $today_y + 1;
					$years = $year_of_event - $hire_y;

					$anniversaries[] = [
						'name'       => $name,
						'location'   => $m->location_name ?? '—',
						'date'       => self::format_md( $m->hire_date ),
						'days_until' => $diff,
						'extra'      => $years . ' ' . ( $years === 1 ? 'ano de empresa' : 'anos de empresa' ),
					];
				}
			}
		}

		usort( $birthdays,     fn( $a, $b ) => $a['days_until'] <=> $b['days_until'] );
		usort( $anniversaries, fn( $a, $b ) => $a['days_until'] <=> $b['days_until'] );

		return compact( 'birthdays', 'anniversaries' );
	}

	/* -----------------------------------------------------------------------
	 * Helpers
	 * -------------------------------------------------------------------- */

	/**
	 * Quantos dias faltam para o próximo aniversário anual (mês+dia) de $date_str.
	 * Retorna 0 se for hoje, 1 se for amanhã, etc.
	 * Retorna false se a data for inválida.
	 */
	private static function days_until_annual( $date_str, $today_ts, $today_y ) {
		if ( ! $date_str ) return false;
		$parts = explode( '-', $date_str );
		if ( count( $parts ) < 3 ) return false;
		$month = (int) $parts[1];
		$day   = (int) $parts[2];

		// Tenta este ano
		$this_year = mktime( 0, 0, 0, $month, $day, $today_y );
		if ( $this_year < $today_ts ) {
			// Já passou — usa o próximo ano
			$this_year = mktime( 0, 0, 0, $month, $day, $today_y + 1 );
		}
		return (int) round( ( $this_year - $today_ts ) / DAY_IN_SECONDS );
	}

	/**
	 * Formata 'YYYY-MM-DD' → 'DD/MM' (sem ano).
	 */
	private static function format_md( $date_str ) {
		if ( ! $date_str ) return '';
		$parts = explode( '-', $date_str );
		return sprintf( '%02d/%02d', (int) $parts[2], (int) $parts[1] );
	}

	/**
	 * Rótulo humanizado para $days_until.
	 */
	private static function days_label( $days ) {
		if ( $days === 0 ) return '🎉 <strong>Hoje!</strong>';
		if ( $days === 1 ) return '⏰ Amanhã';
		return "em {$days} dias";
	}

	/* -----------------------------------------------------------------------
	 * Template do e-mail HTML
	 * -------------------------------------------------------------------- */

	private static function build_email_html( $events, $days_ahead, $site_name ) {
		$birthdays     = $events['birthdays'];
		$anniversaries = $events['anniversaries'];

		$label = $days_ahead === 1 ? 'amanhã' : "nos próximos {$days_ahead} dias";

		$rows_b = '';
		foreach ( $birthdays as $ev ) {
			$rows_b .= '<tr>
				<td style="padding:10px 14px;border-bottom:1px solid #f0f0f0">'
					. esc_html( $ev['name'] ) . '</td>
				<td style="padding:10px 14px;border-bottom:1px solid #f0f0f0;color:#64748b;font-size:.9em">'
					. esc_html( $ev['location'] ) . '</td>
				<td style="padding:10px 14px;border-bottom:1px solid #f0f0f0;font-weight:600">'
					. esc_html( $ev['date'] ) . '</td>
				<td style="padding:10px 14px;border-bottom:1px solid #f0f0f0;color:#64748b;font-size:.9em">'
					. esc_html( $ev['extra'] ) . '</td>
				<td style="padding:10px 14px;border-bottom:1px solid #f0f0f0">'
					. self::days_label( $ev['days_until'] ) . '</td>
			</tr>';
		}

		$rows_a = '';
		foreach ( $anniversaries as $ev ) {
			$rows_a .= '<tr>
				<td style="padding:10px 14px;border-bottom:1px solid #f0f0f0">'
					. esc_html( $ev['name'] ) . '</td>
				<td style="padding:10px 14px;border-bottom:1px solid #f0f0f0;color:#64748b;font-size:.9em">'
					. esc_html( $ev['location'] ) . '</td>
				<td style="padding:10px 14px;border-bottom:1px solid #f0f0f0;font-weight:600">'
					. esc_html( $ev['date'] ) . '</td>
				<td style="padding:10px 14px;border-bottom:1px solid #f0f0f0;color:#64748b;font-size:.9em">'
					. esc_html( $ev['extra'] ) . '</td>
				<td style="padding:10px 14px;border-bottom:1px solid #f0f0f0">'
					. self::days_label( $ev['days_until'] ) . '</td>
			</tr>';
		}

		$table_style = 'width:100%;border-collapse:collapse;margin-top:8px;font-size:.95em';
		$th_style    = 'padding:8px 14px;background:#f8fafc;text-align:left;font-size:.85em;color:#64748b;text-transform:uppercase;letter-spacing:.04em;border-bottom:2px solid #e2e8f0';

		$birthday_section = '';
		if ( $rows_b ) {
			$count = count( $birthdays );
			$birthday_section = "
			<h2 style=\"margin:32px 0 8px;font-size:1.1em;color:#1e293b\">
				🎂 Aniversários de Nascimento
				<span style=\"font-size:.8em;font-weight:400;color:#64748b\">({$count} colaborador" . ( $count > 1 ? 'es' : '' ) . ")</span>
			</h2>
			<table style=\"{$table_style}\">
				<thead><tr>
					<th style=\"{$th_style}\">Nome</th>
					<th style=\"{$th_style}\">Unidade</th>
					<th style=\"{$th_style}\">Data</th>
					<th style=\"{$th_style}\">Idade</th>
					<th style=\"{$th_style}\">Quando</th>
				</tr></thead>
				<tbody>{$rows_b}</tbody>
			</table>";
		}

		$anniversary_section = '';
		if ( $rows_a ) {
			$count = count( $anniversaries );
			$anniversary_section = "
			<h2 style=\"margin:32px 0 8px;font-size:1.1em;color:#1e293b\">
				🏅 Aniversários de Empresa
				<span style=\"font-size:.8em;font-weight:400;color:#64748b\">({$count} colaborador" . ( $count > 1 ? 'es' : '' ) . ")</span>
			</h2>
			<table style=\"{$table_style}\">
				<thead><tr>
					<th style=\"{$th_style}\">Nome</th>
					<th style=\"{$th_style}\">Unidade</th>
					<th style=\"{$th_style}\">Data</th>
					<th style=\"{$th_style}\">Tempo</th>
					<th style=\"{$th_style}\">Quando</th>
				</tr></thead>
				<tbody>{$rows_a}</tbody>
			</table>";
		}

		$date_today = date_i18n( 'd \\d\\e F \\d\\e Y', current_time( 'timestamp' ) );

		return "<!DOCTYPE html>
<html lang=\"pt-BR\">
<head><meta charset=\"UTF-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\"></head>
<body style=\"margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif\">
<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:#f1f5f9;padding:32px 16px\">
<tr><td align=\"center\">
<table width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;width:100%;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08)\">

	<!-- Header -->
	<tr><td style=\"background:linear-gradient(135deg,#1e293b 0%,#334155 100%);padding:28px 32px\">
		<p style=\"margin:0;font-size:1.3em;font-weight:700;color:#fff\">{$site_name}</p>
		<p style=\"margin:4px 0 0;font-size:.9em;color:#94a3b8\">Keen Training — Datas Especiais</p>
	</td></tr>

	<!-- Body -->
	<tr><td style=\"padding:28px 32px\">
		<p style=\"margin:0 0 4px;font-size:.85em;color:#94a3b8\">{$date_today}</p>
		<h1 style=\"margin:0 0 8px;font-size:1.4em;color:#0f172a\">Datas Especiais {$label}</h1>
		<p style=\"margin:0;color:#475569;line-height:1.6\">
			Confira abaixo os colaboradores com aniversários e datas de admissão próximas para que o time de marketing possa preparar as mensagens de felicitação.
		</p>

		{$birthday_section}
		{$anniversary_section}

		<p style=\"margin:40px 0 0;font-size:.82em;color:#94a3b8;border-top:1px solid #f1f5f9;padding-top:16px\">
			Este e-mail foi gerado automaticamente pelo plugin <strong>Keen Training</strong> em <em>" . esc_html( get_bloginfo( 'url' ) ) . "</em>.<br>
			Para alterar as configurações de notificação, acesse o painel administrativo → Keen Training → Painel.
		</p>
	</td></tr>

</table>
</td></tr>
</table>
</body>
</html>";
	}
}
