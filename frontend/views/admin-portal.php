<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
// Mapeia gerente de cada unidade
$manager_map = [];
foreach ( $kt_users as $u ) {
	$loc = (int) get_user_meta( $u->ID, 'kt_location_id', true );
	if ( $loc ) $manager_map[ $loc ] = $u;
}
$current_url = get_permalink();
?>
<div class="kt-portal kt-admin-portal">

	<!-- ── Header ─────────────────────────────────────────── -->
	<div class="kt-portal-header kt-manager-header">
		<div class="kt-manager-header-text">
			<h2>Painel do Administrador</h2>
			<?php $cu = wp_get_current_user(); $cu_name = trim( $cu->first_name ) ?: $cu->display_name; ?>
			<p class="kt-welcome">Olá, <strong><?php echo esc_html( $cu_name ); ?></strong>!</p>
		</div>
		<?php $portal_url = get_option( 'kt_portal_page_url' ); $manager_url = get_option( 'kt_manager_page_url' ); ?>
		<div style="display:flex;gap:10px;flex-wrap:wrap">
			<?php if ( $manager_url ): ?>
			<a href="<?php echo esc_url( $manager_url ); ?>" class="kt-btn kt-btn-outline">📊 Painel do Gerente</a>
			<?php endif; ?>
			<?php if ( $portal_url ): ?>
			<a href="<?php echo esc_url( $portal_url ); ?>" class="kt-btn kt-btn-outline">📚 Meus Treinamentos</a>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( ! empty( $quote ) ): ?>
	<div class="kt-daily-quote">
		<div class="kt-daily-quote-mark">&ldquo;</div>
		<div class="kt-daily-quote-body">
			<p class="kt-daily-quote-text"><?php echo esc_html( $quote['text'] ); ?></p>
			<?php if ( $quote['author'] ): ?><span class="kt-daily-quote-author">— <?php echo esc_html( $quote['author'] ); ?></span><?php endif; ?>
		</div>
	</div>
	<?php endif; ?>

	<!-- ── Stats globais ───────────────────────────────────── -->
	<div class="kt-manager-stats" style="margin-bottom:28px">
		<div class="kt-manager-stat">
			<div class="kt-manager-stat-value"><?php echo $total_units; ?></div>
			<div class="kt-manager-stat-label">Unidades</div>
		</div>
		<div class="kt-manager-stat">
			<div class="kt-manager-stat-value"><?php echo $total_members_all; ?></div>
			<div class="kt-manager-stat-label">Colaboradores</div>
		</div>
		<div class="kt-manager-stat">
			<div class="kt-manager-stat-value"><?php echo $total_enr; ?></div>
			<div class="kt-manager-stat-label">Matrículas ativas</div>
		</div>
		<div class="kt-manager-stat">
			<div class="kt-manager-stat-value"><?php echo $completion_rate; ?>%</div>
			<div class="kt-manager-stat-label">Taxa de conclusão</div>
		</div>
	</div>

	<!-- ── Tabs ────────────────────────────────────────────── -->
	<div class="kt-admin-tabs">
		<a href="<?php echo esc_url( add_query_arg( 'kt_tab', 'painel', $current_url ) ); ?>"
		   class="kt-admin-tab <?php echo $active_tab === 'painel'   ? 'active' : ''; ?>">📊 Painel</a>
		<a href="<?php echo esc_url( add_query_arg( 'kt_tab', 'unidades', $current_url ) ); ?>"
		   class="kt-admin-tab <?php echo $active_tab === 'unidades' ? 'active' : ''; ?>">🏢 Unidades</a>
		<a href="<?php echo esc_url( add_query_arg( 'kt_tab', 'usuarios', $current_url ) ); ?>"
		   class="kt-admin-tab <?php echo $active_tab === 'usuarios' ? 'active' : ''; ?>">👤 Usuários</a>
		<a href="<?php echo esc_url( add_query_arg( 'kt_tab', 'funcoes', $current_url ) ); ?>"
		   class="kt-admin-tab <?php echo $active_tab === 'funcoes'  ? 'active' : ''; ?>">🏷 Funções</a>
	</div>

	<!-- ══════════════════════════════════════════════════════
	     TAB: PAINEL
	══════════════════════════════════════════════════════ -->
	<?php if ( $active_tab === 'painel' ): ?>
	<div class="kt-admin-tab-content">

		<form method="get" style="margin-bottom:24px;display:flex;gap:12px;align-items:center;flex-wrap:wrap">
			<?php foreach ( $_GET as $k => $v ): if ( $k === 'kt_location' ) continue; ?>
				<input type="hidden" name="<?php echo esc_attr($k); ?>" value="<?php echo esc_attr($v); ?>">
			<?php endforeach; ?>
			<label style="font-weight:600">Unidade:</label>
			<select name="kt_location" onchange="this.form.submit()" style="min-width:220px;padding:7px 10px;border:1px solid #e2e8f0;border-radius:7px">
				<option value="">— Selecione —</option>
				<?php foreach ( $locations as $loc ): ?>
					<option value="<?php echo absint($loc->id); ?>" <?php selected($location_id, $loc->id); ?>><?php echo esc_html($loc->name); ?></option>
				<?php endforeach; ?>
			</select>
		</form>

		<?php if ( ! $location_id ): ?>
		<div class="kt-empty-state"><p>Selecione uma unidade para ver colaboradores e matrículas.</p></div>

		<?php else: ?>

		<!-- Stats da unidade -->
		<?php
		$u_enr = 0; $u_done = 0;
		foreach ( $member_progress as $enrs ) {
			foreach ( $enrs as $e ) {
				$u_enr++;
				if ( $e->status === 'concluido' ) $u_done++;
			}
		}
		$u_rate = $u_enr > 0 ? round( $u_done / $u_enr * 100 ) : 0;
		?>
		<div class="kt-manager-stats" style="margin-bottom:20px">
			<div class="kt-manager-stat">
				<div class="kt-manager-stat-value"><?php echo count($tab_members); ?></div>
				<div class="kt-manager-stat-label">Colaboradores</div>
			</div>
			<div class="kt-manager-stat">
				<div class="kt-manager-stat-value"><?php echo $u_enr; ?></div>
				<div class="kt-manager-stat-label">Matrículas</div>
			</div>
			<div class="kt-manager-stat">
				<div class="kt-manager-stat-value"><?php echo $u_rate; ?>%</div>
				<div class="kt-manager-stat-label">Conclusão</div>
			</div>
		</div>

		<!-- Atribuir treinamento -->
		<?php if ( $courses && $tab_members ): ?>
		<div class="kt-manager-enroll-box">
			<h3>Atribuir Treinamento</h3>
			<div class="kt-assign-top-row">
				<div class="kt-assign-field kt-assign-field-course">
					<label>Curso</label>
					<select id="kt-assign-course">
						<option value="">— Selecione o curso —</option>
						<?php foreach ( $courses as $c ): ?>
							<option value="<?php echo absint($c->id); ?>"><?php echo esc_html($c->title); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="kt-assign-field kt-assign-field-date">
					<label>Prazo (opcional)</label>
					<input type="date" id="kt-assign-due">
				</div>
			</div>
			<div class="kt-member-picker" id="kt-member-picker" style="display:none">
				<label>Colaboradores</label>
				<div class="kt-member-picker-box">
					<div class="kt-member-search-wrap">
						<input type="text" id="kt-member-search" placeholder="🔍 Buscar por nome…" autocomplete="off">
					</div>
					<div class="kt-member-select-all-row">
						<label><input type="checkbox" id="kt-select-all"> <span id="kt-select-all-label">Selecionar todos</span></label>
					</div>
					<div class="kt-member-list" id="kt-member-list">
						<?php foreach ( $tab_members as $m ):
							$_d = $m->full_name ?: $m->display_name ?: $m->user_login;
						?>
						<label class="kt-member-check-item" data-name="<?php echo esc_attr(mb_strtolower($_d,'UTF-8')); ?>">
							<input type="checkbox" class="kt-member-cb" value="<?php echo absint($m->id); ?>">
							<span class="kt-member-check-name"><?php echo esc_html($_d); ?></span>
							<?php if ($m->position_name): ?><span class="kt-member-check-pos"><?php echo esc_html($m->position_name); ?></span><?php endif; ?>
						</label>
						<?php endforeach; ?>
						<div class="kt-member-no-results" id="kt-no-results">Nenhum colaborador encontrado.</div>
					</div>
				</div>
				<div class="kt-assign-footer">
					<span class="kt-assign-counter"><strong id="kt-selected-count">0</strong> selecionado(s)</span>
					<button type="button" id="kt-assign-btn" class="kt-btn kt-btn-primary" disabled>Atribuir →</button>
				</div>
			</div>
			<p id="kt-enroll-msg" style="margin:12px 0 0;font-size:.88em;min-height:1.2em"></p>
		</div>
		<?php endif; ?>

		<!-- Tabela de colaboradores -->
		<?php if ( ! $tab_members ): ?>
		<div class="kt-empty-state"><p>Nenhum colaborador nesta unidade.</p></div>
		<?php else: ?>
		<div class="kt-manager-members">
			<h3>Colaboradores — <?php echo esc_html($location->name); ?></h3>
			<table class="kt-members-table">
				<thead><tr>
					<th>Colaborador</th>
					<th>Função</th>
					<th>Treinamentos</th>
				</tr></thead>
				<tbody>
				<?php foreach ( $tab_members as $m ):
					$enrs        = $member_progress[$m->id] ?? [];
					$_name       = $m->full_name ?: $m->display_name ?: $m->user_login;
					$pcts = []; $has_overdue = false; $enrs_json = [];
					foreach ( $enrs as $e ) {
						$pct     = KT_Progress::course_progress_pct($m->id, $e->course_id);
						$overdue = $e->due_date && strtotime($e->due_date) < time() && $e->status !== 'concluido';
						if ($overdue) $has_overdue = true;
						$pcts[] = $pct;
						$enrs_json[] = ['course_id'=>(int)$e->course_id,'course_title'=>$e->course_title,'status'=>$e->status,'overdue'=>$overdue,'due_date'=>$e->due_date?:'','pct'=>$pct];
					}
					$avg_pct = $pcts ? round(array_sum($pcts)/count($pcts)) : 0;
				?>
				<tr>
					<td><div class="kt-member-name"><?php echo esc_html($_name); ?></div></td>
					<td><?php if ($m->position_name): ?><span class="kt-manager-position"><?php echo esc_html($m->position_name); ?></span><?php else: ?><span style="color:#94a3b8">—</span><?php endif; ?></td>
					<td>
						<?php if ($enrs): ?>
						<div class="kt-chips-summary" data-target="kt-chips-<?php echo absint($m->id); ?>">
							<span class="kt-chips-avg"><?php echo $avg_pct; ?>% <span class="kt-chips-count">(<?php echo count($enrs); ?> curso<?php echo count($enrs)!==1?'s':''; ?>)</span></span>
							<?php if ($has_overdue): ?><span class="kt-chips-overdue-flag">atrasado</span><?php endif; ?>
							<span class="kt-chips-toggle-icon">▾</span>
						</div>
						<div class="kt-chips-drawer" id="kt-chips-<?php echo absint($m->id); ?>" style="display:none">
							<?php foreach ($enrs as $e):
								$pct=$KT_Progress::course_progress_pct($m->id,$e->course_id);
								$overdue=$e->due_date&&strtotime($e->due_date)<time()&&$e->status!=='concluido';
							?>
							<div class="kt-drawer-row">
								<span class="kt-enroll-chip kt-enroll-chip-<?php echo $overdue?'overdue':esc_attr($e->status); ?>" style="flex-shrink:0"><?php echo KT_Progress::course_progress_pct($m->id,$e->course_id); ?>%</span>
								<span class="kt-drawer-course-title"><?php echo esc_html($e->course_title); ?></span>
								<a href="#" class="kt-edit-link kt-edit-member-btn"
								   data-member-id="<?php echo absint($m->id); ?>"
								   data-member-name="<?php echo esc_attr($_name); ?>"
								   data-enrollments="<?php echo esc_attr(wp_json_encode($enrs_json)); ?>">Editar →</a>
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
		<?php endif; // tab_members ?>
		<?php endif; // location_id ?>
	</div><!-- /tab painel -->

	<!-- ══════════════════════════════════════════════════════
	     TAB: UNIDADES
	══════════════════════════════════════════════════════ -->
	<?php elseif ( $active_tab === 'unidades' ): ?>
	<div class="kt-admin-tab-content">
		<h3 style="margin-bottom:16px">Unidades cadastradas</h3>
		<table class="kt-members-table" id="kt-locations-table">
			<thead><tr>
				<th>Nome</th>
				<th>Gerente</th>
				<th>Colaboradores</th>
				<th style="width:120px"></th>
			</tr></thead>
			<tbody>
			<?php foreach ( $locations as $loc ):
				$mgr_user  = isset($manager_map[$loc->id]) ? $manager_map[$loc->id] : null;
				$mgr_name  = $mgr_user ? $mgr_user->display_name : '—';
				$mem_count = KT_Location::get_member_count($loc->id);
			?>
			<tr id="kt-loc-row-<?php echo absint($loc->id); ?>">
				<td class="kt-loc-name-cell"><?php echo esc_html($loc->name); ?></td>
				<td class="kt-loc-mgr-cell"><?php echo esc_html($mgr_name); ?></td>
				<td><?php echo $mem_count; ?></td>
				<td>
					<button type="button" class="kt-btn kt-btn-sm kt-loc-edit-btn"
					        data-id="<?php echo absint($loc->id); ?>"
					        data-name="<?php echo esc_attr($loc->name); ?>"
					        data-manager="<?php echo absint($loc->manager_id ?? 0); ?>">Editar</button>
				</td>
			</tr>
			<?php endforeach; ?>
			<?php if (!$locations): ?>
			<tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:20px">Nenhuma unidade cadastrada.</td></tr>
			<?php endif; ?>
			</tbody>
		</table>

		<!-- Adicionar unidade -->
		<div class="kt-manager-enroll-box" style="margin-top:32px">
			<h3>+ Adicionar Unidade</h3>
			<div class="kt-assign-top-row" style="align-items:flex-end">
				<div class="kt-assign-field" style="flex:1">
					<label>Nome da unidade</label>
					<input type="text" id="kt-new-loc-name" placeholder="Ex: Novo Hamburgo" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:7px">
				</div>
				<div class="kt-assign-field" style="flex:1">
					<label>Gerente (opcional)</label>
					<select id="kt-new-loc-manager" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:7px">
						<option value="">— Sem gerente —</option>
						<?php foreach ( $kt_users as $u ): ?>
						<option value="<?php echo absint($u->ID); ?>"><?php echo esc_html($u->display_name); ?> (<?php echo esc_html(KT_Roles::role_label($u->roles[0]??'')); ?>)</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div>
					<button type="button" id="kt-add-loc-btn" class="kt-btn kt-btn-primary">Adicionar</button>
				</div>
			</div>
			<p id="kt-loc-msg" style="margin:10px 0 0;font-size:.88em;min-height:1.2em"></p>
		</div>
	</div><!-- /tab unidades -->

	<!-- ══════════════════════════════════════════════════════
	     TAB: USUÁRIOS
	══════════════════════════════════════════════════════ -->
	<?php elseif ( $active_tab === 'usuarios' ): ?>
	<div class="kt-admin-tab-content">
		<div class="kt-manager-enroll-box">
			<h3>Criar Novo Usuário</h3>
			<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
				<div>
					<label style="display:block;margin-bottom:4px;font-weight:600;font-size:.9em">Nome <span style="color:#ef4444">*</span></label>
					<input type="text" id="kt-u-first" placeholder="Nome" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:7px;box-sizing:border-box">
				</div>
				<div>
					<label style="display:block;margin-bottom:4px;font-weight:600;font-size:.9em">Sobrenome</label>
					<input type="text" id="kt-u-last" placeholder="Sobrenome" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:7px;box-sizing:border-box">
				</div>
				<div>
					<label style="display:block;margin-bottom:4px;font-weight:600;font-size:.9em">E-mail <span style="color:#ef4444">*</span></label>
					<input type="email" id="kt-u-email" placeholder="email@exemplo.com" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:7px;box-sizing:border-box">
				</div>
				<div>
					<label style="display:block;margin-bottom:4px;font-weight:600;font-size:.9em">Função <span style="color:#ef4444">*</span></label>
					<select id="kt-u-role" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:7px;box-sizing:border-box">
						<option value="">— Selecione —</option>
						<option value="kt_admin">Administrador</option>
						<option value="kt_location_manager">Gerente de Unidade</option>
						<option value="kt_staff">Colaborador</option>
					</select>
				</div>
			</div>

			<!-- Campo de unidade (aparece para Gerente e Colaborador) -->
			<div id="kt-u-location-wrap" style="display:none;margin-bottom:16px">
				<label style="display:block;margin-bottom:4px;font-weight:600;font-size:.9em" id="kt-u-location-label">Unidade</label>
				<select id="kt-u-location" style="min-width:280px;padding:8px 12px;border:1px solid #e2e8f0;border-radius:7px">
					<option value="">— Selecione a unidade —</option>
					<?php foreach ( $locations as $loc ): ?>
					<option value="<?php echo absint($loc->id); ?>"><?php echo esc_html($loc->name); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
				<label style="display:flex;align-items:center;gap:8px;font-size:.9em;cursor:pointer">
					<input type="checkbox" id="kt-u-send-email" checked>
					Enviar e-mail com link para definição de senha
				</label>
				<button type="button" id="kt-create-user-btn" class="kt-btn kt-btn-primary">Criar usuário</button>
			</div>
			<p id="kt-user-msg" style="margin:12px 0 0;font-size:.88em;min-height:1.2em"></p>
		</div>

		<!-- Lista de usuários KT -->
		<h3 style="margin:32px 0 12px">Usuários do sistema</h3>
		<?php
		$kt_all_users = get_users(['role__in'=>['kt_admin','kt_super_admin','kt_location_manager','kt_staff','administrator'],'number'=>200,'orderby'=>'display_name','order'=>'ASC']);
		?>
		<table class="kt-members-table">
			<thead><tr>
				<th>Nome</th>
				<th>E-mail</th>
				<th>Função</th>
				<th>Unidade</th>
			</tr></thead>
			<tbody>
			<?php foreach ($kt_all_users as $u):
				$u_roles   = $u->roles;
				$u_role    = $u_roles[0] ?? '';
				$u_loc_id  = (int)get_user_meta($u->ID,'kt_location_id',true);
				$u_loc     = $u_loc_id ? KT_Location::get($u_loc_id) : null;
			?>
			<tr>
				<td><?php echo esc_html($u->display_name); ?></td>
				<td style="color:#64748b;font-size:.9em"><?php echo esc_html($u->user_email); ?></td>
				<td><span style="font-size:.82em;background:#f1f5f9;padding:2px 8px;border-radius:12px"><?php echo esc_html(KT_Roles::role_label($u_role)); ?></span></td>
				<td style="color:#64748b;font-size:.9em"><?php echo $u_loc ? esc_html($u_loc->name) : '—'; ?></td>
			</tr>
			<?php endforeach; ?>
			<?php if (!$kt_all_users): ?>
			<tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:20px">Nenhum usuário encontrado.</td></tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div><!-- /tab usuarios -->

	<!-- ══════════════════════════════════════════════════════
	     TAB: FUNÇÕES
	══════════════════════════════════════════════════════ -->
	<?php elseif ( $active_tab === 'funcoes' ): ?>
	<div class="kt-admin-tab-content">
		<h3 style="margin-bottom:16px">Funções cadastradas</h3>
		<table class="kt-members-table" id="kt-positions-table">
			<thead><tr>
				<th>Nome</th>
				<th>Colaboradores</th>
				<th style="width:100px"></th>
			</tr></thead>
			<tbody>
			<?php foreach ($positions as $pos): ?>
			<tr id="kt-pos-row-<?php echo absint($pos->id); ?>">
				<td><?php echo esc_html($pos->name); ?></td>
				<td><?php echo (int)$pos->member_count; ?></td>
				<td>
					<?php if ( (int)$pos->member_count === 0 ): ?>
					<button type="button" class="kt-btn kt-btn-sm kt-pos-delete-btn"
					        style="color:#b91c1c;border-color:#fca5a5"
					        data-id="<?php echo absint($pos->id); ?>"
					        data-name="<?php echo esc_attr($pos->name); ?>">Remover</button>
					<?php else: ?>
					<span style="font-size:.8em;color:#94a3b8">Em uso</span>
					<?php endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
			<?php if (!$positions): ?>
			<tr><td colspan="3" style="text-align:center;color:#94a3b8;padding:20px">Nenhuma função cadastrada.</td></tr>
			<?php endif; ?>
			</tbody>
		</table>

		<!-- Adicionar função -->
		<div class="kt-manager-enroll-box" style="margin-top:32px">
			<h3>+ Adicionar Função</h3>
			<div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
				<div style="flex:1;min-width:200px">
					<label style="display:block;margin-bottom:4px;font-weight:600;font-size:.9em">Nome da função</label>
					<input type="text" id="kt-new-pos-name" placeholder="Ex: Vendas" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:7px;box-sizing:border-box">
				</div>
				<button type="button" id="kt-add-pos-btn" class="kt-btn kt-btn-primary">Adicionar</button>
			</div>
			<p id="kt-pos-msg" style="margin:10px 0 0;font-size:.88em;min-height:1.2em"></p>
		</div>
	</div><!-- /tab funcoes -->
	<?php endif; ?>

