/* ========== Mobile menu ========== */
const hamburger = document.querySelector('.hamburger');
const navLinks = document.querySelector('.nav-links');
if (hamburger) {
  hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navLinks.classList.toggle('open');
  });
}

document.querySelectorAll('.submenu-toggle').forEach(toggle => {
  toggle.addEventListener('click', event => {
    if (!window.matchMedia('(max-width: 900px)').matches) {
      return;
    }

    event.preventDefault();
    event.stopPropagation();
    const item = toggle.closest('.has-submenu');
    const isOpen = item.classList.toggle('open');
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });
});

/* ========== Reveal on scroll ========== */
const revealEls = document.querySelectorAll('.reveal');
if (revealEls.length && 'IntersectionObserver' in window) {
  const io = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('visible');
        io.unobserve(e.target);
      }
    });
  }, { threshold: 0.12 });
  revealEls.forEach(el => io.observe(el));
}

/* ========== Testimonials carousel ========== */
const carousel = document.querySelector('.carousel');
if (carousel) {
  const track = carousel.querySelector('.carousel-track');
  const slides = track.children;
  const dotsWrap = carousel.querySelector('.carousel-dots');
  let current = 0;
  for (let i = 0; i < slides.length; i++) {
    const b = document.createElement('button');
    b.setAttribute('aria-label', 'Slide ' + (i + 1));
    if (i === 0) b.classList.add('active');
    b.addEventListener('click', () => goTo(i));
    dotsWrap.appendChild(b);
  }
  function goTo(i) {
    current = i;
    track.style.transform = `translateX(-${i * 100}%)`;
    [...dotsWrap.children].forEach((d, idx) => d.classList.toggle('active', idx === i));
  }
  setInterval(() => goTo((current + 1) % slides.length), 5000);
}

/* ========== FAQ accordion ========== */
document.querySelectorAll('.faq-item').forEach(item => {
  const q = item.querySelector('.faq-q');
  const a = item.querySelector('.faq-a');
  q.addEventListener('click', () => {
    const open = item.classList.toggle('open');
    a.style.maxHeight = open ? a.scrollHeight + 'px' : 0;
  });
});

/* ========== Pricing toggle ========== */
const toggleBtns = document.querySelectorAll('.pricing-toggle button');
toggleBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    toggleBtns.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const mode = btn.dataset.mode;
    document.querySelectorAll('.price strong').forEach(p => {
      const m = parseFloat(p.dataset.monthly);
      const a = parseFloat(p.dataset.annual);
      p.textContent = '$' + (mode === 'annual' ? a : m).toFixed(2);
    });
    document.querySelectorAll('.price-period').forEach(s => {
      s.textContent = mode === 'annual' ? '/yr' : '/mo';
    });
  });
});

/* ========== Domain search (mock) ========== */
const domainForm = document.querySelector('#domain-search-form');
if (domainForm) {
  domainForm.addEventListener('submit', e => {
    e.preventDefault();
    const name = domainForm.querySelector('input').value.trim().toLowerCase().replace(/\s+/g, '');
    const tld = domainForm.querySelector('select').value;
    const results = document.querySelector('#domain-results');
    if (!name) return;
    const tlds = ['.com','.net','.io','.co','.dev','.app'];
    const list = [tld, ...tlds.filter(t => t !== tld)].slice(0, 6);
    results.innerHTML = list.map((t, i) => {
      const available = i % 3 !== 1;
      const price = (9.99 + i * 2.5).toFixed(2);
      return `<div class="domain-row">
        <div class="domain-name">${name}${t}</div>
        <div class="${available ? 'status-available' : 'status-taken'}">${available ? 'Available' : 'Taken'}</div>
        <div><strong>$${price}</strong>/yr</div>
        <button class="btn ${available ? 'btn-primary' : 'btn-outline'}" ${available ? '' : 'disabled style="opacity:.5;cursor:not-allowed"'}>${available ? 'Add to Cart' : 'Unavailable'}</button>
      </div>`;
    }).join('');
    results.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
}

/* ========== Form validation ========== */
function validateField(input) {
  const group = input.closest('.form-group');
  let valid = true;
  if (input.required && !input.value.trim()) valid = false;
  if (input.type === 'email' && input.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value)) valid = false;
  if (input.type === 'password' && input.value && input.value.length < 6) valid = false;
  group.classList.toggle('invalid', !valid);
  return valid;
}
document.querySelectorAll('form[data-validate]').forEach(form => {
  const fields = form.querySelectorAll('input, textarea');
  fields.forEach(f => f.addEventListener('blur', () => validateField(f)));
  form.addEventListener('submit', e => {
    e.preventDefault();
    let ok = true;
    fields.forEach(f => { if (!validateField(f)) ok = false; });
    if (ok) {
      const success = form.querySelector('.form-success');
      if (success) { success.classList.add('show'); form.reset(); setTimeout(() => success.classList.remove('show'), 4000); }
      else alert('Submitted successfully!');
    }
  });
});

/* ========== Hero domain bar (homepage) — link to domain page ========== */
const heroDomain = document.querySelector('#hero-domain-form');
if (heroDomain) {
  heroDomain.addEventListener('submit', e => {
    e.preventDefault();
    const q = heroDomain.querySelector('input').value.trim();
    window.location.href = 'domain.html' + (q ? '?q=' + encodeURIComponent(q) : '');
  });
}

/* Prefill domain page from query */
const params = new URLSearchParams(location.search);
const qParam = params.get('q');
if (qParam && document.querySelector('#domain-search-form input')) {
  const inp = document.querySelector('#domain-search-form input');
  inp.value = qParam;
  document.querySelector('#domain-search-form').dispatchEvent(new Event('submit'));
}

/* ========== Pricing toggle (updated for dynamic blocks) ========== */
document.querySelectorAll('.pricing-toggle button').forEach(btn => {
  btn.addEventListener('click', () => {
    const wrap = btn.closest('.pricing-toggle');
    wrap.querySelectorAll('button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const mode = btn.dataset.mode;
    
    const card = btn.closest('section') || document;
    card.querySelectorAll('.price strong').forEach(p => {
      const m = parseFloat(p.dataset.monthly);
      const a = parseFloat(p.dataset.annual);
      const currency = p.dataset.currency || 'TZS';
      if (m && a) {
        p.textContent = currency + ' ' + (mode === 'annual' ? a : m).toLocaleString();
      }
    });
    card.querySelectorAll('.price-period').forEach(s => {
      s.textContent = mode === 'annual' ? '/yr' : '/mo';
    });
  });
});
