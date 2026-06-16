import { useState, useRef, useEffect } from "react";
import { ChevronDown, Search, X } from "lucide-react";

interface Props {
  label: string;
  options: string[];
  value: string | null;
  onChange: (v: string | null) => void;
  placeholder?: string;
}

export function SearchableSelect({ label, options, value, onChange, placeholder = "Todos" }: Props) {
  const [open, setOpen] = useState(false);
  const [query, setQuery] = useState("");
  const ref = useRef<HTMLDivElement>(null);

  const filtered = options.filter((o) => o.toLowerCase().includes(query.toLowerCase()));

  useEffect(() => {
    function handler(e: MouseEvent) {
      if (ref.current && !ref.current.contains(e.target as Node)) {
        setOpen(false);
        setQuery("");
      }
    }
    document.addEventListener("mousedown", handler);
    return () => document.removeEventListener("mousedown", handler);
  }, []);

  return (
    <div ref={ref} className="relative">
      <p className="text-xs text-gray-400 uppercase tracking-wide mb-1.5">{label}</p>
      <button
        onClick={() => { setOpen((o) => !o); setQuery(""); }}
        className={`flex items-center justify-between gap-2 w-full px-3 py-2 border rounded-md bg-white text-sm transition-colors ${
          open ? "border-gray-400" : "border-gray-200 hover:border-gray-300"
        } ${value ? "text-gray-900" : "text-gray-400"}`}
      >
        <span className="truncate">{value ?? placeholder}</span>
        <div className="flex items-center gap-1 shrink-0">
          {value && (
            <span
              role="button"
              onClick={(e) => { e.stopPropagation(); onChange(null); }}
              className="text-gray-300 hover:text-gray-500"
            >
              <X size={12} />
            </span>
          )}
          <ChevronDown size={13} className={`text-gray-300 transition-transform ${open ? "rotate-180" : ""}`} />
        </div>
      </button>

      {open && (
        <div className="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-md shadow-lg z-30 overflow-hidden">
          <div className="flex items-center gap-2 px-3 py-2 border-b border-gray-100">
            <Search size={13} className="text-gray-300 shrink-0" />
            <input
              autoFocus
              type="text"
              placeholder="Buscar..."
              value={query}
              onChange={(e) => setQuery(e.target.value)}
              className="flex-1 outline-none text-sm text-gray-700 placeholder:text-gray-400 bg-transparent"
            />
          </div>
          <div className="max-h-52 overflow-y-auto">
            <button
              onClick={() => { onChange(null); setOpen(false); setQuery(""); }}
              className={`w-full text-left px-3 py-2 text-sm transition-colors ${!value ? "bg-gray-50 text-gray-700" : "text-gray-400 hover:bg-gray-50"}`}
            >
              {placeholder}
            </button>
            {filtered.map((opt) => (
              <button
                key={opt}
                onClick={() => { onChange(opt); setOpen(false); setQuery(""); }}
                className={`w-full text-left px-3 py-2 text-sm transition-colors ${value === opt ? "bg-gray-900 text-white" : "text-gray-700 hover:bg-gray-50"}`}
              >
                {opt}
              </button>
            ))}
            {filtered.length === 0 && (
              <p className="px-3 py-3 text-sm text-gray-400">Nenhum resultado.</p>
            )}
          </div>
        </div>
      )}
    </div>
  );
}
