import { useState, useMemo } from "react";
import { ChevronDown, ChevronRight, X } from "lucide-react";
import { useLanguage } from "../../i18n/LanguageContext";

// ─── Types ────────────────────────────────────────────────────────────────────

type StoreId = "TC" | "NH" | "IG" | "OS";
type StoreFilter = "GERAL" | StoreId;

const STORES: StoreId[] = ["TC", "NH", "IG", "OS"];
const STORE_FILTERS: StoreFilter[] = ["GERAL", "TC", "NH", "IG", "OS"];

interface StoreReplenishment {
  store: StoreId;
  qty: number;
}

interface SlotData {
  row: number;
  address: string;
  productCode: string;
  productName: string;
  size: string;
  replenishments: StoreReplenishment[];
}

interface ColumnData {
  letter: string;
  slots: SlotData[];
}

// ─── Corridor layout ──────────────────────────────────────────────────────────

const COLS_SHORT  = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O"];
const COLS_LONG   = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R"];
const COLS_MICRO6 = ["A","B","C","D","E","F"];
const COLS_MICRO5 = ["A","B","C","D","E"];

const CORRIDOR_CONFIG: Record<number, { columns: string[]; rows: number }> = {
  1:  { columns: COLS_SHORT,  rows: 6 },
  2:  { columns: COLS_SHORT,  rows: 6 },
  3:  { columns: COLS_SHORT,  rows: 6 },
  4:  { columns: COLS_SHORT,  rows: 6 },
  5:  { columns: COLS_SHORT,  rows: 6 },
  6:  { columns: COLS_SHORT,  rows: 6 },
  7:  { columns: COLS_SHORT,  rows: 6 },
  8:  { columns: COLS_SHORT,  rows: 6 },
  9:  { columns: COLS_SHORT,  rows: 6 },
  10: { columns: COLS_SHORT,  rows: 6 },
  11: { columns: COLS_LONG,   rows: 6 },
  12: { columns: COLS_LONG,   rows: 6 },
  13: { columns: COLS_LONG,   rows: 6 },
  14: { columns: COLS_LONG,   rows: 6 },
  15: { columns: COLS_LONG,   rows: 6 },
  16: { columns: COLS_LONG,   rows: 6 },
  17: { columns: COLS_LONG,   rows: 6 },
  18: { columns: COLS_LONG,   rows: 6 },
  19: { columns: COLS_MICRO6, rows: 3 },
  20: { columns: COLS_MICRO6, rows: 3 },
  21: { columns: COLS_MICRO5, rows: 3 },
  22: { columns: COLS_MICRO5, rows: 3 },
};

// ─── Mock product catalog ─────────────────────────────────────────────────────

const PRODUCTS = [
  { code: "CAL-032", name: "Calça Jeans Skinny AD MASC", sizes: ["36","38","40","42","44","46","48"] },
  { code: "CAL-033", name: "Calça Jeans Skinny AD FEM",  sizes: ["34","36","38","40","42","44"] },
  { code: "CAL-041", name: "Calça Jeans Slim JUV MASC",  sizes: ["10","12","14","16"] },
  { code: "CAM-001", name: "Camisa Social Slim AD MASC",  sizes: ["PP","P","M","G","GG","XGG"] },
  { code: "CAM-002", name: "Camisa Casual Linho AD UNI",  sizes: ["P","M","G","GG"] },
  { code: "BLA-007", name: "Blusa Tricô Texturizado AD FEM", sizes: ["PP","P","M","G","GG"] },
  { code: "BLA-012", name: "Blusa Malha Plus EX FEM",    sizes: ["EXG","EXGG","EXGGG"] },
  { code: "VES-015", name: "Vestido Casual Floral AD FEM",sizes: ["P","M","G","GG"] },
  { code: "CNJ-088", name: "Conjunto Moletom INF UNI",   sizes: ["2","4","6","8","10"] },
  { code: "BER-021", name: "Bermuda Jeans AD MASC",      sizes: ["38","40","42","44","46"] },
  { code: "MAC-003", name: "Macacão Linho AD FEM",       sizes: ["P","M","G"] },
  { code: "SHO-014", name: "Short Alfaiataria AD FEM",   sizes: ["34","36","38","40","42"] },
];

// ─── Deterministic data generation ───────────────────────────────────────────

function hash(s: string): number {
  let h = 2166136261;
  for (let i = 0; i < s.length; i++) {
    h ^= s.charCodeAt(i);
    h = Math.imul(h, 16777619);
  }
  return Math.abs(h);
}

