import m from "mithril";
import Stream from "mithril/stream";

const API_URL = 'http://localhost:8081/api/calendar'

class eventModel {
    constructor(data) {
        this.title = Stream(data.title);
        this.description = Stream(data.description);
        this.startAt = Stream(data.startAt);
        this.endAt = Stream(data.endAt);
    }
}

class Event {
    constructor() {
        this.calendar_id = Stream("");
        this.events = Stream({});
    }

    load() {
        return m.request({
            method: "GET",
            url: API_URL,
            type: (data) => {
                let tmp = {};
                for (var day in data) {
                    for (var i = 0, len = data[day].length; i < len; i++) {
                        //let tmp = calendar();
                        if (tmp[day] === undefined) {
                            tmp[day] = [];
                        }
                        tmp[day].push(new eventModel(data[day][i]));
                        //calendar(tmp);
                    }
                }

                return tmp;
            }
        })
        .then(this.events);
    }
}

export default new Event();