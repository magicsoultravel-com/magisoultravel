# magic soul travel

A static travel site built for GitHub Pages — Caribbean adventures, flight tracks, carnival mood, island hopping and travel planning.

## Structure

```
├── index.html          # home — hero, quick links, latest videos, planner shortcut
├── trips.html          # GPX flight tracks rendered on Google Maps
├── gallery.html        # photo gallery with lightbox
├── blog.html           # travel research posts (airlines, ferries, hurricane seasons)
├── calendar.html       # holiday planner — colour-coded calendar (saves in your browser)
├── videos.html         # YouTube embeds from the channel
├── 404.html            # custom 404
├── assets/
│   ├── css/style.css
│   ├── js/
│   │   ├── layout.js     # shared header/nav/footer + flags banner
│   │   ├── main.js       # toasts, nav toggle, carousels, helpers
│   │   ├── gpx-maps.js   # Google Maps + GPX rendering
│   │   └── planner.js    # holiday planner calendar
│   ├── data/
│   │   ├── trips.json
│   │   ├── blog.json
│   │   ├── holiday-data.json
│   │   └── videos.json
│   ├── gpx/              # GPX track files
│   └── img/              # favicon + gallery photos
└── _legacy/              # original PHP dump (kept for reference)
```

## Local preview

```bash
npx serve .
# or
python3 -m http.server 8000
```

## Updating content

- **Videos** — edit `assets/data/videos.json` with your latest YouTube video IDs.
- **Trips** — add a `.gpx` file to `assets/gpx/` and an entry to `assets/data/trips.json`.
- **Blog** — edit `assets/data/blog.json` (array of `{ id, date, title, content }`).
- **Gallery** — drop photos into `assets/img/gallery/` and add entries to the `images` array in `gallery.html`.
- **Calendar** — highlights are saved in your browser's localStorage.

## Notes

- The holiday planner saves data in your browser only (no server). Clearing browser data resets it.
- GPX maps use the Google Maps JavaScript API.
- The original PHP admin panel has been dropped — this is a static site.
