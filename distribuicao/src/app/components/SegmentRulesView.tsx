import { useState } from "react";
import { Save, RotateCcw, CheckCircle2, AlertTriangle, Info } from "lucide-react";
import { SearchableSelect } from "./SearchableSelect";
import { NumberInput } from "./NumberInput";
import { useLanguage } from "../../i18n/LanguageContext";

// ─── Constants ────────────────────────────────────────────────────────────────

const TIPOS = [
  "ALMOFADA", "ALMOFADA DE CADEIRA", "ANEL", "ARVORE",
  "BABADOR", "BAG", "BALDE", "BAND", "BANHEIRA", "BASICA", "BEBE PASSEIO",
  "BERCO", "BERMUDA", "BERMUDA JE", "BERMUDA MO", "BERMUDA SU",
  "BIQUINI", "BLAZER", "BLUSA", "BLUSAO", "BLUSAO MO", "BLUSAO TRI",
  "BLUSINHA", "BODY", "BOINA", "BOLA", "BOLERO", "BOLSA", "BOLSA TOALHA",
  "BONE", "BORBOLETA", "BORRACHINHA", "BOTA", "BOXER", "BRINCO",
  "CABIDE", "CACHECOL", "CADARCO", "CALCA", "CALCA JE", "CALCA MO",
  "CALCA PLASTICA", "CALCA TRI", "CALCAO", "CALCINHA", "CALCOLA",
  "CAMISA", "CAMISA POLO", "CAMISETA", "CAMISETA PI", "CAMISETE",
  "CAMISOLA", "CANELEIRA", "CANGURU", "CAPA ALMOFADA", "CAPA CADEIRA",
  "CAPA CHUVA", "CAPA COLCHAO", "CAPA EDREDOM", "CAPA SOFA", "CAPRI",
  "CARDIGAN", "CARRINHO DE BEBE", "CARTEIRA", "CASACO", "CASACO MO",
  "CASACO TRI", "CASAQUINHO", "CENTRO MESA", "CEROULA", "CHAPEU",
  "CHAVEIRO", "CHINELO", "CHINELO PELO", "CHUTEIRA", "CHUTEIRA SOCIETY",
  "CINTA CALCA", "CINTO", "COBERTOR", "COBREDOM", "COBRELEITO",
  "COLAR", "COLCHA", "COLCHONETE", "COLETE", "CONJ.", "CONJ. BATIZADO",
  "CONJ. LING", "CONJ. RE", "CONJUNTO", "COPO", "CORSARIO", "CORTINA",
  "CUECA", "CUEIRO",
  "DET.TECIDO",
  "EDREDOM", "EMBALAGEM PRESENTE", "ENCHIMENTO", "ESCOVA PARA MAMADEIRA",
  "ESFREGAO", "ESPONJA BANHO", "ESTOJO", "EXTENSOR",
  "FAIXA", "FAIXA UMBILICAL", "FIO", "FITA DE SUSPENSAO", "FOAM ROLLER",
  "FRALDA", "FRASQUEIRA", "FRONHA",
  "GALOCHA", "GARGANTILHA", "GARRAFA", "GOLA", "GRAVATA",
  "GUARDA CHUVA", "GUARDANAPO",
  "JAQUETA", "JAQUETA JE", "JG AMERICANO", "JG BANHEIRO", "JG COLCHA",
  "JG COZINHA", "JG LENCOL", "JG ROUPA DE CAMA", "JG TOALHA",
  "KIMONO", "KIT", "KIT ALIMENTAÇÃO", "KIT BABADOR", "KIT BERCO",
  "KIT BOLSA", "KIT BOXER", "KIT CALCINHA", "KIT CUECA", "KIT ESCOVA",
  "KIT FIO", "KIT FRONHA", "KIT LANCHEIRA", "KIT MASCARA", "KIT MEIA",
  "KIT MOCHILA", "KIT PANO COPA", "KIT SACOLA BEBE", "KIT T HIGIENICA",
  "KIT TANGAO", "KIT TAPETE", "KIT TESOURA", "KLOG",
  "LANCHEIRA", "LEG", "LENCO", "LENCOL", "LEVA TUDO", "LUVA",
  "MACACAO", "MAIO", "MALA RODINHA", "MAMADEIRA", "MANTA", "MASCARA",
  "MASSAGEADOR DENTAL", "MEIA", "MEIA CALCA", "MIJAO", "MINI BAND",
  "MOCASSIM", "MOCHILA", "MOCHILA CAMPING", "MODELADOR", "MORDEDOR",
  "MOSQUITEIRO", "MULE",
  "NECESSAIRE", "NIQUELEIRA",
  "PAGAO", "PALMILHA", "PANO COPA", "PANO MULTIUSO", "PANTACOURT",
  "PANTUFA", "PAPETE", "PASTA", "PIJAMA", "POCHETE", "POLAINA", "POLO",
  "PONCHO", "PORTA", "PORTA ALCOOL", "PORTA BEBE", "PORTA CAMERA",
  "PORTA CD", "PORTA CELULAR", "PORTA CHUPETA", "PORTA TRAVESSEIRO",
  "PRENDEDOR BICO", "PRENDEDOR CABELO", "PRENDEDOR GRAVATA",
  "PROTETOR BERCO", "PROTETOR COLCHAO", "PROTETOR DE MAMILO",
  "PROTETOR DE SOFA", "PROTETOR PORTA", "PROTETOR TRAVESSEIRO",
  "PULA CORDA", "PULSEIRA",
  "RABICO", "RASTEIRINHA", "REGATA", "REGATINHA", "RELOGIO", "ROUPAO",
  "SACO LAVA ROUPAS", "SACOLA BEBE", "SACOLA FUTEBOL", "SACOLA VIAGEM",
  "SAIA", "SAIA BOX", "SAIDA PRAIA", "SALDO", "SAMBA CANCAO",
  "SANDALIA", "SAPATENIS", "SAPATILHA", "SAPATINHO", "SAPATO",
  "SEGURA BEBE", "SHORT", "SHORT JE", "SHORT MO", "SHORT SAIA",
  "SNEAKER", "SOMBRINHA", "SOUTIEN", "SUETER", "SUNGA", "SUSPENSORIO",
  "SUTIA",
  "T ACADEMIA", "T BANHO", "T HIGIENICA", "T MESA", "T PISO", "T ROSTO",
  "TAMANCO", "TANGAO", "TAPETE", "TENIS", "TERMOMETRO", "TERNO",
  "TIARA", "TIP TOP", "TOP", "TORNOZELEIRA", "TOUCA", "TOUCA DE BANHO",
  "TRAVESSEIRO", "TRILHO MESA", "TROCADOR", "TURBANTE",
  "UMIDIFICADOR",
  "VALE PRESENTE", "VARAO", "VESTIDO", "VISEIRA",
];

