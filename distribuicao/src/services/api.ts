/**
 * Camada de dados — todos os fetches do sistema passam por aqui.
 *
 * PARA O DEV: cada função retorna uma Promise com dados tipados.
 * Hoje usam dados mock. Para conectar ao ERP basta substituir
 * o corpo de cada função por um fetch real — os componentes não mudam.
 *
 * Exemplo de troca:
 *   ANTES: return Promise.resolve(MOCK_PRODUCTS);
 *   DEPOIS: const r = await fetch('/api/produtos'); return r.json();
 */

// ─── Tipos ────────────────────────────────────────────────────────────────────

export interface Product {
  id: string;
  code: string;           // SKU raiz (ex: "CAM-2024-001")
  name: string;
  category: string;       // Tipo de produto (ex: "CAMISA")
  collection: string;
  color: string;
  sizes: string[];
  // Campos opcionais vindos do ERP
  brand?: string;
  gender?: string;
  ageClass?: string;      // classe: PP, BB, INF, JUV, AD, EX
}

export interface SkuInfo {
  sku: string;            // SKU completo com tamanho (ex: "CAM-001-M")
  name: string;
  category: string;
}

export interface SizeRule {
  size: string;
  initialSupply: number;
  idealMinimum: number;
  criticalLevel: number;
  enabled: boolean;
}

export interface ProductRules {
  productId: string;
  rules: SizeRule[];
  storeRules?: Record<string, SizeRule[]>; // por loja, quando grade individual
  sameGradeForAll: boolean;
  savedBy: string;
  savedAt: string;        // ISO date
  version: number;
}

export interface SegmentKey {
  tipo: string;
  classe: string;
  genero: string;
  marca?: string;
  colecao?: string;
}

export interface SegmentRules {
  segmentKey: SegmentKey;
  rules: SizeRule[];
  savedBy: string;
  savedAt: string;
  version: number;
}

export interface StockEntry {
  productId: string;
  code: string;
  name: string;
  category: string;
  brand: string;
  collection: string;
  sizes: string[];
  stock: Record<string, Record<string, number>>; // storeId → size → qty
}

export interface CorridorSlot {
  corridor: number;
  column: string;
  row: number;
  address: string;
  productCode: string;
  productName: string;
  size: string;
  replenishments: { store: string; qty: number }[];
}

export interface PromocaoItem {
  id: string;
  sku: string;
  name: string;
  category: string;
  dataLoja: string;       // YYYY-MM-DD
  loja: boolean;
  sistema: boolean;
}

// ─── Mock data ────────────────────────────────────────────────────────────────
// Fonte única de verdade para o protótipo.
// O dev substitui cada bloco pelo fetch correspondente do ERP.

const MOCK_PRODUCTS: Product[] = [
  { id: "p1", code: "CAM-2024-001", name: "Camisa Social Slim Fit",     category: "CAMISA",     collection: "Verão 2025",     color: "Branco / Azul", sizes: ["PP","P","M","G","GG","XGG"] },
  { id: "p2", code: "CAL-2024-032", name: "Calça Jeans Skinny",         category: "CALCA JE",   collection: "Verão 2025",     color: "Azul Indigo",   sizes: ["36","38","40","42","44","46","48"] },
  { id: "p3", code: "VES-2024-015", name: "Vestido Floral Midi",         category: "VESTIDO",    collection: "Primavera 2025", color: "Estampado",     sizes: ["P","M","G","GG"] },
  { id: "p4", code: "INF-2024-088", name: "Conjunto Infantil Moletom",  category: "CONJUNTO",   collection: "Inverno 2025",   color: "Cinza / Rosa",  sizes: ["2","4","6","8","10","12","14"] },
  { id: "p5", code: "BLA-2024-007", name: "Blusa Tricô Texturizado",    category: "BLUSA",      collection: "Inverno 2025",   color: "Caramelo",      sizes: ["PP","P","M","G","GG"] },
  { id: "p6", code: "CAL-2024-033", name: "Calça Jeans Skinny FEM",     category: "CALCA JE",   collection: "Verão 2025",     color: "Azul Claro",    sizes: ["34","36","38","40","42","44"] },
  { id: "p7", code: "CAM-2024-002", name: "Camisa Casual Linho",        category: "CAMISA",     collection: "Verão 2025",     color: "Bege",          sizes: ["P","M","G","GG"] },
  { id: "p8", code: "BER-2024-021", name: "Bermuda Jeans",              category: "BERMUDA JE", collection: "Verão 2025",     color: "Azul Médio",    sizes: ["38","40","42","44","46"] },
  { id: "p9", code: "MAC-2024-003", name: "Macacão Linho FEM",          category: "MACACAO",    collection: "Verão 2025",     color: "Off White",     sizes: ["P","M","G"] },
  { id: "p10",code: "SHO-2024-014", name: "Short Alfaiataria FEM",      category: "SHORT",      collection: "Verão 2025",     color: "Preto",         sizes: ["34","36","38","40","42"] },
];

