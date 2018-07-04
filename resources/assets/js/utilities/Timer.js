/**
 * Class Timer
 */
class Timer {

    /**
     * Formats seconds to HH:mm
     * @param seconds
     * @returns {string}
     */
    format(seconds) {
        let minutes = this.secondsToMinutes(seconds);
        let hours = this.minutesToHours(minutes);
        minutes = minutes % 60;

        return this.pad(hours) + ":" + this.pad(minutes);
    }

    /**
     * @param seconds
     * @returns {number}
     */
    secondsToMinutes(seconds) {
        return Math.floor(seconds / 60);
    }

    /**
     * @param minutes
     * @returns {number}
     */
    minutesToSeconds(minutes) {
        return minutes * 60;
    }

    /**
     * @param minutes
     * @returns {number}
     */
    minutesToHours(minutes) {
        return Math.floor(minutes / 60);
    }

    /**
     * @param value
     * @returns {string}
     */
    pad(value) {
        return ("0" + value).slice(-2);
    }
}

export default Timer;