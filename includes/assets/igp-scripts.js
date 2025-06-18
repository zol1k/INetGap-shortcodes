function setUniformCardHeight() {
    const cards = document.querySelectorAll('.product-card');
    let maxHeight = 0;

    // Reset výšky kariet
    cards.forEach(card => {
        card.style.height = 'auto';
    });

    // Zisti najvyššiu výšku
    cards.forEach(card => {
        const height = card.offsetHeight;
        if (height > maxHeight) {
            maxHeight = height;
        }
    });

    // Nastav túto výšku všetkým
    cards.forEach(card => {
        card.style.height = maxHeight + 'px';
    });
}

// Spusti po načítaní a pri zmene veľkosti
window.addEventListener('load', setUniformCardHeight);
window.addEventListener('resize', setUniformCardHeight);