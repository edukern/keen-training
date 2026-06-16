import { useState } from "react";
import { Layers } from "lucide-react";
import { DistributionRulesScreen } from "./components/DistributionRulesScreen";
import { SegmentView } from "./components/SegmentView";
import { SegmentRulesView } from "./components/SegmentRulesView";
import { CorridorReport } from "./components/CorridorReport";
import { PromocaoView, DEMO_PROMOCAO_ITEMS } from "./components/PromocaoView";
import type { PromocaoItem, PromocaoRules } from "./components/PromocaoView";
import { ReplenishmentPromocaoView } from "./components/ReplenishmentPromocaoView";
import { LanguageProvider, useLanguage } from "../i18n/LanguageContext";

type GroupKey = "produto" | "segmento" | "promocao";
type SubKey   = "cadastro" | "reposicao";

function AppShell() {
  const { lang, t, toggle } = useLanguage();
  const [activeGroup, setActiveGroup] = useState<GroupKey>("produto");
  const [activeSub,   setActiveSub]   = useState<SubKey>("cadastro");
  const [promocaoItems, setPromocaoItems] = useState<PromocaoItem[]>(DEMO_PROMOCAO_ITEMS);

  const GROUPS = [
    { key: "produto"   as GroupKey, label: t.nav_produto,   subs: [{ key: "cadastro" as SubKey, label: t.nav_cadastro }, { key: "reposicao" as SubKey, label: t.nav_reposicao }] },
    { key: "segmento"  as GroupKey, label: t.nav_segmento,  subs: [{ key: "cadastro" as SubKey, label: t.nav_cadastro }, { key: "reposicao" as SubKey, label: t.nav_reposicao }] },
    { key: "promocao"  as GroupKey, label: t.nav_promocao,  subs: [{ key: "cadastro" as SubKey, label: t.nav_cadastro }, { key: "reposicao" as SubKey, label: t.nav_reposicao }] },
  ];

  function selectGroup(g: GroupKey) { setActiveGroup(g); setActiveSub("cadastro"); }

  function handleUpdateItem(id: string, field: keyof PromocaoItem, value: string | boolean | PromocaoRules) {
    setPromocaoItems((prev) => prev.map((item) => (item.id === id ? { ...item, [field]: value } : item)));
  }
  function handleRemoveItem(id: string) {
    setPromocaoItems((prev) => prev.filter((item) => item.id !== id));
  }
  function handleImportItems(newItems: PromocaoItem[]) {
    setPromocaoItems((prev) => [...prev, ...newItems]);
  }

  function renderContent() {
    if (activeGroup === "produto") {
      if (activeSub === "cadastro")  return <DistributionRulesScreen />;
      if (activeSub === "reposicao") return <CorridorReport />;
    }
    if (activeGroup === "segmento") {
      if (activeSub === "cadastro")  return <SegmentRulesView />;
      if (activeSub === "reposicao") return <SegmentView />;
    }
    if (activeGroup === "promocao") {
      if (activeSub === "cadastro") return (
        <PromocaoView
          items={promocaoItems}
          onUpdate={handleUpdateItem}
          onRemove={handleRemoveItem}
          onImport={handleImportItems}
        />
      );
      if (activeSub === "reposicao") return <ReplenishmentPromocaoView items={promocaoItems} />;
    }
    return null;
  }

  return (
    <div className="flex min-h-screen">

      {/* ── Sidebar ── */}
      <aside className="w-56 bg-white border-r border-gray-200 flex flex-col sticky top-0 h-screen shrink-0 z-20">

        {/* Logo / título */}
        <div className="px-5 py-5 border-b border-gray-100">
          <div className="flex items-center gap-2.5">
            <div className="w-7 h-7 bg-gray-900 rounded flex items-center justify-center shrink-0">
              <Layers size={14} className="text-white" />
            </div>
            <div className="flex-1 min-w-0">
              <p className="text-xs text-gray-400 uppercase tracking-widest leading-none">{t.nav_cd}</p>
              <p className="text-sm text-gray-900 mt-0.5 leading-tight truncate" style={{ fontWeight: 600 }}>
                {t.nav_title}
              </p>
            </div>
          </div>
        </div>

        {/* Navegação */}
        <nav className="flex-1 overflow-y-auto py-4">
          {GROUPS.map((group) => {
            const isActive = activeGroup === group.key;
            return (
              <div key={group.key} className="mb-3">
                <button
                  onClick={() => selectGroup(group.key)}
                  className={`w-full text-left px-5 py-1.5 text-xs uppercase tracking-widest transition-colors ${
                    isActive ? "text-gray-900" : "text-gray-400 hover:text-gray-600"
                  }`}
                  style={{ fontWeight: isActive ? 700 : 400 }}
                >
                  {group.label}
                </button>
                <div className="mt-0.5">
                  {group.subs.map((sub) => {
                    const isSubActive = isActive && activeSub === sub.key;
                    return (
                      <button
                        key={sub.key}
                        onClick={() => { selectGroup(group.key); setActiveSub(sub.key); }}
                        className={`w-full text-left pl-8 pr-5 py-1.5 text-sm transition-colors ${
                          isSubActive
                            ? "text-gray-900 bg-gray-100"
                            : "text-gray-400 hover:text-gray-600 hover:bg-gray-50"
                        }`}
                        style={{ fontWeight: isSubActive ? 500 : 400 }}
                      >
                        {sub.label}
                      </button>
                    );
                  })}
                </div>
              </div>
            );
          })}
        </nav>

        {/* Usuário + toggle de idioma */}
        <div className="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
          <p className="text-xs text-gray-400">{t.nav_user}</p>
          <button
            onClick={toggle}
            className="flex items-center gap-1 px-2 py-1 rounded border border-gray-200 hover:bg-gray-50 transition-colors"
            title={lang === "pt" ? "Switch to English" : "Mudar para Português"}
          >
            <span className="text-xs font-mono text-gray-500" style={{ fontWeight: 600 }}>
              {lang === "pt" ? "PT" : "EN"}
            </span>
            <span className="text-gray-300 text-xs">→</span>
            <span className="text-xs font-mono text-gray-400">
              {lang === "pt" ? "EN" : "PT"}
            </span>
          </button>
        </div>

        {/* Aviso: dados fictícios */}
        <div className="px-4 py-3 bg-amber-50 border-t border-amber-100">
          <p className="text-xs text-amber-700" style={{ fontWeight: 600 }}>{t.nav_prototype}</p>
          <p className="text-xs text-amber-600 mt-0.5 leading-snug">{t.nav_prototype_desc}</p>
        </div>
      </aside>

      {/* ── Conteúdo ── */}
      <main className="flex-1 bg-gray-50 overflow-auto">
        {renderContent()}
      </main>
    </div>
  );
}

export default function App() {
  return (
    <LanguageProvider>
      <AppShell />
    </LanguageProvider>
  );
}
