import m from "mithril";
import Stream from "mithril/stream";
import moment from "moment";

import Calendar from "../models/Calendar"

class PvEvent {
    constructor() {
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
                    event.date.add(parseInt(num()), setting().toLowerCase());
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

    generate_calendar() {
    }
}

export default new PvEvent();
