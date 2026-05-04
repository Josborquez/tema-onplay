/* global React */
/* Unified PDP for One Piece + Riftbound. Magic still uses its own PDP. */
const { useState: useStatePDP2, useMemo: useMemoPDP2 } = React;

/* ---------- One Piece PDP ---------- */
function OPPDP({ card, setRoute, addToCart }) {
  const [zoomPos, setZoomPos] = useStatePDP2(null);
  const [condFilter, setCondFilter] = useStatePDP2("all");
  const [foilTab, setFoilTab] = useStatePDP2("all");
  const [quantities, setQuantities] = useStatePDP2({});

  // Build synthetic variants from the card itself (mock multiple printings)
  const variants = useMemoPDP2(() => {
    const base = card;
    const list = [
      { ...base, condition: "NM", lang: "EN", foil: false, price: base.price, stock: base.stock },
      { ...base, condition: "NM", lang: "EN", foil: true, price: Math.round(base.price * 2.4), stock: Math.max(1, Math.floor(base.stock / 3)) },
      { ...base, condition: "LP", lang: "EN", foil: false, price: Math.round(base.price * 0.85), stock: Math.max(1, Math.floor(base.stock / 2)) },
      { ...base, condition: "NM", lang: "JP", foil: false, price: Math.round(base.price * 1.1), stock: Math.max(1, Math.floor(base.stock / 4)) },
      { ...base, condition: "SP", lang: "EN", foil: false, price: Math.round(base.price * 0.7), stock: Math.max(1, Math.floor(base.stock / 5)) },
    ];
    return list;
  }, [card]);

  const filtered = variants.filter(v => {
    if (condFilter !== "all" && v.condition !== condFilter) return false;
    if (foilTab === "foil" && !v.foil) return false;
    if (foilTab === "nonfoil" && v.foil) return false;
    return true;
  });

  const cheapest = Math.min(...variants.map(v => v.price));
  const totalStock = variants.reduce((s, v) => s + v.stock, 0);

  const setQty = (key, val) => setQuantities({ ...quantities, [key]: Math.max(0, val) });

  const otherCards = (window.OP_CARDS || []).filter(c => c.id !== card.id).slice(0, 6);

  // Image: use official One Piece CDN, fallback to gradient block
  const imgUrl = `https://en.onepiece-cardgame.com/images/cardlist/card/${card.id}.png`;
  const primaryColor = (card.colors || [])[0];
  const tint = {
    Red:    "linear-gradient(160deg,#3a1010,#1a0606)",
    Green:  "linear-gradient(160deg,#0f2a18,#061208)",
    Blue:   "linear-gradient(160deg,#0c2540,#04101e)",
    Yellow: "linear-gradient(160deg,#3a2c0d,#1a1404)",
    Purple: "linear-gradient(160deg,#2a1240,#10061f)",
    Black:  "linear-gradient(160deg,#1a1a1a,#000)",
    Multicolor: "linear-gradient(135deg,#3a1010,#3a2c0d,#0c2540)",
  }[primaryColor] || "var(--carbon-2)";

  return (
    <div className="screen-enter">
      <div style={{ padding: "14px 0", borderBottom: "1px solid var(--line)", background: "var(--carbon)" }}>
        <div className="container" style={{ fontSize: 12, color: "var(--mid-2)", padding: "0 32px" }}>
          <span onClick={() => setRoute({ name: "home" })} style={{ cursor: "pointer" }}>Inicio</span>
          <span style={{ margin: "0 8px" }}>/</span>
          <span onClick={() => setRoute({ name: "listing-op" })} style={{ cursor: "pointer" }}>One Piece Card Game</span>
          <span style={{ margin: "0 8px" }}>/</span>
          <span style={{ color: "var(--bone)" }}>{card.name}</span>
        </div>
      </div>

      <div className="container" style={{ padding: "40px 32px 0", display: "grid", gridTemplateColumns: "1fr 1.15fr", gap: 48 }}>
        {/* LEFT: Image + zoom */}
        <div>
          <div
            style={{ position: "sticky", top: 110, aspectRatio: "5 / 7", maxHeight: "calc(100vh - 130px)", background: tint, border: "1px solid var(--line)", borderRadius: 4, overflow: "hidden", cursor: "zoom-in", display: "flex", alignItems: "flex-end", padding: 16 }}
            onMouseMove={(e) => {
              const r = e.currentTarget.getBoundingClientRect();
              setZoomPos({ x: ((e.clientX - r.left) / r.width) * 100, y: ((e.clientY - r.top) / r.height) * 100 });
            }}
            onMouseLeave={() => setZoomPos(null)}
          >
            <img src={imgUrl}
              onError={(e) => { e.target.style.display = "none"; }}
              style={{ position: "absolute", inset: 0, width: "100%", height: "100%", objectFit: "cover", transform: zoomPos ? "scale(2)" : "scale(1)", transformOrigin: zoomPos ? `${zoomPos.x}% ${zoomPos.y}%` : "center", transition: zoomPos ? "transform 0s" : "transform 200ms" }} />
            <div style={{ position: "absolute", top: 12, left: 12, zIndex: 2 }}>
              <OPRarity r={card.rarity} />
            </div>
            <div style={{ position: "absolute", top: 12, right: 12, display: "flex", gap: 4, zIndex: 2 }}>
              {(card.colors || []).map((c, i) => <OPColor key={i} c={c} size={20} />)}
            </div>
            <div style={{ position: "relative", zIndex: 1, color: "rgba(255,255,255,0.9)", fontFamily: "var(--display)", fontSize: 32, lineHeight: 1, textShadow: "0 2px 12px rgba(0,0,0,0.8)" }}>{card.name}</div>
            <div style={{ position: "absolute", bottom: 12, right: 12, zIndex: 2, color: "var(--mid-2)", fontSize: 10, letterSpacing: "0.08em", textTransform: "uppercase", background: "rgba(10,10,10,0.7)", backdropFilter: "blur(8px)", padding: "4px 8px", borderRadius: 2 }}>
              Hover para zoom
            </div>
          </div>
        </div>

        {/* RIGHT: Details + variants */}
        <div>
          <div style={{ display: "flex", alignItems: "center", gap: 8, marginBottom: 10 }}>
            <span className="mono" style={{ fontSize: 11, color: "var(--mid-2)", letterSpacing: "0.12em", textTransform: "uppercase" }}>{card.setName} · {card.id}</span>
          </div>

          <h1 className="d-xl" style={{ fontSize: 56, lineHeight: 1 }}>{card.name}</h1>

          <div style={{ display: "flex", alignItems: "center", gap: 10, marginTop: 12, flexWrap: "wrap" }}>
            <OPTypePill t={card.cardType} />
            {card.attribute && <span style={{ display: "flex", alignItems: "center", gap: 6 }}>
              <OPAttribute a={card.attribute} size={16} />
              <span style={{ fontSize: 12, color: "var(--bone-2)" }}>{card.attribute}</span>
            </span>}
            {(card.colors || []).map((c, i) => <span key={i} style={{ display: "flex", alignItems: "center", gap: 4 }}>
              <OPColor c={c} size={16} />
              <span style={{ fontSize: 12, color: "var(--bone-2)" }}>{c}</span>
            </span>)}
            <OPRarity r={card.rarity} />
          </div>

          {/* Stat row */}
          <div style={{ marginTop: 20, display: "flex", gap: 12 }}>
            {card.cost != null && <PDPStat label="COSTO (DON!!)" v={card.cost} />}
            {card.power != null && <PDPStat label="POWER" v={card.power.toLocaleString()} />}
            {card.life != null && <PDPStat label="LIFE" v={card.life} />}
            {card.counter != null && <PDPStat label="COUNTER" v={card.counter ? `+${card.counter}` : "—"} />}
          </div>

          {/* Effect text box */}
          <div style={{ marginTop: 24, padding: "20px 24px", background: "var(--carbon)", border: "1px solid var(--line)", borderRadius: 3 }}>
            <div style={{ fontStyle: "italic", fontSize: 13, color: "var(--mid-2)", marginBottom: 12, lineHeight: 1.6, fontFamily: "'Playfair Display', serif" }}>
              "{card.flavor || `Una carta del set ${card.setName}.`}"
            </div>
            <div style={{ fontSize: 14, lineHeight: 1.6, color: "var(--bone-2)" }}>
              {card.typeText && <div style={{ fontSize: 11, color: "var(--mid-2)", marginBottom: 8, letterSpacing: "0.08em", textTransform: "uppercase" }}>{card.typeText}</div>}
              {card.effect || "Efecto de la carta. Las habilidades, triggers y mecánicas específicas aparecen aquí con formato consistente."}
            </div>
          </div>

          {/* Price summary */}
          <div style={{ marginTop: 20, padding: "16px 20px", background: "rgba(214,40,40,0.06)", border: "1px solid rgba(214,40,40,0.3)", borderRadius: 3, display: "flex", justifyContent: "space-between", alignItems: "center" }}>
            <div>
              <div style={{ fontSize: 11, color: "var(--mid-2)", letterSpacing: "0.1em", textTransform: "uppercase" }}>Desde</div>
              <div className="d-xl" style={{ color: "var(--carmesi)", fontSize: 40 }}>{formatCLP(cheapest)}</div>
            </div>
            <div style={{ textAlign: "right", fontSize: 12, color: "var(--mid-2)" }}>
              <div><span className="mono" style={{ color: "var(--bone)", fontSize: 16 }}>{variants.length}</span> variantes en stock</div>
              <div style={{ marginTop: 4 }}><span className="mono" style={{ color: "var(--bone)" }}>{totalStock}</span> unidades totales</div>
            </div>
          </div>

          {/* Variant table */}
          <div style={{ marginTop: 32 }}>
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-end", marginBottom: 14 }}>
              <div>
                <div className="mono" style={{ fontSize: 11, color: "var(--carmesi)", letterSpacing: "0.16em", textTransform: "uppercase", marginBottom: 6 }}>Selecciona tu versión</div>
                <h2 className="d-md">Variantes disponibles</h2>
              </div>
              <span style={{ fontSize: 11, color: "var(--mid-2)" }}>{filtered.length} de {variants.length}</span>
            </div>

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

            <div style={{ background: "var(--carbon)", border: "1px solid var(--line)", borderRadius: 3, overflow: "hidden" }}>
              <table className="table-variants">
                <thead>
                  <tr>
                    <th style={{ width: "30%" }}>Set / Edición</th>
                    <th style={{ width: 80 }}>Cond.</th>
                    <th style={{ width: 70 }}>Idioma</th>
                    <th style={{ width: 70 }}>Foil</th>
                    <th style={{ width: 100, textAlign: "right" }}>Precio</th>
                    <th style={{ width: 70 }}>Stock</th>
                    <th style={{ width: 130, textAlign: "right" }}>Cantidad</th>
                    <th style={{ width: 110, textAlign: "right" }}></th>
                  </tr>
                </thead>
                <tbody>
                  {filtered.map((v, i) => {
                    const key = `${v.id}-${v.condition}-${v.lang}-${v.foil}`;
                    const qty = quantities[key] || 0;
                    const isLow = v.stock <= 2;
                    return (
                      <tr key={key}>
                        <td>
                          <div style={{ minWidth: 0 }}>
                            <div style={{ fontSize: 13, fontWeight: 500, color: "var(--bone)" }}>{v.setName}</div>
                            <div className="mono" style={{ fontSize: 10, color: "var(--mid-2)" }}>{v.setCode}</div>
                          </div>
                        </td>
                        <td><ConditionBadge c={v.condition} /></td>
                        <td><span className="mono" style={{ fontSize: 11, color: "var(--bone-2)" }}>{v.lang}</span></td>
                        <td>{v.foil ? <span className="badge badge-foil">FOIL</span> : <span style={{ fontSize: 11, color: "var(--mid)" }}>—</span>}</td>
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
                            onClick={() => { addToCart({ ...card, ...v, set: card.setCode, img: imgUrl }, qty); setQty(key, 0); }}>
                            Agregar
                          </button>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      {/* Other cards */}
      <section style={{ padding: "80px 32px 40px", marginTop: 40, borderTop: "1px solid var(--line)" }}>
        <div className="container">
          <div style={{ marginBottom: 24 }}>
            <div className="mono" style={{ fontSize: 11, color: "var(--mid-2)", letterSpacing: "0.16em", textTransform: "uppercase", marginBottom: 6 }}>También te puede interesar</div>
            <h2 className="d-lg">Más de {card.setName}</h2>
          </div>
          <div style={{ display: "grid", gridTemplateColumns: "repeat(6, 1fr)", gap: 12 }}>
            {otherCards.map((c, i) => <OPProductCard key={i} card={c} onAdd={addToCart}
              onClickCapture={() => setRoute({ name: "pdp-op", card: c })} />)}
          </div>
        </div>
      </section>
    </div>
  );
}

/* ---------- Riftbound PDP ---------- */
function RBPDP({ card, setRoute, addToCart }) {
  const [zoomPos, setZoomPos] = useStatePDP2(null);
  const [condFilter, setCondFilter] = useStatePDP2("all");
  const [foilTab, setFoilTab] = useStatePDP2("all");
  const [quantities, setQuantities] = useStatePDP2({});

  const variants = useMemoPDP2(() => {
    const base = card;
    return [
      { ...base, condition: "NM", lang: "EN", foil: false, price: base.price, stock: base.stock },
      { ...base, condition: "NM", lang: "EN", foil: true, price: Math.round(base.price * 2.5), stock: Math.max(1, Math.floor(base.stock / 3)) },
      { ...base, condition: "LP", lang: "EN", foil: false, price: Math.round(base.price * 0.85), stock: Math.max(1, Math.floor(base.stock / 2)) },
      { ...base, condition: "NM", lang: "ES", foil: false, price: Math.round(base.price * 1.05), stock: Math.max(1, Math.floor(base.stock / 4)) },
      { ...base, condition: "SP", lang: "EN", foil: false, price: Math.round(base.price * 0.7), stock: Math.max(1, Math.floor(base.stock / 5)) },
    ];
  }, [card]);

  const filtered = variants.filter(v => {
    if (condFilter !== "all" && v.condition !== condFilter) return false;
    if (foilTab === "foil" && !v.foil) return false;
    if (foilTab === "nonfoil" && v.foil) return false;
    return true;
  });

  const cheapest = Math.min(...variants.map(v => v.price));
  const totalStock = variants.reduce((s, v) => s + v.stock, 0);
  const setQty = (key, val) => setQuantities({ ...quantities, [key]: Math.max(0, val) });

  const otherCards = (window.RB_CARDS || []).filter(c => c.id !== card.id).slice(0, 6);

  // Image attempt — will fall back to gradient block on error
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
    <div className="screen-enter">
      <div style={{ padding: "14px 0", borderBottom: "1px solid var(--line)", background: "var(--carbon)" }}>
        <div className="container" style={{ fontSize: 12, color: "var(--mid-2)", padding: "0 32px" }}>
          <span onClick={() => setRoute({ name: "home" })} style={{ cursor: "pointer" }}>Inicio</span>
          <span style={{ margin: "0 8px" }}>/</span>
          <span onClick={() => setRoute({ name: "listing-rb" })} style={{ cursor: "pointer" }}>Riftbound TCG</span>
          <span style={{ margin: "0 8px" }}>/</span>
          <span style={{ color: "var(--bone)" }}>{card.name}</span>
        </div>
      </div>

      <div className="container" style={{ padding: "40px 32px 0", display: "grid", gridTemplateColumns: "1fr 1.15fr", gap: 48 }}>
        <div>
          <div
            style={{ position: "sticky", top: 110, aspectRatio: "5 / 7", maxHeight: "calc(100vh - 130px)", background: tint, border: "1px solid var(--line)", borderRadius: 4, overflow: "hidden", cursor: "zoom-in", display: "flex", alignItems: "flex-end", padding: 16 }}
            onMouseMove={(e) => {
              const r = e.currentTarget.getBoundingClientRect();
              setZoomPos({ x: ((e.clientX - r.left) / r.width) * 100, y: ((e.clientY - r.top) / r.height) * 100 });
            }}
            onMouseLeave={() => setZoomPos(null)}
          >
            <img src={imgUrl}
              onError={(e) => { e.target.style.display = "none"; }}
              style={{ position: "absolute", inset: 0, width: "100%", height: "100%", objectFit: "cover", transform: zoomPos ? "scale(2)" : "scale(1)", transformOrigin: zoomPos ? `${zoomPos.x}% ${zoomPos.y}%` : "center", transition: zoomPos ? "transform 0s" : "transform 200ms" }} />
            <div style={{ position: "absolute", top: 12, left: 12, zIndex: 2 }}>
              <RBRarity r={card.rarity} />
            </div>
            <div style={{ position: "absolute", top: 12, right: 12, display: "flex", gap: 4, zIndex: 2 }}>
              {(card.domains || []).map((d, i) => <RBDomain key={i} d={d} size={20} />)}
            </div>
            <div style={{ position: "relative", zIndex: 1, color: "rgba(255,255,255,0.9)", fontFamily: "var(--display)", fontSize: 32, lineHeight: 1.05, textShadow: "0 2px 12px rgba(0,0,0,0.8)" }}>{card.name}</div>
            <div style={{ position: "absolute", bottom: 12, right: 12, zIndex: 2, color: "var(--mid-2)", fontSize: 10, letterSpacing: "0.08em", textTransform: "uppercase", background: "rgba(10,10,10,0.7)", backdropFilter: "blur(8px)", padding: "4px 8px", borderRadius: 2 }}>
              Hover para zoom
            </div>
          </div>
        </div>

        <div>
          <div style={{ display: "flex", alignItems: "center", gap: 8, marginBottom: 10 }}>
            <span className="mono" style={{ fontSize: 11, color: "var(--mid-2)", letterSpacing: "0.12em", textTransform: "uppercase" }}>{card.setName} · {card.id}</span>
          </div>

          <h1 className="d-xl" style={{ fontSize: 56, lineHeight: 1 }}>{card.name}</h1>

          <div style={{ display: "flex", alignItems: "center", gap: 10, marginTop: 12, flexWrap: "wrap" }}>
            <RBTypePill t={card.cardType} />
            {card.champion && <span style={{ fontSize: 12, color: "var(--bone-2)" }}>· {card.champion}</span>}
            {(card.domains || []).map((d, i) => <span key={i} style={{ display: "flex", alignItems: "center", gap: 4 }}>
              <RBDomain d={d} size={16} />
              <span style={{ fontSize: 12, color: "var(--bone-2)" }}>{d}</span>
            </span>)}
            <RBRarity r={card.rarity} />
          </div>

          <div style={{ marginTop: 20, display: "flex", gap: 12 }}>
            {card.cost != null && <PDPStat label="COSTO" v={card.cost} />}
            {card.power != null && <PDPStat label="POWER" v={card.power} />}
            {card.might != null && <PDPStat label="MIGHT" v={card.might} />}
          </div>

          <div style={{ marginTop: 24, padding: "20px 24px", background: "var(--carbon)", border: "1px solid var(--line)", borderRadius: 3 }}>
            <div style={{ fontStyle: "italic", fontSize: 13, color: "var(--mid-2)", marginBottom: 12, lineHeight: 1.6, fontFamily: "'Playfair Display', serif" }}>
              "{card.flavor || `Una carta de ${card.setName}.`}"
            </div>
            <div style={{ fontSize: 14, lineHeight: 1.6, color: "var(--bone-2)" }}>
              {card.effect || "Efecto de la carta. Las habilidades, triggers y mecánicas específicas aparecen aquí."}
            </div>
          </div>

          <div style={{ marginTop: 20, padding: "16px 20px", background: "rgba(214,40,40,0.06)", border: "1px solid rgba(214,40,40,0.3)", borderRadius: 3, display: "flex", justifyContent: "space-between", alignItems: "center" }}>
            <div>
              <div style={{ fontSize: 11, color: "var(--mid-2)", letterSpacing: "0.1em", textTransform: "uppercase" }}>Desde</div>
              <div className="d-xl" style={{ color: "var(--carmesi)", fontSize: 40 }}>{formatCLP(cheapest)}</div>
            </div>
            <div style={{ textAlign: "right", fontSize: 12, color: "var(--mid-2)" }}>
              <div><span className="mono" style={{ color: "var(--bone)", fontSize: 16 }}>{variants.length}</span> variantes en stock</div>
              <div style={{ marginTop: 4 }}><span className="mono" style={{ color: "var(--bone)" }}>{totalStock}</span> unidades totales</div>
            </div>
          </div>

          <div style={{ marginTop: 32 }}>
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-end", marginBottom: 14 }}>
              <div>
                <div className="mono" style={{ fontSize: 11, color: "var(--carmesi)", letterSpacing: "0.16em", textTransform: "uppercase", marginBottom: 6 }}>Selecciona tu versión</div>
                <h2 className="d-md">Variantes disponibles</h2>
              </div>
              <span style={{ fontSize: 11, color: "var(--mid-2)" }}>{filtered.length} de {variants.length}</span>
            </div>

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

            <div style={{ background: "var(--carbon)", border: "1px solid var(--line)", borderRadius: 3, overflow: "hidden" }}>
              <table className="table-variants">
                <thead>
                  <tr>
                    <th style={{ width: "30%" }}>Set / Edición</th>
                    <th style={{ width: 80 }}>Cond.</th>
                    <th style={{ width: 70 }}>Idioma</th>
                    <th style={{ width: 70 }}>Foil</th>
                    <th style={{ width: 100, textAlign: "right" }}>Precio</th>
                    <th style={{ width: 70 }}>Stock</th>
                    <th style={{ width: 130, textAlign: "right" }}>Cantidad</th>
                    <th style={{ width: 110, textAlign: "right" }}></th>
                  </tr>
                </thead>
                <tbody>
                  {filtered.map((v, i) => {
                    const key = `${v.id}-${v.condition}-${v.lang}-${v.foil}`;
                    const qty = quantities[key] || 0;
                    const isLow = v.stock <= 2;
                    return (
                      <tr key={key}>
                        <td>
                          <div>
                            <div style={{ fontSize: 13, fontWeight: 500, color: "var(--bone)" }}>{v.setName}</div>
                            <div className="mono" style={{ fontSize: 10, color: "var(--mid-2)" }}>{v.setCode}</div>
                          </div>
                        </td>
                        <td><ConditionBadge c={v.condition} /></td>
                        <td><span className="mono" style={{ fontSize: 11, color: "var(--bone-2)" }}>{v.lang}</span></td>
                        <td>{v.foil ? <span className="badge badge-foil">FOIL</span> : <span style={{ fontSize: 11, color: "var(--mid)" }}>—</span>}</td>
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
                            onClick={() => { addToCart({ ...card, ...v, set: card.setCode, img: imgUrl }, qty); setQty(key, 0); }}>
                            Agregar
                          </button>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <section style={{ padding: "80px 32px 40px", marginTop: 40, borderTop: "1px solid var(--line)" }}>
        <div className="container">
          <div style={{ marginBottom: 24 }}>
            <div className="mono" style={{ fontSize: 11, color: "var(--mid-2)", letterSpacing: "0.16em", textTransform: "uppercase", marginBottom: 6 }}>También te puede interesar</div>
            <h2 className="d-lg">Más de Riftbound</h2>
          </div>
          <div style={{ display: "grid", gridTemplateColumns: "repeat(6, 1fr)", gap: 12 }}>
            {otherCards.map((c, i) => <RBProductCard key={i} card={c} onAdd={addToCart}
              onClickCapture={() => setRoute({ name: "pdp-rb", card: c })} />)}
          </div>
        </div>
      </section>
    </div>
  );
}

function PDPStat({ label, v }) {
  return (
    <div style={{ padding: "10px 16px", background: "var(--carbon)", border: "1px solid var(--line)", borderRadius: 3, minWidth: 90 }}>
      <div style={{ fontSize: 9, color: "var(--mid)", letterSpacing: "0.14em", textTransform: "uppercase", marginBottom: 4 }}>{label}</div>
      <div className="d-md" style={{ color: "var(--bone)", fontSize: 22, lineHeight: 1 }}>{v}</div>
    </div>
  );
}

Object.assign(window, { OPPDP, RBPDP });
