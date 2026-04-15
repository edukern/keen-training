<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
/**
 * View do shortcode [kt_quiz id="X"] — embed em páginas Elementor.
 * Variáveis: $quiz, $member, $questions, $attempt_count, $best,
 *            $unlimited, $exhausted, $attempts_str, $module_id
 */
$pool_size = (int) ( $quiz->question_pool_size ?? 0 );
?>
<div class="kt-quiz-embed">

	<div class="kt-quiz-embed-meta">
		<span>Nota mínima: <strong><?php echo absint( $quiz->pass_threshold ); ?>%</strong></span>
		<span class="kt-quiz-embed-sep">·</span>
		<span>Tentativas: <strong><?php echo esc_html( $attempts_str ); ?></strong></span>
		<?php if ( $pool_size > 0 ): ?>
		<span class="kt-quiz-embed-sep">·</span>
		<span><strong><?php echo $pool_size; ?></strong> perguntas por tentativa</span>
		<?php endif; ?>
	</div>

	<?php if ( $best ): ?>
	<div class="kt-quiz-result kt-result-pass">
		<div class="kt-score-display"><?php echo absint( $best->score ); ?>%</div>
		<p><?php
			$pass_msg = ! empty( $quiz->pass_message ) ? $quiz->pass_message : '';
			echo $pass_msg ? esc_html( $pass_msg ) : 'Você foi aprovado(a) nesta avaliação!';
		?></p>
	</div>

	<?php elseif ( $exhausted ): ?>
	<div class="kt-quiz-result kt-result-fail">
		<p>Você usou todas as <?php echo absint( $quiz->max_attempts ); ?> tentativas disponíveis. Entre em contato com seu gerente.</p>
	</div>

	<?php elseif ( ! $questions ): ?>
	<p style="color:#94a3b8">Esta avaliação ainda não tem perguntas cadastradas.</p>

	<?php else: ?>

	<div id="kt-quiz-result-<?php echo absint( $quiz->id ); ?>" class="kt-quiz-result" style="display:none"></div>

	<form class="kt-quiz kt-quiz-embed-form" data-quiz-id="<?php echo absint( $quiz->id ); ?>" novalidate>
		<input type="hidden" class="kt-quiz-id-input"   value="<?php echo absint( $quiz->id ); ?>">
		<input type="hidden" class="kt-module-id-input" value="<?php echo absint( $module_id ); ?>">

		<!-- Progress bar -->
		<div class="kt-quiz-progress">
			<div class="kt-quiz-progress-info">
				<span id="kt-progress-label-<?php echo absint( $quiz->id ); ?>">Pergunta 1 de <?php echo count( $questions ); ?></span>
			</div>
			<div class="kt-quiz-progress-bar-wrap">
				<div class="kt-quiz-progress-fill" style="width:<?php echo count($questions) > 0 ? round(1/count($questions)*100) : 100; ?>%"></div>
			</div>
		</div>

		<?php foreach ( $questions as $qi => $q ):
			$answers     = KT_Quiz::get_answers( $q->id );
			if ( $quiz->shuffle_answers ) shuffle( $answers );
			$is_multiple = ( $q->question_type === 'multiple_select' );
		?>
		<input type="hidden" name="question_ids[]" value="<?php echo absint( $q->id ); ?>">
		<div class="kt-q-card kt-question" data-question-id="<?php echo absint( $q->id ); ?>">
			<p class="kt-question-text">
				<span class="kt-question-num"><?php echo $qi + 1; ?></span>
				<?php echo esc_html( $q->question_text ); ?>
			</p>
			<?php if ( $is_multiple ): ?>
			<p class="kt-multiple-note">(Selecione todas as corretas)</p>
			<?php endif; ?>
			<div class="kt-answers">
				<?php foreach ( $answers as $ans ): ?>
				<label class="kt-answer-option">
					<input type="<?php echo $is_multiple ? 'checkbox' : 'radio'; ?>"
						name="<?php echo $is_multiple
							? 'response_' . absint( $q->id ) . '_' . absint( $quiz->id ) . '[]'
							: 'response_' . absint( $q->id ) . '_' . absint( $quiz->id ); ?>"
						value="<?php echo absint( $ans->id ); ?>"
						data-question-id="<?php echo absint( $q->id ); ?>"
						class="kt-response-input <?php echo $is_multiple ? 'kt-response-checkbox' : ''; ?>">
					<span class="kt-answer-label"><?php echo esc_html( $ans->answer_text ); ?></span>
				</label>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endforeach; ?>

		<div class="kt-quiz-footer">
			<p class="kt-quiz-hint">Responda todas as perguntas antes de enviar.</p>
			<button type="submit" class="kt-btn kt-btn-primary kt-btn-lg kt-quiz-embed-submit">
				Enviar Avaliação
			</button>
		</div>
	</form>
	<?php endif; ?>

</div>
