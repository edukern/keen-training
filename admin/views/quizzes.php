<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap kt-wrap">
	<h1>Avaliações
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-quizzes&action=add' ) ); ?>" class="page-title-action">+ Nova Avaliação</a>
	</h1>

	<?php if ( isset( $_GET['saved'] ) ): ?><div class="notice notice-success is-dismissible"><p>✓ Avaliação salva.</p></div><?php endif; ?>
	<?php if ( isset( $_GET['deleted'] ) ): ?><div class="notice notice-success is-dismissible"><p>✓ Avaliação excluída.</p></div><?php endif; ?>
	<?php if ( isset( $_GET['import_done'] ) ): ?><div class="notice notice-success is-dismissible"><p>✓ <?php echo esc_html( urldecode( $_GET['import_done'] ) ); ?></p></div><?php endif; ?>
	<?php if ( isset( $_GET['import_error'] ) ): ?><div class="notice notice-error is-dismissible"><p>⚠ Erro no import — verifique o arquivo e tente novamente.</p></div><?php endif; ?>

	<?php
	$modelo_csv_url = KT_PLUGIN_URL . 'assets/modelo-perguntas.csv';
	?>

	<?php if ( in_array( $action, [ 'add', 'edit' ] ) ): ?>
	<div class="kt-card">
		<h2><?php echo $quiz ? esc_html( $quiz->title ) : 'Nova Avaliação'; ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'kt_quiz' ); ?>
			<input type="hidden" name="action" value="kt_save_quiz">
			<input type="hidden" name="quiz_id" value="<?php echo $quiz ? absint( $quiz->id ) : 0; ?>">
			<table class="form-table">
				<tr>
					<th><label for="title">Título da Avaliação <span class="required">*</span></label></th>
					<td><input type="text" id="title" name="title" class="large-text" value="<?php echo $quiz ? esc_attr( $quiz->title ) : ''; ?>" required placeholder="Ex: Avaliação — Atendimento"></td>
				</tr>
				<tr>
					<th><label for="module_id">Vincular ao Módulo</label></th>
					<td>
						<select id="module_id" name="module_id">
							<option value="">— Sem módulo específico —</option>
							<?php foreach ( $courses as $c ): ?>
							<optgroup label="<?php echo esc_attr( $c->title ); ?>">
								<?php foreach ( KT_Course::get_modules( $c->id ) as $m ): ?>
								<option value="<?php echo esc_attr( $m->id ); ?>" <?php selected( $quiz ? $quiz->module_id : 0, $m->id ); ?>><?php echo esc_html( $m->title ); ?></option>
								<?php endforeach; ?>
							</optgroup>
							<?php endforeach; ?>
						</select>
						<p class="description">Ao vincular a um módulo, o colaborador precisará passar na avaliação para concluir o módulo.</p>
					</td>
				</tr>
				<tr>
					<th><label for="pass_threshold">Nota Mínima para Aprovação (%)</label></th>
					<td><input type="number" id="pass_threshold" name="pass_threshold" min="0" max="100" class="small-text" value="<?php echo $quiz ? absint( $quiz->pass_threshold ) : 70; ?>"> %</td>
				</tr>
				<tr>
					<th><label for="max_attempts">Máximo de Tentativas</label></th>
					<td>
						<input type="number" id="max_attempts" name="max_attempts" min="0" max="99" class="small-text" value="<?php echo $quiz ? absint( $quiz->max_attempts ) : 0; ?>">
						<p class="description"><strong>0 = ilimitado.</strong> Se definir um número maior que zero, o colaborador poderá tentar apenas aquela quantidade de vezes. Após esgotar, um administrador pode resetar as tentativas.</p>
					</td>
				</tr>
				<tr>
					<th>Embaralhamento</th>
					<td>
						<fieldset>
							<label style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
								<input type="checkbox" name="shuffle_questions" value="1" <?php checked( $quiz ? $quiz->shuffle_questions : 0, 1 ); ?>>
								<span><strong>Embaralhar ordem das perguntas</strong> — cada tentativa exibe as perguntas em uma sequência diferente</span>
							</label>
							<label style="display:flex;align-items:center;gap:8px">
								<input type="checkbox" name="shuffle_answers" value="1" <?php checked( $quiz ? $quiz->shuffle_answers : 0, 1 ); ?>>
								<span><strong>Embaralhar alternativas</strong> — a posição de cada alternativa (A, B, C, D) é randomizada por pergunta</span>
							</label>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th><label for="question_pool_size">Banco de Perguntas (Pool)</label></th>
					<td>
						<input type="number" id="question_pool_size" name="question_pool_size" min="0" max="999" class="small-text" value="<?php echo $quiz ? absint( $quiz->question_pool_size ?? 0 ) : 0; ?>">
						<p class="description"><strong>0 = exibir todas.</strong> Se definir um número maior que zero, o sistema selecionará aleatoriamente essa quantidade de perguntas do banco para cada tentativa.</p>
					</td>
				</tr>
				<tr>
					<th><label for="pass_message">Mensagem de Aprovação</label></th>
					<td>
						<textarea id="pass_message" name="pass_message" class="large-text" rows="2" placeholder="Ex: Parabéns! Você foi aprovado(a) com sucesso."><?php echo $quiz ? esc_textarea( $quiz->pass_message ?? '' ) : ''; ?></textarea>
						<p class="description">Exibida ao colaborador quando ele atingir a nota mínima. Deixe em branco para usar a mensagem padrão.</p>
					</td>
				</tr>
				<tr>
					<th><label for="fail_message">Mensagem de Reprovação</label></th>
					<td>
						<textarea id="fail_message" name="fail_message" class="large-text" rows="2" placeholder="Ex: Não foi desta vez. Revise o conteúdo e tente novamente."><?php echo $quiz ? esc_textarea( $quiz->fail_message ?? '' ) : ''; ?></textarea>
						<p class="description">Exibida ao colaborador quando ele não atingir a nota mínima. Deixe em branco para usar a mensagem padrão.</p>
					</td>
				</tr>
			</table>
			<?php submit_button( $quiz ? 'Atualizar e Gerenciar Perguntas →' : 'Criar Avaliação e Adicionar Perguntas →', 'primary', 'submit', false ); ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-quizzes' ) ); ?>" class="button" style="margin-left:8px">Cancelar</a>
		</form>
	</div>

	<?php elseif ( $action === 'questions' && $quiz ): ?>
	<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-quizzes&action=edit&id=' . $quiz->id ) ); ?>">← Voltar às configurações</a></p>
	<div class="kt-card">
		<h2>Perguntas: <?php echo esc_html( $quiz->title ); ?></h2>
		<p class="description">Nota mínima: <strong><?php echo $quiz->pass_threshold; ?>%</strong> &nbsp;|&nbsp; Máx. tentativas: <strong><?php echo $quiz->max_attempts; ?></strong><?php if ( ! empty( $quiz->question_pool_size ) ): ?> &nbsp;|&nbsp; Pool: <strong><?php echo absint( $quiz->question_pool_size ); ?> por tentativa</strong><?php endif; ?></p>
		<p class="description">Marque o(s) checkbox(es) da(s) alternativa(s) correta(s). Para <strong>Múltipla Seleção</strong>, marque todas as corretas.</p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="kt-quiz-form">
			<?php wp_nonce_field( 'kt_quiz_questions' ); ?>
			<input type="hidden" name="action" value="kt_save_quiz_questions">
			<input type="hidden" name="quiz_id" value="<?php echo absint( $quiz->id ); ?>">

			<div id="kt-questions-container">
			<?php foreach ( $questions as $qi => $q ):
				$answers = KT_Quiz::get_answers( $q->id ); ?>
			<div class="kt-question-block" data-index="<?php echo $qi; ?>">
				<div class="kt-question-header">
					<strong>Pergunta <?php echo $qi + 1; ?></strong>
					<div>
						<select name="questions[<?php echo $qi; ?>][question_type]" style="margin-right:8px">
							<option value="multiple_choice" <?php selected( $q->question_type, 'multiple_choice' ); ?>>Múltipla Escolha</option>
							<option value="true_false" <?php selected( $q->question_type, 'true_false' ); ?>>Verdadeiro / Falso</option>
							<option value="multiple_select" <?php selected( $q->question_type, 'multiple_select' ); ?>>Múltipla Seleção (checkboxes)</option>
						</select>
						<button type="button" class="button-link kt-delete-link kt-remove-question">Remover</button>
					</div>
				</div>
				<p><label>Texto da Pergunta<br>
					<textarea name="questions[<?php echo $qi; ?>][question_text]" class="large-text" rows="2" required><?php echo esc_textarea( $q->question_text ); ?></textarea>
				</label></p>
				<p><label>Explicação <small style="color:#888">(exibida na revisão após envio)</small><br>
					<textarea name="questions[<?php echo $qi; ?>][explanation]" class="large-text" rows="2" placeholder="Opcional — explique por que a resposta é correta."><?php echo esc_textarea( $q->explanation ?? '' ); ?></textarea>
				</label></p>
				<div class="kt-answers">
					<p><strong>Alternativas</strong> <small style="color:#888">(marque a(s) correta(s))</small></p>
					<?php foreach ( $answers as $ai => $ans ): ?>
					<div class="kt-answer-row">
						<input type="checkbox" name="questions[<?php echo $qi; ?>][answers][<?php echo $ai; ?>][is_correct]" value="1" <?php checked( $ans->is_correct ); ?> title="Marcar como correta">
						<input type="text" name="questions[<?php echo $qi; ?>][answers][<?php echo $ai; ?>][text]" class="regular-text" value="<?php echo esc_attr( $ans->answer_text ); ?>" placeholder="Alternativa">
						<button type="button" class="button-link kt-delete-link kt-remove-answer">✕</button>
					</div>
					<?php endforeach; ?>
					<button type="button" class="button kt-add-answer">+ Alternativa</button>
				</div>
			</div>
			<?php endforeach; ?>
			</div>

			<p><button type="button" id="kt-add-question" class="button button-secondary">+ Adicionar Pergunta</button></p>
			<?php submit_button( 'Salvar Todas as Perguntas' ); ?>
		</form>
	</div>

	<div class="kt-card" style="margin-top:24px">
		<h3>Importar Perguntas por CSV</h3>
		<p>Adicione várias perguntas de uma vez fazendo upload de um <code>.csv</code>. As perguntas são <strong>acrescentadas</strong> às existentes (não substituem).</p>

		<table class="widefat striped" style="max-width:740px;margin-bottom:16px">
			<thead>
				<tr><th>Coluna</th><th>Obrigatória?</th><th>Valores aceitos</th><th>Exemplo</th></tr>
			</thead>
			<tbody>
				<tr><td><strong>PERGUNTA</strong></td><td>Sim</td><td>Texto livre</td><td>Qual é a política de trocas?</td></tr>
				<tr><td><strong>TIPO</strong></td><td>Não</td><td>MC, VF ou MS</td><td>MC</td></tr>
				<tr><td><strong>ALTERNATIVA_A</strong></td><td>Sim</td><td>Texto</td><td>7 dias corridos</td></tr>
				<tr><td><strong>ALTERNATIVA_B</strong></td><td>Sim</td><td>Texto</td><td>30 dias</td></tr>
				<tr><td><strong>ALTERNATIVA_C</strong></td><td>Não</td><td>Texto</td><td>Não tem prazo</td></tr>
				<tr><td><strong>ALTERNATIVA_D</strong></td><td>Não</td><td>Texto</td><td>Apenas com etiqueta</td></tr>
				<tr><td><strong>CORRETA</strong></td><td>Sim</td><td>A, B, C, D — ou múltiplos com | (ex: A|C)</td><td>A</td></tr>
				<tr><td><strong>EXPLICACAO</strong></td><td>Não</td><td>Texto livre</td><td>O prazo legal é de 7 dias.</td></tr>
			</tbody>
		</table>
		<p class="description">
			<strong>TIPO MC</strong> = Múltipla Escolha &nbsp;|&nbsp; <strong>VF</strong> = Verdadeiro/Falso &nbsp;|&nbsp; <strong>MS</strong> = Múltipla Seleção (separe corretas com | na coluna CORRETA, ex: A|C).<br>
			Separador de colunas: <strong>ponto-e-vírgula (;)</strong> recomendado para Excel PT-BR.
		</p>
		<p>
			<a href="<?php echo esc_url( $modelo_csv_url ); ?>" download class="button">⬇ Baixar planilha modelo (.csv)</a>
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" style="margin-top:12px">
			<?php wp_nonce_field( 'kt_import_quiz_questions' ); ?>
			<input type="hidden" name="action" value="kt_import_quiz_questions">
			<input type="hidden" name="quiz_id" value="<?php echo absint( $quiz->id ); ?>">
			<input type="file" name="csv_file" accept=".csv,text/csv" required style="margin-right:8px">
			<?php submit_button( '↑ Importar Perguntas', 'secondary', 'submit', false ); ?>
		</form>
	</div>

	<?php else: ?>
	<p>
		<a href="<?php echo esc_url( $modelo_csv_url ); ?>" download class="button">⬇ Baixar planilha modelo de perguntas (.csv)</a>
		<span style="margin-left:8px;color:#646970;font-size:.9em">Use este modelo para importar perguntas em bloco em qualquer avaliação.</span>
	</p>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th>Título</th>
				<th>Vinculada ao Módulo</th>
				<th>Nota Mínima</th>
				<th>Tentativas</th>
				<th>Perguntas</th>
				<th>Ações</th>
			</tr>
		</thead>
		<tbody>
		<?php $all_quizzes = KT_Quiz::get_all(); ?>
		<?php if ( ! $all_quizzes ): ?>
			<tr><td colspan="6" style="text-align:center;padding:20px;color:#888">Nenhuma avaliação criada. <a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-quizzes&action=add' ) ); ?>">Criar →</a></td></tr>
		<?php else: ?>
		<?php foreach ( $all_quizzes as $qz ): ?>
			<tr>
				<td><strong><?php echo esc_html( $qz->title ); ?></strong></td>
				<td><?php echo $qz->module_title ? esc_html( $qz->module_title ) : '—'; ?></td>
				<td><?php echo absint( $qz->pass_threshold ); ?>%</td>
				<td><?php echo absint( $qz->max_attempts ); ?></td>
				<td><?php echo count( KT_Quiz::get_questions( $qz->id ) ); ?></td>
				<td>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-quizzes&action=edit&id=' . $qz->id ) ); ?>">Config.</a>
					&nbsp;|&nbsp;
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-quizzes&action=questions&id=' . $qz->id ) ); ?>">Perguntas</a>
					&nbsp;|&nbsp;
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline" onsubmit="return confirm('Excluir esta avaliação?')">
						<?php wp_nonce_field( 'kt_delete_quiz' ); ?>
						<input type="hidden" name="action" value="kt_delete_quiz">
						<input type="hidden" name="quiz_id" value="<?php echo absint( $qz->id ); ?>">
						<button type="submit" class="button-link kt-delete-link">Excluir</button>
					</form>
				</td>
			</tr>
		<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>
	<?php endif; ?>
</div>