</div><!-- /kt-admin-portal -->

<!-- ── Modal de edição de unidade ───────────────────────── -->
<div id="kt-loc-modal-overlay" class="kt-modal-overlay" style="display:none" aria-modal="true" role="dialog">
	<div class="kt-modal">
		<div class="kt-modal-header">
			<h3>Editar Unidade</h3>
			<button type="button" class="kt-modal-close" id="kt-loc-modal-close" aria-label="Fechar">×</button>
		</div>
		<div class="kt-modal-body">
			<input type="hidden" id="kt-edit-loc-id">
			<div style="margin-bottom:14px">
				<label style="display:block;margin-bottom:4px;font-weight:600;font-size:.9em">Nome</label>
				<input type="text" id="kt-edit-loc-name" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:7px;box-sizing:border-box">
			</div>
			<div style="margin-bottom:20px">
				<label style="display:block;margin-bottom:4px;font-weight:600;font-size:.9em">Gerente</label>
				<select id="kt-edit-loc-manager" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:7px">
					<option value="">— Sem gerente —</option>
					<?php foreach ($kt_users as $u): ?>
					<option value="<?php echo absint($u->ID); ?>"><?php echo esc_html($u->display_name); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div style="display:flex;gap:10px">
				<button type="button" id="kt-save-loc-btn" class="kt-btn kt-btn-primary">Salvar</button>
				<button type="button" id="kt-loc-modal-close2" class="kt-btn kt-btn-outline">Cancelar</button>
			</div>
			<p id="kt-loc-modal-msg" style="margin:10px 0 0;font-size:.88em;min-height:1.2em"></p>
		</div>
	</div>
