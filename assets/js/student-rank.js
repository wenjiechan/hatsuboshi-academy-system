(() => {
    // Keep these thresholds aligned with the generated rank column in the database.
    const rankThresholds = [
        [0, 'Debut'],
        [3000, 'E'],
        [5000, 'D'],
        [6500, 'C'],
        [7500, 'C+'],
        [9000, 'B'],
        [10000, 'B+'],
        [11000, 'A'],
        [12000, 'A+'],
        [13000, 'S'],
        [14500, 'S+'],
        [16000, 'SS'],
        [20000, 'SSS'],
        [23000, 'SSS+'],
        [26000, 'S4'],
        [35000, 'S5'],
    ];

    window.calculateStudentRating = (vocal, dance, visual) => (
            Math.max(0, Number(vocal) || 0)
            + Math.max(0, Number(dance) || 0)
            + Math.max(0, Number(visual) || 0)
        ) * 2.3;

    window.getStudentRankProgress = (vocal, dance, visual) => {
        const rating = window.calculateStudentRating(vocal, dance, visual);
        let currentIndex = 0;

        rankThresholds.forEach(([minimumRating], index) => {
            if (rating >= minimumRating) {
                currentIndex = index;
            }
        });

        const [currentMinimum, currentRank] = rankThresholds[currentIndex];
        const nextThreshold = rankThresholds[currentIndex + 1] ?? null;

        if (!nextThreshold) {
            return {
                rating,
                currentRank,
                nextRank: null,
                remaining: 0,
                percentage: 100,
            };
        }

        const [nextMinimum, nextRank] = nextThreshold;
        const range = nextMinimum - currentMinimum;
        const percentage = range > 0
            ? Math.max(0, Math.min(100, ((rating - currentMinimum) / range) * 100))
            : 0;

        return {
            rating,
            currentRank,
            nextRank,
            remaining: Math.max(0, Math.ceil(nextMinimum - rating)),
            percentage,
        };
    };

    window.calculateStudentRank = (vocal, dance, visual) => (
        window.getStudentRankProgress(vocal, dance, visual).currentRank
    );

    document.querySelectorAll('[data-student-performance]').forEach((container) => {
        const progress = window.getStudentRankProgress(
            container.dataset.vocal,
            container.dataset.dance,
            container.dataset.visual
        );
        const formattedRating = Math.round(progress.rating).toLocaleString();

        container.querySelectorAll('[data-current-rating]').forEach((element) => {
            element.textContent = formattedRating;
        });

        const currentRank = container.querySelector('[data-current-rank]');
        const nextRank = container.querySelector('[data-next-rank]');
        const nextRankGroup = container.querySelector('[data-next-rank-group]');
        const track = container.querySelector('[data-rank-progress-track]');
        const fill = container.querySelector('[data-rank-progress-fill]');
        const remaining = container.querySelector('[data-rank-progress-remaining]');

        if (currentRank) {
            currentRank.textContent = progress.currentRank;
            currentRank.dataset.rank = progress.currentRank;
        }

        if (nextRank) {
            if (progress.nextRank) {
                nextRank.textContent = progress.nextRank;
                nextRank.dataset.rank = progress.nextRank;
            } else {
                delete nextRank.dataset.rank;
            }
        }

        if (nextRankGroup) nextRankGroup.hidden = !progress.nextRank;

        if (track) {
            track.setAttribute('aria-valuenow', String(Math.round(progress.percentage)));
            track.setAttribute(
                'aria-label',
                progress.nextRank
                    ? `Progress from ${progress.currentRank} to ${progress.nextRank}`
                    : 'Highest rank achieved'
            );
        }

        if (fill) fill.style.width = `${progress.percentage.toFixed(2)}%`;

        if (remaining) {
            remaining.textContent = progress.nextRank
                ? `${progress.remaining.toLocaleString()} rating remaining to reach ${progress.nextRank}`
                : 'Highest rank achieved';
        }
    });
})();
