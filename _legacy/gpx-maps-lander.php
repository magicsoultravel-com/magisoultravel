<?php
// This PHP file generates an auto-scrolling banner of country flags,
// with each flag optionally linked to its respective Ministry of Foreign Affairs (MFA) website.
// All HTML structure, PHP logic, and JavaScript are contained within this file.

// --- 1. Flag Data and MFA URLs (PHP Arrays) ---
// Define flags using actual Unicode characters.
// IMPORTANT: The MFA URLs below are for demonstration purposes.
// You MUST meticulously verify and maintain these URLs for accuracy and freshness.
// Government websites change frequently!
$flags_and_mfa_urls = [
    '🇦🇫' => 'https://www.mfa.gov.af/', // Afghanistan (Verify)
    '🇦🇱' => 'https://www.punetejashtme.gov.al/', // Albania
    '🇩🇿' => 'https://www.mfa.gov.dz/en/', // Algeria
    '🇦🇸' => 'https://www.doi.gov/ot/american-samoa', // American Samoa (Often handled by US Dept. of Interior/State)
    '🇦🇩' => 'https://www.exteriors.ad/', // Andorra
    '🇦🇴' => 'https://www.mirex.gov.ao/', // Angola
    '🇦🇮' => 'https://www.gov.ai/ministries/foreign-affairs.html', // Anguilla (Often handled by UK FCDO)
    '🇦🇶' => 'https://www.ats.aq/e/atscomm.htm', // Antarctica (Link to Antarctic Treaty System)
    '🇦🇬' => 'https://foreign.gov.ag/', // Antigua & Barbuda
    '🇦🇷' => 'https://www.cancilleria.gob.ar/en', // Argentina
    '🇦🇲' => 'https://www.mfa.am/en/', // Armenia
    '🇦🇼' => 'https://www.arubagovernment.com/government/', // Aruba (Often linked to general government portal or NL MFA)
    '🇦🇺' => 'https://www.dfat.gov.au/', // Australia
    '🇦🇹' => 'https://www.bmeia.gv.at/en/', // Austria
    '🇦🇿' => 'https://mfa.gov.az/en', // Azerbaijan
    '🇧🇸' => 'https://www.bahamas.gov.bs/foreignaffairs', // Bahamas
    '🇧🇭' => 'https://www.mofa.gov.bh/main/en/', // Bahrain
    '🇧🇩' => 'https://mofa.gov.bd/', // Bangladesh
    '🇧🇧' => 'https://foreign.gov.bb/', // Barbados
    '🇧🇾' => 'https://mfa.gov.by/en/', // Belarus
    '🇧🇪' => 'https://diplomatie.belgium.be/en', // Belgium
    '🇧🇿' => 'https://foreign.gov.bz/', // Belize
    '🇧🇯' => 'https://www.gouv.bj/communique/ministere-affaires-etrangeres/', // Benin (Might be general govt page)
    '🇧🇲' => 'https://www.gov.bm/department/ministry-home-affairs', // Bermuda (Often handled by UK FCDO or local affairs)
    '🇧🇹' => 'https://www.mfa.gov.bt/', // Bhutan
    '🇧🇴' => 'https://www.rree.gob.bo/', // Bolivia
    '🇧🇦' => 'https://www.mvp.gov.ba/?lang=en', // Bosnia & Herzegovina
    '🇧🇼' => 'https://www.gov.bw/ministries/international-affairs-cooperation', // Botswana
    '🇧🇷' => 'https://www.gov.br/mre/pt-br/english', // Brazil
    '🇮🇴' => 'https://www.gov.uk/government/organisations/foreign-commonwealth-development-office', // British Indian Ocean Territory (UK FCDO)
    '🇻🇬' => 'https://www.bvi.gov.vg/ministries/deputy-governors-office', // British Virgin Islands (UK FCDO or local DGO)
    '🇧🇳' => 'https://www.mfa.gov.bn/', // Brunei
    '🇧🇬' => 'https://www.mfa.bg/en/', // Bulgaria
    '🇧🇫' => 'https://www.diplomatie.gov.bf/', // Burkina Faso
    '🇧🇮' => 'https://www.mfa.gov.bi/', // Burundi
    '🇨🇻' => 'https://mnec.gov.cv/', // Cabo Verde
    '🇰🇭' => 'https://www.mfaic.gov.kh/', // Cambodia
    '🇨🇲' => 'https://www.diplocam.cm/', // Cameroon
    '🇨🇦' => 'https://www.international.gc.ca/gac-amc/index.aspx?lang=eng', // Canada
    '🇮🇨' => 'https://www.gobiernodecanarias.org/administracion/acciones/europa-exterior/', // Canary Islands (Spain MFA)
    '🇰🇾' => 'https://www.gov.ky/mfa/foreign-affairs', // Cayman Islands (UK FCDO or local MFA)
    '🇨🇫' => 'https://www.presidence.cf/', // Central African Republic (Often link to presidency site if MFA is hard to find)
    '🇹🇩' => 'https://www.ambassade-du-tchad.com/', // Chad (Often embassy if MFA is elusive)
    '🇨🇱' => 'https://chile.gob.cl/chile/ministerios/ministerio-de-relaciones-exteriores', // Chile
    '🇨🇳' => 'https://www.fmprc.gov.cn/eng/', // China
    '🇨🇽' => 'https://www.infrastructure.gov.au/territories-regions-cities/territories/indian-ocean-territories', // Christmas Island (Australia DOTIR)
    '🇨🇨' => 'https://www.infrastructure.gov.au/territories-regions-cities/territories/indian-ocean-territories', // Cocos (Keeling) Islands (Australia DOTIR)
    '🇨🇴' => 'https://www.cancilleria.gov.co/en', // Colombia
    '🇰🇲' => 'https://www.mofa.gov.km/', // Comoros
    '🇨🇬' => 'https://www.ambacongo-us.org/ministere-des-affaires-etrangeres', // Congo - Brazzaville (Often embassy)
    '🇨🇩' => 'https://www.minaffet.gouv.cd/', // Congo - Kinshasa
    '🇨🇰' => 'https://www.mfem.gov.ck/', // Cook Islands (Often Ministry of Finance or similar)
    '🇨🇷' => 'https://www.rree.go.cr/', // Costa Rica
    '🇨🇮' => 'https://diplomatie.gouv.ci/', // Côte d’Ivoire
    '🇭🇷' => 'https://mvep.gov.hr/eng', // Croatia
    '🇨🇺' => 'https://cubaminrex.cu/en', // Cuba
    '🇨🇼' => 'https://www.curacao-gov.cw/government/ministries/ministry-of-foreign-affairs/', // Curaçao
    '🇨🇾' => 'https://www.mfa.gov.cy/mfa/mfa.nsf/index_en/index_en', // Cyprus
    '🇨🇿' => 'https://www.mzv.cz/jnp/en/index.html', // Czechia
    '🇩🇰' => 'https://um.dk/en/', // Denmark
    '🇩🇯' => 'https://www.diplomatie.gouv.dj/index.php/ministere', // Djibouti
    '🇩🇲' => 'https://www.dominica.gov.dm/ministries-and-departments/ministry-of-foreign-affairs/', // Dominica
    '🇩🇴' => 'https://mirex.gob.do/', // Dominican Republic
    '🇪🇨' => 'https://www.cancilleria.gob.ec/', // Ecuador
    '🇪🇬' => 'https://www.mfa.gov.eg/English/Pages/default.aspx', // Egypt
    '🇸🇻' => 'https://www.rree.gob.sv/', // El Salvador
    '🇬🇶' => 'https://www.guineaecuatorial.gob.gq/ministerio-de-asuntos-exteriores-y-cooperacion/', // Equatorial Guinea
    '🇪🇷' => 'https://www.shabait.com/about-eritrea/ministries/', // Eritrea (Often linked from general info sites)
    '🇪🇪' => 'https://vm.ee/en', // Estonia
    '🇪🇹' => 'https://www.mfa.gov.et/', // Ethiopia
    '🇫🇰' => 'https://www.falklands.gov.fk/government/executive', // Falkland Islands (UK FCDO or local government)
    '🇫🇴' => 'https://www.government.fo/foreign-relations/', // Faroe Islands
    '🇫🇯' => 'https://www.foreignaffairs.gov.fj/', // Fiji
    '🇫🇮' => 'https://um.fi/frontpage', // Finland
    '🇫🇷' => 'https://www.diplomatie.gouv.fr/en/', // France
    '🇬🇫' => 'https://www.guyane.pref.gouv.fr/Publications/Actualites/Actualites-2023/Politique-etrangere', // French Guiana (France MFA)
    '🇵🇫' => 'https://www.polynesie-francaise.pref.gouv.fr/Le-haut-commissariat/Relations-internationales', // French Polynesia (France MFA)
    '🇹🇫' => 'https://www.terres-australes.fr/en/the-territory/international-relations', // French Southern Territories (France MFA)
    '🇬🇦' => 'https://www.diplomatie.gouv.ga/', // Gabon
    '🇬🇲' => 'https://mofa.gov.gm/', // Gambia
    '🇬🇪' => 'https://www.mfa.gov.ge/index.aspx?lang=en-US', // Georgia
    '🇩🇪' => 'https://www.auswaertiges-amt.de/en/', // Germany
    '🇬🇭' => 'https://www.mfa.gov.gh/', // Ghana
    '🇬🇮' => 'https://www.gibraltar.gov.gi/foreign-affairs', // Gibraltar (UK FCDO or local)
    '🇬🇷' => 'https://www.mfa.gr/en/', // Greece
    '🇬🇱' => 'https://naalakkersuisut.gl/en/Naalakkersuisut/departments/Department-of-Foreign-Affairs-and-Climate', // Greenland (Denmark MFA or local)
    '🇬🇩' => 'https://www.gov.gd/ministries/ministry-foreign-affairs', // Grenada
    '🇬🇵' => 'https://www.guadeloupe.gouv.fr/Actualites/Cooperation-internationale', // Guadeloupe (France MFA)
    '🇬🇺' => 'https://www.guam.gov/agency/guam-visitors-bureau/', // Guam (US Dept. of State)
    '🇬🇹' => 'https://www.minex.gob.gt/', // Guatemala
    '🇬🇬' => 'https://www.gov.gg/foreignaffairs', // Guernsey (UK FCDO or local)
    '🇬🇳' => 'https://www.diplomatie.gov.gn/index.php/fr/ministere', // Guinea
    '🇬🇼' => 'https://www.embassyofguineabissau.org/', // Guinea-Bissau (Often Embassy)
    '🇬🇾' => 'https://minfora.gov.gy/', // Guyana
    '🇭🇹' => 'https://www.diplomatie.gouv.ht/', // Haiti
    '🇭🇳' => 'https://www.sre.gob.hn/', // Honduras
    '🇭🇰' => 'https://www.gov.hk/en/about/abouthkg/organ/foreign.htm', // Hong Kong SAR China (PRC MFA)
    '🇭🇺' => 'https://www.kormany.hu/en/ministry-of-foreign-affairs-and-trade', // Hungary
    '🇮🇸' => 'https://www.government.is/ministries/foreign-affairs/', // Iceland
    '🇮🇳' => 'https://www.mea.gov.in/', // India
    '🇮🇩' => 'https://kemlu.go.id/portal/en', // Indonesia
    '🇮🇷' => 'https://mfa.gov.ir/', // Iran
    '🇮🇶' => 'https://mofa.gov.iq/en/', // Iraq
    '🇮🇪' => 'https://www.gov.ie/en/organisation/department-of-foreign-affairs/', // Ireland
    '🇮🇲' => 'https://www.gov.im/categories/travel-traffic-and-motoring/foreign-affairs/', // Isle of Man (UK FCDO or local)
    '🇮🇱' => 'https://www.gov.il/en/Departments/ministry_of_foreign_affairs', // Israel
    '🇮🇹' => 'https://www.esteri.it/en/', // Italy
    '🇯🇲' => 'https://www.mfaft.gov.jm/', // Jamaica
    '🇯🇵' => 'https://www.mofa.go.jp/index.html', // Japan
    '🇯🇪' => 'https://www.gov.je/Working/InternationalRelations/Pages/default.aspx', // Jersey (UK FCDO or local)
    '🇯🇴' => 'https://www.mfa.gov.jo/EN', // Jordan
    '🇰🇿' => 'https://www.gov.kz/memleket/entities/mfa?lang=en', // Kazakhstan
    '🇰🇪' => 'https://www.mfa.go.ke/', // Kenya
    '🇰🇮' => 'https://www.mfai.gov.ki/', // Kiribati
    '🇽🇰' => 'https://www.mfa-ks.net/?lang=en', // Kosovo
    '🇰🇼' => 'https://www.mofa.gov.kw/', // Kuwait
    '🇰🇬' => 'https://mfa.gov.kg/en', // Kyrgyzstan
    '🇱🇦' => 'https://www.mofa.gov.la/', // Laos
    '🇱🇻' => 'https://www.mfa.gov.lv/en', // Latvia
    '🇱🇧' => 'https://www.foreign.gov.lb/english/', // Lebanon
    '🇱🇸' => 'https://www.gov.ls/ministries/foreign-affairs/', // Lesotho
    '🇱🇷' => 'https://www.mofa.gov.lr/', // Liberia
    '🇱🇾' => 'https://www.foreign.gov.ly/', // Libya
    '🇱🇮' => 'https://www.regierung.li/en/ministries/foreign-affairs', // Liechtenstein
    '🇱🇹' => 'https://www.urm.lt/default/en/', // Lithuania
    '🇱🇺' => 'https://maee.gouvernement.lu/en.html', // Luxembourg
    '🇲🇴' => 'https://www.gcs.gov.mo/english/contact_us.html', // Macao SAR China (PRC MFA)
    '🇲🇰' => 'https://www.mfa.gov.mk/?lang=en', // North Macedonia
    '🇲🇬' => 'https://www.diplomatie.gov.mg/', // Madagascar
    '🇲🇼' => 'https://www.foreign.gov.mw/', // Malawi
    '🇲🇾' => 'https://www.kln.gov.my/', // Malaysia
    '🇲🇻' => 'https://foreign.gov.mv/', // Maldives
    '🇲🇱' => 'httpswww.diplomatie.ml/', // Mali
    '🇲🇹' => 'https://foreignandeuropean.gov.mt/en/Pages/Home-Page.aspx', // Malta
    '🇲🇭' => 'https://rmiembassyus.org/marshall-islands-government-ministry-foreign-affairs/', // Marshall Islands
    '🇲🇶' => 'https://www.martinique.pref.gouv.fr/Politiques-publiques/Relations-internationales', // Martinique (France MFA)
    '🇲🇷' => 'https://www.diplomatie.gov.mr/', // Mauritania
    '🇲🇺' => 'https://foreign.govmu.org/', // Mauritius
    '🇾🇹' => 'https://www.mayotte.gouv.fr/Politiques-publiques/Relations-internationales', // Mayotte (France MFA)
    '🇲🇽' => 'https://www.gob.mx/sre', // Mexico
    '🇲🇩' => 'https://mfa.gov.md/en', // Moldova
    '🇲🇨' => 'https://www.gouv.mc/Action-Gouvernementale/Monaco-a-l-International', // Monaco
    '🇲🇳' => 'https://www.mfa.gov.mn/', // Mongolia
    '🇲🇪' => 'https://www.gov.me/en/ministries/ministry-of-foreign-affairs', // Montenegro
    '🇲🇸' => 'https://www.gov.ms/department/foreign-affairs/', // Montserrat (UK FCDO or local)
    '🇲🇦' => 'https://www.diplomatie.ma/', // Morocco
    '🇲🇿' => 'https://www.minec.gov.mz/', // Mozambique
    '🇲🇲' => 'https://www.mofa.gov.mm/', // Myanmar (Burma)
    '🇳🇦' => 'https://www.mirco.gov.na/', // Namibia
    '🇳🇷' => 'https://www.gov.nr/government/ministries/foreign-affairs.html', // Nauru
    '🇳🇵' => 'https://mofa.gov.np/', // Nepal
    '🇳🇱' => 'https://www.government.nl/ministries/ministry-of-foreign-affairs', // Netherlands
    '🇳🇨' => 'https://www.nouvelle-caledonie.gouv.fr/relations-internationales/', // New Caledonia (France MFA)
    '🇳🇿' => 'https://www.mfat.govt.nz/', // New Zealand
    '🇳🇮' => 'https://www.cancilleria.gob.ni/', // Nicaragua
    '🇳🇪' => 'https://www.diplomatie.gouv.ne/', // Niger
    '🇳🇬' => 'https://foreignaffairs.gov.ng/', // Nigeria
    '🇳🇺' => 'https://www.gov.nu/government/departments/foreign-affairs/', // Niue
    '🇳🇫' => 'https://www.infrastructure.gov.au/territories-regions-cities/territories/norfolk-island', // Norfolk Island (Australia DOTIR)
    '🇰🇵' => 'http://www.naenara.com.kp/foreign/', // North Korea (Official portal, MFA section often minimal or hard to find)
    '🇲🇵' => 'https://www.cnmi-cmo.gov.mp/about/federal-relations/', // Northern Mariana Islands (US Dept. of State)
    '🇳🇴' => 'https://www.regjeringen.no/en/dep/ud/id1111/', // Norway
    '🇴🇲' => 'https://www.mofa.gov.om/', // Oman
    '🇵🇰' => 'https://mofa.gov.pk/', // Pakistan
    '🇵🇼' => 'https://palauembassy.org/government/ministry-of-foreign-affairs-and-trade/', // Palau
    '🇵🇸' => 'https://www.mofa.pna.ps/en', // Palestine
    '🇵🇦' => 'https://mire.gob.pa/', // Panama
    '🇵🇬' => 'https://foreignaffairs.gov.pg/', // Papua New Guinea
    '🇵🇾' => 'https://www.mre.gov.py/', // Paraguay
    '🇵🇪' => 'https://www.gob.pe/minedu', // Peru (Often Ministry of Education, need to find correct MFA)
    '🇵🇭' => 'https://dfa.gov.ph/', // Philippines
    '🇵🇳' => 'https://www.gov.pn/government/international-relations.html', // Pitcairn Islands (UK FCDO or local)
    '🇵🇱' => 'https://www.gov.pl/web/diplomacy', // Poland
    '🇵🇹' => 'https://www.portaldiplomatico.mne.gov.pt/en/', // Portugal
    '🇵🇷' => 'https://www.estado.pr.gov/', // Puerto Rico (US Dept. of State or local government)
    '🇶🇦' => 'https://www.mofa.gov.qa/en/', // Qatar
    '🇷🇪' => 'https://www.reunion.gouv.fr/Actions-de-l-Etat/Politiques-publiques/Relations-internationales/', // Réunion (France MFA)
    '🇷🇴' => 'https://www.mae.ro/en', // Romania
    '🇷🇺' => 'https://www.mid.ru/en/', // Russia
    '🇷🇼' => 'https://www.minaffet.gov.rw/', // Rwanda
    '🇼🇸' => 'https://www.samoa.travel/about/government-and-economy/', // Samoa (Often general government or tourism)
    '🇸🇲' => 'https://www.esteri.sm/', // San Marino
    '🇸🇹' => 'httpswww.st-tome.gov.st/ministries/foreign-affairs', // São Tomé & Príncipe
    '🇸🇦' => 'https://www.mofa.gov.sa/en/', // Saudi Arabia
    '🇸🇳' => 'https://www.diplomatie.gouv.sn/', // Senegal
    '🇷🇸' => 'https://www.mfa.gov.rs/en', // Serbia
    '🇸🇨' => 'https://www.mfa.gov.sc/', // Seychelles
    '🇸🇱' => 'https://mofaic.gov.sl/', // Sierra Leone
    '🇸🇬' => 'https://www.mfa.gov.sg/', // Singapore
    '🇸🇽' => 'https://www.sintmaartengov.org/government/Ministry-of-Foreign-Affairs/Pages/default.aspx', // Sint Maarten
    '🇸🇰' => 'https://www.mzv.sk/web/en', // Slovakia
    '🇸🇮' => 'https://www.gov.si/en/state-authorities/ministries/ministry-of-foreign-and-european-affairs/', // Slovenia
    '🇸🇧' => 'https://solomons.gov.sb/ministries/ministry-of-foreign-affairs-and-external-trade/', // Solomon Islands
    '🇸🇴' => 'https://www.mfa.gov.so/', // Somalia
    '🇿🇦' => 'https://www.dirco.gov.za/', // South Africa
    '🇬🇸' => 'https://www.gov.uk/government/organisations/foreign-commonwealth-development-office', // South Georgia & South Sandwich Islands (UK FCDO)
    '🇰🇷' => 'https://www.mofa.go.kr/eng/index.do', // South Korea
    '🇸🇸' => 'https://www.mofa.gov.ss/', // South Sudan
    '🇪🇸' => 'https://www.exteriores.gob.es/en/Paginas/index.aspx', // Spain
    '🇱🇰' => 'https://mfa.gov.lk/', // Sri Lanka
    '🇸🇩' => 'https://www.mofa.gov.sd/', // Sudan
    '🇸🇷' => 'https://www.gov.sr/government/ministry-of-foreign-affairs/', // Suriname
    '🇸🇯' => 'https://www.regjeringen.no/en/dep/ud/id1111/', // Svalbard & Jan Mayen (Norway MFA)
    '🇸🇿' => 'https://www.gov.sz/index.php/ministries-departments/ministry-of-foreign-affairs-international-cooperation', // Eswatini
    '🇸🇪' => 'https://www.government.se/government-of-sweden/ministry-for-foreign-affairs/', // Sweden
    '🇨🇭' => 'https://www.eda.admin.ch/eda/en/home.html', // Switzerland
    '🇸🇾' => 'https://www.mofaex.gov.sy/', // Syria
    '🇹🇼' => 'https://en.mofa.gov.tw/', // Taiwan
    '🇹🇯' => 'https://www.mfa.tj/en/', // Tajikistan
    '🇹🇿' => 'https://www.foreign.go.tz/', // Tanzania
    '🇹🇭' => 'https://www.mfa.go.th/', // Thailand
    '🇹🇱' => 'https://www.gov.tl/ministries/foreign-affairs-and-cooperation/', // Timor-Leste
    '🇹🇬' => 'https://www.diplomatie.gouv.tg/', // Togo
    '🇹🇰' => 'https://www.mfat.govt.nz/', // Tokelau (New Zealand MFA)
    '🇹🇴' => 'https://www.mfa.gov.to/', // Tonga
    '🇹🇹' => 'https://www.foreign.gov.tt/', // Trinidad & Tobago
    '🇹🇳' => 'https://www.diplomatie.gov.tn/index.php?id=300&L=2', // Tunisia
    '🇹🇷' => 'https://www.mfa.gov.tr/default.en.mfa', // Turkey
    '🇹🇲' => 'https://www.mfa.gov.tm/en', // Turkmenistan
    '🇹🇨' => 'https://www.gov.tc/foreign-affairs/', // Turks & Caicos Islands (UK FCDO or local)
    '🇹🇻' => 'https://www.tuvalu.gov.tv/ministries/foreign-affairs/', // Tuvalu
    '🇻🇮' => 'https://www.usvi.gov/office-of-the-governor/', // U.S. Virgin Islands (US Dept. of State)
    '🇺🇬' => 'https://www.mofa.go.ug/', // Uganda
    '🇺🇦' => 'https://mfa.gov.ua/en', // Ukraine
    '🇦🇪' => 'https://www.mofaic.gov.ae/en/', // United Arab Emirates
    '🇬🇧' => 'https://www.gov.uk/government/organisations/foreign-commonwealth-development-office', // United Kingdom
    '🇺🇸' => 'https://www.state.gov/', // United States
    '🇺🇾' => 'https://www.gub.uy/ministerio-relaciones-exteriores/', // Uruguay
    '🇺🇿' => 'https://mfa.uz/en/', // Uzbekistan
    '🇻🇺' => 'https://mofcom.gov.vu/', // Vanuatu (Often Ministry of Finance and Economic Management)
    '🇻🇦' => 'https://www.vatican.va/roman_curia/secretariat_state/index_en.htm', // Vatican City (Secretariat of State)
    '🇻🇪' => 'https://www.mppre.gob.ve/', // Venezuela
    '🇻🇳' => 'https://www.mofa.gov.vn/en/', // Vietnam
    '🇼🇫' => 'https://www.wallis-et-futuna.gouv.fr/Les-services-de-l-Etat/Cooperation-regionale-et-relations-internationales', // Wallis & Futuna (France MFA)
    '🇪🇭' => 'https://en.wikipedia.org/wiki/SADR_Ministry_of_Foreign_Affairs', // Western Sahara (Link to Wikipedia as disputed territory)
    '🇾🇪' => 'https://www.mofa.gov.ye/', // Yemen
    '🇿🇲' => 'https://www.mfa.gov.zm/', // Zambia
    '🇿🇼' => 'https://www.zimfa.gov.zw/', // Zimbabwe
];

