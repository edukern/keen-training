<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap kt-wrap">
	<h1>🎓 Keen Training</h1>

	<?php if ( $show_wizard ): ?>
	<div class="kt-wizard-banner">
		<div class="kt-wizard-inner">
			<h2>👋 Bem-vindo(a) ao Keen Training!</h2>
			<p>Siga estes passos para configurar o plugin e começar os treinamentos.</p>
			<div class="kt-steps">
				<div class="kt-step">
					<div class="kt-step-num">1</div>
					<div>
						<strong>Criar Unidades</strong><br>
						<span>Cadastre as unidades ou departamentos da organização e seus responsáveis.</span><br>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-locations&action=add' ) ); ?>" class="button button-primary" style="margin-top:8px">Criar Unidade →</a>
					</div>
				</div>
				<div class="kt-step">
					<div class="kt-step-num">2</div>
					<div>
						<strong>Criar Funções</strong><br>
						<span>Defina os cargos da equipe (Vendas, Caixa, etc.) para organizar e restringir cursos.</span><br>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-positions&action=add' ) ); ?>" class="button button-primary" style="margin-top:8px">Criar Função →</a>
					</div>
				</div>
				<div class="kt-step">
					<div class="kt-step-num">3</div>
					<div>
						<strong>Adicionar Colaboradores</strong><br>
						<span>Cadastre os funcionários, vincule à unidade e defina a função.</span><br>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-members&action=add' ) ); ?>" class="button button-primary" style="margin-top:8px">Adicionar Colaborador →</a>
					</div>
				</div>
				<div class="kt-step">
					<div class="kt-step-num">4</div>
					<div>
						<strong>Criar Cursos e Módulos</strong><br>
						<span>Crie os cursos, adicione módulos e vincule cada módulo a uma página do Elementor. Insira o shortcode <code>[kt_modulo]</code> na página para controle de conclusão.</span><br>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-courses&action=add' ) ); ?>" class="button button-primary" style="margin-top:8px">Criar Curso →</a>
					</div>
				</div>
				<div class="kt-step">
					<div class="kt-step-num">5</div>
					<div>
						<strong>Criar Avaliações</strong><br>
						<span>Monte quizzes e vincule a módulos. O colaborador precisa passar na avaliação para concluir o módulo.</span><br>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-quizzes&action=add' ) ); ?>" class="button button-primary" style="margin-top:8px">Criar Avaliação →</a>
					</div>
				</div>
				<div class="kt-step">
					<div class="kt-step-num">6</div>
					<div>
						<strong>Configurar o Portal</strong><br>
						<span>Crie uma página no WordPress e adicione o shortcode <code>[kt_portal]</code> — é onde os colaboradores acessam os treinamentos.</span><br>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=page' ) ); ?>" class="button button-primary" style="margin-top:8px">Criar Página →</a>
					</div>
				</div>
				<div class="kt-step">
					<div class="kt-step-num">7</div>
					<div>
						<strong>Atribuir Treinamentos</strong><br>
						<span>Matricule colaboradores individuais ou unidades inteiras em cursos, com prazo opcional.</span><br>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-enrollments' ) ); ?>" class="button button-primary" style="margin-top:8px">Atribuir Treinamentos →</a>
					</div>
				</div>
			</div>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:16px">
				<?php wp_nonce_field( 'kt_dismiss_setup' ); ?>
				<input type="hidden" name="action" value="kt_dismiss_setup">
				<button type="submit" class="button">✓ Entendi, dispensar este guia</button>
			</form>
		</div>
	</div>
	<?php endif; ?>

	<div class="kt-stats-grid">
		<div class="kt-stat-card">
			<span class="kt-stat-icon dashicons dashicons-store"></span>
			<div class="kt-stat-value"><?php echo esc_html( $stats['locations'] ); ?></div>
			<div class="kt-stat-label">Unidades</div>
		</div>
		<div class="kt-stat-card">
			<span class="kt-stat-icon dashicons dashicons-groups"></span>
			<div class="kt-stat-value"><?php echo esc_html( $stats['members'] ); ?></div>
			<div class="kt-stat-label">Colaboradores</div>
		</div>
		<div class="kt-stat-card">
			<span class="kt-stat-icon dashicons dashicons-welcome-learn-more"></span>
			<div class="kt-stat-value"><?php echo esc_html( $stats['courses'] ); ?></div>
			<div class="kt-stat-label">Cursos</div>
		</div>
	</div>

	<div class="kt-quick-links">
		<h2>Ações Rápidas</h2>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-members&action=add' ) ); ?>" class="button button-primary">+ Novo Colaborador</a>
		<?php if ( KT_Roles::is_super_admin() ): ?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-courses&action=add' ) ); ?>" class="button button-primary">+ Novo Curso</a>
		<?php endif; ?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-enrollments' ) ); ?>" class="button">Atribuir Treinamento</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-progress' ) ); ?>" class="button">Ver Progresso</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=kt-certificates' ) ); ?>" class="button">Certificados</a>
	</div>

	<?php if ( KT_Roles::is_super_admin() ): ?>
	<div class="kt-settings-box" style="margin-top:32px;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;max-width:480px">
		<h2 style="margin-top:0">Aparência</h2>
		<?php if ( isset( $_GET['color_saved'] ) ): ?>
			<div class="notice notice-success inline" style="margin-bottom:16px"><p>Cor salva com sucesso.</p></div>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'kt_save_color' ); ?>
			<input type="hidden" name="action" value="kt_save_color">
			<table class="form-table" style="margin:0">
				<tr>
					<th style="padding:8px 0;width:160px"><label for="kt_primary_color">Cor primária dos botões</label></th>
					<td style="padding:8px 0">
						<input type="color" id="kt_primary_color" name="kt_primary_color"
							value="<?php echo esc_attr( get_option( 'kt_primary_color', '#3b82f6' ) ); ?>"
							style="width:60px;height:36px;padding:2px;border:1px solid #ddd;border-radius:4px;cursor:pointer">
						<p class="description" style="margin-top:6px">Cor usada nos botões do portal do colaborador.</p>
					</td>
				</tr>
			</table>
			<button type="submit" class="button button-primary" style="margin-top:12px">Salvar Cor</button>
		</form>
	</div>
	<?php endif; ?>
</div>
