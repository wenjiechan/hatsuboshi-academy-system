(function () {
    'use strict';

    const CANVAS_ID = 'birthday-sparkle-canvas';
    const PIECE_COUNT = 12;
    const LIFETIME_MS = 650;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    let canvas = null;
    let ctx = null;
    let particles = [];
    let rafId = null;

    const normalSparkles = [
        { symbol: '\u2605', className: 'gold medium', x: 0, y: 0 },
        { symbol: '\u2726', className: 'small', x: -14, y: -8 },
        { symbol: '\u2727', className: 'secondary small', x: 14, y: -6 },
        { symbol: '\u2726', className: 'gold small', x: -8, y: 12 },
        { symbol: '\u2727', className: 'small', x: 10, y: 10 },
    ];

    function isBirthdayMode() {
        return document.body.dataset.clickSparkle === 'birthday';
    }

    function getThemeColors() {
        const style = getComputedStyle(document.body);
        const primary = style.getPropertyValue('--primary').trim() || '#FF6B9D';
        const secondary = style.getPropertyValue('--secondary').trim() || '#FFB3D1';

        return [primary, secondary, '#FFD454', '#6BB6FF', '#7DD87D', '#B388FF', '#FFFFFF'];
    }

    function resizeCanvas() {
        if (!canvas || !ctx) {
            return;
        }

        const ratio = window.devicePixelRatio || 1;
        canvas.width = Math.round(window.innerWidth * ratio);
        canvas.height = Math.round(window.innerHeight * ratio);
        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
    }

    function mountCanvas() {
        if (canvas && ctx) {
            return;
        }

        canvas = document.createElement('canvas');
        canvas.id = CANVAS_ID;
        canvas.style.cssText = [
            'position:fixed',
            'inset:0',
            'width:100%',
            'height:100%',
            'pointer-events:none',
            'z-index:9999',
        ].join(';');

        document.body.appendChild(canvas);
        ctx = canvas.getContext('2d');
        resizeCanvas();
    }

    function drawBirthdayConfetti() {
        if (!ctx) {
            rafId = null;
            return;
        }

        const now = performance.now();
        particles = particles.filter((particle) => now - particle.born < LIFETIME_MS);
        ctx.clearRect(0, 0, window.innerWidth, window.innerHeight);

        particles.forEach((particle) => {
            const age = (now - particle.born) / LIFETIME_MS;
            const alpha = 1 - age;

            ctx.save();
            ctx.globalAlpha = alpha;
            ctx.fillStyle = particle.color;
            ctx.translate(particle.x, particle.y);
            ctx.rotate(particle.rotation);
            ctx.fillRect(-particle.width / 2, -particle.height / 2, particle.width, particle.height);
            ctx.restore();

            particle.x += particle.vx;
            particle.y += particle.vy;
            particle.vy += 0.12;
            particle.rotation += particle.rotationVelocity;
        });

        if (particles.length > 0) {
            rafId = requestAnimationFrame(drawBirthdayConfetti);
            return;
        }

        rafId = null;
        ctx.clearRect(0, 0, window.innerWidth, window.innerHeight);
    }

    function spawnBirthdayConfetti(x, y) {
        mountCanvas();

        const colors = getThemeColors();
        const now = performance.now();

        for (let index = 0; index < PIECE_COUNT; index += 1) {
            const angle = Math.random() * Math.PI * 2;
            const speed = 0.9 + Math.random() * 1.8;

            particles.push({
                x,
                y,
                vx: Math.cos(angle) * speed,
                vy: Math.sin(angle) * speed - 1.2,
                width: 4 + Math.random() * 5,
                height: 3 + Math.random() * 4,
                rotation: Math.random() * Math.PI,
                rotationVelocity: (Math.random() - 0.5) * 0.2,
                color: colors[Math.floor(Math.random() * colors.length)],
                born: now,
            });
        }

        if (!rafId) {
            rafId = requestAnimationFrame(drawBirthdayConfetti);
        }
    }

    function spawnNormalSparkles(x, y) {
        normalSparkles.forEach((item) => {
            const sparkle = document.createElement('span');

            sparkle.className = `click-sparkle ${item.className}`;
            sparkle.textContent = item.symbol;
            sparkle.style.left = `${x + item.x}px`;
            sparkle.style.top = `${y + item.y}px`;

            document.body.appendChild(sparkle);

            sparkle.addEventListener('animationend', () => {
                sparkle.remove();
            });
        });
    }

    document.addEventListener('click', (event) => {
        if (reducedMotion.matches) {
            return;
        }

        if (isBirthdayMode()) {
            spawnBirthdayConfetti(event.clientX, event.clientY);
            return;
        }

        spawnNormalSparkles(event.clientX, event.clientY);
    });

    window.addEventListener('resize', resizeCanvas);
})();
