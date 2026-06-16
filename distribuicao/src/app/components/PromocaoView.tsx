import { useState } from "react";
import { Upload, Trash2, X, Settings2, CheckCircle2 } from "lucide-react";
import { useLanguage } from "../../i18n/LanguageContext";
import { NumberInput } from "./NumberInput";

// ─── Types (exported for shared state) ───────────────────────────────────────

export interface PromocaoSizeRule {
  size: string;
  initialSupply: number;
  idealMinimum: number;
  criticalLevel: number;
}

// Em produção: api.getProductGrade(skuPrefix) → string[]
export type PromocaoRules = PromocaoSizeRule[];

// ─── Product grade lookup (mock ERP) ─────────────────────────────────────────

const PRODUCT_SIZES: Record<string, string[]> = {
  "CAL-032": ["36", "38", "40", "42", "44", "46", "48"],
  "CAL-033": ["34", "36", "38", "40", "42", "44"],
  "CAM-001": ["PP", "P", "M", "G", "GG", "XGG"],
  "CAM-002": ["P", "M", "G", "GG"],
  "BLA-007": ["PP", "P", "M", "G", "GG"],
  "BLA-012": ["EXG", "EXGG", "EXGGG"],
  "VES-015": ["P", "M", "G", "GG"],
  "CNJ-088": ["2", "4", "6", "8", "10"],
  "BER-021": ["38", "40", "42", "44", "46"],
  "MAC-003": ["P", "M", "G"],
  "SHO-014": ["34", "36", "38", "40", "42"],
};

function getProductSizes(sku: string): string[] {
  const prefix = sku.split("-").slice(0, 2).join("-");
  return PRODUCT_SIZES[prefix] ?? [sku.split("-").pop() ?? sku];
}

function buildBlankRules(sizes: string[]): PromocaoRules {
  return sizes.map((size) => ({ size, initialSupply: 0, idealMinimum: 0, criticalLevel: 0 }));
}

export interface PromocaoItem {
  id: string;
  sku: string;
  name: string;
  category: string;
  dataLoja: string;
  loja: boolean;
  sistema: boolean;
  rules?: PromocaoRules;
}

// ─── Demo data (exported so App can initialise shared state) ──────────────────

