# Demo Images Guide — Pastimes

This guide helps you add real product images to bring the Pastimes demo to life.

---

## Where to Place Images

Put all images in the **`images/`** folder (create it if it doesn't exist):

```
Pastimes/
├── images/
│   ├── item-01-classic-white-shirt.jpg
│   ├── item-02-high-waist-skinny-jeans.jpg
│   └── ... (30 total)
├── dashboard.php
├── viewItem.php
└── ...
```

---

## Naming Scheme

Use this exact pattern so the code can find them automatically:

```
item-{id}-{kebab-case-title}.{ext}
```

- **id**: The clothesID from the database (01–30)
- **kebab-case-title**: Lowercase, words separated by hyphens, no special characters
- **ext**: `.jpg`, `.jpeg`, or `.png`

---

## Required Images (30 items)

| ID | Filename | Description |
|----|----------|-------------|
| 01 | `item-01-classic-white-shirt.jpg` | Ralph Lauren — Classic White Shirt |
| 02 | `item-02-high-waist-skinny-jeans.jpg` | Levi's — High-Waist Skinny Jeans |
| 03 | `item-03-camel-wool-winter-coat.jpg` | Zara — Camel Wool Winter Coat |
| 04 | `item-04-floral-midi-sundress.jpg` | H&M — Floral Midi Sundress |
| 05 | `item-05-genuine-leather-belt.jpg` | Tommy Hilfiger — Genuine Leather Belt |
| 06 | `item-06-navy-blazer.jpg` | Woolworths — Navy Blazer |
| 07 | `item-07-slim-chino-trousers.jpg` | Banana Republic — Slim Chino Trousers |
| 08 | `item-08-striped-boatneck-top.jpg` | Gap — Striped Boatneck Top |
| 09 | `item-09-black-leather-ankle-boots.jpg` | Steve Madden — Black Leather Ankle Boots |
| 10 | `item-10-wrap-maxi-skirt.jpg` | ASOS — Wrap Maxi Skirt |
| 11 | `item-11-oversized-denim-jacket.jpg` | Levi's — Oversized Denim Jacket |
| 12 | `item-12-silk-blouse.jpg` | Reiss — Silk Blouse |
| 13 | `item-13-pleated-midi-skirt.jpg` | Massimo Dutti — Pleated Midi Skirt |
| 14 | `item-14-merino-wool-sweater.jpg` | Uniqlo — Merino Wool Sweater |
| 15 | `item-15-canvas-sneakers.jpg` | Converse — Canvas Sneakers |
| 16 | `item-16-tailored-suit-trousers.jpg` | Hugo Boss — Tailored Suit Trousers |
| 17 | `item-17-puffer-vest.jpg` | The North Face — Puffer Vest |
| 18 | `item-18-flare-leg-trousers.jpg` | Mango — Flare Leg Trousers |
| 19 | `item-19-lightweight-trench-coat.jpg` | Marks & Spencer — Lightweight Trench Coat |
| 20 | `item-20-corset-top.jpg` | Pretty Little Thing — Corset Top |
| 21 | `item-21-wide-brim-hat.jpg` | Cotton On — Wide Brim Hat |
| 22 | `item-22-maxi-wrap-dress.jpg` | Faithfull — Maxi Wrap Dress |
| 23 | `item-23-chelsea-boots.jpg` | Aldo — Chelsea Boots |
| 24 | `item-24-cashmere-scarf.jpg` | Scottish Cashmere — Cashmere Scarf |
| 25 | `item-25-gym-leggings.jpg` | Nike — Gym Leggings |
| 26 | `item-26-running-jacket.jpg` | Adidas — Running Jacket |
| 27 | `item-27-linen-shirt.jpg` | Country Road — Linen Shirt |
| 28 | `item-28-cargo-trousers.jpg` | G-Star Raw — Cargo Trousers |
| 29 | `item-29-graphic-band-tee.jpg` | H&M — Graphic Band Tee |
| 30 | `item-30-structured-handbag.jpg` | Forever New — Structured Handbag |

---

## Recommended Image Specs

| Property | Recommendation |
|----------|----------------|
| **Format** | JPG (photos) or PNG (transparency needed) |
| **Size** | 800×600 px minimum (4:3 ratio works best) |
| **Quality** | 80–90% JPG compression for web |
| **Style** | Clean product shots on neutral or white background |
| **File size** | Under 200KB each for fast loading |

---

## Where to Source Images

### Option 1: Placeholder Services (Quick Demo)
- `https://placehold.co/800x600` — already used as fallback
- `https://picsum.photos/800/600` — random photos

### Option 2: Real Product Photos
- Your own closet photos
- Unsplash.com (free, search "clothing flatlay", "fashion")
- Pexels.com (free stock photos)
- Brand websites (for demo/educational use)

### Option 3: AI Generated
- DALL-E, Midjourney, or Stable Diffusion
- Prompt: *"Product photography of a white Ralph Lauren Oxford shirt on neutral background, professional e-commerce style"*

---

## Quick Setup Steps

1. **Create the folder** if it doesn't exist:
   ```
   Pastimes/images/
   ```

2. **Download/save images** using the filenames above

3. **The code automatically detects them** — no PHP changes needed if you follow the naming scheme

---

## How It Works in Code

The app looks for a local image first:
- If `images/item-{id}-*.jpg` (or .png) exists → use it
- If not found → fall back to `https://placehold.co` with brand name

This means you can add images gradually — the site works fine with partial coverage.
