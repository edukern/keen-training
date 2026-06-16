import { useState, useCallback } from "react";
import React from "react";
import { NumberInput } from "./NumberInput";
import { useLanguage } from "../../i18n/LanguageContext";
import {
  Search,
  Save,
  RotateCcw,
  Copy,
  AlertTriangle,
  CheckCircle2,
  X,
  History,
  ScanLine,
  Plus,
} from "lucide-react";

const STORES = ["TC", "NH", "IG", "OS"] as const;
type StoreId = typeof STORES[number];

const STORE_NAMES: Record<StoreId, string> = {
  TC: "Três Coroas",
  NH: "Nova Hartz",
  IG: "Igrejinha",
  OS: "Osório",
};

// ─── Types & Mock Data ────────────────────────────────────────────────────────

interface ProductReference {
  id: string;
  code: string;
  name: string;
  category: string;
  collection: string;
  color: string;
  sizes: string[];
  // Em produção: api.getProductLocation(code) → string
  location: string;
}

interface SizeRule {
  size: string;
  initialSupply: number;
  idealMinimum: number;
  criticalLevel: number;
  enabled: boolean;
}

interface SavedRule {
  productId: string;
  rules: SizeRule[];
  createdBy: string;
  version: number;
}

const MOCK_PRODUCTS: ProductReference[] = [
  {
    id: "p1",
    code: "CAM-2024-001",
    name: "Camisa Social Slim Fit",
    category: "Camisas",
    collection: "Verão 2025",
    color: "Branco / Azul",
    sizes: ["PP", "P", "M", "G", "GG", "XGG"],
    location: "3-F2",
  },
  {
    id: "p2",
    code: "CAL-2024-032",
    name: "Calça Jeans Skinny",
    category: "Calças",
    collection: "Verão 2025",
    color: "Azul Indigo",
    sizes: ["36", "38", "40", "42", "44", "46", "48"],
    location: "11-A4",
  },
  {
    id: "p3",
    code: "VES-2024-015",
    name: "Vestido Floral Midi",
    category: "Vestidos",
    collection: "Primavera 2025",
    color: "Estampado",
    sizes: ["P", "M", "G", "GG"],
    location: "7-O2",
  },
  {
    id: "p4",
    code: "INF-2024-088",
    name: "Conjunto Infantil Moletom",
    category: "Infantil",
    collection: "Inverno 2025",
    color: "Cinza / Rosa",
    sizes: ["2", "4", "6", "8", "10", "12", "14"],
    location: "19-C1",
  },
  {
    id: "p5",
    code: "BLA-2024-007",
    name: "Blusa Tricô Texturizado",
    category: "Blusas",
    collection: "Inverno 2025",
    color: "Caramelo",
    sizes: ["PP", "P", "M", "G", "GG"],
    location: "1-A5",
  },
];

const SAVED_RULES: Record<string, SavedRule> = {
  p1: {
    productId: "p1",
    rules: [
      { size: "PP",  initialSupply: 2, idealMinimum: 1, criticalLevel: 0, enabled: true },
      { size: "P",   initialSupply: 4, idealMinimum: 2, criticalLevel: 1, enabled: true },
      { size: "M",   initialSupply: 6, idealMinimum: 3, criticalLevel: 1, enabled: true },
      { size: "G",   initialSupply: 5, idealMinimum: 2, criticalLevel: 1, enabled: true },
      { size: "GG",  initialSupply: 3, idealMinimum: 1, criticalLevel: 0, enabled: true },
      { size: "XGG", initialSupply: 1, idealMinimum: 1, criticalLevel: 0, enabled: false },
    ],
    createdBy: "Ana Souza",
    version: 3,
  },
};

// ─── Helpers ──────────────────────────────────────────────────────────────────

function buildDefaultRules(product: ProductReference): SizeRule[] {
  return product.sizes.map((size) => ({
    size,
    initialSupply: 0,
    idealMinimum: 0,
    criticalLevel: 0,
    enabled: true,
  }));
}

function getRuleStatus(rule: SizeRule): "ok" | "incomplete" | "error" | "disabled" {
  if (!rule.enabled) return "disabled";
  if (rule.criticalLevel >= rule.idealMinimum && rule.idealMinimum > 0) return "error";
  if (rule.initialSupply === 0 && rule.idealMinimum === 0) return "incomplete";
  return "ok";
}

