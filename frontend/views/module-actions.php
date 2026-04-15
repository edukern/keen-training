<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
/**
 * Renderizado pelo shortcode [kt_modulo] em páginas Elementor.
 * Variáveis disponíveis: $module, $member, $quiz, $is_complete,
 *   $quiz_passed, $attempts, $unlimited, $quiz_blocked, $course_url
 */
?>
<div class="kt-portal kt-module-actions-block">

	<?php if ( $is_complete ): ?>
		<div class="kt-quiz-result kt-result-pass" style="margin:0">
			<p>✅ Módulo concluído!
				<a href="<?php echo esc_url( $course_url ); ?>" style="margin-left:12px">← Voltar ao curso</a>
			</p>
		</div>

	<?php elseif ( $quiz ): ?>
		<?php /* Módulo com avaliação — quiz é a forma de conclusão */ ?>
		<div class="kt-quiz-block" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
			<?php if ( $quiz_blocked ): ?>
				<p class="kt-quiz-failed" style="margin:0">⚠ Tentativas esgotadas. Fale com seu gerente.</p>
			<?php else:
				$quiz_url = add_query_arg( [
					'kt_view'   => 'quiz',
					'quiz_id'   => $quiz->id,
					'module_id' => $module->id,
					'course_id' => $module->course_id,
				], get_option( 'kt_portal_page_url', home_url( '/' ) ) );
			?>
				<a href="<?php echo esc_url( $quiz_url ); ?>" class="kt-btn kt-btn-quiz kt-btn-lg">
					<?php echo $attempts > 0 ? '🔄 Refazer Avaliação' : '📝 Responder Avaliação'; ?>
				</a>
				<span class="kt-pass-threshold">Mínimo: <strong><?php echo $quiz->pass_threshold; ?>%</strong></span>
				<?php if ( $attempts > 0 ): ?>
				<span class="kt-attempts-left">
					<?php if ( $unlimited ): ?>
						<?php echo $attempts; ?> tentativa(s) realizada(s) · Ilimitadas
					<?php else: ?>
						<?php echo $attempts; ?>/<?php echo $quiz->max_attempts; ?> tentativas usadas
					<?php endif; ?>
				</span>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<p style="margin:10px 0 0;font-size:.88em;color:#64748b">
			Responda a avaliação ao final do conteúdo para concluir este módulo.
			<a href="<?php echo esc_url( $course_url ); ?>">← Voltar ao curso</a>
		</p>

	<?php else: ?>
		<?php /* Módulo sem avaliação — botão manual */ ?>
		<button type="button"
			class="kt-btn kt-btn-complete kt-btn-lg kt-complete-module"
			data-module-id="<?php echo absint( $module->id ); ?>">
			✔ Marcar este Módulo como Concluído
		</button>
		<p style="margin:10px 0 0;font-size:.88em;color:#64748b">
			<a href="<?php echo esc_url( $course_url ); ?>">← Voltar ao curso</a>
		</p>
	<?php endif; ?>

</div>
