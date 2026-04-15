<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap kt-wrap">
	<h1>Funções
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-positions&action=add' ) ); ?>" class="page-title-action">+ Nova Função</a>
	</h1>

	<?php if ( isset( $_GET['saved'] ) ): ?><div class="notice notice-success is-dismissible"><p>✓ Função salva com sucesso.</p></div><?php endif; ?>
	<?php if ( isset( $_GET['deleted'] ) ): ?><div class="notice notice-success is-dismissible"><p>✓ Função removida. Os colaboradores vinculados ficaram sem função.</p></div><?php endif; ?>
	<?php if ( isset( $_GET['error'] ) ): ?><div class="notice notice-error is-dismissible"><p>⚠ <?php echo esc_html( urldecode( $_GET['error'] ) ); ?></p></div><?php endif; ?>

	<?php if ( in_array( $action, [ 'add', 'edit' ] ) ): ?>
	<div class="kt-card">
		<h2><?php echo $action === 'edit' ? 'Editar Função' : 'Nova Função'; ?></h2>
		<p class="description">Funções são categorias de colaboradores usadas para organizar equipes e restringir acesso a cursos. Exemplos: Vendas, Caixa, Administrativo, Marketing.</p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'kt_position' ); ?>
			<input type="hidden" name="action" value="kt_save_position">
			<input type="hidden" name="position_id" value="<?php echo $position ? absint( $position->id ) : 0; ?>">

			<table class="form-table">
				<tr>
					<th><label for="name">Nome da Função <span class="required">*</span></label></th>
					<td>
						<input type="text" id="name" name="name" class="regular-text" required
							value="<?php echo $position ? esc_attr( $position->name ) : ''; ?>"
							placeholder="Ex: Vendas, Caixa, Administrativo">
					</td>
				</tr>
				<tr>
					<th><label for="description">Descrição</label></th>
					<td>
						<textarea id="description" name="description" class="large-text" rows="2"
							placeholder="Descreva brevemente esta função..."><?php echo $position ? esc_textarea( $position->description ) : ''; ?></textarea>
					</td>
				</tr>
				<tr>
					<th><label for="color">Cor de identificação</label></th>
					<td>
						<input type="color" id="color" name="color"
							value="<?php echo $position ? esc_attr( $position->color ) : '#64748b'; ?>">
						<p class="description">Cor usada para identificar a função nas listagens.</p>
					</td>
				</tr>
			</table>

			<p>
				<?php submit_button( $action === 'edit' ? 'Atualizar Função' : 'Criar Função', 'primary', 'submit', false ); ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-positions' ) ); ?>" class="button" style="margin-left:8px">Cancelar</a>
			</p>
		</form>
	</div>

	<?php else: ?>

	<?php $all_positions = KT_Position::get_all(); ?>

	<?php if ( ! $all_positions ): ?>
	<div class="kt-card">
		<p>Nenhuma função cadastrada ainda.</p>
		<p>Crie funções como <strong>Vendas</strong>, <strong>Caixa</strong>, <strong>Administrativo</strong>, <strong>Marketing</strong> para organizar seus colaboradores e usar nas restrições de cursos.</p>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-positions&action=add' ) ); ?>" class="button button-primary">+ Criar primeira função</a>
	</div>
	<?php else: ?>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th style="width:32px">Cor</th>
				<th>Nome</th>
				<th>Descrição</th>
				<th>Colaboradores</th>
				<th>Ações</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $all_positions as $pos ): ?>
			<tr>
				<td>
					<span style="display:inline-block;width:22px;height:22px;border-radius:50%;background:<?php echo esc_attr( $pos->color ); ?>;vertical-align:middle"></span>
				</td>
				<td><strong><?php echo esc_html( $pos->name ); ?></strong></td>
				<td><?php echo $pos->description ? esc_html( $pos->description ) : '<em style="color:#aaa">—</em>'; ?></td>
				<td><?php echo absint( $pos->member_count ); ?></td>
				<td>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-positions&action=edit&id=' . $pos->id ) ); ?>">Editar</a>
					&nbsp;|&nbsp;
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline"
						onsubmit="return confirm('Remover esta função? Os colaboradores vinculados ficarão sem função.')">
						<?php wp_nonce_field( 'kt_delete_position' ); ?>
						<input type="hidden" name="action" value="kt_delete_position">
						<input type="hidden" name="position_id" value="<?php echo absint( $pos->id ); ?>">
						<button type="submit" class="button-link kt-delete-link">Excluir</button>
					</form>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>

	<?php endif; ?>
</div>
