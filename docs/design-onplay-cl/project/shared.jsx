/* global React */
const { useState, useEffect, useRef, useMemo, createContext, useContext } = React;

/* ---------- MANA SYMBOLS (SVG) ---------- */
function Mana({ c, size = "md" }) {
  const cls = `mana ${size === "sm" ? "mana-sm" : size === "lg" ? "mana-lg" : ""}`;
  const map = {
    W: { bg: "#FFFBD5", fg: "#000", glyph: "☀" },
    U: { bg: "#AAE0FA", fg: "#000", glyph: "💧" },
    B: { bg: "#CBC2BF", fg: "#000", glyph: "☠" },
    R: { bg: "#F9AA8F", fg: "#000", glyph: "🔥" },
    G: { bg: "#9BD3AE", fg: "#000", glyph: "🌳" },
    C: { bg: "#CCC2C0", fg: "#000", glyph: "◇" }
  };
  const m = map[c] || map.C;
  // Use custom SVG glyphs (no emoji — cleaner)
  const glyphs = {
    W: (<svg viewBox="0 0 24 24" width="60%" height="60%"><path fill="currentColor" d="M12 2 L14 9 L21 9 L15.5 13 L17.5 20 L12 15.5 L6.5 20 L8.5 13 L3 9 L10 9 Z"/></svg>),
    U: (<svg viewBox="0 0 24 24" width="60%" height="60%"><path fill="currentColor" d="M12 2 C12 2 4 10 4 15 C4 19.5 7.5 22 12 22 C16.5 22 20 19.5 20 15 C20 10 12 2 12 2 Z"/></svg>),
    B: (<svg viewBox="0 0 24 24" width="60%" height="60%"><circle cx="12" cy="12" r="9" fill="currentColor"/><circle cx="12" cy="12" r="9" fill="none" stroke="#000" strokeWidth="0.5"/></svg>),
    R: (<svg viewBox="0 0 24 24" width="65%" height="65%"><path fill="currentColor" d="M12 2 C9 8 5 9 5 14 C5 18.5 8.3 22 12 22 C15.7 22 19 18.5 19 14 C19 11 16 10 14 12 C14 9 13 5 12 2 Z"/></svg>),
    G: (<svg viewBox="0 0 24 24" width="65%" height="65%"><path fill="currentColor" d="M12 2 C7 5 5 10 6 14 C4 15 3 17 4 19 C6 21 9 20 10 18 C11 20 13 22 16 21 C19 20 21 16 20 12 C19 8 16 4 12 2 Z"/></svg>),
    C: (<svg viewBox="0 0 24 24" width="55%" height="55%"><path fill="currentColor" d="M12 3 L21 12 L12 21 L3 12 Z"/></svg>)
  };
  return (
    <span className={cls} style={{ background: m.bg, color: m.fg }}>
      {glyphs[c] || glyphs.C}
    </span>
  );
}

/* ---------- LOGO ---------- */
function Logo({ size = 32, showWordmark = true }) {
  // New PNG logo is full lockup (bulldog + ONPLAY GAMES wordmark). Show it standalone.
  const h = showWordmark ? size * 1.4 : size;
  return (
    <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
      <img src="assets/logo.png" alt="Onplay Games" style={{ height: h, width: "auto", objectFit: "contain", flexShrink: 0 }} />
      {showWordmark && (
        <div style={{ lineHeight: 0.85, borderLeft: "1px solid var(--line-2)", paddingLeft: 10 }}>
          <div style={{ fontFamily: "var(--body)", fontSize: 10, letterSpacing: "0.26em", color: "var(--mid-2)", textTransform: "uppercase" }}>
            Singles
          </div>
          <div style={{ fontFamily: "var(--display)", fontSize: size * 0.55, letterSpacing: "0.04em", color: "var(--bone)", marginTop: 3 }}>
            ONPLAY<span style={{ color: "var(--carmesi)" }}>.</span>CL
          </div>
        </div>
      )}
    </div>
  );
}

/* ---------- SET ICON (simple diamond monogram) ---------- */
function SetIcon({ code, size = 16 }) {
  const colors = {
    MH3: "#C84E3C", MH2: "#7B5EA8", MH1: "#D88A2B", 
    LTR: "#6A8F4A", NEO: "#E94B7B", LCI: "#2A9F8F",
    "2XM": "#D6A843", DMU: "#C06B3A", WAR: "#9A4FA8",
    THS: "#D4A84B", CLB: "#8B6F3A", CMM: "#B86B3A",
    JOU: "#6B8F4A", BBD: "#5E8FA8", "4ED": "#6B6B6B",
    REV: "#9B9B9B", BETA: "#4A4A4A", CLU: "#8A8A8A", M11: "#D4A84B", M10: "#D4A84B"
  };
  const c = colors[code] || "#6B6B6B";
  return (
    <svg width={size} height={size} viewBox="0 0 16 16" style={{ flexShrink: 0 }}>
      <path d="M8 1 L15 8 L8 15 L1 8 Z" fill={c} stroke="rgba(0,0,0,0.5)" strokeWidth="0.5"/>
      <text x="8" y="10.5" textAnchor="middle" fontSize="6" fontFamily="var(--body)" fontWeight="700" fill="white">
        {code.slice(0,3)}
      </text>
    </svg>
  );
}

