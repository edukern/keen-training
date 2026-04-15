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

	<!-- Atribuir curso -->
	<div class="kt-manager-enroll-box">
		<h3>Atribuir Treinamento</h3>
		<div class="kt-manager-enroll-form" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
			<div>
				<label style="display:block;font-size:.85em;font-weight:600;margin-bottom:4px">Colaborador</label>
				<select id="kt-enroll-member" style="min-width:200px;padding:7px 10px;border:1px solid #e2e8f0;border-radius:6px">
					<option value="">— Selecione —</option>
					<?php foreach ( $members as $m ): ?>
						<option value="<?php echo absint( $m->id ); ?>"><?php echo esc_html( $m->display_name ?: $m->user_login ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div>
				<label style="display:block;font-size:.85em;font-weight:600;margin-bottom:4px">Curso</label>
				<select id="kt-enroll-course" style="min-width:200px;padding:7px 10px;border:1px solid #e2e8f0;border-radius:6px">
					<option value="">— Selecione —</option>
					<?php foreach ( $courses as $c ): ?>
						<option value="<?php echo absint( $c->id ); ?>"><?php echo esc_html( $c->title ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div>
				<label style="display:block;font-size:.85em;font-weight:600;margin-bottom:4px">Prazo (opcional)</label>
				<input type="date" id="kt-enroll-due" style="padding:7px 10px;border:1px solid #e2e8f0;border-radius:6px">
			</div>
			<button type="button" id="kt-enroll-btn" class="kt-btn kt-btn-primary">Atribuir</button>
		</div>
		<p id="kt-enroll-msg" style="margin:8px 0 0;font-size:.88em"></p>
	</div>

	<!-- Lista de colaboradores -->
	<?php if ( ! $members ): ?>
	<div class="kt-empty-state"><p>Nenhum colaborador cadastrado nesta unidade.</p></div>
	<?php else: ?>
	<div class="kt-manager-members">
		<h3>Colaboradores e Progresso</h3>
		<?php foreach ( $members as $m ):
			$enrs = $member_progress[ $m->id ] ?? [];
		?>
		<div class="kt-manager-member-row">
			<div class="kt-manager-member-info">
				<strong><?php echo esc_html( $m->display_name ?: $m->user_login ); ?></strong>
				<?php if ( $m->position_name ): ?>
					<span class="kt-manager-position"><?php echo esc_html( $m->position_name ); ?></span>
				<?php endif; ?>
			</div>
			<?php if ( $enrs ): ?>
			<div class="kt-manager-enrollments">
				<?php foreach ( $enrs as $e ):
					$pct     = KT_Progress::course_progress_pct( $m->id, $e->course_id );
					$overdue = $e->due_date && strtotime( $e->due_date ) < time() && $e->status !== 'concluido';
				?>
				<div class="kt-manager-enroll-row">
					<div class="kt-manager-enroll-title">
						<?php echo esc_html( $e->course_title ); ?>
						<?php if ( $overdue ): ?><span class="kt-status-badge kt-status-overdue" style="font-size:.75em">Atrasado</span><?php endif; ?>
					</div>
					<div class="kt-manager-enroll-progress">
						<div class="kt-progress-bar" style="flex:1;max-width:200px">
							<div class="kt-progress-fill" style="width:<?php echo $pct; ?>%"></div>
						</div>
						<span style="font-size:.82em;color:#64748b;white-space:nowrap"><?php echo $pct; ?>%</span>
						<span class="kt-status-badge kt-status-<?php echo esc_attr( $e->status ); ?>"><?php echo esc_html( KT_Progress::status_label( $e->status ) ); ?></span>
					</div>
					<?php if ( $e->due_date ): ?>
					<div style="font-size:.8em;color:<?php echo $overdue ? '#b91c1c' : '#64748b'; ?>">
						Prazo: <?php echo esc_html( date_i18n( 'd/m/Y', strtotime( $e->due_date ) ) ); ?>
					</div>
					<?php endif; ?>
					<button type="button"
						class="kt-btn kt-btn-sm kt-unenroll-btn"
						data-member-id="<?php echo absint( $m->id ); ?>"
						data-course-id="<?php echo absint( $e->course_id ); ?>"
						style="margin-top:4px">
						Remover matrícula
					</button>
				</div>
				<?php endforeach; ?>
			</div>
			<?php else: ?>
			<div class="kt-manager-enrollments" style="color:#94a3b8;font-size:.88em;padding:8px 0">
				Sem treinamentos atribuídos.
			</div>
			<?php endif; ?>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

</div>

<script>
(function($){
	$('#kt-enroll-btn').on('click', function(){
		var memberId = $('#kt-enroll-member').val();
		var courseId = $('#kt-enroll-course').val();
		var dueDate  = $('#kt-enroll-due').val();
		var $msg     = $('#kt-enroll-msg');
		if ( !memberId || !courseId ) { $msg.text('Selecione colaborador e curso.').css('color','#b91c1c'); return; }
		$.post(ktFrontend.ajaxUrl, {
			action: 'kt_enroll_member', nonce: ktFrontend.nonce,
			member_id: memberId, course_id: courseId, due_date: dueDate
		}).done(function(r){
			$msg.text( r.success ? r.data.message : (r.data && r.data.message ? r.data.message : 'Erro.') )
				.css('color', r.success ? '#15803d' : '#b91c1c');
			if ( r.success ) setTimeout(function(){ location.reload(); }, 800);
		}).fail(function(){ $msg.text('Erro de conexão.').css('color','#b91c1c'); });
	});

	$(document).on('click', '.kt-unenroll-btn', function(){
		if ( !confirm('Remover matrícula deste colaborador neste curso?') ) return;
		var $btn = $(this);
		$.post(ktFrontend.ajaxUrl, {
			action: 'kt_unenroll_member', nonce: ktFrontend.nonce,
			member_id: $btn.data('member-id'), course_id: $btn.data('course-id')
		}).done(function(r){
			if ( r.success ) $btn.closest('.kt-manager-enroll-row').fadeOut(300, function(){ $(this).remove(); });
		});
	});
})(jQuery);
</script>
