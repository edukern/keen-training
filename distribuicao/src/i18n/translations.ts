export type Lang = "pt" | "en";

export interface Translations {
  // Sidebar
  nav_cd: string;
  nav_title: string;
  nav_produto: string;
  nav_segmento: string;
  nav_promocao: string;
  nav_cadastro: string;
  nav_reposicao: string;
  nav_user: string;
  nav_prototype: string;
  nav_prototype_desc: string;

  // Common actions
  save: string;
  saved: string;
  discard: string;
  cancel: string;
  apply: string;
  load: string;
  search: string;
  restore: string;
  copy_from_ref: string;
  import_skus: string;
  clear_filters: string;
  scan_code: string;
  scanning: string;

  // Common labels
  how_it_works: string;
  same_grade_all_stores: string;
  individual_grade: string;
  store: string;
  history: string;
  current_version: string;
  version: string;
  errors: string;
  no_history: string;

  // Table headers
  size_rule_header: string;
  initial_supply: string;
  initial_supply_sub: string;
  ideal_minimum: string;
  ideal_minimum_sub: string;
  ideal_minimum_sub_segment: string;
  critical_level: string;
  critical_level_sub: string;
  error_critical_msg: string;
  pieces_initial: string;
  active_sizes: string;

  // Product > Cadastro
  prod_cad_step1_title: string;
  prod_cad_step1_desc: string;
  prod_cad_step2_title: string;
  prod_cad_step2_desc: string;
  prod_cad_step3_title: string;
  prod_cad_step3_desc: string;
  prod_cad_search_placeholder: string;
  prod_cad_rules_tab: string;
  prod_cad_copy_modal_title: string;
  prod_cad_copy_modal_desc: string;
  prod_cad_copy_no_refs: string;
  prod_cad_saved_rules_badge: string;
  prod_cad_no_product: string;

  // Product > Reposição (Corridor)
  prod_rep_step1_title: string;
  prod_rep_step1_desc: string;
  prod_rep_step2_title: string;
  prod_rep_step2_desc: string;
  prod_rep_step3_title: string;
  prod_rep_step3_desc: string;
  corridor: string;
  corridor_general: string;
  no_replenishments: string;

  // Produto > Cadastro
  prod_cad_permanent: string;
  prod_cad_location: string;
  prod_cad_new: string;

  // Segmento > Cadastro
  seg_cad_step1_title: string;
  seg_cad_step1_desc: string;
  seg_cad_step2_title: string;
  seg_cad_step2_desc: string;
  seg_cad_step3_title: string;
  seg_cad_step3_desc: string;
  seg_tipo: string;
  seg_classe: string;
  seg_genero: string;
  seg_marca: string;
  seg_colecao: string;
  seg_all_brands: string;
  seg_all_collections: string;
  seg_select_tipo: string;
  seg_select_classe: string;
  seg_select_genero: string;
  seg_load_grade: string;
  seg_select_hint: string;
  seg_existing: string;
  seg_new: string;
  seg_empty_state: string;

  // Segmento > Reposição
  seg_rep_step1_title: string;
  seg_rep_step1_desc: string;
  seg_rep_step2_title: string;
  seg_rep_step2_desc: string;
  seg_rep_step3_title: string;
  seg_rep_step3_desc: string;
  seg_rep_grade: string;
  seg_rep_total: string;
  seg_rep_legend: string;
  seg_rep_legend_critical: string;
  seg_rep_empty_title: string;
  seg_rep_empty_desc: string;

  // Promoção > Cadastro
  prom_cad_step1_title: string;
  prom_cad_step1_desc: string;
  prom_cad_step2_title: string;
  prom_cad_step2_desc: string;
  prom_cad_step3_title: string;
  prom_cad_step3_desc: string;
  prom_sku: string;
  prom_product: string;
  prom_category: string;
  prom_date_store: string;
  prom_store_check: string;
  prom_system_check: string;
  prom_import_title: string;
  prom_import_hint: string;
  prom_import_placeholder: string;
  prom_no_items: string;
  prom_define_rules: string;
  prom_rules_saved: string;
  prom_rules_modal_title: string;

