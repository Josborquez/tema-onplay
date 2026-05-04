// One Piece TCG sample data — based on EB-04 Adventure on Kami's Island & OP-15
// Image URLs use the official site's pattern (they may show as dummy.gif → fallback handles it)
window.OP_CARDS = [
  // EB-04 sample (Egghead arc)
  { id: "EB04-001", name: "Jewelry Bonney", setCode: "EB-04", setName: "Adventure on Kami's Island", rarity: "L", cardType: "Leader", colors: ["Red","Yellow"], cost: null, power: 5000, life: 4, counter: null, attribute: "Special", typeText: "Egghead/Bonney Pirates", block: 4, condition: "NM", lang: "EN", foil: false, price: 18900, stock: 4 },
  { id: "EB04-002", name: "Jewelry Bonney", setCode: "EB-04", setName: "Adventure on Kami's Island", rarity: "R", cardType: "Character", colors: ["Red"], cost: 1, power: 2000, counter: 1000, attribute: "Special", typeText: "Egghead/Bonney Pirates", block: 4, condition: "NM", lang: "EN", foil: false, price: 1900, stock: 12 },
  { id: "EB04-003", name: "Smoker & Tashigi", setCode: "EB-04", setName: "Adventure on Kami's Island", rarity: "R", cardType: "Character", colors: ["Red"], cost: 8, power: 8000, counter: null, attribute: "Slash/Special", typeText: "Punk Hazard/Navy", block: 4, condition: "NM", lang: "EN", foil: false, price: 4900, stock: 6 },
  { id: "EB04-007", name: "Roronoa Zoro", setCode: "EB-04", setName: "Adventure on Kami's Island", rarity: "SR", cardType: "Character", colors: ["Red"], cost: 7, power: 9000, counter: null, attribute: "Slash", typeText: "Egghead/Straw Hat Crew", block: 4, condition: "NM", lang: "EN", foil: false, price: 14900, stock: 4 },
  { id: "EB04-007", name: "Roronoa Zoro", setCode: "EB-04", setName: "Adventure on Kami's Island", rarity: "SR", cardType: "Character", colors: ["Red"], cost: 7, power: 9000, counter: null, attribute: "Slash", typeText: "Egghead/Straw Hat Crew", block: 4, condition: "NM", lang: "EN", foil: true, price: 38900, stock: 2 },
  { id: "EB04-044", name: "Koby", setCode: "EB-04", setName: "Adventure on Kami's Island", rarity: "SR", cardType: "Character", colors: ["Black"], cost: 6, power: 7000, counter: 1000, attribute: "Strike", typeText: "Navy/SWORD", block: 4, condition: "NM", lang: "EN", foil: false, price: 12900, stock: 5 },
  { id: "EB04-048", name: "Rob Lucci", setCode: "EB-04", setName: "Adventure on Kami's Island", rarity: "SR", cardType: "Character", colors: ["Black"], cost: 4, power: 6000, counter: null, attribute: "Strike", typeText: "Egghead/CP0", block: 4, condition: "NM", lang: "EN", foil: false, price: 9900, stock: 7 },
  { id: "EB04-058", name: "Borsalino", setCode: "EB-04", setName: "Adventure on Kami's Island", rarity: "SR", cardType: "Character", colors: ["Yellow"], cost: 5, power: 6000, counter: 1000, attribute: "Special", typeText: "Egghead/Navy", block: 4, condition: "NM", lang: "EN", foil: false, price: 11900, stock: 3 },
  { id: "EB04-061", name: "Monkey.D.Luffy", setCode: "EB-04", setName: "Adventure on Kami's Island", rarity: "SEC", cardType: "Character", colors: ["Yellow"], cost: 10, power: 12000, counter: null, attribute: "Strike", typeText: "Egghead/The Four Emperors/Straw Hat Crew", block: 4, condition: "NM", lang: "EN", foil: true, price: 89000, stock: 1 },
  // OP-15
  { id: "OP15-001", name: "Krieg", setCode: "OP-15", setName: "Adventure on Kami's Island", rarity: "L", cardType: "Leader", colors: ["Red","Green"], life: 4, power: 5000, counter: null, attribute: "Slash", typeText: "East Blue/Krieg Pirates", block: 4, condition: "NM", lang: "EN", foil: false, price: 8900, stock: 5 },
  { id: "OP15-002", name: "Lucy", setCode: "OP-15", setName: "Adventure on Kami's Island", rarity: "L", cardType: "Leader", colors: ["Red","Blue"], life: 4, power: 5000, counter: null, attribute: "Strike", typeText: "Dressrosa/Revolutionary Army", block: 4, condition: "NM", lang: "EN", foil: false, price: 12900, stock: 4 },
  { id: "OP15-008", name: "Krieg", setCode: "OP-15", setName: "Adventure on Kami's Island", rarity: "SR", cardType: "Character", colors: ["Red"], cost: 8, power: 9000, counter: null, attribute: "Slash", typeText: "East Blue/Krieg Pirates", block: 4, condition: "NM", lang: "EN", foil: false, price: 7900, stock: 6 },
  { id: "OP15-022", name: "Brook", setCode: "OP-15", setName: "Adventure on Kami's Island", rarity: "L", cardType: "Leader", colors: ["Green","Black"], life: 4, power: 5000, counter: null, attribute: "Slash", typeText: "Straw Hat Crew", block: 4, condition: "NM", lang: "EN", foil: false, price: 14900, stock: 3 },
  { id: "OP15-023", name: "Arlong", setCode: "OP-15", setName: "Adventure on Kami's Island", rarity: "R", cardType: "Character", colors: ["Green"], cost: 4, power: 5000, counter: 1000, attribute: "Slash", typeText: "Fish-Man/East Blue/Arlong Pirates", block: 4, condition: "NM", lang: "EN", foil: false, price: 3900, stock: 10 },
  { id: "OP15-027", name: "Dracule Mihawk", setCode: "OP-15", setName: "Adventure on Kami's Island", rarity: "C", cardType: "Character", colors: ["Green"], cost: 4, power: 5000, counter: 2000, attribute: "Slash", typeText: "East Blue/The Seven Warlords of the Sea", block: 4, condition: "NM", lang: "EN", foil: false, price: 2900, stock: 14 },
  { id: "OP15-032", name: "Brook", setCode: "OP-15", setName: "Adventure on Kami's Island", rarity: "SR", cardType: "Character", colors: ["Green"], cost: 6, power: 6000, counter: 1000, attribute: "Slash", typeText: "Straw Hat Crew", block: 4, condition: "NM", lang: "EN", foil: false, price: 8900, stock: 5 },
  { id: "OP15-039", name: "Rebecca", setCode: "OP-15", setName: "Adventure on Kami's Island", rarity: "L", cardType: "Leader", colors: ["Blue"], life: 5, power: 5000, counter: null, attribute: "Wisdom", typeText: "Dressrosa", block: 4, condition: "NM", lang: "EN", foil: false, price: 10900, stock: 4 },
  // Older sets - mock entries
  { id: "OP01-001", name: "Monkey.D.Luffy", setCode: "OP-01", setName: "Romance Dawn", rarity: "L", cardType: "Leader", colors: ["Red"], life: 5, power: 5000, attribute: "Strike", typeText: "Supernovas/Straw Hat Crew", block: 1, condition: "NM", lang: "EN", foil: false, price: 24900, stock: 3 },
  { id: "OP01-024", name: "Roronoa Zoro", setCode: "OP-01", setName: "Romance Dawn", rarity: "SR", cardType: "Character", colors: ["Green"], cost: 3, power: 5000, counter: 1000, attribute: "Slash", typeText: "Supernovas/Straw Hat Crew", block: 1, condition: "NM", lang: "EN", foil: false, price: 18900, stock: 4 },
  { id: "OP01-024", name: "Roronoa Zoro", setCode: "OP-01", setName: "Romance Dawn", rarity: "SR", cardType: "Character", colors: ["Green"], cost: 3, power: 5000, counter: 1000, attribute: "Slash", typeText: "Supernovas/Straw Hat Crew", block: 1, condition: "LP", lang: "EN", foil: false, price: 14900, stock: 2 },
  { id: "OP02-018", name: "Portgas.D.Ace", setCode: "OP-02", setName: "Paramount War", rarity: "SR", cardType: "Character", colors: ["Red"], cost: 4, power: 6000, counter: 1000, attribute: "Special", typeText: "Whitebeard Pirates", block: 1, condition: "NM", lang: "EN", foil: false, price: 22900, stock: 3 },
  { id: "OP04-080", name: "Charlotte Katakuri", setCode: "OP-04", setName: "Kingdoms of Intrigue", rarity: "SR", cardType: "Character", colors: ["Yellow"], cost: 7, power: 8000, counter: null, attribute: "Strike", typeText: "Big Mom Pirates", block: 2, condition: "NM", lang: "EN", foil: false, price: 16900, stock: 5 },
  { id: "OP05-119", name: "Yamato", setCode: "OP-05", setName: "Awakening of the New Era", rarity: "SR", cardType: "Character", colors: ["Yellow"], cost: 6, power: 7000, counter: null, attribute: "Slash", typeText: "Land of Wano", block: 2, condition: "NM", lang: "EN", foil: false, price: 11900, stock: 4 },
  { id: "OP06-118", name: "Eustass\"Captain\"Kid", setCode: "OP-06", setName: "Wings of the Captain", rarity: "L", cardType: "Leader", colors: ["Red","Yellow"], life: 4, power: 5000, attribute: "Strike", typeText: "Supernovas/Kid Pirates", block: 2, condition: "NM", lang: "EN", foil: false, price: 9900, stock: 6 },
  { id: "OP07-001", name: "Trafalgar Law", setCode: "OP-07", setName: "500 Years in the Future", rarity: "L", cardType: "Leader", colors: ["Black"], life: 4, power: 5000, attribute: "Slash", typeText: "Supernovas/Heart Pirates", block: 3, condition: "NM", lang: "EN", foil: false, price: 13900, stock: 4 },
  { id: "OP08-118", name: "Edward.Newgate", setCode: "OP-08", setName: "Two Legends", rarity: "SEC", cardType: "Character", colors: ["Red"], cost: 10, power: 13000, counter: null, attribute: "Strike", typeText: "The Four Emperors/Whitebeard Pirates", block: 3, condition: "NM", lang: "EN", foil: true, price: 64900, stock: 1 },
  { id: "OP09-040", name: "Marshall.D.Teach", setCode: "OP-09", setName: "Emperors in the New World", rarity: "L", cardType: "Leader", colors: ["Black"], life: 5, power: 5000, attribute: "Strike", typeText: "The Four Emperors/Blackbeard Pirates", block: 3, condition: "NM", lang: "EN", foil: false, price: 8900, stock: 5 },
  { id: "OP10-001", name: "Vivi", setCode: "OP-10", setName: "Royal Blood", rarity: "L", cardType: "Leader", colors: ["Red"], life: 4, power: 5000, attribute: "Wisdom", typeText: "Alabasta", block: 3, condition: "NM", lang: "EN", foil: false, price: 7900, stock: 7 },
  // Events & Stages
  { id: "OP15-019", name: "Barrier Bulls", setCode: "OP-15", setName: "Adventure on Kami's Island", rarity: "UC", cardType: "Event", colors: ["Red"], cost: 3, attribute: null, typeText: "Dressrosa/Barto Club", block: 4, condition: "NM", lang: "EN", foil: false, price: 1500, stock: 12 },
  { id: "OP15-020", name: "Fire Fist", setCode: "OP-15", setName: "Adventure on Kami's Island", rarity: "R", cardType: "Event", colors: ["Red"], cost: 7, attribute: null, typeText: "Dressrosa/Revolutionary Army", block: 4, condition: "NM", lang: "EN", foil: false, price: 2900, stock: 8 },
  { id: "EB04-010", name: "Lulucia Kingdom", setCode: "EB-04", setName: "Adventure on Kami's Island", rarity: "C", cardType: "Stage", colors: ["Red"], cost: 7, attribute: null, typeText: "Lulucia Kingdom", block: 4, condition: "NM", lang: "EN", foil: false, price: 990, stock: 22 },
  { id: "EB04-049", name: "Finger Pistol Yellow Lotus", setCode: "EB-04", setName: "Adventure on Kami's Island", rarity: "C", cardType: "Event", colors: ["Black"], cost: 4, attribute: null, typeText: "CP9", block: 4, condition: "NM", lang: "EN", foil: false, price: 1500, stock: 9 },
  // Purple
  { id: "OP06-069", name: "Doflamingo", setCode: "OP-06", setName: "Wings of the Captain", rarity: "L", cardType: "Leader", colors: ["Purple"], life: 5, power: 5000, attribute: "Special", typeText: "Donquixote Pirates", block: 2, condition: "NM", lang: "EN", foil: false, price: 11900, stock: 4 },
  { id: "OP05-076", name: "Magellan", setCode: "OP-05", setName: "Awakening of the New Era", rarity: "SR", cardType: "Character", colors: ["Purple"], cost: 7, power: 8000, counter: null, attribute: "Special", typeText: "Impel Down", block: 2, condition: "NM", lang: "EN", foil: false, price: 6900, stock: 6 },
  // Multicolor
  { id: "OP08-118x", name: "Shanks", setCode: "OP-08", setName: "Two Legends", rarity: "L", cardType: "Leader", colors: ["Multicolor"], life: 4, power: 5000, attribute: "Slash", typeText: "The Four Emperors/Red-Haired Pirates", block: 3, condition: "NM", lang: "EN", foil: false, price: 14900, stock: 3 },
];