function hasRulesErrors(rules: SizeRule[]) {
  return rules.some((r) => r.enabled && r.criticalLevel >= r.idealMinimum && r.idealMinimum > 0);
}

// ─── Rules Table (reutilizável para global e por loja) ────────────────────────

function RulesTable({
  rules,
  onUpdate,
  headerSlot,
  showCriticalLevel = true,
}: {
  rules: SizeRule[];
  onUpdate: (index: number, field: keyof SizeRule, value: number | boolean) => void;
  headerSlot?: React.ReactNode;
  showCriticalLevel?: boolean;
}) {
  const { t } = useLanguage();
  const hasErrors = showCriticalLevel && hasRulesErrors(rules);

  return (
    <div className="bg-white border border-gray-200 rounded-lg overflow-hidden">
      {/* Cabeçalho: tamanhos */}
      <div
        className="grid items-center border-b border-gray-100 bg-gray-50"
        style={{ gridTemplateColumns: `160px repeat(${rules.length}, minmax(72px, 1fr))` }}
      >
        <div className="px-5 py-3">{headerSlot}</div>
        {rules.map((rule) => {
          const hasError = getRuleStatus(rule) === "error";
          return (
            <div key={rule.size} className="py-3 text-center">
              <span
                className={`inline-flex items-center justify-center w-9 h-9 rounded text-sm ${
                  hasError ? "bg-red-600 text-white" : "bg-gray-900 text-white"
                }`}
                style={{ fontWeight: 600 }}
              >
                {rule.size}
              </span>
            </div>
          );
        })}
      </div>

      {/* Linhas */}
      {(
        [
          { labelKey: "initial_supply" as const, subKey: "initial_supply_sub" as const, field: "initialSupply" as const, isError: false, critical: false },
          { labelKey: "ideal_minimum" as const,  subKey: "ideal_minimum_sub" as const,  field: "idealMinimum"  as const, isError: false, critical: false },
          { labelKey: "critical_level" as const, subKey: "critical_level_sub" as const, field: "criticalLevel" as const, isError: true,  critical: true  },
        ] as const
      ).filter((row) => !row.critical || showCriticalLevel).map((row) => (
        <div
          key={row.field}
          className="grid items-center border-b border-gray-100 last:border-0"
          style={{ gridTemplateColumns: `160px repeat(${rules.length}, minmax(72px, 1fr))` }}
        >
          <div className="px-5 py-4">
            <p className="text-xs text-gray-700 uppercase tracking-wide">{t[row.labelKey]}</p>
            <p className="text-xs text-gray-400 mt-0.5">{t[row.subKey]}</p>
          </div>
          {rules.map((rule, i) => {
            const isRowError = row.isError && getRuleStatus(rule) === "error";
            return (
              <div key={rule.size} className="flex justify-center py-3">
                <NumberInput
                  value={rule[row.field] as number}
                  onChange={(v) => onUpdate(i, row.field, v)}
                  isError={isRowError}
                />
              </div>
            );
          })}
        </div>
      ))}

      {/* Banner de erro */}
      {hasErrors && (
        <div className="px-5 py-3 border-t border-red-100 bg-red-50 flex items-center gap-2">
          <AlertTriangle size={14} className="text-red-500 shrink-0" />
          <p className="text-sm text-red-700">{t.error_critical_msg}</p>
        </div>
      )}
    </div>
  );
}

// ─── Main ─────────────────────────────────────────────────────────────────────

