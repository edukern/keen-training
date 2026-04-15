<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap kt-wrap">
	<h1>Progresso dos Treinamentos</h1>

	<div class="kt-filter-bar">
		<form method="get" action="">
			<input type="hidden" name="page" value="kt-progress">
			<?php if ( KT_Roles::is_super_admin() ): ?>
			<label>Unidade:
				<select name="location_id" onchange="this.form.submit()">
					<option value="0">— Todas —</option>
					<?php foreach ( $locations as $loc ): ?>
					<option value="<?php echo esc_attr( $loc->id ); ?>" <?php selected( $location_id, $loc->id ); ?>><?php echo esc_html( $loc->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<?php endif; ?>
			<label>Curso:
				<select name="course_id" onchange="this.form.submit()">
					<option value="0">— Todos os Cursos —</option>
					<?php foreach ( $courses as $c ): ?>
					<option value="<?php echo esc_attr( $c->id ); ?>" <?php selected( $course_id, $c->id ); ?>><?php echo esc_html( $c->title ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</form>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-left:auto">
			<?php wp_nonce_field( 'kt_export_progress' ); ?>
			<input type="hidden" name="action" value="kt_export_progress">
			<input type="hidden" name="location_id" value="<?php echo esc_attr( $location_id ); ?>">
			<button type="submit" class="button">⬇ Exportar CSV</button>
		</form>
	</div>

	<?php if ( isset( $_GET['reset_done'] ) ): ?><div class="notice notice-success is-dismissible"><p>✓ Tentativas resetadas. O colaborador pode tentar novamente.</p></div><?php endif; ?>

	<?php if ( ! $location_id && ! $course_id ): ?>
		<div class="notice notice-info" style="padding:12px"><p>Selecione uma unidade ou curso acima para visualizar o progresso.</p></div>
	<?php elseif ( ! $rows ): ?>
		<div class="notice notice-warning" style="padding:12px"><p>Nenhum dado encontrado para esta seleção.</p></div>
	<?php else:
		// Summary counts
		$total   = count( $rows );
		$done    = count( array_filter( $rows, fn( $r ) => $r->status === 'concluido' ) );
		$late    = count( array_filter( $rows, fn( $r ) => $r->due_date && strtotime( $r->due_date ) < time() && $r->status !== 'concluido' ) );
	?>
	<div class="kt-progress-summary">
		<div class="kt-summary-card kt-summary-total"><span><?php echo $total; ?></span>Total</div>
		<div class="kt-summary-card kt-summary-done"><span><?php echo $done; ?></span>Concluídos</div>
		<div class="kt-summary-card kt-summary-pending"><span><?php echo $total - $done; ?></span>Pendentes</div>
		<div class="kt-summary-card kt-summary-late"><span><?php echo $late; ?></span>Atrasados</div>
	</div>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th>Colaborador</th>
				<?php if ( $course_id ): ?>
				<th>Unidade</th>
				<th>Melhor Nota</th>
				<?php else: ?>
				<th>Curso</th>
				<?php endif; ?>
				<th>Status</th>
				<th>Prazo</th>
				<th>Concluído em</th>
				<th>Progresso</th>
				<th>Avaliação</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $rows as $row ):
			$row_course_id = $course_id ?: $row->course_id;
			$pct           = KT_Progress::course_progress_pct( $row->member_id, $row_course_id );
			$overdue       = $row->due_date && strtotime( $row->due_date ) < time() && $row->status !== 'concluido';
			// Find a quiz linked to this course (for reset button)
			$course_quiz = null;
			foreach ( KT_Course::get_modules( $row_course_id ) as $mod ) {
				$q = KT_Quiz::get_for_module( $mod->id );
				if ( $q ) { $course_quiz = $q; break; }
			}
			$back_url = add_query_arg( array_filter( [
				'page'        => 'kt-progress',
				'location_id' => $location_id ?: null,
				'course_id'   => $course_id ?: null,
			] ), admin_url( 'admin.php' ) );
		?>
			<tr class="<?php echo $overdue ? 'kt-overdue' : ''; ?>">
				<td><?php echo esc_html( $row->display_name ); ?></td>
				<?php if ( $course_id ): ?>
				<td><?php echo esc_html( $row->location_name ?? '—' ); ?></td>
				<td><?php echo isset( $row->best_quiz_score ) && $row->best_quiz_score !== null ? absint( $row->best_quiz_score ) . '%' : '—'; ?></td>
				<?php else: ?>
				<td><?php echo esc_html( $row->course_title ); ?></td>
				<?php endif; ?>
				<td>
					<span class="kt-status kt-status-<?php echo esc_attr( $row->status ); ?>"><?php echo esc_html( KT_Progress::status_label( $row->status ) ); ?></span>
					<?php if ( $overdue ): ?><span class="kt-badge kt-badge-overdue">Atrasado</span><?php endif; ?>
				</td>
				<td><?php echo $row->due_date ? esc_html( date_i18n( 'd/m/Y', strtotime( $row->due_date ) ) ) : '—'; ?></td>
				<td><?php echo $row->completed_at ? esc_html( date_i18n( 'd/m/Y', strtotime( $row->completed_at ) ) ) : '—'; ?></td>
				<td>
					<div class="kt-progress-bar">
						<div class="kt-progress-fill" style="width:<?php echo $pct; ?>%"></div>
					</div>
					<span class="kt-progress-label"><?php echo $pct; ?>%</span>
				</td>
				<td>
					<?php if ( $course_quiz && KT_Quiz::attempt_count( $row->member_id, $course_quiz->id ) > 0 ): ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline"
						onsubmit="return confirm('Resetar tentativas de avaliação deste colaborador?')">
						<?php wp_nonce_field( 'kt_reset_quiz_attempts' ); ?>
						<input type="hidden" name="action" value="kt_reset_quiz_attempts">
						<input type="hidden" name="member_id" value="<?php echo absint( $row->member_id ); ?>">
						<input type="hidden" name="quiz_id" value="<?php echo absint( $course_quiz->id ); ?>">
						<input type="hidden" name="back_url" value="<?php echo esc_attr( $back_url ); ?>">
						<button type="submit" class="button button-small">↺ Resetar</button>
					</form>
					<?php else: ?>
					<em style="color:#aaa">—</em>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>
</div>
