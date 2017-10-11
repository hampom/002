import m from "mithril";
import Stream from "mithril/stream";

const TOKEN_API_URI = "http://localhost:8081/api/token";

class userModel {
    constructor(data) {
        this.id = Stream(data.id);
        this.token = Stream(data.token);
    }
}

class User {
    getToken() {
        return localStorage.getItem("token");
    }

    hasToken() {
        return this.getToken() !== null;
    }

    deletetoken() {
        return localStorage.removeItem("token");
    }
}

export default new User();