const CLASSES = ["PP", "BB", "INF", "JUV", "AD", "EX"];
const GENEROS = ["MASC", "FEM", "UNI"];

const MARCAS = [
  "TANISE", "FAKINI", "ROSA BELLA", "PEGADA", "LUPO", "LUNENDER",
  "ARAMIS", "COLCCI", "ELLUS", "FORUM", "HERING", "JOHN JOHN",
  "LANÇA PARFUM", "OSKLEN", "RESERVA", "ANIMALE", "FARM", "SHOULDER",
  "TNG", "CANTÃO", "MIXED", "DUDALINA", "MALWEE", "MARISOL",
  "ROVITEX", "KAUANA", "BRANDILI", "ELIAN", "ALAKAZOO", "ALPHABETO",
  "KAPÍ", "MILON", "CARINHOSO", "PAMPILI", "MELISSA", "GRENDENE",
  "IPANEMA", "RIDER", "BEIRA RIO", "VIZZANO", "SPEEDO", "FILA BRASIL",
  "TOPPER", "OLYMPIKUS", "PENALTY", "RAINHA", "VITORIA", "DIADORA BRASIL",
  "KAPPA BRASIL", "POOL", "LACOSTE BRASIL", "LE LIS BLANC", "MARIA FILÓ",
  "ZINCO", "SALINAS", "MORENA ROSA", "IÓDICE",
];

const COLECOES = [
  "2026/1", "2025/2", "2025/1", "2024/2", "2024/1",
  "2023/2", "2023/1", "2022/2", "2022/1", "2021/2",
];

// ─── Stores ───────────────────────────────────────────────────────────────────

const STORES = ["TC", "NH", "IG", "OS"] as const;
type StoreId = typeof STORES[number];

