<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="kt-portal">
	<nav class="kt-breadcrumb">
		<a href="<?php echo esc_url( add_query_arg( [ 'kt_view' => 'course', 'course_id' => absint( $_GET['course_id'] ?? 0 ) ] ) ); ?>">← Voltar ao Curso</a>
	</nav>

	<?php
		$unlimited    = (int) $quiz->max_attempts === 0;
		$attempts_str = $unlimited
			? ( $attempt_count > 0 ? $attempt_count . ' tentativa(s) realizada(s) · Ilimitadas' : 'Tentativas ilimitadas' )
			: ( $attempt_count . ' de ' . $quiz->max_attempts );
		$exhausted    = ! $unlimited && $attempt_count >= (int) $quiz->max_attempts;
		$course_url   = esc_url( add_query_arg( [ 'kt_view' => 'course', 'course_id' => absint( $_GET['course_id'] ?? 0 ) ] ) );
		$pool_size    = (int) ( $quiz->question_pool_size ?? 0 );
	?>
	<div class="kt-portal-header">
		<h2><?php echo esc_html( $quiz->title ); ?></h2>
		<p class="kt-quiz-meta">
			Mínimo para aprovação: <strong><?php echo $quiz->pass_threshold; ?>%</strong>
			&nbsp;·&nbsp;
			Tentativas: <strong><?php echo $attempts_str; ?></strong>
			<?php if ( $pool_size > 0 ): ?>
			&nbsp;·&nbsp;
			<strong><?php echo $pool_size; ?></strong> perguntas por tentativa
			<?php endif; ?>
		</p>
	</div>

	<?php if ( $best ): ?>
	<!-- Já aprovado -->
	<div class="kt-quiz-result kt-result-pass">
		<div class="kt-score-display"><?php echo $best->score; ?>%</div>
		<p><?php
			$pass_msg = ! empty( $quiz->pass_message ) ? $quiz->pass_message : '';
			echo $pass_msg ? esc_html( $pass_msg ) : 'Parabéns! Você foi aprovado(a) nesta avaliação. O módulo foi concluído.';
		?></p>
		<div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px">
			<?php if ( $next_module_url ): ?>
			<a href="<?php echo esc_url( $next_module_url ); ?>" class="kt-btn kt-btn-primary">Próximo Módulo →</a>
			<?php endif; ?>
			<a href="<?php echo $course_url; ?>" class="kt-btn kt-btn-outline">← Voltar ao Curso</a>
		</div>
	</div>

	<?php elseif ( $exhausted ): ?>
	<!-- Tentativas esgotadas -->
	<div class="kt-quiz-result kt-result-fail">
		<p>Você usou todas as <?php echo $quiz->max_attempts; ?> tentativas disponíveis. Entre em contato com seu gerente para liberar novas tentativas.</p>
		<a href="<?php echo $course_url; ?>" class="kt-btn">← Voltar ao Curso</a>
	</div>

	<?php elseif ( ! $questions ): ?>
	<p>Esta avaliação ainda não tem perguntas cadastradas.</p>

	<?php else: ?>

	<!-- Painel de resultado (oculto até submeter) -->
	<div id="kt-quiz-result" class="kt-quiz-result" style="display:none"></div>

	<form id="kt-quiz-form" class="kt-quiz" novalidate>
		<input type="hidden" id="kt-quiz-id"      value="<?php echo absint( $quiz->id ); ?>">
		<input type="hidden" id="kt-module-id"    value="<?php echo absint( $module_id ); ?>">
		<input type="hidden" id="kt-next-mod-url" value="<?php echo esc_url( $next_module_url ?? '' ); ?>">
		<input type="hidden" id="kt-course-url"   value="<?php echo esc_url( $course_url ); ?>">

		<!-- Progress bar -->
		<div class="kt-quiz-progress">
			<div class="kt-quiz-progress-info">
				<span id="kt-progress-label">Pergunta 1 de <?php echo count( $questions ); ?></span>
			</div>
			<div class="kt-quiz-progress-bar-wrap">
				<div class="kt-quiz-progress-fill" id="kt-progress-fill" style="width:<?php echo count($questions) > 0 ? round(1/count($questions)*100) : 100; ?>%"></div>
			</div>
		</div>

		<?php foreach ( $questions as $qi => $q ):
			$answers = KT_Quiz::get_answers( $q->id );
			if ( $quiz->shuffle_answers ) {
				shuffle( $answers );
			}
			$is_multiple = ( $q->question_type === 'multiple_select' );
			// Hidden input tracking which questions were served (for pool)
		?>
		<input type="hidden" name="question_ids[]" value="<?php echo absint( $q->id ); ?>">
		<div class="kt-q-card kt-question" data-question-id="<?php echo absint( $q->id ); ?>" data-index="<?php echo $qi; ?>">
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
						name="<?php echo $is_multiple ? 'response_' . absint( $q->id ) . '[]' : 'response_' . absint( $q->id ); ?>"
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
			<button type="submit" id="kt-quiz-submit" class="kt-btn kt-btn-primary kt-btn-lg">
				Enviar Avaliação
			</button>
		</div>
	</form>
	<?php endif; ?>
</div>
