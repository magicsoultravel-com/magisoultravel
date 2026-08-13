/* magic soul travel — shared header/nav/footer injection */

const MST_LAYOUT = {
    header: `
<header class="site-header">
  <div class="header-inner">
    <div class="hero">
      <a href="index.html" class="logo-link">
        <h1>magic soul travel</h1>
      </a>
      <p class="tagline">see the colours, hear the sounds, feel the atmosphere</p>
      <p class="tagline-langs">魔法灵魂旅行 &nbsp;·&nbsp; رحلة سحرية للروح &nbsp;·&nbsp; μαγικό ταξίδι ψυχής &nbsp;·&nbsp; जादुई आत्मा यात्रा &nbsp;·&nbsp; 魂の魔法の旅</p>
    </div>
  </div>
</header>
<div class="flags-banner" id="flags-banner">
  <div class="flags-banner-content" id="flags-banner-content"></div>
</div>
`,

  footer: `
<footer class="site-footer">
  <p>© <span id="year"></span> magicsoultravel.com</p>
  <div class="hashtags">
    #MagicSoulTravel #Wanderlust #TravelMore #HiddenGems #AdventureAwaits #BucketListTravel
    #ExploreTheWorld #TravelVibes #NatureLover #Globetrotter #SaintLucia #Barbados #Martinique
    #CaribbeanTravels #CaribbeanAirlines #BritishAirways #LOTPolishAirlines #ExpressDesIles
    #FerryRide #CruiseShip #Concert #LiveBand #MilitaryOrchestra #RelaxingVideos #NatureSounds
    #AmbientVideos #MardiGras #CarnivalVibes #Sunset #Timelapse #ParadiseCity #ParadiseIsland
  </div>
</footer>
`
};

// Efficient non-blocking stylesheet + script loading
document.addEventListener('DOMContentLoaded', () => {
  // Inject header
  const headerSlot = document.getElementById('header-slot');
  if (headerSlot) {
    headerSlot.innerHTML = MST_LAYOUT.header;
  }

  // Inject footer
  const footerSlot = document.getElementById('footer-slot');
  if (footerSlot) {
    footerSlot.innerHTML = MST_LAYOUT.footer;
    const yearEl = document.getElementById('year');
    if (yearEl) yearEl.textContent = new Date().getFullYear();
  }

  // Init flags banner (only if present on page)
  initFlagsBanner();
});

// ----- Flags banner (scrolling country flags) -----
const MST_FLAGS = [
  '🇦🇫','🇦🇱','🇩🇿','🇦🇸','🇦🇩','🇦🇴','🇦🇮','🇦🇶','🇦🇬','🇦🇷','🇦🇲','🇦🇼','🇦🇺','🇦🇹','🇦🇿',
  '🇧🇸','🇧🇭','🇧🇩','🇧🇧','🇧🇾','🇧🇪','🇧🇿','🇧🇯','🇧🇲','🇧🇹','🇧🇴','🇧🇦','🇧🇼','🇧🇷','🇮🇴','🇻🇬','🇧🇳','🇧🇬','🇧🇫','🇧🇮','🇨🇻','🇰🇭','🇨🇲','🇨🇦','🇮🇨','🇰🇾','🇨🇫','🇹🇩','🇨🇱','🇨🇳','🇨🇽','🇨🇨','🇨🇴','🇰🇲','🇨🇬','🇨🇩','🇨🇰','🇨🇷','🇨🇮','🇭🇷','🇨🇺','🇨🇼','🇨🇾','🇨🇿','🇩🇰','🇩🇯','🇩🇲','🇩🇴','🇪🇨','🇪🇬','🇸🇻','🇬🇶','🇪🇷','🇪🇪','🇪🇹','🇫🇰','🇫🇴','🇫🇯','🇫🇮','🇫🇷','🇬🇫','🇵🇫','🇹🇫','🇬🇦','🇬🇲','🇬🇪','🇩🇪','🇬🇭','🇬🇮','🇬🇷','🇬🇱','🇬🇩','🇬🇵','🇬🇺','🇬🇹','🇬🇬','🇬🇳','🇬🇼','🇬🇾','🇭🇹','🇭🇳','🇭🇰','🇭🇺','🇮🇸','🇮🇳','🇮🇩','🇮🇷','🇮🇶','🇮🇪','🇮🇲','🇮🇱','🇮🇹','🇯🇲','🇯🇵','🇯🇪','🇯🇴','🇰🇿','🇰🇪','🇰🇮','🇽🇰','🇰🇼','🇰🇬','🇱🇦','🇱🇻','🇱🇧','🇱🇸','🇱🇷','🇱🇾','🇱🇮','🇱🇹','🇱🇺','🇲🇴','🇲🇰','🇲🇬','🇲🇼','🇲🇾','🇲🇻','🇲🇱','🇲🇹','🇲🇭','🇲🇶','🇲🇷','🇲🇺','🇾🇹','🇲🇽','🇲🇩','🇲🇨','🇲🇳','🇲🇪','🇲🇸','🇲🇦','🇲🇿','🇲🇲','🇳🇦','🇳🇷','🇳🇵','🇳🇱','🇳🇨','🇳🇿','🇳🇮','🇳🇪','🇳🇬','🇳🇺','🇳🇫','🇰🇵','🇲🇵','🇳🇴','🇴🇲','🇵🇰','🇵🇼','🇵🇸','🇵🇦','🇵🇬','🇵🇾','🇵🇪','🇵🇭','🇵🇳','🇵🇱','🇵🇹','🇵🇷','🇶🇦','🇷🇪','🇷🇴','🇷🇺','🇷🇼','🇼🇸','🇸🇲','🇸🇹','🇸🇦','🇸🇳','🇷🇸','🇸🇨','🇸🇱','🇸🇬','🇸🇽','🇸🇰','🇸🇮','🇸🇧','🇸🇴','🇿🇦','🇬🇸','🇰🇷','🇸🇸','🇪🇸','🇱🇰','🇸🇩','🇸🇷','🇸🇯','🇸🇿','🇸🇪','🇨🇭','🇸🇾','🇹🇼','🇹🇯','🇹🇿','🇹🇭','🇹🇱','🇹🇬','🇹🇰','🇹🇴','🇹🇹','🇹🇳','🇹🇷','🇹🇲','🇹🇨','🇹🇻','🇻🇮','🇺🇬','🇺🇦','🇦🇪','🇬🇧','🇺🇸','🇺🇾','🇺🇿','🇻🇺','🇻🇦','🇻🇪','🇻🇳','🇼🇫','🇪🇭','🇾🇪','🇿🇲','🇿🇼'
];

