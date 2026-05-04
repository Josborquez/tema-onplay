/* global React */
const { useState: useStateOP, useMemo: useMemoOP } = React;

/* ---------- One Piece icons (color circles + attribute glyphs) ---------- */
function OPColor({ c, size = 14 }) {
  const map = {
    Red:    { bg: "#D32027", fg: "#fff", l: "R" },
    Green:  { bg: "#1F8B3F", fg: "#fff", l: "G" },
    Blue:   { bg: "#1E5DB0", fg: "#fff", l: "B" },
    Purple: { bg: "#6E3FA3", fg: "#fff", l: "P" },
    Black:  { bg: "#1A1A1A", fg: "#fff", l: "K", border: "#444" },
    Yellow: { bg: "#E8B04B", fg: "#000", l: "Y" },
    Multicolor: { bg: "linear-gradient(135deg,#D32027 0%,#E8B04B 50%,#1E5DB0 100%)", fg: "#fff", l: "M" },
  };
  const m = map[c] || { bg: "#444", fg: "#fff", l: "?" };
  return (
    <span style={{
      display: "inline-flex", alignItems: "center", justifyContent: "center",
      width: size, height: size, borderRadius: "50%",
      background: m.bg, color: m.fg, fontSize: Math.max(8, size * 0.55),
      fontFamily: "var(--mono)", fontWeight: 700,
      border: m.border ? `1px solid ${m.border}` : "none",
      flexShrink: 0
    }}>{m.l}</span>
  );
}

/* OP attribute icon (Strike, Slash, Special, Ranged, Wisdom) */
function OPAttribute({ a, size = 14 }) {
  const map = {
    Strike: { bg: "#C24028", glyph: "✊" },
    Slash:  { bg: "#3D8AD8", glyph: "⚔" },
    Special:{ bg: "#A040C8", glyph: "✦" },
    Ranged: { bg: "#3FB950", glyph: "➹" },
    Wisdom: { bg: "#E8B04B", glyph: "📖" },
  };
  if (!a) return null;
  // Handle compound attributes like "Slash/Special"
  const first = a.split("/")[0];
  const m = map[first] || { bg: "#666", glyph: "·" };
  return (
    <span title={a} style={{
      display: "inline-flex", alignItems: "center", justifyContent: "center",
      width: size, height: size, fontSize: Math.max(8, size * 0.6),
      background: m.bg, color: "white", borderRadius: 2, flexShrink: 0,
      lineHeight: 1
    }}>{m.glyph}</span>
  );
}

/* OP Card type pill */
function OPTypePill({ t }) {
  const map = {
    Leader:    { bg: "rgba(232,176,75,0.18)", color: "#E8B04B", border: "rgba(232,176,75,0.4)" },
    Character: { bg: "rgba(255,255,255,0.04)", color: "var(--bone-2)", border: "var(--line-2)" },
    Event:     { bg: "rgba(63,185,80,0.12)", color: "#3FB950", border: "rgba(63,185,80,0.3)" },
    Stage:     { bg: "rgba(110,63,163,0.18)", color: "#B68FE0", border: "rgba(110,63,163,0.4)" },
  };
  const m = map[t] || map.Character;
  return (
    <span style={{
      padding: "2px 6px", fontSize: 10, fontFamily: "var(--mono)",
      letterSpacing: "0.08em", textTransform: "uppercase",
      background: m.bg, color: m.color, border: `1px solid ${m.border}`,
      borderRadius: 2
    }}>{t}</span>
  );
}

/* Rarity badge */
function OPRarity({ r }) {
  const map = {
    L:   { bg: "#E8B04B", fg: "#0A0A0A", label: "L" },
    SEC: { bg: "linear-gradient(135deg,#E8B04B,#D62828)", fg: "#fff", label: "SEC" },
    SR:  { bg: "#D62828", fg: "#fff", label: "SR" },
    R:   { bg: "#3D8AD8", fg: "#fff", label: "R" },
    UC:  { bg: "#888", fg: "#fff", label: "UC" },
    C:   { bg: "var(--carbon-3)", fg: "var(--bone-2)", label: "C" },
    TR:  { bg: "#7B5EA8", fg: "#fff", label: "TR" },
  };
  const m = map[r] || map.C;
  return (
    <span style={{
      padding: "2px 7px", fontSize: 10, fontFamily: "var(--mono)",
      fontWeight: 700, letterSpacing: "0.06em",
      background: m.bg, color: m.fg, borderRadius: 2
    }}>{m.label}</span>
  );
}