function generateSlot(corridor: number, col: string, row: number): SlotData {
  const address = `${corridor}-${col}${row}`;
  const seed = hash(address);
  const prod = PRODUCTS[seed % PRODUCTS.length];
  const size = prod.sizes[(seed >> 3) % prod.sizes.length];
  const needsReplenishment = (seed % 100) < 35;
  const replenishments: StoreReplenishment[] = [];
  if (needsReplenishment) {
    STORES.forEach((store) => {
      const s = hash(address + store);
      if (s % 10 < 6) replenishments.push({ store, qty: 1 + (s % 5) });
    });
    if (replenishments.length === 0)
      replenishments.push({ store: STORES[seed % STORES.length], qty: 1 + (seed % 4) });
  }
  return { row, address, productCode: prod.code, productName: prod.name, size, replenishments };
}

function generateCorridor(corridor: number): ColumnData[] {
  const { columns, rows } = CORRIDOR_CONFIG[corridor];
  return columns.map((letter) => ({
    letter,
    slots: Array.from({ length: rows }, (_, i) => generateSlot(corridor, letter, i + 1)),
  }));
}

function slotAlertForFilter(slot: SlotData, filter: StoreFilter): boolean {
  if (filter === "GERAL") return slot.replenishments.length > 0;
  return slot.replenishments.some((r) => r.store === filter);
}

function columnAlertsFiltered(col: ColumnData, filter: StoreFilter): number {
  return col.slots.filter((s) => slotAlertForFilter(s, filter)).length;
}

function columnPiecesFiltered(col: ColumnData, filter: StoreFilter): number {
  return col.slots.reduce((sum, s) => {
    const reps = filter === "GERAL"
      ? s.replenishments
      : s.replenishments.filter((r) => r.store === filter);
    return sum + reps.reduce((a, r) => a + r.qty, 0);
  }, 0);
}

// Pre-compute alert counts per filter × corridor
const CORRIDOR_ALERT_BY_FILTER: Record<StoreFilter, Record<number, number>> = Object.fromEntries(
  STORE_FILTERS.map((filter) => [
    filter,
    Object.fromEntries(
      Array.from({ length: 22 }, (_, i) => i + 1).map((n) => [
        n,
        generateCorridor(n).reduce((sum, col) => sum + columnAlertsFiltered(col, filter), 0),
      ])
    ),
  ])
) as Record<StoreFilter, Record<number, number>>;

// ─── Slot row (expandable) ────────────────────────────────────────────────────

