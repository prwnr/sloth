/**
 * Class string
 */
class String {

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
            .replace(/\s+/g, separator)
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, separator)
            .replace(/^-+/, '')
            .replace(/-+$/, '')
            .replace(/_+$/, '');
    }
}

export default String;