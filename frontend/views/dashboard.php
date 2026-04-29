<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
$wp_user   = get_userdata( $member->user_id );
$_first    = trim( $wp_user ? (string) $wp_user->first_name : '' );
$_last     = trim( $wp_user ? (string) $wp_user->last_name  : '' );
$_full     = trim( "$_first $_last" );
$nome      = $_full ?: ( $wp_user ? $wp_user->display_name : '' ) ?: '';
?>
<div class="kt-portal">
	<div class="kt-portal-header">
		<h2>Meu Portal de Treinamentos</h2>
		<p class="kt-welcome">Olá<?php echo $nome ? ', <strong>' . esc_html( $nome ) . '</strong>' : ''; ?>! Acompanhe seus treinamentos abaixo.</p>
	</div>

	<?php if ( isset( $_GET['kt_acesso_negado'] ) ): ?>
	<div class="kt-notice kt-notice-error" style="background:#fef2f2;border-left:4px solid #ef4444;padding:12px 16px;margin-bottom:20px;border-radius:4px">
		<strong>Acesso negado.</strong> Você não tem permissão para acessar este conteúdo. Se acredita que isso é um erro, fale com seu gerente.
	</div>
	<?php endif; ?>

	<?php if ( ! $enrollments ): ?>
	<div class="kt-empty-state">
		<div class="kt-empty-icon"></div>
		<p>Você ainda não tem treinamentos atribuídos. Em breve seu gerente irá cadastrar seus cursos.</p>
	</div>
	<?php else: ?>

	<!-- Resumo rápido -->
	<?php
	$total     = count( $enrollments );
	$concluido = count( array_filter( $enrollments, fn( $e ) => $e->status === 'concluido' ) );
	$atrasado  = count( array_filter( $enrollments, fn( $e ) => $e->due_date && strtotime( $e->due_date ) < time() && $e->status !== 'concluido' ) );
	?>
	<div class="kt-portal-summary">
		<div class="kt-summary-pill"><?php echo $total; ?> curso<?php echo $total !== 1 ? 's' : ''; ?></div>
		<div class="kt-summary-pill kt-pill-done"><?php echo $concluido; ?> concluído<?php echo $concluido !== 1 ? 's' : ''; ?></div>
		<?php if ( $atrasado ): ?>
		<div class="kt-summary-pill kt-pill-late"><?php echo $atrasado; ?> atrasado<?php echo $atrasado !== 1 ? 's' : ''; ?></div>
		<?php endif; ?>
	</div>

	<div class="kt-courses-grid">
		<?php foreach ( $enrollments as $en ):
			$pct     = KT_Progress::course_progress_pct( $member->id, $en->course_id );
			$overdue = $en->due_date && strtotime( $en->due_date ) < time() && $en->status !== 'concluido';
			$cert    = $en->status === 'concluido' ? KT_Certificate::get( $member->id, $en->course_id ) : null;
		?>
		<div class="kt-course-card kt-status-<?php echo esc_attr( $en->status ); ?>">
			<div class="kt-course-card-top">
				<div class="kt-course-badges">
					<span class="kt-status-badge kt-status-<?php echo esc_attr( $en->status ); ?>"><?php echo esc_html( KT_Progress::status_label( $en->status ) ); ?></span>
					<?php if ( $overdue ): ?><span class="kt-status-badge kt-status-overdue">Atrasado</span><?php endif; ?>
				</div>
			</div>

			<h3 class="kt-course-title"><?php echo esc_html( $en->course_title ); ?></h3>

			<div class="kt-progress-container">
				<div class="kt-progress-bar">
					<div class="kt-progress-fill" style="width:<?php echo $pct; ?>%"></div>
				</div>
				<span class="kt-progress-label"><?php echo $pct; ?>%</span>
			</div>

			<?php if ( $en->due_date ): ?>
			<p class="kt-due-date <?php echo $overdue ? 'kt-due-late' : ''; ?>">
				Prazo: <?php echo esc_html( date_i18n( 'd/m/Y', strtotime( $en->due_date ) ) ); ?>
			</p>
			<?php endif; ?>

			<div class="kt-course-card-actions">
				<?php if ( $en->status !== 'concluido' ): ?>
				<a href="<?php echo esc_url( add_query_arg( [ 'kt_view' => 'course', 'course_id' => $en->course_id ] ) ); ?>" class="kt-btn kt-btn-primary">
					<?php echo $en->status === 'nao_iniciado' ? 'Iniciar Treinamento' : 'Continuar'; ?>
				</a>
				<?php else: ?>
				<a href="<?php echo esc_url( add_query_arg( [ 'kt_view' => 'course', 'course_id' => $en->course_id ] ) ); ?>" class="kt-btn">Revisar</a>
				<?php if ( $cert ): ?>
				<a href="<?php echo esc_url( add_query_arg( [ 'kt_cert' => $cert->cert_uid ] ) ); ?>" target="_blank" class="kt-btn kt-btn-success">Ver Certificado</a>
				<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<?php if ( $certificates ): ?>
	<div class="kt-certificates-section">
		<h3>Meus Certificados</h3>
		<ul class="kt-cert-list">
			<?php foreach ( $certificates as $cert ): ?>
			<li>
				<span class="kt-cert-name"><?php echo esc_html( $cert->course_title ); ?></span>
				<span class="kt-cert-date"><?php echo esc_html( date_i18n( 'd/m/Y', strtotime( $cert->issued_at ) ) ); ?></span>
				<a href="<?php echo esc_url( add_query_arg( [ 'kt_cert' => $cert->cert_uid ] ) ); ?>" target="_blank" class="kt-btn kt-btn-sm">Ver</a>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php endif; ?>
</div>
