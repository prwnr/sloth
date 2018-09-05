/**
 * Class User
 */
class User {

    /**
     * User constructor
     * @param data
     */
    constructor(data) {
        this.data = data.data;
        this.team = data.team;
        this.projects = data.projects;
        this.roles = data.roles;
        this.permissions = data.permissions;
    }

    /**
     * Return user field value
     * @param field
     * @returns {*}
     */
    get(field) {
        return this.data[field];
    }

    /**
     * Check if user has permission
     * @param perm
     * @returns {boolean}
     */
    can(perm) {
        if (!this.permissions) {
            return false;
        }

        if (this.permissions.includes(perm)) {
            return true;
        }

        return false;
    }

    /**
     * Check if user has role
     * @param perm
     * @returns {boolean}
     */
    hasRole(role) {
        if (!this.roles) {
            return false;
        }

        if (this.roles.find(item => item.name == role)) {
            return true;
        }

        return false;
    }
}

export default User;