// Catálogo de SKUs individuais (produto + tamanho).
// No ERP isso vira um endpoint de busca por SKU.
const MOCK_SKU_CATALOG: Record<string, SkuInfo> = (() => {
  const catalog: Record<string, SkuInfo> = {};
  for (const p of MOCK_PRODUCTS) {
    for (const size of p.sizes) {
      // Convenção de SKU: CODE-SIZE (ex: "CAM-2024-001-M")
      const sku = `${p.code}-${size}`.toUpperCase();
      catalog[sku] = { sku, name: p.name, category: p.category };
    }
    // Compatibilidade com SKUs curtos usados no PromocaoView demo
    const shortCode = p.code.replace("-2024-", "-").replace("-2024", "");
    for (const size of p.sizes) {
      const sku = `${shortCode}-${size}`.toUpperCase();
      if (!catalog[sku]) catalog[sku] = { sku, name: p.name, category: p.category };
    }
  }
  return catalog;
})();

const MOCK_PRODUCT_RULES: Record<string, ProductRules> = {
  "p1": {
    productId: "p1",
    sameGradeForAll: true,
    rules: [
      { size: "PP",  initialSupply: 2, idealMinimum: 1, criticalLevel: 0, enabled: true },
      { size: "P",   initialSupply: 4, idealMinimum: 2, criticalLevel: 1, enabled: true },
      { size: "M",   initialSupply: 6, idealMinimum: 3, criticalLevel: 1, enabled: true },
      { size: "G",   initialSupply: 5, idealMinimum: 2, criticalLevel: 1, enabled: true },
      { size: "GG",  initialSupply: 3, idealMinimum: 1, criticalLevel: 0, enabled: true },
      { size: "XGG", initialSupply: 1, idealMinimum: 1, criticalLevel: 0, enabled: false },
    ],
    savedBy: "Ana Souza",
    savedAt: "2025-10-14T09:30:00",
    version: 3,
  },
};

const MOCK_PROMOCAO_ITEMS: PromocaoItem[] = [
  { id: "d1",  sku: "CAL-032-36", name: "Calça Jeans Skinny AD MASC",     category: "CALCA JE",   dataLoja: "2026-05-10", loja: true,  sistema: true  },
  { id: "d2",  sku: "CAL-032-38", name: "Calça Jeans Skinny AD MASC",     category: "CALCA JE",   dataLoja: "2026-05-10", loja: true,  sistema: true  },
  { id: "d3",  sku: "CAL-033-34", name: "Calça Jeans Skinny AD FEM",      category: "CALCA JE",   dataLoja: "2026-05-10", loja: true,  sistema: false },
  { id: "d4",  sku: "CAL-033-38", name: "Calça Jeans Skinny AD FEM",      category: "CALCA JE",   dataLoja: "2026-05-10", loja: false, sistema: false },
  { id: "d5",  sku: "CAM-001-M",  name: "Camisa Social Slim AD MASC",     category: "CAMISA",     dataLoja: "2026-05-12", loja: true,  sistema: true  },
  { id: "d6",  sku: "CAM-001-G",  name: "Camisa Social Slim AD MASC",     category: "CAMISA",     dataLoja: "2026-05-12", loja: false, sistema: false },
  { id: "d7",  sku: "BLA-007-P",  name: "Blusa Tricô Texturizado AD FEM", category: "BLUSA",      dataLoja: "2026-05-15", loja: false, sistema: false },
  { id: "d8",  sku: "BLA-007-M",  name: "Blusa Tricô Texturizado AD FEM", category: "BLUSA",      dataLoja: "2026-05-15", loja: false, sistema: false },
  { id: "d9",  sku: "VES-015-M",  name: "Vestido Casual Floral AD FEM",   category: "VESTIDO",    dataLoja: "2026-05-17", loja: true,  sistema: true  },
  { id: "d10", sku: "VES-015-G",  name: "Vestido Casual Floral AD FEM",   category: "VESTIDO",    dataLoja: "2026-05-17", loja: true,  sistema: false },
  { id: "d11", sku: "BER-021-40", name: "Bermuda Jeans AD MASC",          category: "BERMUDA JE", dataLoja: "2026-05-20", loja: false, sistema: false },
  { id: "d12", sku: "SHO-014-36", name: "Short Alfaiataria AD FEM",       category: "SHORT",      dataLoja: "2026-05-20", loja: false, sistema: false },
];

