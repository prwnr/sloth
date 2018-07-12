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
     * @param format
     * @returns {*}
     */
    revert(format) {
        let time = format.split(':');
        let minutes = parseInt(time[1]) + this.hoursToMinutes(time[0]);
        return this.minutesToSeconds(minutes);
    }

    /**
     * Makes sure that MM in HH:MM won't go over 60 minutes
     */
    correctTime(duration) {
        let time = duration.split(':');
        if (time.length != 2) {
            return null;
        }

        if (time[1] > 60) {
            return time[0] + ':' + 60;
        }
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
     * @param hours
     * @returns {*}
     */
    hoursToMinutes(hours) {
        return Math.floor(hours * 60);
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