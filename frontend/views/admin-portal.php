<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
// Mapeia gerente de cada unidade a partir do manager_id na tabela kt_locations
// (fonte da verdade — evita dessincronismo com user_meta)
$manager_map = [];
foreach ( $locations as $loc ) {
	if ( $loc->manager_id ) {
		$_mgr = get_user_by( 'ID', $loc->manager_id );
		if ( $_mgr ) $manager_map[ (int) $loc->id ] = $_mgr;
	}
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
		<a href="<?php echo esc_url( add_query_arg( 'kt_tab', 'cargos', $current_url ) ); ?>"
		   class="kt-admin-tab <?php echo $active_tab === 'cargos'  ? 'active' : ''; ?>">🏷 Cargos</a>
	</div>

	<!-- ══════════════════════════════════════════════════════
	     TAB: PAINEL
	══════════════════════════════════════════════════════ -->
	<?php if ( $active_tab === 'painel' ): ?>
	<div class="kt-admin-tab-content">

		<!-- Abas de unidade -->
		<div class="kt-unit-tabs">
			<?php foreach ( $locations as $loc ): ?>
			<a href="<?php echo esc_url( add_query_arg( ['kt_tab'=>'painel','kt_location'=>$loc->id], $current_url ) ); ?>"
			   class="kt-unit-tab <?php echo (int)$location_id === (int)$loc->id ? 'active' : ''; ?>">
				<?php echo esc_html($loc->name); ?>
			</a>
			<?php endforeach; ?>
		</div>

		<?php if ( ! $location_id ): ?>
		<div class="kt-empty-state"><p>Selecione uma unidade acima para ver colaboradores e matrículas.</p></div>

		<?php else:
		$unit_mgr = $location->manager_id ? get_user_by( 'ID', $location->manager_id ) : null;
		$u_enr = 0; $u_done = 0;
		foreach ( $member_progress as $enrs ) {
			foreach ( $enrs as $e ) {
				$u_enr++;
				if ( $e->status === 'concluido' ) $u_done++;
			}
		}
		$u_rate = $u_enr > 0 ? round( $u_done / $u_enr * 100 ) : 0;
		?>

		<!-- Cabeçalho da unidade: gerente + stats -->
		<div class="kt-unit-header">
			<div class="kt-unit-header-mgr">
				<span class="kt-unit-header-label">Gerente</span>
				<span class="kt-unit-header-value"><?php echo $unit_mgr ? esc_html( $unit_mgr->user_login ) : '—'; ?></span>
			</div>
			<div class="kt-unit-header-stats">
				<div class="kt-unit-stat"><strong><?php echo count($tab_members); ?></strong><span>colaboradores</span></div>
				<div class="kt-unit-stat"><strong><?php echo $u_enr; ?></strong><span>matrículas</span></div>
				<div class="kt-unit-stat"><strong><?php echo $u_rate; ?>%</strong><span>conclusão</span></div>
			</div>
		</div>

		<!-- Atribuir treinamento -->
		<?php if ( $courses && $tab_members ): ?>
		<div class="kt-manager-enroll-box" style="margin-bottom:24px">
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
		<table class="kt-members-table">
			<thead><tr>
				<th>Colaborador</th>
				<th>Cargo</th>
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
							$pct = KT_Progress::course_progress_pct($m->id,$e->course_id);
							$overdue = $e->due_date && strtotime($e->due_date)<time() && $e->status!=='concluido';
						?>
						<div class="kt-drawer-row">
							<span class="kt-enroll-chip kt-enroll-chip-<?php echo $overdue?'overdue':esc_attr($e->status); ?>" style="flex-shrink:0"><?php echo $pct; ?>%</span>
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
				$mgr_name  = $mgr_user ? $mgr_user->user_login : '—';
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

		<div style="margin-bottom:24px">
			<button type="button" id="kt-open-create-user-btn" class="kt-btn kt-btn-primary">+ Adicionar novo colaborador</button>
		</div>

		<!-- Lista de usuários KT -->
		<h3 style="margin:32px 0 12px">Usuários do sistema</h3>
		<?php
		$kt_all_users = get_users(['role__in'=>['kt_admin','kt_super_admin','kt_location_manager','kt_staff','administrator'],'number'=>200,'orderby'=>'display_name','order'=>'ASC']);
		// Carrega datas de colaboradores para exibir no modal de edição
		global $wpdb;
		$_mem_rows = $wpdb->get_results("SELECT user_id, hire_date, birth_date, position_id FROM {$wpdb->prefix}kt_members");
		$_mem_map  = [];
		foreach ( $_mem_rows as $_mr ) $_mem_map[ (int)$_mr->user_id ] = $_mr;
		?>
		<table class="kt-members-table">
			<thead><tr>
				<th>Nome</th>
				<th>E-mail</th>
				<th>Nível de Acesso</th>
				<th>Unidade</th>
				<th style="width:80px"></th>
			</tr></thead>
			<tbody>
			<?php foreach ($kt_all_users as $u):
				$u_roles   = $u->roles;
				$u_role    = $u_roles[0] ?? '';
				$u_loc_id  = (int)get_user_meta($u->ID,'kt_location_id',true);
				$u_loc     = $u_loc_id ? KT_Location::get($u_loc_id) : null;
				$_md       = $_mem_map[$u->ID] ?? null;
				$u_hire    = $_md ? ($_md->hire_date  ?: '') : '';
				$u_birth   = $_md ? ($_md->birth_date ?: '') : '';
				$u_pos_id  = $_md ? (int)($_md->position_id ?? 0) : 0;
			?>
			<tr id="kt-user-row-<?php echo absint($u->ID); ?>">
				<td class="kt-u-name"><?php echo esc_html($u->user_login); ?></td>
				<td class="kt-u-email" style="color:#64748b;font-size:.9em"><?php echo esc_html($u->user_email); ?></td>
				<td class="kt-u-role"><span style="font-size:.82em;background:#f1f5f9;padding:2px 8px;border-radius:12px"><?php echo esc_html(KT_Roles::role_label($u_role)); ?></span></td>
				<td class="kt-u-loc" style="color:#64748b;font-size:.9em"><?php echo $u_loc ? esc_html($u_loc->name) : '—'; ?></td>
				<td style="white-space:nowrap">
					<button type="button" class="kt-btn kt-btn-sm kt-edit-user-btn"
					        data-id="<?php echo absint($u->ID); ?>"
					        data-login="<?php echo esc_attr($u->user_login); ?>"
					        data-first="<?php echo esc_attr($u->first_name); ?>"
					        data-last="<?php echo esc_attr($u->last_name); ?>"
					        data-email="<?php echo esc_attr($u->user_email); ?>"
					        data-role="<?php echo esc_attr($u_role); ?>"
					        data-location="<?php echo absint($u_loc_id); ?>"
					        data-hire="<?php echo esc_attr($u_hire); ?>"
					        data-birth="<?php echo esc_attr($u_birth); ?>"
					        data-position="<?php echo absint($u_pos_id); ?>">Editar</button>
					<?php if ( $u->ID !== get_current_user_id() && ! in_array('administrator', $u->roles, true) ): ?>
					<button type="button" class="kt-btn kt-btn-sm kt-delete-user-btn"
					        style="color:#b91c1c;border-color:#fca5a5;margin-left:4px"
					        data-id="<?php echo absint($u->ID); ?>"
					        data-login="<?php echo esc_attr($u->user_login); ?>">Excluir</button>
					<?php endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
			<?php if (!$kt_all_users): ?>
			<tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:20px">Nenhum usuário encontrado.</td></tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div><!-- /tab usuarios -->

	<!-- ══════════════════════════════════════════════════════
	     TAB: FUNÇÕES
	══════════════════════════════════════════════════════ -->
	<?php elseif ( $active_tab === 'cargos' ): ?>
	<div class="kt-admin-tab-content">
		<h3 style="margin-bottom:16px">Cargos cadastrados</h3>
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
			<tr><td colspan="3" style="text-align:center;color:#94a3b8;padding:20px">Nenhum cargo cadastrado.</td></tr>
			<?php endif; ?>
			</tbody>
		</table>

		<!-- Adicionar cargo -->
		<div class="kt-manager-enroll-box" style="margin-top:32px">
			<h3>+ Adicionar Cargo</h3>
			<div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
				<div style="flex:1;min-width:200px">
					<label style="display:block;margin-bottom:4px;font-weight:600;font-size:.9em">Nome do cargo</label>
					<input type="text" id="kt-new-pos-name" placeholder="Ex: Vendas" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:7px;box-sizing:border-box">
				</div>
				<button type="button" id="kt-add-pos-btn" class="kt-btn kt-btn-primary">Adicionar</button>
			</div>
			<p id="kt-pos-msg" style="margin:10px 0 0;font-size:.88em;min-height:1.2em"></p>
		</div>
	</div><!-- /tab cargos -->
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

<!-- ── Modal de criação de usuário ──────────────────────── -->
<div id="kt-create-user-modal-overlay" class="kt-modal-overlay" style="display:none" aria-modal="true" role="dialog">
	<div class="kt-modal" style="max-width:600px">
		<div class="kt-modal-header">
			<h3>Adicionar Novo Colaborador</h3>
			<button type="button" class="kt-modal-close" id="kt-create-user-modal-close" aria-label="Fechar">×</button>
		</div>
		<div class="kt-modal-body">

			<!-- Nome + Sobrenome -->
			<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
				<div>
					<label style="display:block;margin-bottom:4px;font-weight:600;font-size:.9em">Nome <span style="color:#ef4444">*</span></label>
					<input type="text" id="kt-u-first" placeholder="Ex: Pedro"
					       style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:7px;box-sizing:border-box;font-size:1em">
				</div>
				<div>
					<label style="display:block;margin-bottom:4px;font-weight:600;font-size:.9em">Sobrenome</label>
					<input type="text" id="kt-u-last" placeholder="Ex: Santos da Silva"
					       style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:7px;box-sizing:border-box;font-size:1em">
				</div>
			</div>
			<p style="margin:-8px 0 14px;font-size:.8em;color:#94a3b8">O login será gerado automaticamente (ex: pedro.silva)</p>

			<!-- E-mail -->
			<div style="margin:14px 0">
				<label style="display:block;margin-bottom:4px;font-weight:600;font-size:.9em">E-mail <span style="color:#ef4444">*</span></label>
				<input type="email" id="kt-u-email" placeholder="email@exemplo.com"
				       style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:7px;box-sizing:border-box;font-size:1em">
			</div>

			<!-- Nível de Acesso + Cargo -->
			<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
				<div>
					<label style="display:block;margin-bottom:4px;font-weight:600;font-size:.9em">Nível de Acesso <span style="color:#ef4444">*</span></label>
					<select id="kt-u-role" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:7px;box-sizing:border-box;font-size:1em">
						<option value="">— Selecione —</option>
						<option value="kt_admin">Administrador</option>
						<option value="kt_location_manager">Gerente de Unidade</option>
						<option value="kt_staff">Colaborador</option>
					</select>
				</div>
				<div>
					<label style="display:block;margin-bottom:4px;font-weight:600;font-size:.9em">Cargo</label>
					<select id="kt-u-position" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:7px;box-sizing:border-box;font-size:1em">
						<option value="">— Sem cargo —</option>
						<?php foreach ( $positions as $pos ): ?>
						<option value="<?php echo absint($pos->id); ?>"><?php echo esc_html($pos->name); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

			<!-- Unidade (condicional) -->
			<div id="kt-u-location-wrap" style="display:none;margin-bottom:14px">
				<label style="display:block;margin-bottom:4px;font-weight:600;font-size:.9em" id="kt-u-location-label">Unidade</label>
				<select id="kt-u-location" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:7px;box-sizing:border-box;font-size:1em">
					<option value="">— Selecione a unidade —</option>
					<?php foreach ( $locations as $loc ): ?>
					<option value="<?php echo absint($loc->id); ?>"><?php echo esc_html($loc->name); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<!-- Datas (secundárias) -->
			<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px;padding-top:4px;border-top:1px solid #f1f5f9">
				<div style="padding-top:12px">
					<label style="display:block;margin-bottom:4px;font-weight:500;font-size:.82em;color:#64748b;text-transform:uppercase;letter-spacing:.04em">Data de Admissão</label>
					<input type="date" id="kt-u-hire-date" style="width:100%;padding:7px 10px;border:1px solid #e2e8f0;border-radius:7px;box-sizing:border-box;font-size:.9em;color:#475569">
				</div>
				<div style="padding-top:12px">
					<label style="display:block;margin-bottom:4px;font-weight:500;font-size:.82em;color:#64748b;text-transform:uppercase;letter-spacing:.04em">Data de Aniversário</label>
					<input type="date" id="kt-u-birth-date" style="width:100%;padding:7px 10px;border:1px solid #e2e8f0;border-radius:7px;box-sizing:border-box;font-size:.9em;color:#475569">
				</div>
			</div>

			<!-- Rodapé: checkbox + botão -->
			<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
				<label style="display:flex;align-items:center;gap:8px;font-size:.88em;color:#64748b;cursor:pointer">
					<input type="checkbox" id="kt-u-send-email" checked>
					Enviar e-mail com as credenciais de acesso
				</label>
				<button type="button" id="kt-create-user-btn" class="kt-btn kt-btn-primary">Criar usuário →</button>
			</div>
			<p id="kt-user-msg" style="margin:8px 0 0;font-size:.88em;min-height:1.2em"></p>

			<!-- Mensagem de acesso copiável -->
			<div id="kt-access-msg-wrap" style="display:none;margin-top:16px;padding-top:16px;border-top:1px solid #e2e8f0">
				<label style="display:block;margin-bottom:6px;font-weight:600;font-size:.9em">
					📋 Mensagem de acesso — copie e envie ao colaborador:
				</label>
				<textarea id="kt-access-msg-text" readonly style="width:100%;height:160px;padding:10px 12px;border:1px solid #e2e8f0;border-radius:7px;font-size:.84em;line-height:1.55;resize:vertical;box-sizing:border-box;color:#1e293b;background:#f8fafc;font-family:inherit"></textarea>
				<div style="display:flex;align-items:center;gap:10px;margin-top:8px">
					<button type="button" id="kt-copy-access-msg" class="kt-btn kt-btn-outline">📋 Copiar mensagem</button>
					<span id="kt-copy-confirm" style="display:none;color:#15803d;font-size:.88em;font-weight:600">✓ Copiado!</span>
				</div>
			</div>

		</div>
	</div>
</div>

<!-- ── Modal de edição de usuário ───────────────────────── -->
<div id="kt-user-modal-overlay" class="kt-modal-overlay" style="display:none" aria-modal="true" role="dialog">
	<div class="kt-modal">
		<div class="kt-modal-header">
			<h3>Editar Usuário</h3>
			<button type="button" class="kt-modal-close" id="kt-user-modal-close" aria-label="Fechar">×</button>
		</div>
		<div class="kt-modal-body">
			<input type="hidden" id="kt-edit-user-id">
			<!-- Usuário (login) — somente leitura -->
			<div style="margin-bottom:14px">
				<label style="display:block;margin-bottom:4px;font-weight:600;font-size:.9em">Usuário (login)</label>
				<input type="text" id="kt-edit-u-login" readonly style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:7px;box-sizing:border-box;background:#f8fafc;color:#64748b;cursor:default">
			</div>
			<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
				<div>
					<label style="display:block;margin-bottom:4px;font-weight:600;font-size:.9em">Nome</label>
					<input type="text" id="kt-edit-u-first" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:7px;box-sizing:border-box">
				</div>
				<div>
					<label style="display:block;margin-bottom:4px;font-weight:600;font-size:.9em">Sobrenome</label>
					<input type="text" id="kt-edit-u-last" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:7px;box-sizing:border-box">
				</div>
				<div>
					<label style="display:block;margin-bottom:4px;font-weight:600;font-size:.9em">E-mail</label>
					<input type="email" id="kt-edit-u-email" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:7px;box-sizing:border-box">
				</div>
				<div>
					<label style="display:block;margin-bottom:4px;font-weight:600;font-size:.9em">Nível de Acesso</label>
					<select id="kt-edit-u-role" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:7px;box-sizing:border-box">
						<option value="kt_admin">Administrador</option>
						<option value="kt_location_manager">Gerente de Unidade</option>
						<option value="kt_staff">Colaborador</option>
					</select>
				</div>
				<div>
					<label style="display:block;margin-bottom:4px;font-weight:600;font-size:.9em">Cargo</label>
					<select id="kt-edit-u-position" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:7px;box-sizing:border-box">
						<option value="">— Sem cargo —</option>
						<?php foreach ( $positions as $pos ): ?>
						<option value="<?php echo absint($pos->id); ?>"><?php echo esc_html($pos->name); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div>
					<label style="display:block;margin-bottom:4px;font-weight:600;font-size:.9em">Data de Admissão</label>
					<input type="date" id="kt-edit-u-hire" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:7px;box-sizing:border-box">
				</div>
				<div>
					<label style="display:block;margin-bottom:4px;font-weight:600;font-size:.9em">Data de Aniversário</label>
					<input type="date" id="kt-edit-u-birth" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:7px;box-sizing:border-box">
				</div>
			</div>
			<!-- Unidade (Gerente ou Colaborador) -->
			<div id="kt-edit-u-location-wrap" style="display:none;margin-bottom:14px">
				<label style="display:block;margin-bottom:4px;font-weight:600;font-size:.9em" id="kt-edit-u-location-label">Unidade</label>
				<select id="kt-edit-u-location" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:7px">
					<option value="">— Selecione a unidade —</option>
					<?php foreach ( $locations as $loc ): ?>
					<option value="<?php echo absint($loc->id); ?>"><?php echo esc_html($loc->name); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div style="display:flex;gap:10px;margin-top:4px">
				<button type="button" id="kt-save-user-btn" class="kt-btn kt-btn-primary">Salvar</button>
				<button type="button" id="kt-user-modal-close2" class="kt-btn kt-btn-outline">Cancelar</button>
			</div>
			<p id="kt-user-modal-msg" style="margin:10px 0 0;font-size:.88em;min-height:1.2em"></p>
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
.kt-admin-tab{padding:10px 18px;text-decoration:none!important;color:#64748b!important;border-radius:8px 8px 0 0;font-size:.92em;font-weight:500;transition:background .15s,color .15s;border:2px solid transparent;border-bottom:none;margin-bottom:-2px}
.kt-admin-tab:hover{color:#1e293b!important;background:#f8fafc}
.kt-admin-tab.active{color:#1e293b!important;background:#fff;border-color:#e2e8f0;border-bottom-color:#fff;font-weight:700}
.kt-admin-tab-content{padding:4px 0 0}
.kt-loc-edit-btn,.kt-edit-user-btn{color:#64748b!important;border-color:#cbd5e1!important;background:#fff!important}
.kt-loc-edit-btn:hover,.kt-edit-user-btn:hover{color:#1e293b!important;border-color:#94a3b8!important}
/* Abas de unidade no Painel */
.kt-unit-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px}
.kt-unit-tab{padding:7px 18px;border-radius:20px;text-decoration:none!important;font-size:.9em;font-weight:500;color:#64748b!important;border:1.5px solid #e2e8f0;background:#fff;transition:all .15s;white-space:nowrap}
.kt-unit-tab:hover{color:#1e293b!important;border-color:#94a3b8}
.kt-unit-tab.active{color:#fff!important;background:#1e293b;border-color:#1e293b;font-weight:600}
/* Cabeçalho da unidade selecionada */
.kt-unit-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;padding:14px 20px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;margin-bottom:24px}
.kt-unit-header-mgr{display:flex;flex-direction:column;gap:2px}
.kt-unit-header-label{font-size:.78em;color:#94a3b8;text-transform:uppercase;letter-spacing:.04em}
.kt-unit-header-value{font-weight:600;color:#1e293b;font-size:1em}
.kt-unit-header-stats{display:flex;gap:28px}
.kt-unit-stat{display:flex;flex-direction:column;align-items:center;gap:1px}
.kt-unit-stat strong{font-size:1.3em;font-weight:700;color:#1e293b;line-height:1.1}
.kt-unit-stat span{font-size:.78em;color:#64748b}
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

/* ══════════════ TAB: USUÁRIOS — modal de criação ══════════════ */

function openCreateUserModal(){
	$('#kt-u-first,#kt-u-last,#kt-u-email').val('');
	$('#kt-u-role').val('').trigger('change');
	$('#kt-u-position').val('');
	$('#kt-u-hire-date,#kt-u-birth-date').val('');
	$('#kt-u-send-email').prop('checked',true);
	$('#kt-user-msg').text('');
	$('#kt-access-msg-wrap').hide();
	$('#kt-create-user-modal-overlay').fadeIn(180);
	$('body').addClass('kt-modal-open');
}
function closeCreateUserModal(){ $('#kt-create-user-modal-overlay').fadeOut(150); $('body').removeClass('kt-modal-open'); }

$('#kt-open-create-user-btn').on('click', openCreateUserModal);
$('#kt-create-user-modal-close').on('click', closeCreateUserModal);
$('#kt-create-user-modal-overlay').on('click',function(e){ if($(e.target).is('#kt-create-user-modal-overlay')) closeCreateUserModal(); });

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
	var hireDate=$('#kt-u-hire-date').val(), birthDate=$('#kt-u-birth-date').val();
	var positionId=$('#kt-u-position').val()||'';

	if(!first||!email||!role){msg('#kt-user-msg','Preencha nome, e-mail e nível de acesso.',false);return;}

	$b.prop('disabled',true).text('Criando…');
	$.post(ktFrontend.ajaxUrl,{action:'kt_admin_create_user',nonce:ktFrontend.nonce,first_name:first,last_name:last,email:email,role:role,location_id:loc,send_email:sendEmail,hire_date:hireDate,birth_date:birthDate,position_id:positionId})
	.done(function(r){
		if(r.success){
			msg('#kt-user-msg','✓ Usuário "'+r.data.name+'" criado! Login: '+r.data.username,true);
			$('#kt-access-msg-wrap').show();
			$('#kt-access-msg-text').val(r.data.access_msg||'');
			$('#kt-copy-confirm').hide();
			// Recarrega lista após 8s para dar tempo de copiar a mensagem
			$('#kt-create-user-modal-overlay').data('needsReload',true);
			setTimeout(function(){ if($('#kt-create-user-modal-overlay').data('needsReload')) location.reload(); },8000);
		} else {
			msg('#kt-user-msg',(r.data&&r.data.message?r.data.message:'Erro.'),false);
		}
		$b.prop('disabled',false).text('Criar usuário');
	}).fail(function(){ msg('#kt-user-msg','Erro de conexão.',false); $b.prop('disabled',false).text('Criar usuário'); });
});

$('#kt-copy-access-msg').on('click',function(){
	var $ta=$('#kt-access-msg-text');
	$ta.select();
	if(navigator.clipboard&&window.isSecureContext){
		navigator.clipboard.writeText($ta.val()).then(function(){
			$('#kt-copy-confirm').show(); setTimeout(function(){$('#kt-copy-confirm').hide();},2500);
		});
	} else {
		try{ document.execCommand('copy'); $('#kt-copy-confirm').show(); setTimeout(function(){$('#kt-copy-confirm').hide();},2500); }catch(e){}
	}
});

/* ══════════════ MODAL: EDITAR USUÁRIO ══════════════ */

function updateEditUserRole(){
	var r=$('#kt-edit-u-role').val();
	$('#kt-edit-u-location-wrap').toggle(r==='kt_location_manager'||r==='kt_staff');
	$('#kt-edit-u-location-label').text(r==='kt_location_manager'?'Unidade (gerente da unidade)':'Unidade do colaborador');
}

$('#kt-edit-u-role').on('change', updateEditUserRole);

$(document).on('click','.kt-edit-user-btn',function(){
	var $b=$(this);
	$('#kt-edit-user-id').val($b.data('id'));
	$('#kt-edit-u-login').val($b.data('login')||'');
	$('#kt-edit-u-first').val($b.data('first'));
	$('#kt-edit-u-last').val($b.data('last'));
	$('#kt-edit-u-email').val($b.data('email'));
	$('#kt-edit-u-role').val($b.data('role')||'kt_staff').trigger('change');
	$('#kt-edit-u-position').val($b.data('position')||'');
	$('#kt-edit-u-location').val($b.data('location')||'');
	$('#kt-edit-u-hire').val($b.data('hire')||'');
	$('#kt-edit-u-birth').val($b.data('birth')||'');
	$('#kt-user-modal-msg').text('');
	$('#kt-user-modal-overlay').fadeIn(180);
	$('body').addClass('kt-modal-open');
});

function closeUserModal(){ $('#kt-user-modal-overlay').fadeOut(150); $('body').removeClass('kt-modal-open'); }
$('#kt-user-modal-close,#kt-user-modal-close2').on('click', closeUserModal);
$('#kt-user-modal-overlay').on('click',function(e){ if($(e.target).is('#kt-user-modal-overlay')) closeUserModal(); });

$('#kt-save-user-btn').on('click',function(){
	var $b=$(this);
	var uid=$('#kt-edit-user-id').val();
	var first=$('#kt-edit-u-first').val().trim(), last=$('#kt-edit-u-last').val().trim();
	var email=$('#kt-edit-u-email').val().trim(), role=$('#kt-edit-u-role').val();
	var loc=$('#kt-edit-u-location').val();
	var hire=$('#kt-edit-u-hire').val(), birth=$('#kt-edit-u-birth').val();
	var posId=$('#kt-edit-u-position').val()||'';
	if(!first||!email||!role){msg('#kt-user-modal-msg','Nome e e-mail são obrigatórios.',false);return;}
	$b.prop('disabled',true).text('Salvando…');
	$.post(ktFrontend.ajaxUrl,{action:'kt_admin_update_user',nonce:ktFrontend.nonce,
		user_id:uid,first_name:first,last_name:last,email:email,role:role,location_id:loc,hire_date:hire,birth_date:birth,position_id:posId})
	.done(function(r){
		msg('#kt-user-modal-msg',r.success?r.data.message:(r.data&&r.data.message?r.data.message:'Erro.'),r.success);
		if(r.success){
			var $row=$('#kt-user-row-'+uid);
			// kt-u-name sempre mostra user_login — não alterar aqui
			$row.find('.kt-u-email').text(email);
			$row.find('.kt-u-role span').text(r.data.role_label);
			$row.find('.kt-u-loc').text(r.data.loc_name||'—');
			$row.find('.kt-edit-user-btn')
				.data('first',first).data('last',last).data('email',email)
				.data('role',role).data('location',parseInt(loc)||0)
				.data('hire',hire).data('birth',birth);
			setTimeout(closeUserModal, 800);
		}
		$b.prop('disabled',false).text('Salvar');
	}).fail(function(){ msg('#kt-user-modal-msg','Erro de conexão.',false); $b.prop('disabled',false).text('Salvar'); });
});

/* ══════════════ TAB: USUÁRIOS — excluir ══════════════ */

$(document).on('click','.kt-delete-user-btn',function(){
	var $b=$(this), id=$b.data('id'), login=$b.data('login');
	if(!confirm('Excluir o usuário "'+login+'"?\n\nEsta ação é irreversível: o usuário, seus registros de membro, matrículas e progresso serão removidos permanentemente.')) return;
	$b.prop('disabled',true).text('Excluindo…');
	$.post(ktFrontend.ajaxUrl,{action:'kt_admin_delete_user',nonce:ktFrontend.nonce,user_id:id})
	.done(function(r){
		if(r.success){
			$('#kt-user-row-'+id).fadeOut(250,function(){$(this).remove();});
		} else {
			alert(r.data&&r.data.message?r.data.message:'Erro ao excluir.');
			$b.prop('disabled',false).text('Excluir');
		}
	}).fail(function(){ alert('Erro de conexão.'); $b.prop('disabled',false).text('Excluir'); });
});

/* ══════════════ TAB: CARGOS ══════════════ */

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
	if(!confirm('Remover o cargo "'+name+'"? Colaboradores vinculados ficarão sem cargo.')) return;
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
$(document).on('keydown',function(e){ if(e.key==='Escape'){ closeModal(); closeLocModal(); closeUserModal(); closeCreateUserModal(); } });

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