// ─── API — Produtos ───────────────────────────────────────────────────────────

/** Lista todos os produtos. ERP: GET /produtos */
export async function getProducts(): Promise<Product[]> {
  return Promise.resolve(MOCK_PRODUCTS);
}

/** Busca um produto pelo ID. ERP: GET /produtos/:id */
export async function getProductById(id: string): Promise<Product | null> {
  return Promise.resolve(MOCK_PRODUCTS.find(p => p.id === id) ?? null);
}

/**
 * Resolve SKUs individuais para nome e categoria.
 * Usado pelo PromocaoView na importação.
 * ERP: POST /produtos/resolve-skus  body: { skus: string[] }
 */
export async function resolveSkus(skus: string[]): Promise<SkuInfo[]> {
  return Promise.resolve(
    skus.map(sku => {
      const upper = sku.trim().toUpperCase();
      return MOCK_SKU_CATALOG[upper] ?? { sku, name: "SKU não encontrado", category: "" };
    })
  );
}

// ─── API — Regras de produto ──────────────────────────────────────────────────

/** Retorna as regras salvas de um produto. ERP: GET /regras/produto/:productId */
export async function getProductRules(productId: string): Promise<ProductRules | null> {
  return Promise.resolve(MOCK_PRODUCT_RULES[productId] ?? null);
}

/** Salva as regras de um produto. ERP: PUT /regras/produto/:productId */
export async function saveProductRules(rules: ProductRules): Promise<void> {
  MOCK_PRODUCT_RULES[rules.productId] = rules; // mock: salva em memória
  return Promise.resolve();
}

// ─── API — Regras de segmento ─────────────────────────────────────────────────

/** Busca regras de um segmento. ERP: GET /regras/segmento?tipo=X&classe=Y&genero=Z */
export async function getSegmentRules(_key: SegmentKey): Promise<SegmentRules | null> {
  // Mock: retorna null (sem regras salvas). O componente trata isso como "novo segmento".
  return Promise.resolve(null);
}

/** Salva regras de um segmento. ERP: PUT /regras/segmento */
export async function saveSegmentRules(_rules: SegmentRules): Promise<void> {
  return Promise.resolve();
}

// ─── API — Estoque por segmento ───────────────────────────────────────────────

/**
 * Retorna estoque filtrado por segmento.
 * ERP: GET /estoque?tipo=X&classe=Y&genero=Z&marca=W&colecao=V
 * Os dados de estoque por loja/tamanho vêm do ERP em tempo real.
 */
export async function getStockBySegment(_filters: Partial<SegmentKey>): Promise<StockEntry[]> {
  // Mock gerado deterministicamente — o componente SegmentView usa seus próprios dados por ora
  return Promise.resolve([]);
}

// ─── API — Relatório de corredor ──────────────────────────────────────────────

/**
 * Retorna os slots de corredor com reposições pendentes.
 * ERP: GET /corredor/reposicoes?loja=TC
 * Inclui o mapeamento físico (corredor, coluna, prateleira) do ERP/WMS.
 */
export async function getCorridorReplenishments(_storeFilter?: string): Promise<CorridorSlot[]> {
  return Promise.resolve([]);
}

// ─── API — Promoção ───────────────────────────────────────────────────────────

/** Lista itens em promoção. ERP: GET /promocao/itens */
export async function getPromocaoItems(): Promise<PromocaoItem[]> {
  return Promise.resolve(MOCK_PROMOCAO_ITEMS);
}

/** Atualiza um item de promoção. ERP: PATCH /promocao/itens/:id */
export async function updatePromocaoItem(id: string, patch: Partial<PromocaoItem>): Promise<void> {
  const item = MOCK_PROMOCAO_ITEMS.find(i => i.id === id);
  if (item) Object.assign(item, patch);
  return Promise.resolve();
}

/** Remove um item de promoção. ERP: DELETE /promocao/itens/:id */
export async function removePromocaoItem(id: string): Promise<void> {
  const idx = MOCK_PROMOCAO_ITEMS.findIndex(i => i.id === id);
  if (idx !== -1) MOCK_PROMOCAO_ITEMS.splice(idx, 1);
  return Promise.resolve();
}

/** Adiciona itens de promoção (importação em lote). ERP: POST /promocao/itens/batch */
export async function importPromocaoItems(items: Omit<PromocaoItem, "id">[]): Promise<PromocaoItem[]> {
  const created = items.map((item, i) => ({
    ...item,
    id: `imp-${Date.now()}-${i}`,
  }));
  MOCK_PROMOCAO_ITEMS.push(...created);
  return Promise.resolve(created);
}