window.OP_SETS = [
  { code: "EB-04", name: "Adventure on Kami's Island", year: 2025, type: "Extra Booster" },
  { code: "OP-15", name: "Adventure on Kami's Island", year: 2025, type: "Booster" },
  { code: "OP-14", name: "The Azure Sea's Seven", year: 2025, type: "Booster" },
  { code: "OP-13", name: "Carrying On His Will", year: 2024, type: "Booster" },
  { code: "OP-12", name: "Legacy of the Master", year: 2024, type: "Booster" },
  { code: "OP-11", name: "A Fist of Divine Speed", year: 2024, type: "Booster" },
  { code: "OP-10", name: "Royal Blood", year: 2024, type: "Booster" },
  { code: "OP-09", name: "Emperors in the New World", year: 2024, type: "Booster" },
  { code: "OP-08", name: "Two Legends", year: 2024, type: "Booster" },
  { code: "OP-07", name: "500 Years in the Future", year: 2023, type: "Booster" },
  { code: "OP-06", name: "Wings of the Captain", year: 2023, type: "Booster" },
  { code: "OP-05", name: "Awakening of the New Era", year: 2023, type: "Booster" },
  { code: "OP-04", name: "Kingdoms of Intrigue", year: 2023, type: "Booster" },
  { code: "OP-03", name: "Pillars of Strength", year: 2023, type: "Booster" },
  { code: "OP-02", name: "Paramount War", year: 2022, type: "Booster" },
  { code: "OP-01", name: "Romance Dawn", year: 2022, type: "Booster" },
  { code: "ST-01", name: "Straw Hat Crew", year: 2022, type: "Starter Deck" },
  { code: "ST-21", name: "GEAR5", year: 2024, type: "Starter Deck EX" },
];
