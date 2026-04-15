<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap kt-wrap">
	<h1>Cursos
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-courses&action=add' ) ); ?>" class="page-title-action">+ Novo Curso</a>
	</h1>

	<?php if ( isset( $_GET['saved'] ) ): ?><div class="notice notice-success is-dismissible"><p>✓ Salvo com sucesso.</p></div><?php endif; ?>
	<?php if ( isset( $_GET['deleted'] ) ): ?><div class="notice notice-success is-dismissible"><p>✓ Excluído com sucesso.</p></div><?php endif; ?>

	<?php if ( in_array( $action, [ 'add', 'edit' ] ) ): ?>
	<h2><?php echo $action === 'edit' && $course ? esc_html( $course->title ) : 'Novo Curso'; ?></h2>

	<!-- Formulário do curso -->
	<div class="kt-card">
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'kt_course' ); ?>
			<input type="hidden" name="action" value="kt_save_course">
			<input type="hidden" name="course_id" value="<?php echo $course ? absint( $course->id ) : 0; ?>">
			<table class="form-table">
				<tr>
					<th><label for="title">Título do Curso <span class="required">*</span></label></th>
					<td><input type="text" id="title" name="title" class="large-text" value="<?php echo $course ? esc_attr( $course->title ) : ''; ?>" required placeholder="Ex: Atendimento ao Cliente"></td>
				</tr>
				<tr>
					<th><label for="description">Descrição</label></th>
					<td><textarea id="description" name="description" class="large-text" rows="4" placeholder="Descreva o objetivo e conteúdo do curso..."><?php echo $course ? esc_textarea( $course->description ) : ''; ?></textarea></td>
				</tr>
				<tr>
					<th><label for="passing_score">Nota Mínima para Aprovação (%)</label></th>
					<td>
						<input type="number" id="passing_score" name="passing_score" min="0" max="100" class="small-text" value="<?php echo $course ? absint( $course->passing_score ) : 70; ?>"> %
						<p class="description">Percentual mínimo nas avaliações para concluir o curso e receber o certificado.</p>
					</td>
				</tr>
			</table>

			<!-- Restrições de acesso -->
			<div class="kt-restriction-section">
				<h3>🔒 Restrição de Acesso</h3>
				<p class="description">Deixe <strong>tudo em branco</strong> para disponibilizar o curso a todos os colaboradores. Selecione unidades e/ou cargos para restringir o acesso.</p>

				<div class="kt-restriction-grid">
					<div>
						<strong>Restringir por Unidade:</strong>
						<p class="description">Apenas colaboradores das unidades marcadas poderão ser matriculados.</p>
						<div class="kt-checkbox-list">
						<?php
						$restricted_locs  = $course ? KT_Restriction::get_location_ids( $course->id ) : [];
						foreach ( $locations as $loc ):
						?>
							<label class="kt-checkbox-option">
								<input type="checkbox" name="restrict_locations[]" value="<?php echo esc_attr( $loc->id ); ?>"
									<?php checked( in_array( (int) $loc->id, $restricted_locs, true ) ); ?>>
								<?php echo esc_html( $loc->name ); ?>
							</label>
						<?php endforeach; ?>
						</div>
					</div>
					<div>
						<strong>Restringir por Função:</strong>
						<p class="description">Apenas colaboradores com as funções marcadas poderão ser matriculados.</p>
						<div class="kt-checkbox-list">
						<?php
						$restricted_positions = $course ? KT_Restriction::get_position_ids( $course->id ) : [];
						$all_positions        = KT_Position::get_all();
						if ( $all_positions ):
							foreach ( $all_positions as $pos ):
						?>
							<label class="kt-checkbox-option">
								<input type="checkbox" name="restrict_positions[]" value="<?php echo esc_attr( $pos->id ); ?>"
									<?php checked( in_array( (int) $pos->id, $restricted_positions, true ) ); ?>>
								<span style="display:inline-flex;align-items:center;gap:6px">
									<span style="width:10px;height:10px;border-radius:50%;background:<?php echo esc_attr( $pos->color ); ?>;flex-shrink:0"></span>
									<?php echo esc_html( $pos->name ); ?>
								</span>
							</label>
						<?php
							endforeach;
						else:
						?>
							<em style="color:#888">Nenhuma função cadastrada. <a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-positions&action=add' ) ); ?>">Criar funções →</a></em>
						<?php endif; ?>
						</div>
					</div>
				</div>

				<?php if ( $course ): ?>
				<p class="kt-restriction-status">
					<strong>Acesso atual:</strong> <?php echo KT_Restriction::describe( $course->id ); ?>
				</p>
				<?php endif; ?>
			</div>

			<p>
				<?php submit_button( $course ? 'Atualizar Curso' : 'Criar Curso', 'primary', 'submit', false ); ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-courses' ) ); ?>" class="button" style="margin-left:8px">Cancelar</a>
			</p>
		</form>
	</div>

	<?php if ( $course ): ?>
	<hr>
	<!-- Módulos -->
	<h3>Módulos do Curso</h3>
	<p class="description">Módulos são as etapas do curso. Você pode vincular cada módulo a uma <strong>página Elementor</strong> (recomendado) ou a uma <strong>URL externa</strong> (YouTube, Google Drive, PDF). Coloque o shortcode <code>[kt_modulo]</code> na página Elementor para exibir o botão de conclusão e o quiz.</p>

	<?php if ( $modules ): ?>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th width="40">#</th>
				<th>Título</th>
				<th>Tipo de Conteúdo</th>
				<th>Avaliação</th>
				<th>Ações</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $modules as $i => $mod ):
			$mod_quiz = KT_Quiz::get_for_module( $mod->id );
		?>
			<tr>
				<td><?php echo $i + 1; ?></td>
				<td>
					<strong><?php echo esc_html( $mod->title ); ?></strong>
					<?php if ( $mod->page_id ): ?>
					<br><a href="<?php echo esc_url( get_permalink( $mod->page_id ) ); ?>" target="_blank" style="font-size:.85em">📄 <?php echo esc_html( get_the_title( $mod->page_id ) ); ?></a>
					<?php elseif ( $mod->content_url ): ?>
					<br><a href="<?php echo esc_url( $mod->content_url ); ?>" target="_blank" style="font-size:.85em">🔗 Ver conteúdo</a>
					<?php endif; ?>
				</td>
				<td>
					<?php if ( $mod->page_id ): ?>
					<span class="kt-badge kt-badge-info">Página WP</span>
					<?php else: ?>
					<span class="kt-badge kt-badge-<?php echo esc_attr( $mod->embed_type ); ?>"><?php echo esc_html( $mod->embed_type ); ?></span>
					<?php endif; ?>
				</td>
				<td><?php echo $mod_quiz ? '<span class="kt-badge kt-badge-info">' . esc_html( $mod_quiz->title ) . '</span>' : '<em style="color:#aaa">Sem avaliação</em>'; ?></td>
				<td>
					<a href="#" onclick="ktToggleForm('mod-edit-<?php echo $mod->id; ?>');return false">Editar</a>
					&nbsp;|&nbsp;
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline" onsubmit="return confirm('Excluir este módulo?')">
						<?php wp_nonce_field( 'kt_delete_module' ); ?>
						<input type="hidden" name="action" value="kt_delete_module">
						<input type="hidden" name="module_id" value="<?php echo absint( $mod->id ); ?>">
						<button type="submit" class="button-link kt-delete-link">Excluir</button>
					</form>
				</td>
			</tr>
			<tr id="mod-edit-<?php echo $mod->id; ?>" style="display:none">
				<td colspan="5" class="kt-inline-form">
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<?php wp_nonce_field( 'kt_module' ); ?>
						<input type="hidden" name="action" value="kt_save_module">
						<input type="hidden" name="module_id" value="<?php echo absint( $mod->id ); ?>">
						<input type="hidden" name="course_id" value="<?php echo absint( $course->id ); ?>">
						<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
							<p><label>Título<br><input type="text" name="title" class="large-text" value="<?php echo esc_attr( $mod->title ); ?>" required></label></p>
							<p><label>Ordem<br><input type="number" name="sort_order" class="small-text" value="<?php echo absint( $mod->sort_order ); ?>"></label></p>
							<p style="grid-column:1/-1"><label><strong>Página Elementor vinculada</strong> (recomendado)<br>
								<select name="page_id" style="max-width:100%">
									<option value="">— Sem página vinculada (usar URL abaixo) —</option>
									<?php foreach ( get_pages( [ 'sort_column' => 'post_title', 'sort_order' => 'ASC' ] ) as $pg ): ?>
									<option value="<?php echo esc_attr( $pg->ID ); ?>" <?php selected( (int) $mod->page_id, $pg->ID ); ?>><?php echo esc_html( $pg->post_title ); ?></option>
									<?php endforeach; ?>
								</select>
								<span class="description">Selecione a página que tem o conteúdo deste módulo. Coloque <code>[kt_modulo]</code> nela. Se selecionada, o campo URL abaixo é ignorado.</span>
							</label></p>
							<p style="grid-column:1/-1"><label>URL de Conteúdo Externo (alternativa)<br>
								<input type="url" name="content_url" class="large-text" value="<?php echo esc_attr( $mod->content_url ); ?>" placeholder="https://...">
								<span class="description">Use se não tiver uma página Elementor. Cole o link do YouTube, Google Drive, Vimeo ou PDF.</span>
							</label></p>
							<p><label>Tipo de embed<br>
								<select name="embed_type">
									<?php foreach ( [ 'youtube'=>'YouTube', 'vimeo'=>'Vimeo', 'google_drive'=>'Google Drive', 'video'=>'Vídeo', 'pdf'=>'PDF', 'link'=>'Link Simples' ] as $et => $el ): ?>
									<option value="<?php echo esc_attr( $et ); ?>" <?php selected( $mod->embed_type, $et ); ?>><?php echo esc_html( $el ); ?></option>
									<?php endforeach; ?>
								</select>
							</label></p>
							<p><label>Descrição<br><textarea name="description" class="large-text" rows="2"><?php echo esc_textarea( $mod->description ); ?></textarea></label></p>
						</div>
						<?php submit_button( 'Salvar Módulo', 'primary', 'submit', false ); ?>
					</form>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>

	<div class="kt-card" style="margin-top:20px">
		<h3>+ Adicionar Módulo</h3>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'kt_module' ); ?>
			<input type="hidden" name="action" value="kt_save_module">
			<input type="hidden" name="course_id" value="<?php echo absint( $course->id ); ?>">
			<table class="form-table">
				<tr>
					<th><label for="new_title">Título do Módulo <span class="required">*</span></label></th>
					<td><input type="text" id="new_title" name="title" class="large-text" required placeholder="Ex: Módulo 1 — Introdução"></td>
				</tr>
				<tr>
					<th><label for="new_page_id">Página Elementor</label></th>
					<td>
						<select id="new_page_id" name="page_id" style="max-width:400px">
							<option value="">— Sem página vinculada (usar URL abaixo) —</option>
							<?php foreach ( get_pages( [ 'sort_column' => 'post_title', 'sort_order' => 'ASC' ] ) as $pg ): ?>
							<option value="<?php echo esc_attr( $pg->ID ); ?>"><?php echo esc_html( $pg->post_title ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description">Selecione a página com o conteúdo do módulo. Coloque <code>[kt_modulo]</code> nela para exibir o botão de conclusão e o quiz. Se selecionada, a URL abaixo é ignorada.</p>
					</td>
				</tr>
				<tr>
					<th><label for="new_url">URL de Conteúdo Externo</label></th>
					<td>
						<input type="url" id="new_url" name="content_url" class="large-text" placeholder="https://www.youtube.com/watch?v=...">
						<p class="description">Alternativa à página Elementor. Cole o link do YouTube, Vimeo, Google Drive ou PDF. O tipo será detectado automaticamente.</p>
					</td>
				</tr>
				<tr>
					<th><label for="new_desc">Descrição do Módulo</label></th>
					<td><textarea id="new_desc" name="description" class="large-text" rows="2" placeholder="O que o colaborador aprenderá neste módulo..."></textarea></td>
				</tr>
				<tr>
					<th><label for="new_order">Ordem</label></th>
					<td><input type="number" id="new_order" name="sort_order" class="small-text" value="<?php echo count( $modules ); ?>"></td>
				</tr>
			</table>
			<?php submit_button( 'Adicionar Módulo', 'secondary' ); ?>
		</form>
	</div>
	<?php endif; ?>

	<?php else: ?>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th>Título</th>
				<th>Módulos</th>
				<th>Nota Mínima</th>
				<th>Restrição de Acesso</th>
				<th>Ações</th>
			</tr>
		</thead>
		<tbody>
		<?php $all_courses = KT_Course::get_all(); ?>
		<?php if ( ! $all_courses ): ?>
			<tr><td colspan="5" style="text-align:center;padding:20px;color:#888">Nenhum curso criado ainda. <a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-courses&action=add' ) ); ?>">Criar o primeiro →</a></td></tr>
		<?php else: ?>
		<?php foreach ( $all_courses as $c ): ?>
			<tr>
				<td><strong><?php echo esc_html( $c->title ); ?></strong></td>
				<td><?php echo absint( $c->module_count ); ?></td>
				<td><?php echo absint( $c->passing_score ); ?>%</td>
				<td><small><?php echo KT_Restriction::describe( $c->id ); ?></small></td>
				<td>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-courses&action=edit&id=' . $c->id ) ); ?>">Editar / Módulos</a>
					&nbsp;|&nbsp;
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline" onsubmit="return confirm('Excluir este curso e todos os seus módulos?')">
						<?php wp_nonce_field( 'kt_delete_course' ); ?>
						<input type="hidden" name="action" value="kt_delete_course">
						<input type="hidden" name="course_id" value="<?php echo absint( $c->id ); ?>">
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