/* ---------- One Piece product card ---------- */
function OPProductCard({ card, onAdd, onOpen, onClickCapture }) {
  const handleClick = (e) => {
    if (onClickCapture) return onClickCapture();
    if (onOpen) onOpen(card);
  };
  // Real card image from official CDN; fallback gradient on error
  const imgUrl = `https://en.onepiece-cardgame.com/images/cardlist/card/${card.id}.png`;
  return (
    <div className="card-tile" onClick={handleClick} style={{
      background: "var(--carbon)", border: "1px solid var(--line)",
      borderRadius: 3, overflow: "hidden", cursor: "pointer",
      display: "flex", flexDirection: "column", transition: "border-color 120ms"
    }}
      onMouseEnter={(e) => e.currentTarget.style.borderColor = "var(--line-2)"}
      onMouseLeave={(e) => e.currentTarget.style.borderColor = "var(--line)"}
    >
      {/* Image — OP cards use 5:7 portrait too. Placeholder color block keyed to color */}
      <div style={{
        position: "relative", aspectRatio: "5/7",
        background: card.colors && card.colors[0] === "Red" ? "linear-gradient(160deg,#3a1010,#1a0606)"
                  : card.colors && card.colors[0] === "Green" ? "linear-gradient(160deg,#0f2a18,#061208)"
                  : card.colors && card.colors[0] === "Blue" ? "linear-gradient(160deg,#0c2540,#04101e)"
                  : card.colors && card.colors[0] === "Yellow" ? "linear-gradient(160deg,#3a2c0d,#1a1404)"
                  : card.colors && card.colors[0] === "Purple" ? "linear-gradient(160deg,#2a1240,#10061f)"
                  : card.colors && card.colors[0] === "Black" ? "linear-gradient(160deg,#1a1a1a,#000)"
                  : card.colors && card.colors[0] === "Multicolor" ? "linear-gradient(135deg,#3a1010,#3a2c0d,#0c2540)"
                  : "var(--carbon-2)",
        display: "flex", alignItems: "flex-end", padding: 10
      }}>
        <img src={imgUrl}
          onError={(e) => { e.target.style.display = "none"; }}
          style={{ position: "absolute", inset: 0, width: "100%", height: "100%", objectFit: "cover" }} />
        {/* Top-left: rarity */}
        <div style={{ position: "absolute", top: 8, left: 8, zIndex: 2 }}>
          <OPRarity r={card.rarity} />
        </div>
        {/* Top-right: foil badge */}
        {card.foil && (
          <div style={{ position: "absolute", top: 8, right: 8, zIndex: 2,
            padding: "2px 6px", fontSize: 9, fontFamily: "var(--mono)",
            fontWeight: 700, letterSpacing: "0.1em",
            background: "linear-gradient(135deg,#E8B04B,#FFD980,#E8B04B)",
            color: "#0A0A0A", borderRadius: 2
          }}>FOIL</div>
        )}
        {/* Bottom-right: colors */}
        <div style={{ position: "absolute", bottom: 8, right: 8, zIndex: 2, display: "flex", gap: 3 }}>
          {(card.colors || []).map((c, i) => <OPColor key={i} c={c} size={16} />)}
        </div>
        {/* Card title — fades when image loads */}
        <div style={{
          position: "relative", zIndex: 1,
          color: "rgba(255,255,255,0)", fontFamily: "var(--display)",
          fontSize: 22, lineHeight: 1, letterSpacing: "0.02em"
        }}>{card.name}</div>
      </div>

      {/* Meta */}
      <div style={{ padding: "10px 12px", display: "flex", flexDirection: "column", gap: 6, flex: 1 }}>
        <div style={{ display: "flex", alignItems: "center", gap: 6 }}>
          <OPTypePill t={card.cardType} />
          {card.attribute && <OPAttribute a={card.attribute} size={14} />}
          <span className="mono" style={{ fontSize: 10, color: "var(--mid)", marginLeft: "auto" }}>{card.id}</span>
        </div>
        <div style={{
          fontSize: 13, fontWeight: 600, color: "var(--bone)",
          overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap"
        }}>{card.name}</div>
        <div style={{ fontSize: 11, color: "var(--mid-2)",
          overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap"
        }}>{card.setCode} · {card.setName}</div>
        {/* Stat row */}
        <div style={{ display: "flex", gap: 6, fontSize: 10, fontFamily: "var(--mono)", color: "var(--mid-2)", marginTop: 2 }}>
          {card.cost != null && <Stat label="COST" v={card.cost} />}
          {card.power != null && <Stat label="PWR" v={card.power} />}
          {card.life != null && <Stat label="LIFE" v={card.life} />}
          {card.counter != null && <Stat label="CTR" v={card.counter} />}
        </div>
        {/* Footer row */}
        <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginTop: "auto", paddingTop: 8, borderTop: "1px dashed var(--line)" }}>
          <div className="mono" style={{ fontSize: 10, color: "var(--mid-2)" }}>{card.condition} · {card.lang}</div>
          <div className={`badge ${card.stock <= 3 ? "badge-stock-low" : "badge-stock-ok"}`} style={{ fontSize: 9 }}>
            {card.stock <= 3 ? `Últ. ${card.stock}` : `${card.stock}`}
          </div>
        </div>
        <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginTop: 4 }}>
          <div className="d-md" style={{ color: "var(--carmesi)", fontSize: 20, lineHeight: 1 }}>
            {formatCLP(card.price)}
          </div>
          <button className="btn btn-primary btn-sm"
            style={{ padding: "6px 14px", fontSize: 11, letterSpacing: "0.06em" }}
            onClick={(e) => { e.stopPropagation(); onAdd({ ...card, set: card.setCode, img: imgUrl }); }}>
            Agregar
          </button>
        </div>
      </div>
    </div>
  );
}

