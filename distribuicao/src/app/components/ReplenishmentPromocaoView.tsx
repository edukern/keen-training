import { ChevronDown, ChevronRight } from "lucide-react";
import { useState } from "react";
import type { PromocaoItem } from "./PromocaoView";
import { useLanguage } from "../../i18n/LanguageContext";

// ─── Stores ───────────────────────────────────────────────────────────────────

type StoreId = "TC" | "NH" | "IG" | "OS";
const STORES: StoreId[] = ["TC", "NH", "IG", "OS"];

// ─── Deterministic replenishment generation ───────────────────────────────────

function hash(s: string): number {
  let h = 2166136261;
  for (let i = 0; i < s.length; i++) {
    h ^= s.charCodeAt(i);
    h = Math.imul(h, 16777619);
  }
  return Math.abs(h);
}

function getReplenishment(sku: string, store: StoreId): number {
  const h = hash(sku + store);
  return h % 10 < 5 ? (h % 4) + 1 : 0;
}

// ─── Group promotion items by product name ────────────────────────────────────

interface ProductGroup {
  name: string;
  category: string;
  skus: Array<{
    sku: string;
    replenishment: Record<StoreId, number>;
    total: number;
  }>;
  totalByStore: Record<StoreId, number>;
  grandTotal: number;
}

function groupItems(items: PromocaoItem[]): ProductGroup[] {
  const map = new Map<string, ProductGroup>();

  for (const item of items) {
    const key = item.name;
    if (!map.has(key)) {
      map.set(key, {
        name: item.name,
        category: item.category,
        skus: [],
        totalByStore: { TC: 0, NH: 0, IG: 0, OS: 0 },
        grandTotal: 0,
      });
    }
    const group = map.get(key)!;
    const replenishment = Object.fromEntries(
      STORES.map((s) => [s, getReplenishment(item.sku, s)])
    ) as Record<StoreId, number>;
    const total = STORES.reduce((sum, s) => sum + replenishment[s], 0);

    group.skus.push({ sku: item.sku, replenishment, total });
    STORES.forEach((s) => { group.totalByStore[s] += replenishment[s]; });
    group.grandTotal += total;
  }

  return Array.from(map.values());
}

// ─── Product group row ────────────────────────────────────────────────────────