export function DistributionRulesScreen() {
  const { t } = useLanguage();
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedProduct, setSelectedProduct] = useState<ProductReference | null>(null);
  const [rules, setRules] = useState<SizeRule[]>([]);
  const [storeRules, setStoreRules] = useState<Record<StoreId, SizeRule[]>>({
    TC: [], NH: [], IG: [], OS: [],
  });
  const [isDirty, setIsDirty] = useState(false);
  const [showDropdown, setShowDropdown] = useState(false);
  const [savedSuccess, setSavedSuccess] = useState(false);
  const [scanning, setScanning] = useState(false);
  const [activeTab, setActiveTab] = useState<"rules" | "history">("rules");
  const [copySourceId, setCopySourceId] = useState("");
  const [showCopyModal, setShowCopyModal] = useState(false);
  const [sameGradeForAll, setSameGradeForAll] = useState(true);
  const [isPermanent, setIsPermanent] = useState(false);

  const filteredProducts = MOCK_PRODUCTS.filter(
    (p) =>
      p.code.toLowerCase().includes(searchQuery.toLowerCase()) ||
      p.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      p.category.toLowerCase().includes(searchQuery.toLowerCase())
  );

  const selectProduct = useCallback((product: ProductReference) => {
    setSelectedProduct(product);
    setShowDropdown(false);
    setSearchQuery(product.code);
    setIsDirty(false);
    setSavedSuccess(false);
    setActiveTab("rules");
    setSameGradeForAll(true);
    setIsPermanent(true);
    const saved = SAVED_RULES[product.id];
    const base = saved ? saved.rules : buildDefaultRules(product);
    setRules(base);
    setStoreRules({ TC: base.map(r => ({ ...r })), NH: base.map(r => ({ ...r })), IG: base.map(r => ({ ...r })), OS: base.map(r => ({ ...r })) });
  }, []);

  const updateRule = (index: number, field: keyof SizeRule, value: number | boolean) => {
    setRules((prev) => {
      const updated = [...prev];
      updated[index] = { ...updated[index], [field]: value };
      return updated;
    });
    setIsDirty(true);
    setSavedSuccess(false);
  };

  const updateStoreRule = (store: StoreId, index: number, field: keyof SizeRule, value: number | boolean) => {
    setStoreRules((prev) => {
      const updated = [...prev[store]];
      updated[index] = { ...updated[index], [field]: value };
      return { ...prev, [store]: updated };
    });
    setIsDirty(true);
    setSavedSuccess(false);
  };

  const toggleSameGradeForAll = () => {
    const next = !sameGradeForAll;
    if (!next) {
      setStoreRules({
        TC: rules.map(r => ({ ...r })),
        NH: rules.map(r => ({ ...r })),
        IG: rules.map(r => ({ ...r })),
        OS: rules.map(r => ({ ...r })),
      });
    }
    setSameGradeForAll(next);
    setIsDirty(true);
  };

  const handleSave = () => {
    setSavedSuccess(true);
    setIsDirty(false);
    setTimeout(() => setSavedSuccess(false), 3000);
  };

  const handleReset = () => {
    if (!selectedProduct) return;
    const saved = SAVED_RULES[selectedProduct.id];
    const base = saved ? saved.rules : buildDefaultRules(selectedProduct);
    setRules(base);
    setStoreRules({ TC: base.map(r => ({ ...r })), NH: base.map(r => ({ ...r })), IG: base.map(r => ({ ...r })), OS: base.map(r => ({ ...r })) });
    setIsDirty(false);
    setSameGradeForAll(true);
  };

  const handleCopy = () => {
    const source = MOCK_PRODUCTS.find((p) => p.id === copySourceId);
    if (!source || !selectedProduct) return;
    const sourceRules = SAVED_RULES[source.id];
    if (sourceRules) {
      const mapped = selectedProduct.sizes.map((size) => {
        const match = sourceRules.rules.find((r) => r.size === size);
        return match ?? { size, initialSupply: 0, idealMinimum: 0, criticalLevel: 0, enabled: true };
      });
      setRules(mapped);
      setStoreRules({ TC: mapped.map(r => ({ ...r })), NH: mapped.map(r => ({ ...r })), IG: mapped.map(r => ({ ...r })), OS: mapped.map(r => ({ ...r })) });
      setIsDirty(true);
    }
    setShowCopyModal(false);
  };

  const handleScan = () => {
    setScanning(true);
    setTimeout(() => {
      setScanning(false);
      selectProduct(MOCK_PRODUCTS[0]);
    }, 1200);
  };

  const handleNewProduct = () => {
    setSelectedProduct(null);
    setSearchQuery("");
    setRules([]);
    setStoreRules({ TC: [], NH: [], IG: [], OS: [] });
    setIsDirty(false);
    setSavedSuccess(false);
    setShowDropdown(false);
    setSameGradeForAll(true);
    setIsPermanent(true);
  };

  const allRulesForValidation = sameGradeForAll
    ? rules
    : STORES.flatMap((s) => storeRules[s]);

  const hasErrors = isPermanent && hasRulesErrors(allRulesForValidation);
  const totalInitial = rules.reduce((s, r) => s + r.initialSupply, 0);

  return (
    <div>
      <div className="max-w-4xl mx-auto px-8 py-8 space-y-6">

        {/* Botão discreto: cadastrar novo produto */}
        {selectedProduct && (
          <div className="flex justify-end">
            <button
              onClick={handleNewProduct}
              className="flex items-center gap-1.5 text-xs text-gray-400 hover:text-gray-700 transition-colors"
            >
              <Plus size={12} />
              {t.prod_cad_new}
            </button>
          </div>
        )}

        {/* Como funciona */}
        <div className="bg-white border border-gray-200 rounded-lg p-5">
          <p className="text-xs text-gray-400 uppercase tracking-wide mb-3">{t.how_it_works}</p>
          <div className="grid grid-cols-3 gap-6">
            <div className="flex gap-3">
              <span className="w-5 h-5 rounded-full bg-gray-900 text-white text-xs flex items-center justify-center shrink-0 mt-0.5" style={{ fontWeight: 700 }}>1</span>
              <div>
                <p className="text-sm text-gray-800" style={{ fontWeight: 500 }}>{t.prod_cad_step1_title}</p>
                <p className="text-xs text-gray-400 mt-0.5 leading-relaxed">{t.prod_cad_step1_desc}</p>
              </div>
            </div>
            <div className="flex gap-3">
              <span className="w-5 h-5 rounded-full bg-gray-900 text-white text-xs flex items-center justify-center shrink-0 mt-0.5" style={{ fontWeight: 700 }}>2</span>
              <div>
                <p className="text-sm text-gray-800" style={{ fontWeight: 500 }}>{t.prod_cad_step2_title}</p>
                <p className="text-xs text-gray-400 mt-0.5 leading-relaxed">{t.prod_cad_step2_desc}</p>
              </div>
            </div>
            <div className="flex gap-3">
              <span className="w-5 h-5 rounded-full bg-gray-900 text-white text-xs flex items-center justify-center shrink-0 mt-0.5" style={{ fontWeight: 700 }}>3</span>
              <div>
                <p className="text-sm text-gray-800" style={{ fontWeight: 500 }}>{t.prod_cad_step3_title}</p>
                <p className="text-xs text-gray-400 mt-0.5 leading-relaxed">{t.prod_cad_step3_desc}</p>
              </div>
            </div>
          </div>
        </div>

        {/* Busca + Scan */}
        <div className="flex flex-col items-center gap-6">
          <div className="relative w-full max-w-md">
            <div className="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2.5 bg-white focus-within:border-gray-400 transition-colors">
              <input
                type="text"
                placeholder={t.prod_cad_search_placeholder}
                value={searchQuery}
                onChange={(e) => { setSearchQuery(e.target.value); setShowDropdown(true); }}
                onFocus={() => setShowDropdown(true)}
                className="flex-1 outline-none bg-transparent text-sm text-gray-700 placeholder:text-gray-400 font-mono"
              />
              {searchQuery ? (
                <button onClick={() => { setSearchQuery(""); setSelectedProduct(null); setRules([]); setShowDropdown(false); }} className="text-gray-300 hover:text-gray-500 transition-colors">
                  <X size={14} />
                </button>
              ) : (
                <button onClick={() => setShowDropdown(true)} className="text-gray-300 hover:text-gray-500 transition-colors">
                  <Search size={14} />
                </button>
              )}
            </div>

            {showDropdown && filteredProducts.length > 0 && (
              <>
                <div className="fixed inset-0 z-10" onClick={() => setShowDropdown(false)} />
                <div className="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-20 overflow-hidden max-h-72 overflow-y-auto">
                  {filteredProducts.map((p) => (
                    <button
                      key={p.id}
                      onClick={() => selectProduct(p)}
                      className={`w-full text-left px-4 py-3 flex items-center justify-between gap-4 hover:bg-gray-50 border-b border-gray-100 last:border-0 transition-colors ${selectedProduct?.id === p.id ? "bg-gray-50" : ""}`}
                    >
                      <div>
                        <p className="text-sm text-gray-900">{p.name}</p>
                        <p className="text-xs text-gray-400 mt-0.5 font-mono">{p.code} · {p.category}</p>
                      </div>
                      {SAVED_RULES[p.id] && (
                        <span className="text-xs text-gray-500 border border-gray-200 px-2 py-0.5 rounded shrink-0">{t.prod_cad_saved_rules_badge}</span>
                      )}
                    </button>
                  ))}
                </div>
              </>
            )}
          </div>

          {!selectedProduct && (
            <button
              onClick={handleScan}
              disabled={scanning}
              className={`flex flex-col items-center justify-center gap-3 w-48 h-48 rounded-2xl border-2 transition-colors ${
                scanning
                  ? "border-gray-200 bg-gray-50 text-gray-300 cursor-not-allowed"
                  : "border-gray-900 bg-gray-900 text-white hover:bg-gray-700 active:scale-95"
              }`}
              style={{ transition: "background-color 0.15s, transform 0.1s" }}
            >
              <ScanLine size={48} className={scanning ? "animate-pulse" : ""} strokeWidth={1.25} />
              <span className="text-sm tracking-wide" style={{ fontWeight: 500 }}>
                {scanning ? t.scanning : t.scan_code}
              </span>
            </button>
          )}
        </div>

        {/* Produto selecionado */}
        {selectedProduct && (
          <>
            {/* Card do produto */}
            <div className="bg-white border border-gray-200 rounded-lg p-5">
              <div className="flex items-start justify-between gap-6 flex-wrap">
                <div>
                  <h2 className="text-gray-900">{selectedProduct.name}</h2>
                  <p className="font-mono text-sm text-gray-400 mt-1">{selectedProduct.code}</p>
                  <p className="text-sm text-gray-500 mt-2">
                    {selectedProduct.category} · {selectedProduct.collection} · {selectedProduct.color}
                  </p>
                  <p className="text-xs text-gray-400 mt-1">
                    Grade: {selectedProduct.sizes.join("  ")}
                  </p>
                </div>
                <div className="flex items-start gap-6">
                  <div>
                    <p className="text-xs text-gray-400 mb-1">{t.prod_cad_location}</p>
                    <span className="font-mono text-2xl text-gray-900" style={{ fontWeight: 600 }}>
                      {selectedProduct.location}
                    </span>
                  </div>
                  <div className="pl-6 border-l border-gray-100">
                    <p className="text-xs text-gray-400 mb-1">{t.pieces_initial}</p>
                    <p className="text-2xl text-gray-900" style={{ fontWeight: 600 }}>{totalInitial}</p>
                  </div>
                  <div>
                    <p className="text-xs text-gray-400 mb-1">{t.active_sizes}</p>
                    <p className="text-2xl text-gray-900" style={{ fontWeight: 600 }}>{rules.length}</p>
                  </div>
                  {hasErrors && (
                    <div>
                      <p className="text-xs text-red-500 mb-1">{t.errors}</p>
                      <p className="text-2xl text-red-600" style={{ fontWeight: 600 }}>
                        {allRulesForValidation.filter((r) => getRuleStatus(r) === "error").length}
                      </p>
                    </div>
                  )}
                </div>
              </div>
            </div>

            {/* Permanente toggle */}
            <button
              role="checkbox"
              aria-checked={isPermanent}
              onClick={() => setIsPermanent((v) => !v)}
              className={`w-full flex items-center gap-4 px-5 py-4 rounded-lg border-2 text-left transition-colors cursor-pointer ${
                isPermanent
                  ? "border-gray-900 bg-white"
                  : "border-gray-200 bg-white hover:border-gray-300"
              }`}
            >
              <span className={`w-5 h-5 rounded border-2 flex items-center justify-center shrink-0 transition-colors ${
                isPermanent ? "bg-gray-900 border-gray-900" : "border-gray-400"
              }`}>
                {isPermanent && (
                  <svg width="9" height="7" viewBox="0 0 8 6" fill="none">
                    <path d="M1 3L3 5L7 1" stroke="white" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                  </svg>
                )}
              </span>
              <div className="flex-1">
                <p className="text-sm text-gray-900" style={{ fontWeight: 600 }}>{t.prod_cad_permanent}</p>
                <p className="text-xs text-gray-400 mt-0.5">
                  {isPermanent
                    ? "O Nível Crítico está ativo — reposição acionada automaticamente ao atingir o limite."
                    : "Produto não permanente — o Nível Crítico não se aplica."}
                </p>
              </div>
              <span className={`text-xs px-2 py-1 rounded font-mono shrink-0 ${
                isPermanent ? "bg-gray-900 text-white" : "bg-gray-100 text-gray-400"
              }`} style={{ fontWeight: 600 }}>
                {isPermanent ? "ON" : "OFF"}
              </span>
            </button>

            {/* Toolbar */}
            <div className="flex items-center justify-between gap-3 flex-wrap">
              <div className="flex border-b border-gray-200">
                {(["rules", "history"] as const).map((tab) => (
                  <button
                    key={tab}
                    onClick={() => setActiveTab(tab)}
                    className={`flex items-center gap-1.5 px-4 py-2 text-sm border-b-2 -mb-px transition-colors ${
                      activeTab === tab
                        ? "border-gray-900 text-gray-900"
                        : "border-transparent text-gray-400 hover:text-gray-600"
                    }`}
                  >
                    {tab === "history" && <History size={13} />}
                    {tab === "rules" ? t.prod_cad_rules_tab : t.history}
                  </button>
                ))}
              </div>

              <div className="flex items-center gap-2">
                <button
                  onClick={() => setShowCopyModal(true)}
                  className="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-500 border border-gray-200 rounded-md hover:bg-gray-50 bg-white transition-colors"
                >
                  <Copy size={13} />
                  {t.copy_from_ref}
                </button>
                {isDirty && (
                  <button
                    onClick={handleReset}
                    className="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-500 border border-gray-200 rounded-md hover:bg-gray-50 bg-white transition-colors"
                  >
                    <RotateCcw size={13} />
                    {t.discard}
                  </button>
                )}
                <button
                  onClick={handleSave}
                  disabled={!isDirty || hasErrors}
                  className={`flex items-center gap-1.5 px-4 py-1.5 text-sm rounded-md transition-colors ${
                    savedSuccess
                      ? "bg-gray-900 text-white"
                      : !isDirty || hasErrors
                        ? "bg-gray-100 text-gray-300 cursor-not-allowed"
                        : "bg-gray-900 text-white hover:bg-gray-700"
                  }`}
                >
                  {savedSuccess ? <CheckCircle2 size={13} /> : <Save size={13} />}
                  {savedSuccess ? t.saved : t.save}
                </button>
              </div>
            </div>

            {/* Aba: Regras */}
            {activeTab === "rules" && (
              <div className="space-y-4">

                {/* Checkbox — aparece como headerSlot na célula A1 */}
                {(() => {
                  const checkbox = (
                    <button
                      role="checkbox"
                      aria-checked={sameGradeForAll}
                      onClick={toggleSameGradeForAll}
                      className="flex items-start gap-2 cursor-pointer select-none text-left w-full"
                    >
                      <span
                        className={`mt-0.5 w-3.5 h-3.5 rounded border-2 flex items-center justify-center transition-colors shrink-0 ${
                          sameGradeForAll ? "bg-gray-900 border-gray-900" : "border-gray-400 hover:border-gray-600"
                        }`}
                      >
                        {sameGradeForAll && (
                          <svg width="7" height="5" viewBox="0 0 8 6" fill="none">
                            <path d="M1 3L3 5L7 1" stroke="white" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                          </svg>
                        )}
                      </span>
                      <span className="text-xs text-gray-600 leading-snug">{t.same_grade_all_stores}</span>
                    </button>
                  );

                  if (sameGradeForAll) {
                    return <RulesTable rules={rules} onUpdate={updateRule} headerSlot={checkbox} showCriticalLevel={isPermanent} />;
                  }

                  return STORES.map((store, idx) => (
                    <div key={store}>
                      <div className="flex items-center gap-3 mb-2">
                        <span className="inline-flex items-center justify-center w-8 h-8 rounded bg-gray-900 text-white text-xs" style={{ fontWeight: 700 }}>
                          {store}
                        </span>
                        <span className="text-sm text-gray-600" style={{ fontWeight: 500 }}>{STORE_NAMES[store]}</span>
                      </div>
                      <RulesTable
                        rules={storeRules[store]}
                        onUpdate={(i, f, v) => updateStoreRule(store, i, f, v)}
                        headerSlot={idx === 0 ? checkbox : undefined}
                        showCriticalLevel={isPermanent}
                      />
                    </div>
                  ));
                })()}
              </div>
            )}

            {/* Aba: Histórico */}
            {activeTab === "history" && (
              <div className="bg-white border border-gray-200 rounded-lg overflow-hidden">
                {SAVED_RULES[selectedProduct.id] ? (
                  <div className="divide-y divide-gray-100">
                    {[3, 2, 1].map((v) => (
                      <div key={v} className="px-5 py-4 flex items-center justify-between gap-4">
                        <div className="flex items-center gap-3">
                          <span className={`text-xs px-2 py-1 rounded font-mono ${v === 3 ? "bg-gray-900 text-white" : "bg-gray-100 text-gray-500"}`}>
                            v{v}
                          </span>
                          <div>
                            <p className="text-sm text-gray-800">
                              {v === 3 ? t.current_version : `${t.version} ${v}`}
                            </p>
                            <p className="text-xs text-gray-400 mt-0.5">
                              Ana Souza · {v === 3 ? "14/10/2025 09:30" : v === 2 ? "02/09/2025 14:15" : "18/07/2025 11:00"}
                            </p>
                          </div>
                        </div>
                        {v !== 3 && (
                          <button className="text-xs text-gray-500 border border-gray-200 px-3 py-1.5 rounded hover:bg-gray-50 transition-colors">
                            {t.restore}
                          </button>
                        )}
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="px-5 py-12 text-center">
                    <History size={28} className="mx-auto mb-3 text-gray-200" />
                    <p className="text-sm text-gray-400">{t.no_history}</p>
                  </div>
                )}
              </div>
            )}
          </>
        )}

      </div>

      {/* Modal: copiar de outra ref. */}
      {showCopyModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div className="absolute inset-0 bg-black/30" onClick={() => setShowCopyModal(false)} />
          <div className="relative bg-white rounded-lg shadow-xl w-full max-w-md p-6 z-10">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-gray-800">{t.prod_cad_copy_modal_title}</h3>
              <button onClick={() => setShowCopyModal(false)} className="text-gray-300 hover:text-gray-500">
                <X size={16} />
              </button>
            </div>
            <p className="text-sm text-gray-400 mb-4">{t.prod_cad_copy_modal_desc}</p>
            <div className="space-y-1.5 max-h-60 overflow-y-auto">
              {MOCK_PRODUCTS.filter((p) => p.id !== selectedProduct?.id && SAVED_RULES[p.id]).map((p) => (
                <label
                  key={p.id}
                  className={`flex items-center gap-3 p-3 rounded-md border cursor-pointer transition-colors ${
                    copySourceId === p.id ? "border-gray-900 bg-gray-50" : "border-gray-200 hover:bg-gray-50"
                  }`}
                >
                  <input
                    type="radio"
                    name="copy-source"
                    value={p.id}
                    checked={copySourceId === p.id}
                    onChange={() => setCopySourceId(p.id)}
                    className="accent-gray-900"
                  />
                  <div>
                    <p className="text-sm text-gray-800">{p.name}</p>
                    <p className="text-xs text-gray-400 font-mono mt-0.5">{p.code} · {p.sizes.join(", ")}</p>
                  </div>
                </label>
              ))}
              {MOCK_PRODUCTS.filter((p) => p.id !== selectedProduct?.id && SAVED_RULES[p.id]).length === 0 && (
                <p className="text-sm text-gray-400 text-center py-6">{t.prod_cad_copy_no_refs}</p>
              )}
            </div>
            <div className="flex gap-2 mt-5">
              <button
                onClick={() => setShowCopyModal(false)}
                className="flex-1 py-2 border border-gray-200 rounded-md text-sm text-gray-500 hover:bg-gray-50 transition-colors"
              >
                {t.cancel}
              </button>
              <button
                onClick={handleCopy}
                disabled={!copySourceId}
                className={`flex-1 py-2 rounded-md text-sm flex items-center justify-center gap-1.5 transition-colors ${
                  copySourceId ? "bg-gray-900 text-white hover:bg-gray-700" : "bg-gray-100 text-gray-300 cursor-not-allowed"
                }`}
              >
                <Plus size={13} />
                {t.apply}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
