# Aliska Business Advisory and Research — Website Rebuild

A from-scratch rebuild of aliskaconsult.com using semantic HTML5, plain CSS
(no framework), vanilla JS, and plain PHP for form handling.

## File structure

```
aliska-consult/
├── index.html              Home page
├── our-services.html       Our Services page (7 services)
├── news.html                News / blog listing (5 posts + sidebar)
├── contact.html             Contact page + working contact form
├── book-a-session.html      Booking page + working booking form
├── css/
│   └── style.css            Design tokens, layout, components, responsive rules
├── js/
│   └── main.js               Mobile nav, dark mode, testimonial slider,
│                              cookie banner, newsletter validation, AJAX
│                              form submission + validation
├── php/
│   ├── config.php            Shared mail settings + helpers
│   ├── contact-handler.php   Processes contact.html's form
│   └── booking-handler.php   Processes book-a-session.html's form
└── README.md                 This file
```

To run the forms, serve the folder with PHP (e.g. `php -S localhost:8000`
from inside `aliska-consult/`) — opening the HTML files directly (file://)
will load the pages fine, but the fetch() calls to the PHP handlers need an
actual PHP server, and `mail()` needs a configured MTA/SMTP relay to
actually deliver email in production (update `php/config.php` first).

## What was recreated

- **Home**: topbar, sticky header/nav, hero statement, 4-item services grid,
  "Advisory Firm" intro, "Why Choose Our Firm" (3 value props), testimonial
  carousel (3 real client quotes), Managing Director profile (Aleem Kumi),
  embedded Google Map, full footer (About/Services/Contact/Newsletter),
  cookie consent banner — all using the actual text from the live site.
- **Our Services**: all 7 services in a tile grid (Accounting & Tax Advisory,
  Corporate Strategy Consulting, Human Resource Advisory, Financial
  Advisory, Project Management, IT Advisory, Software and Web Development).
- **News**: all 5 real blog posts (title, author, date, category, excerpt)
  across both pages of the original blog, plus a sidebar (search, recent
  posts).
- **Contact**: address, two phone numbers, office hours, email, embedded
  map, and a contact form.
- **Book A Session**: reproduces the real WPForms field set observed on the
  live page — Name (first/last), Email, Area of interest (5 real options),
  Onsite/Remote radio choice, optional Additional info, and a working
  "Book a session" submit flow.
- **The Empty Room** link is kept in the nav (pointing at
  emptyroom.aliskaconsult.com) but that subsite itself was intentionally
  **not** rebuilt, per your instruction.

## Deviations from the original — and why

1. **Visual design (colors/fonts/exact spacing) is a reconstruction, not a
   pixel copy.** The live site is WordPress (Site Kit by Google, likely an
   Astra/GeneratePress-family theme) and this environment could not load
   its compiled CSS/JS bundles or theme assets directly — only rendered
   page text/structure was reachable. Rather than guess at exact hex values
   and call it "faithful," I designed a deliberate navy/gold palette with
   Spectral (serif) + Work Sans (sans) type suited to a financial/business
   advisory brand, documented in `css/style.css`. If you can share theme
   screenshots, brand colors, or the original stylesheet, I can match it
   exactly.
2. **Contact form fields are inferred, not confirmed.** The Contact page's
   form widget didn't render into the extracted content, only the address/
   phone/hours/map did. I built a standard Name / Email / Phone / Subject /
   Message form as a reasonable recreation of "core functionality" — let me
   know the real field set if it differs.
3. **Blog post detail pages are not included.** The News page recreates the
   full listing (title, author, date, category, excerpt) exactly as shown,
   but individual full article pages (e.g. the "How Management Consultants…"
   full text) were out of scope for this pass; "Continue Reading" links are
   left as in-page anchors. I can build these out as static article pages
   if you send the article text, or if you'd like me to fetch each one.
4. **Images** are hot-linked from the original site's `wp-content/uploads`
   paths so the real photos show up immediately. For a production
   deployment, download these and serve them from your own `/assets/images`
   folder instead of hot-linking another server.
5. **Newsletter signup** is client-side only (matches the original's visible
   validation messages: "Email is required" / "Email is not valid" /
   "Thanks for your subscription.") — wire it to Mailchimp/Brevo/etc. or a
   PHP handler for real delivery.
6. **PHP mail delivery**: `mail()` is used for zero-dependency portability,
   but many hosts need SMTP (PHPMailer) for reliable delivery — flagged in
   code comments in `php/config.php`.

## Improvements over the original

- Semantic HTML5 landmarks (`header`, `nav`, `main`, `footer`, `aside`),
  ARIA labelling, skip-to-content link, visible focus states.
- Fully responsive (mobile nav drawer, fluid grids, `clamp()` type scale).
- Dark mode toggle (persisted via `localStorage`).
- Real client-side + server-side form validation with inline errors and
  a honeypot field for spam protection, replacing "please enable
  JavaScript" as a hard requirement.
- `prefers-reduced-motion` respected throughout.
- Single shared stylesheet/script instead of a page-builder's bundled CSS,
  so it's easy to extend and maintain.
