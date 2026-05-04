/* global React */
const { useState: useStateH, useMemo: useMemoH, useEffect: useEffectH } = React;

function Home({ setRoute, addToCart, heroVariant = "hybrid" }) {
  return (
    <div className="screen-enter">
      <Hero setRoute={setRoute} variant={heroVariant} />
      <JustArrived setRoute={setRoute} addToCart={addToCart} />
      <BySet setRoute={setRoute} />
      <TrustBand />
      <EditorialBand />
    </div>
  );
}

/* ---------- HERO ---------- */
function Hero({ setRoute, variant }) {
  const featured = window.MTG_CARDS.slice(0, 6);
  const [q, setQ] = useStateH("");
  const suggestions = useMemoH(() => {
    if (!q.trim()) return window.MTG_CARDS.slice(0, 4);
    return window.MTG_CARDS.filter(c => c.name.toLowerCase().includes(q.toLowerCase())).slice(0, 4);
  }, [q]);

  if (variant === "catalog") return <HeroCatalog setRoute={setRoute} />;
  if (variant === "search") return <HeroSearch setRoute={setRoute} q={q} setQ={setQ} suggestions={suggestions} />;

  // Default: hybrid (search + floating card stack)
  return (
    <section style={{ position: "relative", overflow: "hidden", borderBottom: "1px solid var(--line)" }}>
      {/* Atmospheric lamp glow */}
      <div style={{ position: "absolute", top: "-30%", left: "50%", transform: "translateX(-50%)", width: "80%", height: "120%", background: "radial-gradient(ellipse at center, rgba(214,40,40,0.12) 0%, rgba(232,176,75,0.04) 30%, transparent 60%)", pointerEvents: "none" }} />

      <div className="container" style={{ position: "relative", padding: "72px 32px 96px", minHeight: 640 }}>
        <div style={{ display: "grid", gridTemplateColumns: "1.15fr 1fr", gap: 48, alignItems: "center" }}>
          {/* Left: copy + search */}
          <div style={{ position: "relative", zIndex: 2 }}>
            <div style={{ display: "flex", alignItems: "center", gap: 10, marginBottom: 20 }}>
              <span style={{ height: 1, width: 32, background: "var(--carmesi)" }} />
              <span className="mono" style={{ fontSize: 11, letterSpacing: "0.2em", color: "var(--carmesi)", textTransform: "uppercase" }}>Singles · Chile · desde 2019</span>
            </div>
            <h1 className="d-xxl" style={{ marginBottom: 8 }}>
              La carta<br/>
              <span style={{ color: "var(--carmesi)" }}>exacta</span>,<br/>
              <span style={{ fontStyle: "italic", fontFamily: "'Playfair Display', serif', serif", fontSize: "0.7em", fontWeight: 700 }}>sin rodeos.</span>
            </h1>
            <p style={{ color: "var(--mid-2)", fontSize: 16, maxWidth: 440, marginTop: 20, lineHeight: 1.6 }}>
              Miles de singles de <b style={{ color: "var(--bone)" }}>Magic: The Gathering</b> clasificados por condición, set, idioma y foil. Envíos a todo Chile y retiro gratis en nuestra tienda de Merced 832.
            </p>

            {/* Prominent search */}
            <div style={{ marginTop: 32, position: "relative" }}>
              <div style={{ position: "relative" }}>
                <svg style={{ position: "absolute", left: 18, top: "50%", transform: "translateY(-50%)", color: "var(--mid-2)" }} width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input
                  value={q}
                  onChange={(e) => setQ(e.target.value)}
                  placeholder="Lightning Bolt, Ragavan, Force of Negation…"
                  style={{
                    width: "100%", background: "var(--carbon)", border: "1px solid var(--line-2)",
                    color: "var(--bone)", padding: "18px 24px 18px 50px",
                    fontFamily: "var(--body)", fontSize: 16, borderRadius: 3, outline: "none",
                    fontWeight: 500
                  }}
                  onFocus={(e) => e.target.style.borderColor = "var(--carmesi)"}
                  onBlur={(e) => e.target.style.borderColor = "var(--line-2)"}
                />
                <button
                  className="btn btn-primary"
                  style={{ position: "absolute", right: 6, top: 6, bottom: 6, padding: "0 22px" }}
                  onClick={() => setRoute({ name: "listing", query: q })}
                >
                  Buscar
                </button>
              </div>
              {/* Autocomplete preview — always visible for drama */}
              <div style={{ marginTop: 10, background: "var(--carbon)", border: "1px solid var(--line)", borderRadius: 3, overflow: "hidden" }}>
                <div style={{ padding: "8px 14px", fontSize: 10, color: "var(--mid-2)", letterSpacing: "0.12em", textTransform: "uppercase", background: "var(--carbon-2)", borderBottom: "1px solid var(--line)" }}>
                  {q.trim() ? "Sugerencias" : "Populares ahora"}
                </div>
                {suggestions.map((s, i) => (
                  <div key={i} onClick={() => setRoute({ name: "pdp", card: s })}
                    style={{ display: "flex", alignItems: "center", gap: 12, padding: "10px 14px", cursor: "pointer", borderBottom: i < suggestions.length - 1 ? "1px solid var(--line)" : "none" }}
                    onMouseEnter={(e) => e.currentTarget.style.background = "rgba(255,255,255,0.03)"}
                    onMouseLeave={(e) => e.currentTarget.style.background = "transparent"}>
                    <img src={s.img} style={{ width: 32, height: 45, objectFit: "cover", borderRadius: 2 }} />
                    <div style={{ flex: 1, minWidth: 0 }}>
                      <div style={{ fontSize: 14, fontWeight: 500 }}>{s.name}</div>
                      <div style={{ fontSize: 11, color: "var(--mid-2)", display: "flex", alignItems: "center", gap: 6, marginTop: 2 }}>
                        <SetIcon code={s.set} size={11} /> {s.setName} · {s.condition} · {s.lang}
                      </div>
                    </div>
                    <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                      {s.colors.map((c, j) => <Mana key={j} c={c} size="sm" />)}
                      <div className="d-sm" style={{ color: "var(--carmesi)", marginLeft: 8 }}>{formatCLP(s.price)}</div>
                    </div>
                  </div>
                ))}
              </div>
            </div>

            <div style={{ display: "flex", gap: 20, marginTop: 28, flexWrap: "wrap", fontSize: 12, color: "var(--mid-2)" }}>
              <StatChip num="+14.200" label="Singles en stock" />
              <StatChip num="180+" label="Sets disponibles" />
              <StatChip num="24h" label="Despacho típico" />
            </div>
          </div>

          {/* Right: floating card stack */}
          <div style={{ position: "relative", height: 580 }}>
            <FloatingCard card={featured[0]} x={60} y={20} r={-8} z={1} delay={0} />
            <FloatingCard card={featured[1]} x={200} y={80} r={4} z={3} delay={0.4} featured />
            <FloatingCard card={featured[2]} x={0} y={240} r={-14} z={2} delay={0.8} />
            <FloatingCard card={featured[3]} x={280} y={320} r={10} z={1} delay={1.2} />
            <FloatingCard card={featured[4]} x={140} y={380} r={-2} z={2} delay={1.6} small />
          </div>
        </div>
      </div>
    </section>
  );
}

