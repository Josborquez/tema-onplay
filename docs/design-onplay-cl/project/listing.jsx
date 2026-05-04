/* global React */
const { useState: useStateL, useMemo: useMemoL } = React;

function Listing({ setRoute, addToCart, initialQuery, initialSet }) {
  const [query, setQuery] = useStateL(initialQuery || "");
  const [setFilter, setSetFilter] = useStateL(initialSet ? [initialSet] : []);
  const [colorFilter, setColorFilter] = useStateL([]);
  const [rarityFilter, setRarityFilter] = useStateL([]);
  const [conditionFilter, setConditionFilter] = useStateL([]);
  const [foilFilter, setFoilFilter] = useStateL("all"); // all | foil | nonfoil
  const [langFilter, setLangFilter] = useStateL([]);
  const [priceMin, setPriceMin] = useStateL(0);
  const [priceMax, setPriceMax] = useStateL(500000);
  const [inStock, setInStock] = useStateL(true);
  const [sort, setSort] = useStateL("price-desc");
  const [view, setView] = useStateL("grid");

  const allCards = window.MTG_CARDS;
  const sets = [...new Set(allCards.map(c => c.set))].map(code => {
    const card = allCards.find(c => c.set === code);
    return { code, name: card.setName, count: allCards.filter(c => c.set === code).length };
  });

  const filtered = useMemoL(() => {
    let r = allCards;
    if (query) r = r.filter(c => c.name.toLowerCase().includes(query.toLowerCase()));
    if (setFilter.length) r = r.filter(c => setFilter.includes(c.set));
    if (colorFilter.length) r = r.filter(c => {
      if (colorFilter.includes("C") && c.colors.length === 0) return true;
      return c.colors.some(cc => colorFilter.includes(cc));
    });
    if (rarityFilter.length) r = r.filter(c => rarityFilter.includes(c.rarity));
    if (conditionFilter.length) r = r.filter(c => conditionFilter.includes(c.condition));
    if (langFilter.length) r = r.filter(c => langFilter.includes(c.lang));
    if (foilFilter === "foil") r = r.filter(c => c.foil);
    if (foilFilter === "nonfoil") r = r.filter(c => !c.foil);
    r = r.filter(c => c.price >= priceMin && c.price <= priceMax);
    if (inStock) r = r.filter(c => c.stock > 0);
    if (sort === "price-asc") r = [...r].sort((a,b) => a.price - b.price);
    if (sort === "price-desc") r = [...r].sort((a,b) => b.price - a.price);
    if (sort === "name") r = [...r].sort((a,b) => a.name.localeCompare(b.name));
    return r;
  }, [query, setFilter, colorFilter, rarityFilter, conditionFilter, langFilter, foilFilter, priceMin, priceMax, inStock, sort]);

  const toggle = (arr, setArr, v) => setArr(arr.includes(v) ? arr.filter(x => x !== v) : [...arr, v]);

  return (
    <div className="screen-enter">
      {/* Breadcrumb band */}
      <div style={{ padding: "16px 0", borderBottom: "1px solid var(--line)", background: "var(--carbon)" }}>
        <div className="container" style={{ fontSize: 12, color: "var(--mid-2)", letterSpacing: "0.04em" }}>
          <span onClick={() => setRoute({ name: "home" })} style={{ cursor: "pointer" }}>Inicio</span>
          <span style={{ margin: "0 8px" }}>/</span>
          <span>Magic: The Gathering</span>
          <span style={{ margin: "0 8px" }}>/</span>
          <span style={{ color: "var(--bone)" }}>Singles</span>
        </div>
      </div>

      {/* Title band */}
      <div style={{ padding: "32px 0 24px", borderBottom: "1px solid var(--line)" }}>
        <div className="container" style={{ display: "flex", alignItems: "flex-end", justifyContent: "space-between" }}>
          <div>
            <h1 className="d-xl">Singles · Magic: The Gathering</h1>
            <div style={{ marginTop: 10, display: "flex", alignItems: "center", gap: 12, fontSize: 13, color: "var(--mid-2)" }}>
              <span className="mono" style={{ color: "var(--bone)" }}>{filtered.length}</span>
              <span>resultados</span>
              {(setFilter.length + colorFilter.length + rarityFilter.length) > 0 && (
                <>
                  <span>·</span>
                  <span style={{ color: "var(--carmesi)", cursor: "pointer" }}
                    onClick={() => { setSetFilter([]); setColorFilter([]); setRarityFilter([]); setConditionFilter([]); setLangFilter([]); }}>
                    Limpiar filtros
                  </span>
                </>
              )}
            </div>
          </div>
          <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
            <select value={sort} onChange={(e) => setSort(e.target.value)} className="input" style={{ width: "auto", height: 38 }}>
              <option value="price-desc">Precio: mayor a menor</option>
              <option value="price-asc">Precio: menor a mayor</option>
              <option value="name">Nombre A-Z</option>
              <option value="new">Recién ingresado</option>
            </select>
            <div style={{ display: "flex", border: "1px solid var(--line-2)", borderRadius: 2, overflow: "hidden" }}>
              <button className="btn btn-ghost" style={{ background: view === "grid" ? "var(--carbon-3)" : "transparent", color: view === "grid" ? "var(--bone)" : "var(--mid-2)", borderRadius: 0 }} onClick={() => setView("grid")}>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
              </button>
              <button className="btn btn-ghost" style={{ background: view === "list" ? "var(--carbon-3)" : "transparent", color: view === "list" ? "var(--bone)" : "var(--mid-2)", borderRadius: 0 }} onClick={() => setView("list")}>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Body */}
      <div className="container" style={{ display: "grid", gridTemplateColumns: "280px 1fr", gap: 32, padding: "32px" }}>
        {/* Sidebar */}
        <aside style={{ display: "flex", flexDirection: "column", gap: 0, position: "sticky", top: 110, alignSelf: "flex-start", maxHeight: "calc(100vh - 130px)", overflowY: "auto", paddingRight: 8 }}>
          <FilterGroup title="Colores">
            <div style={{ display: "flex", gap: 6, flexWrap: "wrap" }}>
              {["W","U","B","R","G","C"].map(c => (
                <button key={c} onClick={() => toggle(colorFilter, setColorFilter, c)}
                  style={{
                    display: "flex", alignItems: "center", gap: 6, padding: "6px 10px",
                    border: "1px solid " + (colorFilter.includes(c) ? "var(--carmesi)" : "var(--line-2)"),
                    background: colorFilter.includes(c) ? "rgba(214,40,40,0.1)" : "transparent",
                    color: "var(--bone)", fontSize: 11, borderRadius: 2, cursor: "pointer",
                    fontFamily: "var(--body)"
                  }}>
                  <Mana c={c} size="sm" />
                  <span style={{ letterSpacing: "0.04em" }}>{c === "C" ? "Incoloro" : c}</span>
                </button>
              ))}
            </div>
          </FilterGroup>

          <FilterGroup title="Set (edición)">
            <input className="input" placeholder="Buscar set…" style={{ marginBottom: 8, fontSize: 12 }} />
            <div style={{ display: "flex", flexDirection: "column", gap: 2, maxHeight: 180, overflowY: "auto" }}>
              {sets.map(s => (
                <label key={s.code} style={{ display: "flex", alignItems: "center", gap: 8, padding: "5px 2px", cursor: "pointer", fontSize: 12 }}>
                  <input type="checkbox" checked={setFilter.includes(s.code)} onChange={() => toggle(setFilter, setSetFilter, s.code)} style={{ accentColor: "var(--carmesi)" }} />
                  <SetIcon code={s.code} size={12} />
                  <span style={{ flex: 1, color: "var(--bone-2)", overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>{s.name}</span>
                  <span className="mono" style={{ color: "var(--mid)", fontSize: 10 }}>{s.count}</span>
                </label>
              ))}
            </div>
          </FilterGroup>

          <FilterGroup title="Rareza">
            {["mythic","rare","uncommon","common"].map(r => (
              <label key={r} style={{ display: "flex", alignItems: "center", gap: 8, padding: "4px 0", cursor: "pointer", fontSize: 12 }}>
                <input type="checkbox" checked={rarityFilter.includes(r)} onChange={() => toggle(rarityFilter, setRarityFilter, r)} style={{ accentColor: "var(--carmesi)" }} />
                <span style={{ color: "var(--bone-2)", textTransform: "capitalize" }}>{({mythic:"Mythic Rare", rare:"Rare", uncommon:"Uncommon", common:"Common"})[r]}</span>
              </label>
            ))}
          </FilterGroup>

          <FilterGroup title="Condición">
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
          </FilterGroup>

          <FilterGroup title="Foil">
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
          </FilterGroup>

          <FilterGroup title="Idioma">
            <div style={{ display: "grid", gridTemplateColumns: "repeat(3, 1fr)", gap: 4 }}>
              {["EN","ES","JP"].map(l => (
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
          </FilterGroup>

          <FilterGroup title="Precio (CLP)">
            <div style={{ display: "flex", gap: 6, alignItems: "center", marginBottom: 10 }}>
              <input className="input" style={{ fontSize: 12 }} value={priceMin} onChange={(e) => setPriceMin(+e.target.value || 0)} />
              <span style={{ color: "var(--mid)" }}>—</span>
              <input className="input" style={{ fontSize: 12 }} value={priceMax} onChange={(e) => setPriceMax(+e.target.value || 0)} />
            </div>
            <input type="range" min="0" max="500000" step="1000" value={priceMax} onChange={(e) => setPriceMax(+e.target.value)} style={{ width: "100%", accentColor: "var(--carmesi)" }} />
            <div style={{ display: "flex", justifyContent: "space-between", fontSize: 10, color: "var(--mid)", marginTop: 4 }}>
              <span className="mono">$0</span><span className="mono">$500.000</span>
            </div>
          </FilterGroup>

          <FilterGroup title="Disponibilidad">
            <label style={{ display: "flex", alignItems: "center", gap: 10, cursor: "pointer", fontSize: 12 }}>
              <div style={{ position: "relative", width: 32, height: 18, background: inStock ? "var(--carmesi)" : "var(--carbon-3)", borderRadius: 10, transition: "background 120ms" }}
                onClick={() => setInStock(!inStock)}>
                <div style={{ position: "absolute", top: 2, left: inStock ? 16 : 2, width: 14, height: 14, background: "white", borderRadius: "50%", transition: "left 120ms" }} />
              </div>
              <span style={{ color: "var(--bone-2)" }}>Solo con stock</span>
            </label>
          </FilterGroup>
        </aside>

        {/* Grid */}
        <main>
          {/* Active chips */}
          {(setFilter.length + colorFilter.length + rarityFilter.length + conditionFilter.length + langFilter.length) > 0 && (
            <div style={{ display: "flex", flexWrap: "wrap", gap: 6, marginBottom: 20 }}>
              {setFilter.map(s => <Chip key={s} label={s} onRemove={() => toggle(setFilter, setSetFilter, s)} />)}
              {colorFilter.map(s => <Chip key={s} label={s === "C" ? "Incoloro" : s} onRemove={() => toggle(colorFilter, setColorFilter, s)} />)}
              {rarityFilter.map(s => <Chip key={s} label={s} onRemove={() => toggle(rarityFilter, setRarityFilter, s)} />)}
              {conditionFilter.map(s => <Chip key={s} label={s} onRemove={() => toggle(conditionFilter, setConditionFilter, s)} />)}
              {langFilter.map(s => <Chip key={s} label={s} onRemove={() => toggle(langFilter, setLangFilter, s)} />)}
            </div>
          )}

          {view === "grid" ? (
            <div style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: 16 }}>
              {filtered.map((c, i) => <ProductCard key={i} card={c} onOpen={(card) => setRoute({ name: "pdp", card })} onAdd={addToCart} />)}
            </div>
          ) : (
            <div style={{ display: "flex", flexDirection: "column" }}>
              {filtered.map((c, i) => (
                <div key={i} onClick={() => setRoute({ name: "pdp", card: c })}
                  style={{ display: "grid", gridTemplateColumns: "60px 2fr 1fr 100px 120px 100px", gap: 16, alignItems: "center", padding: "10px 12px", border: "1px solid var(--line)", borderTop: i === 0 ? "1px solid var(--line)" : "none", background: "var(--carbon)", cursor: "pointer" }}>
                  <img src={c.img} style={{ width: 48, height: 68, objectFit: "cover", borderRadius: 2 }} />
                  <div>
                    <div style={{ fontWeight: 600, fontSize: 14 }}>{c.name}</div>
                    <div style={{ fontSize: 11, color: "var(--mid-2)", display: "flex", alignItems: "center", gap: 6, marginTop: 2 }}>
                      <SetIcon code={c.set} size={11} /> {c.setName}
                    </div>
                  </div>
                  <div style={{ display: "flex", gap: 4 }}>{c.colors.map((mc, j) => <Mana key={j} c={mc} size="sm" />)}</div>
                  <div className="mono" style={{ fontSize: 11, color: "var(--bone-2)" }}>{c.condition} · {c.lang}</div>
                  <div className={`badge ${c.stock <= 3 ? "badge-stock-low" : "badge-stock-ok"}`} style={{ justifySelf: "start" }}>{c.stock <= 3 ? `Últimas ${c.stock}` : `${c.stock} disp.`}</div>
                  <div className="d-md" style={{ color: "var(--carmesi)", fontSize: 22, textAlign: "right" }}>{formatCLP(c.price)}</div>
                </div>
              ))}
            </div>
          )}

          {/* Pagination */}
          <div style={{ display: "flex", justifyContent: "center", alignItems: "center", gap: 4, marginTop: 48 }}>
            <button className="btn btn-secondary btn-sm">←</button>
            {[1,2,3,4,5].map(n => (
              <button key={n} className={n === 1 ? "btn btn-primary btn-sm" : "btn btn-secondary btn-sm"} style={{ minWidth: 32 }}>{n}</button>
            ))}
            <span style={{ padding: "0 8px", color: "var(--mid)" }}>…</span>
            <button className="btn btn-secondary btn-sm" style={{ minWidth: 32 }}>42</button>
            <button className="btn btn-secondary btn-sm">→</button>
          </div>
        </main>
      </div>
    </div>
  );
}

function FilterGroup({ title, children }) {
  const [open, setOpen] = useStateL(true);
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

function Chip({ label, onRemove }) {
  return (
    <div style={{ display: "flex", alignItems: "center", gap: 6, padding: "4px 10px", background: "rgba(214,40,40,0.12)", border: "1px solid rgba(214,40,40,0.4)", borderRadius: 2, fontSize: 11, color: "var(--bone)" }}>
      {label}
      <span onClick={onRemove} style={{ cursor: "pointer", color: "var(--mid-2)", fontSize: 14, lineHeight: 1 }}>×</span>
    </div>
  );
}

Object.assign(window, { Listing });