const STORE_NAMES: Record<StoreId, string> = {
  TC: "Três Coroas",
  NH: "Nova Hartz",
  IG: "Igrejinha",
  OS: "Osório",
};

// ─── Size grid logic ──────────────────────────────────────────────────────────

const ALPHA_AD   = ["PP", "P", "M", "G", "GG", "XGG"];
const ALPHA_JUV  = ["10", "12", "14", "16"];
const ALPHA_INF  = ["2", "4", "6", "8", "10", "12"];
const ALPHA_BB   = ["RN", "1-3M", "3-6M", "6-9M", "9-12M"];
const ALPHA_PP   = ["RN", "P", "M"];
const NUM_AD_FEM = ["34", "36", "38", "40", "42", "44"];
const NUM_AD_MSC = ["36", "38", "40", "42", "44", "46", "48"];
const NUM_JUV    = ["10", "12", "14", "16"];
const NUM_INF    = ["4", "6", "8", "10", "12", "14"];
const EX_SIZES   = ["EXG", "EXGG", "EXGGG"];

const NUMERIC_TIPOS = new Set([
  "CALÇA JEANS", "CALÇA SARJA", "CALÇA ALFAIATARIA",
  "BERMUDA JEANS", "SHORT ALFAIATARIA",
]);

function getSizes(tipo: string, classe: string, genero: string): string[] {
  const isNumeric = NUMERIC_TIPOS.has(tipo);
  if (classe === "EX") return EX_SIZES;
  if (classe === "BB") return ALPHA_BB;
  if (classe === "PP") return ALPHA_PP;
  if (classe === "INF") return isNumeric ? NUM_INF : ALPHA_INF;
  if (classe === "JUV") return isNumeric ? NUM_JUV : ALPHA_JUV;
  if (classe === "AD") {
    if (isNumeric) return genero === "FEM" ? NUM_AD_FEM : NUM_AD_MSC;
    return ALPHA_AD;
  }
  return ALPHA_AD;
}

// ─── Types ────────────────────────────────────────────────────────────────────

interface SizeRule {
  size: string;
  idealMinimum: number;
  criticalLevel: number;
}

interface SegmentKey {
  tipo: string;
  classe: string;
  genero: string;
}

type SavedSegmentRules = Record<string, SizeRule[]>;

// ─── Mock saved segment rules ─────────────────────────────────────────────────

const MOCK_SAVED: SavedSegmentRules = {
  "CALÇA JEANS|AD|FEM": [
    { size: "34", idealMinimum: 2, criticalLevel: 1 },
    { size: "36", idealMinimum: 3, criticalLevel: 1 },
    { size: "38", idealMinimum: 4, criticalLevel: 2 },
    { size: "40", idealMinimum: 3, criticalLevel: 1 },
    { size: "42", idealMinimum: 2, criticalLevel: 1 },
    { size: "44", idealMinimum: 1, criticalLevel: 0 },
  ],
  "CAMISA|AD|MASC": [
    { size: "PP",  idealMinimum: 1, criticalLevel: 0 },
    { size: "P",   idealMinimum: 2, criticalLevel: 1 },
    { size: "M",   idealMinimum: 3, criticalLevel: 1 },
    { size: "G",   idealMinimum: 2, criticalLevel: 1 },
    { size: "GG",  idealMinimum: 1, criticalLevel: 0 },
    { size: "XGG", idealMinimum: 1, criticalLevel: 0 },
  ],
};

function makeKey(s: SegmentKey) {
  return `${s.tipo}|${s.classe}|${s.genero}`;
}

function buildBlankRules(sizes: string[]): SizeRule[] {
  return sizes.map((size) => ({ size, idealMinimum: 0, criticalLevel: 0 }));
}

// ─── Rules table ──────────────────────────────────────────────────────────────

