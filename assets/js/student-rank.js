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

    document.querySelectorAll('[data-rank-calculator]').forEach((calculator) => {
        const fields = {
            vocal: calculator.querySelector('[data-calculator-stat="vocal"]'),
            dance: calculator.querySelector('[data-calculator-stat="dance"]'),
            visual: calculator.querySelector('[data-calculator-stat="visual"]'),
        };
        const ratingOutput = calculator.querySelector('[data-calculator-rating]');
        const rankOutput = calculator.querySelector('[data-calculator-rank]');

        const updateCalculator = () => {
            const result = window.getStudentRankProgress(
                fields.vocal?.value,
                fields.dance?.value,
                fields.visual?.value
            );

            if (ratingOutput) {
                ratingOutput.textContent = Math.round(result.rating).toLocaleString();
            }

            if (rankOutput) {
                rankOutput.textContent = result.currentRank;
                rankOutput.dataset.rank = result.currentRank;
            }
        };

        Object.values(fields).forEach((field) => {
            field?.addEventListener('input', updateCalculator);
        });

        updateCalculator();
    });

    document.querySelectorAll('[data-rank-table-body]').forEach((tableBody) => {
        const performanceContainer = tableBody.closest('[data-student-performance]');
        const currentRank = performanceContainer
            ? window.calculateStudentRank(
                performanceContainer.dataset.vocal,
                performanceContainer.dataset.dance,
                performanceContainer.dataset.visual
            )
            : null;

        rankThresholds.forEach(([minimumRating, rank], index) => {
            const nextMinimum = rankThresholds[index + 1]?.[0] ?? null;
            const minimumStats = Math.ceil(minimumRating / 2.3);
            const maximumStats = nextMinimum === null
                ? null
                : Math.ceil(nextMinimum / 2.3) - 1;
            const row = document.createElement('tr');

            if (rank === currentRank) {
                row.classList.add('current-rank-row');
                row.setAttribute('aria-current', 'true');
            }

            const rankCell = document.createElement('th');
            rankCell.scope = 'row';
            rankCell.textContent = rank;

            const ratingCell = document.createElement('td');
            ratingCell.textContent = nextMinimum === null
                ? `${minimumRating.toLocaleString()}+`
                : `${minimumRating.toLocaleString()}–${(nextMinimum - 1).toLocaleString()}`;

            const statsCell = document.createElement('td');
            statsCell.textContent = maximumStats === null
                ? `${minimumStats.toLocaleString()}+`
                : `${minimumStats.toLocaleString()}–${maximumStats.toLocaleString()}`;

            const badgeCell = document.createElement('td');
            const badge = document.createElement('span');
            badge.className = 'rank-badge';
            badge.dataset.rank = rank;
            badge.textContent = rank;
            badgeCell.appendChild(badge);

            row.append(rankCell, ratingCell, statsCell, badgeCell);
            tableBody.appendChild(row);
        });
    });
})();
