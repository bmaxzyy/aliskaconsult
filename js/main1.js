/**
 * Aliska Business Advisory and Research — site scripts
 * Handles: mobile nav, dark mode, testimonial carousel, cookie consent,
 * newsletter sign-up, and progressive-enhancement form validation/AJAX
 * submission for the contact and booking forms.
 */
(function () {
  'use strict';

  /* ----------------------------- Mobile nav ----------------------------- */
  const navToggle = document.querySelector('.nav-toggle');
  const mainNav = document.querySelector('.main-nav');
  const navBackdrop = document.querySelector('.nav-backdrop');

  function closeNav() {
    if (!mainNav) return;
    mainNav.classList.remove('is-open');
    navBackdrop && navBackdrop.classList.remove('is-open');
    navToggle && navToggle.setAttribute('aria-expanded', 'false');
  }

  if (navToggle && mainNav) {
    navToggle.addEventListener('click', () => {
      const isOpen = mainNav.classList.toggle('is-open');
      navBackdrop && navBackdrop.classList.toggle('is-open', isOpen);
      navToggle.setAttribute('aria-expanded', String(isOpen));
    });
    navBackdrop && navBackdrop.addEventListener('click', closeNav);
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeNav();
    });
    mainNav.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeNav));
  }

  /* ------------------------------ Dark mode ------------------------------ */
  const themeToggle = document.querySelector('.theme-toggle');
  const root = document.documentElement;
  const savedTheme = localStorage.getItem('aliska-theme');
  if (savedTheme) root.setAttribute('data-theme', savedTheme);

  if (themeToggle) {
    themeToggle.addEventListener('click', () => {
      const current = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      root.setAttribute('data-theme', current);
      localStorage.setItem('aliska-theme', current);
    });
  }

  /* --------------------------- Testimonial slider ------------------------- */
  const track = document.querySelector('.testimonial-track');
  const dots = document.querySelectorAll('.testimonial-dot');
  if (track && dots.length) {
    let index = 0;
    let autoplay;

    function goTo(i) {
      index = (i + dots.length) % dots.length;
      track.scrollTo({ left: track.clientWidth * index, behavior: 'smooth' });
      dots.forEach((d, di) => d.setAttribute('aria-current', String(di === index)));
    }

    dots.forEach((dot, i) => dot.addEventListener('click', () => { goTo(i); resetAutoplay(); }));

    function resetAutoplay() {
      clearInterval(autoplay);
      autoplay = setInterval(() => goTo(index + 1), 7000);
    }
    resetAutoplay();
    track.addEventListener('mouseenter', () => clearInterval(autoplay));
    track.addEventListener('mouseleave', resetAutoplay);
  }

  /* ------------------------------ Cookie banner --------------------------- */
  const cookieBanner = document.querySelector('.cookie-banner');
  const cookieAccept = document.querySelector('.cookie-accept');
  if (cookieBanner) {
    if (!localStorage.getItem('aliska-cookie-consent')) {
      setTimeout(() => cookieBanner.classList.add('is-visible'), 600);
    }
    cookieAccept && cookieAccept.addEventListener('click', () => {
      localStorage.setItem('aliska-cookie-consent', 'accepted');
      cookieBanner.classList.remove('is-visible');
    });
  }

  /* ------------------------------- Newsletter ------------------------------ */
  const newsletterForm = document.querySelector('.newsletter-form');
  if (newsletterForm) {
    const msg = newsletterForm.parentElement.querySelector('.form-msg');
    newsletterForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const input = newsletterForm.querySelector('input[type="email"]');
      const value = input.value.trim();
      const validEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
      if (!value) {
        msg.textContent = 'Email is required';
        msg.className = 'form-msg error';
        return;
      }
      if (!validEmail) {
        msg.textContent = 'Email is not valid';
        msg.className = 'form-msg error';
        return;
      }
      msg.textContent = 'Thanks for your subscription.';
      msg.className = 'form-msg success';
      newsletterForm.reset();
    });
  }

  /* --------------------------- Generic form helpers ------------------------ */
  function showFieldError(field, message) {
    field.classList.add('has-error');
    const err = field.querySelector('.field-error');
    if (err) err.textContent = message;
  }
  function clearFieldError(field) {
    field.classList.remove('has-error');
  }

  function validateForm(form) {
    let valid = true;
    form.querySelectorAll('[data-field]').forEach((field) => {
      clearFieldError(field);
      const control = field.querySelector('input, select, textarea');
      if (!control) return;
      const isRequired = control.hasAttribute('required');
      const value = control.type === 'radio'
        ? !!field.querySelector('input[type="radio"]:checked')
        : control.value.trim();

      if (isRequired && !value) {
        showFieldError(field, 'This field is required.');
        valid = false;
        return;
      }
      if (control.type === 'email' && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
        showFieldError(field, 'Please enter a valid email address.');
        valid = false;
      }
    });
    return valid;
  }

  async function handleAjaxForm(form, endpoint) {
    const status = form.querySelector('.form-status');
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (!validateForm(form)) return;

      const submitBtn = form.querySelector('button[type="submit"]');
      const originalLabel = submitBtn.textContent;
      submitBtn.disabled = true;
      submitBtn.textContent = 'Sending…';
      status.className = 'form-status';

      try {
        const res = await fetch(endpoint, {
          method: 'POST',
          body: new FormData(form),
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json().catch(() => ({ success: res.ok }));

        if (data.success) {
          status.textContent = data.message || 'Thank you — your message has been sent.';
          status.className = 'form-status success show';
          form.reset();
        } else {
          status.textContent = data.message || 'Something went wrong. Please try again or call us directly.';
          status.className = 'form-status error show';
        }
      } catch (err) {
        status.textContent = 'We could not reach the server. Please check your connection and try again.';
        status.className = 'form-status error show';
      } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalLabel;
        status.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });
  }

  const contactForm = document.querySelector('#contact-form');
  if (contactForm) handleAjaxForm(contactForm, 'php/contact-handler.php');

  const bookingForm = document.querySelector('#booking-form');
  if (bookingForm) handleAjaxForm(bookingForm, 'php/booking-handler.php');

  /* ------------------------------ Reveal on load ---------------------------- */
  const heroGraphic = document.querySelector('.hero__graphic path.trend-line');
  if (heroGraphic && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const length = heroGraphic.getTotalLength();
    heroGraphic.style.strokeDasharray = length;
    heroGraphic.style.strokeDashoffset = length;
    requestAnimationFrame(() => {
      heroGraphic.style.transition = 'stroke-dashoffset 1.6s ease';
      heroGraphic.style.strokeDashoffset = '0';
    });
  }
})();
