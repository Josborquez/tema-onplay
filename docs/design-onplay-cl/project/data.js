// Real MTG cards with realistic Chilean pricing (CLP)
// Images from Scryfall CDN
window.MTG_CARDS = [
  { name: "Lightning Bolt", set: "2XM", setName: "Double Masters", rarity: "uncommon", colors: ["R"], type: "Instant", cmc: 1, price: 3900, stock: 12, img: "https://api.scryfall.com/cards/named?exact=Lightning%20Bolt&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Counterspell", set: "MH2", setName: "Modern Horizons 2", rarity: "uncommon", colors: ["U"], type: "Instant", cmc: 2, price: 2500, stock: 24, img: "https://api.scryfall.com/cards/named?exact=Counterspell&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Sol Ring", set: "CLB", setName: "Commander Legends: Baldur's Gate", rarity: "uncommon", colors: [], type: "Artifact", cmc: 1, price: 1900, stock: 40, img: "https://api.scryfall.com/cards/named?exact=Sol%20Ring&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Thoughtseize", set: "THS", setName: "Theros", rarity: "rare", colors: ["B"], type: "Sorcery", cmc: 1, price: 14900, stock: 6, img: "https://api.scryfall.com/cards/named?exact=Thoughtseize&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Urza's Saga", set: "MH2", setName: "Modern Horizons 2", rarity: "rare", colors: [], type: "Enchantment — Saga", cmc: 0, price: 52900, stock: 3, img: "https://api.scryfall.com/cards/named?exact=Urza's%20Saga&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Ragavan, Nimble Pilferer", set: "MH2", setName: "Modern Horizons 2", rarity: "mythic", colors: ["R"], type: "Legendary Creature", cmc: 1, price: 68900, stock: 2, img: "https://api.scryfall.com/cards/named?exact=Ragavan%2C%20Nimble%20Pilferer&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Fable of the Mirror-Breaker", set: "NEO", setName: "Kamigawa: Neon Dynasty", rarity: "rare", colors: ["R"], type: "Enchantment — Saga", cmc: 3, price: 6900, stock: 10, img: "https://api.scryfall.com/cards/named?exact=Fable%20of%20the%20Mirror-Breaker&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Force of Negation", set: "MH1", setName: "Modern Horizons", rarity: "rare", colors: ["U"], type: "Instant", cmc: 3, price: 35900, stock: 4, img: "https://api.scryfall.com/cards/named?exact=Force%20of%20Negation&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Wrenn and Six", set: "MH1", setName: "Modern Horizons", rarity: "mythic", colors: ["R","G"], type: "Legendary Planeswalker", cmc: 2, price: 64900, stock: 2, img: "https://api.scryfall.com/cards/named?exact=Wrenn%20and%20Six&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Teferi, Time Raveler", set: "WAR", setName: "War of the Spark", rarity: "rare", colors: ["W","U"], type: "Legendary Planeswalker", cmc: 3, price: 12900, stock: 8, img: "https://api.scryfall.com/cards/named?exact=Teferi%2C%20Time%20Raveler&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Liliana of the Veil", set: "DMU", setName: "Dominaria United", rarity: "mythic", colors: ["B"], type: "Legendary Planeswalker", cmc: 3, price: 18900, stock: 5, img: "https://api.scryfall.com/cards/named?exact=Liliana%20of%20the%20Veil&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Swords to Plowshares", set: "2XM", setName: "Double Masters", rarity: "uncommon", colors: ["W"], type: "Instant", cmc: 1, price: 2200, stock: 30, img: "https://api.scryfall.com/cards/named?exact=Swords%20to%20Plowshares&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Brainstorm", set: "CMM", setName: "Commander Masters", rarity: "common", colors: ["U"], type: "Instant", cmc: 1, price: 1500, stock: 45, img: "https://api.scryfall.com/cards/named?exact=Brainstorm&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Path to Exile", set: "2XM", setName: "Double Masters", rarity: "uncommon", colors: ["W"], type: "Instant", cmc: 1, price: 2800, stock: 20, img: "https://api.scryfall.com/cards/named?exact=Path%20to%20Exile&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Solitude", set: "MH2", setName: "Modern Horizons 2", rarity: "mythic", colors: ["W"], type: "Creature — Elemental", cmc: 5, price: 42900, stock: 3, img: "https://api.scryfall.com/cards/named?exact=Solitude&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Orcish Bowmasters", set: "LTR", setName: "Lord of the Rings", rarity: "rare", colors: ["B"], type: "Creature — Orc Archer", cmc: 1, price: 29900, stock: 4, img: "https://api.scryfall.com/cards/named?exact=Orcish%20Bowmasters&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "The One Ring", set: "LTR", setName: "Lord of the Rings", rarity: "mythic", colors: [], type: "Legendary Artifact", cmc: 4, price: 48900, stock: 2, img: "https://api.scryfall.com/cards/named?exact=The%20One%20Ring&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Delighted Halfling", set: "LTR", setName: "Lord of the Rings", rarity: "rare", colors: ["G"], type: "Creature — Halfling", cmc: 1, price: 18900, stock: 6, img: "https://api.scryfall.com/cards/named?exact=Delighted%20Halfling&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Prismatic Ending", set: "MH2", setName: "Modern Horizons 2", rarity: "uncommon", colors: ["W"], type: "Sorcery", cmc: 1, price: 3500, stock: 14, img: "https://api.scryfall.com/cards/named?exact=Prismatic%20Ending&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Fatal Push", set: "2XM", setName: "Double Masters", rarity: "uncommon", colors: ["B"], type: "Instant", cmc: 1, price: 3200, stock: 18, img: "https://api.scryfall.com/cards/named?exact=Fatal%20Push&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Snapcaster Mage", set: "2XM", setName: "Double Masters", rarity: "rare", colors: ["U"], type: "Creature — Human Wizard", cmc: 2, price: 9900, stock: 7, img: "https://api.scryfall.com/cards/named?exact=Snapcaster%20Mage&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Bloodghast", set: "2XM", setName: "Double Masters", rarity: "rare", colors: ["B"], type: "Creature — Vampire Spirit", cmc: 2, price: 5900, stock: 9, img: "https://api.scryfall.com/cards/named?exact=Bloodghast&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Chalice of the Void", set: "2XM", setName: "Double Masters", rarity: "mythic", colors: [], type: "Artifact", cmc: 0, price: 38900, stock: 3, img: "https://api.scryfall.com/cards/named?exact=Chalice%20of%20the%20Void&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Cavern of Souls", set: "LCI", setName: "Lost Caverns of Ixalan", rarity: "mythic", colors: [], type: "Land", cmc: 0, price: 54900, stock: 2, img: "https://api.scryfall.com/cards/named?exact=Cavern%20of%20Souls&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Mana Confluence", set: "JOU", setName: "Journey into Nyx", rarity: "rare", colors: [], type: "Land", cmc: 0, price: 32900, stock: 4, img: "https://api.scryfall.com/cards/named?exact=Mana%20Confluence&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Scalding Tarn", set: "MH2", setName: "Modern Horizons 2", rarity: "rare", colors: [], type: "Land", cmc: 0, price: 28900, stock: 5, img: "https://api.scryfall.com/cards/named?exact=Scalding%20Tarn&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Misty Rainforest", set: "MH2", setName: "Modern Horizons 2", rarity: "rare", colors: [], type: "Land", cmc: 0, price: 27900, stock: 6, img: "https://api.scryfall.com/cards/named?exact=Misty%20Rainforest&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Monastery Swiftspear", set: "MH3", setName: "Modern Horizons 3", rarity: "common", colors: ["R"], type: "Creature — Human Monk", cmc: 1, price: 1900, stock: 22, img: "https://api.scryfall.com/cards/named?exact=Monastery%20Swiftspear&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Nethergoyf", set: "MH3", setName: "Modern Horizons 3", rarity: "mythic", colors: ["B"], type: "Creature — Lhurgoyf", cmc: 1, price: 34900, stock: 3, img: "https://api.scryfall.com/cards/named?exact=Nethergoyf&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Phlage, Titan of Fire's Fury", set: "MH3", setName: "Modern Horizons 3", rarity: "mythic", colors: ["R","W"], type: "Legendary Creature", cmc: 3, price: 36900, stock: 3, img: "https://api.scryfall.com/cards/named?exact=Phlage%2C%20Titan%20of%20Fire's%20Fury&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Flare of Denial", set: "MH3", setName: "Modern Horizons 3", rarity: "rare", colors: ["U"], type: "Instant", cmc: 3, price: 14900, stock: 5, img: "https://api.scryfall.com/cards/named?exact=Flare%20of%20Denial&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Grief", set: "MH2", setName: "Modern Horizons 2", rarity: "mythic", colors: ["B"], type: "Creature — Elemental Incarnation", cmc: 3, price: 39900, stock: 3, img: "https://api.scryfall.com/cards/named?exact=Grief&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Ephemerate", set: "MH1", setName: "Modern Horizons", rarity: "common", colors: ["W"], type: "Instant", cmc: 1, price: 890, stock: 35, img: "https://api.scryfall.com/cards/named?exact=Ephemerate&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Emrakul, the Aeons Torn", set: "2XM", setName: "Double Masters", rarity: "mythic", colors: [], type: "Legendary Creature", cmc: 15, price: 32900, stock: 2, img: "https://api.scryfall.com/cards/named?exact=Emrakul%2C%20the%20Aeons%20Torn&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Jace, the Mind Sculptor", set: "2XM", setName: "Double Masters", rarity: "mythic", colors: ["U"], type: "Legendary Planeswalker", cmc: 4, price: 44900, stock: 2, img: "https://api.scryfall.com/cards/named?exact=Jace%2C%20the%20Mind%20Sculptor&format=image&version=normal", condition: "NM", lang: "EN", foil: false },
  { name: "Tarmogoyf", set: "2XM", setName: "Double Masters", rarity: "mythic", colors: ["G"], type: "Creature — Lhurgoyf", cmc: 2, price: 8900, stock: 6, img: "https://api.scryfall.com/cards/named?exact=Tarmogoyf&format=image&version=normal", condition: "NM", lang: "EN", foil: false }
];

window.MTG_SETS = [
  { code: "MH3", name: "Modern Horizons 3", year: 2024, hero: "https://cards.scryfall.io/art_crop/front/5/5/55d20d7b-7c75-4789-b65c-56a4be4fd22a.jpg" },
  { code: "OTJ", name: "Outlaws of Thunder Junction", year: 2024 },
  { code: "MKM", name: "Murders at Karlov Manor", year: 2024 },
  { code: "LCI", name: "Lost Caverns of Ixalan", year: 2023 },
  { code: "LTR", name: "Lord of the Rings", year: 2023 },
  { code: "MH2", name: "Modern Horizons 2", year: 2021 },
  { code: "NEO", name: "Kamigawa: Neon Dynasty", year: 2022 },
  { code: "DMU", name: "Dominaria United", year: 2022 }
];

// Variant rows for PDP (Lightning Bolt example across sets/conditions)
window.VARIANTS = [
  { set: "2XM", setName: "Double Masters", condition: "NM", lang: "EN", foil: false, price: 3900, stock: 12 },
  { set: "2XM", setName: "Double Masters", condition: "LP", lang: "EN", foil: false, price: 3400, stock: 8 },
  { set: "2XM", setName: "Double Masters", condition: "NM", lang: "EN", foil: true, price: 18900, stock: 2 },
  { set: "M11", setName: "Magic 2011", condition: "NM", lang: "EN", foil: false, price: 4200, stock: 5 },
  { set: "M11", setName: "Magic 2011", condition: "LP", lang: "EN", foil: false, price: 3600, stock: 3 },
  { set: "M10", setName: "Magic 2010", condition: "NM", lang: "EN", foil: false, price: 4500, stock: 4 },
  { set: "M10", setName: "Magic 2010", condition: "SP", lang: "EN", foil: false, price: 3200, stock: 2 },
  { set: "BBD", setName: "Battlebond", condition: "NM", lang: "EN", foil: false, price: 3800, stock: 7 },
  { set: "4ED", setName: "Fourth Edition", condition: "MP", lang: "EN", foil: false, price: 2800, stock: 1 },
  { set: "REV", setName: "Revised", condition: "LP", lang: "EN", foil: false, price: 8900, stock: 2 },
  { set: "REV", setName: "Revised", condition: "MP", lang: "EN", foil: false, price: 6500, stock: 3 },
  { set: "BETA", setName: "Beta", condition: "HP", lang: "EN", foil: false, price: 189000, stock: 1 },
  { set: "CLU", setName: "Ravnica: Clue Edition", condition: "NM", lang: "ES", foil: false, price: 3500, stock: 4 },
  { set: "2XM", setName: "Double Masters", condition: "NM", lang: "JP", foil: true, price: 24900, stock: 1 }
];