function Stat({ label, v }) {
  return (
    <div style={{ display: "flex", flexDirection: "column", alignItems: "flex-start", gap: 0, padding: "2px 6px", background: "var(--carbon-2)", borderRadius: 2, minWidth: 32 }}>
      <span style={{ fontSize: 8, color: "var(--mid)", letterSpacing: "0.1em" }}>{label}</span>
      <span style={{ color: "var(--bone)", fontWeight: 600 }}>{v.toLocaleString()}</span>
    </div>
  );
}

/* ---------- Main One Piece Listing ---------- */
function OPListing({ setRoute, addToCart }) {
  const allCards = window.OP_CARDS || [];
  const allSets = window.OP_SETS || [];

  const [query, setQuery] = useStateOP("");
  const [colorFilter, setColorFilter] = useStateOP([]);
  const [typeFilter, setTypeFilter] = useStateOP([]);
  const [setFilter, setSetFilter] = useStateOP([]);
  const [setSearch, setSetSearch] = useStateOP("");
  const [rarityFilter, setRarityFilter] = useStateOP([]);
  const [attrFilter, setAttrFilter] = useStateOP([]);
  const [costFilter, setCostFilter] = useStateOP([]); // 1-10
  const [powerMin, setPowerMin] = useStateOP(0);
  const [powerMax, setPowerMax] = useStateOP(13000);
  const [counterFilter, setCounterFilter] = useStateOP([]); // 0,1000,2000
  const [conditionFilter, setConditionFilter] = useStateOP([]);
  const [foilFilter, setFoilFilter] = useStateOP("all");
  const [langFilter, setLangFilter] = useStateOP([]);
  const [priceMin, setPriceMin] = useStateOP(0);
  const [priceMax, setPriceMax] = useStateOP(100000);
  const [inStock, setInStock] = useStateOP(true);
  const [sort, setSort] = useStateOP("price-desc");

  const toggle = (arr, setArr, v) => setArr(arr.includes(v) ? arr.filter(x => x !== v) : [...arr, v]);

  const filtered = useMemoOP(() => {
    let r = allCards;
    if (query) {
      const q = query.toLowerCase();
      r = r.filter(c =>
        c.name.toLowerCase().includes(q) ||
        c.id.toLowerCase().includes(q) ||
        (c.typeText || "").toLowerCase().includes(q)
      );
    }
    if (colorFilter.length) r = r.filter(c => (c.colors || []).some(x => colorFilter.includes(x)));
    if (typeFilter.length) r = r.filter(c => typeFilter.includes(c.cardType));
    if (setFilter.length) r = r.filter(c => setFilter.includes(c.setCode));
    if (rarityFilter.length) r = r.filter(c => rarityFilter.includes(c.rarity));
    if (attrFilter.length) r = r.filter(c => attrFilter.some(a => (c.attribute || "").includes(a)));
    if (costFilter.length) r = r.filter(c => c.cost != null && costFilter.includes(c.cost >= 10 ? 10 : c.cost));
    if (counterFilter.length) r = r.filter(c => counterFilter.includes(c.counter || 0));
    r = r.filter(c => {
      const p = c.power || 0;
      return p >= powerMin && p <= powerMax;
    });
    if (conditionFilter.length) r = r.filter(c => conditionFilter.includes(c.condition));
    if (langFilter.length) r = r.filter(c => langFilter.includes(c.lang));
    if (foilFilter === "foil") r = r.filter(c => c.foil);
    if (foilFilter === "nonfoil") r = r.filter(c => !c.foil);
    r = r.filter(c => c.price >= priceMin && c.price <= priceMax);
    if (inStock) r = r.filter(c => c.stock > 0);
    if (sort === "price-asc") r = [...r].sort((a,b) => a.price - b.price);
    if (sort === "price-desc") r = [...r].sort((a,b) => b.price - a.price);
    if (sort === "name") r = [...r].sort((a,b) => a.name.localeCompare(b.name));
    if (sort === "id") r = [...r].sort((a,b) => a.id.localeCompare(b.id));
    return r;
  }, [query, colorFilter, typeFilter, setFilter, rarityFilter, attrFilter, costFilter, counterFilter, powerMin, powerMax, conditionFilter, langFilter, foilFilter, priceMin, priceMax, inStock, sort]);

  const filteredSets = allSets.filter(s =>
    !setSearch || s.name.toLowerCase().includes(setSearch.toLowerCase()) || s.code.toLowerCase().includes(setSearch.toLowerCase())
  );

  const activeCount = setFilter.length + colorFilter.length + typeFilter.length + rarityFilter.length + attrFilter.length + costFilter.length + counterFilter.length + conditionFilter.length + langFilter.length + (foilFilter !== "all" ? 1 : 0);

  const clearAll = () => {
    setSetFilter([]); setColorFilter([]); setTypeFilter([]); setRarityFilter([]);
    setAttrFilter([]); setCostFilter([]); setCounterFilter([]); setConditionFilter([]);
    setLangFilter([]); setFoilFilter("all"); setPowerMin(0); setPowerMax(13000);
    setPriceMin(0); setPriceMax(100000);
  };

  return (
    <div className="screen-enter">
      {/* Breadcrumb */}
      <div style={{ padding: "16px 0", borderBottom: "1px solid var(--line)", background: "var(--carbon)" }}>
        <div className="container" style={{ fontSize: 12, color: "var(--mid-2)", letterSpacing: "0.04em", padding: "0 32px" }}>
          <span onClick={() => setRoute({ name: "home" })} style={{ cursor: "pointer" }}>Inicio</span>
          <span style={{ margin: "0 8px" }}>/</span>
          <span>One Piece Card Game</span>
          <span style={{ margin: "0 8px" }}>/</span>
          <span style={{ color: "var(--bone)" }}>Singles</span>
        </div>
      </div>

      {/* Title band — distinct OP treatment */}
      <div style={{
        padding: "36px 0 28px", borderBottom: "1px solid var(--line)",
        background: "linear-gradient(180deg, rgba(214,40,40,0.06), transparent)"
      }}>
        <div className="container" style={{ padding: "0 32px", display: "flex", alignItems: "flex-end", justifyContent: "space-between", gap: 24 }}>
          <div>
            <div style={{ display: "flex", alignItems: "center", gap: 10, marginBottom: 8 }}>
              <span className="mono" style={{ fontSize: 10, color: "var(--carmesi)", letterSpacing: "0.18em", textTransform: "uppercase" }}>BANDAI · TCG</span>
              <span style={{ width: 30, height: 1, background: "var(--line-2)" }} />
              <span className="mono" style={{ fontSize: 10, color: "var(--mid-2)", letterSpacing: "0.18em", textTransform: "uppercase" }}>{allSets.length} sets</span>
            </div>
            <h1 className="d-xl" style={{ fontSize: 56, lineHeight: 1, letterSpacing: "0.01em" }}>Singles · One Piece</h1>
            <div style={{ marginTop: 12, display: "flex", alignItems: "center", gap: 12, fontSize: 13, color: "var(--mid-2)" }}>
              <span className="mono" style={{ color: "var(--bone)" }}>{filtered.length}</span>
              <span>resultados</span>
              {activeCount > 0 && (
                <>
                  <span>·</span>
                  <span style={{ color: "var(--carmesi)", cursor: "pointer" }} onClick={clearAll}>Limpiar filtros ({activeCount})</span>
                </>
              )}
            </div>
          </div>
          <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
            <select value={sort} onChange={(e) => setSort(e.target.value)} className="input" style={{ width: "auto", height: 38 }}>
              <option value="price-desc">Precio: mayor a menor</option>
              <option value="price-asc">Precio: menor a mayor</option>
              <option value="name">Nombre A-Z</option>
              <option value="id">Código (set #)</option>
            </select>
          </div>
        </div>
      </div>

      {/* Search + quick chips bar */}
      <div style={{ borderBottom: "1px solid var(--line)", background: "var(--carbon)" }}>
        <div className="container" style={{ padding: "16px 32px", display: "flex", alignItems: "center", gap: 16 }}>
          <div style={{ position: "relative", flex: 1, maxWidth: 520 }}>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"
              style={{ position: "absolute", left: 12, top: "50%", transform: "translateY(-50%)", color: "var(--mid)" }}>
              <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input
              className="input"
              placeholder="Buscar por nombre, código (ej. OP15-008), tipo…"
              value={query}
              onChange={(e) => setQuery(e.target.value)}
              style={{ paddingLeft: 36, height: 40 }}
            />
          </div>
          {/* Quick color chips */}
          <div style={{ display: "flex", gap: 4 }}>
            {["Red","Green","Blue","Purple","Black","Yellow","Multicolor"].map(c => (
              <button key={c}
                onClick={() => toggle(colorFilter, setColorFilter, c)}
                title={c}
                style={{
                  width: 30, height: 30, borderRadius: "50%", padding: 0,
                  border: colorFilter.includes(c) ? "2px solid var(--carmesi)" : "1px solid var(--line-2)",
                  background: "transparent", cursor: "pointer", display: "flex",
                  alignItems: "center", justifyContent: "center"
                }}>
                <OPColor c={c} size={20} />
              </button>
            ))}
          </div>
        </div>
      </div>

      {/* Body */}
      <div className="container" style={{ display: "grid", gridTemplateColumns: "300px 1fr", gap: 32, padding: "32px" }}>
        {/* Sidebar */}
        <aside style={{ position: "sticky", top: 110, alignSelf: "flex-start", maxHeight: "calc(100vh - 130px)", overflowY: "auto", paddingRight: 8 }}>

          <FilterGroupOP title="Color">
            <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 4 }}>
              {["Red","Green","Blue","Purple","Black","Yellow","Multicolor"].map(c => (
                <button key={c} onClick={() => toggle(colorFilter, setColorFilter, c)}
                  style={{
                    display: "flex", alignItems: "center", gap: 8, padding: "6px 8px",
                    border: "1px solid " + (colorFilter.includes(c) ? "var(--carmesi)" : "var(--line-2)"),
                    background: colorFilter.includes(c) ? "rgba(214,40,40,0.1)" : "transparent",
                    color: "var(--bone)", fontSize: 11, borderRadius: 2, cursor: "pointer",
                    fontFamily: "var(--body)"
                  }}>
                  <OPColor c={c} size={14} />
                  <span>{c}</span>
                </button>
              ))}
            </div>
          </FilterGroupOP>

          <FilterGroupOP title="Tipo de carta">
            <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 4 }}>
              {["Leader","Character","Event","Stage"].map(t => (
                <button key={t} onClick={() => toggle(typeFilter, setTypeFilter, t)}
                  style={{
                    padding: "8px", fontSize: 11, fontFamily: "var(--mono)",
                    letterSpacing: "0.06em", textTransform: "uppercase",
                    border: "1px solid " + (typeFilter.includes(t) ? "var(--carmesi)" : "var(--line-2)"),
                    background: typeFilter.includes(t) ? "rgba(214,40,40,0.1)" : "transparent",
                    color: typeFilter.includes(t) ? "var(--bone)" : "var(--mid-2)",
                    borderRadius: 2, cursor: "pointer"
                  }}>{t}</button>
              ))}
            </div>
          </FilterGroupOP>

          <FilterGroupOP title="Set / Producto">
            <input className="input" placeholder="Buscar set… (ej. OP-15)"
              value={setSearch} onChange={(e) => setSetSearch(e.target.value)}
              style={{ marginBottom: 8, fontSize: 12, height: 32 }} />
            <div style={{ display: "flex", flexDirection: "column", gap: 2, maxHeight: 220, overflowY: "auto" }}>
              {filteredSets.map(s => (
                <label key={s.code} style={{ display: "flex", alignItems: "center", gap: 8, padding: "5px 4px", cursor: "pointer", fontSize: 12 }}>
                  <input type="checkbox" checked={setFilter.includes(s.code)}
                    onChange={() => toggle(setFilter, setSetFilter, s.code)}
                    style={{ accentColor: "var(--carmesi)" }} />
                  <span className="mono" style={{ fontSize: 10, color: "var(--carmesi)", minWidth: 44 }}>{s.code}</span>
                  <span style={{ flex: 1, color: "var(--bone-2)", overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>{s.name}</span>
                  <span style={{ fontSize: 9, color: "var(--mid)" }}>{s.year}</span>
                </label>
              ))}
            </div>
          </FilterGroupOP>

          <FilterGroupOP title="Rareza">
            <div style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: 4 }}>
              {["L","SEC","SR","R","UC","C","TR"].map(r => (
                <button key={r} onClick={() => toggle(rarityFilter, setRarityFilter, r)}
                  style={{
                    padding: "6px 0", fontSize: 11, fontFamily: "var(--mono)", fontWeight: 700,
                    border: "1px solid " + (rarityFilter.includes(r) ? "var(--carmesi)" : "var(--line-2)"),
                    background: rarityFilter.includes(r) ? "rgba(214,40,40,0.1)" : "transparent",
                    color: rarityFilter.includes(r) ? "var(--bone)" : "var(--mid-2)",
                    borderRadius: 2, cursor: "pointer"
                  }}>{r}</button>
              ))}
            </div>
          </FilterGroupOP>

          <FilterGroupOP title="Atributo">
            <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 4 }}>
              {["Strike","Slash","Special","Ranged","Wisdom"].map(a => (
                <button key={a} onClick={() => toggle(attrFilter, setAttrFilter, a)}
                  style={{
                    display: "flex", alignItems: "center", gap: 6, padding: "6px 8px",
                    border: "1px solid " + (attrFilter.includes(a) ? "var(--carmesi)" : "var(--line-2)"),
                    background: attrFilter.includes(a) ? "rgba(214,40,40,0.1)" : "transparent",
                    color: "var(--bone)", fontSize: 11, borderRadius: 2, cursor: "pointer"
                  }}>
                  <OPAttribute a={a} size={12} />
                  <span>{a}</span>
                </button>
              ))}
            </div>
          </FilterGroupOP>

          <FilterGroupOP title="Costo (DON!!)">
            <div style={{ display: "grid", gridTemplateColumns: "repeat(5, 1fr)", gap: 4 }}>
              {[1,2,3,4,5,6,7,8,9,10].map(n => (
                <button key={n} onClick={() => toggle(costFilter, setCostFilter, n)}
                  style={{
                    padding: "6px 0", fontSize: 11, fontFamily: "var(--mono)", fontWeight: 700,
                    border: "1px solid " + (costFilter.includes(n) ? "var(--carmesi)" : "var(--line-2)"),
                    background: costFilter.includes(n) ? "rgba(214,40,40,0.15)" : "transparent",
                    color: costFilter.includes(n) ? "var(--bone)" : "var(--mid-2)",
                    borderRadius: 2, cursor: "pointer"
                  }}>{n === 10 ? "10+" : n}</button>
              ))}
            </div>
          </FilterGroupOP>

          <FilterGroupOP title="Power">
            <div style={{ display: "flex", gap: 6, alignItems: "center", marginBottom: 10 }}>
              <input className="input" style={{ fontSize: 12, height: 30 }} value={powerMin}
                onChange={(e) => setPowerMin(+e.target.value || 0)} />
              <span style={{ color: "var(--mid)" }}>—</span>
              <input className="input" style={{ fontSize: 12, height: 30 }} value={powerMax}
                onChange={(e) => setPowerMax(+e.target.value || 0)} />
            </div>
            <input type="range" min="0" max="13000" step="1000" value={powerMax}
              onChange={(e) => setPowerMax(+e.target.value)}
              style={{ width: "100%", accentColor: "var(--carmesi)" }} />
            <div style={{ display: "flex", justifyContent: "space-between", fontSize: 10, color: "var(--mid)", marginTop: 4 }}>
              <span className="mono">0</span><span className="mono">13.000</span>
            </div>
          </FilterGroupOP>

          <FilterGroupOP title="Counter">
            <div style={{ display: "grid", gridTemplateColumns: "repeat(3, 1fr)", gap: 4 }}>
              {[[0,"—"],[1000,"+1000"],[2000,"+2000"]].map(([v, l]) => (
                <button key={v} onClick={() => toggle(counterFilter, setCounterFilter, v)}
                  style={{
                    padding: "6px 0", fontSize: 11, fontFamily: "var(--mono)",
                    border: "1px solid " + (counterFilter.includes(v) ? "var(--carmesi)" : "var(--line-2)"),
                    background: counterFilter.includes(v) ? "rgba(214,40,40,0.1)" : "transparent",
                    color: counterFilter.includes(v) ? "var(--bone)" : "var(--mid-2)",
                    borderRadius: 2, cursor: "pointer"
                  }}>{l}</button>
              ))}
            </div>
          </FilterGroupOP>

          <FilterGroupOP title="Condición">
            <div style={{ display: "grid", gridTemplateColumns: "repeat(3, 1fr)", gap: 4 }}>
              {["NM","LP","SP","MP","HP","DMG"].map(c => (
                <button key={c} onClick={() => toggle(conditionFilter, setConditionFilter, c)}
                  style={{
                    padding: "6px 0", fontSize: 11, fontFamily: "var(--mono)", fontWeight: 600,
                    border: "1px solid " + (conditionFilter.includes(c) ? "var(--carmesi)" : "var(--line-2)"),
                    background: conditionFilter.includes(c) ? "rgba(214,40,40,0.1)" : "transparent",
                    color: conditionFilter.includes(c) ? "var(--bone)" : "var(--mid-2)",
                    borderRadius: 2, cursor: "pointer"
                  }}>{c}</button>
              ))}
            </div>
          </FilterGroupOP>

          <FilterGroupOP title="Foil">
            <div style={{ display: "grid", gridTemplateColumns: "repeat(3, 1fr)", gap: 4 }}>
              {[["all","Todo"],["nonfoil","Regular"],["foil","Foil"]].map(([v, l]) => (
                <button key={v} onClick={() => setFoilFilter(v)}
                  style={{
                    padding: "6px 0", fontSize: 11,
                    border: "1px solid " + (foilFilter === v ? "var(--carmesi)" : "var(--line-2)"),
                    background: foilFilter === v ? "rgba(214,40,40,0.1)" : "transparent",
                    color: foilFilter === v ? "var(--bone)" : "var(--mid-2)",
                    borderRadius: 2, cursor: "pointer"
                  }}>{l}</button>
              ))}
            </div>
          </FilterGroupOP>

          <FilterGroupOP title="Idioma">
            <div style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: 4 }}>
              {["EN","ES","JP","KR"].map(l => (
                <button key={l} onClick={() => toggle(langFilter, setLangFilter, l)}
                  style={{
                    padding: "6px 0", fontSize: 11, fontFamily: "var(--mono)",
                    border: "1px solid " + (langFilter.includes(l) ? "var(--carmesi)" : "var(--line-2)"),
                    background: langFilter.includes(l) ? "rgba(214,40,40,0.1)" : "transparent",
                    color: langFilter.includes(l) ? "var(--bone)" : "var(--mid-2)",
                    borderRadius: 2, cursor: "pointer"
                  }}>{l}</button>
              ))}
            </div>
          </FilterGroupOP>

          <FilterGroupOP title="Precio (CLP)">
            <div style={{ display: "flex", gap: 6, alignItems: "center", marginBottom: 10 }}>
              <input className="input" style={{ fontSize: 12, height: 30 }} value={priceMin}
                onChange={(e) => setPriceMin(+e.target.value || 0)} />
              <span style={{ color: "var(--mid)" }}>—</span>
              <input className="input" style={{ fontSize: 12, height: 30 }} value={priceMax}
                onChange={(e) => setPriceMax(+e.target.value || 0)} />
            </div>
            <input type="range" min="0" max="100000" step="1000" value={priceMax}
              onChange={(e) => setPriceMax(+e.target.value)}
              style={{ width: "100%", accentColor: "var(--carmesi)" }} />
            <div style={{ display: "flex", justifyContent: "space-between", fontSize: 10, color: "var(--mid)", marginTop: 4 }}>
              <span className="mono">$0</span><span className="mono">$100.000</span>
            </div>
          </FilterGroupOP>

          <FilterGroupOP title="Disponibilidad">
            <label style={{ display: "flex", alignItems: "center", gap: 10, cursor: "pointer", fontSize: 12 }}>
              <div style={{ position: "relative", width: 32, height: 18,
                background: inStock ? "var(--carmesi)" : "var(--carbon-3)",
                borderRadius: 10, transition: "background 120ms" }}
                onClick={() => setInStock(!inStock)}>
                <div style={{ position: "absolute", top: 2, left: inStock ? 16 : 2, width: 14, height: 14, background: "white", borderRadius: "50%", transition: "left 120ms" }} />
              </div>
              <span style={{ color: "var(--bone-2)" }}>Solo con stock</span>
            </label>
          </FilterGroupOP>
        </aside>

        {/* Grid */}
        <main>
          {/* Active chips */}
          {activeCount > 0 && (
            <div style={{ display: "flex", flexWrap: "wrap", gap: 6, marginBottom: 20 }}>
              {colorFilter.map(s => <ChipOP key={"c"+s} label={s} onRemove={() => toggle(colorFilter, setColorFilter, s)} />)}
              {typeFilter.map(s => <ChipOP key={"t"+s} label={s} onRemove={() => toggle(typeFilter, setTypeFilter, s)} />)}
              {setFilter.map(s => <ChipOP key={"s"+s} label={s} onRemove={() => toggle(setFilter, setSetFilter, s)} />)}
              {rarityFilter.map(s => <ChipOP key={"r"+s} label={s} onRemove={() => toggle(rarityFilter, setRarityFilter, s)} />)}
              {attrFilter.map(s => <ChipOP key={"a"+s} label={s} onRemove={() => toggle(attrFilter, setAttrFilter, s)} />)}
              {costFilter.map(s => <ChipOP key={"co"+s} label={`Costo ${s}`} onRemove={() => toggle(costFilter, setCostFilter, s)} />)}
              {conditionFilter.map(s => <ChipOP key={"cd"+s} label={s} onRemove={() => toggle(conditionFilter, setConditionFilter, s)} />)}
              {langFilter.map(s => <ChipOP key={"l"+s} label={s} onRemove={() => toggle(langFilter, setLangFilter, s)} />)}
              {foilFilter !== "all" && <ChipOP label={foilFilter === "foil" ? "Solo Foil" : "Solo Regular"} onRemove={() => setFoilFilter("all")} />}
            </div>
          )}

          {filtered.length === 0 ? (
            <div style={{ padding: 80, textAlign: "center", border: "1px dashed var(--line-2)", borderRadius: 4 }}>
              <div className="d-md" style={{ fontSize: 22, marginBottom: 8 }}>Sin resultados</div>
              <div style={{ color: "var(--mid-2)", fontSize: 13, marginBottom: 16 }}>Probá ajustar los filtros o limpiar todo.</div>
              <button className="btn btn-secondary" onClick={clearAll}>Limpiar filtros</button>
            </div>
          ) : (
            <div style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: 16 }}>
              {filtered.map((c, i) => <OPProductCard key={i} card={c} onAdd={addToCart} onOpen={(card) => setRoute({ name: "pdp-op", card })} />)}
            </div>
          )}

          {/* Pagination */}
          {filtered.length > 0 && (
            <div style={{ display: "flex", justifyContent: "center", alignItems: "center", gap: 4, marginTop: 48 }}>
              <button className="btn btn-secondary btn-sm">←</button>
              {[1,2,3,4,5].map(n => (
                <button key={n} className={n === 1 ? "btn btn-primary btn-sm" : "btn btn-secondary btn-sm"} style={{ minWidth: 32 }}>{n}</button>
              ))}
              <span style={{ padding: "0 8px", color: "var(--mid)" }}>…</span>
              <button className="btn btn-secondary btn-sm" style={{ minWidth: 32 }}>18</button>
              <button className="btn btn-secondary btn-sm">→</button>
            </div>
          )}
        </main>
      </div>
    </div>
  );
}

