/* global React */
const { useState: useStateRB, useMemo: useMemoRB } = React;

/* ---------- Riftbound domain icons (6 elemental domains) ---------- */
function RBDomain({ d, size = 14 }) {
  const map = {
    Body:   { bg: "#C2362E", fg: "#fff", l: "B", glyph: "✦" },     // red — strength
    Mind:   { bg: "#2E78C2", fg: "#fff", l: "M", glyph: "◆" },     // blue — intellect
    Calm:   { bg: "#2EA86A", fg: "#fff", l: "C", glyph: "◐" },     // green — serenity
    Chaos:  { bg: "#7E3AAD", fg: "#fff", l: "X", glyph: "✺" },     // purple — wild
    Order:  { bg: "#D9B23A", fg: "#0A0A0A", l: "O", glyph: "✧" },  // gold — discipline
    Fury:   { bg: "#E07A2A", fg: "#fff", l: "F", glyph: "✱" },     // orange — rage
  };
  const m = map[d] || { bg: "#444", fg: "#fff", glyph: "·" };
  return (
    <span title={d} style={{
      display: "inline-flex", alignItems: "center", justifyContent: "center",
      width: size, height: size, borderRadius: "50%",
      background: m.bg, color: m.fg, fontSize: Math.max(8, size * 0.6),
      fontWeight: 700, flexShrink: 0, lineHeight: 1
    }}>{m.glyph}</span>
  );
}

/* RB Card-type pill */
function RBTypePill({ t }) {
  const map = {
    Legend:      { bg: "rgba(217,178,58,0.18)", color: "#D9B23A", border: "rgba(217,178,58,0.45)" },
    Champion:    { bg: "rgba(126,58,173,0.18)", color: "#B68FE0", border: "rgba(126,58,173,0.4)" },
    Unit:        { bg: "rgba(255,255,255,0.04)", color: "var(--bone-2)", border: "var(--line-2)" },
    Spell:       { bg: "rgba(46,120,194,0.16)", color: "#5DA0DC", border: "rgba(46,120,194,0.4)" },
    Gear:        { bg: "rgba(224,122,42,0.16)", color: "#E89B5A", border: "rgba(224,122,42,0.4)" },
    Battlefield: { bg: "rgba(46,168,106,0.14)", color: "#3EBC7C", border: "rgba(46,168,106,0.4)" },
    Rune:        { bg: "rgba(194,54,46,0.14)", color: "#D85B53", border: "rgba(194,54,46,0.4)" },
  };
  const m = map[t] || map.Unit;
  return (
    <span style={{
      padding: "2px 6px", fontSize: 10, fontFamily: "var(--mono)",
      letterSpacing: "0.08em", textTransform: "uppercase",
      background: m.bg, color: m.color, border: `1px solid ${m.border}`,
      borderRadius: 2
    }}>{t}</span>
  );
}

function RBRarity({ r }) {
  const map = {
    Legendary: { bg: "linear-gradient(135deg,#D9B23A,#FFD980,#D9B23A)", fg: "#0A0A0A", label: "LEG" },
    Epic:      { bg: "#7E3AAD", fg: "#fff", label: "EPIC" },
    Rare:      { bg: "#2E78C2", fg: "#fff", label: "RARE" },
    Uncommon:  { bg: "#888", fg: "#fff", label: "UNC" },
    Common:    { bg: "var(--carbon-3)", fg: "var(--bone-2)", label: "COM" },
  };
  const m = map[r] || map.Common;
  return (
    <span style={{
      padding: "2px 7px", fontSize: 9, fontFamily: "var(--mono)",
      fontWeight: 700, letterSpacing: "0.08em",
      background: m.bg, color: m.fg, borderRadius: 2
    }}>{m.label}</span>
  );
}