// --- 2. Generate HTML with Links ---
$flag_links_html_pieces = [];
foreach ($flags_and_mfa_urls as $flag => $url) {
    // Basic title for accessibility and hover info
    $title_attr = "Ministry of Foreign Affairs for " . $flag; // Placeholder for country name if available
    // For a more robust solution, you'd have a separate array of flag_emoji => country_name
    // e.g., $country_names = ['🇦🇫' => 'Afghanistan', ...];
    // then: $title_attr = "Ministry of Foreign Affairs for " . ($country_names[$flag] ?? 'Unknown Country');

    $flag_links_html_pieces[] = '<a href="' . htmlspecialchars($url) . '" target="_blank" rel="noopener noreferrer" title="' . htmlspecialchars($title_attr) . '">' . $flag . '</a>';
}

// Combine all linked flags into a single string, separated by a space
$all_flags_linked_string = implode(' ', $flag_links_html_pieces);

// --- 3. Output HTML Structure and Inline JavaScript ---
?>
<div id="flags-banner-container" style="
    overflow: hidden;
    white-space: nowrap;
    width: 100vw; /* Occupy full viewport width */
    height: 50px; /* Adjust height as needed */
    line-height: 50px; /* Vertically center flags */
    position: relative; /* For absolute positioning of flags-banner-content */
    left: 0; /* Align to the very left edge of the viewport */
    /* These negative margins compensate for any potential parent padding/margin,
       ensuring the 100vw element truly goes edge-to-edge. */
    margin-left: calc(-1 * (100vw - 100%) / 2);
    margin-right: calc(-1 * (100vw - 100%) / 2);
    /* NEW STYLES BELOW */
    background-color: rgba(26, 26, 26, 0.9); /* Matching your existing transparent dark grey */
    border-bottom: 1px solid #333; /* A subtle bottom border for separation */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* A slight shadow for depth */
