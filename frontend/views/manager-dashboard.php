<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="kt-portal kt-manager-dashboard">

	<div class="kt-portal-header kt-manager-header">
		<div class="kt-manager-header-text">
			<h2>Painel do Gerente</h2>
			<?php
			$_mgr       = wp_get_current_user();
			$_mgr_name  = trim( $_mgr->first_name ) ?: $_mgr->display_name;
			$_portal_url = get_option( 'kt_portal_page_url' );
			?>
			<p class="kt-welcome">Olá, <strong><?php echo esc_html( $_mgr_name ); ?></strong>!<?php if ( $location ): ?> — Unidade: <strong><?php echo esc_html( $location->name ); ?></strong><?php endif; ?></p>
		</div>
		<?php if ( $_portal_url ): ?>
		<a href="<?php echo esc_url( $_portal_url ); ?>" class="kt-btn kt-btn-outline kt-manager-portal-btn">
			📚 Meus Treinamentos
		</a>
		<?php endif; ?>
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
					<?php foreach ( $members as $m ):
						$_display = $m->full_name ?: $m->display_name ?: $m->user_login;
					?>
					<label class="kt-member-check-item" data-name="<?php echo esc_attr( mb_strtolower( $_display, 'UTF-8' ) ); ?>">
						<input type="checkbox" class="kt-member-cb" value="<?php echo absint( $m->id ); ?>">
						<span class="kt-member-check-name"><?php echo esc_html( $_display ); ?></span>
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
					<th>Treinamentos</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $members as $m ):
				$enrs        = $member_progress[ $m->id ] ?? [];
				$_name       = $m->full_name ?: $m->display_name ?: $m->user_login;

				// Calcula média de progresso e detecta atraso
				$pcts = [];
				$has_overdue = false;
				$enrs_for_json = [];
				foreach ( $enrs as $e ) {
					$pct     = KT_Progress::course_progress_pct( $m->id, $e->course_id );
					$overdue = $e->due_date && strtotime( $e->due_date ) < time() && $e->status !== 'concluido';
					if ( $overdue ) $has_overdue = true;
					$pcts[] = $pct;
					$enrs_for_json[] = [
						'course_id'    => (int) $e->course_id,
						'course_title' => $e->course_title,
						'status'       => $e->status,
						'overdue'      => $overdue,
						'due_date'     => $e->due_date ?: '',
						'pct'          => $pct,
					];
				}
				$avg_pct   = $pcts ? round( array_sum( $pcts ) / count( $pcts ) ) : 0;
				$enr_count = count( $enrs );
			?>
			<tr>
				<td>
					<div class="kt-member-name"><?php echo esc_html( $_name ); ?></div>
				</td>
				<td>
					<?php if ( $m->position_name ): ?>
						<span class="kt-manager-position"><?php echo esc_html( $m->position_name ); ?></span>
					<?php else: ?>
						<span style="color:#94a3b8">—</span>
					<?php endif; ?>
				</td>
				<td>
					<?php if ( $enrs ): ?>
					<div class="kt-chips-summary" data-target="kt-chips-<?php echo absint( $m->id ); ?>">
						<span class="kt-chips-avg"><?php echo $avg_pct; ?>% <span class="kt-chips-count">(<?php echo $enr_count; ?> curso<?php echo $enr_count !== 1 ? 's' : ''; ?>)</span></span>
						<?php if ( $has_overdue ): ?><span class="kt-chips-overdue-flag">atrasado</span><?php endif; ?>
						<span class="kt-chips-toggle-icon">▾</span>
					</div>
					<div class="kt-chips-drawer" id="kt-chips-<?php echo absint( $m->id ); ?>" style="display:none">
						<?php foreach ( $enrs as $e ):
							$pct        = KT_Progress::course_progress_pct( $m->id, $e->course_id );
							$overdue    = $e->due_date && strtotime( $e->due_date ) < time() && $e->status !== 'concluido';
							$chip_class = $overdue ? 'overdue' : esc_attr( $e->status );
						?>
						<div class="kt-drawer-row">
							<span class="kt-enroll-chip kt-enroll-chip-<?php echo $chip_class; ?>" style="flex-shrink:0">
								<?php echo $pct; ?>%
							</span>
							<span class="kt-drawer-course-title"><?php echo esc_html( $e->course_title ); ?></span>
							<span class="kt-drawer-due">
								<?php if ( $e->due_date ): ?>
									<span class="<?php echo $overdue ? 'kt-due-late' : ''; ?>">
										<?php echo esc_html( date_i18n( 'd/m/Y', strtotime( $e->due_date ) ) ); ?>
									</span>
								<?php else: ?>
									<span style="color:#cbd5e1">—</span>
								<?php endif; ?>
							</span>
							<a href="#"
								class="kt-edit-link kt-edit-member-btn"
								data-member-id="<?php echo absint( $m->id ); ?>"
								data-member-name="<?php echo esc_attr( $_name ); ?>"
								data-enrollments="<?php echo esc_attr( wp_json_encode( $enrs_for_json ) ); ?>">
								Editar →
							</a>
						</div>
						<?php endforeach; ?>
					</div>
					<?php else: ?>
					<span class="kt-no-enroll">Sem treinamentos</span>
					<?php endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>

