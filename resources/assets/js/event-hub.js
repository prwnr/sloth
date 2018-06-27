/**
 * Event Hub class to fire and listen for events in application
 * @type {{listen(*=, *=): void, fire(*=, *=): void}}
 */
window.EventHub = new class {
    /**
     * EventHub constructor
     */
    constructor() {
        this.vue = new Vue();
    }

    /**
     * Fire event
     * @param event
     * @param data
     */
    fire(event, data = null) {
        this.vue.$emit(event, data);
    }

    /**
     * Listen for event
     * @param event
     * @param callback
     */
    listen(event, callback) {
        this.vue.$on(event, callback);
    }

    /**
     * Forget event
     * @param event 
     */
    forget(event) {
        this.vue.$off(event);
    }
};