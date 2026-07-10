const search = document.getElementById('docsSearch');
const sections = [...document.querySelectorAll('[data-doc-section]')];
const navLinks = [...document.querySelectorAll('#docsNav a')];

search?.addEventListener('input', () => {
    const term = search.value.trim().toLowerCase();

    sections.forEach((section) => {
        section.classList.toggle('is-hidden', term !== '' && !section.textContent.toLowerCase().includes(term));
    });
});

const observer = new IntersectionObserver((entries) => {
    const visible = entries
        .filter((entry) => entry.isIntersecting && entry.target.id)
        .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

    if (!visible) return;

    navLinks.forEach((link) => {
        link.classList.toggle('active', link.getAttribute('href') === `#${visible.target.id}`);
    });
}, { rootMargin: '-20% 0px -65% 0px', threshold: [0.1, 0.4, 0.8] });

sections.forEach((section) => observer.observe(section));