function FilterGroupOP({ title, children }) {
  const [open, setOpen] = useStateOP(true);
  return (
    <div style={{ borderBottom: "1px solid var(--line)", padding: "14px 0" }}>
      <div onClick={() => setOpen(!open)} style={{ display: "flex", justifyContent: "space-between", alignItems: "center", cursor: "pointer", marginBottom: open ? 12 : 0 }}>
        <div className="d-sm" style={{ color: "var(--bone)", fontSize: 13 }}>{title}</div>
        <span style={{ color: "var(--mid)", fontSize: 10, transform: open ? "rotate(180deg)" : "none", transition: "transform 120ms" }}>▼</span>
      </div>
      {open && <div>{children}</div>}
    </div>
  );
}

function ChipOP({ label, onRemove }) {
  return (
    <div style={{ display: "flex", alignItems: "center", gap: 6, padding: "4px 10px", background: "rgba(214,40,40,0.12)", border: "1px solid rgba(214,40,40,0.4)", borderRadius: 2, fontSize: 11, color: "var(--bone)" }}>
      {label}
      <span onClick={onRemove} style={{ cursor: "pointer", color: "var(--mid-2)", fontSize: 14, lineHeight: 1 }}>×</span>
    </div>
  );
}

Object.assign(window, { OPListing });
