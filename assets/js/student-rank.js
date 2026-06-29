(() => {
    const rankThresholds = [
        [35000, 'S5'],
        [26000, 'S4'],
        [23000, 'SSS+'],
        [20000, 'SSS'],
        [16000, 'SS'],
        [14500, 'S+'],
        [13000, 'S'],
        [12000, 'A+'],
        [11000, 'A'],
        [10000, 'B+'],
        [9000, 'B'],
        [7500, 'C+'],
        [6500, 'C'],
        [5000, 'D'],
        [3000, 'E'],
    ];

    window.calculateStudentRank = (vocal, dance, visual) => {
        const rating = (
            Math.max(0, Number(vocal) || 0)
            + Math.max(0, Number(dance) || 0)
            + Math.max(0, Number(visual) || 0)
        ) * 2.3;

        const matchingRank = rankThresholds.find(([minimumRating]) => rating >= minimumRating);

        return matchingRank ? matchingRank[1] : 'Debut';
    };
})();