function ProductGroupRow({ group }: { group: ProductGroup }) {
  const { t } = useLanguage();
  const [open, setOpen] = useState(false);

  return (
    <div className="bg-white border border-gray-200 rounded-lg overflow-hidden">
      {/* Header */}
      <button
        onClick={() => setOpen((v) => !v)}
        className="w-full flex items-center gap-4 px-5 py-4 hover:bg-gray-50 transition-colors text-left"
      >
        <span className="text-gray-300 shrink-0">
          {open ? <ChevronDown size={15} /> : <ChevronRight size={15} />}
        </span>
        <div className="flex-1 min-w-0">
          <p className="text-sm text-gray-900">{group.name}</p>
          <p className="text-xs text-gray-400 mt-0.5">{group.category} · {group.skus.length} SKU{group.skus.length !== 1 ? "s" : ""}</p>
        </div>

        {/* Per-store totals */}
        <div className="flex items-center gap-4 shrink-0">
          {STORES.map((store) => (
            <div key={store} className="flex flex-col items-center min-w-[40px]">
              <span className="text-xs text-gray-400">{store}</span>
              <span className={`text-sm ${group.totalByStore[store] === 0 ? "text-gray-300" : "text-gray-900"}`}
                style={{ fontWeight: group.totalByStore[store] > 0 ? 600 : 400 }}>
                {group.totalByStore[store]}
              </span>
            </div>
          ))}
          <div className="pl-4 border-l border-gray-100 text-right min-w-[48px]">
            <p className="text-xs text-gray-400">Total</p>
            <p className="text-base text-gray-900" style={{ fontWeight: 600 }}>{group.grandTotal}</p>
          </div>
        </div>
      </button>

      {/* Expanded: SKU breakdown */}
      {open && (
        <div className="border-t border-gray-100 overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="bg-gray-50">
                <th className="text-left px-5 py-2.5 text-xs text-gray-400 font-normal uppercase tracking-wide">SKU</th>
                {STORES.map((s) => (
                  <th key={s} className="py-2.5 text-center text-xs font-normal">
                    <span className="inline-flex items-center justify-center w-8 h-8 rounded bg-gray-900 text-white text-xs" style={{ fontWeight: 600 }}>
                      {s}
                    </span>
                  </th>
                ))}
                <th className="py-2.5 text-center text-xs text-gray-400 font-normal pr-5">{t.prom_rep_total}</th>
              </tr>
            </thead>
            <tbody>
              {group.skus.map(({ sku, replenishment, total }) => (
                <tr key={sku} className="border-t border-gray-100">
                  <td className="px-5 py-2.5">
                    <span className="font-mono text-xs text-gray-600 bg-gray-100 px-2 py-0.5 rounded">{sku}</span>
                  </td>
                  {STORES.map((s) => {
                    const qty = replenishment[s];
                    return (
                      <td key={s} className={`py-2.5 text-center text-sm ${qty === 0 ? "text-gray-300" : "text-gray-800"}`}
                        style={{ fontWeight: qty > 0 ? 500 : 400 }}>
                        {qty}
                      </td>
                    );
                  })}
                  <td className="py-2.5 text-center text-sm text-gray-700 pr-5" style={{ fontWeight: 600 }}>
                    {total}
                  </td>
                </tr>
              ))}

              {/* Group total row */}
              <tr className="border-t-2 border-gray-200 bg-gray-50">
                <td className="px-5 py-2.5 text-xs text-gray-400 uppercase tracking-wide">{t.prom_rep_total}</td>
                {STORES.map((s) => (
                  <td key={s} className="py-2.5 text-center text-sm text-gray-900" style={{ fontWeight: 600 }}>
                    {group.totalByStore[s]}
                  </td>
                ))}
                <td className="py-2.5 text-center text-sm text-gray-900 pr-5" style={{ fontWeight: 700 }}>
                  {group.grandTotal}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}

// ─── Main ─────────────────────────────────────────────────────────────────────

export function ReplenishmentPromocaoView({ items }: { items: PromocaoItem[] }) {
  const { t } = useLanguage();
  const groups = groupItems(items);

  const totalByStore = STORES.reduce((acc, s) => ({
    ...acc,
    [s]: groups.reduce((sum, g) => sum + g.totalByStore[s], 0),
  }), {} as Record<StoreId, number>);

  const grandTotal = groups.reduce((sum, g) => sum + g.grandTotal, 0);

  if (items.length === 0) {
    return (
      <div className="max-w-4xl mx-auto px-8 py-8">
        <div className="bg-white border border-gray-200 rounded-lg p-14 text-center">
          <p className="text-gray-400">{t.prom_rep_empty}</p>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-4xl mx-auto px-8 py-8 space-y-5">

      {/* How it works */}
      <div className="bg-white border border-gray-200 rounded-lg p-5">
        <p className="text-xs text-gray-400 uppercase tracking-wide mb-3">{t.how_it_works}</p>
        <div className="grid grid-cols-3 gap-6">
          <div className="flex gap-3">
            <span className="w-5 h-5 rounded-full bg-gray-900 text-white text-xs flex items-center justify-center shrink-0 mt-0.5" style={{ fontWeight: 700 }}>1</span>
            <div>
              <p className="text-sm text-gray-800" style={{ fontWeight: 500 }}>{t.prom_rep_step1_title}</p>
              <p className="text-xs text-gray-400 mt-0.5 leading-relaxed">{t.prom_rep_step1_desc}</p>
            </div>
          </div>
          <div className="flex gap-3">
            <span className="w-5 h-5 rounded-full bg-gray-900 text-white text-xs flex items-center justify-center shrink-0 mt-0.5" style={{ fontWeight: 700 }}>2</span>
            <div>
              <p className="text-sm text-gray-800" style={{ fontWeight: 500 }}>{t.prom_rep_step2_title}</p>
              <p className="text-xs text-gray-400 mt-0.5 leading-relaxed">{t.prom_rep_step2_desc}</p>
            </div>
          </div>
          <div className="flex gap-3">
            <span className="w-5 h-5 rounded-full bg-gray-900 text-white text-xs flex items-center justify-center shrink-0 mt-0.5" style={{ fontWeight: 700 }}>3</span>
            <div>
              <p className="text-sm text-gray-800" style={{ fontWeight: 500 }}>{t.prom_rep_step3_title}</p>
              <p className="text-xs text-gray-400 mt-0.5 leading-relaxed">{t.prom_rep_step3_desc}</p>
            </div>
          </div>
        </div>
      </div>

      {/* Summary header */}
      <div className="bg-white border border-gray-200 rounded-lg px-5 py-4">
        <p className="text-xs text-gray-400 uppercase tracking-wide mb-3">{t.prom_rep_by_store}</p>
        <div className="flex items-center gap-8">
          {STORES.map((store) => (
            <div key={store} className="flex flex-col">
              <span className="text-xs text-gray-400 mb-0.5">{store}</span>
              <span className="text-2xl text-gray-900" style={{ fontWeight: 700 }}>{totalByStore[store]}</span>
              <span className="text-xs text-gray-400">{t.prom_rep_pieces}</span>
            </div>
          ))}
          <div className="ml-auto pl-8 border-l border-gray-200 flex flex-col items-end">
            <span className="text-xs text-gray-400 mb-0.5">{t.prom_rep_grand_total}</span>
            <span className="text-3xl text-gray-900" style={{ fontWeight: 700 }}>{grandTotal}</span>
            <span className="text-xs text-gray-400">{t.prom_rep_pieces}</span>
          </div>
        </div>
      </div>

      {/* Product groups */}
      <div className="space-y-2">
        {groups.map((group) => (
          <ProductGroupRow key={group.name} group={group} />
        ))}
      </div>
    </div>
  );
}