function FloatingCard({ card, x, y, r, z, delay, featured, small }) {
  return (
    <div style={{
      position: "absolute",
      left: x, top: y,
      transform: `rotate(${r}deg)`,
      zIndex: z,
      width: small ? 160 : featured ? 240 : 200,
      aspectRatio: "5 / 7",
      boxShadow: featured ? "0 24px 60px rgba(0,0,0,0.8), 0 0 0 1px rgba(232,176,75,0.4)" : "0 16px 40px rgba(0,0,0,0.6)",
      borderRadius: 8,
      overflow: "hidden",
      animation: `floatCard 6s ease-in-out infinite`,
      animationDelay: `${delay}s`,
      border: featured ? "1px solid var(--gold)" : "1px solid var(--line-2)"
    }}>
      <img src={card.img} style={{ width: "100%", height: "100%", objectFit: "cover" }} />
      {featured && (
        <div style={{ position: "absolute", bottom: 0, left: 0, right: 0, background: "linear-gradient(to top, rgba(10,10,10,0.95), transparent)", padding: "20px 12px 12px" }}>
          <div style={{ fontSize: 10, color: "var(--gold)", letterSpacing: "0.12em", textTransform: "uppercase", fontWeight: 600 }}>Destacado</div>
          <div style={{ fontSize: 13, fontWeight: 600, color: "var(--bone)", marginTop: 2 }}>{card.name}</div>
          <div className="d-md" style={{ color: "var(--carmesi)", fontSize: 22, marginTop: 4 }}>{formatCLP(card.price)}</div>
        </div>
      )}
      <style>{`
        @keyframes floatCard {
          0%, 100% { translate: 0 0; }
          50% { translate: 0 -8px; }
        }
      `}</style>
    </div>
  );
}

