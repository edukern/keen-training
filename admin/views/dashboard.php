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

	<!-- Páginas do Plugin -->
	<?php
	$all_pages        = get_pages( [ 'sort_column' => 'post_title', 'sort_order' => 'ASC' ] );
	$saved_portal_url = get_option( 'kt_portal_page_url',  '' );
	$saved_manager_url= get_option( 'kt_manager_page_url', '' );
	?>
	<div class="kt-settings-box" style="margin-top:32px;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;max-width:600px">
		<h2 style="margin-top:0">Páginas do Plugin</h2>
		<?php if ( isset( $_GET['pages_saved'] ) ): ?>
			<div class="notice notice-success inline" style="margin-bottom:16px"><p>Páginas salvas.</p></div>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'kt_save_pages' ); ?>
			<input type="hidden" name="action" value="kt_save_pages">
			<table class="form-table" style="margin:0">
				<tr>
					<th style="padding:8px 0;width:200px"><label for="kt_portal_page">Portal do Colaborador</label></th>
					<td style="padding:8px 0">
						<select id="kt_portal_page" name="kt_portal_page_url" style="min-width:280px">
							<option value="">— Selecione a página —</option>
							<?php foreach ( $all_pages as $p ): ?>
							<option value="<?php echo esc_attr( get_permalink( $p->ID ) ); ?>" <?php selected( $saved_portal_url, get_permalink( $p->ID ) ); ?>>
								<?php echo esc_html( $p->post_title ); ?>
							</option>
							<?php endforeach; ?>
						</select>
						<p class="description">Página com o shortcode <code>[kt_portal]</code>. Colaboradores são redirecionados aqui após o login.</p>
					</td>
				</tr>
				<tr>
					<th style="padding:8px 0"><label for="kt_manager_page">Portal do Gerente</label></th>
					<td style="padding:8px 0">
						<select id="kt_manager_page" name="kt_manager_page_url" style="min-width:280px">
							<option value="">— Selecione a página —</option>
							<?php foreach ( $all_pages as $p ): ?>
							<option value="<?php echo esc_attr( get_permalink( $p->ID ) ); ?>" <?php selected( $saved_manager_url, get_permalink( $p->ID ) ); ?>>
								<?php echo esc_html( $p->post_title ); ?>
							</option>
							<?php endforeach; ?>
						</select>
						<p class="description">Página com o shortcode <code>[kt_gerente]</code>. Gerentes de unidade são redirecionados aqui após o login.</p>
					</td>
				</tr>
			</table>
			<button type="submit" class="button button-primary" style="margin-top:16px">Salvar Páginas</button>
		</form>
	</div>

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

	// Tenta puxar o logo do tema automaticamente se ainda não foi configurado
	$theme_logo_url = '';
	$theme_logo_id  = get_theme_mod( 'custom_logo' );
	if ( $theme_logo_id ) {
		$theme_logo_url = wp_get_attachment_image_url( $theme_logo_id, 'full' ) ?: '';
	}
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
					<th style="padding:8px 0"><label for="kt_cert_logo_url">Logo</label></th>
					<td style="padding:8px 0">
						<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;max-width:440px">
							<input type="url" id="kt_cert_logo_url" name="kt_cert_logo_url"
								value="<?php echo esc_attr( $cert_logo_url ); ?>"
								style="flex:1;min-width:200px"
								placeholder="https://...">
							<button type="button" id="kt-cert-logo-select" class="button">
								Selecionar da Biblioteca
							</button>
							<?php if ( $theme_logo_url && $cert_logo_url !== $theme_logo_url ): ?>
							<button type="button" id="kt-cert-logo-theme" class="button"
								data-url="<?php echo esc_attr( $theme_logo_url ); ?>">
								Usar logo do site
							</button>
							<?php endif; ?>
						</div>
						<?php $preview_url = $cert_logo_url ?: $theme_logo_url; ?>
						<div id="kt-cert-logo-preview" style="margin-top:10px;<?php echo $preview_url ? '' : 'display:none'; ?>">
							<img id="kt-cert-logo-img" src="<?php echo esc_url( $preview_url ); ?>"
								style="max-height:52px;max-width:220px;border:1px solid #e2e8f0;border-radius:4px;padding:4px;background:#fafafa">
							<button type="button" id="kt-cert-logo-remove" class="button-link" style="margin-left:10px;color:#b91c1c;font-size:.85em">Remover</button>
						</div>
						<p class="description" style="margin-top:6px">PNG, SVG ou JPG. Se vazio, exibe o nome da empresa em texto.</p>
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
	<script>
	(function($){
		var frame;

		// Abre o media uploader
		$('#kt-cert-logo-select').on('click', function(e){
			e.preventDefault();
			if ( frame ) { frame.open(); return; }
			frame = wp.media({
				title:    'Selecionar Logo do Certificado',
				button:   { text: 'Usar esta imagem' },
				multiple: false,
				library:  { type: 'image' }
			});
			frame.on('select', function(){
				var attachment = frame.state().get('selection').first().toJSON();
				$('#kt_cert_logo_url').val( attachment.url );
				$('#kt-cert-logo-img').attr( 'src', attachment.url );
				$('#kt-cert-logo-preview').show();
			});
			frame.open();
		});

		// Usa o logo do tema
		$('#kt-cert-logo-theme').on('click', function(){
			var url = $(this).data('url');
			$('#kt_cert_logo_url').val( url );
			$('#kt-cert-logo-img').attr( 'src', url );
			$('#kt-cert-logo-preview').show();
		});

		// Remove logo
		$('#kt-cert-logo-remove').on('click', function(){
			$('#kt_cert_logo_url').val('');
			$('#kt-cert-logo-preview').hide();
		});
	})(jQuery);
	</script>
	<!-- Frases Inspiradoras -->
	<?php
	// Carrega defaults da classe frontend se a opção ainda não foi salva
	$_qt_staff_raw   = get_option( 'kt_quotes_staff' );
	$_qt_manager_raw = get_option( 'kt_quotes_manager' );

	// Se nunca salvo, usa os padrões do PHP (mesmos da classe frontend)
	$_default_staff = [
		'"Um investimento em conhecimento sempre paga os melhores juros." — Benjamin Franklin',
		'"A coisa mais linda do aprendizado é que ninguém pode tirar isso de você." — B.B. King',
		'"Nunca deixe de aprender. O dia em que você para de aprender é o dia em que começa a morrer." — Albert Einstein',
		'"É bom celebrar o sucesso, mas é mais importante aprender com o fracasso." — Bill Gates',
		'"Unir é um começo, manter-se unidos é um progresso, trabalhar juntos é o sucesso." — Henry Ford',
		'"Se você quer ir rápido, vá sozinho. Se quer ir longe, vá acompanhado." — Provérbio africano',
		'"Somos o que fazemos repetidamente. Excelência, portanto, não é um ato — é um hábito." — Aristóteles',
		'"Qualidade é fazer certo quando ninguém está olhando." — Henry Ford',
		'"Faça sempre o melhor que puder. O que você plantar agora, colherá mais tarde." — Og Mandino',
		'"Você não precisa ser grande para começar, mas precisa começar para ser grande." — Zig Ziglar',
		'"Não espere. O momento nunca será perfeito." — Napoleon Hill',
		'"A melhor maneira de encontrar a si mesmo é se perder no serviço ao próximo." — Mahatma Gandhi',
		'"Para se comunicar bem, é preciso primeiro ouvir bem." — Ernest Hemingway',
		'"Fale de forma que as pessoas queiram te ouvir. Ouça de forma que as pessoas queiram falar." — Simon Sinek',
		'"O preço da grandeza é a responsabilidade." — Winston Churchill',
		'"Aproxime-se dos seus clientes. Tão próximo que você já saiba o que eles precisam antes que eles percebam." — Steve Jobs',
		'"Faça um cliente, não uma venda." — Katherine Barchetti',
		'"O cliente mais importante é o que veio reclamar — ele te dá a chance de melhorar." — Bill Gates',
		'"Perfeição não é atingível, mas se corrermos atrás dela podemos alcançar a excelência." — Vince Lombardi',
		'"Posso aceitar o fracasso. Todo mundo fracassa em alguma coisa. Mas não consigo aceitar não tentar." — Michael Jordan',
		'"Grandes coisas nos negócios nunca são feitas por uma só pessoa. São feitas por um time." — Steve Jobs',
		'"É incrível o que você pode realizar quando não se importa com quem leva o crédito." — Harry S. Truman',
		'"A pergunta mais persistente e urgente da vida é: o que você está fazendo pelos outros?" — Martin Luther King Jr.',
		'"Estou sempre fazendo o que não consigo fazer — para aprender como fazer." — Pablo Picasso',
		'"A mente que se abre a uma nova ideia jamais voltará ao seu tamanho original." — Albert Einstein',
		'"Nossa maior glória não está em nunca cair, mas em nos levantar cada vez que caímos." — Confúcio',
		'"Amadores se concentram em destruir os outros. Profissionais se concentram em fazer com que todos melhorem." — Shane Parrish',
		'"O talento vence jogos, mas só o trabalho em equipe ganha campeonatos." — Michael Jordan',
		'"A verdadeira influência vem de agregar valor aos outros." — Adam Grant',
		'"O objetivo é conhecer e entender o cliente tão bem que o produto ou serviço se encaixe nele e se venda sozinho." — Peter Drucker',
		'"O sucesso geralmente vem para quem está ocupado demais para ficar procurando por ele." — Henry David Thoreau',
		'"Você não pode voltar atrás e mudar o começo, mas pode começar agora e mudar o final." — C.S. Lewis',
		'"Acredite que pode — e você já está na metade do caminho." — Theodore Roosevelt',
		'"Amanhã pertence às pessoas que se preparam para ele hoje." — Malcolm X',
		'"Clientes não esperam que você seja perfeito. Esperam que você resolva quando algo dá errado." — Donald Porter',
	];
	$_default_manager = [
		'"Um investimento em conhecimento sempre paga os melhores juros." — Benjamin Franklin',
		'"A coisa mais linda do aprendizado é que ninguém pode tirar isso de você." — B.B. King',
		'"Nunca deixe de aprender. O dia em que você para de aprender é o dia em que começa a morrer." — Albert Einstein',
		'"Estou sempre fazendo o que não consigo fazer — para aprender como fazer." — Pablo Picasso',
		'"É bom celebrar o sucesso, mas é mais importante aprender com o fracasso." — Bill Gates',
		'"Posso aceitar o fracasso. Todo mundo fracassa em alguma coisa. Mas não consigo aceitar não tentar." — Michael Jordan',
		'"Somos o que fazemos repetidamente. Excelência, portanto, não é um ato — é um hábito." — Aristóteles',
		'"Qualidade é fazer certo quando ninguém está olhando." — Henry Ford',
		'"Perfeição não é atingível, mas se corrermos atrás dela podemos alcançar a excelência." — Vince Lombardi',
		'"Faça sempre o melhor que puder. O que você plantar agora, colherá mais tarde." — Og Mandino',
		'"Você não precisa ser grande para começar, mas precisa começar para ser grande." — Zig Ziglar',
		'"Não espere. O momento nunca será perfeito." — Napoleon Hill',
		'"Unir é um começo, manter-se unidos é um progresso, trabalhar juntos é o sucesso." — Henry Ford',
		'"Se você quer ir rápido, vá sozinho. Se quer ir longe, vá acompanhado." — Provérbio africano',
		'"Grandes coisas nos negócios nunca são feitas por uma só pessoa. São feitas por um time." — Steve Jobs',
		'"É incrível o que você pode realizar quando não se importa com quem leva o crédito." — Harry S. Truman',
		'"A melhor maneira de encontrar a si mesmo é se perder no serviço ao próximo." — Mahatma Gandhi',
		'"A pergunta mais persistente e urgente da vida é: o que você está fazendo pelos outros?" — Martin Luther King Jr.',
		'"Para se comunicar bem, é preciso primeiro ouvir bem." — Ernest Hemingway',
		'"Fale de forma que as pessoas queiram te ouvir. Ouça de forma que as pessoas queiram falar." — Simon Sinek',
		'"Aproxime-se dos seus clientes. Tão próximo que você já saiba o que eles precisam antes que eles percebam." — Steve Jobs',
		'"Faça um cliente, não uma venda." — Katherine Barchetti',
		'"O cliente mais importante é o que veio reclamar — ele te dá a chance de melhorar." — Bill Gates',
		'"O preço da grandeza é a responsabilidade." — Winston Churchill',
		'"O exemplo não é a principal maneira de influenciar os outros. É a única." — Albert Schweitzer',
		'"Um líder é alguém que conhece o caminho, faz o caminho e mostra o caminho." — John C. Maxwell',
		'"A velocidade do líder é a velocidade do time." — Lee Iacocca',
		'"A qualidade mais importante da liderança é a integridade." — Dwight D. Eisenhower',
		'"Antes de ser líder, o sucesso é sobre crescer a si mesmo. Quando você se torna líder, o sucesso é sobre fazer os outros crescerem." — Jack Welch',
		'"Treine as pessoas bem o suficiente para que possam ir embora. Trate-as bem o suficiente para que não queiram." — Richard Branson',
		'"Um grande líder não é o que faz grandes coisas. É o que faz as pessoas ao redor fazerem grandes coisas." — Ronald Reagan',
		'"Se suas ações inspiram outros a sonhar mais, aprender mais, fazer mais e ser mais — você é um líder." — John Quincy Adams',
		'"Você não constrói um negócio. Você constrói pessoas, e as pessoas constroem o negócio." — Zig Ziglar',
		'"Cuide dos seus funcionários e eles cuidarão dos seus clientes." — Richard Branson',
		'"Liderança é sobre fazer os outros melhores como resultado da sua presença — e garantir que esse impacto dure na sua ausência." — Sheryl Sandberg',
		'"Sempre trate seus liderados exatamente como você gostaria que eles tratassem seus melhores clientes." — Stephen R. Covey',
		'"Pessoas não são apenas recursos a serem gerenciados, mas fontes de inovação, criatividade e diferenciação estratégica." — Gary Hamel',
		'"A cultura de uma organização é moldada pelo pior comportamento que o líder está disposto a tolerar." — Steve Gruenert',
		'"Critique em particular. Elogie em público." — Vince Lombardi',
		'"O melhor presente que um líder pode dar ao time é a clareza." — Patrick Lencioni',
		'"Não me diga o que é prioritário. Me mostre onde você gasta seu tempo e seu dinheiro — e eu te direi." — Jim Collins',
		'"O maior sinal de um mau líder é que ele tem sempre pressa — e nunca tem tempo." — Robert Townsend',
		'"A função do líder é produzir mais líderes — não mais seguidores." — Ralph Nader',
		'"Amadores se concentram em destruir os outros. Profissionais se concentram em fazer com que todos melhorem." — Shane Parrish',
	];

	$_qt_staff_lines   = $_qt_staff_raw   !== false ? ( json_decode( $_qt_staff_raw,   true ) ?: [] ) : $_default_staff;
	$_qt_manager_lines = $_qt_manager_raw !== false ? ( json_decode( $_qt_manager_raw, true ) ?: [] ) : $_default_manager;
	?>
	<div class="kt-settings-box" style="margin-top:24px;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;max-width:600px">
		<h2 style="margin-top:0">Frases Inspiradoras</h2>
		<p class="description" style="margin-bottom:16px">Uma frase diferente é exibida a cada dia nos portais do colaborador e do gerente. Edite o banco abaixo — <strong>uma frase por linha</strong>, no formato <code>"Texto da frase." — Autor</code>.</p>
		<?php if ( isset( $_GET['quotes_saved'] ) ): ?>
			<div class="notice notice-success inline" style="margin-bottom:16px"><p>Frases salvas com sucesso.</p></div>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'kt_save_quotes' ); ?>
			<input type="hidden" name="action" value="kt_save_quotes">
			<table class="form-table" style="margin:0">
				<tr>
					<th style="padding:8px 0;width:200px;vertical-align:top;padding-top:12px">
						<label for="kt_quotes_staff">Colaboradores</label>
						<p class="description" style="font-weight:400"><?php echo count( $_qt_staff_lines ); ?> frases</p>
					</th>
					<td style="padding:8px 0">
						<textarea id="kt_quotes_staff" name="kt_quotes_staff" rows="10" style="width:100%;font-family:monospace;font-size:.82em;line-height:1.6"><?php echo esc_textarea( implode( "\n", $_qt_staff_lines ) ); ?></textarea>
					</td>
				</tr>
				<tr>
					<th style="padding:8px 0;vertical-align:top;padding-top:12px">
						<label for="kt_quotes_manager">Gerentes</label>
						<p class="description" style="font-weight:400"><?php echo count( $_qt_manager_lines ); ?> frases</p>
					</th>
					<td style="padding:8px 0">
						<textarea id="kt_quotes_manager" name="kt_quotes_manager" rows="10" style="width:100%;font-family:monospace;font-size:.82em;line-height:1.6"><?php echo esc_textarea( implode( "\n", $_qt_manager_lines ) ); ?></textarea>
					</td>
				</tr>
			</table>
			<button type="submit" class="button button-primary" style="margin-top:16px">Salvar Frases</button>
		</form>
	</div>

	<!-- Notificações de Aniversário -->
	<?php
	$notif_email     = get_option( 'kt_notif_email',      '' );
	$notif_frequency = get_option( 'kt_notif_frequency',  '' );
	$notif_day       = absint( get_option( 'kt_notif_day', 1 ) );
	$notif_days_ahead= absint( get_option( 'kt_notif_days_ahead', 7 ) );

	$days_names = [
		0 => 'Domingo', 1 => 'Segunda-feira', 2 => 'Terça-feira',
		3 => 'Quarta-feira', 4 => 'Quinta-feira', 5 => 'Sexta-feira', 6 => 'Sábado',
	];
	?>
	<div class="kt-settings-box" style="margin-top:24px;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;max-width:600px">
		<h2 style="margin-top:0">🎂 Notificações de Aniversário</h2>
		<p class="description" style="margin-bottom:16px">
			Configure um e-mail para receber automaticamente a lista de colaboradores com
			<strong>aniversário de nascimento</strong> e <strong>aniversário de empresa</strong>
			nos próximos dias — perfeito para o time de marketing preparar as mensagens de felicitação.
		</p>
		<?php if ( isset( $_GET['notif_saved'] ) ): ?>
			<div class="notice notice-success inline" style="margin-bottom:16px"><p>✓ Configurações de notificação salvas.</p></div>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="kt-notif-form">
			<?php wp_nonce_field( 'kt_save_notifications' ); ?>
			<input type="hidden" name="action" value="kt_save_notifications">

			<table class="form-table" style="margin:0">
				<tr>
					<th style="padding:8px 0;width:200px"><label for="kt_notif_email">E-mail de destino</label></th>
					<td style="padding:8px 0">
						<input type="email" id="kt_notif_email" name="kt_notif_email"
							value="<?php echo esc_attr( $notif_email ); ?>"
							style="width:100%;max-width:340px"
							placeholder="marketing@suaempresa.com.br">
						<p class="description">Endereço que receberá o digest de datas especiais.</p>
					</td>
				</tr>
				<tr>
					<th style="padding:8px 0"><label for="kt_notif_frequency">Frequência</label></th>
					<td style="padding:8px 0">
						<select id="kt_notif_frequency" name="kt_notif_frequency"
							onchange="ktNotifToggle()" style="min-width:180px">
							<option value="" <?php selected( $notif_frequency, '' ); ?>>— Desativado —</option>
							<option value="weekly"  <?php selected( $notif_frequency, 'weekly'  ); ?>>Semanal</option>
							<option value="monthly" <?php selected( $notif_frequency, 'monthly' ); ?>>Mensal</option>
						</select>
					</td>
				</tr>
			</table>

			<!-- Opções semanais -->
			<div id="kt-notif-weekly" style="<?php echo $notif_frequency === 'weekly' ? '' : 'display:none'; ?>">
				<table class="form-table" style="margin:0">
					<tr>
						<th style="padding:8px 0;width:200px"><label for="kt_notif_day_weekly">Dia de envio</label></th>
						<td style="padding:8px 0">
							<select id="kt_notif_day_weekly" name="kt_notif_day" style="min-width:180px">
								<?php foreach ( $days_names as $val => $label ): ?>
								<option value="<?php echo esc_attr( $val ); ?>"
									<?php selected( ( $notif_frequency === 'weekly' ? $notif_day : 1 ), $val ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th style="padding:8px 0"><label for="kt_notif_days_ahead_weekly">Janela de dias</label></th>
						<td style="padding:8px 0">
							<select id="kt_notif_days_ahead_weekly" name="kt_notif_days_ahead_weekly" style="min-width:120px">
								<option value="3"  <?php selected( $notif_frequency === 'weekly' ? $notif_days_ahead : 7, 3 ); ?>>3 dias</option>
								<option value="7"  <?php selected( $notif_frequency === 'weekly' ? $notif_days_ahead : 7, 7 ); ?>>7 dias</option>
								<option value="14" <?php selected( $notif_frequency === 'weekly' ? $notif_days_ahead : 7, 14 ); ?>>14 dias</option>
							</select>
							<p class="description">Listar aniversários que ocorrem nos próximos X dias a partir da data do envio.</p>
						</td>
					</tr>
				</table>
			</div>

			<!-- Opções mensais -->
			<div id="kt-notif-monthly" style="<?php echo $notif_frequency === 'monthly' ? '' : 'display:none'; ?>">
				<table class="form-table" style="margin:0">
					<tr>
						<th style="padding:8px 0;width:200px"><label for="kt_notif_day_monthly">Dia do mês</label></th>
						<td style="padding:8px 0">
							<input type="number" id="kt_notif_day_monthly" name="kt_notif_day"
								min="1" max="28" style="width:70px"
								value="<?php echo esc_attr( $notif_frequency === 'monthly' ? $notif_day : 1 ); ?>">
							<p class="description">Dia do mês em que o e-mail será enviado (1–28).</p>
						</td>
					</tr>
					<tr>
						<th style="padding:8px 0"><label for="kt_notif_days_ahead_monthly">Janela de dias</label></th>
						<td style="padding:8px 0">
							<select id="kt_notif_days_ahead_monthly" name="kt_notif_days_ahead_monthly" style="min-width:120px">
								<option value="14" <?php selected( $notif_frequency === 'monthly' ? $notif_days_ahead : 30, 14 ); ?>>14 dias</option>
								<option value="30" <?php selected( $notif_frequency === 'monthly' ? $notif_days_ahead : 30, 30 ); ?>>30 dias</option>
								<option value="60" <?php selected( $notif_frequency === 'monthly' ? $notif_days_ahead : 30, 60 ); ?>>60 dias</option>
							</select>
							<p class="description">Listar aniversários que ocorrem nos próximos X dias a partir da data do envio.</p>
						</td>
					</tr>
				</table>
			</div>

			<div style="margin-top:16px;display:flex;gap:10px;align-items:center;flex-wrap:wrap">
				<button type="submit" class="button button-primary">Salvar Notificações</button>
				<label style="display:flex;align-items:center;gap:6px;font-size:.9em;color:#475569">
					<input type="checkbox" name="kt_notif_test_send" value="1">
					Enviar e-mail de teste agora
				</label>
			</div>
			<?php if ( $notif_frequency && $notif_email ): ?>
			<p class="description" style="margin-top:10px">
				📅 Próximo envio configurado:
				<strong>
				<?php
				if ( $notif_frequency === 'weekly' ) {
					echo esc_html( 'toda ' . $days_names[ $notif_day ] );
				} else {
					echo esc_html( 'todo dia ' . $notif_day . ' do mês' );
				}
				?>
				</strong> → <em><?php echo esc_html( $notif_email ); ?></em>
			</p>
			<?php endif; ?>
		</form>
	</div>

	<script>
	function ktNotifToggle() {
		var freq = document.getElementById('kt_notif_frequency').value;
		document.getElementById('kt-notif-weekly').style.display  = freq === 'weekly'  ? '' : 'none';
		document.getElementById('kt-notif-monthly').style.display = freq === 'monthly' ? '' : 'none';
		// Garante que apenas um campo 'kt_notif_day' seja submetido
		document.getElementById('kt_notif_day_weekly').disabled  = freq !== 'weekly';
		document.getElementById('kt_notif_day_monthly').disabled = freq !== 'monthly';
	}
	// Inicializa estado correto dos campos disabled ao carregar
	(function(){ ktNotifToggle(); })();
	</script>

	<?php endif; ?>
</div>
