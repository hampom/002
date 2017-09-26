import m from "mithril";
import Stream from "mithril/stream";

const API_URL = 'http://localhost:8081/api/calendar';

class eventModel {
    constructor(data) {
        this.id = Stream(data.id);
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

    load(calendar_id) {
        this.calendar_id(calendar_id);

        return m.request({
            method: "GET",
            url: API_URL + "/" + calendar_id,
            extract: function (xhr) {
                if (xhr.status === 404) {
                    // TODO: ステータスが404だった場合は、404ビューに切りかえたい。
                }

                let data = xhr.responseText;
                try {return data !== "" ? JSON.parse(data) : null}
                catch (e) {throw new Error(data)}
            },
            type: (data) => {
                let tmp = {};
                let list = {};
                for (var day in data) {
                    for (var i = 0, len = data[day].length; i < len; i++) {
                        if (tmp[day] === undefined) {
                            tmp[day] = [];
                        }
                        list[data[day][i]['id']] = { "name": data[day][i]['title'], "visible": true };
                        tmp[day].push(new eventModel(data[day][i]));
                    }
                }

                return { event: tmp, title: list };
            }
        })
        .then(this.events);
    }

    add(date, title, interval_setting, interval_num) {
        let calendar_id = this.getCalenderId();
        return m.request({
            method: "POST",
            url: API_URL + "/" + calendar_id,
            data: {
                title: title(),
                date: date(),
                interval_setting: interval_setting(),
                interval_num: interval_num(),
            }
        })
        .then((result) => this.load(calendar_id));
    }

    getCalenderId() {
        return this.calendar_id();
    }
}

export default new Event();