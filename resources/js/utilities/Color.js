/**
 * Class color
 */
class Color {

    /**
     * Get random RGB color
     * @returns {string}
     */
    static randomRgb() {
        let r = Math.floor(Math.random() * 255);
        let g = Math.floor(Math.random() * 255);
        let b = Math.floor(Math.random() * 255);
        return "rgb(" + r + "," + g + "," + b + ")";
    }
}

export default Color;