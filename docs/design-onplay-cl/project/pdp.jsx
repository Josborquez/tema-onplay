/* global React */
const { useState: useStateP, useMemo: useMemoP } = React;

function PDP({ card, setRoute, addToCart }) {
  const [zoomPos, setZoomPos] = useStateP(null);
  const [selectedVariant, setSelectedVariant] = useStateP(null);
  const [quantities, setQuantities] = useStateP({});
  const [condFilter, setCondFilter] = useStateP("all");
  const [foilTab, setFoilTab] = useStateP("all");

  const variants = window.VARIANTS;
  const filteredVariants = useMemoP(() => {
    return variants.filter(v => {
      if (condFilter !== "all" && v.condition !== condFilter) return false;
      if (foilTab === "foil" && !v.foil) return false;
      if (foilTab === "nonfoil" && v.foil) return false;
      return true;
    });
  }, [condFilter, foilTab]);

  const cheapest = Math.min(...variants.map(v => v.price));
  const available = variants.reduce((s, v) => s + v.stock, 0);

  const setQty = (key, val) => setQuantities({ ...quantities, [key]: Math.max(0, val) });

  const otherPrints = window.MTG_CARDS.filter(c => c.name !== card.name).slice(0, 6);

  return (
    <div className="screen-enter">
      {/* Breadcrumb */}
      <div style={{ padding: "14px 0", borderBottom: "1px solid var(--line)", background: "var(--carbon)" }}>
        <div className="container" style={{ fontSize: 12, color: "var(--mid-2)" }}>
          <span onClick={() => setRoute({ name: "home" })} style={{ cursor: "pointer" }}>Inicio</span>
          <span style={{ margin: "0 8px" }}>/</span>
          <span onClick={() => setRoute({ name: "listing" })} style={{ cursor: "pointer" }}>Magic</span>
          <span style={{ margin: "0 8px" }}>/</span>
          <span onClick={() => setRoute({ name: "listing", setFilter: card.set })} style={{ cursor: "pointer" }}>{card.setName}</span>
          <span style={{ margin: "0 8px" }}>/</span>
          <span style={{ color: "var(--bone)" }}>{card.name}</span>
        </div>
      </div>

      <div className="container" style={{ padding: "40px 32px 0", display: "grid", gridTemplateColumns: "1fr 1.15fr", gap: 48 }}>
        {/* LEFT: Image + zoom */}
        <div>
          <div
            style={{ position: "sticky", top: 110, aspectRatio: "5 / 7", maxHeight: "calc(100vh - 130px)", background: "var(--carbon)", border: "1px solid var(--line)", borderRadius: 4, overflow: "hidden", cursor: "zoom-in", position: "sticky" }}
            onMouseMove={(e) => {
              const r = e.currentTarget.getBoundingClientRect();
              setZoomPos({ x: ((e.clientX - r.left) / r.width) * 100, y: ((e.clientY - r.top) / r.height) * 100 });
            }}
            onMouseLeave={() => setZoomPos(null)}
          >
            <img src={card.img} style={{ width: "100%", height: "100%", objectFit: "cover", transform: zoomPos ? "scale(2)" : "scale(1)", transformOrigin: zoomPos ? `${zoomPos.x}% ${zoomPos.y}%` : "center", transition: zoomPos ? "transform 0s" : "transform 200ms" }} />
            {/* Foil shimmer indicator */}
            <div style={{ position: "absolute", top: 12, left: 12, display: "flex", gap: 6 }}>
              <span className={`badge ${card.rarity === "mythic" ? "badge-mythic" : "badge-rare"}`}>{card.rarity.toUpperCase()}</span>
            </div>
            <div style={{ position: "absolute", bottom: 12, right: 12, color: "var(--mid-2)", fontSize: 10, letterSpacing: "0.08em", textTransform: "uppercase", background: "rgba(10,10,10,0.7)", backdropFilter: "blur(8px)", padding: "4px 8px", borderRadius: 2 }}>
              Hover para zoom
            </div>
          </div>

          {/* Thumbnails (alt arts) */}
          <div style={{ display: "flex", gap: 8, marginTop: 12 }}>
            {[0,1,2,3].map(i => (
              <div key={i} style={{ width: 60, aspectRatio: "5/7", background: "var(--carbon)", border: "1px solid " + (i === 0 ? "var(--carmesi)" : "var(--line)"), borderRadius: 2, overflow: "hidden", cursor: "pointer" }}>
                <img src={card.img} style={{ width: "100%", height: "100%", objectFit: "cover", opacity: i === 0 ? 1 : 0.5 }} />
              </div>
            ))}
          </div>
        </div>

        {/* RIGHT: Details + variants */}
        <div>
          {/* Header */}
          <div style={{ display: "flex", alignItems: "center", gap: 8, marginBottom: 10 }}>
            <SetIcon code={card.set} size={18} />
            <span className="mono" style={{ fontSize: 11, color: "var(--mid-2)", letterSpacing: "0.12em", textTransform: "uppercase" }}>{card.setName} · #142 / 274</span>
          </div>
          <h1 className="d-xl" style={{ fontSize: 56 }}>{card.name}</h1>
          
          <div style={{ display: "flex", alignItems: "center", gap: 10, marginTop: 12, flexWrap: "wrap" }}>
            {/* mana cost */}
            {card.colors.map((c, i) => <Mana key={i} c={c} />)}
            {card.colors.length === 0 && card.cmc > 0 && <Mana c="C" />}
            <div style={{ height: 16, width: 1, background: "var(--line-2)", margin: "0 4px" }} />
            <span style={{ fontSize: 14, color: "var(--bone-2)" }}>{card.type}</span>
            <span className={`badge ${card.rarity === "mythic" ? "badge-mythic" : card.rarity === "rare" ? "badge-rare" : "badge-uncommon"}`}>{card.rarity.toUpperCase()}</span>
          </div>

          {/* Oracle text box */}
          <div style={{ marginTop: 24, padding: "20px 24px", background: "var(--carbon)", border: "1px solid var(--line)", borderRadius: 3 }}>
            <div style={{ fontStyle: "italic", fontSize: 13, color: "var(--mid-2)", marginBottom: 12, lineHeight: 1.6, fontFamily: "'Playfair Display', serif" }}>
              "{card.name === 'Lightning Bolt' ? 'The sparkmage shrieked, calling on the rage of the storms of her youth.' : 'Un texto de ambientación evocador que cabe aquí.'}"
            </div>
            <div style={{ fontSize: 14, lineHeight: 1.6, color: "var(--bone-2)" }}>
              {card.name === 'Lightning Bolt' 
                ? <>Lightning Bolt hace <b style={{ color: "var(--bone)" }}>3 de daño</b> a cualquier objetivo.</>
                : <>Texto de reglas oficial de la carta. Habilidades, palabras clave, costes adicionales y cualquier mecánica específica aparecen aquí con formato consistente para lectura rápida.</>}
            </div>
            {card.name === 'Lightning Bolt' && (
              <div style={{ marginTop: 16, paddingTop: 16, borderTop: "1px dashed var(--line-2)", fontSize: 11, color: "var(--mid-2)", letterSpacing: "0.04em" }}>
                Legal en: <span style={{ color: "var(--bone-2)" }}>Modern</span> · <span style={{ color: "var(--bone-2)" }}>Legacy</span> · <span style={{ color: "var(--bone-2)" }}>Pioneer (no)</span> · <span style={{ color: "var(--bone-2)" }}>Commander</span> · <span style={{ color: "var(--bone-2)" }}>Pauper</span>
              </div>
            )}
          </div>

          {/* Price summary */}
          <div style={{ marginTop: 20, padding: "16px 20px", background: "rgba(214,40,40,0.06)", border: "1px solid rgba(214,40,40,0.3)", borderRadius: 3, display: "flex", justifyContent: "space-between", alignItems: "center" }}>
            <div>
              <div style={{ fontSize: 11, color: "var(--mid-2)", letterSpacing: "0.1em", textTransform: "uppercase" }}>Desde</div>
              <div className="d-xl" style={{ color: "var(--carmesi)", fontSize: 40 }}>{formatCLP(cheapest)}</div>
            </div>
            <div style={{ textAlign: "right", fontSize: 12, color: "var(--mid-2)" }}>
              <div><span className="mono" style={{ color: "var(--bone)", fontSize: 16 }}>{variants.length}</span> variantes en stock</div>
              <div style={{ marginTop: 4 }}><span className="mono" style={{ color: "var(--bone)" }}>{available}</span> unidades totales</div>
            </div>
          </div>

          {/* VARIANT SELECTOR — the critical screen */}
          <div style={{ marginTop: 32 }}>
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-end", marginBottom: 14 }}>
              <div>
                <div className="mono" style={{ fontSize: 11, color: "var(--carmesi)", letterSpacing: "0.16em", textTransform: "uppercase", marginBottom: 6 }}>Selecciona tu versión</div>
                <h2 className="d-md">Variantes disponibles</h2>
              </div>
              <span style={{ fontSize: 11, color: "var(--mid-2)" }}>{filteredVariants.length} de {variants.length}</span>
            </div>

            {/* Filter tabs above table */}
            <div style={{ display: "flex", gap: 6, marginBottom: 10, flexWrap: "wrap", alignItems: "center" }}>
              <span style={{ fontSize: 10, color: "var(--mid-2)", letterSpacing: "0.1em", textTransform: "uppercase", marginRight: 4 }}>Foil:</span>
              {[["all","Todo"],["nonfoil","Regular"],["foil","Foil"]].map(([v, l]) => (
                <button key={v} onClick={() => setFoilTab(v)} className={foilTab === v ? "btn btn-primary btn-sm" : "btn btn-secondary btn-sm"} style={{ padding: "4px 10px", fontSize: 11 }}>{l}</button>
              ))}
              <div style={{ width: 1, height: 18, background: "var(--line-2)", margin: "0 6px" }} />
              <span style={{ fontSize: 10, color: "var(--mid-2)", letterSpacing: "0.1em", textTransform: "uppercase", marginRight: 4 }}>Condición:</span>
              {["all","NM","LP","SP","MP","HP"].map(c => (
                <button key={c} onClick={() => setCondFilter(c)} className={condFilter === c ? "btn btn-primary btn-sm" : "btn btn-secondary btn-sm"} style={{ padding: "4px 10px", fontSize: 11, fontFamily: c === "all" ? "inherit" : "var(--mono)" }}>{c === "all" ? "Todas" : c}</button>
              ))}
            </div>

            {/* The table */}
            <div style={{ background: "var(--carbon)", border: "1px solid var(--line)", borderRadius: 3, overflow: "hidden" }}>
              <table className="table-variants">
                <thead>
                  <tr>
                    <th style={{ width: "35%" }}>Set / Edición</th>
                    <th style={{ width: 80 }}>Cond.</th>
                    <th style={{ width: 70 }}>Idioma</th>
                    <th style={{ width: 70 }}>Foil</th>
                    <th style={{ width: 90, textAlign: "right" }}>Precio</th>
                    <th style={{ width: 70 }}>Stock</th>
                    <th style={{ width: 130, textAlign: "right" }}>Cantidad</th>
                    <th style={{ width: 110, textAlign: "right" }}></th>
                  </tr>
                </thead>
                <tbody>
                  {filteredVariants.map((v, i) => {
                    const key = `${v.set}-${v.condition}-${v.lang}-${v.foil}`;
                    const qty = quantities[key] || 0;
                    const isLow = v.stock <= 2;
                    return (
                      <tr key={key}>
                        <td>
                          <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                            <SetIcon code={v.set} size={14} />
                            <div style={{ minWidth: 0 }}>
                              <div style={{ fontSize: 13, fontWeight: 500, color: "var(--bone)" }}>{v.setName}</div>
                              <div className="mono" style={{ fontSize: 10, color: "var(--mid-2)" }}>{v.set}</div>
                            </div>
                          </div>
                        </td>
                        <td>
                          <ConditionBadge c={v.condition} />
                        </td>
                        <td>
                          <span className="mono" style={{ fontSize: 11, color: "var(--bone-2)" }}>{v.lang}</span>
                        </td>
                        <td>
                          {v.foil ? <span className="badge badge-foil">FOIL</span> : <span style={{ fontSize: 11, color: "var(--mid)" }}>—</span>}
                        </td>
                        <td style={{ textAlign: "right" }}>
                          <div className="d-sm" style={{ color: "var(--carmesi)", fontSize: 18 }}>{formatCLP(v.price)}</div>
                        </td>
                        <td>
                          <div style={{ display: "flex", alignItems: "center", gap: 6 }}>
                            <div style={{ width: 6, height: 6, borderRadius: "50%", background: isLow ? "var(--amber)" : "var(--green)" }} />
                            <span className="mono" style={{ fontSize: 11, color: isLow ? "var(--amber)" : "var(--bone-2)" }}>{v.stock}</span>
                          </div>
                        </td>
                        <td>
                          <div style={{ display: "flex", alignItems: "center", gap: 0, justifyContent: "flex-end" }}>
                            <button onClick={() => setQty(key, qty - 1)} style={{ width: 28, height: 28, background: "var(--carbon-2)", border: "1px solid var(--line-2)", color: "var(--bone-2)", cursor: "pointer", borderRadius: "2px 0 0 2px" }}>−</button>
                            <input value={qty} onChange={(e) => setQty(key, Math.min(v.stock, +e.target.value || 0))}
                              style={{ width: 38, height: 28, background: "var(--carbon)", border: "1px solid var(--line-2)", borderLeft: "none", borderRight: "none", color: "var(--bone)", textAlign: "center", fontFamily: "var(--mono)", fontSize: 12, outline: "none" }} />
                            <button onClick={() => setQty(key, Math.min(v.stock, qty + 1))} style={{ width: 28, height: 28, background: "var(--carbon-2)", border: "1px solid var(--line-2)", color: "var(--bone-2)", cursor: "pointer", borderRadius: "0 2px 2px 0" }}>+</button>
                          </div>
                        </td>
                        <td style={{ textAlign: "right" }}>
                          <button 
                            className="btn btn-primary btn-sm"
                            disabled={qty === 0}
                            style={{ opacity: qty === 0 ? 0.5 : 1 }}
                            onClick={() => { addToCart({ ...card, ...v }, qty); setQty(key, 0); }}>
                            Agregar
                          </button>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
              {filteredVariants.length === 0 && (
                <div style={{ padding: 40, textAlign: "center", color: "var(--mid-2)", fontSize: 13 }}>
                  Sin variantes que coincidan con los filtros. <span style={{ color: "var(--carmesi)", cursor: "pointer" }} onClick={() => { setCondFilter("all"); setFoilTab("all"); }}>Limpiar</span>
                </div>
              )}
            </div>

            <div style={{ marginTop: 16, padding: "12px 16px", background: "var(--carbon-2)", border: "1px solid var(--line)", borderRadius: 3, display: "flex", gap: 12, alignItems: "flex-start", fontSize: 12, color: "var(--mid-2)", lineHeight: 1.6 }}>
              <span style={{ color: "var(--carmesi)", flexShrink: 0, fontSize: 14, marginTop: 1 }}>ⓘ</span>
              <div>
                <b style={{ color: "var(--bone-2)" }}>Escala de condición:</b> NM = Near Mint (impecable), LP = Lightly Played (leve uso), SP = Slightly Played, MP = Moderately Played, HP = Heavily Played, DMG = Damaged. Cada carta es revisada y clasificada individualmente por nuestro equipo.
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Other prints */}
      <section style={{ padding: "80px 32px 40px", marginTop: 40, borderTop: "1px solid var(--line)" }}>
        <div className="container">
          <div style={{ marginBottom: 24 }}>
            <div className="mono" style={{ fontSize: 11, color: "var(--mid-2)", letterSpacing: "0.16em", textTransform: "uppercase", marginBottom: 6 }}>Otras versiones</div>
            <h2 className="d-lg">Otros prints de {card.name}</h2>
          </div>
          <div style={{ display: "grid", gridTemplateColumns: "repeat(6, 1fr)", gap: 12 }}>
            {otherPrints.map((c, i) => <ProductCard key={i} card={c} onOpen={(x) => setRoute({ name: "pdp", card: x })} onAdd={addToCart} />)}
          </div>
        </div>
      </section>
    </div>
  );
}

function ConditionBadge({ c }) {
  const colors = {
    NM: { bg: "rgba(63,185,80,0.12)", fg: "#6FD98A", bd: "rgba(63,185,80,0.35)" },
    LP: { bg: "rgba(63,185,80,0.08)", fg: "#88C79A", bd: "rgba(63,185,80,0.2)" },
    SP: { bg: "rgba(245,158,11,0.1)", fg: "#F5B853", bd: "rgba(245,158,11,0.3)" },
    MP: { bg: "rgba(245,158,11,0.1)", fg: "#E8A248", bd: "rgba(245,158,11,0.25)" },
    HP: { bg: "rgba(214,40,40,0.12)", fg: "#E66565", bd: "rgba(214,40,40,0.3)" },
    DMG: { bg: "rgba(214,40,40,0.2)", fg: "#E66565", bd: "rgba(214,40,40,0.5)" }
  };
  const s = colors[c] || colors.NM;
  return (
    <span style={{ display: "inline-flex", padding: "2px 8px", fontSize: 11, fontFamily: "var(--mono)", fontWeight: 600, background: s.bg, color: s.fg, border: `1px solid ${s.bd}`, borderRadius: 2, letterSpacing: "0.02em" }}>{c}</span>
  );
}

Object.assign(window, { PDP });
