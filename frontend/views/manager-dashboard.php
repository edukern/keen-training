<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="kt-portal kt-manager-dashboard">

	<div class="kt-portal-header">
		<h2>Painel do Gerente</h2>
		<?php
		$_mgr      = wp_get_current_user();
		$_mgr_name = trim( $_mgr->first_name ) ?: $_mgr->display_name;
		?>
		<p class="kt-welcome">Olá, <strong><?php echo esc_html( $_mgr_name ); ?></strong>!<?php if ( $location ): ?> — Unidade: <strong><?php echo esc_html( $location->name ); ?></strong><?php endif; ?></p>
	</div>

	<?php if ( ! empty( $quote ) ): ?>
	<div class="kt-daily-quote">
		<div class="kt-daily-quote-mark">&ldquo;</div>
		<div class="kt-daily-quote-body">
			<p class="kt-daily-quote-text"><?php echo esc_html( $quote['text'] ); ?></p>
			<?php if ( $quote['author'] ): ?>
				<span class="kt-daily-quote-author">— <?php echo esc_html( $quote['author'] ); ?></span>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>

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

		<!-- Passo 1: Curso + Prazo -->
		<div class="kt-assign-top-row">
			<div class="kt-assign-field kt-assign-field-course">
				<label>Curso</label>
				<select id="kt-assign-course">
					<option value="">— Selecione o curso —</option>
					<?php foreach ( $courses as $c ): ?>
						<option value="<?php echo absint( $c->id ); ?>"><?php echo esc_html( $c->title ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="kt-assign-field kt-assign-field-date">
				<label>Prazo (opcional)</label>
				<input type="date" id="kt-assign-due">
			</div>
		</div>

		<!-- Passo 2: Selecionar colaboradores (aparece após escolher curso) -->
		<?php if ( $members ): ?>
		<div class="kt-member-picker" id="kt-member-picker" style="display:none">
			<label>Colaboradores</label>
			<div class="kt-member-picker-box">
				<!-- Busca -->
				<div class="kt-member-search-wrap">
					<input type="text" id="kt-member-search" placeholder="&#128269; Buscar por nome…" autocomplete="off">
				</div>
				<!-- Selecionar todos -->
				<div class="kt-member-select-all-row">
					<label>
						<input type="checkbox" id="kt-select-all">
						<span id="kt-select-all-label">Selecionar todos</span>
					</label>
				</div>
				<!-- Lista de membros -->
				<div class="kt-member-list" id="kt-member-list">
					<?php foreach ( $members as $m ): ?>
					<label class="kt-member-check-item" data-name="<?php echo esc_attr( mb_strtolower( $m->display_name ?: $m->user_login, 'UTF-8' ) ); ?>">
						<input type="checkbox" class="kt-member-cb" value="<?php echo absint( $m->id ); ?>">
						<span class="kt-member-check-name"><?php echo esc_html( $m->display_name ?: $m->user_login ); ?></span>
						<?php if ( $m->position_name ): ?>
							<span class="kt-member-check-pos"><?php echo esc_html( $m->position_name ); ?></span>
						<?php endif; ?>
					</label>
					<?php endforeach; ?>
					<div class="kt-member-no-results" id="kt-no-results">Nenhum colaborador encontrado.</div>
				</div>
			</div>

			<!-- Rodapé: contador + botão -->
			<div class="kt-assign-footer">
				<span class="kt-assign-counter"><strong id="kt-selected-count">0</strong> selecionado(s)</span>
				<button type="button" id="kt-assign-btn" class="kt-btn kt-btn-primary" disabled>Atribuir →</button>
			</div>
		</div>
		<?php endif; ?>

		<p id="kt-enroll-msg" style="margin:12px 0 0;font-size:.88em;min-height:1.2em"></p>
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
					<th>Treinamentos <span style="font-size:.85em;opacity:.55;font-weight:400">— × remove e zera o progresso</span></th>
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
							$pct        = KT_Progress::course_progress_pct( $m->id, $e->course_id );
							$overdue    = $e->due_date && strtotime( $e->due_date ) < time() && $e->status !== 'concluido';
							$chip_class = $overdue ? 'overdue' : esc_attr( $e->status );
							$short      = mb_strlen( $e->course_title ) > 26
								? mb_substr( $e->course_title, 0, 24, 'UTF-8' ) . '…'
								: $e->course_title;
						?>
						<span class="kt-enroll-chip kt-enroll-chip-<?php echo $chip_class; ?>">
							<span class="kt-chip-title" title="<?php echo esc_attr( $e->course_title ); ?>"><?php echo esc_html( $short ); ?></span>
							<span class="kt-chip-pct"> · <?php echo $pct; ?>%</span>
							<button type="button"
								class="kt-chip-remove kt-unenroll-btn"
								data-member-id="<?php echo absint( $m->id ); ?>"
								data-course-id="<?php echo absint( $e->course_id ); ?>"
								data-course-name="<?php echo esc_attr( $e->course_title ); ?>"
								title="Remover matrícula">×</button>
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

	/* ── Helpers ── */
	function showMsg( msg, ok ) {
		$('#kt-enroll-msg').text( msg ).css( 'color', ok ? '#15803d' : '#b91c1c' );
	}

	function getCheckedIds() {
		return $('.kt-member-cb:checked').map(function(){ return parseInt($(this).val()); }).get();
	}

	function updateCounter() {
		var n       = getCheckedIds().length;
		var visible = $('.kt-member-check-item:not(.kt-item-hidden)').length;

		$('#kt-selected-count').text( n );
		$('#kt-assign-btn').prop( 'disabled', n === 0 );

		// Atualiza estado do "selecionar todos"
		var $all = $('#kt-select-all');
		var visChecked = $('.kt-member-check-item:not(.kt-item-hidden) .kt-member-cb:checked').length;
		if ( visChecked === 0 ) {
			$all.prop( 'indeterminate', false ).prop( 'checked', false );
		} else if ( visChecked === visible ) {
			$all.prop( 'indeterminate', false ).prop( 'checked', true );
		} else {
			$all.prop( 'indeterminate', true );
		}

		// Atualiza label do select-all
		var total = $('.kt-member-check-item').length;
		$('#kt-select-all-label').text( visible === total ? 'Selecionar todos (' + total + ')' : 'Selecionar visíveis (' + visible + ')' );
	}

	/* ── Mostrar picker ao selecionar curso ── */
	$('#kt-assign-course').on('change', function(){
		if ( $(this).val() ) {
			$('#kt-member-picker').slideDown( 180 );
		} else {
			$('#kt-member-picker').slideUp( 180 );
			$('.kt-member-cb').prop( 'checked', false );
			updateCounter();
		}
	});

	/* ── Busca de membros ── */
	$('#kt-member-search').on('input', function(){
		var q = $(this).val().toLowerCase().trim();
		var hasVisible = false;
		$('.kt-member-check-item').each(function(){
			var name = $(this).data('name') || '';
			var match = ! q || name.indexOf( q ) !== -1;
			$(this).toggleClass( 'kt-item-hidden', ! match );
			if ( match ) hasVisible = true;
		});
		$('#kt-no-results').toggle( ! hasVisible );
		updateCounter();
	});

	/* ── Selecionar todos (visíveis) ── */
	$('#kt-select-all').on('change', function(){
		var checked = $(this).prop('checked');
		$('.kt-member-check-item:not(.kt-item-hidden) .kt-member-cb').prop( 'checked', checked );
		updateCounter();
	});

	/* ── Checkbox individual ── */
	$(document).on('change', '.kt-member-cb', function(){
		updateCounter();
	});

	/* ── Atribuir ── */
	$('#kt-assign-btn').on('click', function(){
		var courseId   = $('#kt-assign-course').val();
		var memberIds  = getCheckedIds();
		var dueDate    = $('#kt-assign-due').val();
		var courseName = $('#kt-assign-course option:selected').text();

		if ( ! courseId ) { showMsg( 'Selecione um curso.', false ); return; }
		if ( memberIds.length === 0 ) { showMsg( 'Selecione ao menos um colaborador.', false ); return; }

		var confirmMsg = memberIds.length === 1
			? 'Atribuir "' + courseName + '" para 1 colaborador?'
			: 'Atribuir "' + courseName + '" para ' + memberIds.length + ' colaboradores?';
		if ( ! confirm( confirmMsg ) ) return;

		showMsg( 'Salvando…', true );
		$('#kt-assign-btn').prop( 'disabled', true ).text( 'Salvando…' );

		var data = {
			action:    'kt_enroll_member',
			nonce:     ktFrontend.nonce,
			course_id: courseId,
			due_date:  dueDate,
		};
		$.each( memberIds, function( i, id ){ data[ 'member_ids[' + i + ']' ] = id; });

		$.post( ktFrontend.ajaxUrl, data ).done(function(r){
			showMsg( r.success ? r.data.message : ( r.data && r.data.message ? r.data.message : 'Erro.' ), r.success );
			if ( r.success ) setTimeout(function(){ location.reload(); }, 700 );
			else $('#kt-assign-btn').prop('disabled', false).text('Atribuir →');
		}).fail(function(){
			showMsg( 'Erro de conexão.', false );
			$('#kt-assign-btn').prop('disabled', false).text('Atribuir →');
		});
	});

	/* ── Remover matrícula (chip ×) ── */
	$(document).on('click', '.kt-unenroll-btn', function(){
		var $btn       = $(this);
		var courseName = $btn.data('course-name') || 'este curso';
		if ( ! confirm( 'Remover matrícula em "' + courseName + '" e zerar todo o progresso?\n\nO colaborador poderá ser rematriculado para refazer do zero.' ) ) return;

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