</div>

<!-- ── Modal de edição de matrícula (reutilizado do gerente) ─ -->
<div id="kt-member-modal-overlay" class="kt-modal-overlay" style="display:none" aria-modal="true" role="dialog">
	<div class="kt-modal">
		<div class="kt-modal-header">
			<h3 id="kt-modal-title">Editar colaborador</h3>
			<button type="button" class="kt-modal-close" id="kt-modal-close" aria-label="Fechar">×</button>
		</div>
		<div class="kt-modal-body" id="kt-modal-body"></div>
	</div>
</div>

<style>
.kt-admin-tabs{display:flex;gap:4px;margin-bottom:24px;border-bottom:2px solid #e2e8f0;flex-wrap:wrap}
.kt-admin-tab{padding:10px 18px;text-decoration:none;color:#64748b;border-radius:8px 8px 0 0;font-size:.92em;font-weight:500;transition:background .15s,color .15s;border:2px solid transparent;border-bottom:none;margin-bottom:-2px}
.kt-admin-tab:hover{color:#1e293b;background:#f8fafc}
.kt-admin-tab.active{color:#1e293b;background:#fff;border-color:#e2e8f0;border-bottom-color:#fff;font-weight:700}
.kt-admin-tab-content{padding:4px 0 0}
</style>

<script>
(function($){

/* ── Helpers ── */
function msg(sel, text, ok){ $(sel).text(text).css('color', ok?'#15803d':'#b91c1c'); }

/* ══════════════ TAB: PAINEL — Enrollment logic ══════════════ */

function showMsg(text, ok){ msg('#kt-enroll-msg', text, ok); }

function getCheckedIds(){
	return $('.kt-member-cb:checked').map(function(){ return parseInt($(this).val()); }).get();
}

function updateCounter(){
	var n=getCheckedIds().length, visible=$('.kt-member-check-item:not(.kt-item-hidden)').length;
	$('#kt-selected-count').text(n);
	$('#kt-assign-btn').prop('disabled', n===0);
	var $all=$('#kt-select-all'), vc=$('.kt-member-check-item:not(.kt-item-hidden) .kt-member-cb:checked').length;
	$all.prop('indeterminate', vc>0&&vc<visible).prop('checked', vc===visible&&visible>0);
	var total=$('.kt-member-check-item').length;
	$('#kt-select-all-label').text(visible===total?'Selecionar todos ('+total+')':'Selecionar visíveis ('+visible+')');
}

$(document).on('click','.kt-chips-summary',function(){
	var $s=$(this), id=$s.data('target'), $d=$('#'+id), open=$s.hasClass('open');
	$s.toggleClass('open',!open);
	open ? $d.slideUp(160) : $d.slideDown(160);
});
$(document).on('click','.kt-edit-link',function(e){ e.preventDefault(); });

$('#kt-assign-course').on('change',function(){
	$(this).val() ? $('#kt-member-picker').slideDown(180) : $('#kt-member-picker').slideUp(180);
	$('.kt-member-cb').prop('checked',false); updateCounter();
});

$('#kt-member-search').on('input',function(){
	var q=$(this).val().toLowerCase().trim(), hasV=false;
	$('.kt-member-check-item').each(function(){
		var m=!q||($(this).data('name')||'').indexOf(q)!==-1;
		$(this).toggleClass('kt-item-hidden',!m);
		if(m) hasV=true;
	});
	$('#kt-no-results').toggle(!hasV); updateCounter();
});

$('#kt-select-all').on('change',function(){
	$('.kt-member-check-item:not(.kt-item-hidden) .kt-member-cb').prop('checked',$(this).prop('checked'));
	updateCounter();
});
$(document).on('change','.kt-member-cb',updateCounter);

$('#kt-assign-btn').on('click',function(){
	var courseId=$('#kt-assign-course').val(), ids=getCheckedIds(), due=$('#kt-assign-due').val();
	if(!courseId){showMsg('Selecione um curso.',false);return;}
	if(!ids.length){showMsg('Selecione ao menos um colaborador.',false);return;}
	var cname=$('#kt-assign-course option:selected').text();
	if(!confirm(ids.length===1?'Atribuir "'+cname+'" para 1 colaborador?':'Atribuir "'+cname+'" para '+ids.length+' colaboradores?')) return;
	showMsg('Salvando…',true); $(this).prop('disabled',true).text('Salvando…');
	var d={action:'kt_enroll_member',nonce:ktFrontend.nonce,course_id:courseId,due_date:due};
	$.each(ids,function(i,id){d['member_ids['+i+']']=id;});
	$.post(ktFrontend.ajaxUrl,d).done(function(r){
		showMsg(r.success?r.data.message:(r.data&&r.data.message?r.data.message:'Erro.'),r.success);
		if(r.success) setTimeout(function(){location.reload();},700);
		else $('#kt-assign-btn').prop('disabled',false).text('Atribuir →');
	}).fail(function(){ showMsg('Erro de conexão.',false); $('#kt-assign-btn').prop('disabled',false).text('Atribuir →'); });
});

/* ══════════════ TAB: UNIDADES ══════════════ */

/* Abrir modal de edição */
$(document).on('click','.kt-loc-edit-btn',function(){
	var $b=$(this);
	$('#kt-edit-loc-id').val($b.data('id'));
	$('#kt-edit-loc-name').val($b.data('name'));
	$('#kt-edit-loc-manager').val($b.data('manager'));
	$('#kt-loc-modal-msg').text('');
	$('#kt-loc-modal-overlay').fadeIn(180);
	$('body').addClass('kt-modal-open');
});

function closeLocModal(){ $('#kt-loc-modal-overlay').fadeOut(150); $('body').removeClass('kt-modal-open'); }
$('#kt-loc-modal-close, #kt-loc-modal-close2').on('click', closeLocModal);
$('#kt-loc-modal-overlay').on('click',function(e){ if($(e.target).is('#kt-loc-modal-overlay')) closeLocModal(); });

/* Salvar edição de unidade */
$('#kt-save-loc-btn').on('click',function(){
	var $b=$(this);
	var id=$('#kt-edit-loc-id').val(), name=$('#kt-edit-loc-name').val().trim(), mgr=$('#kt-edit-loc-manager').val();
	if(!name){msg('#kt-loc-modal-msg','Nome é obrigatório.',false);return;}
	$b.prop('disabled',true).text('Salvando…');
	$.post(ktFrontend.ajaxUrl,{action:'kt_admin_update_location',nonce:ktFrontend.nonce,location_id:id,name:name,manager_id:mgr})
	.done(function(r){
		msg('#kt-loc-modal-msg',r.success?r.data.message:(r.data&&r.data.message?r.data.message:'Erro.'),r.success);
		if(r.success){
			var $row=$('#kt-loc-row-'+id);
			$row.find('.kt-loc-name-cell').text(name);
			$row.find('.kt-loc-mgr-cell').text(r.data.manager_name||'—');
			$row.find('.kt-loc-edit-btn').data('name',name).data('manager',parseInt(mgr)||0);
			setTimeout(closeLocModal, 800);
		}
		$b.prop('disabled',false).text('Salvar');
	}).fail(function(){ msg('#kt-loc-modal-msg','Erro de conexão.',false); $b.prop('disabled',false).text('Salvar'); });
});

/* Adicionar nova unidade */
$('#kt-add-loc-btn').on('click',function(){
	var $b=$(this), name=$('#kt-new-loc-name').val().trim(), mgr=$('#kt-new-loc-manager').val();
	if(!name){msg('#kt-loc-msg','Nome é obrigatório.',false);return;}
	$b.prop('disabled',true).text('Adicionando…');
	$.post(ktFrontend.ajaxUrl,{action:'kt_admin_add_location',nonce:ktFrontend.nonce,name:name,manager_id:mgr})
	.done(function(r){
		msg('#kt-loc-msg',r.success?r.data.message:(r.data&&r.data.message?r.data.message:'Erro.'),r.success);
		if(r.success){
			var d=r.data;
			$('#kt-locations-table tbody tr:last').after(
				'<tr id="kt-loc-row-'+d.id+'">' +
				'<td class="kt-loc-name-cell">'+$('<div>').text(d.name).html()+'</td>' +
				'<td class="kt-loc-mgr-cell">'+$('<div>').text(d.manager_name||'—').html()+'</td>' +
				'<td>0</td>' +
				'<td><button type="button" class="kt-btn kt-btn-sm kt-loc-edit-btn" data-id="'+d.id+'" data-name="'+$('<div>').text(d.name).html()+'" data-manager="0">Editar</button></td>' +
				'</tr>'
			);
			$('#kt-new-loc-name').val('');
			$('#kt-new-loc-manager').val('');
		}
		$b.prop('disabled',false).text('Adicionar');
	}).fail(function(){ msg('#kt-loc-msg','Erro de conexão.',false); $b.prop('disabled',false).text('Adicionar'); });
});

/* ══════════════ TAB: USUÁRIOS ══════════════ */

$('#kt-u-role').on('change',function(){
	var r=$(this).val();
	var show=r==='kt_location_manager'||r==='kt_staff';
	$('#kt-u-location-wrap').toggle(show);
	$('#kt-u-location-label').text(r==='kt_location_manager'?'Unidade (será atribuído como gerente)':'Unidade do colaborador');
});

$('#kt-create-user-btn').on('click',function(){
	var $b=$(this);
	var first=$('#kt-u-first').val().trim(), last=$('#kt-u-last').val().trim();
	var email=$('#kt-u-email').val().trim(), role=$('#kt-u-role').val();
	var loc=$('#kt-u-location').val(), sendEmail=$('#kt-u-send-email').prop('checked')?1:0;

	if(!first||!email||!role){msg('#kt-user-msg','Preencha nome, e-mail e função.',false);return;}

	$b.prop('disabled',true).text('Criando…');
	$.post(ktFrontend.ajaxUrl,{action:'kt_admin_create_user',nonce:ktFrontend.nonce,first_name:first,last_name:last,email:email,role:role,location_id:loc,send_email:sendEmail})
	.done(function(r){
		msg('#kt-user-msg',r.success?'✓ Usuário "'+r.data.name+'" criado! Login: '+r.data.username:(r.data&&r.data.message?r.data.message:'Erro.'),r.success);
		if(r.success){
			$('#kt-u-first,#kt-u-last,#kt-u-email').val('');
			$('#kt-u-role').val('').trigger('change');
			setTimeout(function(){location.reload();},1500);
		}
		$b.prop('disabled',false).text('Criar usuário');
	}).fail(function(){ msg('#kt-user-msg','Erro de conexão.',false); $b.prop('disabled',false).text('Criar usuário'); });
});

/* ══════════════ TAB: FUNÇÕES ══════════════ */

$('#kt-add-pos-btn').on('click',function(){
	var $b=$(this), name=$('#kt-new-pos-name').val().trim();
	if(!name){msg('#kt-pos-msg','Nome é obrigatório.',false);return;}
	$b.prop('disabled',true).text('Adicionando…');
	$.post(ktFrontend.ajaxUrl,{action:'kt_admin_add_position',nonce:ktFrontend.nonce,name:name})
	.done(function(r){
		msg('#kt-pos-msg',r.success?r.data.message:(r.data&&r.data.message?r.data.message:'Erro.'),r.success);
		if(r.success){
			var d=r.data;
			$('#kt-positions-table tbody').append(
				'<tr id="kt-pos-row-'+d.id+'">' +
				'<td>'+$('<div>').text(d.name).html()+'</td><td>0</td>' +
				'<td><button type="button" class="kt-btn kt-btn-sm kt-pos-delete-btn" style="color:#b91c1c;border-color:#fca5a5" data-id="'+d.id+'" data-name="'+$('<div>').text(d.name).html()+'">Remover</button></td>' +
				'</tr>'
			);
			$('#kt-new-pos-name').val('');
		}
		$b.prop('disabled',false).text('Adicionar');
	}).fail(function(){ msg('#kt-pos-msg','Erro de conexão.',false); $b.prop('disabled',false).text('Adicionar'); });
});

$(document).on('click','.kt-pos-delete-btn',function(){
	var $b=$(this), id=$b.data('id'), name=$b.data('name');
	if(!confirm('Remover a função "'+name+'"? Colaboradores vinculados ficarão sem função.')) return;
	$b.prop('disabled',true);
	$.post(ktFrontend.ajaxUrl,{action:'kt_admin_delete_position',nonce:ktFrontend.nonce,position_id:id})
	.done(function(r){
		if(r.success) $('#kt-pos-row-'+id).fadeOut(200,function(){$(this).remove();});
		else{ msg('#kt-pos-msg',r.data&&r.data.message?r.data.message:'Erro.',false); $b.prop('disabled',false); }
	}).fail(function(){ msg('#kt-pos-msg','Erro de conexão.',false); $b.prop('disabled',false); });
});

/* ══════════════ Modal de matrícula (igual ao gerente) ══════════════ */

var STATUS_LABEL={nao_iniciado:'Não iniciado',em_andamento:'Em andamento',concluido:'Concluído'};
function statusChip(s,ov){ var cls=ov?'overdue':s,lbl=ov?'Atrasado':(STATUS_LABEL[s]||s); return '<span class="kt-enroll-chip kt-enroll-chip-'+cls+'" style="font-size:.78em">'+lbl+'</span>'; }

function openModal(memberId,memberName,enrollments){
	$('#kt-modal-title').text(memberName);
	var $opts=$('#kt-assign-course option').clone();
	var html='';
	if(enrollments.length){
		html+='<div class="kt-modal-section"><p class="kt-modal-section-title">Treinamentos atribuídos</p><table class="kt-modal-enroll-table"><thead><tr><th>Curso</th><th>Status</th><th>Prazo</th><th></th></tr></thead><tbody>';
		$.each(enrollments,function(i,e){
			var rowId='kt-modal-row-'+memberId+'-'+e.course_id;
			html+='<tr id="'+rowId+'"><td class="kt-modal-course-title">'+$('<div>').text(e.course_title).html()+'</td>';
			html+='<td>'+statusChip(e.status,e.overdue)+' <small style="color:#94a3b8">'+e.pct+'%</small></td>';
			html+='<td><input type="date" class="kt-modal-due-input" data-member-id="'+memberId+'" data-course-id="'+e.course_id+'" value="'+e.due_date+'" style="border:1px solid #e2e8f0;border-radius:6px;padding:4px 7px;font-size:.82em"></td>';
			html+='<td class="kt-modal-actions"><button type="button" class="kt-btn kt-btn-sm kt-modal-save-due" data-member-id="'+memberId+'" data-course-id="'+e.course_id+'">Salvar prazo</button> <button type="button" class="kt-btn kt-btn-sm kt-modal-unenroll" data-member-id="'+memberId+'" data-course-id="'+e.course_id+'" data-course-name="'+$('<div>').text(e.course_title).html()+'" data-row="'+rowId+'" style="color:#b91c1c;border-color:#fca5a5">Remover</button></td></tr>';
		});
		html+='</tbody></table><p class="kt-modal-row-msg" id="kt-modal-enroll-msg"></p></div>';
	} else { html+='<p style="color:#94a3b8;margin-bottom:20px;font-style:italic">Sem treinamentos atribuídos.</p>'; }
	html+='<div class="kt-modal-section kt-modal-assign-section"><p class="kt-modal-section-title">Atribuir novo treinamento</p><div class="kt-modal-assign-row"><select id="kt-modal-course-select" class="kt-modal-course-select"><option value="">— Selecione o curso —</option></select><input type="date" id="kt-modal-assign-due" style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:7px;font-size:.88em"><button type="button" id="kt-modal-assign-btn" class="kt-btn kt-btn-primary" data-member-id="'+memberId+'" disabled>Atribuir</button></div><p class="kt-modal-row-msg" id="kt-modal-assign-msg"></p></div>';
	$('#kt-modal-body').html(html);
	$opts.each(function(){ $('#kt-modal-course-select').append($(this).clone()); });
	$('#kt-modal-course-select').on('change',function(){ $('#kt-modal-assign-btn').prop('disabled',!$(this).val()); });
	$('#kt-member-modal-overlay').fadeIn(180); $('body').addClass('kt-modal-open');
}

function closeModal(){ $('#kt-member-modal-overlay').fadeOut(150); $('body').removeClass('kt-modal-open'); }
$('#kt-modal-close').on('click',closeModal);
$('#kt-member-modal-overlay').on('click',function(e){ if($(e.target).is('#kt-member-modal-overlay')) closeModal(); });
$(document).on('keydown',function(e){ if(e.key==='Escape'){ closeModal(); closeLocModal(); } });

$(document).on('click','.kt-edit-member-btn',function(){
	var $b=$(this); var enrollments;
	try{ enrollments=JSON.parse($b.attr('data-enrollments')||'[]'); }catch(e){ enrollments=[]; }
	openModal($b.data('member-id'),$b.data('member-name'),enrollments);
});

$(document).on('click','.kt-modal-save-due',function(){
	var $b=$(this), $r=$b.closest('tr');
	$b.prop('disabled',true).text('Salvando…');
	$.post(ktFrontend.ajaxUrl,{action:'kt_update_due_date',nonce:ktFrontend.nonce,member_id:$b.data('member-id'),course_id:$b.data('course-id'),due_date:$r.find('.kt-modal-due-input').val()})
	.done(function(r){
		$('#kt-modal-enroll-msg').text(r.success?'Prazo salvo!':(r.data&&r.data.message?r.data.message:'Erro.')).css('color',r.success?'#15803d':'#b91c1c');
		$b.prop('disabled',false).text('Salvar prazo');
		if(r.success) setTimeout(function(){ $('#kt-modal-enroll-msg').text(''); },2000);
	}).fail(function(){ $('#kt-modal-enroll-msg').text('Erro de conexão.').css('color','#b91c1c'); $b.prop('disabled',false).text('Salvar prazo'); });
});

$(document).on('click','.kt-modal-unenroll',function(){
	var $b=$(this);
	if(!confirm('Remover matrícula em "'+($b.data('course-name')||'este curso')+'" e zerar progresso?')) return;
	$b.prop('disabled',true);
	$.post(ktFrontend.ajaxUrl,{action:'kt_unenroll_member',nonce:ktFrontend.nonce,member_id:$b.data('member-id'),course_id:$b.data('course-id')})
	.done(function(r){
		if(r.success){ $('#'+$b.data('row')).fadeOut(200,function(){$(this).remove();}); $('#kt-modal-enroll-msg').text('Matrícula removida.').css('color','#15803d'); $('#kt-member-modal-overlay').data('needsReload',true); }
	}).fail(function(){ $b.prop('disabled',false); });
});

$(document).on('click','#kt-modal-assign-btn',function(){
	var $b=$(this), courseId=$('#kt-modal-course-select').val(), due=$('#kt-modal-assign-due').val();
	var cname=$('#kt-modal-course-select option:selected').text();
	if(!courseId) return;
	if(!confirm('Atribuir "'+cname+'"?')) return;
	$b.prop('disabled',true).text('Salvando…');
	$.post(ktFrontend.ajaxUrl,{action:'kt_enroll_member',nonce:ktFrontend.nonce,course_id:courseId,due_date:due,'member_ids[0]':$b.data('member-id')})
	.done(function(r){
		$('#kt-modal-assign-msg').text(r.success?r.data.message:(r.data&&r.data.message?r.data.message:'Erro.')).css('color',r.success?'#15803d':'#b91c1c');
		if(r.success){ $b.text('Atribuído ✓'); $('#kt-member-modal-overlay').data('needsReload',true); }
		else $b.prop('disabled',false).text('Atribuir');
	}).fail(function(){ $('#kt-modal-assign-msg').text('Erro de conexão.').css('color','#b91c1c'); $b.prop('disabled',false).text('Atribuir'); });
});

$('#kt-modal-close, #kt-member-modal-overlay').on('click',function(e){
	if(e.target.id!=='kt-member-modal-overlay'&&e.target.id!=='kt-modal-close') return;
	if($('#kt-member-modal-overlay').data('needsReload')) location.reload();
});

})(jQuery);
</script>