function RulesTable({
  rules,
  onUpdate,
  headerSlot,
}: {
  rules: SizeRule[];
  onUpdate: (i: number, field: keyof SizeRule, v: number) => void;
  headerSlot?: React.ReactNode;
}) {
  const { t } = useLanguage();
  const hasErrors = rules.some((r) => r.idealMinimum > 0 && r.criticalLevel >= r.idealMinimum);

  return (
    <div className="bg-white border border-gray-200 rounded-lg overflow-hidden">
      <div
        className="grid border-b border-gray-100 bg-gray-50"
        style={{ gridTemplateColumns: `180px repeat(${rules.length}, minmax(80px, 1fr))` }}
      >
        <div className="px-5 py-3">{headerSlot}</div>
        {rules.map((rule) => {
          const isColError = rule.idealMinimum > 0 && rule.criticalLevel >= rule.idealMinimum;
          return (
            <div key={rule.size} className="py-3 flex justify-center">
              <span
                className={`inline-flex items-center justify-center w-9 h-9 rounded text-sm ${
                  isColError ? "bg-red-600 text-white" : "bg-gray-900 text-white"
                }`}
                style={{ fontWeight: 600 }}
              >
                {rule.size}
              </span>
            </div>
          );
        })}
      </div>

      {([
        { label: t.ideal_minimum,  sub: t.ideal_minimum_sub_segment, field: "idealMinimum"  as const, isErrorField: false },
        { label: t.critical_level, sub: t.critical_level_sub,        field: "criticalLevel" as const, isErrorField: true  },
      ]).map((row) => (
        <div
          key={row.field}
          className="grid border-b border-gray-100 last:border-0"
          style={{ gridTemplateColumns: `180px repeat(${rules.length}, minmax(80px, 1fr))` }}
        >
          <div className="px-5 py-4">
            <p className="text-xs text-gray-700 uppercase tracking-wide">{row.label}</p>
            <p className="text-xs text-gray-400 mt-0.5">{row.sub}</p>
          </div>
          {rules.map((rule, i) => {
            const isCellError = row.isErrorField && rule.idealMinimum > 0 && rule.criticalLevel >= rule.idealMinimum;
            return (
              <div key={rule.size} className="flex justify-center py-3">
                <NumberInput
                  value={rule[row.field]}
                  onChange={(v) => onUpdate(i, row.field, v)}
                  isError={isCellError}
                />
              </div>
            );
          })}
        </div>
      ))}

      {hasErrors && (
        <div className="px-5 py-3 border-t border-red-100 bg-red-50 flex items-center gap-2">
          <AlertTriangle size={14} className="text-red-500 shrink-0" />
          <p className="text-sm text-red-700">
            {t.error_critical_msg}
          </p>
        </div>
      )}
    </div>
  );
}

import React from "react";

// ─── Component ────────────────────────────────────────────────────────────────