/* ---------- Riftbound product card ---------- */
function RBProductCard({ card, onAdd, onOpen, onClickCapture }) {
  const handleClick = () => {
    if (onClickCapture) return onClickCapture();
    if (onOpen) onOpen(card);
  };
  const imgUrl = `https://riftbound.leagueoflegends.com/images/cards/${card.id}.png`;
  const primaryDomain = (card.domains || [])[0];
  const tint = {
    Body:   "linear-gradient(160deg,#3a1010,#1a0606)",
    Mind:   "linear-gradient(160deg,#0c2540,#04101e)",
    Calm:   "linear-gradient(160deg,#0f2a18,#061208)",
    Chaos:  "linear-gradient(160deg,#28114a,#10061f)",
    Order:  "linear-gradient(160deg,#3a2c0d,#1a1404)",
    Fury:   "linear-gradient(160deg,#3a1d08,#1a0c03)",
  }[primaryDomain] || "var(--carbon-2)";

  return (
    <div className="card-tile" onClick={handleClick} style={{
      background: "var(--carbon)", border: "1px solid var(--line)",
      borderRadius: 3, overflow: "hidden", cursor: "pointer",
      display: "flex", flexDirection: "column", transition: "border-color 120ms"
    }}
      onMouseEnter={(e) => e.currentTarget.style.borderColor = "var(--line-2)"}
      onMouseLeave={(e) => e.currentTarget.style.borderColor = "var(--line)"}
    >
      <div style={{
        position: "relative", aspectRatio: "5/7",
        background: tint,
        display: "flex", alignItems: "flex-end", padding: 10
      }}>
        <img src={imgUrl}
          onError={(e) => { e.target.style.display = "none"; }}
          style={{ position: "absolute", inset: 0, width: "100%", height: "100%", objectFit: "cover" }} />
        <div style={{ position: "absolute", top: 8, left: 8, zIndex: 2 }}>
          <RBRarity r={card.rarity} />
        </div>
        {card.foil && (
          <div style={{ position: "absolute", top: 8, right: 8, zIndex: 2,
            padding: "2px 6px", fontSize: 9, fontFamily: "var(--mono)",
            fontWeight: 700, letterSpacing: "0.1em",
            background: "linear-gradient(135deg,#D9B23A,#FFD980,#D9B23A)",
            color: "#0A0A0A", borderRadius: 2
          }}>FOIL</div>
        )}
        <div style={{ position: "absolute", bottom: 8, right: 8, zIndex: 2, display: "flex", gap: 3 }}>
          {(card.domains || []).map((d, i) => <RBDomain key={i} d={d} size={16} />)}
        </div>
        <div style={{
          position: "relative", zIndex: 1,
          color: "rgba(255,255,255,0)", fontFamily: "var(--display)",
          fontSize: 20, lineHeight: 1.05, letterSpacing: "0.01em"
        }}>{card.name}</div>
      </div>

      <div style={{ padding: "10px 12px", display: "flex", flexDirection: "column", gap: 6, flex: 1 }}>
        <div style={{ display: "flex", alignItems: "center", gap: 6 }}>
          <RBTypePill t={card.cardType} />
          <span className="mono" style={{ fontSize: 10, color: "var(--mid)", marginLeft: "auto" }}>{card.id}</span>
        </div>
        <div style={{
          fontSize: 13, fontWeight: 600, color: "var(--bone)",
          overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap"
        }}>{card.name}</div>
        <div style={{ fontSize: 11, color: "var(--mid-2)",
          overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap"
        }}>{card.setCode} · {card.setName}{card.champion ? ` · ${card.champion}` : ""}</div>

        <div style={{ display: "flex", gap: 6, fontSize: 10, fontFamily: "var(--mono)", color: "var(--mid-2)", marginTop: 2 }}>
          {card.cost != null && <RBStat label="COST" v={card.cost} />}
          {card.power != null && <RBStat label="PWR" v={card.power} />}
          {card.might != null && <RBStat label="MGHT" v={card.might} />}
        </div>

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

function RBStat({ label, v }) {
  return (
    <div style={{ display: "flex", flexDirection: "column", alignItems: "flex-start", gap: 0, padding: "2px 6px", background: "var(--carbon-2)", borderRadius: 2, minWidth: 32 }}>
      <span style={{ fontSize: 8, color: "var(--mid)", letterSpacing: "0.1em" }}>{label}</span>
      <span style={{ color: "var(--bone)", fontWeight: 600 }}>{v.toLocaleString()}</span>
    </div>
  );
}

/* ---------- Main Riftbound Listing ---------- */
function RBListing({ setRoute, addToCart }) {
  const allCards = window.RB_CARDS || [];
  const allSets = window.RB_SETS || [];
  const allChampions = window.RB_CHAMPIONS || [];

  const [query, setQuery] = useStateRB("");
  const [domainFilter, setDomainFilter] = useStateRB([]);
  const [typeFilter, setTypeFilter] = useStateRB([]);
  const [setFilter, setSetFilter] = useStateRB([]);
  const [setSearch, setSetSearch] = useStateRB("");
  const [rarityFilter, setRarityFilter] = useStateRB([]);
  const [championFilter, setChampionFilter] = useStateRB([]);
  const [championSearch, setChampionSearch] = useStateRB("");
  const [costFilter, setCostFilter] = useStateRB([]);
  const [mightMin, setMightMin] = useStateRB(0);
  const [mightMax, setMightMax] = useStateRB(10);
  const [conditionFilter, setConditionFilter] = useStateRB([]);
  const [foilFilter, setFoilFilter] = useStateRB("all");
  const [langFilter, setLangFilter] = useStateRB([]);
  const [priceMin, setPriceMin] = useStateRB(0);
  const [priceMax, setPriceMax] = useStateRB(50000);
  const [inStock, setInStock] = useStateRB(true);
  const [sort, setSort] = useStateRB("price-desc");

  const toggle = (arr, setArr, v) => setArr(arr.includes(v) ? arr.filter(x => x !== v) : [...arr, v]);

  const filtered = useMemoRB(() => {
    let r = allCards;
    if (query) {
      const q = query.toLowerCase();
      r = r.filter(c =>
        c.name.toLowerCase().includes(q) ||
        c.id.toLowerCase().includes(q) ||
        (c.champion || "").toLowerCase().includes(q) ||
        (c.cardType || "").toLowerCase().includes(q)
      );
    }
    if (domainFilter.length) r = r.filter(c => (c.domains || []).some(x => domainFilter.includes(x)));
    if (typeFilter.length) r = r.filter(c => typeFilter.includes(c.cardType));
    if (setFilter.length) r = r.filter(c => setFilter.includes(c.setCode));
    if (rarityFilter.length) r = r.filter(c => rarityFilter.includes(c.rarity));
    if (championFilter.length) r = r.filter(c => championFilter.includes(c.champion));
    if (costFilter.length) r = r.filter(c => c.cost != null && costFilter.includes(c.cost >= 7 ? 7 : c.cost));
    r = r.filter(c => {
      if (c.might == null) return true;
      return c.might >= mightMin && c.might <= mightMax;
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
  }, [query, domainFilter, typeFilter, setFilter, rarityFilter, championFilter, costFilter, mightMin, mightMax, conditionFilter, langFilter, foilFilter, priceMin, priceMax, inStock, sort]);

  const filteredSets = allSets.filter(s =>
    !setSearch || s.name.toLowerCase().includes(setSearch.toLowerCase()) || s.code.toLowerCase().includes(setSearch.toLowerCase())
  );
  const filteredChampions = allChampions.filter(c =>
    !championSearch || c.toLowerCase().includes(championSearch.toLowerCase())
  );

  const activeCount = setFilter.length + domainFilter.length + typeFilter.length + rarityFilter.length + championFilter.length + costFilter.length + conditionFilter.length + langFilter.length + (foilFilter !== "all" ? 1 : 0);

  const clearAll = () => {
    setSetFilter([]); setDomainFilter([]); setTypeFilter([]); setRarityFilter([]);
    setChampionFilter([]); setCostFilter([]); setConditionFilter([]);
    setLangFilter([]); setFoilFilter("all"); setMightMin(0); setMightMax(10);
    setPriceMin(0); setPriceMax(50000);
  };

  return (
    <div className="screen-enter">
      {/* Breadcrumb */}
      <div style={{ padding: "16px 0", borderBottom: "1px solid var(--line)", background: "var(--carbon)" }}>
        <div className="container" style={{ fontSize: 12, color: "var(--mid-2)", letterSpacing: "0.04em", padding: "0 32px" }}>
          <span onClick={() => setRoute({ name: "home" })} style={{ cursor: "pointer" }}>Inicio</span>
          <span style={{ margin: "0 8px" }}>/</span>
          <span>Riftbound TCG</span>
          <span style={{ margin: "0 8px" }}>/</span>
          <span style={{ color: "var(--bone)" }}>Singles</span>
        </div>
      </div>

      {/* Title band — Riftbound treatment with hex-tech accent */}
      <div style={{
        padding: "36px 0 28px", borderBottom: "1px solid var(--line)",
        background: "linear-gradient(180deg, rgba(46,120,194,0.05), rgba(126,58,173,0.04), transparent)"
      }}>
        <div className="container" style={{ padding: "0 32px", display: "flex", alignItems: "flex-end", justifyContent: "space-between", gap: 24 }}>
          <div>
            <div style={{ display: "flex", alignItems: "center", gap: 10, marginBottom: 8 }}>
              <span className="mono" style={{ fontSize: 10, color: "#5DA0DC", letterSpacing: "0.18em", textTransform: "uppercase" }}>RIOT GAMES · TCG</span>
              <span style={{ width: 30, height: 1, background: "var(--line-2)" }} />
              <span className="mono" style={{ fontSize: 10, color: "var(--mid-2)", letterSpacing: "0.18em", textTransform: "uppercase" }}>{allSets.length} sets · {allChampions.length} campeones</span>
            </div>
            <h1 className="d-xl" style={{ fontSize: 56, lineHeight: 1, letterSpacing: "0.01em" }}>Singles · Riftbound</h1>
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

      {/* Search + quick domain chips */}
      <div style={{ borderBottom: "1px solid var(--line)", background: "var(--carbon)" }}>
        <div className="container" style={{ padding: "16px 32px", display: "flex", alignItems: "center", gap: 16 }}>
          <div style={{ position: "relative", flex: 1, maxWidth: 520 }}>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"
              style={{ position: "absolute", left: 12, top: "50%", transform: "translateY(-50%)", color: "var(--mid)" }}>
              <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input
              className="input"
              placeholder="Buscar por nombre, código (ej. OGN-001), campeón…"
              value={query}
              onChange={(e) => setQuery(e.target.value)}
              style={{ paddingLeft: 36, height: 40 }}
            />
          </div>
          <div style={{ display: "flex", gap: 4 }}>
            {["Body","Mind","Calm","Chaos","Order","Fury"].map(d => (
              <button key={d}
                onClick={() => toggle(domainFilter, setDomainFilter, d)}
                title={d}
                style={{
                  width: 30, height: 30, borderRadius: "50%", padding: 0,
                  border: domainFilter.includes(d) ? "2px solid var(--carmesi)" : "1px solid var(--line-2)",
                  background: "transparent", cursor: "pointer", display: "flex",
                  alignItems: "center", justifyContent: "center"
                }}>
                <RBDomain d={d} size={20} />
              </button>
            ))}
          </div>
        </div>
      </div>

      {/* Body */}
      <div className="container" style={{ display: "grid", gridTemplateColumns: "300px 1fr", gap: 32, padding: "32px" }}>
        {/* Sidebar */}
        <aside style={{ position: "sticky", top: 110, alignSelf: "flex-start", maxHeight: "calc(100vh - 130px)", overflowY: "auto", paddingRight: 8 }}>

          <FilterGroupRB title="Dominio">
            <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 4 }}>
              {["Body","Mind","Calm","Chaos","Order","Fury"].map(d => (
                <button key={d} onClick={() => toggle(domainFilter, setDomainFilter, d)}
                  style={{
                    display: "flex", alignItems: "center", gap: 8, padding: "6px 8px",
                    border: "1px solid " + (domainFilter.includes(d) ? "var(--carmesi)" : "var(--line-2)"),
                    background: domainFilter.includes(d) ? "rgba(214,40,40,0.1)" : "transparent",
                    color: "var(--bone)", fontSize: 11, borderRadius: 2, cursor: "pointer",
                    fontFamily: "var(--body)"
                  }}>
                  <RBDomain d={d} size={14} />
                  <span>{d}</span>
                </button>
              ))}
            </div>
          </FilterGroupRB>

          <FilterGroupRB title="Tipo de carta">
            <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 4 }}>
              {["Legend","Champion","Unit","Spell","Gear","Battlefield","Rune"].map(t => (
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
          </FilterGroupRB>

          <FilterGroupRB title="Set / Producto">
            <input className="input" placeholder="Buscar set… (ej. Origins)"
              value={setSearch} onChange={(e) => setSetSearch(e.target.value)}
              style={{ marginBottom: 8, fontSize: 12, height: 32 }} />
            <div style={{ display: "flex", flexDirection: "column", gap: 2, maxHeight: 200, overflowY: "auto" }}>
              {filteredSets.map(s => (
                <label key={s.code} style={{ display: "flex", alignItems: "center", gap: 8, padding: "5px 4px", cursor: "pointer", fontSize: 12 }}>
                  <input type="checkbox" checked={setFilter.includes(s.code)}
                    onChange={() => toggle(setFilter, setSetFilter, s.code)}
                    style={{ accentColor: "var(--carmesi)" }} />
                  <span className="mono" style={{ fontSize: 10, color: "var(--carmesi)", minWidth: 36 }}>{s.code}</span>
                  <span style={{ flex: 1, color: "var(--bone-2)", overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>{s.name}</span>
                  <span style={{ fontSize: 9, color: "var(--mid)" }}>{s.year}</span>
                </label>
              ))}
            </div>
          </FilterGroupRB>

          <FilterGroupRB title="Campeón">
            <input className="input" placeholder="Buscar campeón… (ej. Jinx)"
              value={championSearch} onChange={(e) => setChampionSearch(e.target.value)}
              style={{ marginBottom: 8, fontSize: 12, height: 32 }} />
            <div style={{ display: "flex", flexDirection: "column", gap: 2, maxHeight: 200, overflowY: "auto" }}>
              {filteredChampions.map(c => (
                <label key={c} style={{ display: "flex", alignItems: "center", gap: 8, padding: "5px 4px", cursor: "pointer", fontSize: 12 }}>
                  <input type="checkbox" checked={championFilter.includes(c)}
                    onChange={() => toggle(championFilter, setChampionFilter, c)}
                    style={{ accentColor: "var(--carmesi)" }} />
                  <span style={{ flex: 1, color: "var(--bone-2)" }}>{c}</span>
                </label>
              ))}
            </div>
          </FilterGroupRB>

          <FilterGroupRB title="Rareza">
            <div style={{ display: "grid", gridTemplateColumns: "repeat(2, 1fr)", gap: 4 }}>
              {["Legendary","Epic","Rare","Uncommon","Common"].map(r => (
                <button key={r} onClick={() => toggle(rarityFilter, setRarityFilter, r)}
                  style={{
                    padding: "6px 8px", fontSize: 11, fontFamily: "var(--mono)", fontWeight: 700,
                    letterSpacing: "0.06em", textTransform: "uppercase",
                    border: "1px solid " + (rarityFilter.includes(r) ? "var(--carmesi)" : "var(--line-2)"),
                    background: rarityFilter.includes(r) ? "rgba(214,40,40,0.1)" : "transparent",
                    color: rarityFilter.includes(r) ? "var(--bone)" : "var(--mid-2)",
                    borderRadius: 2, cursor: "pointer"
                  }}>{r}</button>
              ))}
            </div>
          </FilterGroupRB>

          <FilterGroupRB title="Costo">
            <div style={{ display: "grid", gridTemplateColumns: "repeat(7, 1fr)", gap: 4 }}>
              {[1,2,3,4,5,6,7].map(n => (
                <button key={n} onClick={() => toggle(costFilter, setCostFilter, n)}
                  style={{
                    padding: "6px 0", fontSize: 11, fontFamily: "var(--mono)", fontWeight: 700,
                    border: "1px solid " + (costFilter.includes(n) ? "var(--carmesi)" : "var(--line-2)"),
                    background: costFilter.includes(n) ? "rgba(214,40,40,0.15)" : "transparent",
                    color: costFilter.includes(n) ? "var(--bone)" : "var(--mid-2)",
                    borderRadius: 2, cursor: "pointer"
                  }}>{n === 7 ? "7+" : n}</button>
              ))}
            </div>
          </FilterGroupRB>

          <FilterGroupRB title="Might (Power total)">
            <div style={{ display: "flex", gap: 6, alignItems: "center", marginBottom: 10 }}>
              <input className="input" style={{ fontSize: 12, height: 30 }} value={mightMin}
                onChange={(e) => setMightMin(+e.target.value || 0)} />
              <span style={{ color: "var(--mid)" }}>—</span>
              <input className="input" style={{ fontSize: 12, height: 30 }} value={mightMax}
                onChange={(e) => setMightMax(+e.target.value || 0)} />
            </div>
            <input type="range" min="0" max="10" step="1" value={mightMax}
              onChange={(e) => setMightMax(+e.target.value)}
              style={{ width: "100%", accentColor: "var(--carmesi)" }} />
            <div style={{ display: "flex", justifyContent: "space-between", fontSize: 10, color: "var(--mid)", marginTop: 4 }}>
              <span className="mono">0</span><span className="mono">10</span>
            </div>
          </FilterGroupRB>

          <FilterGroupRB title="Condición">
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
          </FilterGroupRB>

          <FilterGroupRB title="Foil">
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
          </FilterGroupRB>

          <FilterGroupRB title="Idioma">
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
          </FilterGroupRB>

          <FilterGroupRB title="Precio (CLP)">
            <div style={{ display: "flex", gap: 6, alignItems: "center", marginBottom: 10 }}>
              <input className="input" style={{ fontSize: 12, height: 30 }} value={priceMin}
                onChange={(e) => setPriceMin(+e.target.value || 0)} />
              <span style={{ color: "var(--mid)" }}>—</span>
              <input className="input" style={{ fontSize: 12, height: 30 }} value={priceMax}
                onChange={(e) => setPriceMax(+e.target.value || 0)} />
            </div>
            <input type="range" min="0" max="50000" step="500" value={priceMax}
              onChange={(e) => setPriceMax(+e.target.value)}
              style={{ width: "100%", accentColor: "var(--carmesi)" }} />
            <div style={{ display: "flex", justifyContent: "space-between", fontSize: 10, color: "var(--mid)", marginTop: 4 }}>
              <span className="mono">$0</span><span className="mono">$50.000</span>
            </div>
          </FilterGroupRB>

          <FilterGroupRB title="Disponibilidad">
            <label style={{ display: "flex", alignItems: "center", gap: 10, cursor: "pointer", fontSize: 12 }}>
              <div style={{ position: "relative", width: 32, height: 18,
                background: inStock ? "var(--carmesi)" : "var(--carbon-3)",
                borderRadius: 10, transition: "background 120ms" }}
                onClick={() => setInStock(!inStock)}>
                <div style={{ position: "absolute", top: 2, left: inStock ? 16 : 2, width: 14, height: 14, background: "white", borderRadius: "50%", transition: "left 120ms" }} />
              </div>
              <span style={{ color: "var(--bone-2)" }}>Solo con stock</span>
            </label>
          </FilterGroupRB>
        </aside>

        {/* Grid */}
        <main>
          {activeCount > 0 && (
            <div style={{ display: "flex", flexWrap: "wrap", gap: 6, marginBottom: 20 }}>
              {domainFilter.map(s => <ChipRB key={"d"+s} label={s} onRemove={() => toggle(domainFilter, setDomainFilter, s)} />)}
              {typeFilter.map(s => <ChipRB key={"t"+s} label={s} onRemove={() => toggle(typeFilter, setTypeFilter, s)} />)}
              {setFilter.map(s => <ChipRB key={"s"+s} label={s} onRemove={() => toggle(setFilter, setSetFilter, s)} />)}
              {championFilter.map(s => <ChipRB key={"ch"+s} label={s} onRemove={() => toggle(championFilter, setChampionFilter, s)} />)}
              {rarityFilter.map(s => <ChipRB key={"r"+s} label={s} onRemove={() => toggle(rarityFilter, setRarityFilter, s)} />)}
              {costFilter.map(s => <ChipRB key={"co"+s} label={`Costo ${s}`} onRemove={() => toggle(costFilter, setCostFilter, s)} />)}
              {conditionFilter.map(s => <ChipRB key={"cd"+s} label={s} onRemove={() => toggle(conditionFilter, setConditionFilter, s)} />)}
              {langFilter.map(s => <ChipRB key={"l"+s} label={s} onRemove={() => toggle(langFilter, setLangFilter, s)} />)}
              {foilFilter !== "all" && <ChipRB label={foilFilter === "foil" ? "Solo Foil" : "Solo Regular"} onRemove={() => setFoilFilter("all")} />}
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
              {filtered.map((c, i) => <RBProductCard key={i} card={c} onAdd={addToCart} onOpen={(card) => setRoute({ name: "pdp-rb", card })} />)}
            </div>
          )}

          {filtered.length > 0 && (
            <div style={{ display: "flex", justifyContent: "center", alignItems: "center", gap: 4, marginTop: 48 }}>
              <button className="btn btn-secondary btn-sm">←</button>
              {[1,2,3].map(n => (
                <button key={n} className={n === 1 ? "btn btn-primary btn-sm" : "btn btn-secondary btn-sm"} style={{ minWidth: 32 }}>{n}</button>
              ))}
              <button className="btn btn-secondary btn-sm">→</button>
            </div>
          )}
        </main>
      </div>
    </div>
  );
}

function FilterGroupRB({ title, children }) {
  const [open, setOpen] = useStateRB(true);
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

function ChipRB({ label, onRemove }) {
  return (
    <div style={{ display: "flex", alignItems: "center", gap: 6, padding: "4px 10px", background: "rgba(214,40,40,0.12)", border: "1px solid rgba(214,40,40,0.4)", borderRadius: 2, fontSize: 11, color: "var(--bone)" }}>
      {label}
      <span onClick={onRemove} style={{ cursor: "pointer", color: "var(--mid-2)", fontSize: 14, lineHeight: 1 }}>×</span>
    </div>
  );
}

Object.assign(window, { RBListing });