function StatChip({ num, label }) {
  return (
    <div style={{ display: "flex", alignItems: "baseline", gap: 8, paddingRight: 20, borderRight: "1px solid var(--line)" }}>
      <span className="d-md" style={{ color: "var(--bone)", fontSize: 24 }}>{num}</span>
      <span style={{ fontSize: 11, color: "var(--mid-2)", letterSpacing: "0.08em", textTransform: "uppercase" }}>{label}</span>
    </div>
  );
}

/* Alternate hero — Catalog */
function HeroCatalog({ setRoute }) {
  const feat = window.MTG_CARDS.slice(0, 5);
  return (
    <section style={{ borderBottom: "1px solid var(--line)", padding: "48px 0" }}>
      <div className="container">
        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-end", marginBottom: 32 }}>
          <div>
            <div className="mono" style={{ fontSize: 11, color: "var(--carmesi)", letterSpacing: "0.2em", textTransform: "uppercase", marginBottom: 12 }}>La vitrina · Editorial</div>
            <h1 className="d-xxl">Singles<br/><span style={{ color: "var(--carmesi)" }}>recién</span> ingresados</h1>
          </div>
          <button className="btn btn-secondary btn-lg" onClick={() => setRoute({ name: "listing" })}>Ver catálogo completo →</button>
        </div>
        <div style={{ display: "grid", gridTemplateColumns: "repeat(5, 1fr)", gap: 20 }}>
          {feat.map((c, i) => <ProductCard key={i} card={c} onOpen={(card) => setRoute({ name: "pdp", card })} />)}
        </div>
      </div>
    </section>
  );
}

function HeroSearch({ setRoute, q, setQ, suggestions }) {
  return (
    <section style={{ borderBottom: "1px solid var(--line)", padding: "120px 0 80px", textAlign: "center", position: "relative" }}>
      <div className="container">
        <h1 className="d-xxl" style={{ marginBottom: 24 }}>¿Qué carta <span style={{ color: "var(--carmesi)" }}>buscas</span>?</h1>
        <p style={{ color: "var(--mid-2)", fontSize: 16, maxWidth: 560, margin: "0 auto 40px" }}>
          Escribe el nombre. Empieza a escribir y aparecen las coincidencias de nuestro stock real en Santiago.
        </p>
        <div style={{ maxWidth: 720, margin: "0 auto", position: "relative" }}>
          <input
            value={q}
            onChange={(e) => setQ(e.target.value)}
            placeholder="Lightning Bolt…"
            style={{ width: "100%", background: "var(--carbon)", border: "1px solid var(--line-2)", color: "var(--bone)", padding: "22px 24px", fontSize: 18, borderRadius: 3, outline: "none" }}
          />
        </div>
      </div>
    </section>
  );
}