</div>

<!-- ── Modal de edição do colaborador ── -->
<div id="kt-member-modal-overlay" class="kt-modal-overlay" style="display:none" aria-modal="true" role="dialog">
	<div class="kt-modal">
		<div class="kt-modal-header">
			<h3 id="kt-modal-title">Editar colaborador</h3>
			<button type="button" class="kt-modal-close" id="kt-modal-close" aria-label="Fechar">×</button>
		</div>
		<div class="kt-modal-body" id="kt-modal-body">
			<!-- conteúdo injetado via JS -->
		</div>
	</div>
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

		var $all = $('#kt-select-all');
		var visChecked = $('.kt-member-check-item:not(.kt-item-hidden) .kt-member-cb:checked').length;
		if ( visChecked === 0 ) {
			$all.prop( 'indeterminate', false ).prop( 'checked', false );
		} else if ( visChecked === visible ) {
			$all.prop( 'indeterminate', false ).prop( 'checked', true );
		} else {
			$all.prop( 'indeterminate', true );
		}

		var total = $('.kt-member-check-item').length;
		$('#kt-select-all-label').text( visible === total ? 'Selecionar todos (' + total + ')' : 'Selecionar visíveis (' + visible + ')' );
	}

	/* ── Toggle de treinamentos na tabela ── */
	$(document).on('click', '.kt-chips-summary', function(){
		var $summary = $(this);
		var targetId = $summary.data('target');
		var $drawer  = $('#' + targetId);
		var isOpen   = $summary.hasClass('open');
		$summary.toggleClass('open', ! isOpen);
		if ( isOpen ) {
			$drawer.slideUp( 160 );
		} else {
			$drawer.slideDown( 160 );
		}
	});

	/* ── Evita navegação no link Editar ── */
	$(document).on('click', '.kt-edit-link', function(e){ e.preventDefault(); });

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

	/* ── Atribuir (bulk) ── */
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

	/* ══════════════════════════════════════════
	   Modal de edição do colaborador
	══════════════════════════════════════════ */

	var STATUS_LABEL = {
		nao_iniciado: 'Não iniciado',
		em_andamento: 'Em andamento',
		concluido:    'Concluído'
	};

	function statusChip( status, overdue ) {
		var cls   = overdue ? 'overdue' : status;
		var label = overdue ? 'Atrasado' : ( STATUS_LABEL[ status ] || status );
		return '<span class="kt-enroll-chip kt-enroll-chip-' + cls + '" style="font-size:.78em">' + label + '</span>';
	}

	function openModal( memberId, memberName, enrollments ) {
		$('#kt-modal-title').text( memberName );

		/* ── Cursos disponíveis para o select de atribuição ── */
		var $courseOpts = $('#kt-assign-course option').clone();

		/* ── Monta corpo do modal ── */
		var html = '';

		// Seção: matrículas existentes
		if ( enrollments.length ) {
			html += '<div class="kt-modal-section">';
			html += '<p class="kt-modal-section-title">Treinamentos atribuídos</p>';
			html += '<table class="kt-modal-enroll-table">';
			html += '<thead><tr><th>Curso</th><th>Status</th><th>Prazo</th><th></th></tr></thead>';
			html += '<tbody>';
			$.each( enrollments, function( i, e ) {
				var rowId = 'kt-modal-row-' + memberId + '-' + e.course_id;
				html += '<tr id="' + rowId + '">';
				html += '<td class="kt-modal-course-title" title="' + $('<div>').text(e.course_title).html() + '">' + $('<div>').text(e.course_title).html() + '</td>';
				html += '<td>' + statusChip( e.status, e.overdue ) + ' <small style="color:#94a3b8">' + e.pct + '%</small></td>';
				html += '<td><input type="date" class="kt-modal-due-input" data-member-id="' + memberId + '" data-course-id="' + e.course_id + '" value="' + e.due_date + '" style="border:1px solid #e2e8f0;border-radius:6px;padding:4px 7px;font-size:.82em"></td>';
				html += '<td class="kt-modal-actions">';
				html +=   '<button type="button" class="kt-btn kt-btn-sm kt-modal-save-due" data-member-id="' + memberId + '" data-course-id="' + e.course_id + '">Salvar prazo</button>';
				html +=   ' <button type="button" class="kt-btn kt-btn-sm kt-modal-unenroll" data-member-id="' + memberId + '" data-course-id="' + e.course_id + '" data-course-name="' + $('<div>').text(e.course_title).html() + '" data-row="' + rowId + '" style="color:#b91c1c;border-color:#fca5a5">Remover</button>';
				html += '</td>';
				html += '</tr>';
			});
			html += '</tbody></table>';
			html += '<p class="kt-modal-row-msg" id="kt-modal-enroll-msg"></p>';
			html += '</div>';
		} else {
			html += '<p style="color:#94a3b8;margin-bottom:20px;font-style:italic">Sem treinamentos atribuídos.</p>';
		}

		// Seção: atribuir novo treinamento
		html += '<div class="kt-modal-section kt-modal-assign-section">';
		html += '<p class="kt-modal-section-title">Atribuir novo treinamento</p>';
		html += '<div class="kt-modal-assign-row">';
		html +=   '<select id="kt-modal-course-select" class="kt-modal-course-select"><option value="">— Selecione o curso —</option></select>';
		html +=   '<input type="date" id="kt-modal-assign-due" style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:7px;font-size:.88em">';
		html +=   '<button type="button" id="kt-modal-assign-btn" class="kt-btn kt-btn-primary" data-member-id="' + memberId + '" disabled>Atribuir</button>';
		html += '</div>';
		html += '<p class="kt-modal-row-msg" id="kt-modal-assign-msg"></p>';
		html += '</div>';

		$('#kt-modal-body').html( html );

		// Popula select de cursos (copia do formulário principal)
		$courseOpts.each(function(){
			$('#kt-modal-course-select').append( $(this).clone() );
		});
		$('#kt-modal-course-select').on('change', function(){
			$('#kt-modal-assign-btn').prop('disabled', ! $(this).val() );
		});

		$('#kt-member-modal-overlay').fadeIn( 180 );
		$('body').addClass('kt-modal-open');
	}

	function closeModal() {
		$('#kt-member-modal-overlay').fadeOut( 150 );
		$('body').removeClass('kt-modal-open');
	}

	/* Abrir modal */
	$(document).on('click', '.kt-edit-member-btn', function(){
		var $btn       = $(this);
		var memberId   = $btn.data('member-id');
		var memberName = $btn.data('member-name');
		var enrollments;
		try {
			enrollments = JSON.parse( $btn.attr('data-enrollments') || '[]' );
		} catch(e) {
			enrollments = [];
		}
		openModal( memberId, memberName, enrollments );
	});

	/* Fechar modal */
	$('#kt-modal-close').on('click', closeModal );
	$('#kt-member-modal-overlay').on('click', function(e){
		if ( $(e.target).is('#kt-member-modal-overlay') ) closeModal();
	});
	$(document).on('keydown', function(e){
		if ( e.key === 'Escape' ) closeModal();
	});

	/* ── Salvar prazo ── */
	$(document).on('click', '.kt-modal-save-due', function(){
		var $btn      = $(this);
		var memberId  = $btn.data('member-id');
		var courseId  = $btn.data('course-id');
		var $row      = $btn.closest('tr');
		var dueDate   = $row.find('.kt-modal-due-input').val();

		$btn.prop('disabled', true).text('Salvando…');

		$.post( ktFrontend.ajaxUrl, {
			action:    'kt_update_due_date',
			nonce:     ktFrontend.nonce,
			member_id: memberId,
			course_id: courseId,
			due_date:  dueDate,
		}).done(function(r){
			var ok = r.success;
			$('#kt-modal-enroll-msg').text( ok ? 'Prazo salvo!' : (r.data && r.data.message ? r.data.message : 'Erro.') ).css('color', ok ? '#15803d' : '#b91c1c');
			$btn.prop('disabled', false).text('Salvar prazo');
			if ( ok ) setTimeout(function(){ $('#kt-modal-enroll-msg').text(''); }, 2000 );
		}).fail(function(){
			$('#kt-modal-enroll-msg').text('Erro de conexão.').css('color','#b91c1c');
			$btn.prop('disabled', false).text('Salvar prazo');
		});
	});

	/* ── Remover matrícula (no modal) ── */
	$(document).on('click', '.kt-modal-unenroll', function(){
		var $btn       = $(this);
		var courseName = $btn.data('course-name') || 'este curso';
		var rowId      = $btn.data('row');
		if ( ! confirm( 'Remover matrícula em "' + courseName + '" e zerar todo o progresso?' ) ) return;

		$btn.prop('disabled', true);

		$.post( ktFrontend.ajaxUrl, {
			action:    'kt_unenroll_member',
			nonce:     ktFrontend.nonce,
			member_id: $btn.data('member-id'),
			course_id: $btn.data('course-id'),
		}).done(function(r){
			if ( r.success ) {
				$('#' + rowId).fadeOut( 200, function(){ $(this).remove(); });
				// Atualiza chips na tabela
				var memberId = $btn.data('member-id');
				var courseId = $btn.data('course-id');
				var $chips   = $('#kt-chips-' + memberId);
				$chips.find('.kt-enroll-chip').each(function(){
					// Não temos como identificar o chip pelo course_id facilmente sem data-attr,
					// então fazemos reload após fechar o modal
				});
				$('#kt-modal-enroll-msg').text('Matrícula removida.').css('color','#15803d');
				// Sinaliza que houve mudança — reload ao fechar
				$('#kt-member-modal-overlay').data('needsReload', true);
			}
		}).fail(function(){
			$('#kt-modal-enroll-msg').text('Erro de conexão.').css('color','#b91c1c');
			$btn.prop('disabled', false);
		});
	});

	/* ── Atribuir no modal ── */
	$(document).on('click', '#kt-modal-assign-btn', function(){
		var $btn      = $(this);
		var memberId  = $btn.data('member-id');
		var courseId  = $('#kt-modal-course-select').val();
		var dueDate   = $('#kt-modal-assign-due').val();
		var courseName = $('#kt-modal-course-select option:selected').text();

		if ( ! courseId ) return;
		if ( ! confirm('Atribuir "' + courseName + '" para este colaborador?') ) return;

		$btn.prop('disabled', true).text('Salvando…');

		$.post( ktFrontend.ajaxUrl, {
			action:       'kt_enroll_member',
			nonce:        ktFrontend.nonce,
			course_id:    courseId,
			due_date:     dueDate,
			'member_ids[0]': memberId,
		}).done(function(r){
			var ok = r.success;
			$('#kt-modal-assign-msg')
				.text( ok ? r.data.message : (r.data && r.data.message ? r.data.message : 'Erro.') )
				.css('color', ok ? '#15803d' : '#b91c1c');
			if ( ok ) {
				$btn.text('Atribuído ✓');
				$('#kt-member-modal-overlay').data('needsReload', true);
			} else {
				$btn.prop('disabled', false).text('Atribuir');
			}
		}).fail(function(){
			$('#kt-modal-assign-msg').text('Erro de conexão.').css('color','#b91c1c');
			$btn.prop('disabled', false).text('Atribuir');
		});
	});

	/* ── Reload ao fechar se houve mudanças ── */
	$('#kt-modal-close, #kt-member-modal-overlay').on('click', function(e){
		if ( e.target.id !== 'kt-member-modal-overlay' && e.target.id !== 'kt-modal-close' ) return;
		if ( $('#kt-member-modal-overlay').data('needsReload') ) {
			location.reload();
		}
	});

})(jQuery);
</script>
