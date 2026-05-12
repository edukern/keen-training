import { useState } from "react";
import { ChevronDown, ChevronRight, PackageOpen } from "lucide-react";
import { useLanguage } from "../../i18n/LanguageContext";

// ─── Stores ───────────────────────────────────────────────────────────────────

const STORES = ["TC", "NH", "IG", "OS"] as const;
type StoreId = typeof STORES[number];

const STORE_NAMES: Record<StoreId, string> = {
  TC: "Três Coroas",
  NH: "Nova Hartz",
  IG: "Igrejinha",
  OS: "Osório",
};

// ─── Mock: segmentos com regras salvas ────────────────────────────────────────
// Em produção virá de api.getSegmentRules() — mesmo dado de Segmento > Cadastro.

interface SizeRule {
  size: string;
  idealMinimum: number;
  criticalLevel: number;
}

interface SavedSegment {
  key: string;
  label: string;
  sizes: string[];
  rules: SizeRule[];
}

const SAVED_SEGMENTS: SavedSegment[] = [
  {
    key: "CALÇA JEANS|AD|FEM",
    label: "CALÇA JEANS · AD · FEM",
    sizes: ["34", "36", "38", "40", "42", "44"],
    rules: [
      { size: "34", idealMinimum: 2, criticalLevel: 1 },
      { size: "36", idealMinimum: 3, criticalLevel: 1 },
      { size: "38", idealMinimum: 4, criticalLevel: 2 },
      { size: "40", idealMinimum: 3, criticalLevel: 1 },
      { size: "42", idealMinimum: 2, criticalLevel: 1 },
      { size: "44", idealMinimum: 1, criticalLevel: 0 },
    ],
  },
  {
    key: "CAMISA|AD|MASC",
    label: "CAMISA · AD · MASC",
    sizes: ["PP", "P", "M", "G", "GG", "XGG"],
    rules: [
      { size: "PP",  idealMinimum: 1, criticalLevel: 0 },
      { size: "P",   idealMinimum: 2, criticalLevel: 1 },
      { size: "M",   idealMinimum: 3, criticalLevel: 1 },
      { size: "G",   idealMinimum: 2, criticalLevel: 1 },
      { size: "GG",  idealMinimum: 1, criticalLevel: 0 },
      { size: "XGG", idealMinimum: 1, criticalLevel: 0 },
    ],
  },
  {
    key: "BLUSA|AD|FEM",
    label: "BLUSA · AD · FEM",
    sizes: ["PP", "P", "M", "G", "GG"],
    rules: [
      { size: "PP", idealMinimum: 2, criticalLevel: 1 },
      { size: "P",  idealMinimum: 3, criticalLevel: 1 },
      { size: "M",  idealMinimum: 4, criticalLevel: 2 },
      { size: "G",  idealMinimum: 3, criticalLevel: 1 },
      { size: "GG", idealMinimum: 2, criticalLevel: 1 },
    ],
  },
  {
    key: "BERMUDA JE|AD|MASC",
    label: "BERMUDA JE · AD · MASC",
    sizes: ["38", "40", "42", "44", "46"],
    rules: [
      { size: "38", idealMinimum: 2, criticalLevel: 1 },
      { size: "40", idealMinimum: 3, criticalLevel: 1 },
      { size: "42", idealMinimum: 3, criticalLevel: 1 },
      { size: "44", idealMinimum: 2, criticalLevel: 1 },
      { size: "46", idealMinimum: 1, criticalLevel: 0 },
    ],
  },
  {
    key: "CONJUNTO|INF|UNI",
    label: "CONJUNTO · INF · UNI",
    sizes: ["2", "4", "6", "8", "10", "12"],
    rules: [
      { size: "2",  idealMinimum: 2, criticalLevel: 1 },
      { size: "4",  idealMinimum: 3, criticalLevel: 1 },
      { size: "6",  idealMinimum: 3, criticalLevel: 1 },
      { size: "8",  idealMinimum: 3, criticalLevel: 1 },
      { size: "10", idealMinimum: 2, criticalLevel: 1 },
      { size: "12", idealMinimum: 1, criticalLevel: 0 },
    ],
  },
];

// ─── Mock stock (determinístico) ──────────────────────────────────────────────
// Em produção: api.getStockBySegment() retorna estoque real do ERP.

