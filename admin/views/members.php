<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap kt-wrap">
	<h1>Colaboradores
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-members&action=add' ) ); ?>" class="page-title-action">+ Adicionar Colaborador</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-members&action=import' ) ); ?>" class="page-title-action">↑ Importar CSV</a>
	</h1>

	<?php if ( isset( $_GET['saved'] ) ): ?><div class="notice notice-success is-dismissible"><p>✓ Colaborador salvo com sucesso.</p></div><?php endif; ?>
	<?php if ( isset( $_GET['deleted'] ) ): ?><div class="notice notice-success is-dismissible"><p>✓ Colaborador removido.</p></div><?php endif; ?>
	<?php if ( isset( $_GET['error'] ) ): ?><div class="notice notice-error is-dismissible"><p>⚠ <?php echo esc_html( urldecode( $_GET['error'] ) ); ?></p></div><?php endif; ?>
	<?php if ( isset( $_GET['import_done'] ) ): ?><div class="notice notice-success is-dismissible"><p>✓ <?php echo esc_html( urldecode( $_GET['import_done'] ) ); ?></p></div><?php endif; ?>
	<?php if ( isset( $_GET['import_error'] ) ): ?><div class="notice notice-error is-dismissible"><p>⚠ <?php echo esc_html( urldecode( $_GET['import_error'] ) ); ?></p></div><?php endif; ?>
	<?php if ( isset( $_GET['bulk_done'] ) ): ?><div class="notice notice-success is-dismissible"><p>✓ <?php echo absint( $_GET['bulk_done'] ); ?> colaborador(es) atualizado(s).</p></div><?php endif; ?>
	<?php if ( isset( $_GET['bulk_removed'] ) ): ?><div class="notice notice-success is-dismissible"><p>✓ <?php echo absint( $_GET['bulk_removed'] ); ?> colaborador(es) removido(s).</p></div><?php endif; ?>
	<?php if ( isset( $_GET['bulk_error'] ) ): ?><div class="notice notice-error is-dismissible"><p>⚠ Selecione ao menos um colaborador e uma ação.</p></div><?php endif; ?>

	<?php if ( $action === 'import' ): ?>
	<div class="kt-card">
		<h2>Importar Colaboradores por CSV</h2>
		<p>Faça upload de um arquivo <code>.csv</code> com as colunas abaixo. O separador pode ser <strong>ponto-e-vírgula (;)</strong> ou vírgula (,).</p>

		<table class="widefat striped" style="max-width:680px;margin-bottom:20px">
			<thead><tr><th>Coluna</th><th>Obrigatória?</th><th>Exemplo</th></tr></thead>
			<tbody>
				<tr><td><strong>NOME</strong></td><td>Sim</td><td>João Silva</td></tr>
				<tr><td><strong>E-MAIL</strong></td><td>Sim</td><td>joao@empresa.com.br</td></tr>
				<tr><td><strong>UNIDADE</strong></td><td>Não</td><td>Sede SP</td></tr>
				<tr><td><strong>FUNÇÃO</strong></td><td>Não</td><td>Vendedor(a)</td></tr>
				<tr><td><strong>DATA DE ADMISSÃO</strong></td><td>Não</td><td>2023-03-15 <em style="color:#888">ou</em> 15/03/2023</td></tr>
				<tr><td><strong>DATA DE ANIVERSÁRIO</strong></td><td>Não</td><td>1990-07-22 <em style="color:#888">ou</em> 22/07/1990</td></tr>
			</tbody>
		</table>

		<p class="description">
			<strong>Senha:</strong> gerada automaticamente como <em>PrimeiroNome + ano + !</em> (ex: <code>João2026!</code>). O colaborador pode alterar após o primeiro acesso.<br>
			<strong>Usuário já existe:</strong> se o e-mail já tiver conta no WordPress, o colaborador é vinculado sem criar novo usuário.<br>
			<strong>UNIDADE:</strong> deve ser exatamente o nome da unidade cadastrada no sistema. Se não for encontrada, o colaborador é criado sem unidade.
		</p>

		<p>
			<a href="<?php echo esc_url( KT_PLUGIN_URL . 'assets/modelo-colaboradores.csv' ); ?>" download class="button">⬇ Baixar modelo CSV</a>
		</p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
			<?php wp_nonce_field( 'kt_import_members' ); ?>
			<input type="hidden" name="action" value="kt_import_members">

			<?php if ( KT_Roles::is_super_admin() ): ?>
			<table class="form-table">
				<tr>
					<th><label for="default_location_id">Unidade padrão</label></th>
					<td>
						<select id="default_location_id" name="default_location_id">
							<option value="0">— Usar UNIDADE do CSV —</option>
							<?php foreach ( $locations as $loc ): ?>
							<option value="<?php echo esc_attr( $loc->id ); ?>"><?php echo esc_html( $loc->name ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description">Se selecionada, todos os colaboradores do CSV serão atribuídos a esta unidade (ignora a coluna UNIDADE).</p>
					</td>
				</tr>
			</table>
			<?php endif; ?>

			<table class="form-table">
				<tr>
					<th><label for="csv_file">Arquivo CSV <span class="required">*</span></label></th>
					<td>
						<input type="file" id="csv_file" name="csv_file" accept=".csv,text/csv" required>
					</td>
				</tr>
				<tr>
					<th><label for="default_password">Senha padrão</label></th>
					<td>
						<input type="text" id="default_password" name="default_password" class="regular-text" placeholder="Ex: Treinamento@2026">
						<p class="description">
							Se preenchida, todos os colaboradores importados receberão esta senha.<br>
							Se deixada em branco, a senha será gerada automaticamente como <strong>PrimeiroNome + ano + !</strong> (ex: <code>João2026!</code>).
						</p>
					</td>
				</tr>
				<tr>
					<th>Atualizar existentes</th>
					<td>
						<label style="display:flex;align-items:center;gap:8px">
							<input type="checkbox" name="update_existing" value="1">
							<span>Atualizar dados de colaboradores que já existem no sistema</span>
						</label>
						<p class="description">Se marcado, colaboradores com o mesmo e-mail terão <strong>unidade, função, data de admissão e data de aniversário</strong> atualizados com os valores do CSV (campos vazios no CSV preservam o valor atual). Se desmarcado, duplicatas são ignoradas.</p>
					</td>
				</tr>
				<tr>
					<th>E-mail de boas-vindas</th>
					<td>
						<label style="display:flex;align-items:center;gap:8px">
							<input type="checkbox" name="send_welcome_email" value="1">
							<span>Enviar e-mail de boas-vindas para cada colaborador novo criado</span>
						</label>
						<p class="description">O e-mail contém o link de acesso ao site. A senha temporária <strong>não</strong> é enviada por segurança — o colaborador define uma nova senha pelo link. Colaboradores já existentes no WordPress não recebem o e-mail.</p>
					</td>
				</tr>
			</table>
			<?php submit_button( '↑ Importar Colaboradores', 'primary', 'submit', false ); ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-members' ) ); ?>" class="button" style="margin-left:8px">Cancelar</a>
		</form>
	</div>

	<?php elseif ( in_array( $action, [ 'add', 'edit' ] ) ): ?>
	<div class="kt-card">
		<h2><?php echo $action === 'edit' ? 'Editar Colaborador' : 'Novo Colaborador'; ?></h2>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'kt_member' ); ?>
			<input type="hidden" name="action" value="kt_save_member">
			<input type="hidden" name="member_id" value="<?php echo $member ? absint( $member->id ) : 0; ?>">

			<?php if ( ! $member ): ?>
			<div class="kt-section-label">
				<strong>Opção A — Vincular usuário WordPress existente</strong>
				<p class="description">Se o colaborador já tem conta no WordPress, selecione aqui.</p>
			</div>
			<table class="form-table">
				<tr>
					<th><label for="existing_user_id">Usuário existente</label></th>
					<td>
						<select id="existing_user_id" name="existing_user_id">
							<option value="">— Criar novo usuário (preencha abaixo) —</option>
							<?php foreach ( get_users( [ 'orderby' => 'display_name' ] ) as $u ): ?>
							<option value="<?php echo esc_attr( $u->ID ); ?>"><?php echo esc_html( $u->display_name . ' (' . $u->user_email . ')' ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
			</table>

			<hr style="margin:20px 0">
			<div class="kt-section-label">
				<strong>Opção B — Criar novo usuário WordPress</strong>
				<p class="description">Preencha apenas se não selecionou um usuário acima. O colaborador receberá acesso com estas credenciais.</p>
			</div>
			<table class="form-table">
				<tr>
					<th><label for="first_name">Nome</label></th>
					<td><input type="text" id="first_name" name="first_name" class="regular-text" placeholder="João"></td>
				</tr>
				<tr>
					<th><label for="last_name">Sobrenome</label></th>
					<td><input type="text" id="last_name" name="last_name" class="regular-text" placeholder="Silva"></td>
				</tr>
				<tr>
					<th><label for="email">E-mail <span class="required">*</span></label></th>
					<td><input type="email" id="email" name="email" class="regular-text" placeholder="joao@empresa.com.br"></td>
				</tr>
				<tr>
					<th><label for="username">Login (usuário) <span class="required">*</span></label></th>
					<td><input type="text" id="username" name="username" class="regular-text" placeholder="joao.silva"></td>
				</tr>
				<tr>
					<th><label for="password">Senha temporária <span class="required">*</span></label></th>
					<td>
						<input type="text" id="password" name="password" class="regular-text" placeholder="Mínimo 6 caracteres">
						<p class="description">O colaborador poderá alterar a senha após o primeiro acesso.</p>
					</td>
				</tr>
			</table>
			<hr style="margin:20px 0">
			<?php endif; ?>

			<table class="form-table">
				<tr>
					<th><label for="location_id">Unidade <span class="required">*</span></label></th>
					<td>
						<select id="location_id" name="location_id" required>
							<option value="">— Selecionar Unidade —</option>
							<?php foreach ( $locations as $loc ):
								if ( ! KT_Roles::can_manage_location( $loc->id ) ) continue; ?>
							<option value="<?php echo esc_attr( $loc->id ); ?>" <?php selected( $member ? $member->location_id : $current_loc, $loc->id ); ?>>
								<?php echo esc_html( $loc->name ); ?>
							</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="position_id">Função</label></th>
					<td>
						<?php $all_positions = KT_Position::get_all(); ?>
						<?php if ( $all_positions ): ?>
						<select id="position_id" name="position_id">
							<option value="">— Sem função definida —</option>
							<?php foreach ( $all_positions as $pos ): ?>
							<option value="<?php echo esc_attr( $pos->id ); ?>"
								<?php selected( $member ? (int) $member->position_id : 0, (int) $pos->id ); ?>>
								<?php echo esc_html( $pos->name ); ?>
							</option>
							<?php endforeach; ?>
						</select>
						<?php else: ?>
						<em style="color:#888">Nenhuma função cadastrada. <a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-positions&action=add' ) ); ?>">Criar funções →</a></em>
						<input type="hidden" name="position_id" value="">
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th><label for="hire_date">Data de Admissão</label></th>
					<td><input type="date" id="hire_date" name="hire_date" value="<?php echo $member ? esc_attr( $member->hire_date ) : ''; ?>"></td>
				</tr>
				<tr>
					<th><label for="birth_date">Data de Aniversário</label></th>
					<td>
						<input type="date" id="birth_date" name="birth_date" value="<?php echo $member ? esc_attr( $member->birth_date ) : ''; ?>">
						<p class="description">Usada para enviar notificações de felicitação ao time de marketing.</p>
					</td>
				</tr>
			</table>
			<p>
				<?php submit_button( $action === 'edit' ? 'Atualizar Colaborador' : 'Adicionar Colaborador', 'primary', 'submit', false ); ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-members' ) ); ?>" class="button" style="margin-left:8px">Cancelar</a>
			</p>
		</form>
	</div>

	<?php else: ?>

	<div class="kt-filter-bar">
		<?php if ( KT_Roles::is_super_admin() ): ?>
		<label>Unidade:
			<select id="kt-filter-loc" onchange="kt_apply_member_filters()">
				<option value="0">Todas</option>
				<?php foreach ( $locations as $loc ): ?>
				<option value="<?php echo esc_attr( $loc->id ); ?>" <?php selected( absint( $_GET['loc'] ?? 0 ), $loc->id ); ?>><?php echo esc_html( $loc->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<?php endif; ?>
		<?php $all_positions_filter = KT_Position::get_all(); ?>
		<?php if ( $all_positions_filter ): ?>
		<label>Função:
			<select id="kt-filter-pos" onchange="kt_apply_member_filters()">
				<option value="0">Todas</option>
				<?php foreach ( $all_positions_filter as $pos ): ?>
				<option value="<?php echo esc_attr( $pos->id ); ?>" <?php selected( absint( $_GET['pos'] ?? 0 ), $pos->id ); ?>><?php echo esc_html( $pos->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<script>
		function kt_apply_member_filters() {
			var loc = document.getElementById('kt-filter-loc') ? document.getElementById('kt-filter-loc').value : 0;
			var pos = document.getElementById('kt-filter-pos').value;
			window.location.href = '<?php echo esc_url( admin_url( 'admin.php?page=kt-members' ) ); ?>&loc=' + loc + '&pos=' + pos;
		}
		</script>
		<?php endif; ?>
	</div>

	<?php
	$filter_loc  = KT_Roles::is_super_admin() ? absint( $_GET['loc'] ?? 0 ) : $current_loc;
	$filter_pos  = absint( $_GET['pos'] ?? 0 );
	$all_members = KT_Member::get_all( $filter_loc, $filter_pos );
	$all_positions_bulk = KT_Position::get_all();
	?>

	<!-- Form de delete (único, fora do bulk — preenchido via JS) -->
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="kt-delete-member-form">
		<?php wp_nonce_field( 'kt_delete_member' ); ?>
		<input type="hidden" name="action" value="kt_delete_member">
		<input type="hidden" name="member_id" value="" id="kt-delete-member-id">
		<input type="hidden" name="delete_user" value="0" id="kt-delete-user-flag">
	</form>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="kt-bulk-form">
		<?php wp_nonce_field( 'kt_bulk_members' ); ?>
		<input type="hidden" name="action" value="kt_bulk_members">
		<input type="hidden" name="filter_loc" value="<?php echo absint( $filter_loc ); ?>">
		<input type="hidden" name="filter_pos" value="<?php echo absint( $filter_pos ); ?>">

		<!-- Barra de ações em massa -->
		<div class="kt-bulk-bar" id="kt-bulk-bar">
			<span class="kt-bulk-count" id="kt-bulk-count">0 selecionado(s)</span>
			<select name="bulk_action" id="kt-bulk-action" onchange="ktBulkActionChange()">
				<option value="">— Ação em massa —</option>
				<option value="location">Alterar Unidade</option>
				<option value="position">Alterar Função</option>
				<option value="remove">Remover do Keen Training</option>
				<option value="remove_with_user">Remover + Excluir conta WP</option>
			</select>

			<span id="kt-bulk-loc-wrap" style="display:none">
				<select name="bulk_location_id">
					<option value="">— Selecionar Unidade —</option>
					<?php foreach ( $locations as $loc ): ?>
					<option value="<?php echo absint( $loc->id ); ?>"><?php echo esc_html( $loc->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</span>

			<span id="kt-bulk-pos-wrap" style="display:none">
				<select name="bulk_position_id">
					<option value="">— Sem função —</option>
					<?php foreach ( $all_positions_bulk as $pos ): ?>
					<option value="<?php echo absint( $pos->id ); ?>"><?php echo esc_html( $pos->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</span>

			<button type="submit" class="button button-primary" id="kt-bulk-apply" disabled onclick="return ktBulkConfirm()">Aplicar</button>
		</div>

		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th style="width:32px"><input type="checkbox" id="kt-check-all" title="Selecionar todos"></th>
					<th>Nome</th>
					<th>E-mail</th>
					<th>Unidade</th>
					<th>Função</th>
					<th>Admissão</th>
					<th>Aniversário</th>
					<th>Ações</th>
				</tr>
			</thead>
			<tbody>
			<?php if ( ! $all_members ): ?>
				<tr><td colspan="8" style="text-align:center;padding:20px;color:#888">Nenhum colaborador encontrado. <a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-members&action=add' ) ); ?>">Adicionar →</a></td></tr>
			<?php else: ?>
			<?php foreach ( $all_members as $m ): ?>
				<tr>
					<td><input type="checkbox" name="member_ids[]" value="<?php echo absint( $m->id ); ?>" class="kt-member-check"></td>
					<td><strong><?php echo esc_html( $m->full_name ?: $m->display_name ); ?></strong></td>
					<td><?php echo esc_html( $m->user_email ); ?></td>
					<td><?php echo esc_html( $m->location_name ?? '—' ); ?></td>
					<td>
						<?php
						if ( $m->position_id ) {
							$pos = KT_Position::get( $m->position_id );
							if ( $pos ) {
								echo '<span style="display:inline-flex;align-items:center;gap:6px">'
									. '<span style="width:10px;height:10px;border-radius:50%;background:' . esc_attr( $pos->color ) . ';flex-shrink:0"></span>'
									. esc_html( $pos->name )
									. '</span>';
							} else {
								echo '<em style="color:#aaa">—</em>';
							}
						} else {
							echo '<em style="color:#aaa">—</em>';
						}
						?>
					</td>
					<td><?php echo $m->hire_date  ? esc_html( date_i18n( 'd/m/Y', strtotime( $m->hire_date ) ) )  : '—'; ?></td>
					<td><?php echo $m->birth_date ? esc_html( date_i18n( 'd/m',   strtotime( $m->birth_date ) ) ) : '—'; ?></td>
					<td>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-members&action=edit&id=' . $m->id ) ); ?>">Editar</a>
						&nbsp;|&nbsp;
						<button type="button" class="button-link kt-delete-link"
							onclick="ktDeleteMember(<?php echo absint( $m->id ); ?>, false)">Remover</button>
						&nbsp;|&nbsp;
						<button type="button" class="button-link kt-delete-link"
							onclick="ktDeleteMember(<?php echo absint( $m->id ); ?>, true)">Excluir conta</button>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
	</form>

	<script>
	(function(){
		var $checkAll  = document.getElementById('kt-check-all');
		var $applyBtn  = document.getElementById('kt-bulk-apply');
		var $countSpan = document.getElementById('kt-bulk-count');

		function getChecked() {
			return document.querySelectorAll('.kt-member-check:checked');
		}
		function updateBar() {
			var n = getChecked().length;
			$countSpan.textContent = n + ' selecionado(s)';
			$applyBtn.disabled = ( n === 0 || !document.getElementById('kt-bulk-action').value );
		}

		if ($checkAll) {
			$checkAll.addEventListener('change', function(){
				document.querySelectorAll('.kt-member-check').forEach(function(cb){ cb.checked = $checkAll.checked; });
				updateBar();
			});
		}
		document.querySelectorAll('.kt-member-check').forEach(function(cb){
			cb.addEventListener('change', function(){
				$checkAll.indeterminate = false;
				var all   = document.querySelectorAll('.kt-member-check').length;
				var chkd  = getChecked().length;
				if ( chkd === 0 )    $checkAll.checked = false;
				else if ( chkd === all ) $checkAll.checked = true;
				else                 { $checkAll.checked = false; $checkAll.indeterminate = true; }
				updateBar();
			});
		});
	})();

	function ktBulkActionChange() {
		var action = document.getElementById('kt-bulk-action').value;
		var btn    = document.getElementById('kt-bulk-apply');
		document.getElementById('kt-bulk-loc-wrap').style.display = action === 'location' ? '' : 'none';
		document.getElementById('kt-bulk-pos-wrap').style.display = action === 'position' ? '' : 'none';
		btn.disabled = ( !action || document.querySelectorAll('.kt-member-check:checked').length === 0 );
		// Botão vermelho para remoção, primário para demais
		if ( action === 'remove' || action === 'remove_with_user' ) {
			btn.classList.remove('button-primary');
			btn.classList.add('button-danger-kt');
			btn.textContent = action === 'remove_with_user' ? 'Remover + Excluir conta' : 'Remover';
		} else {
			btn.classList.add('button-primary');
			btn.classList.remove('button-danger-kt');
			btn.textContent = 'Aplicar';
		}
	}

	function ktDeleteMember(id, deleteUser) {
		var msg = deleteUser
			? 'Remover este colaborador E excluir a conta WordPress?\n\nTodo o histórico de treinamentos será apagado e o login no site será desativado permanentemente. Esta ação não pode ser desfeita.'
			: 'Remover este colaborador do Keen Training?\n\nA conta WordPress será mantida, mas todo o histórico de treinamentos será apagado.';
		if ( ! confirm(msg) ) return;
		document.getElementById('kt-delete-member-id').value    = id;
		document.getElementById('kt-delete-user-flag').value    = deleteUser ? '1' : '0';
		document.getElementById('kt-delete-member-form').submit();
	}

	function ktBulkConfirm() {
		var n      = document.querySelectorAll('.kt-member-check:checked').length;
		var action = document.getElementById('kt-bulk-action');
		if ( n === 0 || !action.value ) return false;
		if ( action.value === 'remove' ) {
			return confirm( '⚠ Remover ' + n + ' colaborador(es) do Keen Training?\n\nAs contas WordPress NÃO serão excluídas, mas todo o histórico de treinamentos, progresso e certificados será apagado. Esta ação não pode ser desfeita.' );
		}
		if ( action.value === 'remove_with_user' ) {
			return confirm( '⚠ Remover ' + n + ' colaborador(es) E excluir as contas WordPress?\n\nOs logins no site serão desativados permanentemente e todo o histórico de treinamentos será apagado. Esta ação não pode ser desfeita.' );
		}
		var label = action.options[action.selectedIndex].text;
		return confirm( 'Aplicar "' + label + '" para ' + n + ' colaborador(es)?' );
	}
	</script>
	<?php endif; ?>
</div>
