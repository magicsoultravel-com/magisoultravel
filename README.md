# magic soul travel

A static travel site built for GitHub Pages — Caribbean adventures, flight tracks, carnival mood, island hopping and travel planning.

## Structure

```
├── index.html          # home — journey cards (trips, gallery, videos, planner + blog posts), latest videos
├── post.html           # individual blog post page (post.html?id=<post-id>)
├── trips.html          # GPX flight tracks rendered on Leaflet.js
├── gallery.html        # photo gallery with lightbox
├── blog.html           # redirects to index.html (blog posts are now in the journey grid)
├── calendar.html       # holiday planner — colour-coded calendar (saves in your browser)
├── videos.html         # YouTube embeds from the channel
├── 404.html            # custom 404
├── assets/
│   ├── css/style.css
│   ├── js/
│   │   ├── layout.js     # shared header (hero text) + footer + flags banner
│   │   ├── main.js       # toasts, nav toggle, carousels, helpers
│   │   ├── gpx-maps.js   # Leaflet.js + GPX rendering
│   │   └── planner.js    # holiday planner calendar
│   ├── data/
│   │   ├── trips.json
│   │   ├── blog.json
│   │   ├── holiday-data.json
│   │   ├── videos.json
│   │   └── backgrounds.json
│   ├── gpx/              # GPX track files
│   └── img/
│       ├── favicon.png
│       ├── backgrounds/  # rotating site background images
│       ├── markers/      # map markers (PLAY/STOP icons for GPX tracks)
│       └── gallery/      # gallery photos
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
- **Background** — drop images into `assets/img/backgrounds/` and add entries to `assets/data/backgrounds.json`. Images rotate every 60 seconds with a smooth crossfade; left/right arrows let you navigate manually.
- **Gallery** — drop photos into `assets/img/gallery/` and add entries to the `images` array in `gallery.html`.
- **Calendar** — highlights are saved in your browser's localStorage.

## Notes

- The holiday planner saves data in your browser only (no server). Clearing browser data resets it.
- GPX maps use Leaflet.js with OpenStreetMap (map) and Esri WorldImagery (satellite) tiles — no API key required.
- The original PHP admin panel has been dropped — this is a static site.
