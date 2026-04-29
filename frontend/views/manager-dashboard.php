<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="kt-portal kt-manager-dashboard">

	<div class="kt-portal-header">
		<h2>Painel do Gerente</h2>
		<?php if ( $location ): ?>
			<p class="kt-welcome">Unidade: <strong><?php echo esc_html( $location->name ); ?></strong></p>
		<?php endif; ?>
	</div>

	<?php if ( KT_Roles::is_super_admin() && $locations ): ?>
	<form method="get" style="margin-bottom:24px;display:flex;gap:12px;align-items:center;flex-wrap:wrap">
		<input type="hidden" name="page_id" value="<?php echo get_the_ID(); ?>">
		<label style="font-weight:600">Unidade:</label>
		<select name="location_id" onchange="this.form.submit()" style="min-width:200px;padding:6px 10px;border:1px solid #e2e8f0;border-radius:6px">
			<option value="">— Selecione —</option>
			<?php foreach ( $locations as $loc ): ?>
				<option value="<?php echo absint( $loc->id ); ?>" <?php selected( $location_id, $loc->id ); ?>><?php echo esc_html( $loc->name ); ?></option>
			<?php endforeach; ?>
		</select>
	</form>
	<?php endif; ?>

	<?php if ( ! $location_id ): ?>
	<div class="kt-empty-state"><p>Selecione uma unidade para visualizar o painel.</p></div>
	<?php return; ?>
	<?php endif; ?>

	<!-- Stats -->
	<div class="kt-manager-stats">
		<div class="kt-manager-stat">
			<div class="kt-manager-stat-value"><?php echo $total_members; ?></div>
			<div class="kt-manager-stat-label">Colaboradores</div>
		</div>
		<div class="kt-manager-stat">
			<div class="kt-manager-stat-value"><?php echo $total_enrollments; ?></div>
			<div class="kt-manager-stat-label">Matrículas ativas</div>
		</div>
		<div class="kt-manager-stat">
			<div class="kt-manager-stat-value"><?php echo $completion_rate; ?>%</div>
			<div class="kt-manager-stat-label">Taxa de conclusão</div>
		</div>
	</div>

	<!-- Atribuir Treinamento -->
	<?php if ( $courses ): ?>
	<div class="kt-manager-enroll-box">
		<h3>Atribuir Treinamento</h3>

		<!-- Individual -->
		<div class="kt-enroll-form-row">
			<div>
				<label>Colaborador</label>
				<select id="kt-enroll-member" style="min-width:180px">
					<option value="">— Selecione —</option>
					<?php foreach ( $members as $m ): ?>
						<option value="<?php echo absint( $m->id ); ?>"><?php echo esc_html( $m->display_name ?: $m->user_login ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div>
				<label>Curso</label>
				<select id="kt-enroll-course" style="min-width:200px">
					<option value="">— Selecione —</option>
					<?php foreach ( $courses as $c ): ?>
						<option value="<?php echo absint( $c->id ); ?>"><?php echo esc_html( $c->title ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div>
				<label>Prazo (opcional)</label>
				<input type="date" id="kt-enroll-due">
			</div>
			<button type="button" id="kt-enroll-btn" class="kt-btn kt-btn-primary">Atribuir</button>
		</div>

		<!-- Bulk: toda a unidade -->
		<div class="kt-enroll-form-row kt-enroll-bulk-row">
			<span class="kt-enroll-bulk-label">Toda a unidade →</span>
			<div>
				<label>Curso</label>
				<select id="kt-bulk-course" style="min-width:200px">
					<option value="">— Selecione —</option>
					<?php foreach ( $courses as $c ): ?>
						<option value="<?php echo absint( $c->id ); ?>"><?php echo esc_html( $c->title ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div>
				<label>Prazo (opcional)</label>
				<input type="date" id="kt-bulk-due">
			</div>
			<button type="button" id="kt-bulk-btn" class="kt-btn kt-btn-outline">Atribuir a todos</button>
		</div>

		<p id="kt-enroll-msg" style="margin:10px 0 0;font-size:.88em;min-height:1.2em"></p>
	</div>
	<?php endif; ?>

	<!-- Tabela compacta de colaboradores -->
	<?php if ( ! $members ): ?>
	<div class="kt-empty-state"><p>Nenhum colaborador cadastrado nesta unidade.</p></div>
	<?php else: ?>
	<div class="kt-manager-members">
		<h3>Colaboradores e Progresso</h3>
		<table class="kt-members-table">
			<thead>
				<tr>
					<th style="width:200px">Colaborador</th>
					<th style="width:120px">Função</th>
					<th>Treinamentos <span style="font-size:.85em;opacity:.6;font-weight:400">(clique × para remover e zerar progresso)</span></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $members as $m ):
				$enrs = $member_progress[ $m->id ] ?? [];
			?>
			<tr>
				<td>
					<div class="kt-member-name"><?php echo esc_html( $m->display_name ?: $m->user_login ); ?></div>
					<div class="kt-member-email"><?php echo esc_html( $m->user_email ); ?></div>
				</td>
				<td>
					<?php if ( $m->position_name ): ?>
						<span class="kt-manager-position"><?php echo esc_html( $m->position_name ); ?></span>
					<?php else: ?>
						<span style="color:#94a3b8">—</span>
					<?php endif; ?>
				</td>
				<td>
					<div class="kt-enroll-chips" id="kt-chips-<?php echo absint( $m->id ); ?>">
						<?php foreach ( $enrs as $e ):
							$pct     = KT_Progress::course_progress_pct( $m->id, $e->course_id );
							$overdue = $e->due_date && strtotime( $e->due_date ) < time() && $e->status !== 'concluido';
							$chip_class = $overdue ? 'overdue' : esc_attr( $e->status );
							$short = mb_strlen( $e->course_title ) > 26
								? mb_substr( $e->course_title, 0, 24 ) . '…'
								: $e->course_title;
						?>
						<span class="kt-enroll-chip kt-enroll-chip-<?php echo $chip_class; ?>">
							<span class="kt-chip-title" title="<?php echo esc_attr( $e->course_title ); ?>"><?php echo esc_html( $short ); ?></span>
							<span class="kt-chip-pct"> · <?php echo $pct; ?>%</span>
							<button type="button"
								class="kt-chip-remove kt-unenroll-btn"
								data-member-id="<?php echo absint( $m->id ); ?>"
								data-course-id="<?php echo absint( $e->course_id ); ?>"
								title="Remover matrícula e zerar progresso em &quot;<?php echo esc_attr( $e->course_title ); ?>&quot;">×</button>
						</span>
						<?php endforeach; ?>
						<span class="kt-no-enroll" id="kt-noenroll-<?php echo absint( $m->id ); ?>"<?php echo $enrs ? ' style="display:none"' : ''; ?>>Sem treinamentos atribuídos</span>
					</div>
				</td>
			</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>

</div>

<script>
(function($){

	var locationId = <?php echo absint( $location_id ); ?>;

	function showMsg( msg, ok ) {
		$('#kt-enroll-msg').text( msg ).css( 'color', ok ? '#15803d' : '#b91c1c' );
	}

	function enrollAjax( memberId, courseId, dueDate ) {
		showMsg( 'Salvando…', true );
		$.post( ktFrontend.ajaxUrl, {
			action:      'kt_enroll_member',
			nonce:       ktFrontend.nonce,
			member_id:   memberId,
			course_id:   courseId,
			due_date:    dueDate,
			location_id: locationId
		}).done(function(r){
			showMsg( r.success ? r.data.message : ( r.data && r.data.message ? r.data.message : 'Erro.' ), r.success );
			if ( r.success ) setTimeout(function(){ location.reload(); }, 700 );
		}).fail(function(){
			showMsg( 'Erro de conexão.', false );
		});
	}

	// Atribuição individual
	$('#kt-enroll-btn').on('click', function(){
		var memberId = $('#kt-enroll-member').val();
		var courseId = $('#kt-enroll-course').val();
		if ( ! memberId || ! courseId ) { showMsg( 'Selecione colaborador e curso.', false ); return; }
		enrollAjax( memberId, courseId, $('#kt-enroll-due').val() );
	});

	// Atribuição bulk (toda a unidade)
	$('#kt-bulk-btn').on('click', function(){
		var courseId = $('#kt-bulk-course').val();
		if ( ! courseId ) { showMsg( 'Selecione o curso para atribuir a todos.', false ); return; }
		if ( ! confirm( 'Atribuir este curso a todos os colaboradores da unidade?\n\nColaboradores que já estão matriculados não serão afetados.' ) ) return;
		enrollAjax( 0, courseId, $('#kt-bulk-due').val() );
	});

	// Remover matrícula + zerar progresso (chip ×)
	$(document).on('click', '.kt-unenroll-btn', function(){
		var $btn      = $(this);
		var courseName = $btn.attr('title') ? $btn.attr('title').replace('Remover matrícula e zerar progresso em "','').replace('"','') : 'este curso';
		if ( ! confirm( 'Remover matrícula e zerar todo o progresso em "' + courseName + '"?\n\nO colaborador poderá ser rematriculado para refazer o curso do zero.' ) ) return;

		var $chip  = $btn.closest('.kt-enroll-chip');
		var $chips = $btn.closest('.kt-enroll-chips');

		$.post( ktFrontend.ajaxUrl, {
			action:    'kt_unenroll_member',
			nonce:     ktFrontend.nonce,
			member_id: $btn.data('member-id'),
			course_id: $btn.data('course-id')
		}).done(function(r){
			if ( r.success ) {
				$chip.fadeOut( 200, function(){
					$(this).remove();
					if ( $chips.find('.kt-enroll-chip').length === 0 ) {
						$chips.find('.kt-no-enroll').show();
					}
				});
			}
		}).fail(function(){
			alert( 'Erro de conexão. Tente novamente.' );
		});
	});

})(jQuery);
</script>
