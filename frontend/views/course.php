<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="kt-portal">
	<nav class="kt-breadcrumb">
		<a href="<?php echo esc_url( remove_query_arg( [ 'kt_view', 'course_id' ] ) ); ?>">← Meus Treinamentos</a>
	</nav>

	<div class="kt-portal-header">
		<h2><?php echo esc_html( $course->title ); ?></h2>
		<?php if ( $course->description ): ?>
		<p class="kt-course-desc"><?php echo esc_html( $course->description ); ?></p>
		<?php endif; ?>

		<?php $pct = KT_Progress::course_progress_pct( $member->id, $course->id ); ?>
		<div class="kt-progress-container">
			<div class="kt-progress-bar kt-progress-large">
				<div class="kt-progress-fill" id="kt-course-progress-bar" style="width:<?php echo $pct; ?>%"></div>
			</div>
			<span class="kt-progress-label" id="kt-course-progress-label"><?php echo $pct; ?>% concluído</span>
		</div>
	</div>

	<?php if ( ! $modules ): ?>
		<p>Este curso ainda não tem módulos. Aguarde.</p>
	<?php else: ?>
	<div class="kt-modules-list">
		<?php foreach ( $modules as $i => $mod ):
			$is_complete  = KT_Progress::is_module_complete( $member->id, $mod->id );
			$quiz         = KT_Quiz::get_for_module( $mod->id );
			$quiz_passed  = $quiz ? (bool) KT_Quiz::best_result( $member->id, $quiz->id ) : false;
			$attempts     = $quiz ? KT_Quiz::attempt_count( $member->id, $quiz->id ) : 0;
			$max_attempts = $quiz ? (int) $quiz->max_attempts : 0; // 0 = ilimitado
			$unlimited    = $max_attempts === 0;
			$quiz_blocked = $quiz && ! $quiz_passed && ! $unlimited && $attempts >= $max_attempts;
		?>
		<div class="kt-module-item <?php echo $is_complete ? 'kt-module-complete' : ''; ?>" id="kt-module-<?php echo absint( $mod->id ); ?>">
			<div class="kt-module-header">
				<div class="kt-module-number"><?php echo $is_complete ? '✓' : ( $i + 1 ); ?></div>
				<div class="kt-module-info">
					<h3><?php echo esc_html( $mod->title ); ?></h3>
					<?php if ( $mod->description ): ?>
					<p><?php echo esc_html( $mod->description ); ?></p>
					<?php endif; ?>
				</div>
				<div class="kt-module-status-badge">
					<?php if ( $is_complete ): ?>
						<span class="kt-badge kt-badge-success">Concluído</span>
					<?php elseif ( $quiz && ! $quiz_passed && ! $quiz_blocked ): ?>
						<span class="kt-badge kt-badge-info">Avaliação necessária</span>
					<?php elseif ( $quiz_blocked ): ?>
						<span class="kt-badge kt-badge-overdue">Tentativas esgotadas</span>
					<?php else: ?>
						<span class="kt-badge">Em andamento</span>
					<?php endif; ?>
				</div>
			</div>

			<div class="kt-module-body">
				<?php if ( $mod->page_id ): ?>
				<?php /* Conteúdo vive em página Elementor — mostra botão de acesso */ ?>
				<?php if ( ! $is_complete ): ?>
				<p>
					<a href="<?php echo esc_url( get_permalink( $mod->page_id ) ); ?>" class="kt-btn kt-btn-primary">
						▶ Acessar Conteúdo do Módulo
					</a>
				</p>
				<?php endif; ?>
				<?php else: ?>
				<?php echo KT_Course::render_embed( $mod ); ?>
				<?php endif; ?>

				<div class="kt-module-actions">
					<?php if ( $quiz ): ?>
					<?php /* Módulo com avaliação: o quiz É a conclusão — sem botão separado */ ?>
					<div class="kt-quiz-block">
						<?php if ( $is_complete ): ?>
							<span class="kt-module-done-label">✓ Módulo concluído</span>
						<?php elseif ( $quiz_blocked ): ?>
							<p class="kt-quiz-failed">⚠ Tentativas esgotadas. Fale com seu gerente.</p>
						<?php else:
							$quiz_url = add_query_arg( [
								'kt_view'   => 'quiz',
								'quiz_id'   => $quiz->id,
								'module_id' => $mod->id,
								'course_id' => $course->id,
							] );
						?>
							<a href="<?php echo esc_url( $quiz_url ); ?>" class="kt-btn kt-btn-quiz">
								<?php echo $attempts > 0 ? '🔄 Refazer Avaliação' : '📝 Fazer Avaliação'; ?>
							</a>
							<span class="kt-attempts-left">
								<?php if ( $unlimited ): ?>
									<?php echo $attempts > 0 ? $attempts . ' tentativa(s) realizada(s) · Tentativas ilimitadas' : 'Tentativas ilimitadas'; ?>
								<?php else: ?>
									<?php echo $attempts; ?>/<?php echo $max_attempts; ?> tentativas usadas
								<?php endif; ?>
							</span>
							<span class="kt-pass-threshold">Mínimo para aprovação: <strong><?php echo $quiz->pass_threshold; ?>%</strong></span>
						<?php endif; ?>
					</div>

					<?php else: ?>
					<?php /* Módulo sem avaliação: botão manual de conclusão */ ?>
					<?php if ( ! $is_complete ): ?>
					<button type="button"
						class="kt-btn kt-btn-complete kt-complete-module"
						data-module-id="<?php echo absint( $mod->id ); ?>">
						✔ Marcar como Concluído
					</button>
					<?php else: ?>
					<span class="kt-module-done-label">✓ Módulo concluído</span>
					<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<?php if ( $enrollment->status === 'concluido' ):
		$cert = KT_Certificate::get( $member->id, $course->id );
	?>
	<div class="kt-completion-banner" id="kt-completion-banner">
		🎉 Parabéns! Você concluiu este curso!
		<?php if ( $cert ): ?>
		<a href="<?php echo esc_url( add_query_arg( [ 'kt_cert' => $cert->cert_uid ] ) ); ?>" target="_blank" class="kt-btn kt-btn-success">🏆 Ver Certificado</a>
		<?php endif; ?>
	</div>
	<?php else: ?>
	<div class="kt-completion-banner" id="kt-completion-banner" style="display:none">
		🎉 Parabéns! Você concluiu este curso! Atualize a página para ver seu certificado.
	</div>
	<?php endif; ?>
</div>