/* ---------- PRICE ---------- */
function formatCLP(n) {
  return "$" + n.toLocaleString("es-CL");
}

/* ---------- PRODUCT CARD ---------- */
function ProductCard({ card, onOpen, onAdd, compact = false }) {
  const [added, setAdded] = useState(false);
  const handleAdd = (e) => {
    e.stopPropagation();
    setAdded(true);
    onAdd && onAdd(card);
    setTimeout(() => setAdded(false), 800);
  };
  const stockClass = card.stock <= 3 ? "badge-stock-low" : "badge-stock-ok";
  const stockLabel = card.stock <= 3 ? `Últimas ${card.stock}` : `${card.stock} disp.`;
  const rarityClass = card.rarity === "mythic" ? "badge-mythic" : card.rarity === "rare" ? "badge-rare" : card.rarity === "uncommon" ? "badge-uncommon" : "badge-common";

  return (
    <div className="card-product" onClick={() => onOpen && onOpen(card)} style={{ cursor: "pointer" }}>
      <div className="card-img-wrap">
        <img className="card-img" src={card.img} alt={card.name} loading="lazy"
          onError={(e) => { e.target.style.display = "none"; const p = e.target.parentElement; if (!p.querySelector('.img-fallback')) { const d = document.createElement('div'); d.className = 'img-fallback'; d.innerHTML = `<div style="position:absolute;inset:0;display:flex;flex-direction:column;justify-content:space-between;padding:14px;background:linear-gradient(160deg,#1A1A1A,#0A0A0A);"><div style="font-family:'Playfair Display',serif;font-weight:700;font-size:16px;color:#F5F5F5;line-height:1.1;">${card.name}</div><div style="font-family:'IBM Plex Mono',monospace;font-size:10px;color:#6B6B6B;letter-spacing:0.12em;text-transform:uppercase;">${card.set} · ${card.type||''}</div></div>`; p.appendChild(d); } }} />
        {/* rarity pip top-left */}
        <div style={{ position: "absolute", top: 8, left: 8, display: "flex", gap: 4 }}>
          <span className={`badge ${rarityClass}`} style={{ backdropFilter: "blur(8px)", background: "rgba(10,10,10,0.7)" }}>
            {card.rarity.slice(0,1).toUpperCase()}
          </span>
          {card.foil && <span className="badge badge-foil">FOIL</span>}
        </div>
        {/* colors bottom-right */}
        {card.colors && card.colors.length > 0 && (
          <div style={{ position: "absolute", bottom: 8, right: 8, display: "flex", gap: 2 }}>
            {card.colors.map((c, i) => <Mana key={i} c={c} size="sm" />)}
          </div>
        )}
      </div>
      <div className="card-meta">
        <div style={{ display: "flex", alignItems: "flex-start", justifyContent: "space-between", gap: 8 }}>
          <div style={{ minWidth: 0, flex: 1 }}>
            <div style={{ fontWeight: 600, fontSize: 13, color: "var(--bone)", overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>
              {card.name}
            </div>
            <div style={{ display: "flex", alignItems: "center", gap: 6, marginTop: 3, fontSize: 11, color: "var(--mid-2)" }}>
              <SetIcon code={card.set} size={12} />
              <span style={{ overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>{card.setName}</span>
            </div>
          </div>
        </div>
        <div style={{ display: "flex", alignItems: "center", gap: 6, fontSize: 10, color: "var(--mid-2)", letterSpacing: "0.06em" }}>
          <span className="mono" style={{ color: "var(--bone-2)" }}>{card.condition}</span>
          <span>·</span>
          <span className="mono">{card.lang}</span>
          <span>·</span>
          <span className={`badge ${stockClass}`} style={{ padding: "1px 5px", fontSize: 9 }}>{stockLabel}</span>
        </div>
        <div style={{ display: "flex", alignItems: "flex-end", justifyContent: "space-between", marginTop: 2 }}>
          <div className="d-md" style={{ color: "var(--carmesi)", fontFamily: "var(--display)", fontSize: 24 }}>
            {formatCLP(card.price)}
          </div>
          <button className={`btn btn-primary btn-sm ${added ? "added-pulse" : ""}`} onClick={handleAdd}>
            {added ? "✓" : "Agregar"}
          </button>
        </div>
      </div>
    </div>
  );
}

/* ---------- HEADER ---------- */
function Header({ route, setRoute, cartCount, onSearchFocus }) {
  const [q, setQ] = useState("");
  const [showSug, setShowSug] = useState(false);
  const suggestions = useMemo(() => {
    if (!q.trim()) return [];
    const ql = q.toLowerCase();
    return window.MTG_CARDS.filter(c => c.name.toLowerCase().includes(ql)).slice(0, 6);
  }, [q]);

  return (
    <header style={{ position: "sticky", top: 0, zIndex: 100, background: "rgba(10,10,10,0.92)", backdropFilter: "blur(12px)", borderBottom: "1px solid var(--line)" }}>
      {/* Top announce bar */}
      <div style={{ background: "var(--carbon-2)", borderBottom: "1px solid var(--line)", fontSize: 11, padding: "6px 0" }}>
        <div className="container" style={{ display: "flex", justifyContent: "space-between", color: "var(--mid-2)", letterSpacing: "0.04em" }}>
          <span>🏪 Retiro gratis en tienda · Merced 832, Galería Casa Colorada, Santiago</span>
          <div style={{ display: "flex", gap: 20 }}>
            <span>Despacho Chile vía Chilexpress</span>
            <span>Ayuda</span>
            <span style={{ color: "var(--bone-2)" }}>Mi cuenta</span>
          </div>
        </div>
      </div>

      <div className="container" style={{ display: "flex", alignItems: "center", gap: 24, padding: "14px 32px" }}>
        <div onClick={() => setRoute({ name: "home" })} style={{ cursor: "pointer" }}>
          <Logo size={40} />
        </div>

        {/* Search */}
        <div style={{ flex: 1, maxWidth: 640, position: "relative" }}>
          <div style={{ position: "relative" }}>
            <svg style={{ position: "absolute", left: 14, top: "50%", transform: "translateY(-50%)", color: "var(--mid)" }} width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input
              className="input"
              style={{ paddingLeft: 40, paddingRight: 80, height: 42, fontSize: 14 }}
              placeholder="Buscar cartas, sets, ediciones…  (ej: Lightning Bolt, MH3)"
              value={q}
              onChange={(e) => { setQ(e.target.value); setShowSug(true); }}
              onFocus={() => { setShowSug(true); onSearchFocus && onSearchFocus(); }}
              onBlur={() => setTimeout(() => setShowSug(false), 200)}
            />
            <div className="mono" style={{ position: "absolute", right: 12, top: "50%", transform: "translateY(-50%)", color: "var(--mid)", fontSize: 11, border: "1px solid var(--line-2)", padding: "2px 6px", borderRadius: 2 }}>⌘K</div>
          </div>
          {showSug && suggestions.length > 0 && (
            <div style={{ position: "absolute", top: "calc(100% + 6px)", left: 0, right: 0, background: "var(--carbon)", border: "1px solid var(--line-2)", borderRadius: 3, boxShadow: "0 12px 40px rgba(0,0,0,0.6)", overflow: "hidden" }}>
              {suggestions.map((s, i) => (
                <div key={i} onClick={() => { setRoute({ name: "pdp", card: s }); setShowSug(false); setQ(""); }}
                  style={{ display: "flex", alignItems: "center", gap: 12, padding: "8px 12px", cursor: "pointer", borderBottom: i < suggestions.length - 1 ? "1px solid var(--line)" : "none" }}
                  onMouseEnter={(e) => e.currentTarget.style.background = "rgba(255,255,255,0.03)"}
                  onMouseLeave={(e) => e.currentTarget.style.background = "transparent"}
                >
                  <img src={s.img} style={{ width: 28, height: 40, objectFit: "cover", borderRadius: 2 }} />
                  <div style={{ flex: 1 }}>
                    <div style={{ fontSize: 13, fontWeight: 500 }}>{s.name}</div>
                    <div style={{ fontSize: 11, color: "var(--mid-2)", display: "flex", alignItems: "center", gap: 6 }}>
                      <SetIcon code={s.set} size={10} /> {s.setName}
                    </div>
                  </div>
                  <div className="d-sm" style={{ color: "var(--carmesi)" }}>{formatCLP(s.price)}</div>
                </div>
              ))}
              <div style={{ padding: "8px 12px", fontSize: 11, color: "var(--mid-2)", background: "var(--carbon-2)", borderTop: "1px solid var(--line)" }}>
                Presiona Enter para ver todos los resultados →
              </div>
            </div>
          )}
        </div>

        {/* TCG Nav */}
        <nav style={{ display: "flex", alignItems: "center", gap: 4 }}>
          <NavItem active={route.name === "listing" || route.name === "pdp"} onClick={() => setRoute({ name: "listing" })}>Magic</NavItem>
          <NavItem active={route.name === "listing-op"} onClick={() => setRoute({ name: "listing-op" })}>One Piece</NavItem>
          <NavItem soon>Pokémon</NavItem>
          <NavItem active={route.name === "listing-rb"} onClick={() => setRoute({ name: "listing-rb" })}>Riftbound</NavItem>
        </nav>

        <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
          <button className="btn btn-ghost" title="Cuenta">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v2"/></svg>
          </button>
          <button className="btn btn-secondary" style={{ position: "relative", padding: "8px 14px" }}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            Carrito
            {cartCount > 0 && (
              <span style={{ background: "var(--carmesi)", color: "white", fontSize: 10, fontWeight: 700, padding: "1px 6px", borderRadius: 10, minWidth: 18 }}>{cartCount}</span>
            )}
          </button>
        </div>
      </div>
    </header>
  );
}

function NavItem({ children, active, soon, onClick }) {
  return (
    <div onClick={onClick} style={{
      position: "relative", padding: "8px 12px", fontSize: 13, fontWeight: 500,
      letterSpacing: "0.02em", cursor: soon ? "default" : "pointer",
      color: soon ? "var(--mid)" : active ? "var(--bone)" : "var(--bone-2)",
      borderBottom: active ? "2px solid var(--carmesi)" : "2px solid transparent",
      display: "flex", alignItems: "center", gap: 6
    }}>
      {children}
      {soon && <span className="badge badge-proximamente" style={{ fontSize: 8, padding: "1px 4px" }}>Pronto</span>}
    </div>
  );
}

/* ---------- FOOTER ---------- */
function Footer() {
  return (
    <footer style={{ borderTop: "1px solid var(--line)", marginTop: 80, background: "var(--carbon)" }}>
      <div className="container" style={{ padding: "56px 32px 32px" }}>
        <div style={{ display: "grid", gridTemplateColumns: "1.4fr 1fr 1fr 1fr 1fr", gap: 48, marginBottom: 48 }}>
          <div>
            <Logo size={42} />
            <p style={{ color: "var(--mid-2)", fontSize: 13, marginTop: 16, maxWidth: 280, lineHeight: 1.6 }}>
              La tienda especializada en singles de TCG en Chile. Operada por Onplay Games desde Santiago Centro.
            </p>
            <div style={{ display: "flex", gap: 8, marginTop: 20 }}>
              <SocialIcon label="IG" />
              <SocialIcon label="FB" />
              <SocialIcon label="X" />
              <SocialIcon label="YT" />
            </div>
          </div>
          <FooterCol title="Juegos" items={["Magic: The Gathering", "One Piece Card Game", "Pokémon TCG · Pronto", "Riftbound TCG"]} />
          <FooterCol title="Ayuda" items={["Condiciones de carta", "Guía de compra", "Despachos", "Devoluciones", "Preguntas frecuentes"]} />
          <FooterCol title="Empresa" items={["Sobre Onplay", "Tienda física", "Vende tus cartas", "Mayoristas", "Torneos"]} />
          <FooterCol title="Contacto" items={["Merced 832, Galería Casa Colorada", "Santiago Centro", "contacto@onplay.cl", "+56 9 XXXX XXXX", "Lun-Sáb 11:00 – 20:00"]} />
        </div>
        <div className="line-top" style={{ paddingTop: 20, display: "flex", justifyContent: "space-between", color: "var(--mid)", fontSize: 11, letterSpacing: "0.06em" }}>
          <span>© 2026 ONPLAY.CL · OPERADO POR ONPLAY GAMES SPA</span>
          <span>MAGIC: THE GATHERING ES MARCA REGISTRADA DE WIZARDS OF THE COAST</span>
        </div>
      </div>
    </footer>
  );
}
function FooterCol({ title, items }) {
  return (
    <div>
      <div className="d-sm" style={{ color: "var(--bone)", marginBottom: 16 }}>{title}</div>
      <ul style={{ listStyle: "none", padding: 0, margin: 0, display: "flex", flexDirection: "column", gap: 10 }}>
        {items.map((it, i) => <li key={i} style={{ fontSize: 13, color: "var(--mid-2)", cursor: "pointer" }}>{it}</li>)}
      </ul>
    </div>
  );
}
function SocialIcon({ label }) {
  return (
    <div style={{ width: 34, height: 34, border: "1px solid var(--line-2)", borderRadius: 2, display: "flex", alignItems: "center", justifyContent: "center", fontSize: 10, fontWeight: 700, letterSpacing: "0.06em", color: "var(--mid-2)", cursor: "pointer" }}>
      {label}
    </div>
  );
}

/* Export all shared */
Object.assign(window, { Mana, Logo, SetIcon, formatCLP, ProductCard, Header, Footer, NavItem });