// MFA / foreign affairs URLs (kept short — uses generic links where official MFA is hard to find)
const MST_FLAG_URLS = {
  '🇺🇸': 'https://www.state.gov/',
  '🇬🇧': 'https://www.gov.uk/government/organisations/foreign-commonwealth-development-office',
  '🇫🇷': 'https://www.diplomatie.gouv.fr/en/',
  '🇩🇪': 'https://www.auswaertiges-amt.de/en/',
  '🇵🇱': 'https://www.gov.pl/web/diplomacy',
  '🇧🇧': 'https://www.foreign.gov.bb/',
  '🇱🇨': 'https://www.govt.lc/',
  '🇬🇵': 'https://www.guadeloupe.gouv.fr/',
  '🇲🇶': 'https://www.martinique.pref.gouv.fr/',
  '🇸🇽': 'https://www.sintmaartengov.org/',
  '🇦🇬': 'https://foreign.gov.ag/',
  '🇩🇲': 'https://www.dominica.gov.dm/',
  '🇬🇩': 'https://www.gov.gd/',
  '🇲🇸': 'https://www.gov.ms/',
  '🇰🇳': 'https://www.gov.kn/',
  '🇻🇨': 'https://www.gov.vc/',
  '🇧🇶': 'https://www.rijksoverheid.nl/',
  '🇨🇼': 'https://www.curacao-gov.cw/',
  '🇦🇼': 'https://www.arubagovernment.com/',
  '🇹🇹': 'https://www.foreign.gov.tt/',
  '🇨🇦': 'https://www.international.gc.ca/',
  '🇳🇱': 'https://www.government.nl/ministries/ministry-of-foreign-affairs',
  '🇯🇵': 'https://www.mofa.go.jp/index.html',
  '🇨🇳': 'https://www.fmprc.gov.cn/eng/',
  '🇮🇳': 'https://www.mea.gov.in/',
  '🇧🇷': 'https://www.gov.br/mre/pt-br/english',
  '🇲🇽': 'https://www.gob.mx/sre',
  '🇮🇹': 'https://www.esteri.it/en/',
  '🇪🇸': 'https://www.exteriores.gob.es/en/Paginas/index.aspx',
  '🇵🇹': 'https://www.portaldiplomatico.mne.gov.pt/en/',
  '🇸🇪': 'https://www.government.se/government-of-sweden/ministry-for-foreign-affairs/',
  '🇳🇴': 'https://www.regjeringen.no/en/dep/ud/id1111/',
  '🇩🇰': 'https://um.dk/en/',
  '🇫🇮': 'https://um.fi/frontpage',
  '🇺🇦': 'https://mfa.gov.ua/en',
  '🇮🇪': 'https://www.gov.ie/en/organisation/department-of-foreign-affairs/',
  '🇦🇹': 'https://www.bmeia.gv.at/en/',
  '🇨🇭': 'https://www.eda.admin.ch/eda/en/home.html',
  '🇧🇪': 'https://diplomatie.belgium.be/en',
  '🇬🇷': 'https://www.mfa.gr/en/',
  '🇦🇺': 'https://www.dfat.gov.au/',
  '🇳🇿': 'https://www.mfat.govt.nz/',
  '🇿🇦': 'https://www.dirco.gov.za/'
};

function initFlagsBanner() {
  const banner = document.getElementById('flags-banner');
  const content = document.getElementById('flags-banner-content');
  if (!banner || !content) return;

  // Build flag links
  const links = MST_FLAGS.map(flag => {
    const url = MST_FLAG_URLS[flag] || 'https://www.google.com/search?q=' + encodeURIComponent(flag + ' ministry of foreign affairs');
    return `<a href="${url}" target="_blank" rel="noopener noreferrer" title="Ministry of Foreign Affairs">${flag}</a>`;
  }).join(' ');

  // Repeat content for seamless loop
  content.innerHTML = links.repeat(5);

  let position = 0;
  const speed = 0.5;
  let animationId = null;
  let paused = false;

  const contentWidth = content.scrollWidth;
  const setWidth = contentWidth / 5; // one full set width

  function scroll() {
    position -= speed;
    if (position <= -setWidth) position = 0;
    content.style.transform = `translateX(${position}px)`;
    animationId = requestAnimationFrame(scroll);
  }

  banner.addEventListener('mouseenter', () => {
    if (animationId) {
      cancelAnimationFrame(animationId);
      animationId = null;
    }
  });

  banner.addEventListener('mouseleave', () => {
    if (!animationId) scroll();
  });

  if (contentWidth > banner.offsetWidth) scroll();
}