  // Promoção > Reposição
  prom_rep_step1_title: string;
  prom_rep_step1_desc: string;
  prom_rep_step2_title: string;
  prom_rep_step2_desc: string;
  prom_rep_step3_title: string;
  prom_rep_step3_desc: string;
  prom_rep_total: string;
  prom_rep_by_store: string;
  prom_rep_pieces: string;
  prom_rep_grand_total: string;
  prom_rep_empty: string;

  // Produto > Reposição (Corridor UI labels)
  prod_rep_view: string;
  prod_rep_select_corridor: string;
  prod_rep_close_corridor: string;
  prod_rep_select_column: string;
}

export const PT: Translations = {
  nav_cd: "CD",
  nav_title: "Solução Logística",
  nav_produto: "Produto",
  nav_segmento: "Segmento",
  nav_promocao: "Promoção",
  nav_cadastro: "Cadastro",
  nav_reposicao: "Reposição",
  nav_user: "Ana Souza",
  nav_prototype: "Protótipo",
  nav_prototype_desc: "Todos os dados exibidos são fictícios e para fins de demonstração.",

  save: "Salvar",
  saved: "Salvo",
  discard: "Descartar",
  cancel: "Cancelar",
  apply: "Aplicar",
  load: "Carregar grade",
  search: "Buscar",
  restore: "Restaurar",
  copy_from_ref: "Copiar de outra ref.",
  import_skus: "Importar SKUs",
  clear_filters: "Limpar filtros",
  scan_code: "Escanear código",
  scanning: "Escaneando...",

  how_it_works: "Como funciona",
  same_grade_all_stores: "Grade igual para todas as lojas",
  individual_grade: "Grade individual por loja",
  store: "Loja",
  history: "Histórico",
  current_version: "Versão atual",
  version: "Versão",
  errors: "Erros",
  no_history: "Nenhum histórico para esta referência.",

  size_rule_header: "Regras de grade",
  initial_supply: "Abast. Inicial",
  initial_supply_sub: "ao entrar na loja",
  ideal_minimum: "Mínimo Ideal",
  ideal_minimum_sub: "manutenção",
  ideal_minimum_sub_segment: "estoque de manutenção",
  critical_level: "Nível Crítico",
  critical_level_sub: "aciona reposição",
  error_critical_msg: "O Nível Crítico deve ser menor que o Mínimo Ideal. Corrija os tamanhos sinalizados para salvar.",
  pieces_initial: "Peças no inicial",
  active_sizes: "Tamanhos ativos",

  prod_cad_step1_title: "Buscar ou escanear produto",
  prod_cad_step1_desc: "Digite o SKU no campo de busca ou use o botão para escanear o código de barras com a câmera.",
  prod_cad_step2_title: "Definir a grade de reposição",
  prod_cad_step2_desc: "Para cada tamanho, informe o abastecimento inicial, o mínimo ideal e o nível crítico que aciona a reposição.",
  prod_cad_step3_title: "Ajustar por loja (opcional)",
  prod_cad_step3_desc: "Por padrão a grade se aplica a todas as lojas. Desmarque Grade igual para todas as lojas para definir valores individuais por loja.",
  prod_cad_permanent: "Permanente",
  prod_cad_location: "Localização",
  prod_cad_new: "Cadastrar novo produto",
  prod_cad_search_placeholder: "Buscar SKU manualmente...",
  prod_cad_rules_tab: "Regras de grade",
  prod_cad_copy_modal_title: "Copiar regras de outra referência",
  prod_cad_copy_modal_desc: "As regras serão copiadas para os tamanhos correspondentes.",
  prod_cad_copy_no_refs: "Nenhuma referência com regras disponível.",
  prod_cad_saved_rules_badge: "Regras salvas",
  prod_cad_no_product: "Busque ou escaneie um produto para começar.",

  prod_rep_step1_title: "Escolher loja ou visão geral",
  prod_rep_step1_desc: "Selecione uma loja específica (TC, NH, IG, OS) para ver só as reposições dela, ou mantenha Geral para ver todas.",
  prod_rep_step2_title: "Navegar pelos corredores",
  prod_rep_step2_desc: "Cada corredor lista os endereços com reposição pendente. Clique para expandir e ver os detalhes por produto e tamanho.",
  prod_rep_step3_title: "Executar a reposição",
  prod_rep_step3_desc: "Use o endereço para localizar o ponto no armazém e repor a quantidade indicada em cada loja.",
  corridor: "Corredor",
  corridor_general: "Geral",
  no_replenishments: "Nenhuma reposição pendente.",

  seg_cad_step1_title: "Selecionar o segmento",
  seg_cad_step1_desc: "Escolha tipo de produto, classe e gênero para identificar o segmento. Marca e coleção são filtros opcionais.",
  seg_cad_step2_title: "Configurar a grade",
  seg_cad_step2_desc: "Para cada tamanho do segmento, defina o mínimo ideal e o nível crítico. Por padrão a grade vale para todas as lojas — desmarque para ajustar individualmente.",
  seg_cad_step3_title: "Salvar e reutilizar",
  seg_cad_step3_desc: "As regras salvas são aplicadas a todos os produtos deste segmento. Produtos com cadastro individual têm prioridade sobre as regras de segmento.",
  seg_tipo: "Tipo de produto",
  seg_classe: "Classificação",
  seg_genero: "Classe",
  seg_marca: "Marca",
  seg_colecao: "Coleção",
  seg_all_brands: "Todas as marcas",
  seg_all_collections: "Todas as coleções",
  seg_select_tipo: "Selecionar tipo",
  seg_select_classe: "Selecionar classificação",
  seg_select_genero: "Selecionar classe",
  seg_load_grade: "Carregar grade",
  seg_select_hint: "Selecione tipo, classe e gênero para carregar a grade.",
  seg_existing: "Regras existentes — editando",
  seg_new: "Novo segmento — sem regras salvas",
  seg_empty_state: "Selecione tipo, classe e gênero para definir os mínimos do segmento.",

  seg_rep_step1_title: "Segmentos cadastrados",
  seg_rep_step1_desc: "Cada card representa um segmento com regras definidas na aba Cadastro. Novos segmentos aparecem aqui automaticamente.",
  seg_rep_step2_title: "Necessidade por loja",
  seg_rep_step2_desc: "Cada loja mostra quantas peças precisam ser repostas para atingir o mínimo ideal. Vermelho indica nível crítico.",
  seg_rep_step3_title: "Expandir para ver detalhes",
  seg_rep_step3_desc: "Clique no card para ver o detalhamento por tamanho e loja, com o estoque atual e a quantidade a enviar.",
  seg_rep_grade: "Grade:",
  seg_rep_total: "total",
  seg_rep_legend: "Estoque atual em cinza · Quantidade a repor em destaque",
  seg_rep_legend_critical: "Vermelho = nível crítico",
  seg_rep_empty_title: "Nenhum segmento cadastrado ainda.",
  seg_rep_empty_desc: "Acesse Segmento → Cadastro para criar regras por segmento.",

  prom_cad_step1_title: "Importar SKUs",
  prom_cad_step1_desc: "Clique em Importar SKUs e cole os códigos dos produtos em promoção — um por linha ou separados por vírgula.",
  prom_cad_step2_title: "Definir data e confirmar envio",
  prom_cad_step2_desc: "Defina a data prevista para cada SKU. Marque Loja quando o produto chegar fisicamente e Sistema quando for lançado no ERP.",
  prom_cad_step3_title: "Acompanhar conclusão",
  prom_cad_step3_desc: "Itens com ambos os checks marcados descem para o final da lista. Os pendentes ficam sempre no topo.",
  prom_sku: "SKU",
  prom_product: "Produto",
  prom_category: "Categoria",
  prom_date_store: "Data / Loja",
  prom_store_check: "Loja",
  prom_system_check: "Sistema",
  prom_import_title: "Importar SKUs",
  prom_import_hint: "Cole os SKUs dos produtos em promoção, um por linha ou separados por vírgula.",
  prom_import_placeholder: "CAL-032-36\nCAM-001-M\nVES-015-G",
  prom_no_items: "Nenhum item em promoção. Importe SKUs para começar.",
  prom_define_rules: "Definir regras",
  prom_rules_saved: "Regras salvas",
  prom_rules_modal_title: "Regras de reposição",

  prom_rep_step1_title: "Gerado automaticamente",
  prom_rep_step1_desc: "Os SKUs em promoção cadastrados na aba Cadastro aparecem aqui agrupados por produto, com as quantidades a repor por loja.",
  prom_rep_step2_title: "Expandir por produto",
  prom_rep_step2_desc: "Clique em cada produto para ver o detalhamento por SKU e por loja.",
  prom_rep_step3_title: "Total por loja",
  prom_rep_step3_desc: "Ao final de cada grupo, o total de peças a enviar para cada loja é calculado automaticamente.",
  prom_rep_total: "Total",
  prom_rep_by_store: "Reposição total por loja",
  prom_rep_pieces: "peças",
  prom_rep_grand_total: "Total geral",
  prom_rep_empty: "Nenhum item em promoção cadastrado. Acesse Promoção → Cadastro para adicionar.",

  prod_rep_view: "Visão",
  prod_rep_select_corridor: "Selecionar corredor",
  prod_rep_close_corridor: "Fechar corredor",
  prod_rep_select_column: "Selecionar coluna",
};

