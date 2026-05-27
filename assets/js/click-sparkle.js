document.addEventListener('click', (event) => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    const sparkles = [
        { symbol: '★', className: 'gold medium', x: 0, y: 0 },
        { symbol: '✦', className: 'small', x: -14, y: -8 },
        { symbol: '✧', className: 'secondary small', x: 14, y: -6 },
        { symbol: '✦', className: 'gold small', x: -8, y: 12 },
        { symbol: '✧', className: 'small', x: 10, y: 10 },
    ];

    sparkles.forEach((item) => {
        const sparkle = document.createElement('span');

        sparkle.className = `click-sparkle ${item.className}`;
        sparkle.textContent = item.symbol;
        sparkle.style.left = `${event.clientX + item.x}px`;
        sparkle.style.top = `${event.clientY + item.y}px`;

        document.body.appendChild(sparkle);

        sparkle.addEventListener('animationend', () => {
            sparkle.remove();
        });
    });
});