function SlotRow({ slot, filter }: { slot: SlotData; filter: StoreFilter }) {
  const [open, setOpen] = useState(false);
  const hasAlert = slotAlertForFilter(slot, filter);

  const visibleStores = filter === "GERAL" ? STORES : ([filter] as StoreId[]);
  const total = slot.replenishments
    .filter((r) => filter === "GERAL" || r.store === filter)
    .reduce((s, r) => s + r.qty, 0);

  if (!hasAlert) {
    return (
      <div className="flex items-center gap-3 px-4 py-2.5 border-b border-gray-100 last:border-0 opacity-35">
        <span className="font-mono text-xs text-gray-400 w-14 shrink-0">{slot.address}</span>
        <span className="text-xs text-gray-400 flex-1 truncate">{slot.productName}</span>
        <span className="text-xs text-gray-300 font-mono">{slot.size}</span>
        <span className="text-xs text-gray-300">ok</span>
      </div>
    );
  }

  return (
    <div className="border-b border-gray-100 last:border-0">
      <button
        onClick={() => setOpen((v) => !v)}
        className="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition-colors text-left"
      >
        <span className="text-gray-300 shrink-0">
          {open ? <ChevronDown size={13} /> : <ChevronRight size={13} />}
        </span>
        <span className="font-mono text-xs text-gray-500 w-14 shrink-0">{slot.address}</span>
        <div className="flex-1 min-w-0">
          <span className="text-sm text-gray-800 truncate block">{slot.productName}</span>
        </div>
        <span className="font-mono text-xs text-gray-400 shrink-0">{slot.size}</span>
        <span className="text-sm text-gray-900 shrink-0 ml-2" style={{ fontWeight: 600 }}>
          {total} pç
        </span>
      </button>

      {open && (
        <div className="mx-4 mb-3 border border-gray-200 rounded-lg overflow-hidden">
          <div className="grid bg-gray-50 border-b border-gray-200" style={{ gridTemplateColumns: `1fr repeat(${visibleStores.length}, 56px) 56px` }}>
            <div className="px-3 py-2 text-xs text-gray-400 uppercase tracking-wide">Loja</div>
            {visibleStores.map((s) => (
              <div key={s} className="py-2 text-center text-xs text-gray-500 font-mono">{s}</div>
            ))}
            <div className="py-2 text-center text-xs text-gray-400">Total</div>
          </div>
          <div className="grid" style={{ gridTemplateColumns: `1fr repeat(${visibleStores.length}, 56px) 56px` }}>
            <div className="px-3 py-2.5 flex items-center gap-2">
              <span className="w-8 h-8 rounded bg-gray-900 text-white text-xs flex items-center justify-center" style={{ fontWeight: 600 }}>
                {slot.size}
              </span>
              <span className="text-xs text-gray-400 font-mono">{slot.productCode}</span>
            </div>
            {visibleStores.map((store) => {
              const rep = slot.replenishments.find((r) => r.store === store);
              return (
                <div key={store} className="py-2.5 text-center text-sm">
                  {rep ? (
                    <span className="text-gray-900" style={{ fontWeight: 600 }}>{rep.qty}</span>
                  ) : (
                    <span className="text-gray-300">—</span>
                  )}
                </div>
              );
            })}
            <div className="py-2.5 text-center text-sm text-gray-900" style={{ fontWeight: 700 }}>
              {total}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

// ─── Main ─────────────────────────────────────────────────────────────────────

export function CorridorReport() {
  const { t } = useLanguage();
  const [storeFilter, setStoreFilter]       = useState<StoreFilter>("GERAL");
  const [selectedCorridor, setSelectedCorridor] = useState<number | null>(null);
  const [selectedColumn, setSelectedColumn]     = useState<string | null>(null);

  const columns = useMemo(
    () => (selectedCorridor ? generateCorridor(selectedCorridor) : []),
    [selectedCorridor]
  );

  function pickCorridor(n: number | null) {
    setSelectedCorridor(n);
    setSelectedColumn(null);
  }

  function pickColumn(letter: string) {
    setSelectedColumn((prev) => (prev === letter ? null : letter));
  }

  const activeColumn = columns.find((c) => c.letter === selectedColumn) ?? null;
  const alertCounts  = CORRIDOR_ALERT_BY_FILTER[storeFilter];

  return (
    <div className="max-w-4xl mx-auto px-8 py-8 space-y-5">

      {/* How it works */}
      <div className="bg-white border border-gray-200 rounded-lg p-5">
        <p className="text-xs text-gray-400 uppercase tracking-wide mb-3">{t.how_it_works}</p>
        <div className="grid grid-cols-3 gap-6">
          <div className="flex gap-3">
            <span className="w-5 h-5 rounded-full bg-gray-900 text-white text-xs flex items-center justify-center shrink-0 mt-0.5" style={{ fontWeight: 700 }}>1</span>
            <div>
              <p className="text-sm text-gray-800" style={{ fontWeight: 500 }}>{t.prod_rep_step1_title}</p>
              <p className="text-xs text-gray-400 mt-0.5 leading-relaxed">{t.prod_rep_step1_desc}</p>
            </div>
          </div>
          <div className="flex gap-3">
            <span className="w-5 h-5 rounded-full bg-gray-900 text-white text-xs flex items-center justify-center shrink-0 mt-0.5" style={{ fontWeight: 700 }}>2</span>
            <div>
              <p className="text-sm text-gray-800" style={{ fontWeight: 500 }}>{t.prod_rep_step2_title}</p>
              <p className="text-xs text-gray-400 mt-0.5 leading-relaxed">{t.prod_rep_step2_desc}</p>
            </div>
          </div>
          <div className="flex gap-3">
            <span className="w-5 h-5 rounded-full bg-gray-900 text-white text-xs flex items-center justify-center shrink-0 mt-0.5" style={{ fontWeight: 700 }}>3</span>
            <div>
              <p className="text-sm text-gray-800" style={{ fontWeight: 500 }}>{t.prod_rep_step3_title}</p>
              <p className="text-xs text-gray-400 mt-0.5 leading-relaxed">{t.prod_rep_step3_desc}</p>
            </div>
          </div>
        </div>
      </div>

      {/* ── Store filter ── */}
      <div className="bg-white border border-gray-200 rounded-lg px-5 py-4 flex items-center gap-4">
        <p className="text-xs text-gray-400 uppercase tracking-wide shrink-0">{t.prod_rep_view}</p>
        <div className="relative">
          <select
            value={storeFilter}
            onChange={(e) => {
              setStoreFilter(e.target.value as StoreFilter);
              setSelectedCorridor(null);
              setSelectedColumn(null);
            }}
            className="appearance-none border border-gray-200 rounded-md px-3 py-1.5 text-sm text-gray-800 bg-white focus:outline-none focus:border-gray-400 cursor-pointer pr-7"
          >
            {STORE_FILTERS.map((f) => (
              <option key={f} value={f}>{f === "GERAL" ? t.corridor_general : f}</option>
            ))}
          </select>
          <ChevronDown size={13} className="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" />
        </div>
      </div>

      {/* ── Corridor grid ── */}
      <div className="bg-white border border-gray-200 rounded-lg p-5">
        <div className="flex items-center justify-between mb-3">
          <p className="text-xs text-gray-400 uppercase tracking-wide">{t.prod_rep_select_corridor}</p>
          {selectedCorridor && (
            <button
              onClick={() => pickCorridor(null)}
              className="flex items-center gap-1.5 text-xs text-gray-400 hover:text-gray-700 border border-gray-200 rounded-md px-2.5 py-1.5 hover:bg-gray-50 transition-colors"
            >
              <X size={12} />
              {t.prod_rep_close_corridor}
            </button>
          )}
        </div>
        <div className="flex flex-wrap gap-2">
          {Array.from({ length: 22 }, (_, i) => i + 1).map((n) => {
            const alerts    = alertCounts[n];
            const isSelected = selectedCorridor === n;
            const hasAlert   = alerts > 0;
            return (
              <button
                key={n}
                onClick={() => pickCorridor(isSelected ? null : n)}
                className={`flex flex-col items-center px-3 py-2 rounded-md border transition-colors min-w-[52px] ${
                  isSelected
                    ? "bg-gray-900 text-white border-gray-900"
                    : hasAlert
                      ? "border-gray-200 text-gray-900 hover:border-gray-400 hover:bg-gray-50"
                      : "border-gray-100 text-gray-300 hover:border-gray-200"
                }`}
              >
                <span className="text-xl font-bold leading-tight">{n}</span>
                {hasAlert ? (
                  <span className="text-[10px] font-normal leading-tight mt-0.5 text-gray-400">
                    {alerts}
                  </span>
                ) : (
                  <span className="text-xs leading-tight mt-0.5 opacity-0">0</span>
                )}
              </button>
            );
          })}
        </div>
      </div>

      {/* ── Column letter grid ── */}
      {selectedCorridor && (
        <div className="bg-white border border-gray-200 rounded-lg p-5">
          <p className="text-xs text-gray-400 uppercase tracking-wide mb-3">{t.prod_rep_select_column}</p>
          <div className="flex flex-wrap gap-2">
            {columns.map((col) => {
              const alerts    = columnAlertsFiltered(col, storeFilter);
              const isSelected = selectedColumn === col.letter;
              const hasAlert   = alerts > 0;
              return (
                <button
                  key={col.letter}
                  onClick={() => pickColumn(col.letter)}
                  className={`flex flex-col items-center px-3 py-2 rounded-md border transition-colors min-w-[52px] ${
                    isSelected
                      ? "bg-gray-900 text-white border-gray-900"
                      : hasAlert
                        ? "border-gray-200 text-gray-900 hover:border-gray-400 hover:bg-gray-50"
                        : "border-gray-100 text-gray-300 hover:border-gray-200"
                  }`}
                >
                  <span className="text-xl font-black leading-tight">{col.letter}</span>
                  {hasAlert ? (
                    <span className="text-[10px] font-normal leading-tight mt-0.5 text-gray-400">
                      {alerts}
                    </span>
                  ) : (
                    <span className="text-xs leading-tight mt-0.5 opacity-0">0</span>
                  )}
                </button>
              );
            })}
          </div>
        </div>
      )}

      {/* ── Column slot detail ── */}
      {activeColumn && (
        <div className="bg-white border border-gray-200 rounded-lg overflow-hidden">
          <div className="flex items-center gap-3 px-5 py-3.5 border-b border-gray-100 bg-gray-50">
            <span className="w-8 h-8 rounded bg-gray-900 text-white flex items-center justify-center text-sm" style={{ fontWeight: 700 }}>
              {activeColumn.letter}
            </span>
            <div>
              <p className="text-sm text-gray-800">
                Corredor {selectedCorridor} · Coluna {activeColumn.letter}
              </p>
              <p className="text-xs text-gray-400 mt-0.5">
                {columnAlertsFiltered(activeColumn, storeFilter)} slot{columnAlertsFiltered(activeColumn, storeFilter) !== 1 ? "s" : ""} com alerta
                {" · "}
                {columnPiecesFiltered(activeColumn, storeFilter)} peça{columnPiecesFiltered(activeColumn, storeFilter) !== 1 ? "s" : ""} a repor
              </p>
            </div>
          </div>

          <div>
            {activeColumn.slots.map((slot) => (
              <SlotRow key={slot.address} slot={slot} filter={storeFilter} />
            ))}
          </div>
        </div>
      )}

      {!selectedCorridor && (
        <div className="bg-white border border-gray-200 rounded-lg p-14 text-center">
          <p className="text-gray-400">{t.no_replenishments}</p>
        </div>
      )}

      {selectedCorridor && !selectedColumn && (
        <div className="bg-white border border-gray-200 rounded-lg p-10 text-center">
          <p className="text-gray-400">{t.prod_rep_select_column}.</p>
        </div>
      )}
    </div>
  );
}