export const DEMO_PROMOCAO_ITEMS: PromocaoItem[] = [
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

// ─── Product catalog ──────────────────────────────────────────────────────────

const PRODUCT_CATALOG: Record<string, { name: string; category: string }> = {
  "CAL-032-36": { name: "Calça Jeans Skinny AD MASC", category: "CALCA JE" },
  "CAL-032-38": { name: "Calça Jeans Skinny AD MASC", category: "CALCA JE" },
  "CAL-032-40": { name: "Calça Jeans Skinny AD MASC", category: "CALCA JE" },
  "CAL-032-42": { name: "Calça Jeans Skinny AD MASC", category: "CALCA JE" },
  "CAL-033-34": { name: "Calça Jeans Skinny AD FEM",  category: "CALCA JE" },
  "CAL-033-36": { name: "Calça Jeans Skinny AD FEM",  category: "CALCA JE" },
  "CAL-033-38": { name: "Calça Jeans Skinny AD FEM",  category: "CALCA JE" },
  "CAL-033-40": { name: "Calça Jeans Skinny AD FEM",  category: "CALCA JE" },
  "CAM-001-PP": { name: "Camisa Social Slim AD MASC", category: "CAMISA" },
  "CAM-001-P":  { name: "Camisa Social Slim AD MASC", category: "CAMISA" },
  "CAM-001-M":  { name: "Camisa Social Slim AD MASC", category: "CAMISA" },
  "CAM-001-G":  { name: "Camisa Social Slim AD MASC", category: "CAMISA" },
  "CAM-002-P":  { name: "Camisa Casual Linho AD UNI", category: "CAMISA" },
  "CAM-002-M":  { name: "Camisa Casual Linho AD UNI", category: "CAMISA" },
  "CAM-002-G":  { name: "Camisa Casual Linho AD UNI", category: "CAMISA" },
  "BLA-007-PP": { name: "Blusa Tricô Texturizado AD FEM", category: "BLUSA" },
  "BLA-007-P":  { name: "Blusa Tricô Texturizado AD FEM", category: "BLUSA" },
  "BLA-007-M":  { name: "Blusa Tricô Texturizado AD FEM", category: "BLUSA" },
  "BLA-012-EXG":  { name: "Blusa Malha Plus EX FEM", category: "BLUSA" },
  "BLA-012-EXGG": { name: "Blusa Malha Plus EX FEM", category: "BLUSA" },
  "VES-015-P":  { name: "Vestido Casual Floral AD FEM", category: "VESTIDO" },
  "VES-015-M":  { name: "Vestido Casual Floral AD FEM", category: "VESTIDO" },
  "VES-015-G":  { name: "Vestido Casual Floral AD FEM", category: "VESTIDO" },
  "CNJ-088-2":  { name: "Conjunto Moletom INF UNI",   category: "CONJUNTO" },
  "CNJ-088-4":  { name: "Conjunto Moletom INF UNI",   category: "CONJUNTO" },
  "CNJ-088-6":  { name: "Conjunto Moletom INF UNI",   category: "CONJUNTO" },
  "BER-021-38": { name: "Bermuda Jeans AD MASC",      category: "BERMUDA JE" },
  "BER-021-40": { name: "Bermuda Jeans AD MASC",      category: "BERMUDA JE" },
  "BER-021-42": { name: "Bermuda Jeans AD MASC",      category: "BERMUDA JE" },
  "MAC-003-P":  { name: "Macacão Linho AD FEM",       category: "MACACAO" },
  "MAC-003-M":  { name: "Macacão Linho AD FEM",       category: "MACACAO" },
  "SHO-014-34": { name: "Short Alfaiataria AD FEM",   category: "SHORT" },
  "SHO-014-36": { name: "Short Alfaiataria AD FEM",   category: "SHORT" },
  "SHO-014-38": { name: "Short Alfaiataria AD FEM",   category: "SHORT" },
};

function resolveSku(sku: string): { name: string; category: string } {
  return PRODUCT_CATALOG[sku.trim().toUpperCase()] ?? { name: "SKU não encontrado", category: "" };
}

// ─── Checkbox cell ────────────────────────────────────────────────────────────

function CheckCell({ checked, onChange }: { checked: boolean; onChange: () => void }) {
  return (
    <button
      onClick={onChange}
      className={`w-5 h-5 rounded border-2 flex items-center justify-center transition-colors ${
        checked ? "bg-gray-900 border-gray-900" : "border-gray-300 hover:border-gray-500"
      }`}
    >
      {checked && (
        <svg width="10" height="8" viewBox="0 0 10 8" fill="none">
          <path d="M1 4L3.5 6.5L9 1" stroke="white" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
      )}
    </button>
  );
}

// ─── Import modal ─────────────────────────────────────────────────────────────

function ImportModal({ onClose, onImport }: { onClose: () => void; onImport: (items: PromocaoItem[]) => void }) {
  const { t } = useLanguage();
  const [raw, setRaw] = useState("");

  function handleConfirm() {
    const skus = raw.split(/[\n,;]+/).map((s) => s.trim()).filter(Boolean);
    if (!skus.length) return;
    const newItems: PromocaoItem[] = skus.map((sku, i) => ({
      id: `imp-${Date.now()}-${i}`,
      sku: sku.toUpperCase(),
      ...resolveSku(sku),
      dataLoja: "",
      loja: false,
      sistema: false,
    }));
    onImport(newItems);
    onClose();
  }

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/30">
      <div className="bg-white rounded-xl shadow-xl border border-gray-200 w-full max-w-md mx-4 p-6 space-y-4">
        <div className="flex items-center justify-between">
          <p className="text-sm text-gray-900" style={{ fontWeight: 600 }}>{t.prom_import_title}</p>
          <button onClick={onClose} className="text-gray-400 hover:text-gray-600 transition-colors">
            <X size={16} />
          </button>
        </div>
        <p className="text-xs text-gray-400">{t.prom_import_hint}</p>
        <textarea
          value={raw}
          onChange={(e) => setRaw(e.target.value)}
          placeholder={t.prom_import_placeholder}
          rows={7}
          autoFocus
          className="w-full border border-gray-200 rounded-md px-3 py-2.5 text-sm font-mono text-gray-800 placeholder-gray-300 focus:outline-none focus:border-gray-400 resize-none"
        />
        <div className="flex justify-end gap-2">
          <button onClick={onClose} className="px-3 py-1.5 text-sm text-gray-500 border border-gray-200 rounded-md hover:bg-gray-50 transition-colors">
            {t.cancel}
          </button>
          <button
            onClick={handleConfirm}
            disabled={!raw.trim()}
            className={`flex items-center gap-1.5 px-4 py-1.5 text-sm rounded-md transition-colors ${
              raw.trim() ? "bg-gray-900 text-white hover:bg-gray-700" : "bg-gray-100 text-gray-300 cursor-not-allowed"
            }`}
          >
            <Upload size={13} />
            {t.import_skus}
          </button>
        </div>
      </div>
    </div>
  );
}

