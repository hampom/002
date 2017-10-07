import m from "mithril";
import Stream from "mithril/stream";
import moment from "moment";

import Calendar from "../models/Calendar"

class PvEvent {
    constructor() {
        this.momentKeys = {
            "Y": "years",
            "M": "months",
            "W": "weeks",
            "D": "days",
        };
        this.id = Stream("");
        this.title = Stream("");
        this.description = Stream("");
        this.startAt = Stream("");
        this.endAt = Stream("");
        this.interval_setting = Stream("N");
        this.interval_num = Stream("");

        this.calendar = Stream.combine((title, startAt, setting, num, startDate, endDate) => {


            if (!title() || !startAt() || !setting()) {
                return [];
            }

            let event = {
                'date': moment(startAt(), "Y-M-DD"),
                'title': title(),
            };
            let result = [];
            result[event.date.format("Y-MM-DD")] = event;

            if (setting() !== "N" && num() !== "") {
                while (event.date.isSameOrBefore(endDate())) {
                    result[event.date.format("Y-MM-DD")] = event;
                    event.date.add(parseInt(num()), this.momentKeys[setting()]);
                }
            }

            return result;
        }, [
            this.title,
            this.startAt,
            this.interval_setting,
            this.interval_num,
            Calendar.calendarStartDate,
            Calendar.calendarEndDate,
        ]);
    }

    edit (event) {
        this.id(event.id());
        this.title(event.title());
        this.description(event.description());
        this.startAt(moment(event.startAt()).format("Y-MM-DD"));
        this.endAt(event.endAt());
        this.interval_setting("");
        this.interval_num("");
        document.querySelector("[for=interval-N]").MaterialRadio.check();

        if (event.interval() !== null) {
            const interval = event.interval().match(/^P([0-9]+)([YMWD])/);
            this.interval_setting(interval[2]);
            document.querySelector("[for=interval-" + interval[2] + "]").MaterialRadio.check();
            this.interval_num(interval[1]);
        }
    }
}

export default new PvEvent();
