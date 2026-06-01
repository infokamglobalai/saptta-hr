# React, TypeScript & shadcn/ui setup (this repo)

## Current state

`hr-services` is a **static HTML site**:

- Tailwind CSS via CDN (`cdn.tailwindcss.com` + `assets/js/tailwind-config.js`)
- Global styles in `assets/css/global.css`
- **No** `package.json`, **no** React, **no** TypeScript build

The animated **Globe** is already integrated on `contact.html` as a **vanilla HTML/CSS** port (see `assets/css/global.css` → `.contact-globe`).

The React source lives at `components/ui/globe.tsx` for when you migrate to a React stack.

---

## Why `/components/ui` matters

shadcn/ui installs primitives into **`components/ui`** by convention:

- Predictable imports: `@/components/ui/globe`
- CLI upgrades and docs assume this path
- Keeps page-level components separate from reusable UI

Create it even before running the CLI:

```text
components/
  ui/
    globe.tsx
    demo.tsx
```

---

## Option A — Add a React app beside the static site (recommended)

From the repo root:

```bash
npm create vite@latest web -- --template react-ts
cd web
npx shadcn@latest init
```

When prompted, set:

- **Components path:** `src/components` → then move or alias to `src/components/ui`
- **CSS:** Tailwind v4 or v3 per shadcn wizard
- **Alias `@`:** `src` (default in Vite)

Copy the globe component:

```bash
mkdir -p src/components/ui
cp ../components/ui/globe.tsx src/components/ui/
cp ../components/ui/demo.tsx src/components/ui/
```

Ensure `tsconfig.json`:

```json
{
  "compilerOptions": {
    "baseUrl": ".",
    "paths": {
      "@/*": ["./src/*"]
    }
  }
}
```

Install dependencies (Globe has **no** extra npm packages — only React):

```bash
npm install
```

Use in a route:

```tsx
import Globe from "@/components/ui/globe";

export function ContactGlobe() {
  return <Globe size={280} className="py-12" />;
}
```

---

## Option B — Next.js + shadcn (full migration)

```bash
npx create-next-app@latest kam-hr-web --typescript --tailwind --eslint --app --src-dir
cd kam-hr-web
npx shadcn@latest init
```

Copy `components/ui/globe.tsx` into `src/components/ui/`.

---

## Globe component notes

| Topic | Detail |
|--------|--------|
| **Props** | `size`, `textureUrl`, `className` (optional) |
| **State** | None — pure presentation |
| **Providers** | None required |
| **Assets** | Default texture: Unsplash earth (`photo-1614730321146`) |
| **Icons** | Not used (no `lucide-react` dependency) |
| **Responsive** | Scale via `size` prop or wrapper `max-width` |

### Best placement in this project

**Contact page → Global Presence** (`contact.html`): left column visual beside country flag cards — reinforces international workforce reach without the old AI map.

---

## Static site (no build) — already done

- HTML: `.contact-globe` in `contact.html`
- CSS: `@keyframes earthRotate`, `twinkling*` in `global.css`
- Texture: same Unsplash URL as React default (or host under `assets/img/globe-texture.jpg`)

To use a local asset, download a texture and set:

```css
.contact-globe__sphere {
  background-image: url("../img/globe-texture.jpg");
}
```

---

## External dependencies

**None** for `globe.tsx` beyond React itself. No `lucide-react`, no Radix, no shadcn base components required.