// ─── Rules modal ─────────────────────────────────────────────────────────────

function RulesModal({ item, onSave, onClose }: {
  item: PromocaoItem;
  onSave: (rules: PromocaoRules) => void;
  onClose: () => void;
}) {
  const { t } = useLanguage();
  const sizes = getProductSizes(item.sku);
  const [draft, setDraft] = useState<PromocaoRules>(
    item.rules ?? buildBlankRules(sizes)
  );

  function updateCell(size: string, field: keyof PromocaoSizeRule, value: number) {
    setDraft((prev) =>
      prev.map((row) => row.size === size ? { ...row, [field]: value } : row)
    );
  }

  const hasError = draft.some(
    (row) => row.idealMinimum > 0 && row.criticalLevel >= row.idealMinimum
  );

  const rows = [
    { label: t.initial_supply, sub: t.initial_supply_sub, field: "initialSupply" as keyof PromocaoSizeRule, isError: false },
    { label: t.ideal_minimum,  sub: t.ideal_minimum_sub,  field: "idealMinimum"  as keyof PromocaoSizeRule, isError: false },
    { label: t.critical_level, sub: t.critical_level_sub, field: "criticalLevel" as keyof PromocaoSizeRule, isError: true  },
  ];

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/30">
      <div className="bg-white rounded-xl shadow-xl border border-gray-200 w-full max-w-3xl mx-4 overflow-hidden">
        {/* Header */}
        <div className="flex items-center justify-between px-5 py-4 border-b border-gray-100">
          <div>
            <p className="text-sm text-gray-900" style={{ fontWeight: 600 }}>{t.prom_rules_modal_title}</p>
            <p className="text-xs text-gray-400 mt-0.5 font-mono">{item.sku} · {item.name}</p>
          </div>
          <button onClick={onClose} className="text-gray-400 hover:text-gray-600 transition-colors">
            <X size={16} />
          </button>
        </div>

        {/* Grid table */}
        <div className="overflow-x-auto">
          <div className="px-5 py-4">
            <table className="w-full text-sm">
              <thead>
                <tr>
                  <th className="text-left py-2 pr-6 text-xs text-gray-400 font-normal w-44">Regra</th>
                  {sizes.map((size) => (
                    <th key={size} className="py-2 text-center text-xs font-normal min-w-[72px]">
                      <span className="inline-flex items-center justify-center w-9 h-9 rounded bg-gray-900 text-white text-xs" style={{ fontWeight: 600 }}>
                        {size}
                      </span>
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {rows.map((row) => (
                  <tr key={String(row.field)} className="border-t border-gray-100">
                    <td className="py-3 pr-6">
                      <p className="text-xs text-gray-700 uppercase tracking-wide" style={{ fontWeight: 500 }}>{row.label}</p>
                      <p className="text-xs text-gray-400 mt-0.5">{row.sub}</p>
                    </td>
                    {sizes.map((size) => {
                      const sizeRule = draft.find((r) => r.size === size) ?? { size, initialSupply: 0, idealMinimum: 0, criticalLevel: 0 };
                      const isCellError = row.isError && sizeRule.idealMinimum > 0 && sizeRule.criticalLevel >= sizeRule.idealMinimum;
                      return (
                        <td key={size} className="py-3 text-center">
                          <NumberInput
                            value={sizeRule[row.field] as number}
                            onChange={(v) => updateCell(size, row.field, v)}
                            isError={isCellError}
                          />
                        </td>
                      );
                    })}
                  </tr>
                ))}
              </tbody>
            </table>

            {hasError && (
              <p className="text-xs text-red-600 bg-red-50 border border-red-100 rounded px-3 py-2 mt-3">
                {t.error_critical_msg}
              </p>
            )}
          </div>
        </div>

        {/* Footer */}
        <div className="flex gap-2 px-5 py-4 border-t border-gray-100">
          <button
            onClick={onClose}
            className="flex-1 py-2 border border-gray-200 rounded-md text-sm text-gray-500 hover:bg-gray-50 transition-colors"
          >
            {t.cancel}
          </button>
          <button
            onClick={() => { onSave(draft); onClose(); }}
            disabled={hasError}
            className={`flex-1 py-2 rounded-md text-sm transition-colors ${
              hasError ? "bg-gray-100 text-gray-300 cursor-not-allowed" : "bg-gray-900 text-white hover:bg-gray-700"
            }`}
          >
            {t.save}
          </button>
        </div>
      </div>
    </div>
  );
}

// ─── Table ────────────────────────────────────────────────────────────────────

function PromocaoTable({
  items,
  onUpdate,
  onRemove,
  onDefineRules,
}: {
  items: PromocaoItem[];
  onUpdate: (id: string, field: keyof PromocaoItem, value: string | boolean | PromocaoRules) => void;
  onRemove: (id: string) => void;
  onDefineRules: (item: PromocaoItem) => void;
}) {
  const { t } = useLanguage();
  const cols = "160px 1fr 112px 148px 72px 72px 36px";
  return (
    <div className="bg-white border border-gray-200 rounded-lg overflow-hidden">
      <div className="grid border-b border-gray-100 bg-gray-50 text-xs text-gray-400 uppercase tracking-wide"
        style={{ gridTemplateColumns: cols }}>
        <div className="px-5 py-3">{t.prom_sku}</div>
        <div className="px-4 py-3">{t.prom_product}</div>
        <div className="py-3" />
        <div className="px-4 py-3">{t.prom_date_store}</div>
        <div className="py-3 text-center">{t.prom_store_check}</div>
        <div className="py-3 text-center">{t.prom_system_check}</div>
        <div />
      </div>
      {items.map((item) => {
        const done = item.loja && item.sistema;
        const hasRules = !!item.rules;
        return (
          <div
            key={item.id}
            className={`grid items-center border-b border-gray-100 last:border-0 transition-colors ${done ? "bg-gray-50" : "bg-white"}`}
            style={{ gridTemplateColumns: cols }}
          >
            <div className="px-5 py-3">
              <span className="font-mono text-xs text-gray-700 bg-gray-100 px-2 py-0.5 rounded">{item.sku}</span>
            </div>
            <div className="px-4 py-3 min-w-0">
              <p className={`text-sm truncate ${done ? "text-gray-400" : item.name === "SKU não encontrado" ? "text-gray-300 italic" : "text-gray-800"}`}>
                {item.name}
              </p>
              {item.category && <p className="text-xs text-gray-400 mt-0.5">{item.category}</p>}
            </div>
            <div className="py-3 flex justify-center">
              <button
                onClick={() => onDefineRules(item)}
                className={`flex items-center gap-1 text-xs px-2 py-1 rounded border transition-colors ${
                  hasRules
                    ? "border-gray-900 text-gray-900 bg-white hover:bg-gray-50"
                    : "border-gray-200 text-gray-400 bg-white hover:border-gray-400 hover:text-gray-600"
                }`}
              >
                {hasRules
                  ? <><CheckCircle2 size={11} />{t.prom_rules_saved}</>
                  : <><Settings2 size={11} />{t.prom_define_rules}</>
                }
              </button>
            </div>
            <div className="px-4 py-3">
              <input
                type="date"
                value={item.dataLoja}
                onChange={(e) => onUpdate(item.id, "dataLoja", e.target.value)}
                className="w-full border border-gray-200 rounded-md px-2 py-1.5 text-xs text-gray-700 focus:outline-none focus:border-gray-400 bg-white"
              />
            </div>
            <div className="py-3 flex justify-center">
              <CheckCell checked={item.loja} onChange={() => onUpdate(item.id, "loja", !item.loja)} />
            </div>
            <div className="py-3 flex justify-center">
              <CheckCell checked={item.sistema} onChange={() => onUpdate(item.id, "sistema", !item.sistema)} />
            </div>
            <div className="flex justify-center pr-1">
              <button onClick={() => onRemove(item.id)} className="p-1 text-gray-300 hover:text-gray-500 hover:bg-gray-100 rounded transition-colors">
                <Trash2 size={12} />
              </button>
            </div>
          </div>
        );
      })}
    </div>
  );
}

// ─── Main (receives shared state from App) ────────────────────────────────────

export function PromocaoView({
  items,
  onUpdate,
  onRemove,
  onImport,
}: {
  items: PromocaoItem[];
  onUpdate: (id: string, field: keyof PromocaoItem, value: string | boolean | PromocaoRules) => void;
  onRemove: (id: string) => void;
  onImport: (newItems: PromocaoItem[]) => void;
}) {
  const { t } = useLanguage();
  const [showModal, setShowModal] = useState(false);
  const [editingRulesItem, setEditingRulesItem] = useState<PromocaoItem | null>(null);

  const sorted = [
    ...items.filter((i) => !(i.loja && i.sistema)),
    ...items.filter((i) =>   i.loja && i.sistema),
  ];

  const countLoja    = items.filter((i) => i.loja).length;
  const countSistema = items.filter((i) => i.sistema).length;
  const countDone    = items.filter((i) => i.loja && i.sistema).length;

  return (
    <>
      {showModal && <ImportModal onClose={() => setShowModal(false)} onImport={onImport} />}
      {editingRulesItem && (
        <RulesModal
          item={editingRulesItem}
          onSave={(rules) => onUpdate(editingRulesItem.id, "rules", rules)}
          onClose={() => setEditingRulesItem(null)}
        />
      )}

      <div className="max-w-4xl mx-auto px-8 py-8 space-y-5">
        {/* How it works */}
        <div className="bg-white border border-gray-200 rounded-lg p-5">
          <p className="text-xs text-gray-400 uppercase tracking-wide mb-3">{t.how_it_works}</p>
          <div className="grid grid-cols-3 gap-6">
            <div className="flex gap-3">
              <span className="w-5 h-5 rounded-full bg-gray-900 text-white text-xs flex items-center justify-center shrink-0 mt-0.5" style={{ fontWeight: 700 }}>1</span>
              <div>
                <p className="text-sm text-gray-800" style={{ fontWeight: 500 }}>{t.prom_cad_step1_title}</p>
                <p className="text-xs text-gray-400 mt-0.5 leading-relaxed">{t.prom_cad_step1_desc}</p>
              </div>
            </div>
            <div className="flex gap-3">
              <span className="w-5 h-5 rounded-full bg-gray-900 text-white text-xs flex items-center justify-center shrink-0 mt-0.5" style={{ fontWeight: 700 }}>2</span>
              <div>
                <p className="text-sm text-gray-800" style={{ fontWeight: 500 }}>{t.prom_cad_step2_title}</p>
                <p className="text-xs text-gray-400 mt-0.5 leading-relaxed">{t.prom_cad_step2_desc}</p>
              </div>
            </div>
            <div className="flex gap-3">
              <span className="w-5 h-5 rounded-full bg-gray-900 text-white text-xs flex items-center justify-center shrink-0 mt-0.5" style={{ fontWeight: 700 }}>3</span>
              <div>
                <p className="text-sm text-gray-800" style={{ fontWeight: 500 }}>{t.prom_cad_step3_title}</p>
                <p className="text-xs text-gray-400 mt-0.5 leading-relaxed">{t.prom_cad_step3_desc}</p>
              </div>
            </div>
          </div>
        </div>

        {/* Toolbar */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-3">
            <span className="text-xs text-gray-500 border border-gray-200 rounded-full px-3 py-1 bg-white">
              {items.length} SKU{items.length !== 1 ? "s" : ""}
            </span>
            <span className="text-xs text-gray-500 border border-gray-200 rounded-full px-3 py-1 bg-white">
              {countLoja} / {items.length} loja
            </span>
            <span className="text-xs text-gray-500 border border-gray-200 rounded-full px-3 py-1 bg-white">
              {countSistema} / {items.length} sistema
            </span>
            {countDone > 0 && (
              <span className="text-xs text-gray-400 border border-gray-200 rounded-full px-3 py-1 bg-white">
                {countDone} concluído{countDone !== 1 ? "s" : ""}
              </span>
            )}
          </div>
          <button
            onClick={() => setShowModal(true)}
            className="flex items-center gap-1.5 px-4 py-2 text-sm bg-gray-900 text-white rounded-md hover:bg-gray-700 transition-colors"
          >
            <Upload size={13} />
            {t.import_skus}
          </button>
        </div>

        {/* Table */}
        {items.length > 0 ? (
          <PromocaoTable items={sorted} onUpdate={onUpdate} onRemove={onRemove} onDefineRules={setEditingRulesItem} />
        ) : (
          <div className="bg-white border border-gray-200 rounded-lg p-14 text-center">
            <p className="text-gray-400">{t.prom_no_items}</p>
          </div>
        )}
      </div>
    </>
  );
}
