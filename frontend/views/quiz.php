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
	?>
	<div class="kt-portal-header">
		<h2>📝 <?php echo esc_html( $quiz->title ); ?></h2>
		<p class="kt-quiz-meta">
			Mínimo para aprovação: <strong><?php echo $quiz->pass_threshold; ?>%</strong>
			&nbsp;·&nbsp;
			Tentativas: <strong><?php echo $attempts_str; ?></strong>
		</p>
	</div>

	<?php if ( $best ): ?>
	<!-- Já aprovado -->
	<div class="kt-quiz-result kt-result-pass">
		<p>✅ Você foi aprovado(a) nesta avaliação com <strong><?php echo $best->score; ?>%</strong>! O módulo foi concluído.</p>
		<a href="<?php echo $course_url; ?>" class="kt-btn">← Voltar ao Curso</a>
	</div>

	<?php elseif ( $exhausted ): ?>
	<!-- Tentativas esgotadas -->
	<div class="kt-quiz-result kt-result-fail">
		<p>⚠ Você usou todas as <?php echo $quiz->max_attempts; ?> tentativas disponíveis. Entre em contato com seu gerente para liberar novas tentativas.</p>
		<a href="<?php echo $course_url; ?>" class="kt-btn">← Voltar ao Curso</a>
	</div>

	<?php elseif ( ! $questions ): ?>
	<p>Esta avaliação ainda não tem perguntas cadastradas.</p>

	<?php else: ?>

	<!-- Painel de resultado (oculto até submeter) -->
	<div id="kt-quiz-result" class="kt-quiz-result" style="display:none"></div>

	<form id="kt-quiz-form" class="kt-quiz" novalidate>
		<input type="hidden" id="kt-quiz-id"   value="<?php echo absint( $quiz->id ); ?>">
		<input type="hidden" id="kt-module-id" value="<?php echo absint( $module_id ); ?>">

		<?php foreach ( $questions as $qi => $q ):
			$answers = KT_Quiz::get_answers( $q->id );
			if ( $quiz->shuffle_answers ) {
				shuffle( $answers );
			}
		?>
		<div class="kt-question" data-question-id="<?php echo absint( $q->id ); ?>">
			<p class="kt-question-text">
				<span class="kt-question-num"><?php echo $qi + 1; ?></span>
				<?php echo esc_html( $q->question_text ); ?>
			</p>
			<div class="kt-answers">
				<?php foreach ( $answers as $ans ): ?>
				<label class="kt-answer-option">
					<input type="radio"
						name="response_<?php echo absint( $q->id ); ?>"
						value="<?php echo absint( $ans->id ); ?>"
						data-question-id="<?php echo absint( $q->id ); ?>"
						class="kt-response-input">
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
