<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap kt-wrap">
	<h1>Matrículas — Atribuir Treinamentos</h1>

	<?php if ( isset( $_GET['saved'] ) ): ?><div class="notice notice-success is-dismissible"><p>✓ Treinamento atribuído com sucesso.</p></div><?php endif; ?>
	<?php if ( isset( $_GET['deleted'] ) ): ?><div class="notice notice-success is-dismissible"><p>✓ Matrícula removida.</p></div><?php endif; ?>

	<div class="kt-card">
		<h2>Atribuir Curso</h2>
		<p class="description">Selecione um curso e escolha para quem atribuir. Colaboradores em unidades sem acesso ao curso serão ignorados automaticamente.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'kt_enrollment' ); ?>
			<input type="hidden" name="action" value="kt_save_enrollment">
			<table class="form-table">
				<tr>
					<th><label for="course_id">Curso <span class="required">*</span></label></th>
					<td>
						<select id="course_id" name="course_id" required>
							<option value="">— Selecionar Curso —</option>
							<?php foreach ( $courses as $c ): ?>
							<option value="<?php echo esc_attr( $c->id ); ?>">
								<?php echo esc_html( $c->title ); ?>
								<?php
								$desc = KT_Restriction::describe( $c->id );
								if ( $desc !== 'Livre (sem restrição)' ) echo ' [Restrito: ' . esc_html( $desc ) . ']';
								?>
							</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th>Atribuir para</th>
					<td>
						<label style="margin-right:20px"><input type="radio" name="target_type" value="member" checked> Colaboradores específicos</label>
						<label><input type="radio" name="target_type" value="location"> Toda uma unidade</label>
					</td>
				</tr>
				<tr id="kt-members-row">
					<th><label for="member_ids">Colaboradores</label></th>
					<td>
						<select name="member_ids[]" id="member_ids" multiple size="8" style="min-width:340px">
							<?php foreach ( $members as $m ): ?>
							<option value="<?php echo esc_attr( $m->id ); ?>">
								<?php echo esc_html( $m->display_name . ' — ' . ( $m->location_name ?? '?' ) ); ?>
							</option>
							<?php endforeach; ?>
						</select>
						<p class="description">Segure Ctrl (Windows) ou ⌘ Cmd (Mac) para selecionar mais de um.</p>
					</td>
				</tr>
				<tr id="kt-location-row" style="display:none">
					<th><label for="location_id">Unidade</label></th>
					<td>
						<select name="location_id" id="location_id">
							<option value="">— Selecionar Unidade —</option>
							<?php foreach ( $locations as $loc ):
								if ( ! KT_Roles::can_manage_location( $loc->id ) ) continue; ?>
							<option value="<?php echo esc_attr( $loc->id ); ?>"><?php echo esc_html( $loc->name ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description">Todos os colaboradores desta unidade serão matriculados.</p>
					</td>
				</tr>
				<tr>
					<th><label for="due_date">Prazo para Conclusão</label></th>
					<td>
						<input type="date" id="due_date" name="due_date">
						<p class="description">Opcional. Matrículas com prazo vencido serão marcadas como "Atrasado" no painel de progresso.</p>
					</td>
				</tr>
			</table>
			<?php submit_button( 'Atribuir Treinamento' ); ?>
		</form>
	</div>

	<!-- Lista de matrículas -->
	<h2>Matrículas Cadastradas</h2>
	<?php
	global $wpdb;
	$loc_id    = KT_Roles::is_super_admin() ? 0 : KT_Roles::current_user_location_id();
	$where     = $loc_id ? $wpdb->prepare( 'AND m.location_id = %d', $loc_id ) : '';
	$filter_course = absint( $_GET['filter_course'] ?? 0 );
	if ( $filter_course ) $where .= $wpdb->prepare( ' AND e.course_id = %d', $filter_course );

	$enrollments = $wpdb->get_results(
		"SELECT e.*, u.display_name, c.title AS course_title, l.name AS location_name
		 FROM {$wpdb->prefix}kt_enrollments e
		 JOIN {$wpdb->prefix}kt_members m ON m.id = e.member_id
		 JOIN {$wpdb->users} u ON u.ID = m.user_id
		 JOIN {$wpdb->prefix}kt_courses c ON c.id = e.course_id
		 LEFT JOIN {$wpdb->prefix}kt_locations l ON l.id = m.location_id
		 WHERE 1=1 $where
		 ORDER BY l.name ASC, u.display_name ASC, c.title ASC"
	);
	?>
	<div class="kt-filter-bar">
		<label>Filtrar por Curso:
			<select onchange="location.href='<?php echo esc_url( admin_url( 'admin.php?page=kt-enrollments' ) ); ?>&filter_course='+this.value">
				<option value="0">Todos os Cursos</option>
				<?php foreach ( $courses as $c ): ?>
				<option value="<?php echo esc_attr( $c->id ); ?>" <?php selected( $filter_course, $c->id ); ?>><?php echo esc_html( $c->title ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
	</div>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th>Colaborador</th>
				<th>Unidade</th>
				<th>Curso</th>
				<th>Status</th>
				<th>Prazo</th>
				<th>Ações</th>
			</tr>
		</thead>
		<tbody>
		<?php if ( ! $enrollments ): ?>
			<tr><td colspan="6" style="text-align:center;padding:20px;color:#888">Nenhuma matrícula encontrada.</td></tr>
		<?php else: ?>
		<?php foreach ( $enrollments as $en ):
			$overdue = $en->due_date && strtotime( $en->due_date ) < time() && $en->status !== 'concluido';
		?>
			<tr class="<?php echo $overdue ? 'kt-overdue' : ''; ?>">
				<td><?php echo esc_html( $en->display_name ); ?></td>
				<td><?php echo esc_html( $en->location_name ?? '—' ); ?></td>
				<td><?php echo esc_html( $en->course_title ); ?></td>
				<td>
					<span class="kt-status kt-status-<?php echo esc_attr( $en->status ); ?>"><?php echo esc_html( KT_Progress::status_label( $en->status ) ); ?></span>
					<?php if ( $overdue ): ?><span class="kt-badge kt-badge-overdue">Atrasado</span><?php endif; ?>
				</td>
				<td><?php echo $en->due_date ? esc_html( date_i18n( 'd/m/Y', strtotime( $en->due_date ) ) ) : '—'; ?></td>
				<td>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline" onsubmit="return confirm('Remover esta matrícula?')">
						<?php wp_nonce_field( 'kt_delete_enrollment' ); ?>
						<input type="hidden" name="action" value="kt_delete_enrollment">
						<input type="hidden" name="member_id" value="<?php echo absint( $en->member_id ); ?>">
						<input type="hidden" name="course_id" value="<?php echo absint( $en->course_id ); ?>">
						<button type="submit" class="button-link kt-delete-link">Remover</button>
					</form>
				</td>
			</tr>
		<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>
</div>
