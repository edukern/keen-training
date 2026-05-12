export function NumberInput({
  value,
  onChange,
  disabled = false,
  isError = false,
}: {
  value: number;
  onChange: (v: number) => void;
  disabled?: boolean;
  isError?: boolean;
}) {
  return (
    <input
      type="number"
      min={0}
      max={999}
      value={value}
      disabled={disabled}
      onChange={(e) => {
        const v = parseInt(e.target.value, 10);
        if (!isNaN(v) && v >= 0 && v <= 999) onChange(v);
      }}
      className={`w-20 text-center px-2 py-1.5 rounded border text-sm transition-all focus:outline-none focus:ring-1 ${
        disabled
          ? "bg-gray-50 border-gray-200 text-gray-300 cursor-not-allowed"
          : isError
            ? "border-red-400 bg-white text-red-700 focus:ring-red-300"
            : "border-gray-200 bg-white text-gray-800 focus:border-gray-400 focus:ring-gray-200"
      }`}
    />
  );
}
