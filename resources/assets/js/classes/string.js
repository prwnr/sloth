/**
 * Class string
 */
export default class String {

    /**
     * Create a new Errors instance
     */
    constructor(text) {
        this.text = text.toString();
    }

    /**
     * Changes string to short code
     * @param {integer} lenght 
     */
    codify(lenght) {
        if (!this.text) {
            return '';
        }

        let code = this.slugify('-').toUpperCase();
        let codeParts = code.split('-');
        code = codeParts.map(item => {
            return item.slice(0,1);    
        }).join('');

        return code.slice(0, lenght);
    }

    /**
     * Returns text as slugged string
     * @param {string} separator 
     */
    slugify(separator) {
        return this.text.toLowerCase()
            .replace(/\s+/g, separator)     // Replace spaces with -
            .replace(/[^\w\-]+/g, '') // Remove all non-word chars
            .replace(/\-\-+/g, separator)   // Replace multiple - with single -
            .replace(/^-+/, '')       // Trim - from start of text
            .replace(/-+$/, '');
    }
}