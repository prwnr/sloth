import Errors from './Errors.js';

/**
 * class Form
 */
class Form {

    /**
     * Create a new Form instance
     * @param data
     */
    constructor(data) {
        this.originalData = data;

        for (let field in data) {
            this[field] = data[field]
        }

        this.errors = new Errors();
    }

    /**
     * Fetch all relevant data for the form.
     */
    data() {
        let data = {};
        for (let property in this.originalData) {
            data[property] = this[property];
        }
        return data;
    }

    /**
     * Reset the form fields.
     */
    reset() {
        for (let field in this.originalData) {
            this[field] = '';
        }

        this.errors.clear();
    }

    /**
     * Submit form using PUT method.
     * @param url
     * @returns url
     */
    put(url) {
        return this.submit('put', url);
    }

    /**
     * Submit form using POST method.
     * @param url
     * @returns url
     */
    post(url) {
        return this.submit('post', url);
    }

    /**
     * Submit the form
     * @param requestType
     * @param url
     */
    submit(requestType, url) {
        return new Promise((resolve, reject) => {
            axios[requestType](url, this.data())
                .then(response => {
                    this.onSuccess(response.data);
                    resolve(response.data);
            })
            .catch(error => {
                if (error.response.data.errors){
                    this.onFail(error.response.data.errors);
                }
                reject(error.response.data);
            });
        });
    }

    /**
     * Handle a succesful form submission.
     * @param response
     */
    onSuccess(response) {

    }

    /**
     * Handle a failed form submission.
     * @param error
     */
    onFail(errors) {
        this.errors.record(errors)
    }

    /**
     * Update original data to latest
     * @param data
     */
    updateOriginalData(){
        this.originalData = this.data();
    }
}

export default Form;