/* ---------- JUST ARRIVED ---------- */
function JustArrived({ setRoute, addToCart }) {
  const cards = window.MTG_CARDS.slice(5, 17);
  const [scroll, setScroll] = useStateH(0);
  const ref = React.useRef(null);

  return (
    <section style={{ padding: "64px 0 48px", borderBottom: "1px solid var(--line)" }}>
      <div className="container">
        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-end", marginBottom: 28 }}>
          <div>
            <div className="mono" style={{ fontSize: 11, color: "var(--mid-2)", letterSpacing: "0.2em", textTransform: "uppercase", marginBottom: 10 }}>Nuevos en stock</div>
            <h2 className="d-xl">Singles recién ingresados</h2>
          </div>
          <div style={{ display: "flex", gap: 8 }}>
            <button className="btn btn-secondary" onClick={() => { ref.current && ref.current.scrollBy({ left: -400, behavior: "smooth" }); }}>←</button>
            <button className="btn btn-secondary" onClick={() => { ref.current && ref.current.scrollBy({ left: 400, behavior: "smooth" }); }}>→</button>
            <button className="btn btn-ghost" onClick={() => setRoute({ name: "listing" })}>Ver todos →</button>
          </div>
        </div>
        <div ref={ref} style={{ display: "grid", gridAutoFlow: "column", gridAutoColumns: "minmax(220px, 1fr)", gap: 16, overflowX: "auto", scrollSnapType: "x mandatory", paddingBottom: 8 }}>
          {cards.map((c, i) => (
            <div key={i} style={{ scrollSnapAlign: "start" }}>
              <ProductCard card={c} onOpen={(card) => setRoute({ name: "pdp", card })} onAdd={addToCart} />
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}

/* ---------- BY SET ---------- */
function BySet({ setRoute }) {
  const sets = [
    { code: "MH3", name: "Modern Horizons 3", year: 2024, count: 432, card: window.MTG_CARDS.find(c => c.set === "MH3") },
    { code: "LTR", name: "Lord of the Rings: Tales of Middle-Earth", year: 2023, count: 521, card: window.MTG_CARDS.find(c => c.set === "LTR") },
    { code: "MH2", name: "Modern Horizons 2", year: 2021, count: 368, card: window.MTG_CARDS.find(c => c.set === "MH2") },
    { code: "NEO", name: "Kamigawa: Neon Dynasty", year: 2022, count: 284, card: window.MTG_CARDS.find(c => c.set === "NEO") },
    { code: "DMU", name: "Dominaria United", year: 2022, count: 192, card: window.MTG_CARDS.find(c => c.set === "DMU") },
    { code: "2XM", name: "Double Masters", year: 2020, count: 156, card: window.MTG_CARDS.find(c => c.set === "2XM") },
  ];
  return (
    <section style={{ padding: "64px 0", borderBottom: "1px solid var(--line)" }}>
      <div className="container">
        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-end", marginBottom: 28 }}>
          <div>
            <div className="mono" style={{ fontSize: 11, color: "var(--mid-2)", letterSpacing: "0.2em", textTransform: "uppercase", marginBottom: 10 }}>Explorar por set</div>
            <h2 className="d-xl">Los sets que <span style={{ color: "var(--carmesi)" }}>importan</span></h2>
          </div>
          <div className="btn btn-ghost">Ver todos los sets →</div>
        </div>
        <div style={{ display: "grid", gridTemplateColumns: "repeat(3, 1fr)", gap: 20 }}>
          {sets.map((s, i) => (
            <div key={i} onClick={() => setRoute({ name: "listing", setFilter: s.code })}
              style={{ position: "relative", aspectRatio: "16 / 9", background: "var(--carbon)", border: "1px solid var(--line)", borderRadius: 3, overflow: "hidden", cursor: "pointer", transition: "all 160ms" }}
              onMouseEnter={(e) => { e.currentTarget.style.borderColor = "var(--carmesi)"; }}
              onMouseLeave={(e) => { e.currentTarget.style.borderColor = "var(--line)"; }}>
              {s.card && (
                <img src={s.card.img} style={{ position: "absolute", right: -20, top: "50%", transform: "translateY(-50%) rotate(8deg)", height: "130%", opacity: 0.45, filter: "saturate(0.9)" }} />
              )}
              <div style={{ position: "absolute", inset: 0, background: "linear-gradient(90deg, rgba(10,10,10,0.95) 30%, rgba(10,10,10,0.4) 70%, transparent)" }} />
              <div style={{ position: "relative", padding: "24px 28px", height: "100%", display: "flex", flexDirection: "column", justifyContent: "space-between" }}>
                <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
                  <SetIcon code={s.code} size={24} />
                  <div className="mono" style={{ fontSize: 11, color: "var(--mid-2)", letterSpacing: "0.12em", textTransform: "uppercase" }}>{s.code} · {s.year}</div>
                </div>
                <div>
                  <div className="d-lg" style={{ color: "var(--bone)", maxWidth: "80%" }}>{s.name}</div>
                  <div style={{ display: "flex", alignItems: "center", gap: 12, marginTop: 10, fontSize: 12, color: "var(--mid-2)" }}>
                    <span className="mono" style={{ color: "var(--bone-2)" }}>{s.count} singles</span>
                    <span style={{ color: "var(--carmesi)", fontWeight: 600 }}>Explorar →</span>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}

/* ---------- TRUST ---------- */
function TrustBand() {
  const items = [
    { title: "Retiro gratis en tienda", sub: "Merced 832 · Galería Casa Colorada · Santiago Centro", icon: "🏪" },
    { title: "Despachos a todo Chile", sub: "Chilexpress · entregamos desde el día siguiente", icon: "📦" },
    { title: "Webpay & Mercado Pago", sub: "Pagos protegidos y cuotas sin interés en débito", icon: "💳" },
    { title: "Condición garantizada", sub: "Cada carta inspeccionada y clasificada NM/LP/SP/MP/HP", icon: "🔍" }
  ];
  return (
    <section style={{ padding: "40px 0", borderBottom: "1px solid var(--line)", background: "var(--carbon)" }}>
      <div className="container">
        <div style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: 32 }}>
          {items.map((it, i) => (
            <div key={i} style={{ display: "flex", gap: 14, paddingRight: 20, borderRight: i < 3 ? "1px solid var(--line)" : "none" }}>
              <div style={{ fontSize: 22, filter: "grayscale(0.3)" }}>{it.icon}</div>
              <div>
                <div className="d-sm" style={{ color: "var(--bone)", marginBottom: 4 }}>{it.title}</div>
                <div style={{ fontSize: 12, color: "var(--mid-2)", lineHeight: 1.5 }}>{it.sub}</div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}

/* ---------- EDITORIAL BAND ---------- */
function EditorialBand() {
  return (
    <section style={{ padding: "80px 0", background: "linear-gradient(180deg, var(--black), var(--carbon))" }}>
      <div className="container">
        <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 64, alignItems: "center" }}>
          <div>
            <div className="mono" style={{ fontSize: 11, color: "var(--carmesi)", letterSpacing: "0.2em", textTransform: "uppercase", marginBottom: 16 }}>Vende tus cartas</div>
            <h2 className="d-xl" style={{ marginBottom: 20 }}>¿Tienes cartas<br/>que no usas?</h2>
            <p style={{ color: "var(--mid-2)", fontSize: 15, lineHeight: 1.7, maxWidth: 460, marginBottom: 24 }}>
              Te compramos tu colección de Magic: un singles, un Commander deck, o binders completos. Tasación justa basada en precios actualizados y pago vía transferencia o crédito en tienda (con bonus de 20%).
            </p>
            <div style={{ display: "flex", gap: 12 }}>
              <button className="btn btn-primary btn-lg">Solicitar tasación</button>
              <button className="btn btn-secondary btn-lg">Cómo funciona</button>
            </div>
          </div>
          <div style={{ position: "relative", aspectRatio: "4/3", background: "var(--carbon)", border: "1px solid var(--line)", borderRadius: 3, overflow: "hidden", padding: 32 }}>
            <div style={{ position: "absolute", inset: 0, background: "radial-gradient(circle at 30% 30%, rgba(214,40,40,0.15), transparent 60%)" }} />
            <div style={{ position: "relative", display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: 10, transform: "rotate(-4deg)" }}>
              {window.MTG_CARDS.slice(10, 22).map((c, i) => (
                <img key={i} src={c.img} style={{ aspectRatio: "5/7", width: "100%", objectFit: "cover", borderRadius: 3, boxShadow: "0 4px 12px rgba(0,0,0,0.5)" }} />
              ))}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}

Object.assign(window, { Home });
