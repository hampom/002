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

    refreshToken() {
        return m.request({
            method: "POST",
            url: TOKEN_API_URI + "_refresh",
            type: userModel,
            headers: {
                "Authorization": "Bearer " + this.getToken()
            }
        })
        .then((result) => localStorage.setItem('token', result.token()))
        .catch((e) => {
            throw new Error(e);
        });
    }
}

export default new User();