function hash(s: string): number {
  let h = 2166136261;
  for (let i = 0; i < s.length; i++) {
    h ^= s.charCodeAt(i);
    h = Math.imul(h, 16777619);
  }
  return Math.abs(h);
}

function getMockStock(segKey: string, size: string, store: StoreId): number {
  return hash(segKey + size + store) % 6; // 0–5
}

// ─── Derived replenishment data ───────────────────────────────────────────────

interface SizeReplenishment {
  size: string;
  stock: number;
  idealMinimum: number;
  toReplenish: number;
  isCritical: boolean;
}

interface StoreReplenishment {
  store: StoreId;
  sizes: SizeReplenishment[];
  totalToReplenish: number;
  criticalCount: number;
}

function computeReplenishment(seg: SavedSegment): StoreReplenishment[] {
  return STORES.map((store) => {
    const sizes: SizeReplenishment[] = seg.rules.map((rule) => {
      const stock = getMockStock(seg.key, rule.size, store);
      const toReplenish = Math.max(0, rule.idealMinimum - stock);
      const isCritical = stock <= rule.criticalLevel && rule.idealMinimum > 0;
      return { size: rule.size, stock, idealMinimum: rule.idealMinimum, toReplenish, isCritical };
    });
    return {
      store,
      sizes,
      totalToReplenish: sizes.reduce((s, r) => s + r.toReplenish, 0),
      criticalCount: sizes.filter((r) => r.isCritical).length,
    };
  });
}

// ─── Segment card ─────────────────────────────────────────────────────────────

function SegmentCard({ seg }: { seg: SavedSegment }) {
  const [open, setOpen] = useState(false);
  const { t } = useLanguage();
  const replenishment = computeReplenishment(seg);
  const totalAll = replenishment.reduce((s, r) => s + r.totalToReplenish, 0);
  const hasCritical = replenishment.some((r) => r.criticalCount > 0);

  return (
    <div className={`bg-white border rounded-lg overflow-hidden transition-colors ${hasCritical ? "border-red-200" : "border-gray-200"}`}>
      {/* Card header */}
      <button
        onClick={() => setOpen((v) => !v)}
        className="w-full flex items-center gap-4 px-5 py-4 hover:bg-gray-50 transition-colors text-left"
      >
        <span className="text-gray-300 shrink-0">
          {open ? <ChevronDown size={15} /> : <ChevronRight size={15} />}
        </span>

        <div className="flex-1 min-w-0">
          <p className="text-sm text-gray-900" style={{ fontWeight: 500 }}>{seg.label}</p>
          <p className="text-xs text-gray-400 mt-0.5">
            {t.seg_rep_grade} {seg.sizes.join(", ")}
          </p>
        </div>

        {/* Store pills */}
        <div className="flex items-center gap-2 shrink-0">
          {replenishment.map((r) => (
            <div key={r.store} className="flex flex-col items-center gap-0.5">
              <span className={`text-xs px-2 py-0.5 rounded font-mono ${
                r.criticalCount > 0
                  ? "bg-red-600 text-white"
                  : r.totalToReplenish > 0
                    ? "bg-amber-100 text-amber-800"
                    : "bg-gray-100 text-gray-500"
              }`} style={{ fontWeight: 600 }}>
                {r.store}
              </span>
              <span className={`text-xs ${
                r.criticalCount > 0 ? "text-red-600" : r.totalToReplenish > 0 ? "text-amber-700" : "text-gray-400"
              }`} style={{ fontWeight: r.totalToReplenish > 0 ? 600 : 400 }}>
                {r.totalToReplenish > 0 ? `+${r.totalToReplenish}` : "ok"}
              </span>
            </div>
          ))}

          <div className="ml-2 pl-2 border-l border-gray-200 flex flex-col items-center gap-0.5">
            <span className={`text-xs px-2 py-0.5 rounded ${
              totalAll > 0 ? "bg-gray-900 text-white" : "bg-gray-100 text-gray-400"
            }`} style={{ fontWeight: 600 }}>
              {totalAll > 0 ? `${totalAll} pç` : "—"}
            </span>
            <span className="text-xs text-gray-400">{t.seg_rep_total}</span>
          </div>
        </div>
      </button>

      {/* Expanded breakdown */}
      {open && (
        <div className="border-t border-gray-100">
          <div
            className="grid text-xs text-gray-400 uppercase tracking-wide bg-gray-50 border-b border-gray-100"
            style={{ gridTemplateColumns: `80px repeat(${seg.sizes.length}, 1fr)` }}
          >
            <div className="px-5 py-2">{t.store}</div>
            {seg.sizes.map((s) => (
              <div key={s} className="py-2 text-center">{s}</div>
            ))}
          </div>

          {replenishment.map((r) => (
            <div
              key={r.store}
              className="grid border-b border-gray-100 last:border-0"
              style={{ gridTemplateColumns: `80px repeat(${seg.sizes.length}, 1fr)` }}
            >
              <div className="px-5 py-3 flex items-center gap-2">
                <span className={`inline-flex items-center justify-center w-7 h-7 rounded text-xs ${
                  r.criticalCount > 0 ? "bg-red-600 text-white" : "bg-gray-900 text-white"
                }`} style={{ fontWeight: 700 }}>
                  {r.store}
                </span>
              </div>
              {r.sizes.map((sz) => (
                <div key={sz.size} className="py-3 text-center">
                  {sz.toReplenish > 0 ? (
                    <span className={`inline-block text-xs px-1.5 py-0.5 rounded ${
                      sz.isCritical ? "bg-red-100 text-red-700 font-semibold" : "bg-amber-50 text-amber-700"
                    }`} style={{ fontWeight: 600 }}>
                      +{sz.toReplenish}
                    </span>
                  ) : (
                    <span className="text-gray-300 text-xs">{sz.stock}</span>
                  )}
                </div>
              ))}
            </div>
          ))}

          <div className="px-5 py-2 bg-gray-50 border-t border-gray-100">
            <p className="text-xs text-gray-400">
              {t.seg_rep_legend} · <span className="text-red-600">{t.seg_rep_legend_critical}</span>
            </p>
          </div>
        </div>
      )}
    </div>
  );
}

