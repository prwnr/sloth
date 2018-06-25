/**
 * Class errors
 */
export class Errors {

    /**
     * Create a new Errors instance
     */
    constructor() {
        this.errors = {};
    }

    /**
     * Get error message for given field.
     * @param field
     * @returns {string} field
     */
    get(field) {
        if (this.errors[field]) {
            return this.errors[field][0];
        }
    }

    /**
     * Record new errors.
     * @param errors
     */
    record(errors) {
        this.errors = errors;
    }

    /**
     * Check if error exists for given field.
     * @param field
     * @returns {boolean}
     */
    has(field) {
        if (this.errors[field]) {
            return true;
        }
        return false;
    }

    /**
     * Check if any error exists.
     * @returns {boolean}
     */
    any() {
        return Object.keys(this.errors).length > 0;
    }

    /**
     * Clear one or all error fields.
     * @param field
     */
    clear(field) {
        if(field) {
            delete this.errors[field];
            return;
        }
        delete this.errors[field];
    }
}