export const EN: Translations = {
  nav_cd: "DC",
  nav_title: "Logistics Solution",
  nav_produto: "Product",
  nav_segmento: "Segment",
  nav_promocao: "Promotion",
  nav_cadastro: "Setup",
  nav_reposicao: "Replenishment",
  nav_user: "Ana Souza",
  nav_prototype: "Prototype",
  nav_prototype_desc: "All data shown is fictional and for demonstration purposes only.",

  save: "Save",
  saved: "Saved",
  discard: "Discard",
  cancel: "Cancel",
  apply: "Apply",
  load: "Load grade",
  search: "Search",
  restore: "Restore",
  copy_from_ref: "Copy from another ref.",
  import_skus: "Import SKUs",
  clear_filters: "Clear filters",
  scan_code: "Scan barcode",
  scanning: "Scanning...",

  how_it_works: "How it works",
  same_grade_all_stores: "Same grade for all stores",
  individual_grade: "Individual grade per store",
  store: "Store",
  history: "History",
  current_version: "Current version",
  version: "Version",
  errors: "Errors",
  no_history: "No history for this reference.",

  size_rule_header: "Grade rules",
  initial_supply: "Initial Supply",
  initial_supply_sub: "on store entry",
  ideal_minimum: "Ideal Minimum",
  ideal_minimum_sub: "maintenance",
  ideal_minimum_sub_segment: "maintenance stock",
  critical_level: "Critical Level",
  critical_level_sub: "triggers replenishment",
  error_critical_msg: "Critical Level must be less than Ideal Minimum. Fix the highlighted sizes to save.",
  pieces_initial: "Initial pieces",
  active_sizes: "Active sizes",

  prod_cad_step1_title: "Search or scan product",
  prod_cad_step1_desc: "Type the SKU in the search field or use the button to scan the barcode with the camera.",
  prod_cad_step2_title: "Define the replenishment grade",
  prod_cad_step2_desc: "For each size, enter the initial supply, ideal minimum, and critical level that triggers replenishment.",
  prod_cad_step3_title: "Adjust per store (optional)",
  prod_cad_step3_desc: "By default the grade applies to all stores. Uncheck Same grade for all stores to define individual values per store.",
  prod_cad_permanent: "Permanent",
  prod_cad_location: "Location",
  prod_cad_new: "Register new product",
  prod_cad_search_placeholder: "Search SKU manually...",
  prod_cad_rules_tab: "Grade rules",
  prod_cad_copy_modal_title: "Copy rules from another reference",
  prod_cad_copy_modal_desc: "Rules will be copied to matching sizes.",
  prod_cad_copy_no_refs: "No references with saved rules available.",
  prod_cad_saved_rules_badge: "Rules saved",
  prod_cad_no_product: "Search or scan a product to get started.",

  prod_rep_step1_title: "Choose store or general view",
  prod_rep_step1_desc: "Select a specific store (TC, NH, IG, OS) to see only its replenishments, or keep General to see all.",
  prod_rep_step2_title: "Browse corridors",
  prod_rep_step2_desc: "Each corridor lists addresses with pending replenishment. Click to expand and see details by product and size.",
  prod_rep_step3_title: "Execute replenishment",
  prod_rep_step3_desc: "Use the address to locate the spot in the warehouse and replenish the indicated quantity for each store.",
  corridor: "Corridor",
  corridor_general: "General",
  no_replenishments: "No pending replenishments.",

  seg_cad_step1_title: "Select the segment",
  seg_cad_step1_desc: "Choose product type, class and gender to identify the segment. Brand and collection are optional filters.",
  seg_cad_step2_title: "Configure the grade",
  seg_cad_step2_desc: "For each size in the segment, define the ideal minimum and critical level. By default the grade applies to all stores — uncheck to adjust individually.",
  seg_cad_step3_title: "Save and reuse",
  seg_cad_step3_desc: "Saved rules apply to all products in this segment. Products with individual setup take priority over segment rules.",
  seg_tipo: "Product type",
  seg_classe: "Classification",
  seg_genero: "Class",
  seg_marca: "Brand",
  seg_colecao: "Collection",
  seg_all_brands: "All brands",
  seg_all_collections: "All collections",
  seg_select_tipo: "Select type",
  seg_select_classe: "Select classification",
  seg_select_genero: "Select class",
  seg_load_grade: "Load grade",
  seg_select_hint: "Select type, class and gender to load the grade.",
  seg_existing: "Existing rules — editing",
  seg_new: "New segment — no saved rules",
  seg_empty_state: "Select type, class and gender to define segment minimums.",

  seg_rep_step1_title: "Registered segments",
  seg_rep_step1_desc: "Each card represents a segment with rules defined in the Setup tab. New segments appear here automatically.",
  seg_rep_step2_title: "Replenishment need per store",
  seg_rep_step2_desc: "Each store shows how many pieces need to be replenished to reach the ideal minimum. Red indicates critical level.",
  seg_rep_step3_title: "Expand for details",
  seg_rep_step3_desc: "Click a card to see the breakdown by size and store, with current stock and quantity to send.",
  seg_rep_grade: "Grade:",
  seg_rep_total: "total",
  seg_rep_legend: "Current stock in grey · Quantity to replenish highlighted",
  seg_rep_legend_critical: "Red = critical level",
  seg_rep_empty_title: "No segments registered yet.",
  seg_rep_empty_desc: "Go to Segment → Setup to create segment rules.",

  prom_cad_step1_title: "Import SKUs",
  prom_cad_step1_desc: "Click Import SKUs and paste the codes of promotional products — one per line or comma-separated.",
  prom_cad_step2_title: "Set date and confirm receipt",
  prom_cad_step2_desc: "Set the expected date for each SKU. Check Store when the product arrives physically and System when it is registered in the ERP.",
  prom_cad_step3_title: "Track completion",
  prom_cad_step3_desc: "Items with both boxes checked move to the bottom of the list. Pending ones stay at the top.",
  prom_sku: "SKU",
  prom_product: "Product",
  prom_category: "Category",
  prom_date_store: "Date / Store",
  prom_store_check: "Store",
  prom_system_check: "System",
  prom_import_title: "Import SKUs",
  prom_import_hint: "Paste the SKUs of promotional products, one per line or comma-separated.",
  prom_import_placeholder: "CAL-032-36\nCAM-001-M\nVES-015-G",
  prom_no_items: "No promotional items. Import SKUs to get started.",
  prom_define_rules: "Define rules",
  prom_rules_saved: "Rules saved",
  prom_rules_modal_title: "Replenishment rules",

  prom_rep_step1_title: "Auto-generated",
  prom_rep_step1_desc: "Promotional SKUs registered in the Setup tab appear here grouped by product, with quantities to replenish per store.",
  prom_rep_step2_title: "Expand by product",
  prom_rep_step2_desc: "Click each product to see the breakdown by SKU and store.",
  prom_rep_step3_title: "Total per store",
  prom_rep_step3_desc: "At the end of each group, the total pieces to send to each store is calculated automatically.",
  prom_rep_total: "Total",
  prom_rep_by_store: "Total replenishment by store",
  prom_rep_pieces: "pieces",
  prom_rep_grand_total: "Grand total",
  prom_rep_empty: "No promotional items registered. Go to Promotion → Setup to add.",

  prod_rep_view: "View",
  prod_rep_select_corridor: "Select corridor",
  prod_rep_close_corridor: "Close corridor",
  prod_rep_select_column: "Select column",
};