// ─── Main view ────────────────────────────────────────────────────────────────

export function SegmentView() {
  const { t } = useLanguage();
  return (
    <div className="max-w-4xl mx-auto px-8 py-8 space-y-5">

      {/* Como funciona */}
      <div className="bg-white border border-gray-200 rounded-lg p-5">
        <p className="text-xs text-gray-400 uppercase tracking-wide mb-3">{t.how_it_works}</p>
        <div className="grid grid-cols-3 gap-6">
          <div className="flex gap-3">
            <span className="w-5 h-5 rounded-full bg-gray-900 text-white text-xs flex items-center justify-center shrink-0 mt-0.5" style={{ fontWeight: 700 }}>1</span>
            <div>
              <p className="text-sm text-gray-800" style={{ fontWeight: 500 }}>{t.seg_rep_step1_title}</p>
              <p className="text-xs text-gray-400 mt-0.5 leading-relaxed">
                {t.seg_rep_step1_desc}
              </p>
            </div>
          </div>
          <div className="flex gap-3">
            <span className="w-5 h-5 rounded-full bg-gray-900 text-white text-xs flex items-center justify-center shrink-0 mt-0.5" style={{ fontWeight: 700 }}>2</span>
            <div>
              <p className="text-sm text-gray-800" style={{ fontWeight: 500 }}>{t.seg_rep_step2_title}</p>
              <p className="text-xs text-gray-400 mt-0.5 leading-relaxed">
                {t.seg_rep_step2_desc}
              </p>
            </div>
          </div>
          <div className="flex gap-3">
            <span className="w-5 h-5 rounded-full bg-gray-900 text-white text-xs flex items-center justify-center shrink-0 mt-0.5" style={{ fontWeight: 700 }}>3</span>
            <div>
              <p className="text-sm text-gray-800" style={{ fontWeight: 500 }}>{t.seg_rep_step3_title}</p>
              <p className="text-xs text-gray-400 mt-0.5 leading-relaxed">
                {t.seg_rep_step3_desc}
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* Cards */}
      {SAVED_SEGMENTS.length === 0 ? (
        <div className="bg-white border border-gray-200 rounded-lg p-14 text-center">
          <PackageOpen size={28} className="mx-auto mb-3 text-gray-200" />
          <p className="text-sm text-gray-400">{t.seg_rep_empty_title}</p>
          <p className="text-xs text-gray-400 mt-1">{t.seg_rep_empty_desc}</p>
        </div>
      ) : (
        <div className="space-y-3">
          {SAVED_SEGMENTS.map((seg) => (
            <SegmentCard key={seg.key} seg={seg} />
          ))}
        </div>
      )}
    </div>
  );
}
