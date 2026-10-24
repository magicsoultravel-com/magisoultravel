# AGENTS.md — magic soul travel (static site)

This file guides automated agents and contributors editing this repository.

## Project type
- **Static website** for GitHub Pages. No build step, no package manager, no backend.
- Plain HTML, CSS, and vanilla JavaScript only. Do not introduce frameworks, bundlers, or server-side code.
- Assets use relative paths so the site works when served from a subpath or opened locally.
- Local preview: `python3 -m http.server 8000` (or `npx serve .`), then open `http://localhost:8000`.

## Key pages
- `index.html` — home: journey cards, latest 6 videos (from CSV), "other videos" grid (from `videos.json`).
- `all-videos.html` — full catalog with filters (all/normal/shorts) and tile/list views.
- `trips.html`, `gallery.html`, `calendar.html`, `post.html`, `blog.html` (redirects to index).

## Shared code
- `assets/js/main.js` — toasts, nav, carousels, background slideshow, `initOtherVideos`.
- `assets/js/layout.js` — shared header/footer/flags banner.
- `assets/css/style.css` — global styles.
- Loader contract: pages call `window.MST_LOADER.register(...)` / `.done(...)` / `.setVideo(...)`; do not remove these hooks.

## Data sources (keep in sync)
### `assets/data/Magic-Soul-Travel.csv` (canonical video catalog)
- UTF-8 with BOM. Columns (do not rename):
  `Channel Title, Channel URL, Channel ID, Position, Video Title, Video URL, Video ID, Publish Date, Duration, Views, Description, Thumbnail URL, Availability Status`
- `Position` = 1-based, newest first (sort by `Publish Date` desc).
- `Duration` = `MM:SS` (parser supports `HH:MM:SS` too; shorts = duration <= 60s or title contains `#shorts`/`#short`).
- `Views` = integer (commas optional; `formatViews` strips them).
- `Thumbnail URL` = `https://i.ytimg.com/vi/<id>/hqdefault.jpg`.
- `Availability Status` = `available` for live videos.
- The in-browser CSV parser does **not** strip the BOM, but only the first header is affected; keep the BOM to match existing files.
- Descriptions may contain newlines/quotes/emoji; the CSV writer must quote them.

### `assets/data/videos.json`
- Array of `{ "id": "vN", "videoId": "...", "title": "..." }`, sorted newest first, matching CSV order.
- Used by `initOtherVideos()` which takes `slice(6)` (excludes latest 6) then shuffles.
- Must be regenerated whenever the CSV changes.

## Refreshing the video lists
Channel: `Magic Soul Travel` (`UC4zkEzqEk1yyAgoxctP3z8g`). Uploads playlist: `UU4zkEzqEk1yyAgoxctP3z8g`.

1. Fetch all items via YouTube Data API v3 `playlistItems` (paginated, `maxResults=50`).
2. Batch-fetch `videos` (`contentDetails.duration`, `statistics.viewCount`) 50 IDs at a time.
3. Drop `Private video` / `Deleted video` entries.
4. Sort by `publishedAt` desc; assign `Position` 1..N.
5. Regenerate both `Magic-Soul-Travel.csv` (with BOM, LF line endings) and `videos.json`.
6. Convert ISO-8601 durations (`PT#H#M#S`) to `MM:SS`.
7. Verify: record count, contiguous positions, unique IDs, JSON parse, and that the JS `parseCSV` returns the same count.

Legacy API key lives in `_legacy/` (already exposed client-side); reuse it for refresh scripts. Do not commit new secrets.

## Conventions
- Vanilla JS only; no new dependencies.
- Keep relative asset paths and existing class/ID names.
- Do not edit `_legacy/` (reference only).
- Prefer small, targeted edits; preserve the loader and carousel behavior.
- When adding videos, update **both** CSV and `videos.json`.

## Local verification checklist
- `python3 -m http.server 8000`
- Open `index.html`, `all-videos.html`; check console for errors.
- Confirm video counts match and thumbnails/durations render.
- Validate JSON: `python3 -c "import json; json.load(open('assets/data/videos.json'))"`.