">
<div id="flags-banner-content" style="
    font-size: 1.5em; /* Significantly reduced from 3em */
    display: inline-block;
    position: absolute;
    top: 0;
    left: 0;
    padding: 0 10px;
">
        <?php
        // Output the string of linked flags directly
        echo $all_flags_linked_string;
        ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bannerContent = document.getElementById('flags-banner-content');
    const container = document.getElementById('flags-banner-container');

    // Get the current content of the banner (the linked flags)
    const originalFlagsContent = bannerContent.innerHTML;

    // Duplicate the content multiple times to create a seamless looping effect.
    // The number of repetitions needs to be enough so that a full "next set"
    // is always visible before the position resets, preventing visual jumps.
    // 25-30 is a good starting point for a very long string of flags.
    bannerContent.innerHTML = originalFlagsContent.repeat(25);

    // Calculate dynamic widths after content is populated
    let contentWidth = bannerContent.scrollWidth; // Actual width of the repeated content
    let containerWidth = container.offsetWidth;   // Visible width of the banner area (100vw)

    let position = 0;
    const speed = 0.5; // Pixels per frame. Adjust for faster/slower scrolling.

    let animationFrameId; // To store the ID of the animation frame for pausing/resuming

    function scrollFlags() {
        position -= speed; // Move content to the left

        // If the content has scrolled past one full set of the original flags,
        // reset its position to create a seamless loop.
        // We divide by the number of times we repeated the string.
        if (position <= -(contentWidth / 25)) {
            position = 0; // Snap back to the start of the next set of flags
        }

        bannerContent.style.transform = `translateX(${position}px)`; // Apply the transform
        animationFrameId = requestAnimationFrame(scrollFlags); // Continue the animation
    }

    // --- Optional: Pause on Hover ---
    // This improves user experience for clickable flags.
    container.addEventListener('mouseenter', () => {
        if (animationFrameId) {
            cancelAnimationFrame(animationFrameId);
            animationFrameId = null;
        }
    });

    container.addEventListener('mouseleave', () => {
        if (!animationFrameId && contentWidth > containerWidth) { // Only resume if paused and content is wider
            scrollFlags();
        }
    });

    // Start the scrolling animation only if the content is wider than the container
    if (contentWidth > containerWidth) {
        scrollFlags();
    }
});
</script>