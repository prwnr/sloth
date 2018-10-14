/**
 * Class color
 */
class Color {

    /**
     * Constructor
     */
    constructor() {
        this.generated = [];
        this.colorsSet = [
            0, 150, 200, 230, 245, 340
        ];
    }

    /**
     * Get random RGB color
     * @returns {string}
     */
    random() {
        let H = this.colorsSet[Math.floor(Math.random() * this.colorsSet.length)];
        let S = Math.floor(Math.random() * (75 - 50 + 1)) + 50;
        let L = Math.floor(Math.random() * (65 - 45 + 1)) + 45;

        if (this.generated.includes(`${H + S + L}`)) {
            return this.random();
        }

        this.generated.push(`${H + S + L}`);
        return `hsl(${H},${S}%,${L}%)`;
    }

    /**
     * Clears generated colors array
     */
    clearGenerated() {
        this.generated = [];
    }
}


export default Color;