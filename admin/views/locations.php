<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap kt-wrap">
	<h1>Unidades
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-locations&action=add' ) ); ?>" class="page-title-action">+ Adicionar Unidade</a>
	</h1>

	<?php if ( isset( $_GET['saved'] ) ): ?><div class="notice notice-success is-dismissible"><p>✓ Unidade salva com sucesso.</p></div><?php endif; ?>
	<?php if ( isset( $_GET['deleted'] ) ): ?><div class="notice notice-success is-dismissible"><p>✓ Unidade excluída.</p></div><?php endif; ?>

	<?php if ( in_array( $action, [ 'add', 'edit' ] ) ): ?>
	<div class="kt-card">
		<h2><?php echo $action === 'edit' ? 'Editar Unidade' : 'Nova Unidade'; ?></h2>
		<p class="description">Cada unidade representa um departamento, filial ou área da organização. Vincule um responsável para que ele possa administrar os colaboradores daquela unidade.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'kt_location' ); ?>
			<input type="hidden" name="action" value="kt_save_location">
			<input type="hidden" name="location_id" value="<?php echo $location ? absint( $location->id ) : 0; ?>">
			<table class="form-table">
				<tr>
					<th><label for="name">Nome da Unidade <span class="required">*</span></label></th>
					<td><input type="text" id="name" name="name" class="regular-text" value="<?php echo $location ? esc_attr( $location->name ) : ''; ?>" required placeholder="Ex: Sede SP, Filial RJ, Depto. Comercial"></td>
				</tr>
				<tr>
					<th><label for="address">Endereço</label></th>
					<td><textarea id="address" name="address" class="large-text" rows="3" placeholder="Rua, número, cidade..."><?php echo $location ? esc_textarea( $location->address ) : ''; ?></textarea></td>
				</tr>
				<tr>
					<th><label for="manager_id">Gerente da Unidade</label></th>
					<td>
						<select id="manager_id" name="manager_id">
							<option value="0">— Selecionar Gerente —</option>
							<?php foreach ( $managers as $u ): ?>
							<option value="<?php echo esc_attr( $u->ID ); ?>" <?php selected( $location ? $location->manager_id : 0, $u->ID ); ?>>
								<?php echo esc_html( $u->display_name . ' (' . $u->user_email . ')' ); ?>
							</option>
							<?php endforeach; ?>
						</select>
						<p class="description">O gerente selecionado receberá automaticamente o papel de "Gerente de Unidade" e só verá os dados desta unidade no painel.</p>
					</td>
				</tr>
			</table>
			<p>
				<?php submit_button( $action === 'edit' ? 'Atualizar Unidade' : 'Criar Unidade', 'primary', 'submit', false ); ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-locations' ) ); ?>" class="button" style="margin-left:8px">Cancelar</a>
			</p>
		</form>
	</div>
	<?php else: ?>
	<p class="description">Gerencie as unidades da organização. Cada unidade tem seu próprio responsável e lista de colaboradores.</p>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th>Nome da Unidade</th>
				<th>Endereço</th>
				<th>Gerente</th>
				<th>Colaboradores</th>
				<th>Ações</th>
			</tr>
		</thead>
		<tbody>
		<?php $locs = KT_Location::get_all(); ?>
		<?php if ( ! $locs ): ?>
			<tr><td colspan="5" style="text-align:center;padding:20px;color:#888">Nenhuma unidade cadastrada ainda. <a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-locations&action=add' ) ); ?>">Criar a primeira →</a></td></tr>
		<?php else: ?>
		<?php foreach ( $locs as $loc ):
			$manager = $loc->manager_id ? get_user_by( 'id', $loc->manager_id ) : null;
		?>
			<tr>
				<td><strong><?php echo esc_html( $loc->name ); ?></strong></td>
				<td><?php echo esc_html( $loc->address ) ?: '<em style="color:#aaa">—</em>'; ?></td>
				<td><?php echo $manager ? esc_html( $manager->display_name ) : '<em style="color:#aaa">Sem gerente</em>'; ?></td>
				<td><?php echo esc_html( KT_Location::get_member_count( $loc->id ) ); ?></td>
				<td>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-locations&action=edit&id=' . $loc->id ) ); ?>">Editar</a>
					&nbsp;|&nbsp;
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline" onsubmit="return confirm('Excluir esta unidade? Os colaboradores não serão excluídos, mas perderão o vínculo.')">
						<?php wp_nonce_field( 'kt_delete_location' ); ?>
						<input type="hidden" name="action" value="kt_delete_location">
						<input type="hidden" name="location_id" value="<?php echo absint( $loc->id ); ?>">
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
