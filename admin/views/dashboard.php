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
	<?php
	$fonts_disponiveis = [
		''                 => '— Herdar do tema —',
		'Inter'            => 'Inter',
		'Roboto'           => 'Roboto',
		'Open Sans'        => 'Open Sans',
		'Lato'             => 'Lato',
		'Montserrat'       => 'Montserrat',
		'Poppins'          => 'Poppins',
		'Raleway'          => 'Raleway',
		'Nunito'           => 'Nunito',
		'Playfair Display' => 'Playfair Display',
		'Merriweather'     => 'Merriweather',
	];
	$pesos_disponiveis = [ 300 => 'Leve (300)', 400 => 'Normal (400)', 500 => 'Médio (500)', 600 => 'Semibold (600)', 700 => 'Bold (700)', 800 => 'Extrabold (800)' ];
	$saved_primary          = get_option( 'kt_primary_color',       '#3b82f6' );
	$saved_btn_bg           = get_option( 'kt_btn_bg',              $saved_primary );
	$saved_btn_text         = get_option( 'kt_btn_text',            '#ffffff' );
	$saved_btn_hover_bg     = get_option( 'kt_btn_hover_bg',        '' );
	$saved_btn_hover_text   = get_option( 'kt_btn_hover_text',      '#ffffff' );
	$saved_font_heading        = get_option( 'kt_font_heading',        '' );
	$saved_font_heading_weight = get_option( 'kt_font_heading_weight', 700 );
	$saved_font_body           = get_option( 'kt_font_body',           '' );
	?>
	<div class="kt-settings-box" style="margin-top:32px;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;max-width:600px">
		<h2 style="margin-top:0">Aparência do Portal</h2>
		<?php if ( isset( $_GET['color_saved'] ) ): ?>
			<div class="notice notice-success inline" style="margin-bottom:16px"><p>Configurações salvas.</p></div>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'kt_save_color' ); ?>
			<input type="hidden" name="action" value="kt_save_color">

			<h3 style="margin:0 0 8px;font-size:.95em;text-transform:uppercase;letter-spacing:.05em;color:#64748b">Cores</h3>
			<table class="form-table" style="margin:0 0 20px">
				<tr>
					<th style="padding:8px 0;width:200px"><label for="kt_primary_color">Cor primária</label></th>
					<td style="padding:8px 0;display:flex;align-items:center;gap:10px">
						<input type="color" id="kt_primary_color" name="kt_primary_color" value="<?php echo esc_attr( $saved_primary ); ?>" style="width:50px;height:32px;padding:2px;border:1px solid #ddd;border-radius:4px;cursor:pointer">
						<span class="description">Links, barra de progresso e destaques.</span>
					</td>
				</tr>
				<tr>
					<th style="padding:8px 0"><label for="kt_btn_bg">Fundo do botão</label></th>
					<td style="padding:8px 0;display:flex;align-items:center;gap:10px">
						<input type="color" id="kt_btn_bg" name="kt_btn_bg" value="<?php echo esc_attr( $saved_btn_bg ); ?>" style="width:50px;height:32px;padding:2px;border:1px solid #ddd;border-radius:4px;cursor:pointer">
					</td>
				</tr>
				<tr>
					<th style="padding:8px 0"><label for="kt_btn_text">Texto do botão</label></th>
					<td style="padding:8px 0;display:flex;align-items:center;gap:10px">
						<input type="color" id="kt_btn_text" name="kt_btn_text" value="<?php echo esc_attr( $saved_btn_text ); ?>" style="width:50px;height:32px;padding:2px;border:1px solid #ddd;border-radius:4px;cursor:pointer">
					</td>
				</tr>
				<tr>
					<th style="padding:8px 0"><label for="kt_btn_hover_bg">Fundo no hover</label></th>
					<td style="padding:8px 0;display:flex;align-items:center;gap:10px">
						<input type="color" id="kt_btn_hover_bg" name="kt_btn_hover_bg" value="<?php echo esc_attr( $saved_btn_hover_bg ?: $saved_btn_bg ); ?>" style="width:50px;height:32px;padding:2px;border:1px solid #ddd;border-radius:4px;cursor:pointer">
						<span class="description">Se não definido, usa versão mais escura do fundo.</span>
					</td>
				</tr>
				<tr>
					<th style="padding:8px 0"><label for="kt_btn_hover_text">Texto no hover</label></th>
					<td style="padding:8px 0;display:flex;align-items:center;gap:10px">
						<input type="color" id="kt_btn_hover_text" name="kt_btn_hover_text" value="<?php echo esc_attr( $saved_btn_hover_text ); ?>" style="width:50px;height:32px;padding:2px;border:1px solid #ddd;border-radius:4px;cursor:pointer">
					</td>
				</tr>
			</table>

			<h3 style="margin:0 0 8px;font-size:.95em;text-transform:uppercase;letter-spacing:.05em;color:#64748b">Tipografia</h3>
			<table class="form-table" style="margin:0">
				<tr>
					<th style="padding:8px 0;width:200px"><label for="kt_font_heading">Fonte dos títulos</label></th>
					<td style="padding:8px 0">
						<select id="kt_font_heading" name="kt_font_heading" style="min-width:200px">
							<?php foreach ( $fonts_disponiveis as $val => $label ): ?>
								<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $saved_font_heading, $val ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th style="padding:8px 0"><label for="kt_font_heading_weight">Peso dos títulos</label></th>
					<td style="padding:8px 0">
						<select id="kt_font_heading_weight" name="kt_font_heading_weight" style="min-width:200px">
							<?php foreach ( $pesos_disponiveis as $val => $label ): ?>
								<option value="<?php echo esc_attr( $val ); ?>" <?php selected( (int) $saved_font_heading_weight, $val ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th style="padding:8px 0"><label for="kt_font_body">Fonte do texto</label></th>
					<td style="padding:8px 0">
						<select id="kt_font_body" name="kt_font_body" style="min-width:200px">
							<?php foreach ( $fonts_disponiveis as $val => $label ): ?>
								<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $saved_font_body, $val ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description" style="margin-top:4px">Fontes carregadas do Google Fonts automaticamente.</p>
					</td>
				</tr>
			</table>
			<button type="submit" class="button button-primary" style="margin-top:16px">Salvar Aparência</button>
		</form>
	</div>
	<?php
	$cert_company  = get_option( 'kt_cert_company_name', get_bloginfo( 'name' ) );
	$cert_logo_url = get_option( 'kt_cert_logo_url',     '' );
	$cert_accent   = get_option( 'kt_cert_accent_color', $saved_primary );
	$cert_show_id  = get_option( 'kt_cert_show_id',      '1' );
	?>
	<div class="kt-settings-box" style="margin-top:24px;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;max-width:600px">
		<h2 style="margin-top:0">Certificado de Conclusão</h2>
		<?php if ( isset( $_GET['cert_saved'] ) ): ?>
			<div class="notice notice-success inline" style="margin-bottom:16px"><p>Configurações do certificado salvas.</p></div>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'kt_save_cert' ); ?>
			<input type="hidden" name="action" value="kt_save_cert">
			<table class="form-table" style="margin:0">
				<tr>
					<th style="padding:8px 0;width:200px"><label for="kt_cert_company_name">Nome da empresa</label></th>
					<td style="padding:8px 0">
						<input type="text" id="kt_cert_company_name" name="kt_cert_company_name"
							value="<?php echo esc_attr( $cert_company ); ?>"
							style="width:100%;max-width:340px"
							placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
						<p class="description">Aparece no rodapé lateral e no cabeçalho do certificado.</p>
					</td>
				</tr>
				<tr>
					<th style="padding:8px 0"><label for="kt_cert_logo_url">URL da logo</label></th>
					<td style="padding:8px 0">
						<input type="url" id="kt_cert_logo_url" name="kt_cert_logo_url"
							value="<?php echo esc_attr( $cert_logo_url ); ?>"
							style="width:100%;max-width:340px"
							placeholder="https://...">
						<p class="description">Cole a URL de uma imagem da Biblioteca de Mídia (PNG, SVG ou JPG). Se vazio, exibe o nome da empresa em texto.</p>
						<?php if ( $cert_logo_url ): ?>
							<div style="margin-top:8px"><img src="<?php echo esc_url( $cert_logo_url ); ?>" style="max-height:48px;max-width:200px;border:1px solid #e2e8f0;border-radius:4px;padding:4px"></div>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th style="padding:8px 0"><label for="kt_cert_accent_color">Cor de destaque</label></th>
					<td style="padding:8px 0;display:flex;align-items:center;gap:10px">
						<input type="color" id="kt_cert_accent_color" name="kt_cert_accent_color"
							value="<?php echo esc_attr( $cert_accent ?: $saved_primary ); ?>"
							style="width:50px;height:32px;padding:2px;border:1px solid #ddd;border-radius:4px;cursor:pointer">
						<span class="description">Barra superior, sublinhados e destaques. Por padrão usa a cor primária do portal.</span>
					</td>
				</tr>
				<tr>
					<th style="padding:8px 0"><label for="kt_cert_show_id">Mostrar ID do certificado</label></th>
					<td style="padding:8px 0">
						<label>
							<input type="checkbox" id="kt_cert_show_id" name="kt_cert_show_id" value="1" <?php checked( $cert_show_id, '1' ); ?>>
							Exibir o código único no rodapé do certificado
						</label>
					</td>
				</tr>
			</table>
			<div style="margin-top:16px;display:flex;gap:12px;align-items:center;flex-wrap:wrap">
				<button type="submit" class="button button-primary">Salvar Certificado</button>
				<a href="<?php echo esc_url( add_query_arg( 'kt_cert_preview', '1', admin_url( 'admin.php?page=kt-dashboard' ) ) ); ?>" target="_blank" class="button">Pré-visualizar</a>
			</div>
		</form>
	</div>
	<?php endif; ?>
</div>