export function SegmentRulesView() {
  const { t } = useLanguage();
  const [tipo, setTipo]       = useState<string | null>(null);
  const [classe, setClasse]   = useState<string | null>(null);
  const [genero, setGenero]   = useState<string | null>(null);
  const [marca, setMarca]     = useState<string | null>(null);
  const [colecao, setColecao] = useState<string | null>(null);

  const [rules, setRules]           = useState<SizeRule[]>([]);
  const [storeRules, setStoreRules] = useState<Record<StoreId, SizeRule[]>>({ TC: [], NH: [], IG: [], OS: [] });
  const [sameGradeForAll, setSameGradeForAll] = useState(true);

  const [activeSegment, setActiveSegment] = useState<SegmentKey | null>(null);
  const [isDirty, setIsDirty]           = useState(false);
  const [savedSuccess, setSavedSuccess] = useState(false);
  const [savedRules, setSavedRules]     = useState<SavedSegmentRules>(MOCK_SAVED);

  const isComplete = tipo && classe && genero;

  function loadSegment() {
    if (!tipo || !classe || !genero) return;
    const seg = { tipo, classe, genero };
    const key = makeKey(seg);
    const sizes = getSizes(tipo, classe, genero);
    const saved = savedRules[key];
    const base = saved
      ? sizes.map((s) => saved.find((r) => r.size === s) ?? { size: s, idealMinimum: 0, criticalLevel: 0 })
      : buildBlankRules(sizes);
    setRules(base);
    setStoreRules({ TC: base.map(r => ({ ...r })), NH: base.map(r => ({ ...r })), IG: base.map(r => ({ ...r })), OS: base.map(r => ({ ...r })) });
    setActiveSegment(seg);
    setSameGradeForAll(true);
    setIsDirty(false);
    setSavedSuccess(false);
  }

  function updateRule(i: number, field: keyof SizeRule, value: number) {
    setRules((prev) => { const n = [...prev]; n[i] = { ...n[i], [field]: value }; return n; });
    setIsDirty(true);
    setSavedSuccess(false);
  }

  function updateStoreRule(store: StoreId, i: number, field: keyof SizeRule, value: number) {
    setStoreRules((prev) => {
      const n = [...prev[store]]; n[i] = { ...n[i], [field]: value };
      return { ...prev, [store]: n };
    });
    setIsDirty(true);
    setSavedSuccess(false);
  }

  function toggleSameGrade() {
    const next = !sameGradeForAll;
    if (!next) {
      setStoreRules({ TC: rules.map(r => ({ ...r })), NH: rules.map(r => ({ ...r })), IG: rules.map(r => ({ ...r })), OS: rules.map(r => ({ ...r })) });
    }
    setSameGradeForAll(next);
    setIsDirty(true);
  }

  function handleSave() {
    if (!activeSegment) return;
    setSavedRules((prev) => ({ ...prev, [makeKey(activeSegment)]: rules }));
    setSavedSuccess(true);
    setIsDirty(false);
    setTimeout(() => setSavedSuccess(false), 3000);
  }

  function handleReset() {
    if (!activeSegment) return;
    const key = makeKey(activeSegment);
    const sizes = getSizes(activeSegment.tipo, activeSegment.classe, activeSegment.genero);
    const saved = savedRules[key];
    const base = saved
      ? sizes.map((s) => saved.find((r) => r.size === s) ?? { size: s, idealMinimum: 0, criticalLevel: 0 })
      : buildBlankRules(sizes);
    setRules(base);
    setStoreRules({ TC: base.map(r => ({ ...r })), NH: base.map(r => ({ ...r })), IG: base.map(r => ({ ...r })), OS: base.map(r => ({ ...r })) });
    setSameGradeForAll(true);
    setIsDirty(false);
  }

  const hasErrors = sameGradeForAll
    ? rules.some((r) => r.idealMinimum > 0 && r.criticalLevel >= r.idealMinimum)
    : STORES.some((s) => storeRules[s].some((r) => r.idealMinimum > 0 && r.criticalLevel >= r.idealMinimum));

  const isExistingSegment = activeSegment ? !!savedRules[makeKey(activeSegment)] : false;

  const checkbox = (
    <button
      role="checkbox"
      aria-checked={sameGradeForAll}
      onClick={toggleSameGrade}
      className="flex items-start gap-2 cursor-pointer select-none text-left w-full"
    >
      <span className={`mt-0.5 w-3.5 h-3.5 rounded border-2 flex items-center justify-center transition-colors shrink-0 ${
        sameGradeForAll ? "bg-gray-900 border-gray-900" : "border-gray-400 hover:border-gray-600"
      }`}>
        {sameGradeForAll && (
          <svg width="7" height="5" viewBox="0 0 8 6" fill="none">
            <path d="M1 3L3 5L7 1" stroke="white" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
          </svg>
        )}
      </span>
      <span className="text-xs text-gray-600 leading-snug">
        {t.same_grade_all_stores}
      </span>
    </button>
  );

  return (
    <div className="max-w-4xl mx-auto px-8 py-8 space-y-5">

      {/* Como funciona */}
      <div className="bg-white border border-gray-200 rounded-lg p-5">
        <p className="text-xs text-gray-400 uppercase tracking-wide mb-3">{t.how_it_works}</p>
        <div className="grid grid-cols-3 gap-6">
          <div className="flex gap-3">
            <span className="w-5 h-5 rounded-full bg-gray-900 text-white text-xs flex items-center justify-center shrink-0 mt-0.5" style={{ fontWeight: 700 }}>1</span>
            <div>
              <p className="text-sm text-gray-800" style={{ fontWeight: 500 }}>{t.seg_cad_step1_title}</p>
              <p className="text-xs text-gray-400 mt-0.5 leading-relaxed">
                {t.seg_cad_step1_desc}
              </p>
            </div>
          </div>
          <div className="flex gap-3">
            <span className="w-5 h-5 rounded-full bg-gray-900 text-white text-xs flex items-center justify-center shrink-0 mt-0.5" style={{ fontWeight: 700 }}>2</span>
            <div>
              <p className="text-sm text-gray-800" style={{ fontWeight: 500 }}>{t.seg_cad_step2_title}</p>
              <p className="text-xs text-gray-400 mt-0.5 leading-relaxed">
                {t.seg_cad_step2_desc}
              </p>
            </div>
          </div>
          <div className="flex gap-3">
            <span className="w-5 h-5 rounded-full bg-gray-900 text-white text-xs flex items-center justify-center shrink-0 mt-0.5" style={{ fontWeight: 700 }}>3</span>
            <div>
              <p className="text-sm text-gray-800" style={{ fontWeight: 500 }}>{t.seg_cad_step3_title}</p>
              <p className="text-xs text-gray-400 mt-0.5 leading-relaxed">
                {t.seg_cad_step3_desc}
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* Seletor de segmento */}
      <div className="bg-white border border-gray-200 rounded-lg p-5 space-y-4">
        <div className="grid grid-cols-3 gap-4">
          <SearchableSelect label={t.seg_tipo}   options={TIPOS}   value={tipo}    onChange={setTipo}    placeholder={t.seg_select_tipo} />
          <SearchableSelect label={t.seg_classe} options={CLASSES} value={classe}  onChange={setClasse}  placeholder={t.seg_select_classe} />
          <SearchableSelect label={t.seg_genero} options={GENEROS} value={genero}  onChange={setGenero}  placeholder={t.seg_select_genero} />
        </div>
        <div className="grid grid-cols-2 gap-4">
          <SearchableSelect label={t.seg_marca}   options={MARCAS}   value={marca}   onChange={setMarca}   placeholder={t.seg_all_brands} />
          <SearchableSelect label={t.seg_colecao} options={COLECOES} value={colecao} onChange={setColecao} placeholder={t.seg_all_collections} />
        </div>

        <div className="flex items-center justify-between pt-1">
          {tipo && classe && genero ? (
            <p className="text-sm text-gray-500">
              <span className="text-gray-900" style={{ fontWeight: 500 }}>{tipo} · {classe} · {genero}</span>
              {marca && <span className="text-gray-500"> · {marca}</span>}
              {colecao && <span className="text-gray-500"> · {colecao}</span>}
              {" — "}
              {getSizes(tipo, classe, genero).join(", ")}
            </p>
          ) : (
            <p className="text-sm text-gray-400">{t.seg_select_hint}</p>
          )}

          <button
            onClick={loadSegment}
            disabled={!isComplete}
            className={`px-4 py-1.5 rounded-md text-sm transition-colors ${
              isComplete ? "bg-gray-900 text-white hover:bg-gray-700" : "bg-gray-100 text-gray-300 cursor-not-allowed"
            }`}
          >
            {t.seg_load_grade}
          </button>
        </div>
      </div>

      {/* Grade do segmento */}
      {activeSegment && (
        <>
          <div className="flex items-center justify-between gap-3 flex-wrap">
            <div>
              <h2 className="text-gray-900">
                {activeSegment.tipo} · {activeSegment.classe} · {activeSegment.genero}
                {marca && <span className="text-gray-500 text-base"> · {marca}</span>}
                {colecao && <span className="text-gray-500 text-base"> · {colecao}</span>}
              </h2>
              <p className="text-xs text-gray-400 mt-0.5 flex items-center gap-1.5">
                <Info size={11} />
                {isExistingSegment ? t.seg_existing : t.seg_new}
              </p>
            </div>
            <div className="flex items-center gap-2">
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

          <div className="space-y-4">
            {/* Grade global */}
            {sameGradeForAll && (
              <RulesTable rules={rules} onUpdate={updateRule} headerSlot={checkbox} />
            )}

            {/* Checkbox standalone quando por loja */}
            {!sameGradeForAll && (
              <div className="bg-white border border-gray-200 rounded-lg px-5 py-3 flex items-center">
                {checkbox}
                <span className="ml-auto text-xs text-gray-400">{t.individual_grade}</span>
              </div>
            )}

            {/* Uma tabela por loja */}
            {!sameGradeForAll && STORES.map((store) => (
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
                />
              </div>
            ))}
          </div>
        </>
      )}

      {/* Empty state */}
      {!activeSegment && (
        <div className="bg-white border border-gray-200 rounded-lg p-14 text-center">
          <p className="text-gray-400">
            {t.seg_empty_state}
          </p>
        </div>
      )}
    </div>
  );
}
