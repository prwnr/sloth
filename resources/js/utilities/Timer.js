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

        return hours + ":" + this.pad(minutes);
    }

    /**
     * @param format
     * @returns {*}
     */
    revert(format) {
        let time = this.getCorrectedTime(format);

        let minutes = parseInt(time[1]) + this.hoursToMinutes(time[0]);
        return this.minutesToSeconds(minutes);
    }

    /**
     * Get time array with two elements and proper values
     * @param timeString
     * @returns {array}
     */
    getCorrectedTime(timeString) {
        let time = [];
        if (timeString.includes(':')) {
            time = timeString.split(':');
            if (time.length == 2 && !time[1]) {
                time[1] = '00';
            }
        } else {
            time[0] = '00';
            time[1] = timeString;
        }

        return time;
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