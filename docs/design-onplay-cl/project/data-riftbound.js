// Riftbound TCG sample data — Origins (OGN) set, base on League of Legends champions
// Domains: Body (red/strength), Mind (blue/intellect), Calm (green/serene), 
//          Chaos (purple/wild), Order (yellow/discipline), Fury (orange/rage)
// Card types: Legend, Champion, Unit, Spell, Gear, Battlefield, Rune

window.RB_CARDS = [
  // === LEGENDS (champion legend cards — like commanders) ===
  { id: "OGN-001", name: "Jinx, Get Excited!", setCode: "OGN", setName: "Origins", rarity: "Legendary", cardType: "Legend", domains: ["Chaos","Body"], cost: null, power: null, might: 6, champion: "Jinx", flavor: "Loose cannon, loud and proud.", condition: "NM", lang: "EN", foil: false, price: 24900, stock: 4 },
  { id: "OGN-002", name: "Lux, Light's Embrace", setCode: "OGN", setName: "Origins", rarity: "Legendary", cardType: "Legend", domains: ["Order","Mind"], might: 5, champion: "Lux", condition: "NM", lang: "EN", foil: false, price: 18900, stock: 5 },
  { id: "OGN-003", name: "Yasuo, the Unforgiven", setCode: "OGN", setName: "Origins", rarity: "Legendary", cardType: "Legend", domains: ["Fury","Calm"], might: 6, champion: "Yasuo", condition: "NM", lang: "EN", foil: false, price: 22900, stock: 3 },
  { id: "OGN-004", name: "Viktor, the Machine Herald", setCode: "OGN", setName: "Origins", rarity: "Legendary", cardType: "Legend", domains: ["Mind","Order"], might: 5, champion: "Viktor", condition: "NM", lang: "EN", foil: false, price: 19900, stock: 4 },
  { id: "OGN-005", name: "Garen, Demacian Might", setCode: "OGN", setName: "Origins", rarity: "Legendary", cardType: "Legend", domains: ["Body","Order"], might: 7, champion: "Garen", condition: "NM", lang: "EN", foil: false, price: 16900, stock: 6 },
  { id: "OGN-006", name: "Ahri, the Nine-Tailed", setCode: "OGN", setName: "Origins", rarity: "Legendary", cardType: "Legend", domains: ["Mind","Chaos"], might: 5, champion: "Ahri", condition: "NM", lang: "EN", foil: true, price: 38900, stock: 2 },
  // === CHAMPIONS ===
  { id: "OGN-024", name: "Jinx, Pow-Pow's Pretty", setCode: "OGN", setName: "Origins", rarity: "Epic", cardType: "Champion", domains: ["Chaos"], cost: 5, power: 5, might: 5, champion: "Jinx", condition: "NM", lang: "EN", foil: false, price: 8900, stock: 6 },
  { id: "OGN-025", name: "Jinx, Super Mega", setCode: "OGN", setName: "Origins", rarity: "Epic", cardType: "Champion", domains: ["Chaos","Body"], cost: 7, power: 7, might: 6, champion: "Jinx", condition: "NM", lang: "EN", foil: false, price: 11900, stock: 4 },
  { id: "OGN-031", name: "Lux, Final Spark", setCode: "OGN", setName: "Origins", rarity: "Epic", cardType: "Champion", domains: ["Order"], cost: 6, power: 4, might: 5, champion: "Lux", condition: "NM", lang: "EN", foil: false, price: 9900, stock: 5 },
  { id: "OGN-040", name: "Yasuo, Steel Tempest", setCode: "OGN", setName: "Origins", rarity: "Epic", cardType: "Champion", domains: ["Fury"], cost: 4, power: 5, might: 4, champion: "Yasuo", condition: "NM", lang: "EN", foil: false, price: 7900, stock: 7 },
  { id: "OGN-041", name: "Yasuo, Wandering Swordsman", setCode: "OGN", setName: "Origins", rarity: "Rare", cardType: "Champion", domains: ["Fury","Calm"], cost: 6, power: 6, might: 5, champion: "Yasuo", condition: "NM", lang: "EN", foil: false, price: 5900, stock: 9 },
  { id: "OGN-052", name: "Garen, the Might of Demacia", setCode: "OGN", setName: "Origins", rarity: "Epic", cardType: "Champion", domains: ["Body"], cost: 5, power: 6, might: 5, champion: "Garen", condition: "NM", lang: "EN", foil: false, price: 6900, stock: 8 },
  { id: "OGN-068", name: "Ahri, Charm", setCode: "OGN", setName: "Origins", rarity: "Rare", cardType: "Champion", domains: ["Mind"], cost: 4, power: 3, might: 4, champion: "Ahri", condition: "NM", lang: "EN", foil: false, price: 4900, stock: 11 },
  { id: "OGN-069", name: "Ahri, Spirit Rush", setCode: "OGN", setName: "Origins", rarity: "Epic", cardType: "Champion", domains: ["Mind","Chaos"], cost: 6, power: 5, might: 6, champion: "Ahri", condition: "NM", lang: "EN", foil: true, price: 28900, stock: 1 },
  { id: "OGN-074", name: "Viktor, Glorious Evolution", setCode: "OGN", setName: "Origins", rarity: "Epic", cardType: "Champion", domains: ["Mind"], cost: 6, power: 5, might: 5, champion: "Viktor", condition: "NM", lang: "EN", foil: false, price: 8900, stock: 5 },
  // === UNITS ===
  { id: "OGN-101", name: "Demacian Vanguard", setCode: "OGN", setName: "Origins", rarity: "Common", cardType: "Unit", domains: ["Order"], cost: 2, power: 2, might: 2, condition: "NM", lang: "EN", foil: false, price: 990, stock: 32 },
  { id: "OGN-102", name: "Piltovan Tinkerer", setCode: "OGN", setName: "Origins", rarity: "Common", cardType: "Unit", domains: ["Mind"], cost: 1, power: 1, might: 1, condition: "NM", lang: "EN", foil: false, price: 590, stock: 41 },
  { id: "OGN-103", name: "Zaunite Bruiser", setCode: "OGN", setName: "Origins", rarity: "Uncommon", cardType: "Unit", domains: ["Chaos","Body"], cost: 3, power: 3, might: 3, condition: "NM", lang: "EN", foil: false, price: 1490, stock: 24 },
  { id: "OGN-104", name: "Ionian Wanderer", setCode: "OGN", setName: "Origins", rarity: "Common", cardType: "Unit", domains: ["Calm"], cost: 2, power: 2, might: 1, condition: "NM", lang: "EN", foil: false, price: 690, stock: 36 },
  { id: "OGN-105", name: "Noxian Berserker", setCode: "OGN", setName: "Origins", rarity: "Uncommon", cardType: "Unit", domains: ["Fury"], cost: 3, power: 4, might: 2, condition: "NM", lang: "EN", foil: false, price: 1290, stock: 22 },
  { id: "OGN-106", name: "Targonian Aspect", setCode: "OGN", setName: "Origins", rarity: "Rare", cardType: "Unit", domains: ["Order","Mind"], cost: 5, power: 4, might: 4, condition: "NM", lang: "EN", foil: false, price: 3900, stock: 12 },
  { id: "OGN-107", name: "Voidborn Stalker", setCode: "OGN", setName: "Origins", rarity: "Rare", cardType: "Unit", domains: ["Chaos"], cost: 4, power: 4, might: 3, condition: "NM", lang: "EN", foil: false, price: 3490, stock: 14 },
  { id: "OGN-108", name: "Freljord Skirmisher", setCode: "OGN", setName: "Origins", rarity: "Common", cardType: "Unit", domains: ["Calm","Body"], cost: 2, power: 3, might: 2, condition: "NM", lang: "EN", foil: false, price: 890, stock: 28 },
  { id: "OGN-109", name: "Shuriman Sentinel", setCode: "OGN", setName: "Origins", rarity: "Uncommon", cardType: "Unit", domains: ["Order"], cost: 4, power: 3, might: 4, condition: "NM", lang: "EN", foil: false, price: 1690, stock: 18 },
  { id: "OGN-110", name: "Bandle City Yordle", setCode: "OGN", setName: "Origins", rarity: "Common", cardType: "Unit", domains: ["Mind"], cost: 1, power: 1, might: 2, condition: "NM", lang: "EN", foil: false, price: 590, stock: 38 },
  // === SPELLS ===
  { id: "OGN-150", name: "Lightning Bolt", setCode: "OGN", setName: "Origins", rarity: "Common", cardType: "Spell", domains: ["Fury"], cost: 1, power: null, might: null, condition: "NM", lang: "EN", foil: false, price: 690, stock: 52 },
  { id: "OGN-151", name: "Mind Spike", setCode: "OGN", setName: "Origins", rarity: "Uncommon", cardType: "Spell", domains: ["Mind"], cost: 2, condition: "NM", lang: "EN", foil: false, price: 1190, stock: 28 },
  { id: "OGN-152", name: "Demacian Justice", setCode: "OGN", setName: "Origins", rarity: "Rare", cardType: "Spell", domains: ["Order","Body"], cost: 4, condition: "NM", lang: "EN", foil: false, price: 4900, stock: 9 },
  { id: "OGN-153", name: "Whirlwind", setCode: "OGN", setName: "Origins", rarity: "Uncommon", cardType: "Spell", domains: ["Fury","Calm"], cost: 3, condition: "NM", lang: "EN", foil: false, price: 1690, stock: 19 },
  { id: "OGN-154", name: "Spirit Walk", setCode: "OGN", setName: "Origins", rarity: "Rare", cardType: "Spell", domains: ["Calm"], cost: 3, condition: "NM", lang: "EN", foil: false, price: 3290, stock: 14 },
  { id: "OGN-155", name: "Chemtech Eruption", setCode: "OGN", setName: "Origins", rarity: "Epic", cardType: "Spell", domains: ["Chaos","Mind"], cost: 5, condition: "NM", lang: "EN", foil: true, price: 12900, stock: 3 },
  { id: "OGN-156", name: "Charm", setCode: "OGN", setName: "Origins", rarity: "Common", cardType: "Spell", domains: ["Mind"], cost: 2, condition: "NM", lang: "EN", foil: false, price: 890, stock: 24 },
  // === GEAR ===
  { id: "OGN-200", name: "Hex-Tech Gauntlet", setCode: "OGN", setName: "Origins", rarity: "Rare", cardType: "Gear", domains: ["Mind","Order"], cost: 3, condition: "NM", lang: "EN", foil: false, price: 3490, stock: 14 },
  { id: "OGN-201", name: "Doran's Blade", setCode: "OGN", setName: "Origins", rarity: "Common", cardType: "Gear", domains: ["Body"], cost: 1, condition: "NM", lang: "EN", foil: false, price: 690, stock: 44 },
  { id: "OGN-202", name: "Infinity Edge", setCode: "OGN", setName: "Origins", rarity: "Epic", cardType: "Gear", domains: ["Fury","Body"], cost: 5, condition: "NM", lang: "EN", foil: false, price: 9900, stock: 5 },
  { id: "OGN-203", name: "Rabadon's Deathcap", setCode: "OGN", setName: "Origins", rarity: "Epic", cardType: "Gear", domains: ["Mind","Chaos"], cost: 5, condition: "NM", lang: "EN", foil: false, price: 9900, stock: 4 },
  // === BATTLEFIELDS ===
  { id: "OGN-250", name: "Summoner's Rift", setCode: "OGN", setName: "Origins", rarity: "Rare", cardType: "Battlefield", domains: ["Order","Body","Mind"], cost: null, condition: "NM", lang: "EN", foil: false, price: 4900, stock: 8 },
  { id: "OGN-251", name: "Howling Abyss", setCode: "OGN", setName: "Origins", rarity: "Rare", cardType: "Battlefield", domains: ["Calm"], condition: "NM", lang: "EN", foil: false, price: 3900, stock: 11 },
  { id: "OGN-252", name: "Ionian Temple", setCode: "OGN", setName: "Origins", rarity: "Uncommon", cardType: "Battlefield", domains: ["Calm","Order"], condition: "NM", lang: "EN", foil: false, price: 2290, stock: 16 },
  // === RUNES ===
  { id: "OGN-300", name: "Body Rune", setCode: "OGN", setName: "Origins", rarity: "Common", cardType: "Rune", domains: ["Body"], condition: "NM", lang: "EN", foil: false, price: 290, stock: 99 },
  { id: "OGN-301", name: "Mind Rune", setCode: "OGN", setName: "Origins", rarity: "Common", cardType: "Rune", domains: ["Mind"], condition: "NM", lang: "EN", foil: false, price: 290, stock: 99 },
  { id: "OGN-302", name: "Calm Rune", setCode: "OGN", setName: "Origins", rarity: "Common", cardType: "Rune", domains: ["Calm"], condition: "NM", lang: "EN", foil: false, price: 290, stock: 99 },
  { id: "OGN-303", name: "Chaos Rune", setCode: "OGN", setName: "Origins", rarity: "Common", cardType: "Rune", domains: ["Chaos"], condition: "NM", lang: "EN", foil: false, price: 290, stock: 99 },
  { id: "OGN-304", name: "Order Rune", setCode: "OGN", setName: "Origins", rarity: "Common", cardType: "Rune", domains: ["Order"], condition: "NM", lang: "EN", foil: false, price: 290, stock: 99 },
  { id: "OGN-305", name: "Fury Rune", setCode: "OGN", setName: "Origins", rarity: "Common", cardType: "Rune", domains: ["Fury"], condition: "NM", lang: "EN", foil: false, price: 290, stock: 99 },
  // === Proving Grounds set (next expansion sample) ===
  { id: "PRO-001", name: "Jhin, the Virtuoso", setCode: "PRO", setName: "Proving Grounds", rarity: "Legendary", cardType: "Legend", domains: ["Order","Chaos"], might: 5, champion: "Jhin", condition: "NM", lang: "EN", foil: false, price: 19900, stock: 3 },
  { id: "PRO-014", name: "Zed, Master of Shadows", setCode: "PRO", setName: "Proving Grounds", rarity: "Epic", cardType: "Champion", domains: ["Chaos","Fury"], cost: 5, power: 5, might: 5, champion: "Zed", condition: "NM", lang: "EN", foil: false, price: 9900, stock: 5 },
  { id: "PRO-022", name: "Thresh, Chain Warden", setCode: "PRO", setName: "Proving Grounds", rarity: "Epic", cardType: "Champion", domains: ["Chaos"], cost: 4, power: 3, might: 5, champion: "Thresh", condition: "NM", lang: "EN", foil: true, price: 24900, stock: 1 },
];

window.RB_SETS = [
  { code: "OGN", name: "Origins", year: 2025, type: "Core Set" },
  { code: "PRO", name: "Proving Grounds", year: 2026, type: "Expansion" },
];

window.RB_CHAMPIONS = ["Ahri","Garen","Jinx","Jhin","Lux","Thresh","Viktor","Yasuo","Zed"];
