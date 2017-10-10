import m from "mithril";
import Stream from "mithril/stream";

import User from "./User";
import PvEvent from "../models/PvEvent"

const HOST = 'http://localhost:8081';
const API_URL = HOST + '/api/calendar';
const WEB_URL = HOST + '/view';

class eventModel {
    constructor(data) {
        this.id = Stream(data.id);
        this.title = Stream(data.title);
        this.description = Stream(data.description);
        this.startAt = Stream(data.startAt);
        this.endAt = Stream(data.endAt);
        this.interval = Stream(data.interval);
    }
}

class Event {
    constructor() {
        this.calendar_id = Stream("");
        this.events = Stream({});
        this.icalUrl = Stream.combine((calendar_id) => {
            return HOST + "/" + calendar_id() + ".ical";
        },[ this.calendar_id ]);
    }

    load(calendar_id) {
        this.calendar_id(calendar_id);

        return m.request({
            method: "GET",
            url: HOST + "/" + calendar_id + ".json",
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
                for (let day in data) {
                    for (let i = 0, len = data[day].length; i < len; i++) {
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
        .then(this.events)
        .then(User.refreshToken());
    }

    loadItem(event_id) {
        return m.request({
            method: "GET",
            url: API_URL + "/" + this.calendar_id() + "/" + event_id,
            headers: {
                "Authorization": "Bearer " + User.getToken()
            },
            extract: function (xhr) {
                if (xhr.status === 404) {
                    throw new Error;
                }

                let data = xhr.responseText;
                try {return data !== "" ? JSON.parse(data) : null}
                catch (e) {throw new Error(data)}
            },
            type: eventModel
        })
        .then((result) => PvEvent.edit(result))
        .catch((e) => {
            alert("指定にあやまりがあります");
        });

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
            },
            headers: {
                "Authorization": "Bearer " + User.getToken()
            }
        })
            .then((result) => this.load(calendar_id));
    }

    update(id, date, title, interval_setting, interval_num) {
        let calendar_id = this.getCalenderId();
        return m.request({
            method: "PUT",
            url: API_URL + "/" + calendar_id + "/" + id(),
            data: {
                title: title(),
                date: date(),
                interval_setting: interval_setting(),
                interval_num: interval_num(),
            },
            headers: {
                "Authorization": "Bearer " + User.getToken()
            }
        })
        .then((result) => this.load(calendar_id));
    }

    getCalenderId() {
        return this.calendar_id();
    }
}

export default new Event();